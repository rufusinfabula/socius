<?php
/*
 * Socius - Open Source Association Management System
 * Copyright (C) 2026 Fabio Ranfi
 */

declare(strict_types=1);

require_once __DIR__ . '/_init.php';

requireStaff();

use Socius\Models\Member;
use Socius\Models\Membership;
use Socius\Models\Payment;
use Socius\Models\MembershipCategory;
use Socius\Models\BoardRole;
use Socius\Models\BoardMembership;

$currentUser = current_user();
$id          = (int) ($_GET['id'] ?? 0);

if ($id <= 0) {
    redirect('members.php');
}

$member = null;
try {
    $member = Member::findById($id);
} catch (\Throwable) {}

if ($member === null) {
    flash_set('error', 'Socio non trovato.');
    redirect('members.php');
}

$categories       = [];
$boardRoles       = [];
$currentBoardRole = null;
$error            = null;

try {
    $categories = MembershipCategory::findAll(true);
} catch (\Throwable) {}

try {
    $boardRoles = BoardRole::findAll(true);
} catch (\Throwable) {}

// Load the member's currently active board role (if any)
try {
    $today = date('Y-m-d');
    $allMemberships = BoardMembership::findByMember($id);
    foreach ($allMemberships as $bm) {
        if ($bm['resigned_on'] === null &&
            ($bm['expires_on'] === null || $bm['expires_on'] >= $today)
        ) {
            $currentBoardRole = $bm;
            break;
        }
    }
} catch (\Throwable) {}

$isSuperAdmin = (int) ($currentUser['role_id'] ?? 4) === 1;

