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
 * Association member (anagrafica soci).
 *
 * Distinct from User, which represents a staff/admin account.
 * Members are never deleted in normal operations — only via emergencyDelete().
 */
class Member extends BaseModel
{
    // =========================================================================
    // Read
    // =========================================================================

    /**
     * Return a paginated list of members with optional filters.
     *
     * Supported filter keys:
     *   - status   (string)  One of: active, suspended, expired, resigned, deceased
     *   - category (int)     membership_categories.id
     *   - search   (string)  Fulltext: name, surname, email, membership_number
     *
     * @return array{items: list<array<string,mixed>>, total: int, page: int, per_page: int, pages: int}
     */
    public static function findAll(array $filters = [], int $page = 1, int $perPage = 25): array
    {
        $where  = [];
        $params = [];

        if (!empty($filters['status'])) {
            $where[]  = 'm.status = ?';
            $params[] = $filters['status'];
        }

        if (!empty($filters['category'])) {
            $where[]  = 'm.category_id = ?';
            $params[] = (int) $filters['category'];
        }

        if (!empty($filters['search'])) {
            $like     = '%' . $filters['search'] . '%';
            $where[]  = '(m.name LIKE ? OR m.surname LIKE ? OR m.email LIKE ? OR m.membership_number LIKE ?)';
            $params[] = $like;
            $params[] = $like;
            $params[] = $like;
            $params[] = $like;
        }

        $whereClause = $where ? ('WHERE ' . implode(' AND ', $where)) : '';

        $total = (int) (self::db()->fetch(
            "SELECT COUNT(*) AS cnt FROM members m {$whereClause}",
            $params
        )['cnt'] ?? 0);

        $offset = ($page - 1) * $perPage;
        $items  = self::db()->fetchAll(
            "SELECT m.*, mc.label AS category_name
               FROM members m
               LEFT JOIN membership_categories mc ON mc.id = m.category_id
               {$whereClause}
               ORDER BY m.surname ASC, m.name ASC
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
     * Find a member by primary key.
     *
     * @return array<string, mixed>|null
     */
    public static function findById(int $id): ?array
    {
        $row = self::db()->fetch(
            'SELECT m.*, mc.label AS category_name
               FROM members m
               LEFT JOIN membership_categories mc ON mc.id = m.category_id
              WHERE m.id = ?
              LIMIT 1',
            [$id]
        );
        return $row ?: null;
    }

    /**
     * Find a member by membership_number (e.g. "SOC0042").
     *
     * @return array<string, mixed>|null
     */
    public static function findByMembershipNumber(string $number): ?array
    {
        $row = self::db()->fetch(
            'SELECT * FROM members WHERE membership_number = ? LIMIT 1',
            [$number]
        );
        return $row ?: null;
    }

    /**
     * Return counts of members grouped by status.
     *
     * @return array<string, int>  e.g. ['active' => 42, 'suspended' => 3, ...]
     */
    public static function getStatsByStatus(): array
    {
        $rows = self::db()->fetchAll(
            'SELECT status, COUNT(*) AS cnt FROM members GROUP BY status'
        );
        $out = [];
        foreach ($rows as $row) {
            $out[(string) $row['status']] = (int) $row['cnt'];
        }
        return $out;
    }

    /**
     * Quick search across name, surname, email, membership_number.
     *
     * @return list<array<string, mixed>>
     */
    public static function search(string $query): array
    {
        $like = '%' . $query . '%';
        return self::db()->fetchAll(
            'SELECT m.*, mc.label AS category_name
               FROM members m
               LEFT JOIN membership_categories mc ON mc.id = m.category_id
              WHERE m.name LIKE ? OR m.surname LIKE ? OR m.email LIKE ? OR m.membership_number LIKE ?
              ORDER BY m.surname ASC, m.name ASC
              LIMIT 50',
            [$like, $like, $like, $like]
        );
    }

    // =========================================================================
    // Write
    // =========================================================================

    /**
     * Insert a new member row.
     *
     * IMPORTANT: membership_number is intentionally set to NULL at creation
     * time. A card number is only assigned when the first membership record
     * is created in the memberships table. This ensures the card number is
     * always linked to an actual membership record and never exists in isolation.
     *
     * member_number is a permanent sequential integer assigned via the
     * next_member_number() global helper (reads and increments members.next_number
     * in settings).
     *
     * @param  array<string, mixed> $data  Member fields (without membership_number)
     * @return int  New member ID
     */
    public static function create(array $data): int
    {
        // membership_number is NULL at creation — assigned with first membership
        unset($data['membership_number']);

        // Assign permanent member number from settings counter
        // next_member_number() reads members.next_number and increments it
        $data['member_number'] = next_member_number();

        return (int) self::db()->insert('members', $data);
    }

    /**
     * Update the denormalized card number on the member record.
     *
     * This method should only be called by Membership model operations
     * (create, update, releaseCardNumber). Direct calls from other contexts
     * are strongly discouraged — always go through Membership.
     *
     * Pass NULL when the member lapses and the card number should be released
     * (it then becomes available for reassignment).
     *
     * @param int         $memberId
     * @param string|null $cardNumber NULL when member lapses
     */
    public static function updateCardNumber(int $memberId, ?string $cardNumber): bool
    {
        return self::db()->update(
            'members',
            ['membership_number' => $cardNumber],
            ['id' => $memberId]
        ) >= 0;
    }

    /**
     * Update member fields. Returns true when at least one row was changed.
     *
     * @param array<string, mixed> $data
     */
    public static function update(int $id, array $data): bool
    {
        return self::db()->update('members', $data, ['id' => $id]) > 0;
    }

    // =========================================================================
    // Member number helpers
    // =========================================================================

    /**
     * Return the next available permanent member_number.
     *
     * Uses MAX(member_number) + 1 when members already exist,
     * otherwise falls back to settings 'members.number_start' (default 1).
     * member_number is assigned at creation and never changed.
     */
    public static function getNextMemberNumber(): int
    {
        $max = (int) (self::db()->fetch(
            'SELECT COALESCE(MAX(member_number), 0) AS m FROM members'
        )['m'] ?? 0);

        if ($max > 0) {
            return $max + 1;
        }

        $start = (int) (self::db()->fetch(
            "SELECT `value` FROM settings WHERE `key` = 'members.number_start' LIMIT 1"
        )['value'] ?? 1);

        return max(1, $start);
    }

    // =========================================================================
    // Membership number helpers
    // =========================================================================

    /**
     * Generate the next membership_number string.
     *
     * Format: {prefix}{zero-padded number}
     * e.g. "SOC0001" when prefix=SOC, padding=4, next=1
     *
     * Reads from settings:
     *   members.number_prefix  (default 'SOC')
     *   members.number_padding (default 4)
     *   members.next_number    (default 1)
     */
    public static function getNextMembershipNumber(): string
    {
        $prefix  = (string) (self::db()->fetch(
            "SELECT `value` FROM settings WHERE `key` = 'members.number_prefix' LIMIT 1"
        )['value'] ?? 'SOC');

        $padding = (int) (self::db()->fetch(
            "SELECT `value` FROM settings WHERE `key` = 'members.number_padding' LIMIT 1"
        )['value'] ?? 4);

        $next = (int) (self::db()->fetch(
            "SELECT `value` FROM settings WHERE `key` = 'members.next_number' LIMIT 1"
        )['value'] ?? 1);

        // Safety net: never reuse an existing number
        $prefixLen = strlen($prefix);
        $max = (int) (self::db()->fetch(
            "SELECT COALESCE(MAX(CAST(SUBSTRING(membership_number, ?) AS UNSIGNED)), 0) AS m
               FROM members",
            [$prefixLen + 1]
        )['m'] ?? 0);

        $number = max($next, $max + 1);

        return $prefix . str_pad((string) $number, $padding, '0', STR_PAD_LEFT);
    }

    // =========================================================================
    // Emergency delete (super_admin only)
    // =========================================================================

    /**
     * Permanently delete a member and their memberships.
     *
     * Payments and audit_logs are kept.
     * If $freeNumeroSocio is false, the membership_number is saved to
     * reserved_member_numbers so it cannot be reused accidentally.
     *
     * @throws \RuntimeException on any DB error (rolls back the transaction)
     */
    public static function emergencyDelete(
        int    $id,
        bool   $freeNumeroSocio,
        int    $deletedByUserId,
        string $ip = ''
    ): bool {
        $member = self::findById($id);
        if ($member === null) {
            return false;
        }

        return (bool) self::db()->transaction(function ($db) use (
            $id, $freeNumeroSocio, $deletedByUserId, $member, $ip
        ) {
            // 1. Write audit log BEFORE deletion (audit_logs has no FK on member_id)
            $db->insert('audit_logs', [
                'user_id'     => $deletedByUserId,
                'action'      => 'emergency_delete',
                'entity_type' => 'members',
                'entity_id'   => $id,
                'old_values'  => json_encode($member),
                'new_values'  => null,
                'ip_address'  => $ip,
                'user_agent'  => substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 512),
            ]);

            // 2. Reserve the number if not freed
            if (!$freeNumeroSocio) {
                try {
                    $db->insert('reserved_member_numbers', [
                        'membership_number' => $member['membership_number'],
                        'reserved_at'       => date('Y-m-d H:i:s'),
                        'reserved_by'       => $deletedByUserId,
                        'reason'            => 'emergency_delete of member id ' . $id,
                    ]);
                } catch (\Exception) {
                    // Already reserved — silently continue
                }
            }

            // 3. payment_requests: member_id is set to NULL automatically by the
            //    ON DELETE SET NULL FK (migration 019). No manual UPDATE needed.
            //    Payment records are preserved intact for audit purposes.

            // 4. memberships and other ON DELETE CASCADE rows are removed by FK
            // Delete memberships explicitly for clarity
            $db->delete('memberships', ['member_id' => $id]);

            // 5. Delete the member record
            $db->delete('members', ['id' => $id]);

            return true;
        });
    }

    // =========================================================================
    // Linked data helpers
    // =========================================================================

    /**
     * Return all memberships for a member, newest first.
     *
     * @return list<array<string, mixed>>
     */
    public static function getMemberships(int $memberId): array
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
     * Return all payments for a member, newest first.
     *
     * @return list<array<string, mixed>>
     */
    public static function getPayments(int $memberId): array
    {
        return self::db()->fetchAll(
            'SELECT p.*
               FROM payments p
              WHERE p.member_id = ?
              ORDER BY p.paid_at DESC',
            [$memberId]
        );
    }

    // =========================================================================
    // Audit log helper
    // =========================================================================

    /**
     * Write a row to audit_logs.
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
            'entity_type' => 'members',
            'entity_id'   => $entityId,
            'old_values'  => $oldValues !== null ? json_encode($oldValues) : null,
            'new_values'  => $newValues !== null ? json_encode($newValues) : null,
            'ip_address'  => $ip,
            'user_agent'  => substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 512),
        ]);
    }
}
