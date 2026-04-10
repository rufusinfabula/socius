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
 * Communication model.
 *
 * Manages association communications: drafts, recipient lists,
 * template resolution, and export.
 *
 * Available placeholders for body text:
 *   [nome]               member first name
 *   [cognome]            member surname
 *   [nome_completo]      name + surname
 *   [numero_socio]       M00001 format
 *   [numero_tessera]     C00001 format
 *   [anno]               current social year
 *   [data_chiusura]      renewal_close date formatted
 *   [data_scadenza]      renewal_lapse date formatted
 *   [associazione]       association name from settings
 *   [email_associazione] association email from settings
 */
class Communication extends BaseModel
{
    // =========================================================================
    // Read
    // =========================================================================

    /**
     * Return a paginated list of communications with optional filters.
     *
     * Supported filter keys: status, type, renewal_period
     *
     * @return array{items: list<array<string,mixed>>, total: int, page: int, per_page: int, pages: int}
     */
    public static function findAll(array $filters = [], int $page = 1, int $perPage = 25): array
    {
        $where  = [];
        $params = [];

        if (!empty($filters['status'])) {
            $where[]  = 'c.status = ?';
            $params[] = $filters['status'];
        }
        if (!empty($filters['type'])) {
            $where[]  = 'c.type = ?';
            $params[] = $filters['type'];
        }
        if (!empty($filters['renewal_period'])) {
            $where[]  = 'c.renewal_period = ?';
            $params[] = $filters['renewal_period'];
        }

        $whereClause = $where ? ('WHERE ' . implode(' AND ', $where)) : '';

        $total = (int) (self::db()->fetch(
            "SELECT COUNT(*) AS cnt FROM communications c {$whereClause}",
            $params
        )['cnt'] ?? 0);

        $offset = ($page - 1) * $perPage;
        $items  = self::db()->fetchAll(
            "SELECT c.*,
                    u.name    AS created_by_name,
                    u.surname AS created_by_surname
               FROM communications c
               LEFT JOIN users u ON u.id = c.created_by
               {$whereClause}
               ORDER BY c.created_at DESC
               LIMIT ? OFFSET ?",
            [...$params, $perPage, $offset]
        );

        return [
            'items'    => $items,
            'total'    => $total,
            'page'     => $page,
            'per_page' => $perPage,
            'pages'    => (int) ceil($total / max(1, $perPage)),
        ];
    }

    /**
     * @return array<string,mixed>|null
     */
    public static function findById(int $id): ?array
    {
        $row = self::db()->fetch(
            "SELECT c.*,
                    u.name    AS created_by_name,
                    u.surname AS created_by_surname
               FROM communications c
               LEFT JOIN users u ON u.id = c.created_by
              WHERE c.id = ? LIMIT 1",
            [$id]
        );
        return $row ?: null;
    }

    // =========================================================================
    // Write
    // =========================================================================

    /**
     * Create a new communication. Returns the new ID.
     */
    public static function create(array $data): int
    {
        return (int) self::db()->insert('communications', $data);
    }

    /**
     * Update a communication. Only allowed when status = 'draft'.
     */
    public static function update(int $id, array $data): bool
    {
        $existing = self::findById($id);
        if (!$existing || (string) ($existing['status'] ?? '') !== 'draft') {
            return false;
        }
        self::db()->update('communications', $data, ['id' => $id]);
        return true;
    }

    /**
     * Delete a communication. Only allowed when status = 'draft'.
     * Recipients are cascade-deleted.
     */
    public static function delete(int $id): bool
    {
        $existing = self::findById($id);
        if (!$existing || (string) ($existing['status'] ?? '') !== 'draft') {
            return false;
        }
        self::db()->query('DELETE FROM communications WHERE id = ?', [$id]);
        return true;
    }

    // =========================================================================
    // Recipients
    // =========================================================================

    /**
     * Add members as recipients (INSERT IGNORE — duplicates silently skipped).
     * Updates recipient_count on the communication row.
     *
     * @param  int[] $memberIds
     * @return int   Number of newly added recipients
     */
    public static function addRecipients(int $commId, array $memberIds): int
    {
        if (empty($memberIds)) {
            return 0;
        }
        $db = self::db();

        $countBefore = (int) ($db->fetch(
            'SELECT COUNT(*) AS cnt FROM communication_recipients WHERE communication_id = ?',
            [$commId]
        )['cnt'] ?? 0);

        foreach ($memberIds as $memberId) {
            try {
                $db->query(
                    'INSERT IGNORE INTO communication_recipients (communication_id, member_id) VALUES (?, ?)',
                    [$commId, (int) $memberId]
                );
            } catch (\Throwable) {}
        }

        $countAfter = (int) ($db->fetch(
            'SELECT COUNT(*) AS cnt FROM communication_recipients WHERE communication_id = ?',
            [$commId]
        )['cnt'] ?? 0);

        self::updateRecipientCount($commId, $countAfter);
        return $countAfter - $countBefore;
    }