// Pre-load danger zone data (shown in emergency delete summary)
$memberMemberships = [];
$memberPayments    = [];
if ($isSuperAdmin) {
    try { $memberMemberships = Membership::findByMember($id); } catch (\Throwable) {}
    try { $memberPayments    = Payment::findByMember($id);    } catch (\Throwable) {}
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_verify()) {
        $error = 'Sessione scaduta. Ricarica la pagina e riprova.';
    } elseif (trim((string) ($_POST['_action'] ?? '')) === 'dangerous') {
        // =====================================================================
        // DANGER ZONE — super_admin only
        // =====================================================================
        if (!$isSuperAdmin) {
            flash_set('error', __('members.delete_forbidden'));
            redirect('member-edit.php?id=' . $id);
        }

        $operation  = trim((string) ($_POST['operation'] ?? ''));
        $motivation = trim((string) ($_POST['motivation'] ?? ''));

        if ($operation === 'change_member_number') {
            $newMemberNumber = (int) ($_POST['new_member_number'] ?? 0);

            if (strlen($motivation) < 10) {
                flash_set('error', __('members.dangerous_motivation_required'));
                redirect('member-edit.php?id=' . $id);
            }
            if ($newMemberNumber <= 0) {
                flash_set('error', __('members.dangerous_number_invalid'));
                redirect('member-edit.php?id=' . $id);
            }

            // Check not already taken by another member
            $db     = \Socius\Core\Database::getInstance();
            $taken  = $db->fetch(
                'SELECT id FROM members WHERE member_number = ? AND id != ? LIMIT 1',
                [$newMemberNumber, $id]
            );
            if ($taken !== false) {
                flash_set('error', __('members.dangerous_number_taken',
                    ['number' => format_member_number($newMemberNumber)]));
                redirect('member-edit.php?id=' . $id);
            }

            $oldNumber = (int) ($member['member_number'] ?? 0);
            $db->update('members', ['member_number' => $newMemberNumber], ['id' => $id]);

            $db->insert('audit_logs', [
                'user_id'     => current_user_id(),
                'action'      => 'member.dangerous.change_member_number',
                'entity_type' => 'members',
                'entity_id'   => $id,
                'old_values'  => json_encode(['member_number' => $oldNumber]),
                'new_values'  => json_encode([
                    'member_number' => $newMemberNumber,
                    'motivation'    => $motivation,
                ]),
                'ip_address'  => client_ip(),
                'user_agent'  => substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 512),
            ]);

            csrf_regenerate();
            flash_set('success', __('members.dangerous_number_changed',
                ['number' => format_member_number($newMemberNumber)]));
            redirect('member-edit.php?id=' . $id);
        }

        if ($operation === 'force_member_status') {
            $newStatus = trim((string) ($_POST['new_status'] ?? ''));
            $validStatuses = ['active', 'in_renewal', 'not_renewed', 'lapsed', 'suspended', 'resigned', 'deceased'];

            if (strlen($motivation) < 10) {
                flash_set('error', __('members.dangerous_motivation_required'));
                redirect('member-edit.php?id=' . $id);
            }
            if (!in_array($newStatus, $validStatuses, true)) {
                flash_set('error', __('members.error_save_generic'));
                redirect('member-edit.php?id=' . $id);
            }

            $oldStatus = $member['status'] ?? '';
            $db = \Socius\Core\Database::getInstance();
            $db->update('members', ['status' => $newStatus], ['id' => $id]);

            $db->insert('audit_logs', [
                'user_id'     => current_user_id(),
                'action'      => 'member.dangerous.force_status',
                'entity_type' => 'members',
                'entity_id'   => $id,
                'old_values'  => json_encode(['status' => $oldStatus]),
                'new_values'  => json_encode([
                    'status'     => $newStatus,
                    'motivation' => $motivation,
                ]),
                'ip_address'  => client_ip(),
                'user_agent'  => substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 512),
            ]);

            csrf_regenerate();
            flash_set('success', __('members.dangerous_force_status_changed',
                ['status' => __('members.status_' . $newStatus)]));
            redirect('member-edit.php?id=' . $id);
        }

        if ($operation === 'emergency_delete') {
            $confirmWord = trim((string) ($_POST['confirm_word'] ?? ''));
            $freeNumber  = (bool) ($_POST['free_number'] ?? false);

            if (strlen($motivation) < 10) {
                flash_set('error', __('members.dangerous_motivation_required'));
                redirect('member-edit.php?id=' . $id);
            }
            if ($confirmWord !== 'DELETE') {
                flash_set('error', __('members.delete_wrong_confirm'));
                redirect('member-edit.php?id=' . $id);
            }

            $memberNumber = (int) ($member['member_number'] ?? 0);
            $deleted = Member::emergencyDelete($id, $freeNumber, current_user_id(), $motivation, client_ip());

            if ($deleted) {
                flash_set('success', __('members.deleted_ok', ['number' => format_member_number($memberNumber)]));
                redirect('members.php');
            } else {
                flash_set('error', __('members.not_found'));
                redirect('member-edit.php?id=' . $id);
            }
        }

        // Unknown operation — fall through to normal render
    } else {
        $oldData = $member;
        $data    = [
            'surname'     => trim((string) ($_POST['surname'] ?? '')),
            'name'        => trim((string) ($_POST['name'] ?? '')),
            'sex'         => in_array($_POST['sex'] ?? '', ['M', 'F'], true) ? $_POST['sex'] : null,
            'gender'      => trim((string) ($_POST['gender'] ?? '')) ?: null,
            'birth_date'  => ($_POST['birth_date'] ?? '') ?: null,
            'birth_place' => trim((string) ($_POST['birth_place'] ?? '')),
            'fiscal_code' => strtoupper(trim((string) ($_POST['fiscal_code'] ?? ''))) ?: null,
            'email'       => trim((string) ($_POST['email'] ?? '')) ?: null,
            'phone1'      => trim((string) ($_POST['phone1'] ?? '')),
            'phone2'      => trim((string) ($_POST['phone2'] ?? '')),
            'address'     => trim((string) ($_POST['address'] ?? '')),
            'postal_code' => trim((string) ($_POST['postal_code'] ?? '')),
            'city'        => trim((string) ($_POST['city'] ?? '')),
            'province'    => strtoupper(trim((string) ($_POST['province'] ?? ''))),
            'country'     => strtoupper(trim((string) ($_POST['country'] ?? 'IT'))),
            'status'      => (string) ($_POST['status'] ?? 'active'),
            'joined_on'   => ($_POST['joined_on'] ?? '') ?: date('Y-m-d'),
            'resigned_on' => ($_POST['resigned_on'] ?? '') ?: null,
            'notes'       => (int) ($currentUser['role_id'] ?? 4) <= 3
                ? trim((string) ($_POST['notes'] ?? ''))
                : ($member['notes'] ?? ''),
        ];

        if ($data['surname'] === '' || $data['name'] === '') {
            $error  = 'Cognome e nome sono obbligatori.';
            $member = array_merge($member, $data);
        } else {
            try {
                Member::update($id, $data);

                // Board membership
                $boardRoleId  = (int) ($_POST['board_role_id'] ?? 0);
                $electedOn    = ($_POST['board_elected_on'] ?? '') ?: date('Y-m-d');
                $boardNotes   = trim((string) ($_POST['board_notes'] ?? '')) ?: null;

                try {
                    if ($boardRoleId > 0) {
                        if ($currentBoardRole) {
                            if ((int) $currentBoardRole['role_id'] !== $boardRoleId) {
                                // Role changed: end old, open new
                                BoardMembership::update($currentBoardRole['id'], [
                                    'resigned_on' => date('Y-m-d'),
                                ]);
                                BoardMembership::create([
                                    'member_id'  => $id,
                                    'role_id'    => $boardRoleId,
                                    'elected_on' => $electedOn,
                                    'notes'      => $boardNotes,
                                    'created_by' => current_user_id(),
                                ]);
                            } else {
                                // Same role: update notes only
                                BoardMembership::update($currentBoardRole['id'], [
                                    'notes' => $boardNotes,
                                ]);
                            }
                        } else {
                            // No existing role: create
                            BoardMembership::create([
                                'member_id'  => $id,
                                'role_id'    => $boardRoleId,
                                'elected_on' => $electedOn,
                                'notes'      => $boardNotes,
                                'created_by' => current_user_id(),
                            ]);
                        }
                    } elseif ($currentBoardRole) {
                        // Role cleared: set resigned_on today
                        BoardMembership::update($currentBoardRole['id'], [
                            'resigned_on' => date('Y-m-d'),
                        ]);
                    }
                } catch (\Throwable) {}

                Member::audit(current_user_id(), 'update', $id, $oldData, $data, client_ip());
                csrf_regenerate();
                flash_set('success', 'Socio aggiornato con successo.');
                redirect('member.php?id=' . $id);
            } catch (\Throwable $ex) {
                $exMsg = $ex->getMessage();
                if ((string) $ex->getCode() === '23000') {
                    if (str_contains($exMsg, 'uq_email')) {
                        $error = __('members.error_duplicate_email');
                    } elseif (str_contains($exMsg, 'uq_fiscal_code')) {
                        $error = __('members.error_duplicate_fiscal');
                    } else {
                        $error = __('members.error_duplicate_number');
                    }
                } else {
                    $error = __('members.error_save_generic');
                }
                $errorDebug = \Socius\Core\Config::get('app.debug', false) ? $exMsg : null;
                $member     = array_merge($member, $data);
            }
        }
    }
}

csrf_token();

theme('member-form', [
    'activeNav'          => 'members',
    'currentUser'        => $currentUser,
    'isSuperAdmin'       => $isSuperAdmin,
    'member'             => $member,
    'categories'         => $categories,
    'boardRoles'         => $boardRoles,
    'currentBoardRole'   => $currentBoardRole,
    'memberMemberships'  => $memberMemberships,
    'memberPayments'     => $memberPayments,
    'isEdit'             => true,
    'error'              => $error,
    'errorDebug'         => $errorDebug ?? null,
]);
