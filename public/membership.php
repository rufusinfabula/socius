<?php
/*
 * Socius - Open Source Association Management System
 * Copyright (C) 2026 Fabio Ranfi
 */

declare(strict_types=1);

require_once __DIR__ . '/_init.php';

requireAuth();

use Socius\Models\Membership;

$currentUser = current_user();
$id          = (int) ($_GET['id'] ?? 0);

if ($id <= 0) {
    flash_set('error', __('memberships.not_found'));
    redirect('memberships.php');
}

$membership = null;
try {
    $membership = Membership::findById($id);
} catch (\Throwable) {}

if ($membership === null) {
    flash_set('error', __('memberships.not_found'));
    redirect('memberships.php');
}

theme('membership', [
    'activeNav'    => 'memberships',
    'currentUser'  => $currentUser,
    'membership'   => $membership,
    'flashSuccess' => flash_get('success'),
    'flashError'   => flash_get('error'),
]);
