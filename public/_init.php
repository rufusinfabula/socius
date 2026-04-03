<?php
/*
 * Socius - Open Source Association Management System
 * Copyright (C) 2026 Fabio Ranfi
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 */

declare(strict_types=1);

define('BASE_PATH', dirname(__DIR__));
define('SOCIUS_START', microtime(true));

require_once BASE_PATH . '/vendor/autoload.php';

use Socius\Core\Config;
use Socius\Core\Database;
use Socius\Core\Lang;

// Load .env
Config::loadEnv(BASE_PATH . '/.env');

// Runtime settings
$_debug = (bool) Config::get('app.debug', false);
error_reporting($_debug ? E_ALL : E_ALL & ~E_DEPRECATED & ~E_STRICT);
ini_set('display_errors', $_debug ? '1' : '0');
ini_set('log_errors', '1');
date_default_timezone_set((string) Config::get('app.timezone', 'Europe/Rome'));

// Session
session_set_cookie_params([
    'lifetime' => 0,
    'path'     => '/',
    'domain'   => '',
    'secure'   => !$_debug,
    'httponly' => true,
    'samesite' => 'Strict',
]);
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Locale — priority: session → DB ui.locale → .env app.locale → 'it'
(static function (): void {
    if (!empty($_SESSION['locale'])) {
        Lang::setLocale((string) $_SESSION['locale']);
        return;
    }
    try {
        $row = Database::getInstance()->fetch("SELECT `value` FROM settings WHERE `key` = 'ui.locale' LIMIT 1");
        if ($row && !empty($row['value'])) {
            Lang::setLocale((string) $row['value']);
            return;
        }
    } catch (\Throwable) {}
    Lang::setLocale((string) \Socius\Core\Config::get('app.locale', 'it'));
})();

// -------------------------------------------------------------------------
// Helpers
// -------------------------------------------------------------------------

/**
 * Load and render a theme template.
 * Falls back to uikit if the active theme template doesn't exist.
 */
function theme(string $template, array $vars = []): void
{
    $themeName = 'uikit';
    try {
        $db  = Database::getInstance();
        $row = $db->fetch("SELECT `value` FROM settings WHERE `key` = 'ui.theme' LIMIT 1");
        if ($row && !empty($row['value'])) {
            $themeName = (string) $row['value'];
        }
    } catch (\Throwable) {
        // DB not available — use default theme
    }

    $file = __DIR__ . "/themes/{$themeName}/{$template}.php";
    if (!file_exists($file)) {
        $file = __DIR__ . "/themes/uikit/{$template}.php";
    }

    extract($vars, EXTR_SKIP);
    require $file;
}

/**
 * HTTP redirect and exit.
 */
function redirect(string $url): never
{
    header('Location: ' . $url);
    exit;
}

/**
 * Require authenticated session or redirect to login.
 */
function requireAuth(): void
{
    if (empty($_SESSION['auth_user_id'])) {
        redirect('login.php');
    }
}

/**
 * Require super_admin role (role_id = 1) or redirect to dashboard.
 */
function requireSuperAdmin(): void
{
    requireAuth();
    $userId = (int) $_SESSION['auth_user_id'];
    try {
        $db   = Database::getInstance();
        $user = $db->fetch('SELECT role_id FROM users WHERE id = ? LIMIT 1', [$userId]);
        if (!$user || (int) $user['role_id'] !== 1) {
            redirect('dashboard.php');
        }
    } catch (\Throwable) {
        redirect('dashboard.php');
    }
}

/**
 * Require staff role (role_id <= 3) or redirect to dashboard.
 */
function requireStaff(): void
{
    requireAuth();
    $userId = (int) $_SESSION['auth_user_id'];
    try {
        $db   = Database::getInstance();
        $user = $db->fetch('SELECT role_id FROM users WHERE id = ? LIMIT 1', [$userId]);
        if (!$user || (int) $user['role_id'] > 3) {
            redirect('dashboard.php');
        }
    } catch (\Throwable) {
        redirect('dashboard.php');
    }
}

/**
 * Get CSRF token from session (generate if absent).
 */
function csrf_token(): string
{
    if (empty($_SESSION['_csrf_token'])) {
        $_SESSION['_csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['_csrf_token'];
}

/**
 * Output a hidden CSRF input field.
 */
function csrf_field(): string
{
    return '<input type="hidden" name="_csrf_token" value="'
        . htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8') . '">';
}

/**
 * Verify CSRF token from POST data.
 */
function csrf_verify(): bool
{
    $token  = $_SESSION['_csrf_token'] ?? '';
    $posted = $_POST['_csrf_token'] ?? '';
    return $token !== '' && hash_equals($token, $posted);
}

/**
 * Rotate the CSRF token.
 */
function csrf_regenerate(): void
{
    $_SESSION['_csrf_token'] = bin2hex(random_bytes(32));
}

/**
 * Store a flash message.
 */
function flash_set(string $type, string $message): void
{
    $_SESSION['_flash'][$type] = $message;
}

/**
 * Read and clear a flash message.
 */
function flash_get(string $type): ?string
{
    $msg = $_SESSION['_flash'][$type] ?? null;
    unset($_SESSION['_flash'][$type]);
    return $msg;
}

/**
 * Return the authenticated user ID (or 0 if not logged in).
 */
function current_user_id(): int
{
    return (int) ($_SESSION['auth_user_id'] ?? 0);
}

/**
 * Load the authenticated user row from DB.
 *
 * @return array<string,mixed>|null
 */
function current_user(): ?array
{
    $id = current_user_id();
    if ($id === 0) {
        return null;
    }
    static $cache = [];
    if (!isset($cache[$id])) {
        try {
            $db         = Database::getInstance();
            $user       = $db->fetch('SELECT * FROM users WHERE id = ? LIMIT 1', [$id]);
            $cache[$id] = $user ?: null;
        } catch (\Throwable) {
            return null;
        }
    }
    return $cache[$id];
}

/**
 * Get the client IP address.
 */
function client_ip(): string
{
    return (string) ($_SERVER['HTTP_X_FORWARDED_FOR']
        ?? $_SERVER['HTTP_CLIENT_IP']
        ?? $_SERVER['REMOTE_ADDR']
        ?? '');
}
