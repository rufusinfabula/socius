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

use Socius\Controllers\AuthController;
use Socius\Controllers\EventController;
use Socius\Controllers\HomeController;
use Socius\Controllers\MemberController;
use Socius\Controllers\PaymentController;

/**
 * Application route definitions.
 *
 * $router is injected by Application::registerRoutes() as a local variable.
 * Signature: $router->get|post(pattern, FQCN, method, [middleware, ...])
 */

// Public routes
$router->get('/',              HomeController::class,    'index');
$router->get('/login',         AuthController::class,    'showLogin');
$router->post('/login',        AuthController::class,    'login');
$router->get('/logout',        AuthController::class,    'logout');

// Members (auth required)
$router->get('/members',              MemberController::class,   'index',   ['auth']);
$router->get('/members/{id}',         MemberController::class,   'show',    ['auth']);
$router->get('/members/create',       MemberController::class,   'create',  ['auth']);
$router->post('/members',             MemberController::class,   'store',   ['auth', 'csrf']);
$router->get('/members/{id}/edit',    MemberController::class,   'edit',    ['auth']);
$router->post('/members/{id}/edit',   MemberController::class,   'update',  ['auth', 'csrf']);
$router->post('/members/{id}/delete', MemberController::class,   'delete',  ['auth', 'csrf']);

// Events (auth required)
$router->get('/events',              EventController::class,    'index',   ['auth']);
$router->get('/events/{id}',         EventController::class,    'show',    ['auth']);
$router->post('/events',             EventController::class,    'store',   ['auth', 'csrf']);
$router->post('/events/{id}/edit',   EventController::class,    'update',  ['auth', 'csrf']);
$router->post('/events/{id}/delete', EventController::class,    'delete',  ['auth', 'csrf']);

// Payments (auth required)
$router->get('/payments',            PaymentController::class,  'index',   ['auth']);
$router->post('/payments/paypal',    PaymentController::class,  'paypal',  ['auth', 'csrf']);
$router->post('/payments/satispay',  PaymentController::class,  'satispay',['auth', 'csrf']);
