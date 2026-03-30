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
$events      = [];

try {
    $db     = Database::getInstance();
    $events = $db->fetchAll('SELECT * FROM events ORDER BY starts_at DESC LIMIT 100');
} catch (\Throwable) {}

theme('events', [
    'activeNav'    => 'events',
    'currentUser'  => $currentUser,
    'events'       => $events,
    'flashSuccess' => flash_get('success'),
    'flashError'   => flash_get('error'),
]);
