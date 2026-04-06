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

declare(strict_types=1);

namespace Socius\Models;

/**
 * Annual membership (tessera) linked to a member.
 */
class Membership extends BaseModel
{
    // =========================================================================
    // Read
    // =========================================================================

    /**
     * Return a paginated list of memberships with optional filters.
     *
     * Supported filter keys: year, status, category_id, member_id
     *
     * @return array{items: list<array<string,mixed>>, total: int, page: int, per_page: int, pages: int}
     */
    public static function findAll(array $filters = [], int $page = 1, int $perPage = 25): array
    {
        $where  = [];
        $params = [];

        if (!empty($filters['year'])) {
            $where[]  = 'ms.year = ?';
            $params[] = (int) $filters['year'];
        }

        if (!empty($filters['status'])) {
            $where[]  = 'ms.status = ?';
            $params[] = $filters['status'];
        }

        if (!empty($filters['category_id'])) {
            $where[]  = 'ms.category_id = ?';
            $params[] = (int) $filters['category_id'];
        }

        if (!empty($filters['member_id'])) {
            $where[]  = 'ms.member_id = ?';
            $params[] = (int) $filters['member_id'];
        }

        $whereClause = $where ? ('WHERE ' . implode(' AND ', $where)) : '';

        $total = (int) (self::db()->fetch(
            "SELECT COUNT(*) AS cnt
               FROM memberships ms
               JOIN members m ON m.id = ms.member_id
               {$whereClause}",
            $params
        )['cnt'] ?? 0);

        $offset = ($page - 1) * $perPage;
        $items  = self::db()->fetchAll(
            "SELECT ms.*, mc.label AS category_name,
                    m.name AS member_name, m.surname AS member_surname,
                    m.email AS member_email, m.status AS member_status,
                    m.membership_number, m.member_number
               FROM memberships ms
               JOIN members m ON m.id = ms.member_id
               LEFT JOIN membership_categories mc ON mc.id = ms.category_id
               {$whereClause}
               ORDER BY m.surname ASC, m.name ASC, ms.year DESC
               LIMIT ? OFFSET ?",
            [...$params, $perPage, $offset]
        );

        return [
            'items'    => $items,
            'total'    => $total,
            'page'     => $page,
            'per_page' => $perPage,
            'pages'    => (int) ceil($total / $perPage),
        ];
    }

    /**
     * Find a membership by primary key, with member and category data.
     *
     * @return array<string, mixed>|null
     */
    public static function findById(int $id): ?array
    {
        $row = self::db()->fetch(
            'SELECT ms.*, mc.label AS category_name, mc.is_exempt_from_renewal,
                    m.name AS member_name, m.surname AS member_surname,
                    m.email AS member_email, m.status AS member_status,
                    m.membership_number, m.member_number
               FROM memberships ms
               JOIN members m ON m.id = ms.member_id
               LEFT JOIN membership_categories mc ON mc.id = ms.category_id
              WHERE ms.id = ?
              LIMIT 1',
            [$id]
        );
        return $row ?: null;
    }

    /**
     * Return all memberships for a member, newest first.
     *
     * @return list<array<string, mixed>>
     */
    public static function findByMember(int $memberId): array
    {
        return self::db()->fetchAll(
            'SELECT ms.*, mc.label AS category_name
               FROM memberships ms
               LEFT JOIN membership_categories mc ON mc.id = ms.category_id
              WHERE ms.member_id = ?
              ORDER BY ms.year DESC',
            [$memberId]
        );
    }

    /**
     * Return the membership for the current year for a given member.
     *
     * @return array<string, mixed>|null
     */
    public static function getCurrentForMember(int $memberId): ?array
    {
        $row = self::db()->fetch(
            'SELECT ms.*, mc.label AS category_name
               FROM memberships ms
               LEFT JOIN membership_categories mc ON mc.id = ms.category_id
              WHERE ms.member_id = ? AND ms.year = ?
              LIMIT 1',
            [$memberId, (int) date('Y')]
        );
        return $row ?: null;
    }

    /**
     * Return distinct years that have at least one membership.
     *
     * @return list<int>
     */
    public static function getYearsWithMemberships(): array
    {
        $rows = self::db()->fetchAll(
            'SELECT DISTINCT year FROM memberships ORDER BY year DESC'
        );
        return array_map(fn($r) => (int) $r['year'], $rows);
    }

    // =========================================================================
    // Write
    // =========================================================================

    /**
     * Insert a new membership row.
     *
     * @param array<string, mixed> $data
     * @return int  New membership ID
     */
    public static function create(array $data): int
    {
        return (int) self::db()->insert('memberships', $data);
    }

    /**
     * Update membership fields.
     *
     * @param array<string, mixed> $data
     */
    public static function update(int $id, array $data): bool
    {
        return self::db()->update('memberships', $data, ['id' => $id]) >= 0;
    }

    // =========================================================================
    // Membership number helpers
    // =========================================================================

    /**
     * Return the next available membership number string (SOC0001 format).
     *
     * Reads prefix and padding from settings.
     * Excludes numbers already assigned to members AND numbers in reserved_member_numbers.
     */
    public static function getNextAvailableNumber(): string
    {
        $db = self::db();

        $prefix  = (string) ($db->fetch(
            "SELECT `value` FROM settings WHERE `key` = 'members.number_prefix' LIMIT 1"
        )['value'] ?? 'SOC');

        $padding = (int) ($db->fetch(
            "SELECT `value` FROM settings WHERE `key` = 'members.number_padding' LIMIT 1"
        )['value'] ?? 4);

        $prefixLen = strlen($prefix);

        // Max numeric part from active members
        $maxMembers = (int) ($db->fetch(
            "SELECT COALESCE(MAX(CAST(SUBSTRING(membership_number, ?) AS UNSIGNED)), 0) AS m
               FROM members",
            [$prefixLen + 1]
        )['m'] ?? 0);

        // Max numeric part from reserved numbers with same prefix
        $maxReserved = (int) ($db->fetch(
            "SELECT COALESCE(MAX(CAST(SUBSTRING(membership_number, ?) AS UNSIGNED)), 0) AS m
               FROM reserved_member_numbers
              WHERE membership_number LIKE ?",
            [$prefixLen + 1, $prefix . '%']
        )['m'] ?? 0);

        $next = max($maxMembers, $maxReserved) + 1;

        return $prefix . str_pad((string) $next, $padding, '0', STR_PAD_LEFT);
    }

    // =========================================================================
    // Audit helper
    // =========================================================================

    /**
     * Write a row to audit_logs for a membership-related action.
     *
     * @param array<string,mixed>|null $oldValues
     * @param array<string,mixed>|null $newValues
     */
    public static function audit(
        int    $userId,
        string $action,
        int    $entityId,
        ?array $oldValues,
        ?array $newValues,
        string $ip = ''
    ): void {
        self::db()->insert('audit_logs', [
            'user_id'     => $userId,
            'action'      => $action,
            'entity_type' => 'memberships',
            'entity_id'   => $entityId,
            'old_values'  => $oldValues !== null ? json_encode($oldValues) : null,
            'new_values'  => $newValues !== null ? json_encode($newValues) : null,
            'ip_address'  => $ip,
            'user_agent'  => substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 512),
        ]);
    }
}
