<?php
/*
 * Socius - Open Source Association Management System
 * Copyright (C) 2026 Fabio Ranfi
 */

declare(strict_types=1);

require_once __DIR__ . '/_init.php';

use Socius\Models\User;

const MAX_ATTEMPTS    = 5;
const LOCKOUT_SECONDS = 900; // 15 minutes

// Already logged in
if (!empty($_SESSION['auth_user_id'])) {
    redirect('dashboard.php');
}

$error    = flash_get('error');
$success  = flash_get('success');
$info     = flash_get('info');
$emailVal = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (!csrf_verify()) {
        $error = 'Sessione scaduta. Ricarica la pagina e riprova.';
    } else {
        $ip  = client_ip();
        $key = 'login_attempts_' . md5($ip);

        $attempts = $_SESSION[$key] ?? ['count' => 0, 'until' => 0];

        // Rate limiting
        if ((int) $attempts['count'] >= MAX_ATTEMPTS && time() < (int) $attempts['until']) {
            $remaining = (int) ceil(((int) $attempts['until'] - time()) / 60);
            $error = "Troppi tentativi di accesso. Riprova tra {$remaining} minuti.";
        } else {
            // Reset expired lockout
            if (time() >= (int) $attempts['until']) {
                $attempts = ['count' => 0, 'until' => 0];
            }

            $email    = trim((string) ($_POST['email'] ?? ''));
            $password = (string) ($_POST['password'] ?? '');
            $emailVal = $email;

            $user = null;
            try {
                $user = User::findByEmail($email);
            } catch (\Throwable) {
                $error = 'Errore di sistema. Riprova.';
            }

            if ($error === null) {
                if ($user === null || !User::verifyPassword($password, (string) $user['password_hash'])) {
                    $attempts['count']++;
                    if ((int) $attempts['count'] >= MAX_ATTEMPTS) {
                        $attempts['until'] = time() + LOCKOUT_SECONDS;
                    }
                    $_SESSION[$key] = $attempts;
                    $error = 'Credenziali non valide. Controlla email e password.';
                } elseif (!(bool) $user['is_active']) {
                    $error = 'Il tuo account non è attivo. Contatta l\'amministratore.';
                } else {
                    // Success
                    unset($_SESSION[$key]);
                    session_regenerate_id(true);
                    $_SESSION['auth_user_id'] = (int) $user['id'];
                    csrf_regenerate();

                    try {
                        User::updateLastLogin((int) $user['id'], $ip);
                    } catch (\Throwable) {}

                    redirect('dashboard.php');
                }
            }
        }
    }
}

csrf_token(); // ensure token exists

theme('login', [
    'error'    => $error,
    'success'  => $success,
    'info'     => $info,
    'emailVal' => $emailVal,
]);
