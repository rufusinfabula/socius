<?php
/*
 * Socius - Open Source Association Management System
 * Copyright (C) 2026 Fabio Ranfi
 */

declare(strict_types=1);

require_once __DIR__ . '/_init.php';

requireAuth();

use Socius\Core\Database;
use Socius\Models\Member;

$currentUser = current_user();

$filters = [
    'search'   => trim((string) ($_GET['search'] ?? '')),
    'status'   => trim((string) ($_GET['status'] ?? '')),
    'category' => (int) ($_GET['category'] ?? 0),
];
$page = max(1, (int) ($_GET['page'] ?? 1));

$members    = ['items' => [], 'total' => 0, 'page' => 1, 'per_page' => 25, 'pages' => 0];
$stats      = [];
$categories = [];

try {
    $members = Member::findAll($filters, $page);
} catch (\Throwable) {}

try {
    $stats = Member::getStatsByStatus();
} catch (\Throwable) {}

try {
    $db         = Database::getInstance();
    $categories = $db->fetchAll('SELECT id, label FROM membership_categories ORDER BY sort_order ASC, label ASC');
} catch (\Throwable) {}

theme('members', [
    'activeNav'    => 'members',
    'currentUser'  => $currentUser,
    'members'      => $members,
    'stats'        => $stats,
    'filters'      => $filters,
    'categories'   => $categories,
    'flashSuccess' => flash_get('success'),
    'flashError'   => flash_get('error'),
]);
