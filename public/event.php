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
$id          = (int) ($_GET['id'] ?? 0);

if ($id <= 0) {
    redirect('events.php');
}

$event = null;
try {
    $db    = Database::getInstance();
    $event = $db->fetch('SELECT * FROM events WHERE id = ? LIMIT 1', [$id]);
} catch (\Throwable) {}

if (!$event) {
    flash_set('error', 'Evento non trovato.');
    redirect('events.php');
}

theme('event', [
    'activeNav'   => 'events',
    'currentUser' => $currentUser,
    'event'       => $event,
]);
