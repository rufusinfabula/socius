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
 * Membership model — manages annual membership records.
 *
 * MEMBER NUMBER vs CARD NUMBER
 *
 * Member number (members.member_number):
 *   - Permanent sequential integer assigned at first registration
 *   - Never changes, even if the member lapses and rejoins years later
 *   - Format: M + 5 digits → M00001
 *   - Displayed with CSS class: badge-member-number (blue)
 *
 * Card number (memberships.membership_number  ←  SOURCE OF TRUTH):
 *   - Alphanumeric code assigned when a membership record is created
 *   - Stored per-membership row so historical records keep their number
 *   - Format: C + 5 digits → C00001
 *   - Displayed with CSS class: badge-card-number (green)
 *
 * members.membership_number is a DENORMALIZED COPY of the current active
 * card number. It is updated automatically via Member::updateCardNumber().
 * It must NEVER be modified directly — only through Membership operations.
 *
 * Card number lifecycle:
 *   1. Assigned at membership creation via next_card_number()
 *   2. Stored in memberships.membership_number (source of truth)
 *   3. Copied to members.membership_number via Member::updateCardNumber()
 *   4. On member lapse: releaseCardNumber() sets members.membership_number = NULL
 *   5. Historical membership records retain their card number permanently
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
     * NOTE: queries alias m.membership_number as member_card_number to avoid
     * collision with ms.membership_number (the source of truth for card numbers).
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
            // ms.* includes ms.membership_number (source of truth for this card).
            // m.membership_number (denormalized copy) is aliased to avoid collision.
            "SELECT ms.*, mc.label AS category_name,
                    m.name AS member_name, m.surname AS member_surname,
                    m.email AS member_email, m.status AS member_status,
                    m.membership_number AS member_card_number,
                    m.member_number
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
     * ms.membership_number is the source of truth for the card number.
     * m.membership_number (denormalized copy) is aliased as member_card_number.
     *
     * @return array<string, mixed>|null
     */
    public static function findById(int $id): ?array
    {
        $row = self::db()->fetch(
            'SELECT ms.*, mc.label AS category_name, mc.is_exempt_from_renewal,
                    m.name AS member_name, m.surname AS member_surname,
                    m.email AS member_email, m.status AS member_status,
                    m.membership_number AS member_card_number,
                    m.member_number
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
     * ms.membership_number is the card number for each historical record
     * (source of truth — remains even after card is released on lapse).
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
     * If $data does not include 'membership_number', one is generated
     * automatically via next_card_number(). After insertion, the denormalized
     * copy on members.membership_number is updated via Member::updateCardNumber().
     *
     * @param array<string, mixed> $data
     * @return int  New membership ID
     */
    public static function create(array $data): int
    {
        // Auto-assign card number if not provided
        if (empty($data['membership_number'])) {
            $data['membership_number'] = next_card_number();
        }

        $id       = (int) self::db()->insert('memberships', $data);
        $memberId = (int) ($data['member_id'] ?? 0);

        // Update denormalized copy on the member record
        if ($memberId > 0) {
            Member::updateCardNumber($memberId, (string) $data['membership_number']);
        }

        return $id;
    }

    /**
     * Update membership fields.
     *
     * If the record belongs to the current year and membership_number is being
     * changed, syncs the denormalized copy on members via Member::updateCardNumber().
     *
     * @param array<string, mixed> $data
     */
    public static function update(int $id, array $data): bool
    {
        $result = self::db()->update('memberships', $data, ['id' => $id]) >= 0;

        // Sync card number on member if it changed for a current-year record
        if (isset($data['membership_number'])) {
            $ms = self::findById($id);
            if ($ms && (int) $ms['year'] === (int) date('Y')) {
                Member::updateCardNumber((int) $ms['member_id'], $data['membership_number'] ?: null);
            }
        }

        return $result;
    }

    // =========================================================================
    // Card number lifecycle
    // =========================================================================

    /**
     * Release the card number when a member lapses.
     *
     * Sets members.membership_number to NULL — the number becomes available
     * for reassignment to new members. Historical membership records in the
     * memberships table retain their card number permanently.
     *
     * Called automatically by the renewal/lapse process.
     */
    public static function releaseCardNumber(int $memberId): bool
    {
        return Member::updateCardNumber($memberId, null);
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
