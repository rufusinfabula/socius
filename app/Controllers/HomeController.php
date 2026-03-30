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

namespace Socius\Controllers;

use Socius\Core\Middleware;
use Socius\Core\Request;
use Socius\Core\Response;
use Socius\Models\User;

/**
 * Dashboard / home page.
 */
class HomeController extends BaseController
{
    public function index(Request $request, array $params): Response
    {
        if (!Middleware::isAuthenticated()) {
            return $this->redirect('/login');
        }

        $user = User::findById((int) Middleware::authUserId());

        return $this->view('themes/uikit/home/dashboard', [
            'activeNav' => 'dashboard',
            'user'      => $user,
        ]);
    }
}
