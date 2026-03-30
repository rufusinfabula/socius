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
$categories  = [];
$error       = null;
$formData    = null;

try {
    $db         = Database::getInstance();
    $categories = $db->fetchAll('SELECT id, label FROM membership_categories ORDER BY label ASC');
} catch (\Throwable) {}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_verify()) {
        $error = 'Sessione scaduta. Ricarica la pagina e riprova.';
    } else {
        $data = [
            'surname'     => trim((string) ($_POST['surname'] ?? '')),
            'name'        => trim((string) ($_POST['name'] ?? '')),
            'sesso'       => in_array($_POST['sesso'] ?? '', ['M', 'F'], true) ? $_POST['sesso'] : null,
            'genere'      => trim((string) ($_POST['genere'] ?? '')) ?: null,
            'birth_date'  => ($_POST['birth_date'] ?? '') ?: null,
            'birth_place' => trim((string) ($_POST['birth_place'] ?? '')),
            'fiscal_code' => strtoupper(trim((string) ($_POST['fiscal_code'] ?? ''))),
            'email'       => trim((string) ($_POST['email'] ?? '')),
            'phone1'      => trim((string) ($_POST['phone1'] ?? '')),
            'phone2'      => trim((string) ($_POST['phone2'] ?? '')),
            'address'     => trim((string) ($_POST['address'] ?? '')),
            'postal_code' => trim((string) ($_POST['postal_code'] ?? '')),
            'city'        => trim((string) ($_POST['city'] ?? '')),
            'province'    => strtoupper(trim((string) ($_POST['province'] ?? ''))),
            'country'     => strtoupper(trim((string) ($_POST['country'] ?? 'IT'))),
            'status'      => (string) ($_POST['status'] ?? 'attivo'),
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
                $newId = Member::create($data);
                Member::audit(current_user_id(), 'create', $newId, null, $data, client_ip());
                csrf_regenerate();
                flash_set('success', 'Socio creato con successo.');
                redirect('member.php?id=' . $newId);
            } catch (\Throwable $e) {
                $error    = 'Errore durante il salvataggio: ' . $e->getMessage();
                $formData = $data;
            }
        }
    }
}

csrf_token();

theme('member-form', [
    'activeNav'   => 'members',
    'currentUser' => $currentUser,
    'member'      => $formData,
    'categories'  => $categories,
    'isEdit'      => false,
    'error'       => $error,
]);
