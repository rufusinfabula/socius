<?php
/*
 * Socius - Open Source Association Management System
 * Copyright (C) 2026 Fabio Ranfi
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 */

declare(strict_types=1);

namespace Socius\Core;

/**
 * Application bootstrap helper.
 *
 * The flat-file structure no longer needs routing — each public/*.php file
 * handles its own logic. This class is kept for shared boot logic that may
 * be useful in CLI scripts, jobs, or future extensions.
 *
 * Usage (optional, in CLI scripts):
 *   define('BASE_PATH', dirname(__DIR__, 2));
 *   require BASE_PATH . '/vendor/autoload.php';
 *   (new \Socius\Core\Application(BASE_PATH))->boot();
 */
class Application
{
    public function __construct(private readonly string $basePath)
    {
    }

    /**
     * Load environment, apply runtime settings, and initialise helpers.
     */
    public function boot(): void
    {
        Config::loadEnv($this->basePath . '/.env');

        $tz = (string) Config::get('app.timezone', 'Europe/Rome');
        date_default_timezone_set($tz);

        $debug = (bool) Config::get('app.debug', false);
        error_reporting($debug ? E_ALL : E_ALL & ~E_DEPRECATED & ~E_STRICT);
        ini_set('display_errors', $debug ? '1' : '0');
        ini_set('log_errors', '1');

        $helpersFile = $this->basePath . '/app/helpers.php';
        if (is_file($helpersFile)) {
            require_once $helpersFile;
        }
    }
}
