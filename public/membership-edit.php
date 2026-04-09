<?php
/*
 * Socius - Open Source Association Management System
 * Copyright (C) 2026 Fabio Ranfi
 */

declare(strict_types=1);

require_once __DIR__ . '/_init.php';

requireStaff();

use Socius\Core\Database;
use Socius\Models\Membership;

$currentUser  = current_user();
$db           = Database::getInstance();
$isSuperAdmin = (int) ($currentUser['role_id'] ?? 4) === 1;
$id           = (int) ($_GET['id'] ?? 0);

if ($id <= 0) {
    flash_set('error', __('memberships.not_found'));
    redirect('memberships.php');
}

$membership = null;
try {
    $membership = Membership::findById($id);
} catch (\Throwable) {}

if ($membership === null) {
    flash_set('error', __('memberships.not_found'));
    redirect('memberships.php');
}

$error      = null;
$categories = [];

try {
    $categories = $db->fetchAll(
        'SELECT id, label, annual_fee, is_exempt_from_renewal
           FROM membership_categories
          WHERE is_active = 1
          ORDER BY sort_order ASC, label ASC'
    );
} catch (\Throwable) {}

// Category fees for JS
$categoryFees = [];
foreach ($categories as $cat) {
    $categoryFees[(int) $cat['id']] = (float) $cat['annual_fee'];
}

