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

/**
 * English language strings.
 *
 * @todo Populate all UI strings, validation messages, email templates.
 */
return [
    'app' => [
        'name' => 'Socius',
    ],
    'auth' => [
        'login'          => 'Log in',
        'logout'         => 'Log out',
        'email'          => 'Email',
        'password'       => 'Password',
        'failed'         => 'Invalid credentials.',
        'reset_password' => 'Reset password',
    ],
    'members' => [
        'title'     => 'Members',
        'add'       => 'Add member',
        'edit'      => 'Edit member',
        'delete'    => 'Delete member',
        'not_found' => 'Member not found.',
    ],
    'payments' => [
        'title'     => 'Payments',
        'pending'   => 'Pending',
        'completed' => 'Completed',
        'failed'    => 'Failed',
        'refunded'  => 'Refunded',
    ],
    'errors' => [
        '404' => 'Page not found.',
        '500' => 'Internal server error.',
    ],
];
