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
 * Payment gateway configuration (PayPal, Satispay).
 */
return [
    'paypal' => [
        'mode'          => $_ENV['PAYPAL_MODE']          ?? 'sandbox',
        'client_id'     => $_ENV['PAYPAL_CLIENT_ID']     ?? '',
        'client_secret' => $_ENV['PAYPAL_CLIENT_SECRET'] ?? '',
        'currency'      => $_ENV['PAYPAL_CURRENCY']      ?? 'EUR',
        'webhook_id'    => $_ENV['PAYPAL_WEBHOOK_ID']    ?? '',
    ],

    'satispay' => [
        'environment'      => $_ENV['SATISPAY_ENVIRONMENT']        ?? 'staging',
        'key_id'           => $_ENV['SATISPAY_KEY_ID']             ?? '',
        'private_key_path' => $_ENV['SATISPAY_PRIVATE_KEY_PATH']   ?? '',
        'public_key_path'  => $_ENV['SATISPAY_PUBLIC_KEY_PATH']    ?? '',
        'currency'         => $_ENV['SATISPAY_CURRENCY']           ?? 'EUR',
        'webhook_secret'   => $_ENV['SATISPAY_WEBHOOK_SECRET']     ?? '',
    ],
];
