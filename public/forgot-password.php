<?php
/*
 * Socius - Open Source Association Management System
 * Copyright (C) 2026 Fabio Ranfi
 */

declare(strict_types=1);

require_once __DIR__ . '/_init.php';

use Socius\Core\Config;
use Socius\Models\User;

if (!empty($_SESSION['auth_user_id'])) {
    redirect('dashboard.php');
}

$error   = null;
$success = flash_get('success');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_verify()) {
        $error = 'Sessione scaduta. Ricarica la pagina e riprova.';
    } else {
        $email = trim((string) ($_POST['email'] ?? ''));
        $user  = null;
        try {
            $user = User::findByEmail($email);
        } catch (\Throwable) {}

        if ($user !== null && (bool) $user['is_active']) {
            $token     = bin2hex(random_bytes(32));
            $expiresAt = date('Y-m-d H:i:s', time() + 3600);

            try {
                User::saveResetToken((int) $user['id'], $token, $expiresAt);

                $appName  = (string) Config::get('app.name', 'Socius');
                $appUrl   = rtrim((string) Config::get('app.url', ''), '/');
                $resetUrl = $appUrl . '/reset-password.php?token=' . urlencode($token);
                $host     = (string) (parse_url($appUrl, PHP_URL_HOST) ?: 'localhost');

                $subject = "Reimposta la password — {$appName}";
                $body    = "Clicca sul link seguente per reimpostare la password:\n\n{$resetUrl}\n\nIl link scade tra 1 ora.\n\n{$appName}";
                $headers = "From: {$appName} <noreply@{$host}>\r\nContent-Type: text/plain; charset=UTF-8\r\n";

                @mail($email, $subject, $body, $headers);
            } catch (\Throwable) {}
        }

        // Always show success (prevent user enumeration)
        csrf_regenerate();
        flash_set('success', 'Se l\'indirizzo è registrato, riceverai un\'email con le istruzioni.');
        redirect('forgot-password.php');
    }
}

csrf_token();

theme('forgot-password', [
    'error'   => $error,
    'success' => $success,
]);
