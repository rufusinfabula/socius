<?php
/*
 * Socius - Open Source Association Management System
 * Copyright (C) 2026 Fabio Ranfi
 */

declare(strict_types=1);

require_once __DIR__ . '/_init.php';

requireStaff();

use Socius\Core\Database;
use Socius\Models\Member;
use Socius\Models\Membership;

$currentUser = current_user();
$db          = Database::getInstance();
$currentYear = (int) date('Y');

// Pre-fill from ?member_id=N
$preMemberId = (int) ($_GET['member_id'] ?? 0);
$preMember   = null;
if ($preMemberId > 0) {
    try {
        $preMember = Member::findById($preMemberId);
    } catch (\Throwable) {}
}

$error      = null;
$formData   = [];
$categories = [];
$members    = [];

try {
    $categories = $db->fetchAll(
        'SELECT id, label, annual_fee, is_exempt_from_renewal
           FROM membership_categories
          WHERE is_active = 1
          ORDER BY sort_order ASC, label ASC'
    );
} catch (\Throwable) {}

try {
    $members = $db->fetchAll(
        'SELECT id, member_number, membership_number, name, surname
           FROM members
          WHERE status NOT IN (\'resigned\', \'deceased\')
          ORDER BY surname ASC, name ASC'
    );
} catch (\Throwable) {}

// Next available card number (C00001 format).
// If the member already has a card number, pre-fill with it.
// Card numbers come from memberships.membership_number (source of truth).
$nextNumber = '';
try {
    if ($preMember && !empty($preMember['membership_number'])) {
        // Member already has a card number — show it, admin can keep or change
        $nextNumber = $preMember['membership_number'];
    } else {
        // No card yet — generate the next available
        $nextNumber = next_card_number();
    }
} catch (\Throwable) {}

// POST handler
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_verify()) {
        flash_set('error', 'Token di sicurezza non valido. Ricarica la pagina.');
        redirect('membership-new.php' . ($preMemberId > 0 ? '?member_id=' . $preMemberId : ''));
    }

    $memberId   = (int) ($_POST['member_id'] ?? 0);
    $year       = (int) ($_POST['year'] ?? $currentYear);
    $categoryId = (int) ($_POST['category_id'] ?? 0);
    $fee        = (float) str_replace(',', '.', (string) ($_POST['fee'] ?? '0'));
    $status     = trim((string) ($_POST['status'] ?? 'pending'));
    // Card number: admin may supply one; if empty, auto-generate at save time.
    // Stored in memberships.membership_number (source of truth).
    $cardNumber = trim((string) ($_POST['membership_number'] ?? ''));
    $method     = trim((string) ($_POST['payment_method'] ?? 'none'));
    $paidOn     = trim((string) ($_POST['paid_on'] ?? ''));
    $reference  = trim((string) ($_POST['payment_reference'] ?? ''));
    $notes      = trim((string) ($_POST['notes'] ?? ''));

    $formData = compact('memberId', 'year', 'categoryId', 'fee', 'status', 'cardNumber', 'method', 'paidOn', 'reference', 'notes');

    // Validation
    if ($memberId <= 0) {
        $error = __('memberships.error_member_required');
    } elseif ($year < 2000 || $year > 2100) {
        $error = __('memberships.error_year_required');
    } elseif ($categoryId <= 0) {
        $error = __('memberships.error_category_required');
    } elseif ($fee < 0) {
        $error = __('memberships.error_fee_invalid');
    } elseif ($method !== 'none' && $method !== 'waived' && $paidOn === '') {
        $error = __('memberships.error_paid_on_required');
    }

    if ($error === null) {
        try {
            // Load member and category
            $member   = Member::findById($memberId);
            $category = $db->fetch('SELECT * FROM membership_categories WHERE id = ? LIMIT 1', [$categoryId]);

            if ($member === null || $category === false) {
                throw new \RuntimeException('Member or category not found.');
            }

            // Check for duplicate membership year
            $existing = $db->fetch(
                'SELECT id FROM memberships WHERE member_id = ? AND year = ? LIMIT 1',
                [$memberId, $year]
            );
            if ($existing !== false) {
                $error = __('memberships.error_duplicate');
            } else {
                // Validate / assign card number
                if ($cardNumber !== '') {
                    // Check not reserved
                    $reserved = $db->fetch(
                        'SELECT id FROM reserved_member_numbers WHERE membership_number = ? LIMIT 1',
                        [$cardNumber]
                    );
                    if ($reserved !== false) {
                        $error = __('memberships.error_number_reserved');
                    }
                    // Check not taken by another member
                    if ($error === null) {
                        $taken = $db->fetch(
                            'SELECT id FROM members WHERE membership_number = ? AND id != ? LIMIT 1',
                            [$cardNumber, $memberId]
                        );
                        if ($taken !== false) {
                            $error = __('memberships.error_number_taken');
                        }
                    }
                }
            }

            if ($error === null) {
                $db->transaction(function ($db) use (
                    $memberId, $year, $categoryId, $fee, $status,
                    $cardNumber, $method, $paidOn, $reference, $notes,
                    $member, $category, $currentUser
                ): void {
                    $isExempt = (bool) ($category['is_exempt_from_renewal'] ?? false);

                    // Determine final status
                    $finalStatus = 'pending';
                    if ($isExempt) {
                        $finalStatus = 'waived';
                    } elseif ($method !== 'none' && $method !== 'waived') {
                        $finalStatus = 'paid';
                    } elseif ($method === 'waived') {
                        $finalStatus = 'waived';
                    }

                    // Resolve card number: use submitted value or auto-generate.
                    // Stored in memberships.membership_number (source of truth).
                    $assignedCard = $cardNumber !== '' ? $cardNumber : next_card_number();

                    // Create membership record (card number stored here as source of truth)
                    $membershipId = (int) $db->insert('memberships', [
                        'member_id'          => $memberId,
                        'membership_number'  => $assignedCard,
                        'category_id'        => $categoryId,
                        'year'               => $year,
                        'fee'                => number_format($fee, 2, '.', ''),
                        'status'             => $finalStatus,
                        'valid_from'         => $year . '-01-01',
                        'valid_until'        => $year . '-12-31',
                        'paid_on'            => ($finalStatus === 'paid' && $paidOn !== '') ? $paidOn : null,
                        'payment_method'     => $method !== 'none' ? $method : null,
                        'payment_reference'  => $reference !== '' ? $reference : null,
                        'notes'              => $notes !== '' ? $notes : null,
                    ]);

                    // Create payment records for actual payment methods
                    $gatewayMap = [
                        'cash'          => 'cash',
                        'bank_transfer' => 'bank_transfer',
                        'paypal'        => 'paypal',
                        'satispay'      => 'satispay',
                        'other'         => 'cash',
                    ];
                    if (!$isExempt && $method !== 'none' && $method !== 'waived' && isset($gatewayMap[$method])) {
                        $gateway  = $gatewayMap[$method];
                        $paidAtTs = $paidOn !== '' ? $paidOn . ' 00:00:00' : date('Y-m-d H:i:s');

                        $prId = (int) $db->insert('payment_requests', [
                            'member_id'     => $memberId,
                            'membership_id' => $membershipId,
                            'amount'        => number_format($fee, 2, '.', ''),
                            'description'   => 'Tessera ' . $year . ($category['label'] ? ' — ' . $category['label'] : ''),
                            'status'        => 'paid',
                            'gateway'       => $gateway,
                        ]);

                        $db->insert('payments', [
                            'payment_request_id' => $prId,
                            'member_id'          => $memberId,
                            'amount'             => number_format($fee, 2, '.', ''),
                            'gateway'            => $gateway,
                            'status'             => 'completed',
                            'paid_at'            => $paidAtTs,
                            'notes'              => $reference !== '' ? $reference : null,
                        ]);
                    }

                    // Sync card number to members.membership_number (denormalized copy).
                    // Always update — membership creation always (re-)assigns the card.
                    $db->update('members', ['membership_number' => $assignedCard], ['id' => $memberId]);

                    // Update member status to active if paid or waived
                    if (in_array($finalStatus, ['paid', 'waived'], true)) {
                        $db->update('members', ['status' => 'active'], ['id' => $memberId]);
                    }

                    // Audit log
                    $db->insert('audit_logs', [
                        'user_id'     => (int) ($currentUser['id'] ?? 0),
                        'action'      => 'membership.create',
                        'entity_type' => 'memberships',
                        'entity_id'   => $membershipId,
                        'old_values'  => null,
                        'new_values'  => json_encode([
                            'member_id'        => $memberId,
                            'year'             => $year,
                            'status'           => $finalStatus,
                            'fee'              => $fee,
                            'method'           => $method,
                            'membership_number'=> $assignedCard,
                        ]),
                        'ip_address'  => client_ip(),
                        'user_agent'  => substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 512),
                    ]);

                    flash_set('success', __('memberships.created_ok'));
                    redirect('membership.php?id=' . $membershipId);
                });
            }
        } catch (\RuntimeException $ex) {
            // redirect was called inside the transaction callback; if we get here it's an error
            $error = __('memberships.error_save_generic');
        } catch (\Throwable $ex) {
            $error = __('memberships.error_save_generic');
        }
    }
}

// Category fees for JS
$categoryFees = [];
foreach ($categories as $cat) {
    $categoryFees[(int) $cat['id']] = (float) $cat['annual_fee'];
}

theme('membership-form', [
    'activeNav'    => 'memberships',
    'currentUser'  => $currentUser,
    'isEdit'       => false,
    'membership'   => $formData,
    'preMember'    => $preMember,
    'members'      => $members,
    'categories'   => $categories,
    'categoryFees' => $categoryFees,
    'nextNumber'   => $nextNumber,
    'currentYear'  => $currentYear,
    'error'        => $error,
    'flashSuccess' => flash_get('success'),
    'flashError'   => flash_get('error'),
]);