// POST handler
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_verify()) {
        flash_set('error', 'Token di sicurezza non valido. Ricarica la pagina.');
        redirect('membership-edit.php?id=' . $id);
    }

    $action = trim((string) ($_POST['_action'] ?? 'save'));

    // =========================================================================
    // Normal save
    // =========================================================================
    if ($action === 'save') {
        $status    = trim((string) ($_POST['status'] ?? 'pending'));
        $fee       = (float) str_replace(',', '.', (string) ($_POST['fee'] ?? '0'));
        $paidOn    = trim((string) ($_POST['paid_on'] ?? ''));
        $method    = trim((string) ($_POST['payment_method'] ?? ''));
        $reference = trim((string) ($_POST['payment_reference'] ?? ''));
        $notes     = trim((string) ($_POST['notes'] ?? ''));

        if ($fee < 0) {
            $error = __('memberships.error_fee_invalid');
        } elseif ($status === 'paid' && $paidOn === '') {
            $error = __('memberships.error_paid_on_required');
        }

        if ($error === null) {
            try {
                $oldValues = [
                    'status'           => $membership['status'],
                    'fee'              => $membership['fee'],
                    'paid_on'          => $membership['paid_on'],
                    'payment_method'   => $membership['payment_method'] ?? null,
                    'payment_reference'=> $membership['payment_reference'] ?? null,
                    'notes'            => $membership['notes'],
                ];

                $newData = [
                    'status'           => $status,
                    'fee'              => number_format($fee, 2, '.', ''),
                    'paid_on'          => $paidOn !== '' ? $paidOn : null,
                    'payment_method'   => $method !== '' ? $method : null,
                    'payment_reference'=> $reference !== '' ? $reference : null,
                    'notes'            => $notes !== '' ? $notes : null,
                ];

                Membership::update($id, $newData);

                Membership::audit(
                    (int) ($currentUser['id'] ?? 0),
                    'membership.update',
                    $id,
                    $oldValues,
                    $newData,
                    client_ip()
                );

                // Ricalcola lo status del socio se è cambiato lo status della tessera
                if (($oldValues['status'] ?? '') !== $status) {
                    try {
                        $settingsRows = $db->fetchAll('SELECT `key`, `value` FROM `settings`');
                        $settings     = array_column($settingsRows, 'value', 'key');
                        $memberId2    = (int) ($membership['member_id'] ?? 0);
                        $freshMember  = $db->fetch(
                            'SELECT id, status FROM members WHERE id = ? LIMIT 1',
                            [$memberId2]
                        );
                        if ($freshMember) {
                            $freshMember['membership_year']   = (int) ($membership['year'] ?? 0);
                            $freshMember['membership_status'] = $status;
                            $calcStatus = calculate_member_status($freshMember, $settings);
                            if ($calcStatus !== (string) ($freshMember['status'] ?? '')) {
                                $db->update('members', ['status' => $calcStatus], ['id' => $memberId2]);
                            }
                        }
                    } catch (\Throwable) {}
                }

                flash_set('success', __('memberships.updated_ok'));
                redirect('membership.php?id=' . $id);
            } catch (\Throwable) {
                $error = __('memberships.error_save_generic');
            }
        }

        // Reload membership with updated values on error
        if ($error !== null) {
            $membership['status']           = $status;
            $membership['fee']              = $fee;
            $membership['paid_on']          = $paidOn ?: null;
            $membership['payment_method']   = $method ?: null;
            $membership['payment_reference']= $reference ?: null;
            $membership['notes']            = $notes;
        }
    }

    // =========================================================================
    // Dangerous zone operations (super_admin only)
    // =========================================================================
    elseif ($action === 'dangerous') {
        if (!$isSuperAdmin) {
            flash_set('error', __('memberships.dangerous_forbidden'));
            redirect('membership-edit.php?id=' . $id);
        }

        $operation  = trim((string) ($_POST['operation'] ?? ''));
        $motivation = trim((string) ($_POST['motivation'] ?? ''));

        if (strlen($motivation) < 10) {
            flash_set('error', __('memberships.dangerous_motivation_min'));
            redirect('membership-edit.php?id=' . $id);
        }

        $userId = (int) ($currentUser['id'] ?? 0);
        $ip     = client_ip();

        try {
            switch ($operation) {

                case 'reserve_number':
                    $confirmNumber = trim((string) ($_POST['confirm_number'] ?? ''));
                    $memberNumber  = (string) ($membership['membership_number'] ?? '');
                    if ($confirmNumber !== $memberNumber) {
                        flash_set('error', __('memberships.dangerous_confirm_mismatch'));
                        redirect('membership-edit.php?id=' . $id);
                    }
                    try {
                        $db->insert('reserved_member_numbers', [
                            'membership_number' => $memberNumber,
                            'reserved_at'       => date('Y-m-d H:i:s'),
                            'reserved_by'       => $userId,
                            'reason'            => $motivation,
                        ]);
                    } catch (\Exception) {
                        // Already reserved — ignore
                    }
                    Membership::audit($userId, 'membership.dangerous.reserve_number', $id,
                        ['membership_number' => $memberNumber],
                        ['reserved' => true, 'motivation' => $motivation],
                        $ip
                    );
                    break;

                case 'change_number':
                    $newNumber = trim((string) ($_POST['new_number'] ?? ''));
                    if ($newNumber === '') {
                        flash_set('error', 'Numero tessera non valido.');
                        redirect('membership-edit.php?id=' . $id);
                    }
                    // membership_number on ms row = source of truth for this record's card number
                    $oldNumber = (string) ($membership['membership_number'] ?? '');
                    $memberId  = (int) ($membership['member_id'] ?? 0);
                    // Update source of truth on the membership record
                    $db->update('memberships', ['membership_number' => $newNumber], ['id' => $id]);
                    // Update denormalized copy on members via model helper
                    \Socius\Models\Member::updateCardNumber($memberId, $newNumber);
                    Membership::audit($userId, 'membership.dangerous.change_number', $id,
                        ['membership_number' => $oldNumber],
                        ['membership_number' => $newNumber, 'motivation' => $motivation],
                        $ip
                    );
                    break;

                case 'change_status':
                    $newStatus = trim((string) ($_POST['new_status'] ?? ''));
                    $allowed   = ['pending', 'paid', 'waived', 'cancelled'];
                    if (!in_array($newStatus, $allowed, true)) {
                        flash_set('error', 'Status non valido.');
                        redirect('membership-edit.php?id=' . $id);
                    }
                    $oldStatus = (string) ($membership['status'] ?? '');
                    Membership::update($id, ['status' => $newStatus]);
                    Membership::audit($userId, 'membership.dangerous.change_status', $id,
                        ['status' => $oldStatus],
                        ['status' => $newStatus, 'motivation' => $motivation],
                        $ip
                    );
                    // Ricalcola lo status del socio
                    try {
                        $settingsRows2 = $db->fetchAll('SELECT `key`, `value` FROM `settings`');
                        $settings2     = array_column($settingsRows2, 'value', 'key');
                        $memberId3     = (int) ($membership['member_id'] ?? 0);
                        $freshMember3  = $db->fetch(
                            'SELECT id, status FROM members WHERE id = ? LIMIT 1',
                            [$memberId3]
                        );
                        if ($freshMember3) {
                            $freshMember3['membership_year']   = (int) ($membership['year'] ?? 0);
                            $freshMember3['membership_status'] = $newStatus;
                            $calcStatus3 = calculate_member_status($freshMember3, $settings2);
                            if ($calcStatus3 !== (string) ($freshMember3['status'] ?? '')) {
                                $db->update('members', ['status' => $calcStatus3], ['id' => $memberId3]);
                            }
                        }
                    } catch (\Throwable) {}
                    break;

                case 'change_fee':
                    $newFee = (float) str_replace(',', '.', (string) ($_POST['new_fee'] ?? '0'));
                    if ($newFee < 0) {
                        flash_set('error', __('memberships.error_fee_invalid'));
                        redirect('membership-edit.php?id=' . $id);
                    }
                    $oldFee = (float) ($membership['fee'] ?? 0);
                    Membership::update($id, ['fee' => number_format($newFee, 2, '.', '')]);
                    // Also update linked payment if exists
                    $pr = $db->fetch(
                        'SELECT p.id FROM payments p
                           JOIN payment_requests pr ON pr.id = p.payment_request_id
                          WHERE pr.membership_id = ?
                          ORDER BY p.id DESC LIMIT 1',
                        [$id]
                    );
                    if ($pr !== false) {
                        $db->update('payments', ['amount' => number_format($newFee, 2, '.', '')], ['id' => (int) $pr['id']]);
                    }
                    Membership::audit($userId, 'membership.dangerous.change_fee', $id,
                        ['fee' => $oldFee],
                        ['fee' => $newFee, 'motivation' => $motivation],
                        $ip
                    );
                    break;

                case 'force_member_status':
                    $newMemberStatus = trim((string) ($_POST['new_member_status'] ?? ''));
                    $allowedMember   = ['active', 'in_renewal', 'not_renewed', 'lapsed', 'suspended', 'resigned', 'deceased'];
                    if (!in_array($newMemberStatus, $allowedMember, true)) {
                        flash_set('error', 'Status socio non valido.');
                        redirect('membership-edit.php?id=' . $id);
                    }
                    $memberId       = (int) ($membership['member_id'] ?? 0);
                    $oldMemberStatus = (string) ($membership['member_status'] ?? '');
                    $db->update('members', ['status' => $newMemberStatus], ['id' => $memberId]);
                    Membership::audit($userId, 'membership.dangerous.force_member_status', $id,
                        ['member_status' => $oldMemberStatus],
                        ['member_status' => $newMemberStatus, 'motivation' => $motivation],
                        $ip
                    );
                    break;

                default:
                    flash_set('error', 'Operazione non riconosciuta.');
                    redirect('membership-edit.php?id=' . $id);
            }

            flash_set('success', __('memberships.dangerous_ok'));
            redirect('membership-edit.php?id=' . $id);

        } catch (\Throwable) {
            flash_set('error', __('memberships.error_save_generic'));
            redirect('membership-edit.php?id=' . $id);
        }
    }
}

theme('membership-form', [
    'activeNav'    => 'memberships',
    'currentUser'  => $currentUser,
    'isEdit'       => true,
    'isSuperAdmin' => $isSuperAdmin,
    'membership'   => $membership,
    'preMember'    => null,
    'categories'   => $categories,
    'categoryFees' => $categoryFees,
    // membership_number from the ms.* row is the source of truth for this card
    'nextNumber'   => (string) ($membership['membership_number'] ?? ''),
    'currentYear'  => (int) date('Y'),
    'error'        => $error,
    'flashSuccess' => flash_get('success'),
    'flashError'   => flash_get('error'),
]);
