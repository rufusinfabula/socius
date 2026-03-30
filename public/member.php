<?php
/*
 * Socius - Open Source Association Management System
 * Copyright (C) 2026 Fabio Ranfi
 */

declare(strict_types=1);

require_once __DIR__ . '/_init.php';

requireAuth();

use Socius\Models\Member;

$currentUser = current_user();
$id          = (int) ($_GET['id'] ?? 0);

if ($id <= 0) {
    redirect('members.php');
}

$member = null;
try {
    $member = Member::findById($id);
} catch (\Throwable) {}

if ($member === null) {
    flash_set('error', 'Socio non trovato.');
    redirect('members.php');
}

$memberships = [];
$payments    = [];

try {
    $memberships = Member::getMemberships($id);
} catch (\Throwable) {}

try {
    $payments = Member::getPayments($id);
} catch (\Throwable) {}

theme('member', [
    'activeNav'    => 'members',
    'currentUser'  => $currentUser,
    'member'       => $member,
    'memberships'  => $memberships,
    'payments'     => $payments,
    'flashSuccess' => flash_get('success'),
    'flashError'   => flash_get('error'),
]);
