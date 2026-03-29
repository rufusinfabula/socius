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
 * Mail / SMTP configuration.
 */
return [
    'driver'     => $_ENV['MAIL_DRIVER']       ?? 'smtp',
    'host'       => $_ENV['MAIL_HOST']         ?? '',
    'port'       => (int) ($_ENV['MAIL_PORT']  ?? 587),
    'username'   => $_ENV['MAIL_USERNAME']     ?? '',
    'password'   => $_ENV['MAIL_PASSWORD']     ?? '',
    'encryption' => $_ENV['MAIL_ENCRYPTION']   ?? 'tls',
    'from'       => [
        'address' => $_ENV['MAIL_FROM_ADDRESS'] ?? '',
        'name'    => $_ENV['MAIL_FROM_NAME']    ?? 'Socius',
    ],
];
