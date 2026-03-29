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
 * Italian language strings.
 *
 * @todo Populate all UI strings, validation messages, email templates.
 */
return [
    'app' => [
        'name' => 'Socius',
    ],
    'auth' => [
        'login'          => 'Accedi',
        'logout'         => 'Esci',
        'email'          => 'Email',
        'password'       => 'Password',
        'failed'         => 'Credenziali non valide.',
        'reset_password' => 'Reimposta password',
    ],
    'members' => [
        'title'       => 'Soci',
        'add'         => 'Aggiungi socio',
        'edit'        => 'Modifica socio',
        'delete'      => 'Elimina socio',
        'not_found'   => 'Socio non trovato.',
    ],
    'payments' => [
        'title'     => 'Pagamenti',
        'pending'   => 'In attesa',
        'completed' => 'Completato',
        'failed'    => 'Fallito',
        'refunded'  => 'Rimborsato',
    ],
    'errors' => [
        '404' => 'Pagina non trovata.',
        '500' => 'Errore interno del server.',
    ],
];
