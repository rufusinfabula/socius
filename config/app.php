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
 * Application configuration.
 * Values are read from the .env file via a helper (to be implemented).
 */
return [
    'name'     => $_ENV['APP_NAME']     ?? 'Socius',
    'env'      => $_ENV['APP_ENV']      ?? 'production',
    'debug'    => filter_var($_ENV['APP_DEBUG'] ?? false, FILTER_VALIDATE_BOOLEAN),
    'url'      => $_ENV['APP_URL']      ?? '',
    'timezone' => $_ENV['APP_TIMEZONE'] ?? 'Europe/Rome',
    'locale'   => $_ENV['APP_LOCALE']   ?? 'it',
    'key'      => $_ENV['APP_KEY']      ?? '',
];