    /**
     * Replace the entire recipient list for a communication.
     * Deletes all existing recipients and re-adds from memberIds.
     *
     * @param  int[] $memberIds
     */
    public static function replaceRecipients(int $commId, array $memberIds): void
    {
        $db = self::db();
        $db->query('DELETE FROM communication_recipients WHERE communication_id = ?', [$commId]);
        self::addRecipients($commId, $memberIds);
    }

    /**
     * Remove a single recipient.
     */
    public static function removeRecipient(int $commId, int $memberId): bool
    {
        self::db()->query(
            'DELETE FROM communication_recipients WHERE communication_id = ? AND member_id = ?',
            [$commId, $memberId]
        );
        self::updateRecipientCount($commId);
        return true;
    }

    /**
     * Toggle the included flag for a recipient.
     */
    public static function toggleRecipient(int $commId, int $memberId): bool
    {
        self::db()->query(
            'UPDATE communication_recipients
                SET included = NOT included
              WHERE communication_id = ? AND member_id = ?',
            [$commId, $memberId]
        );
        return true;
    }

    /**
     * Return recipients for a communication, joined with member data.
     *
     * @return list<array<string,mixed>>
     */
    public static function getRecipients(int $commId, bool $includedOnly = false): array
    {
        $sql = "SELECT cr.id AS cr_id, cr.member_id, cr.included, cr.personalised_body,
                       m.name, m.surname, m.email, m.member_number, m.membership_number,
                       m.status
                  FROM communication_recipients cr
                  JOIN members m ON m.id = cr.member_id
                 WHERE cr.communication_id = ?";
        $params = [$commId];

        if ($includedOnly) {
            $sql     .= ' AND cr.included = 1';
        }

        $sql .= ' ORDER BY m.surname ASC, m.name ASC';

        return self::db()->fetchAll($sql, $params);
    }

    // =========================================================================
    // Template resolution
    // =========================================================================

    /**
     * Replace all [key] placeholders in $body with values for $member.
     *
     * @param array $member   Member row — must include name, surname, member_number, membership_number
     * @param array $settings Flat key→value settings array
     */
    public static function resolveTemplate(string $body, array $member, array $settings): string
    {
        $today    = new \DateTimeImmutable('today');
        $thisYear = (int) $today->format('Y');

        // Social year
        $lapseMmdd = (string) ($settings['renewal.date_lapse'] ?? '12-31');
        [$lm, $ld] = explode('-', $lapseMmdd);
        $lapseCheck = new \DateTimeImmutable(
            sprintf('%04d-%02d-%02d', $thisYear, (int) $lm, (int) $ld)
        );
        $socialYear = ($today > $lapseCheck) ? $thisYear + 1 : $thisYear;

        // Renewal dates
        $closeMmdd = (string) ($settings['renewal.date_close'] ?? '04-15');
        [$cm, $cd] = explode('-', $closeMmdd);
        $closeDate = new \DateTimeImmutable(sprintf('%04d-%02d-%02d', $socialYear, (int) $cm, (int) $cd));

        [$lm2, $ld2] = explode('-', $lapseMmdd);
        $lapseDate  = new \DateTimeImmutable(sprintf('%04d-%02d-%02d', $socialYear, (int) $lm2, (int) $ld2));

        $dateFormat = (string) ($settings['ui.date_format'] ?? 'd/m/Y');
        $assocName  = (string) ($settings['association.name']  ?? '');
        $assocEmail = (string) ($settings['association.email'] ?? '');

        $placeholders = [
            '[nome]'               => (string) ($member['name']    ?? ''),
            '[cognome]'            => (string) ($member['surname'] ?? ''),
            '[nome_completo]'      => trim(($member['name'] ?? '') . ' ' . ($member['surname'] ?? '')),
            '[numero_socio]'       => format_member_number((int) ($member['member_number'] ?? 0)),
            '[numero_tessera]'     => format_card_number($member['membership_number'] ?? null),
            '[anno]'               => (string) $socialYear,
            '[data_chiusura]'      => $closeDate->format($dateFormat),
            '[data_scadenza]'      => $lapseDate->format($dateFormat),
            '[associazione]'       => $assocName,
            '[email_associazione]' => $assocEmail,
        ];

        return str_replace(array_keys($placeholders), array_values($placeholders), $body);
    }

    // =========================================================================
    // Recipient builders
    // =========================================================================

