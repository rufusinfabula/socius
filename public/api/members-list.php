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
 * Internal API — Filtered member list
 *
 * Returns a paginated list of members with advanced filters.
 * Used in communications, assemblies, and renewal modules.
 *
 * Parameters:
 *   status      (string) Filter by member status
 *   category_id (int)    Filter by category
 *   board       (bool)   If true, only board members (is_board_member = 1)
 *   year        (int)    Filter by membership year (has a membership for this year)
 *   page        (int)    Page number — default 1
 *   per_page    (int)    Results per page — default 25, max 100
 *   q           (string) Optional search query (name, surname, email, number)
 *
 * Response:
 * {
 *   "members": [...],
 *   "pagination": {
 *     "total": 150,
 *     "page": 1,
 *     "per_page": 25,
 *     "pages": 6
 *   },
 *   "filters": {
 *     "status": "active",
 *     "category_id": null,
 *     "board": false
 *   }
 * }
 */

declare(strict_types=1);

require_once __DIR__ . '/../_init.php';

requireAuth();

header('Content-Type: application/json');

// Single status (backward compat) or multiple via statuses[]
$status     = trim((string) ($_GET['status'] ?? ''));
$statusesRaw = isset($_GET['statuses']) ? (array) $_GET['statuses'] : [];
$statuses   = array_values(array_filter(array_map('trim', $statusesRaw)));
$categoryId = (int) ($_GET['category_id'] ?? 0);
$board      = filter_var($_GET['board'] ?? false, FILTER_VALIDATE_BOOLEAN);
$year       = (int) ($_GET['year'] ?? 0);
$page       = max(1, (int) ($_GET['page'] ?? 1));
$perPage    = min(500, max(1, (int) ($_GET['per_page'] ?? 25)));
$q          = trim((string) ($_GET['q'] ?? ''));

try {
    $db     = \Socius\Core\Database::getInstance();
    $where  = [];
    $params = [];

    if ($statuses) {
        $ph      = implode(',', array_fill(0, count($statuses), '?'));
        $where[] = "m.status IN ($ph)";
        $params  = array_merge($params, $statuses);
    } elseif ($status !== '') {
        $where[]  = 'm.status = ?';
        $params[] = $status;
    }

    if ($categoryId > 0) {
        // category_id was dropped from members in migration 021 — filter via memberships
        $where[]  = 'EXISTS (SELECT 1 FROM memberships ms2 WHERE ms2.member_id = m.id AND ms2.category_id = ?)';
        $params[] = $categoryId;
    }

    if ($board) {
        $where[] = 'm.is_board_member = 1';
    }

    if ($year > 0) {
        $where[]  = 'EXISTS (SELECT 1 FROM memberships ms WHERE ms.member_id = m.id AND ms.year = ?)';
        $params[] = $year;
    }

    if ($q !== '') {
        $like     = '%' . $q . '%';
        $where[]  = '(m.name LIKE ? OR m.surname LIKE ? OR m.email LIKE ? OR m.membership_number LIKE ?)';
        $params[] = $like;
        $params[] = $like;
        $params[] = $like;
        $params[] = $like;
    }

    $whereClause = $where ? ('WHERE ' . implode(' AND ', $where)) : '';

    $total = (int) ($db->fetch(
        "SELECT COUNT(*) AS cnt FROM members m {$whereClause}",
        $params
    )['cnt'] ?? 0);

    $offset = ($page - 1) * $perPage;
    $rows   = $db->fetchAll(
        "SELECT m.id, m.member_number, m.membership_number,
                m.name, m.surname, m.email, m.status,
                m.joined_on,
                (SELECT mc.label FROM memberships ms3
                   JOIN membership_categories mc ON mc.id = ms3.category_id
                  WHERE ms3.member_id = m.id
                  ORDER BY ms3.year DESC LIMIT 1) AS category_label
           FROM members m
           {$whereClause}
           ORDER BY m.surname ASC, m.name ASC
           LIMIT ? OFFSET ?",
        [...$params, $perPage, $offset]
    );

    $members = array_map(function (array $row): array {
        $status = (string) $row['status'];
        return [
            'id'               => (int) $row['id'],
            'member_number'    => format_member_number((int) $row['member_number']),
            'membership_number'=> (string) ($row['membership_number'] ?? ''),
            'name'             => (string) $row['name'],
            'surname'          => (string) $row['surname'],
            'email'            => (string) ($row['email'] ?? ''),
            'status'           => $status,
            'status_label'     => (string) __('members.status_' . $status),
            'category_label'   => (string) ($row['category_label'] ?? ''),
            'joined_on'        => $row['joined_on'] ? format_date((string) $row['joined_on']) : '',
        ];
    }, $rows);

    echo json_encode([
        'members'    => $members,
        'pagination' => [
            'total'    => $total,
            'page'     => $page,
            'per_page' => $perPage,
            'pages'    => (int) ceil($total / $perPage),
        ],
        'filters'    => [
            'status'      => $statuses ?: ($status ?: null),
            'category_id' => $categoryId ?: null,
            'board'       => $board,
            'year'        => $year ?: null,
            'q'           => $q ?: null,
        ],
    ]);

} catch (\Throwable $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Internal server error.']);
}
