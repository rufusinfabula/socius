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
 * Abstract base for all middleware.
 *
 * Concrete classes must implement handle(). They can either:
 *   - Call $next($request) to pass control down the pipeline, or
 *   - Return a Response directly to short-circuit the remaining pipeline.
 *
 * Static helpers for CSRF token management and authentication checks are
 * provided here so all middleware and controllers can share them without
 * an extra dependency.
 *
 * Usage (concrete class):
 *
 *   class AuthMiddleware extends Middleware
 *   {
 *       public function handle(Request $request, callable $next): Response
 *       {
 *           if (!Middleware::isAuthenticated()) {
 *               return (new Response())->redirect('/login');
 *           }
 *           return $next($request);
 *       }
 *   }
 *
 * Usage (CSRF in a form template):
 *   <input type="hidden" name="_csrf_token" value="<?= Middleware::csrfToken() ?>">
 */
abstract class Middleware
{
    /**
     * Process the request.
     *
     * @param  Request  $request  The current HTTP request
     * @param  callable $next     fn(Request): Response — the next handler
     * @return Response
     */
    abstract public function handle(Request $request, callable $next): Response;

    // =========================================================================
    // CSRF token helpers
    // =========================================================================

    /**
     * Return the current CSRF token, generating and storing one in the session
     * if it does not already exist.
     *
     * Embed in every HTML form:
     *   <input type="hidden" name="_csrf_token" value="<?= Middleware::csrfToken() ?>">
     */
    public static function csrfToken(): string
    {
        self::ensureSession();

        if (empty($_SESSION['_csrf_token'])) {
            $_SESSION['_csrf_token'] = bin2hex(random_bytes(32));
        }

        return $_SESSION['_csrf_token'];
    }

    /**
     * Verify that the CSRF token submitted with the request matches the one
     * stored in the session.
     *
     * Accepts the token from (in order of precedence):
     *   1. POST field      _csrf_token
     *   2. JSON body key   _csrf_token
     *   3. HTTP header     X-CSRF-Token
     *
     * Uses hash_equals() to prevent timing attacks.
     */
    public static function verifyCsrfToken(Request $request): bool
    {
        self::ensureSession();

        $sessionToken = $_SESSION['_csrf_token'] ?? '';

        if ($sessionToken === '') {
            return false;
        }

        // 1. POST field
        $submitted = (string) $request->post('_csrf_token', '');

        // 2. JSON body
        if ($submitted === '' && $request->isJson()) {
            $submitted = (string) ($request->json()['_csrf_token'] ?? '');
        }

        // 3. HTTP header (X-CSRF-Token)
        if ($submitted === '') {
            $submitted = (string) ($_SERVER['HTTP_X_CSRF_TOKEN'] ?? '');
        }

        return hash_equals($sessionToken, $submitted);
    }

    /**
     * Rotate the CSRF token.
     * Call after a successful POST to prevent replay attacks.
     */
    public static function regenerateCsrfToken(): string
    {
        self::ensureSession();
        $_SESSION['_csrf_token'] = bin2hex(random_bytes(32));
        return $_SESSION['_csrf_token'];
    }

    // =========================================================================
    // Auth helpers
    // =========================================================================

    /**
     * Returns true when the current session contains an authenticated user ID.
     */
    public static function isAuthenticated(): bool
    {
        self::ensureSession();
        return !empty($_SESSION['auth_user_id']);
    }

    /**
     * Return the ID of the currently authenticated user, or null if not logged in.
     */
    public static function authUserId(): ?int
    {
        self::ensureSession();
        $id = $_SESSION['auth_user_id'] ?? null;
        return $id !== null ? (int) $id : null;
    }

    /**
     * Store the authenticated user's ID in the session.
     * Regenerates the session ID and CSRF token to prevent fixation attacks.
     */
    public static function login(int $userId): void
    {
        self::ensureSession();
        session_regenerate_id(true);
        $_SESSION['auth_user_id'] = $userId;
        self::regenerateCsrfToken();
    }

    /**
     * Destroy the current session, effectively logging the user out.
     */
    public static function logout(): void
    {
        self::ensureSession();
        $_SESSION = [];

        if (ini_get('session.use_cookies')) {
            $p = session_get_cookie_params();
            setcookie(
                (string) session_name(),
                '',
                time() - 42000,
                $p['path'],
                $p['domain'],
                $p['secure'],
                $p['httponly']
            );
        }

        session_destroy();
    }

    // =========================================================================
    // Internal helpers
    // =========================================================================

    private static function ensureSession(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }
}
