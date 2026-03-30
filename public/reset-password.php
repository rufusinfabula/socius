<?php
/*
 * Socius - Open Source Association Management System
 * Copyright (C) 2026 Fabio Ranfi
 */

declare(strict_types=1);

require_once __DIR__ . '/_init.php';

use Socius\Models\User;

if (!empty($_SESSION['auth_user_id'])) {
    redirect('dashboard.php');
}

$token        = trim((string) ($_GET['token'] ?? ''));
$tokenInvalid = false;
$error        = null;
$user         = null;

if ($token !== '') {
    try {
        $user = User::findByResetToken($token);
    } catch (\Throwable) {}
}

if ($token === '' || $user === null) {
    $tokenInvalid = true;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !$tokenInvalid) {
    if (!csrf_verify()) {
        $error = 'Sessione scaduta. Ricarica la pagina e riprova.';
    } else {
        $newPassword     = (string) ($_POST['password'] ?? '');
        $confirmPassword = (string) ($_POST['password_confirmation'] ?? '');

        if (strlen($newPassword) < 8) {
            $error = 'La password deve essere di almeno 8 caratteri.';
        } elseif (!hash_equals($newPassword, $confirmPassword)) {
            $error = 'Le password non coincidono.';
        } else {
            try {
                User::updatePassword((int) $user['id'], $newPassword);
                User::clearResetToken((int) $user['id']);
                csrf_regenerate();
                flash_set('success', 'Password reimpostata con successo. Accedi con la nuova password.');
                redirect('login.php');
            } catch (\Throwable) {
                $error = 'Errore di sistema. Riprova.';
            }
        }
    }
}

csrf_token();

theme('reset-password', [
    'token'        => $token,
    'tokenInvalid' => $tokenInvalid,
    'error'        => $error,
]);
