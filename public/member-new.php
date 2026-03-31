<?php
/*
 * Socius - Open Source Association Management System
 * Copyright (C) 2026 Fabio Ranfi
 */

declare(strict_types=1);

require_once __DIR__ . '/_init.php';

requireStaff();

use Socius\Models\Member;
use Socius\Models\MembershipCategory;
use Socius\Models\BoardRole;
use Socius\Models\BoardMembership;

$currentUser = current_user();
$categories  = [];
$boardRoles  = [];
$error       = null;
$formData    = null;

try {
    $categories = MembershipCategory::findAll(true);
} catch (\Throwable) {}

try {
    $boardRoles = BoardRole::findAll(true);
} catch (\Throwable) {}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_verify()) {
        $error = 'Sessione scaduta. Ricarica la pagina e riprova.';
    } else {
        $data = [
            'surname'     => trim((string) ($_POST['surname'] ?? '')),
            'name'        => trim((string) ($_POST['name'] ?? '')),
            'sex'         => in_array($_POST['sex'] ?? '', ['M', 'F'], true) ? $_POST['sex'] : null,
            'gender'      => trim((string) ($_POST['gender'] ?? '')) ?: null,
            'birth_date'  => ($_POST['birth_date'] ?? '') ?: null,
            'birth_place' => trim((string) ($_POST['birth_place'] ?? '')),
            'fiscal_code' => strtoupper(trim((string) ($_POST['fiscal_code'] ?? ''))) ?: null,
            'email'       => trim((string) ($_POST['email'] ?? '')),
            'phone1'      => trim((string) ($_POST['phone1'] ?? '')),
            'phone2'      => trim((string) ($_POST['phone2'] ?? '')),
            'address'     => trim((string) ($_POST['address'] ?? '')),
            'postal_code' => trim((string) ($_POST['postal_code'] ?? '')),
            'city'        => trim((string) ($_POST['city'] ?? '')),
            'province'    => strtoupper(trim((string) ($_POST['province'] ?? ''))),
            'country'     => strtoupper(trim((string) ($_POST['country'] ?? 'IT'))),
            'status'      => (string) ($_POST['status'] ?? 'active'),
            'category_id' => ($_POST['category_id'] ?? '') !== '' ? (int) $_POST['category_id'] : null,
            'joined_on'   => ($_POST['joined_on'] ?? '') ?: date('Y-m-d'),
            'resigned_on' => ($_POST['resigned_on'] ?? '') ?: null,
            'notes'       => (int) ($currentUser['role_id'] ?? 4) <= 3
                ? trim((string) ($_POST['notes'] ?? ''))
                : '',
        ];

        if ($data['surname'] === '' || $data['name'] === '' || $data['email'] === '') {
            $error    = 'Cognome, nome ed email sono obbligatori.';
            $formData = $data;
        } else {
            try {
                $newId     = Member::create($data);
                $created   = Member::findById($newId);
                $memberNum = $created['member_number'] ?? '';

                // Board membership
                $boardRoleId = (int) ($_POST['board_role_id'] ?? 0);
                if ($boardRoleId > 0) {
                    try {
                        BoardMembership::create([
                            'member_id'  => $newId,
                            'role_id'    => $boardRoleId,
                            'elected_on' => ($_POST['board_elected_on'] ?? '') ?: date('Y-m-d'),
                            'notes'      => trim((string) ($_POST['board_notes'] ?? '')) ?: null,
                            'created_by' => current_user_id(),
                        ]);
                    } catch (\Throwable) {}
                }

                Member::audit(current_user_id(), 'create', $newId, null, $data, client_ip());
                csrf_regenerate();
                flash_set('success', 'Socio creato con successo — N. socio: ' . $memberNum);
                redirect('member.php?id=' . $newId);
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
                $formData   = $data;
            }
        }
    }
}

csrf_token();

theme('member-form', [
    'activeNav'        => 'members',
    'currentUser'      => $currentUser,
    'member'           => $formData,
    'categories'       => $categories,
    'boardRoles'       => $boardRoles,
    'currentBoardRole' => null,
    'isEdit'           => false,
    'error'            => $error,
    'errorDebug'       => $errorDebug ?? null,
]);
