<?php
/*
 * Socius - Open Source Association Management System
 * Copyright (C) 2026 Fabio Ranfi
 *
 * Internal API — Communication recipients management
 *
 * GET  ?action=list&comm_id=N         → list recipients as JSON
 * POST action=toggle body: comm_id, member_id  → toggle included flag
 * POST action=remove body: comm_id, member_id  → remove recipient
 */

declare(strict_types=1);

require_once __DIR__ . '/../_init.php';

requireAuth();

header('Content-Type: application/json; charset=utf-8');

use Socius\Models\Communication;

$action = trim((string) ($_REQUEST['action'] ?? 'list'));

try {
    // ─── GET list ───────────────────────────────────────────────────────────
    if ($action === 'list' && $_SERVER['REQUEST_METHOD'] === 'GET') {
        $commId = (int) ($_GET['comm_id'] ?? 0);
        if ($commId <= 0) {
            http_response_code(400);
            echo json_encode(['error' => 'Missing comm_id.']);
            exit;
        }

        $recipients = Communication::getRecipients($commId, false);
        $members = array_map(static function (array $r): array {
            return [
                'member_id'     => (int) $r['member_id'],
                'member_number' => format_member_number((int) $r['member_number']),
                'name'          => (string) $r['name'],
                'surname'       => (string) $r['surname'],
                'email'         => (string) ($r['email'] ?? ''),
                'status'        => (string) ($r['status'] ?? ''),
                'included'      => (bool) $r['included'],
            ];
        }, $recipients);

        echo json_encode(['ok' => true, 'recipients' => $members]);
        exit;
    }

    // ─── POST actions ───────────────────────────────────────────────────────
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        echo json_encode(['error' => 'Method not allowed.']);
        exit;
    }

    if (!csrf_verify()) {
        http_response_code(403);
        echo json_encode(['error' => 'CSRF token mismatch.']);
        exit;
    }

    $commId   = (int) ($_POST['comm_id']   ?? 0);
    $memberId = (int) ($_POST['member_id'] ?? 0);

    if ($commId <= 0) {
        http_response_code(400);
        echo json_encode(['error' => 'Missing comm_id.']);
        exit;
    }

    if ($action === 'toggle') {
        if ($memberId <= 0) {
            http_response_code(400);
            echo json_encode(['error' => 'Missing member_id.']);
            exit;
        }
        Communication::toggleRecipient($commId, $memberId);
        echo json_encode(['ok' => true]);
        exit;
    }

    if ($action === 'remove') {
        if ($memberId <= 0) {
            http_response_code(400);
            echo json_encode(['error' => 'Missing member_id.']);
            exit;
        }
        Communication::removeRecipient($commId, $memberId);
        echo json_encode(['ok' => true]);
        exit;
    }

    http_response_code(400);
    echo json_encode(['error' => 'Unknown action.']);

} catch (\Throwable) {
    http_response_code(500);
    echo json_encode(['error' => 'Internal server error.']);
}
