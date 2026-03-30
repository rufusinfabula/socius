<?php
/*
 * Socius - Open Source Association Management System
 * Copyright (C) 2026 Fabio Ranfi
 */

declare(strict_types=1);

require_once __DIR__ . '/_init.php';

requireSuperAdmin();

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

$memberships = [];
$payments    = [];

try {
    $memberships = Member::getMemberships($id);
} catch (\Throwable) {}

try {
    $payments = Member::getPayments($id);
} catch (\Throwable) {}

$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_verify()) {
        $error = 'Sessione scaduta. Ricarica la pagina e riprova.';
    } elseif (($_POST['confirm_word'] ?? '') !== 'DELETE') {
        $error = 'Digitare DELETE per confermare la cancellazione.';
    } else {
        $freeNumber = (bool) (int) ($_POST['free_number'] ?? 1);
        try {
            $ok = Member::emergencyDelete($id, $freeNumber, current_user_id(), client_ip());
            if ($ok) {
                csrf_regenerate();
                flash_set('success', 'Socio eliminato definitivamente.');
                redirect('members.php');
            } else {
                $error = 'Impossibile eliminare il socio. Riprovare.';
            }
        } catch (\Throwable $e) {
            $error = 'Errore durante la cancellazione: ' . $e->getMessage();
        }
    }
}

csrf_token();

theme('member-delete', [
    'activeNav'   => 'members',
    'currentUser' => $currentUser,
    'member'      => $member,
    'memberships' => $memberships,
    'payments'    => $payments,
    'error'       => $error,
]);
