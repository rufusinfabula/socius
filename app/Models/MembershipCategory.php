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
 * Membership categories and their annual fee history.
 */
class MembershipCategory extends BaseModel
{
    // =========================================================================
    // Read
    // =========================================================================

    /**
     * Return all categories, ordered by sort_order then label.
     *
     * @param  bool $onlyActive  When true, exclude inactive categories.
     * @return list<array<string, mixed>>
     */
    public static function findAll(bool $onlyActive = false): array
    {
        $where = $onlyActive ? 'WHERE is_active = 1' : '';
        return self::db()->fetchAll(
            "SELECT * FROM membership_categories {$where} ORDER BY sort_order ASC, label ASC"
        );
    }

    /**
     * Find a category by primary key.
     *
     * @return array<string, mixed>|null
     */
    public static function findById(int $id): ?array
    {
        $row = self::db()->fetch(
            'SELECT * FROM membership_categories WHERE id = ? LIMIT 1',
            [$id]
        );
        return $row ?: null;
    }

    // =========================================================================
    // Write
    // =========================================================================

    /**
     * Insert a new category row.
     *
     * @param  array<string, mixed> $data
     * @return int  New category ID
     */
    public static function create(array $data): int
    {
        return (int) self::db()->insert('membership_categories', $data);
    }

    /**
     * Update a category row. Returns true when at least one row was changed.
     *
     * @param array<string, mixed> $data
     */
    public static function update(int $id, array $data): bool
    {
        return self::db()->update('membership_categories', $data, ['id' => $id]) > 0;
    }

    // =========================================================================
    // Fee helpers
    // =========================================================================

    /**
     * Return the applicable fee for a category in a given year.
     *
     * Resolution order:
     *   1. Exact match in membership_category_fees for (category_id, anno)
     *   2. Most recent earlier year in membership_category_fees
     *   3. annual_fee from the category row itself
     *
     * @return float|null  null only when the category does not exist at all
     */
    public static function getFeeForYear(int $categoryId, int $anno): ?float
    {
        // 1. Exact year
        $row = self::db()->fetch(
            'SELECT fee FROM membership_category_fees
              WHERE category_id = ? AND year = ?
              LIMIT 1',
            [$categoryId, $anno]
        );
        if ($row !== null) {
            return (float) $row['fee'];
        }

        // 2. Most recent earlier year
        $row = self::db()->fetch(
            'SELECT fee FROM membership_category_fees
              WHERE category_id = ? AND year < ?
              ORDER BY year DESC
              LIMIT 1',
            [$categoryId, $anno]
        );
        if ($row !== null) {
            return (float) $row['fee'];
        }

        // 3. Fallback to category's own annual_fee
        $cat = self::findById($categoryId);
        return $cat !== null ? (float) $cat['annual_fee'] : null;
    }

    /**
     * Insert or update the fee for a category in a given year.
     *
     * Uses INSERT … ON DUPLICATE KEY UPDATE against the unique key
     * uq_category_anno (category_id, anno).
     */
    public static function setFeeForYear(
        int    $categoryId,
        int    $anno,
        float  $quota,
        string $note,
        int    $userId
    ): bool {
        self::db()->query(
            'INSERT INTO membership_category_fees
                 (category_id, year, fee, note, approved_by)
             VALUES (?, ?, ?, ?, ?)
             ON DUPLICATE KEY UPDATE
                 fee         = VALUES(fee),
                 note        = VALUES(note),
                 approved_by = VALUES(approved_by)',
            [$categoryId, $anno, $quota, $note ?: null, $userId]
        );
        return true;
    }

    /**
     * Return the full fee history for a category, newest year first.
     *
     * @return list<array<string, mixed>>
     */
    public static function getFeesHistory(int $categoryId): array
    {
        return self::db()->fetchAll(
            'SELECT f.*, u.name AS approved_by_name
               FROM membership_category_fees f
               LEFT JOIN users u ON u.id = f.approved_by
              WHERE f.category_id = ?
              ORDER BY f.year DESC',
            [$categoryId]
        );
    }
}
