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

/**
 * Internal API — Member statistics
 *
 * Returns aggregate statistics for the dashboard.
 * Results are cached for 5 minutes using a simple
 * file-based cache in storage/cache/.
 *
 * Parameters:
 *   year (int) Social year — default current year
 *
 * Response:
 * {
 *   "year": 2026,
 *   "by_status": {
 *     "active": 45,
 *     "in_renewal": 12,
 *     "not_renewed": 3,
 *     "lapsed": 8,
 *     "suspended": 1,
 *     "resigned": 2,
 *     "deceased": 1
 *   },
 *   "by_category": [
 *     {"label": "Ordinario", "count": 38},
 *     {"label": "Onorario", "count": 7}
 *   ],
 *   "memberships_this_year": 42,
 *   "memberships_paid": 38,
 *   "memberships_pending": 4,
 *   "new_members_this_year": 8,
 *   "total_members_ever": 72,
 *   "generated_at": "06/04/2026 12:30"
 * }
 */

declare(strict_types=1);

require_once __DIR__ . '/../_init.php';

requireAuth();

header('Content-Type: application/json');

$year      = (int) ($_GET['year'] ?? date('Y'));
$cacheDir  = BASE_PATH . '/storage/cache';
$cacheFile = $cacheDir . '/member-stats-' . $year . '.json';
$cacheTtl  = 300; // 5 minutes

// Serve from cache if fresh
if (file_exists($cacheFile) && (time() - filemtime($cacheFile)) < $cacheTtl) {
    header('X-Cache: HIT');
    readfile($cacheFile);
    exit;
}

header('X-Cache: MISS');

try {
    $db = \Socius\Core\Database::getInstance();

    // Members by status
    $byStatusRows = $db->fetchAll(
        'SELECT status, COUNT(*) AS cnt FROM members GROUP BY status'
    );
    $byStatus = [];
    foreach ($byStatusRows as $row) {
        $byStatus[(string) $row['status']] = (int) $row['cnt'];
    }

    // Members by category
    $byCategoryRows = $db->fetchAll(
        'SELECT mc.label, COUNT(m.id) AS cnt
           FROM members m
           LEFT JOIN membership_categories mc ON mc.id = m.category_id
          WHERE m.status NOT IN (\'resigned\', \'deceased\')
          GROUP BY m.category_id, mc.label
          ORDER BY cnt DESC'
    );
    $byCategory = array_map(fn($r) => [
        'label' => (string) ($r['label'] ?? 'Senza categoria'),
        'count' => (int) $r['cnt'],
    ], $byCategoryRows);

    // Memberships this year
    $membershipsThisYear = (int) ($db->fetch(
        'SELECT COUNT(*) AS cnt FROM memberships WHERE year = ?',
        [$year]
    )['cnt'] ?? 0);

    $membershipsPaid = (int) ($db->fetch(
        'SELECT COUNT(*) AS cnt FROM memberships WHERE year = ? AND status = \'paid\'',
        [$year]
    )['cnt'] ?? 0);

    $membershipsPending = (int) ($db->fetch(
        'SELECT COUNT(*) AS cnt FROM memberships WHERE year = ? AND status = \'pending\'',
        [$year]
    )['cnt'] ?? 0);

    // New members registered this calendar year
    $yearStart       = $year . '-01-01';
    $yearEnd         = $year . '-12-31';
    $newMembersThisYear = (int) ($db->fetch(
        'SELECT COUNT(*) AS cnt FROM members WHERE joined_on BETWEEN ? AND ?',
        [$yearStart, $yearEnd]
    )['cnt'] ?? 0);

    // Total members ever (excluding emergency-deleted, which are gone)
    $totalMembersEver = (int) ($db->fetch(
        'SELECT COUNT(*) AS cnt FROM members'
    )['cnt'] ?? 0);

    $data = [
        'year'                  => $year,
        'by_status'             => $byStatus,
        'by_category'           => $byCategory,
        'memberships_this_year' => $membershipsThisYear,
        'memberships_paid'      => $membershipsPaid,
        'memberships_pending'   => $membershipsPending,
        'new_members_this_year' => $newMembersThisYear,
        'total_members_ever'    => $totalMembersEver,
        'generated_at'          => date('d/m/Y H:i'),
    ];

    $json = (string) json_encode($data);

    // Write to cache (best-effort — ignore errors)
    if (is_dir($cacheDir) && is_writable($cacheDir)) {
        file_put_contents($cacheFile, $json, LOCK_EX);
    }

    echo $json;

} catch (\Throwable $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Internal server error.']);
}
