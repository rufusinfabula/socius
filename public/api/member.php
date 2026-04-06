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
 * Internal API — Single member data
 *
 * Returns complete data for a single member including
 * current membership, board role, and recent history.
 * Used to pre-fill forms after member selection.
 *
 * Parameters:
 *   id (int) Member ID
 *
 * Response:
 * {
 *   "member": {
 *     "id": 1,
 *     "member_number": "M00001",
 *     "membership_number": "C00001",
 *     "name": "Fabio",
 *     "surname": "Ranfi",
 *     "email": "fabio@mail.it",
 *     "phone1": "...",
 *     "status": "active",
 *     "status_label": "Attivo",
 *     "category_id": 1,
 *     "category_label": "Ordinario",
 *     "joined_on": "30/03/2026",
 *     "current_board_role": "Presidente",
 *     "current_membership": {
 *       "id": 1,
 *       "year": 2026,
 *       "membership_number": "C00001",
 *       "fee": "50.00",
 *       "status": "paid",
 *       "status_label": "Pagata",
 *       "paid_on": "30/03/2026"
 *     }
 *   }
 * }
 *
 * Sensitive fields (fiscal_code, birth_date, address, notes) are only
 * included when the requester has role_id <= 2 (super_admin or segreteria).
 */

declare(strict_types=1);

require_once __DIR__ . '/../_init.php';

requireAuth();

header('Content-Type: application/json');

$id = (int) ($_GET['id'] ?? 0);

if ($id <= 0) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid member ID.']);
    exit;
}

try {
    $db          = \Socius\Core\Database::getInstance();
    $currentUser = current_user();
    $isAdmin     = (int) ($currentUser['role_id'] ?? 4) <= 2;

    $member = $db->fetch(
        'SELECT m.*, mc.label AS category_label
           FROM members m
           LEFT JOIN membership_categories mc ON mc.id = m.category_id
          WHERE m.id = ?
          LIMIT 1',
        [$id]
    );

    if (!$member) {
        http_response_code(404);
        echo json_encode(['error' => 'Member not found.']);
        exit;
    }

    // Current membership (this year)
    $currentYear       = (int) date('Y');
    $currentMembership = $db->fetch(
        'SELECT ms.id, ms.year, ms.membership_number, ms.fee, ms.status, ms.paid_on
           FROM memberships ms
          WHERE ms.member_id = ? AND ms.year = ?
          LIMIT 1',
        [$id, $currentYear]
    );

    // Current board role
    $boardRole = $db->fetch(
        'SELECT br.title
           FROM board_roles br
          WHERE br.member_id = ?
            AND br.is_active = 1
          LIMIT 1',
        [$id]
    );

    $status     = (string) $member['status'];
    $memberData = [
        'id'                => (int) $member['id'],
        'member_number'     => format_member_number((int) $member['member_number']),
        'membership_number' => (string) ($member['membership_number'] ?? ''),
        'name'              => (string) $member['name'],
        'surname'           => (string) $member['surname'],
        'email'             => (string) $member['email'],
        'phone1'            => (string) ($member['phone1'] ?? ''),
        'status'            => $status,
        'status_label'      => (string) __('members.status_' . $status),
        'category_id'       => $member['category_id'] ? (int) $member['category_id'] : null,
        'category_label'    => (string) ($member['category_label'] ?? ''),
        'joined_on'         => $member['joined_on'] ? format_date((string) $member['joined_on']) : '',
        'current_board_role'=> $boardRole ? (string) ($boardRole['title'] ?? '') : null,
        'current_membership'=> null,
    ];

    if ($currentMembership) {
        $msStatus = (string) $currentMembership['status'];
        $memberData['current_membership'] = [
            'id'               => (int) $currentMembership['id'],
            'year'             => (int) $currentMembership['year'],
            'membership_number'=> (string) ($currentMembership['membership_number'] ?? ''),
            'fee'              => number_format((float) $currentMembership['fee'], 2, '.', ''),
            'status'           => $msStatus,
            'status_label'     => (string) __('memberships.status_' . $msStatus),
            'paid_on'          => $currentMembership['paid_on']
                                    ? format_date((string) $currentMembership['paid_on'])
                                    : '',
        ];
    }

    // Sensitive fields — only for admin/segreteria
    if ($isAdmin) {
        $memberData['fiscal_code'] = (string) ($member['fiscal_code'] ?? '');
        $memberData['birth_date']  = $member['birth_date'] ? format_date((string) $member['birth_date']) : '';
        $memberData['notes']       = (string) ($member['notes'] ?? '');
        $memberData['address']     = (string) ($member['address'] ?? '');
        $memberData['city']        = (string) ($member['city'] ?? '');
    }

    echo json_encode(['member' => $memberData]);

} catch (\Throwable $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Internal server error.']);
}
