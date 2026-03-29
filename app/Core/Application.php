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

namespace Socius\Core;

/**
 * Application kernel.
 *
 * Bootstraps the framework and handles the full request lifecycle:
 *   1. Load environment variables from .env
 *   2. Apply PHP runtime settings (timezone, error reporting)
 *   3. Build a Router and register application routes from config/routes.php
 *   4. Dispatch the incoming Request
 *   5. Send the Response
 *
 * Usage in public/index.php:
 *
 *   define('BASE_PATH', dirname(__DIR__));
 *   require BASE_PATH . '/vendor/autoload.php';
 *   (new \Socius\Core\Application(BASE_PATH))->run();
 */
class Application
{
    private Router $router;

    public function __construct(private readonly string $basePath)
    {
        $this->boot();
        $this->router = new Router();
    }

    // -------------------------------------------------------------------------
    // Public API
    // -------------------------------------------------------------------------

    /**
     * Handle the incoming HTTP request and send the response.
     */
    public function run(): void
    {
        $this->registerRoutes();

        $request  = Request::fromGlobals();
        $response = $this->router->dispatch($request);
        $response->send();
    }

    /**
     * Expose the Router so routes can be registered externally if needed.
     */
    public function getRouter(): Router
    {
        return $this->router;
    }

    // -------------------------------------------------------------------------
    // Bootstrap
    // -------------------------------------------------------------------------

    /**
     * Load the .env file and apply runtime PHP configuration.
     * Must be called before any Config::get() or database access.
     */
    private function boot(): void
    {
        Config::loadEnv($this->basePath . '/.env');

        $tz = (string) Config::get('app.timezone', 'Europe/Rome');
        date_default_timezone_set($tz);

        $debug = (bool) Config::get('app.debug', false);
        error_reporting($debug ? E_ALL : E_ALL & ~E_DEPRECATED & ~E_STRICT);
        ini_set('display_errors', $debug ? '1' : '0');
        ini_set('log_errors', '1');
    }

    /**
     * Load route definitions from config/routes.php.
     *
     * The file receives $router as a local variable so it can call
     * $router->get(...) and $router->post(...) directly.
     */
    private function registerRoutes(): void
    {
        $router    = $this->router;
        $routeFile = $this->basePath . '/config/routes.php';

        if (is_file($routeFile)) {
            require $routeFile;
        }
    }
}
