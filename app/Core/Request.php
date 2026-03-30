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
 * Wraps the current HTTP request.
 *
 * Instantiate from PHP superglobals with Request::fromGlobals(), or inject
 * custom arrays in the constructor for unit testing.
 *
 * Usage:
 *   $req  = Request::fromGlobals();
 *   $name = $req->post('name');
 *   $id   = $req->get('id');
 *   $file = $req->file('avatar');
 *   if ($req->isPost() && $req->isAjax()) { ... }
 */
class Request
{
    /** @var array<string, mixed> */
    private array $queryParams;
    /** @var array<string, mixed> */
    private array $bodyParams;
    /** @var array<string, mixed> */
    private array $uploadedFiles;
    /** @var array<string, mixed> */
    private array $serverParams;
    /** @var array<string, mixed> */
    private array $cookieParams;
    /** @var array<string, mixed>|null  Lazily-parsed JSON body */
    private ?array $jsonBody = null;

    public function __construct(
        array $queryParams   = [],
        array $bodyParams    = [],
        array $uploadedFiles = [],
        array $serverParams  = [],
        array $cookieParams  = []
    ) {
        $this->queryParams   = $queryParams;
        $this->bodyParams    = $bodyParams;
        $this->uploadedFiles = $uploadedFiles;
        $this->serverParams  = $serverParams;
        $this->cookieParams  = $cookieParams;
    }

    /**
     * Build a Request from PHP's superglobals.
     */
    public static function fromGlobals(): self
    {
        return new self($_GET, $_POST, $_FILES, $_SERVER, $_COOKIE);
    }

    // -------------------------------------------------------------------------
    // Input accessors
    // -------------------------------------------------------------------------

    /**
     * Retrieve a value from the query string ($_GET).
     */
    public function get(string $key, mixed $default = null): mixed
    {
        return $this->queryParams[$key] ?? $default;
    }

    /**
     * Retrieve a value from the POST body or, for JSON requests, from the
     * parsed JSON body.
     */
    public function post(string $key, mixed $default = null): mixed
    {
        if ($this->isJson()) {
            return $this->json()[$key] ?? $default;
        }

        return $this->bodyParams[$key] ?? $default;
    }

    /**
     * Retrieve an uploaded file descriptor from $_FILES.
     *
     * @return array<string, mixed>|null
     */
    public function file(string $key): ?array
    {
        return $this->uploadedFiles[$key] ?? null;
    }

    /**
     * Retrieve a cookie value.
     */
    public function cookie(string $key, mixed $default = null): mixed
    {
        return $this->cookieParams[$key] ?? $default;
    }

    /**
     * Parse the raw request body as JSON and return it as an associative array.
     * Returns an empty array if the body is missing or not valid JSON.
     */
    public function json(): array
    {
        if ($this->jsonBody === null) {
            $raw            = file_get_contents('php://input') ?: '{}';
            $this->jsonBody = (array) (json_decode($raw, true) ?? []);
        }

        return $this->jsonBody;
    }

    // -------------------------------------------------------------------------
    // Request metadata
    // -------------------------------------------------------------------------

    /**
     * Return the HTTP method in uppercase (GET, POST, PUT, PATCH, DELETE, …).
     */
    public function method(): string
    {
        return strtoupper($this->serverParams['REQUEST_METHOD'] ?? 'GET');
    }

    /**
     * Return the full request URI, including query string.
     */
    public function uri(): string
    {
        return $this->serverParams['REQUEST_URI'] ?? '/';
    }

    /**
     * Return the normalised request path for routing.
     *
     * Priority:
     *   1. Query-string mode  — $_GET['route'] is present.
     *      Works on any hosting without server configuration.
     *      e.g. index.php?route=soci/123  →  /soci/123
     *
     *   2. Clean-URL mode — REQUEST_URI path is used.
     *      Requires mod_rewrite (Apache) or try_files (Nginx).
     *      e.g. /soci/123  →  /soci/123
     *
     * The Router calls this method instead of uri() so that both
     * modes resolve to the same pattern-matching path.
     */
    public function path(): string
    {
        // Mode 1: query-string routing (?route=...)
        $route = trim((string) ($this->queryParams['route'] ?? ''), '/');
        if ($route !== '') {
            return '/' . $route;
        }

        // Mode 2: clean URL — extract path from REQUEST_URI
        $path = parse_url($this->serverParams['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?? '/';
        return (string) $path;
    }

    /**
     * Return the client IP address.
     * Trusts X-Forwarded-For as first candidate; applications behind a trusted
     * reverse proxy should strip untrusted forwarded headers at the proxy level.
     */
    public function ip(): string
    {
        return $this->serverParams['HTTP_X_FORWARDED_FOR']
            ?? $this->serverParams['REMOTE_ADDR']
            ?? '0.0.0.0';
    }

    // -------------------------------------------------------------------------
    // Request type checks
    // -------------------------------------------------------------------------

    /**
     * Returns true when the HTTP method is POST.
     */
    public function isPost(): bool
    {
        return $this->method() === 'POST';
    }

    /**
     * Returns true when the request was sent via XMLHttpRequest (AJAX).
     */
    public function isAjax(): bool
    {
        return ($this->serverParams['HTTP_X_REQUESTED_WITH'] ?? '') === 'XMLHttpRequest';
    }

    /**
     * Returns true when the Content-Type header signals a JSON body.
     */
    public function isJson(): bool
    {
        return str_contains($this->serverParams['CONTENT_TYPE'] ?? '', 'application/json');
    }
}
