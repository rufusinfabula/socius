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

$categories = [];
$error      = null;

try {
    $db         = Database::getInstance();
    $categories = $db->fetchAll('SELECT id, label FROM membership_categories ORDER BY label ASC');
} catch (\Throwable) {}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_verify()) {
        $error = 'Sessione scaduta. Ricarica la pagina e riprova.';
    } else {
        $oldData = $member;
        $data    = [
            'surname'     => trim((string) ($_POST['surname'] ?? '')),
            'name'        => trim((string) ($_POST['name'] ?? '')),
            'email'       => trim((string) ($_POST['email'] ?? '')),
            'phone'       => trim((string) ($_POST['phone'] ?? '')),
            'fiscal_code' => strtoupper(trim((string) ($_POST['fiscal_code'] ?? ''))),
            'birth_date'  => ($_POST['birth_date'] ?? '') ?: null,
            'birth_place' => trim((string) ($_POST['birth_place'] ?? '')),
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
                : ($member['notes'] ?? ''),
        ];

        if ($data['surname'] === '' || $data['name'] === '' || $data['email'] === '') {
            $error  = 'Cognome, nome ed email sono obbligatori.';
            $member = array_merge($member, $data);
        } else {
            try {
                Member::update($id, $data);
                Member::audit(current_user_id(), 'update', $id, $oldData, $data, client_ip());
                csrf_regenerate();
                flash_set('success', 'Socio aggiornato con successo.');
                redirect('member.php?id=' . $id);
            } catch (\Throwable $e) {
                $error  = 'Errore durante il salvataggio: ' . $e->getMessage();
                $member = array_merge($member, $data);
            }
        }
    }
}

csrf_token();

theme('member-form', [
    'activeNav'   => 'members',
    'currentUser' => $currentUser,
    'member'      => $member,
    'categories'  => $categories,
    'isEdit'      => true,
    'error'       => $error,
]);
