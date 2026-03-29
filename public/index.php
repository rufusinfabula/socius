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

// ============================================================
// Socius — Front Controller
// All HTTP requests are routed through this file.
// ============================================================

define('SOCIUS_START', microtime(true));
define('BASE_PATH', dirname(__DIR__));

// Autoloader
require BASE_PATH . '/vendor/autoload.php';

// Bootstrap the application
// $app = require BASE_PATH . '/bootstrap/app.php';
// $app->run();

// Placeholder — remove once bootstrap is implemented
echo 'Socius is not yet installed. Please visit /install to begin setup.';
