<?php
/*
 * Socius - Open Source Association Management System
 * Copyright (C) 2026 Fabio Ranfi
 */

declare(strict_types=1);

require_once __DIR__ . '/_init.php';

requireAuth();

use Socius\Core\Database;
use Socius\Models\Membership;

$currentUser = current_user();
$currentYear = (int) date('Y');

$filters = [
    'year'        => (int) ($_GET['year'] ?? $currentYear),
    'status'      => trim((string) ($_GET['status'] ?? '')),
    'category_id' => (int) ($_GET['category_id'] ?? 0),
];
$page = max(1, (int) ($_GET['page'] ?? 1));

$memberships = ['items' => [], 'total' => 0, 'page' => 1, 'per_page' => 25, 'pages' => 0];
$years       = [];
$categories  = [];

try {
    $memberships = Membership::findAll($filters, $page);
} catch (\Throwable) {}

try {
    $years = Membership::getYearsWithMemberships();
    // Always include current and next year even if no records yet
    foreach ([$currentYear, $currentYear + 1] as $y) {
        if (!in_array($y, $years, true)) {
            $years[] = $y;
        }
    }
    rsort($years);
} catch (\Throwable) {}

try {
    $db         = Database::getInstance();
    $categories = $db->fetchAll(
        'SELECT id, label FROM membership_categories WHERE is_active = 1 ORDER BY sort_order ASC, label ASC'
    );
} catch (\Throwable) {}

theme('memberships', [
    'activeNav'    => 'memberships',
    'currentUser'  => $currentUser,
    'memberships'  => $memberships,
    'years'        => $years,
    'categories'   => $categories,
    'filters'      => $filters,
    'currentYear'  => $currentYear,
    'flashSuccess' => flash_get('success'),
    'flashError'   => flash_get('error'),
]);
