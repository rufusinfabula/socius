<?php
/*
 * Socius - Open Source Association Management System
 * Copyright (C) 2026 Fabio Ranfi
 *
 * Sync — Member status recalculation
 *
 * Called:
 * 1. Automatically via sync-run.php on first login of the day
 * 2. Manually via the sync button in the navbar
 *
 * GET ?action=run    → execute sync, return JSON result
 * GET ?action=status → return current sync metadata only
 */

declare(strict_types=1);

require_once __DIR__ . '/_init.php';

requireAuth();

use Socius\Core\Database;
use Socius\Models\Setting;

header('Content-Type: application/json; charset=utf-8');

$action = trim((string) ($_GET['action'] ?? 'status'));

// ─── action=status ──────────────────────────────────────────────────────────
if ($action === 'status') {
    $lastSyncRaw   = Setting::get('system.last_sync_date', '');
    $lastSyncDate  = $lastSyncRaw !== '' ? format_date($lastSyncRaw) : '—';
    $isSynced      = ($lastSyncRaw === date('Y-m-d'));

    echo json_encode([
        'ok'             => true,
        'last_sync_date' => $lastSyncDate,
        'is_synced'      => $isSynced,
        'updated'        => (int) Setting::get('system.last_sync_count', '0'),
        'duration_ms'    => (int) Setting::get('system.last_sync_duration_ms', '0'),
    ]);
    exit;
}

// ─── action=run ─────────────────────────────────────────────────────────────
if ($action !== 'run') {
    echo json_encode(['ok' => false, 'error' => 'Unknown action']);
    exit;
}

$start = microtime(true);
$db    = Database::getInstance();

// Load settings as flat key→value array
$settingsRows = $db->fetchAll('SELECT `key`, `value` FROM `settings`');
$settings     = array_column($settingsRows, 'value', 'key');

// Load all members with their most recent membership
// Skip override statuses — calculate_member_status handles them internally,
// but we exclude them here to avoid unnecessary processing.
$members = $db->fetchAll(
    "SELECT
       m.id,
       m.status,
       m.member_number,
       ms.year  AS membership_year,
       ms.status AS membership_status
     FROM members m
     LEFT JOIN memberships ms ON ms.id = (
       SELECT id FROM memberships
       WHERE member_id = m.id
       ORDER BY year DESC, id DESC
       LIMIT 1
     )
     WHERE m.status NOT IN ('suspended', 'resigned', 'deceased')"
);

$total   = count($members);
$updated = 0;

foreach ($members as $member) {
    $newStatus = calculate_member_status($member, $settings);

    if ($newStatus !== (string) ($member['status'] ?? '')) {
        $db->update('members', ['status' => $newStatus], ['id' => (int) $member['id']]);
        $updated++;
    }
}

$durationMs = (int) round((microtime(true) - $start) * 1000);

Setting::set('system.last_sync_date',        date('Y-m-d'));
Setting::set('system.last_sync_count',       (string) $updated);
Setting::set('system.last_sync_duration_ms', (string) $durationMs);

// ─── Period detection and history tracking ──────────────────────────────────
// Runs once per day. Tracks which renewal period we are in and records
// the history of period transitions for the current social year.
// period_history is reset when entering a new 'open' period.
$newPeriod       = detect_current_period($settings);
$oldPeriod       = Setting::get('system.current_period', '');
$lastPeriodCheck = Setting::get('system.last_period_check', '');
$todayStr        = date('Y-m-d');
$periodChanged   = false;

if ($lastPeriodCheck !== $todayStr) {
    Setting::set('system.last_period_check', $todayStr);

    if ($newPeriod !== $oldPeriod) {
        $periodChanged = true;
        Setting::set('system.current_period', $newPeriod);

        $history = json_decode(Setting::get('system.period_history', '[]'), true);
        if (!is_array($history)) {
            $history = [];
        }

        // Reset history at the start of a new renewal cycle
        if ($newPeriod === 'open') {
            $history = [];
        }

        // Record entry into a named period
        if ($newPeriod !== '') {
            $history[] = [
                'period'      => $newPeriod,
                'entered_on'  => get_period_start_date($newPeriod, $settings),
                'detected_on' => $todayStr,
            ];
        }

        Setting::set('system.period_history', json_encode($history, JSON_UNESCAPED_UNICODE));
    }
}

$currentPeriod = Setting::get('system.current_period', '');
$periodHistory = json_decode(Setting::get('system.period_history', '[]'), true);
$lastSyncDate  = format_date(date('Y-m-d'));

echo json_encode([
    'ok'             => true,
    'updated'        => $updated,
    'total'          => $total,
    'duration_ms'    => $durationMs,
    'last_sync_date' => $lastSyncDate,
    'is_synced'      => true,
    'current_period' => $currentPeriod,
    'period_changed' => $periodChanged,
    'period_history' => is_array($periodHistory) ? $periodHistory : [],
]);
