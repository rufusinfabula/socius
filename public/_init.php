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

/**
 * Format a date string according to the configured ui.date_format setting.
 * Returns '—' for empty/null/zero dates.
 */
function format_date(string $date, bool $withTime = false): string
{
    if ($date === '' || $date === '0000-00-00' || $date === '0000-00-00 00:00:00') {
        return '—';
    }
    try {
        $dt     = new \DateTime($date);
        $format = \Socius\Models\Setting::get('ui.date_format', 'd/m/Y');
        if ($withTime) {
            $format .= ' H:i';
        }
        return $dt->format($format);
    } catch (\Throwable) {
        return $date;
    }
}

/**
 * Format a date as Y-m-d for use in input type="date" value attributes.
 * Returns '' for empty/null/zero dates.
 */
function format_date_iso(string $date): string
{
    if ($date === '' || $date === '0000-00-00' || $date === '0000-00-00 00:00:00') {
        return '';
    }
    try {
        return (new \DateTime($date))->format('Y-m-d');
    } catch (\Throwable) {
        return '';
    }
}

// =============================================================================
// Member number and card number helpers
//
// MEMBER NUMBER vs CARD NUMBER
//
// Member number (members.member_number):
//   - Permanent sequential integer assigned at first registration
//   - Never changes, even if the member lapses and rejoins years later
//   - Format: M + 5 digits → M00001
//   - Prefix configurable in settings: members.number_prefix (default 'M')
//   - Displayed with CSS class: badge-member-number (blue)
//
// Card number (memberships.membership_number  ←  SOURCE OF TRUTH):
//   - Alphanumeric code assigned when a membership record is created
//   - Stable as long as the member renews regularly
//   - Released (set to NULL) if member reaches status 'lapsed'
//   - Format: C + 5 digits → C00001
//   - Prefix configurable in settings: members.card_prefix (default 'C')
//   - Displayed with CSS class: badge-card-number (green)
//
// members.membership_number is a denormalized copy of the current active card
// number. It is updated automatically by the system when a membership is
// created or updated. It must NEVER be modified directly — only through
// Membership model operations.
// =============================================================================

/**
 * Format a member number for display.
 *
 * Takes the raw integer member_number from the database
 * and returns the formatted display string.
 *
 * Examples:
 *   format_member_number(1)    → 'M00001'
 *   format_member_number(42)   → 'M00042'
 *   format_member_number(null) → '—'
 *
 * @param int|null $number Raw member_number from members table
 * @return string Formatted member number with prefix
 */
function format_member_number(int|null $number): string
{
    if ($number === null || $number <= 0) {
        return '—';
    }
    $prefix = (string) \Socius\Models\Setting::get('members.number_prefix', 'M');
    $digits = (int) \Socius\Models\Setting::get('members.number_digits', 5);
    return $prefix . str_pad((string) $number, $digits, '0', STR_PAD_LEFT);
}

/**
 * Format a card number for display.
 *
 * The card number is already stored in the correct format (e.g. C00001)
 * in both members.membership_number and memberships.membership_number.
 *
 * This function ensures consistent output — returns '—' for NULL or empty.
 *
 * Examples:
 *   format_card_number('C00001') → 'C00001'
 *   format_card_number(null)     → '—'
 *   format_card_number('')       → '—'
 *
 * @param string|null $number Card number from memberships or members table
 * @return string Formatted card number or '—'
 */
function format_card_number(string|null $number): string
{
    if ($number === null || $number === '') {
        return '—';
    }
    return $number;
}

/**
 * Generate the next available member number.
 *
 * Logic:
 * 1. If no members exist yet, use members.number_start from settings
 *    (configured during installation or first setup).
 * 2. Otherwise, always use MAX(member_number) + 1 from the database.
 *
 * This approach is fully automatic — no manual counter needed.
 * The only configuration is members.number_start which is used
 * exclusively for the very first member in a fresh installation.
 *
 * Note: deleted members leave gaps in the sequence. This is intentional —
 * member numbers are permanent identifiers and are never reused
 * automatically. Manual reassignment is possible via the danger zone
 * in member-edit.php (super_admin only).
 *
 * @return int Raw integer to store in members.member_number
 */
function next_member_number(): int
{
    $db  = \Socius\Core\Database::getInstance();
    $row = $db->fetch('SELECT COALESCE(MAX(member_number), 0) AS max_num FROM members');
    $dbMax = (int) ($row['max_num'] ?? 0);

    if ($dbMax === 0) {
        // No members yet — use the configured starting number
        $start = (int) \Socius\Models\Setting::get('members.number_start', 1);
        return max(1, $start);
    }

    // Always MAX + 1 — fully automatic, no counter to maintain
    return $dbMax + 1;
}

/**
 * Generate the next available card number string.
 *
 * Finds the highest numeric value currently in use across:
 *   - members.membership_number (denormalized copy)
 *   - memberships.membership_number (source of truth)
 *   - reserved_member_numbers (numbers that must not be reused)
 *
 * Returns the next formatted card number. Does NOT persist anything —
 * card numbers are derived from the current DB maximum each time.
 *
 * @return string Next available card number (e.g. 'C00042')
 */
function next_card_number(): string
{
    $db     = \Socius\Core\Database::getInstance();
    $prefix = (string) \Socius\Models\Setting::get('members.card_prefix', 'C');
    $digits = (int) \Socius\Models\Setting::get('members.number_digits', 5);
    $pLen   = strlen($prefix);
    $pos    = $pLen + 1; // SUBSTRING start position (1-based)

    $rgx = '^[A-Z][0-9]+$';

    $maxMembers = (int) ($db->fetch(
        "SELECT COALESCE(MAX(CAST(SUBSTRING(membership_number, ?) AS UNSIGNED)), 0) AS m
           FROM members
          WHERE membership_number IS NOT NULL
            AND membership_number REGEXP ?",
        [$pos, $rgx]
    )['m'] ?? 0);

    $maxMemberships = (int) ($db->fetch(
        "SELECT COALESCE(MAX(CAST(SUBSTRING(membership_number, ?) AS UNSIGNED)), 0) AS m
           FROM memberships
          WHERE membership_number IS NOT NULL
            AND membership_number REGEXP ?",
        [$pos, $rgx]
    )['m'] ?? 0);

    $maxReserved = (int) ($db->fetch(
        "SELECT COALESCE(MAX(CAST(SUBSTRING(membership_number, ?) AS UNSIGNED)), 0) AS m
           FROM reserved_member_numbers
          WHERE membership_number REGEXP ?",
        [$pos, $rgx]
    )['m'] ?? 0);

    $next = max($maxMembers, $maxMemberships, $maxReserved) + 1;
    return $prefix . str_pad((string) $next, $digits, '0', STR_PAD_LEFT);
}
