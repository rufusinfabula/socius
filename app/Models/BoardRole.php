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
 * Board roles catalog and helper queries for the board (direttivo).
 */
class BoardRole extends BaseModel
{
    // =========================================================================
    // Read — roles catalog
    // =========================================================================

    /**
     * Return all roles, ordered by sort_order then label.
     *
     * @param  bool $onlyActive  When true, exclude inactive roles.
     * @return list<array<string, mixed>>
     */
    public static function findAll(bool $onlyActive = false): array
    {
        $where = $onlyActive ? 'WHERE is_active = 1' : '';
        return self::db()->fetchAll(
            "SELECT * FROM board_roles {$where} ORDER BY sort_order ASC, label ASC"
        );
    }

    /**
     * Find a role by primary key.
     *
     * @return array<string, mixed>|null
     */
    public static function findById(int $id): ?array
    {
        $row = self::db()->fetch(
            'SELECT * FROM board_roles WHERE id = ? LIMIT 1',
            [$id]
        );
        return $row ?: null;
    }

    // =========================================================================
    // Write — roles catalog
    // =========================================================================

    /**
     * Insert a new board role.
     *
     * @param  array<string, mixed> $data
     * @return int  New role ID
     */
    public static function create(array $data): int
    {
        return (int) self::db()->insert('board_roles', $data);
    }

    /**
     * Update a board role. Returns true when at least one row was changed.
     *
     * @param array<string, mixed> $data
     */
    public static function update(int $id, array $data): bool
    {
        return self::db()->update('board_roles', $data, ['id' => $id]) > 0;
    }

    // =========================================================================
    // Board composition queries
    // =========================================================================

    /**
     * Return the current board composition.
     *
     * A membership is "current" when:
     *   - resigned_on IS NULL
     *   - AND (expires_on IS NULL OR expires_on >= today)
     *
     * Rows are ordered by role sort_order, then member surname.
     *
     * @return list<array<string, mixed>>
     */
    public static function getCurrentBoard(): array
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

    /**
     * Return all board memberships (current and historical) for a member.
     *
     * Ordered newest first.
     *
     * @return list<array<string, mixed>>
     */
    public static function getMemberRoles(int $memberId): array
    {
        return self::db()->fetchAll(
            'SELECT bm.*, br.name AS role_name, br.label AS role_label,
                    br.is_board_member, br.can_sign
               FROM board_memberships bm
               JOIN board_roles br ON br.id = bm.role_id
              WHERE bm.member_id = ?
              ORDER BY bm.elected_on DESC',
            [$memberId]
        );
    }

    /**
     * Return true when the member holds at least one active board role today.
     */
    public static function isCurrentBoardMember(int $memberId): bool
    {
        $row = self::db()->fetch(
            'SELECT id FROM board_memberships
              WHERE member_id   = ?
                AND resigned_on IS NULL
                AND (expires_on IS NULL OR expires_on >= CURDATE())
              LIMIT 1',
            [$memberId]
        );
        return $row !== null;
    }
}
