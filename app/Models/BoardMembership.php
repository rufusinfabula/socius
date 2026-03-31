<?php
/*
 * Socius - Open Source Association Management System
 * Copyright (C) 2026 Fabio Ranfi
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 */

declare(strict_types=1);

namespace Socius\Models;

/**
 * Board membership records (appartenenze al direttivo).
 *
 * Each row links a member to a board_role for a time period.
 * Use BoardRole::getCurrentBoard() for the full board view.
 */
class BoardMembership extends BaseModel
{
    // =========================================================================
    // Write
    // =========================================================================

    /**
     * Insert a new board membership record.
     *
     * @param  array<string, mixed> $data
     * @return int  New record ID
     */
    public static function create(array $data): int
    {
        return (int) self::db()->insert('board_memberships', $data);
    }

    /**
     * Update a board membership record. Returns true when a row was changed.
     *
     * @param array<string, mixed> $data
     */
    public static function update(int $id, array $data): bool
    {
        return self::db()->update('board_memberships', $data, ['id' => $id]) > 0;
    }

    // =========================================================================
    // Read
    // =========================================================================

    /**
     * Return all memberships for a member, newest first.
     *
     * @return list<array<string, mixed>>
     */
    public static function findByMember(int $memberId): array
    {
        return self::db()->fetchAll(
            'SELECT bm.*, br.name AS role_name, br.label AS role_label,
                    br.is_board_member, br.can_sign, br.sort_order
               FROM board_memberships bm
               JOIN board_roles br ON br.id = bm.role_id
              WHERE bm.member_id = ?
              ORDER BY bm.elected_on DESC',
            [$memberId]
        );
    }

    /**
     * Return only currently active memberships.
     *
     * Active = resigned_on IS NULL AND (expires_on IS NULL OR expires_on >= today)
     *
     * @return list<array<string, mixed>>
     */
    public static function findCurrent(): array
    {
        return self::db()->fetchAll(
            'SELECT bm.*, br.name AS role_name, br.label AS role_label,
                    br.is_board_member, br.can_sign, br.sort_order,
                    m.name AS member_name, m.surname AS member_surname,
                    m.membership_number, m.member_number
               FROM board_memberships bm
               JOIN board_roles br ON br.id = bm.role_id
               JOIN members     m  ON m.id  = bm.member_id
              WHERE bm.resigned_on IS NULL
                AND (bm.expires_on IS NULL OR bm.expires_on >= CURDATE())
              ORDER BY br.sort_order ASC, m.surname ASC, m.name ASC'
        );
    }
}
