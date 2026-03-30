<?php
/*
 * Socius - Open Source Association Management System
 * Copyright (C) 2026 Fabio Ranfi
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 */

declare(strict_types=1);

namespace Socius\Controllers;

use Socius\Core\Config;
use Socius\Core\Middleware;
use Socius\Core\Request;
use Socius\Core\Response;
use Socius\Models\User;

/**
 * Authentication: login, logout, password reset.
 */
class AuthController extends BaseController
{
    private const MAX_ATTEMPTS    = 5;
    private const LOCKOUT_MINUTES = 15;

    // =========================================================================
    // Login
    // =========================================================================

    public function showLogin(Request $request, array $params): Response
    {
        if (Middleware::isAuthenticated()) {
            return $this->redirect('/');
        }

        return $this->view('themes/uikit/auth/login', [
            'csrf'  => Middleware::csrfToken(),
            'error' => $this->getFlash('error'),
        ]);
    }

    public function login(Request $request, array $params): Response
    {
        if (Middleware::isAuthenticated()) {
            return $this->redirect('/');
        }

        // CSRF
        if (!Middleware::verifyCsrfToken($request)) {
            return $this->view('themes/uikit/auth/login', [
                'csrf'  => Middleware::csrfToken(),
                'error' => __('auth.csrf_invalid'),
            ]);
        }

        $ip = $request->ip();

        // Rate limiting (session-based per IP)
        if ($this->isRateLimited($ip)) {
            return $this->view('themes/uikit/auth/login', [
                'csrf'  => Middleware::csrfToken(),
                'error' => __('auth.too_many_attempts', ['minutes' => self::LOCKOUT_MINUTES]),
            ]);
        }

        $email    = trim((string) $request->post('email', ''));
        $password = (string) $request->post('password', '');

        $user = User::findByEmail($email);

        // Verify credentials — keep error message generic to avoid user enumeration
        if (
            $user === null
            || !User::verifyPassword($password, (string) $user['password_hash'])
        ) {
            $this->incrementAttempts($ip);
            return $this->view('themes/uikit/auth/login', [
                'csrf'  => Middleware::csrfToken(),
                'error' => __('auth.invalid_credentials'),
            ]);
        }

        // Account inactive
        if (!(bool) $user['is_active']) {
            return $this->view('themes/uikit/auth/login', [
                'csrf'  => Middleware::csrfToken(),
                'error' => __('auth.account_inactive'),
            ]);
        }

        // Success
        $this->clearAttempts($ip);
        Middleware::login((int) $user['id']);
        User::updateLastLogin((int) $user['id'], $ip);

        return $this->redirect('/');
    }

    public function logout(Request $request, array $params): Response
    {
        Middleware::logout();
        $this->setFlash('info', __('auth.logged_out'));
        return $this->redirect('/login');
    }

    // =========================================================================
    // Forgot password
    // =========================================================================

    public function showForgotPassword(Request $request, array $params): Response
    {
        if (Middleware::isAuthenticated()) {
            return $this->redirect('/');
        }

        return $this->view('themes/uikit/auth/forgot-password', [
            'csrf'    => Middleware::csrfToken(),
            'success' => $this->getFlash('success'),
        ]);
    }

    public function forgotPassword(Request $request, array $params): Response
    {
        if (!Middleware::verifyCsrfToken($request)) {
            return $this->view('themes/uikit/auth/forgot-password', [
                'csrf'  => Middleware::csrfToken(),
                'error' => __('auth.csrf_invalid'),
            ]);
        }

        $email = trim((string) $request->post('email', ''));
        $user  = User::findByEmail($email);

        if ($user !== null && (bool) $user['is_active']) {
            $token     = bin2hex(random_bytes(32));
            $expiresAt = date('Y-m-d H:i:s', time() + 3600);

            User::saveResetToken((int) $user['id'], $token, $expiresAt);

            $appName = (string) Config::get('app.name', 'Socius');
            $appUrl  = rtrim((string) Config::get('app.url', ''), '/');
            $resetUrl = $appUrl . '/reset-password/' . $token;

            $subject = __('auth.email_reset_subject', ['app_name' => $appName]);
            $body    = __('auth.email_reset_body', [
                'url'      => $resetUrl,
                'app_name' => $appName,
            ]);

            $headers = 'From: ' . $appName . ' <noreply@' . parse_host($appUrl) . '>' . "\r\n"
                     . 'Content-Type: text/plain; charset=UTF-8' . "\r\n";

            @mail($email, $subject, $body, $headers);
        }

        // Always show success to avoid user enumeration
        Middleware::regenerateCsrfToken();
        $this->setFlash('success', __('auth.reset_link_sent'));
        return $this->redirect('/forgot-password');
    }

