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
 * Internal API — Member search
 *
 * Searches members by name, surname, or member number.
 * Used in all forms that require member selection.
 *
 * Parameters:
 *   q      (string) Search query — min 2 chars
 *   limit  (int)    Max results — default 10, max 50
 *   status (string) Filter by status — optional
 *
 * Response:
 * {
 *   "members": [
 *     {
 *       "id": 1,
 *       "member_number": "M00001",
 *       "membership_number": "C00001",
 *       "name": "Fabio",
 *       "surname": "Ranfi",
 *       "email": "fabio@mail.it",
 *       "status": "active",
 *       "status_label": "Attivo",
 *       "category_id": 1,
 *       "category_label": "Ordinario"
 *     }
 *   ],
 *   "total": 1,
 *   "query": "ranfi"
 * }
 */

declare(strict_types=1);

require_once __DIR__ . '/../_init.php';

requireAuth();

header('Content-Type: application/json');

$q      = trim((string) ($_GET['q'] ?? ''));
$limit  = min(50, max(1, (int) ($_GET['limit'] ?? 10)));
$status = trim((string) ($_GET['status'] ?? ''));

if (strlen($q) < 2) {
    echo json_encode(['error' => 'Query must be at least 2 characters.', 'members' => [], 'total' => 0, 'query' => $q]);
    exit;
}

try {
    $db   = \Socius\Core\Database::getInstance();
    $like = '%' . $q . '%';

    $rows = $db->fetchAll(
        'SELECT
           m.id,
           m.member_number,
           m.membership_number,
           m.name,
           m.surname,
           m.email,
           m.status,
           m.category_id,
           mc.label AS category_label
         FROM members m
         LEFT JOIN membership_categories mc ON mc.id = m.category_id
         WHERE (
           m.surname LIKE :q
           OR m.name LIKE :q
           OR CONCAT(m.surname, \' \', m.name) LIKE :q
           OR CONCAT(m.name, \' \', m.surname) LIKE :q
           OR m.membership_number LIKE :q
           OR CAST(m.member_number AS CHAR) LIKE :q
         )
         AND (:status = \'\' OR m.status = :status)
         AND m.status != \'deceased\'
         ORDER BY m.surname, m.name
         LIMIT :limit',
        [
            ':q'      => $like,
            ':status' => $status,
            ':limit'  => $limit,
        ]
    );

    $members = array_map(function (array $row) use ($db): array {
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
        ];
    }, $rows);

    echo json_encode([
        'members' => $members,
        'total'   => count($members),
        'query'   => $q,
    ]);

} catch (\Throwable $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Internal server error.', 'members' => [], 'total' => 0, 'query' => $q]);
}
