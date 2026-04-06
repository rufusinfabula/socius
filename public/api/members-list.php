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

$status     = trim((string) ($_GET['status'] ?? ''));
$categoryId = (int) ($_GET['category_id'] ?? 0);
$board      = filter_var($_GET['board'] ?? false, FILTER_VALIDATE_BOOLEAN);
$year       = (int) ($_GET['year'] ?? 0);
$page       = max(1, (int) ($_GET['page'] ?? 1));
$perPage    = min(100, max(1, (int) ($_GET['per_page'] ?? 25)));
$q          = trim((string) ($_GET['q'] ?? ''));

try {
    $db     = \Socius\Core\Database::getInstance();
    $where  = [];
    $params = [];

    if ($status !== '') {
        $where[]  = 'm.status = ?';
        $params[] = $status;
    }

    if ($categoryId > 0) {
        $where[]  = 'm.category_id = ?';
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
                m.category_id, mc.label AS category_label,
                m.joined_on
           FROM members m
           LEFT JOIN membership_categories mc ON mc.id = m.category_id
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
            'email'            => (string) $row['email'],
            'status'           => $status,
            'status_label'     => (string) __('members.status_' . $status),
            'category_id'      => $row['category_id'] ? (int) $row['category_id'] : null,
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
            'status'      => $status ?: null,
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
