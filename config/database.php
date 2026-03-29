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
 * Database configuration.
 */
return [
    'driver'    => 'mysql',
    'host'      => $_ENV['DB_HOST']      ?? '127.0.0.1',
    'port'      => (int) ($_ENV['DB_PORT'] ?? 3306),
    'database'  => $_ENV['DB_NAME']  ?? 'socius',
    'username'  => $_ENV['DB_USER']  ?? '',
    'password'  => $_ENV['DB_PASS']  ?? '',
    'charset'   => $_ENV['DB_CHARSET']   ?? 'utf8mb4',
    'collation' => $_ENV['DB_COLLATION'] ?? 'utf8mb4_unicode_ci',
    'prefix'    => $_ENV['DB_PREFIX']    ?? '',
    'options'   => [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ],
];