    // =========================================================================
    // Reset password
    // =========================================================================

    public function showResetPassword(Request $request, array $params): Response
    {
        $token = $params['token'] ?? '';
        $user  = User::findByResetToken($token);

        if ($user === null) {
            return $this->view('themes/uikit/auth/reset-password', [
                'csrf'        => Middleware::csrfToken(),
                'token'       => '',
                'tokenInvalid' => true,
            ]);
        }

        return $this->view('themes/uikit/auth/reset-password', [
            'csrf'        => Middleware::csrfToken(),
            'token'       => $token,
            'tokenInvalid' => false,
        ]);
    }

    public function resetPassword(Request $request, array $params): Response
    {
        $token = $params['token'] ?? '';

        if (!Middleware::verifyCsrfToken($request)) {
            return $this->view('themes/uikit/auth/reset-password', [
                'csrf'        => Middleware::csrfToken(),
                'token'       => $token,
                'tokenInvalid' => false,
                'error'       => __('auth.csrf_invalid'),
            ]);
        }

        $user = User::findByResetToken($token);

        if ($user === null) {
            return $this->view('themes/uikit/auth/reset-password', [
                'csrf'        => Middleware::csrfToken(),
                'token'       => '',
                'tokenInvalid' => true,
            ]);
        }

        $newPassword     = (string) $request->post('password', '');
        $confirmPassword = (string) $request->post('password_confirmation', '');

        if (strlen($newPassword) < 8) {
            return $this->view('themes/uikit/auth/reset-password', [
                'csrf'        => Middleware::csrfToken(),
                'token'       => $token,
                'tokenInvalid' => false,
                'error'       => __('auth.password_too_short'),
            ]);
        }

        if (!hash_equals($newPassword, $confirmPassword)) {
            return $this->view('themes/uikit/auth/reset-password', [
                'csrf'        => Middleware::csrfToken(),
                'token'       => $token,
                'tokenInvalid' => false,
                'error'       => __('auth.passwords_mismatch'),
            ]);
        }

        User::updatePassword((int) $user['id'], $newPassword);
        User::clearResetToken((int) $user['id']);
        Middleware::regenerateCsrfToken();

        $this->setFlash('success', __('auth.reset_success'));
        return $this->redirect('/login');
    }

    // =========================================================================
    // Rate limiting helpers (session-based)
    // =========================================================================

    private function isRateLimited(string $ip): bool
    {
        $key  = 'login_attempts_' . md5($ip);
        $data = $_SESSION[$key] ?? null;

        if ($data === null) {
            return false;
        }

        if ((int) $data['count'] < self::MAX_ATTEMPTS) {
            return false;
        }

        if (time() < (int) $data['until']) {
            return true;
        }

        // Lockout expired — clear it
        unset($_SESSION[$key]);
        return false;
    }

    private function incrementAttempts(string $ip): void
    {
        $key  = 'login_attempts_' . md5($ip);
        $data = $_SESSION[$key] ?? ['count' => 0, 'until' => 0];

        $data['count']++;

        if ((int) $data['count'] >= self::MAX_ATTEMPTS) {
            $data['until'] = time() + (self::LOCKOUT_MINUTES * 60);
        }

        $_SESSION[$key] = $data;
    }

    private function clearAttempts(string $ip): void
    {
        $key = 'login_attempts_' . md5($ip);
        unset($_SESSION[$key]);
    }

    // =========================================================================
    // Flash message helpers
    // =========================================================================

    private function setFlash(string $type, string $message): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $_SESSION['_flash'][$type] = $message;
    }

    private function getFlash(string $type): ?string
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $message = $_SESSION['_flash'][$type] ?? null;
        unset($_SESSION['_flash'][$type]);
        return $message;
    }
}

// ---------------------------------------------------------------------------
// Module-local helper — not exposed globally
// ---------------------------------------------------------------------------

function parse_host(string $url): string
{
    $host = parse_url($url, PHP_URL_HOST) ?? 'localhost';
    return (string) $host;
}
