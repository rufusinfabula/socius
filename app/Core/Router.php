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
 * Minimal HTTP router.
 *
 * Supports GET and POST verbs. Named URL parameters are declared with
 * curly-brace syntax: /members/{id}/edit
 * Captured values are passed to the controller action as a $params array.
 *
 * Usage:
 *   $router = new Router();
 *   $router->get('/', HomeController::class, 'index');
 *   $router->get('/members/{id}', MemberController::class, 'show');
 *   $router->post('/members', MemberController::class, 'store', ['auth', 'csrf']);
 *   $response = $router->dispatch(Request::fromGlobals());
 *   $response->send();
 */
class Router
{
    /**
     * Registered routes grouped by HTTP method.
     *
     * Structure:
     *   ['GET' => ['/pattern' => ['controller' => FQCN, 'action' => method, 'middleware' => []]]]
     *
     * @var array<string, array<string, array{controller: string, action: string, middleware: list<string>}>>
     */
    private array $routes = [];

    // -------------------------------------------------------------------------
    // Route registration
    // -------------------------------------------------------------------------

    /**
     * Register a route for GET requests.
     *
     * @param string   $pattern    URI pattern, e.g. '/members/{id}'
     * @param string   $controller Fully-qualified class name
     * @param string   $action     Method name to call on the controller
     * @param string[] $middleware Middleware identifiers to run before the action
     */
    public function get(string $pattern, string $controller, string $action, array $middleware = []): void
    {
        $this->register('GET', $pattern, $controller, $action, $middleware);
    }

    /**
     * Register a route for POST requests.
     *
     * @param string   $pattern    URI pattern
     * @param string   $controller Fully-qualified class name
     * @param string   $action     Method name
     * @param string[] $middleware Middleware identifiers
     */
    public function post(string $pattern, string $controller, string $action, array $middleware = []): void
    {
        $this->register('POST', $pattern, $controller, $action, $middleware);
    }

    private function register(
        string $method,
        string $pattern,
        string $controller,
        string $action,
        array  $middleware
    ): void {
        $this->routes[$method][$pattern] = [
            'controller' => $controller,
            'action'     => $action,
            'middleware' => $middleware,
        ];
    }

    // -------------------------------------------------------------------------
    // Dispatching
    // -------------------------------------------------------------------------

    /**
     * Match the incoming request against registered routes and invoke the
     * matching controller action.
     *
     * Returns a 404 Response when no route matches.
     *
     * @throws \RuntimeException when the matched controller or action does not exist.
     */
    public function dispatch(Request $request): Response
    {
        $method = $request->method();
        $path   = $this->normalizePath($request->path());

        foreach ($this->routes[$method] ?? [] as $pattern => $route) {
            $params = $this->matchPattern($pattern, $path);

            if ($params !== null) {
                return $this->invoke($route, $params, $request);
            }
        }

        return (new Response())->setStatus(404);
    }

    // -------------------------------------------------------------------------
    // Pattern matching
    // -------------------------------------------------------------------------

    /**
     * Match $path against a route $pattern.
     *
     * Curly-brace segments ({id}, {slug}) become named capture groups. All
     * other characters are treated as regex literals via preg_quote.
     *
     * Returns an array of named URL parameters on success, or null on failure.
     *
     * @return array<string, string>|null
     */
    private function matchPattern(string $pattern, string $path): ?array
    {
        // Unique sentinel used to protect {param} tokens from preg_quote
        $sentinel = "\x00P\x00";
        $names    = [];

        // Step 1: replace {param} with sentinel tokens, collect names
        $tokenised = preg_replace_callback(
            '/\{([a-zA-Z_][a-zA-Z0-9_]*)\}/',
            function (array $m) use ($sentinel, &$names): string {
                $names[] = $m[1];
                return $sentinel . count($names) . $sentinel;
            },
            $pattern
        );

        // Step 2: quote all literal characters
        $quoted = preg_quote((string) $tokenised, '#');

        // Step 3: replace the (now-quoted) sentinel tokens with named capture groups
        $i     = 0;
        $regex = preg_replace_callback(
            '/' . preg_quote($sentinel, '/') . '(\d+)' . preg_quote($sentinel, '/') . '/',
            function () use (&$i, $names): string {
                return '(?P<' . $names[$i++] . '>[^/]+)';
            },
            $quoted
        );

        $regex = '#^' . $regex . '$#u';

        if (!preg_match($regex, $path, $matches)) {
            return null;
        }

        // Return only the named (string-keyed) captures
        return array_filter($matches, fn($k) => is_string($k), ARRAY_FILTER_USE_KEY);
    }

    // -------------------------------------------------------------------------
    // Controller invocation
    // -------------------------------------------------------------------------

    /**
     * Instantiate the controller and call the action.
     *
     * The action signature must be:
     *   public function action(Request $request, array $params): Response
     *
     * @param  array<string, mixed>  $route  Route definition
     * @param  array<string, string> $params Named URL parameters
     */
    private function invoke(array $route, array $params, Request $request): Response
    {
        $class = $route['controller'];

        if (!class_exists($class)) {
            throw new \RuntimeException("Controller class not found: {$class}");
        }

        $controller = new $class();
        $action     = $route['action'];

        if (!method_exists($controller, $action)) {
            throw new \RuntimeException("Action not found: {$class}::{$action}");
        }

        return $controller->{$action}($request, $params);
    }

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    /**
     * Extract and normalise the path component of a URI.
     * Trailing slashes are removed (except for the root '/').
     */
    private function normalizePath(string $uri): string
    {
        $path = (string) (parse_url($uri, PHP_URL_PATH) ?? '/');
        $path = '/' . trim($path, '/');
        return $path === '/' ? '/' : rtrim($path, '/');
    }
}
