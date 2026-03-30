<?php
/*
 * Socius - Open Source Association Management System
 * Copyright (C) 2026 Fabio Ranfi
 */

declare(strict_types=1);

require_once __DIR__ . '/_init.php';

requireAuth();

use Socius\Core\Database;

$currentUser = current_user();
$stats       = [];

try {
    $db   = Database::getInstance();
    $rows = $db->fetchAll('SELECT status, COUNT(*) AS cnt FROM members GROUP BY status');
    foreach ($rows as $row) {
        $stats[(string) $row['status']] = (int) $row['cnt'];
    }
} catch (\Throwable) {}

theme('dashboard', [
    'activeNav'    => 'dashboard',
    'currentUser'  => $currentUser,
    'stats'        => $stats,
    'flashSuccess' => flash_get('success'),
    'flashError'   => flash_get('error'),
]);
