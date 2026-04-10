<?php
/*
 * Socius - Open Source Association Management System
 * Copyright (C) 2026 Fabio Ranfi
 *
 * Internal API — Communication template loader
 *
 * Returns the template body for a given renewal period from settings.
 *
 * GET ?period=open|first_reminder|second_reminder|third_reminder|close
 *
 * Response:
 * {
 *   "subject": "Rinnovo tessera anno 2026",
 *   "body": "Gentile [nome],\n\n..."
 * }
 */

declare(strict_types=1);

require_once __DIR__ . '/../_init.php';

requireAuth();

header('Content-Type: application/json; charset=utf-8');

$period = trim((string) ($_GET['period'] ?? ''));

$validPeriods = ['open', 'first_reminder', 'second_reminder', 'third_reminder', 'close'];

if (!in_array($period, $validPeriods, true)) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid period.']);
    exit;
}

try {
    $body = (string) \Socius\Models\Setting::get('comm.template_' . $period, '');

    // Compute social year for subject line
    $today     = new DateTimeImmutable('today');
    $thisYear  = (int) $today->format('Y');
    $lapseMmdd = (string) \Socius\Models\Setting::get('renewal.date_lapse', '12-31');
    [$lm, $ld] = explode('-', $lapseMmdd);
    $lapseCheck = new DateTimeImmutable(sprintf('%04d-%02d-%02d', $thisYear, (int) $lm, (int) $ld));
    $socialYear = ($today > $lapseCheck) ? $thisYear + 1 : $thisYear;

    echo json_encode([
        'subject' => 'Rinnovo tessera anno ' . $socialYear,
        'body'    => $body,
    ]);

} catch (\Throwable) {
    http_response_code(500);
    echo json_encode(['error' => 'Internal server error.']);
}