    /**
     * Build a list of member IDs matching the given filters.
     *
     * Supported filter keys:
     *   statuses    (string[])  array of member status values
     *   category_id (int)       filter by most-recent membership category
     *   board_only  (bool)      only board members
     *   member_ids  (int[])     explicit list of IDs
     *
     * @return int[]
     */
    public static function buildRecipientsFromFilters(array $filters): array
    {
        $where  = [];
        $params = [];

        if (!empty($filters['statuses'])) {
            $ph      = implode(',', array_fill(0, count($filters['statuses']), '?'));
            $where[] = "m.status IN ($ph)";
            $params  = array_merge($params, array_map('strval', $filters['statuses']));
        }

        if (!empty($filters['category_id'])) {
            $where[]  = 'EXISTS (SELECT 1 FROM memberships ms2 WHERE ms2.member_id = m.id AND ms2.category_id = ?)';
            $params[] = (int) $filters['category_id'];
        }

        if (!empty($filters['board_only'])) {
            $where[] = 'm.is_board_member = 1';
        }

        if (!empty($filters['member_ids'])) {
            $ph      = implode(',', array_fill(0, count($filters['member_ids']), '?'));
            $where[] = "m.id IN ($ph)";
            $params  = array_merge($params, array_map('intval', $filters['member_ids']));
        }

        if (empty($where)) {
            return [];
        }

        $whereClause = 'WHERE ' . implode(' AND ', $where);
        $rows        = self::db()->fetchAll(
            "SELECT m.id FROM members m {$whereClause} ORDER BY m.surname, m.name",
            $params
        );

        return array_map(static fn(array $r) => (int) $r['id'], $rows);
    }

    // =========================================================================
    // Export
    // =========================================================================

    /**
     * Export recipients as CSV, TXT (email only), or TXT with names.
     *
     * @param  string $format  'csv' | 'txt' | 'txt_names'
     * @return string          Raw export content
     */
    public static function exportRecipients(int $commId, string $format): string
    {
        $recipients = self::getRecipients($commId, true);

        switch ($format) {
            case 'csv':
                $lines = ["N.Socio,Cognome,Nome,Email,Status"];
                foreach ($recipients as $r) {
                    $lines[] = implode(',', [
                        '"' . str_replace('"', '""', format_member_number((int) $r['member_number'])) . '"',
                        '"' . str_replace('"', '""', (string) $r['surname']) . '"',
                        '"' . str_replace('"', '""', (string) $r['name']) . '"',
                        '"' . str_replace('"', '""', (string) ($r['email'] ?? '')) . '"',
                        '"' . str_replace('"', '""', (string) ($r['status'] ?? '')) . '"',
                    ]);
                }
                return implode("\r\n", $lines);

            case 'txt':
                $lines = [];
                foreach ($recipients as $r) {
                    $email = trim((string) ($r['email'] ?? ''));
                    if ($email !== '') {
                        $lines[] = $email;
                    }
                }
                return implode("\n", $lines);

            case 'txt_names':
                $lines = [];
                foreach ($recipients as $r) {
                    $email = trim((string) ($r['email'] ?? ''));
                    if ($email !== '') {
                        $lines[] = trim($r['surname'] . ' ' . $r['name']) . ' <' . $email . '>';
                    }
                }
                return implode("\n", $lines);

            default:
                return '';
        }
    }

    // =========================================================================
    // Status transitions
    // =========================================================================

    /**
     * Transition a communication to 'ready'.
     * Allowed from 'draft' only.
     */
    public static function markAsReady(int $commId): bool
    {
        $existing = self::findById($commId);
        if (!$existing || (string) ($existing['status'] ?? '') !== 'draft') {
            return false;
        }
        self::db()->update('communications', ['status' => 'ready'], ['id' => $commId]);
        return true;
    }

    /**
     * Transition a communication to 'sent'.
     * Allowed from 'ready' only.
     */
    public static function markAsSent(int $commId): bool
    {
        $existing = self::findById($commId);
        if (!$existing || (string) ($existing['status'] ?? '') !== 'ready') {
            return false;
        }
        self::db()->update('communications', [
            'status'  => 'sent',
            'sent_at' => date('Y-m-d H:i:s'),
        ], ['id' => $commId]);
        return true;
    }

    // =========================================================================
    // Duplicate
    // =========================================================================

    /**
     * Duplicate a communication as a new draft (without recipients).
     * Returns the ID of the new communication.
     */
    public static function duplicate(int $commId, ?int $createdBy = null): int
    {
        $original = self::findById($commId);
        if (!$original) {
            throw new \RuntimeException('Communication not found');
        }

        $newId = (int) self::db()->insert('communications', [
            'title'          => 'Copia — ' . $original['title'],
            'subject'        => (string) $original['subject'],
            'body_text'      => (string) $original['body_text'],
            'body_md'        => $original['body_md'],
            'format'         => (string) $original['format'],
            'status'         => 'draft',
            'type'           => (string) $original['type'],
            'renewal_period' => $original['renewal_period'],
            'recipient_count'=> 0,
            'created_by'     => $createdBy ?? $original['created_by'],
        ]);

        return $newId;
    }

    // =========================================================================
    // Internal
    // =========================================================================

    private static function updateRecipientCount(int $commId, ?int $count = null): void
    {
        if ($count === null) {
            $count = (int) (self::db()->fetch(
                'SELECT COUNT(*) AS cnt FROM communication_recipients WHERE communication_id = ?',
                [$commId]
            )['cnt'] ?? 0);
        }
        self::db()->update('communications', ['recipient_count' => $count], ['id' => $commId]);
    }
}
