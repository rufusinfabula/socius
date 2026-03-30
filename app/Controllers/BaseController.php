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

use Socius\Core\Response;

/**
 * Base controller providing shared utilities (view rendering, redirects, JSON).
 */
abstract class BaseController
{
    protected function view(string $template, array $data = [], int $status = 200): Response
    {
        return (new Response())->view($template, $data, $status);
    }

    protected function redirect(string $url, int $status = 302): Response
    {
        return (new Response())->redirect($url, $status);
    }

    protected function json(mixed $data, int $status = 200): Response
    {
        return (new Response())->json($data, $status);
    }
}
