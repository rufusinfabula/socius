<?php
/*
 * Socius - Open Source Association Management System
 * Copyright (C) 2026 Fabio Ranfi
 */

declare(strict_types=1);

require_once __DIR__ . '/_init.php';

requireAuth();

use Socius\Core\Database;
use Socius\Models\Communication;
use Socius\Models\Setting;

$currentUser = current_user();
$db          = Database::getInstance();

// Active filters
$filterStatus = trim((string) ($_GET['status'] ?? ''));
$filterType   = trim((string) ($_GET['type']   ?? ''));
$page         = max(1, (int) ($_GET['page'] ?? 1));

$filters = [];
if ($filterStatus !== '') $filters['status'] = $filterStatus;
if ($filterType   !== '') $filters['type']   = $filterType;

$result = Communication::findAll($filters, $page, 25);

// Current renewal period
$currentPeriod = Setting::get('system.current_period', '');
$periodHistory = json_decode(Setting::get('system.period_history', '[]'), true);

theme('communications', [
    'activeNav'      => 'communications',
    'currentUser'    => $currentUser,
    'communications' => $result['items'],
    'pagination'     => $result,
    'currentPeriod'  => $currentPeriod,
    'periodHistory'  => is_array($periodHistory) ? $periodHistory : [],
    'filterStatus'   => $filterStatus,
    'filterType'     => $filterType,
    'flashSuccess'   => flash_get('success'),
    'flashError'     => flash_get('error'),
]);
