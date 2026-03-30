<?php
/*
 * Socius - Open Source Association Management System
 * Copyright (C) 2026 Fabio Ranfi
 */

declare(strict_types=1);

require_once __DIR__ . '/_init.php';

requireAuth();

$currentUser = current_user();

theme('settings', [
    'activeNav'   => 'settings',
    'currentUser' => $currentUser,
]);
