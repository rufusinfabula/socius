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
 * Represents an HTTP response to be sent to the client.
 *
 * Methods are chainable; nothing is sent to the client until send() is called.
 *
 * Usage:
 *   (new Response())->json(['ok' => true])->send();
 *   (new Response())->redirect('/login')->send();
 *   (new Response())->view('home/index', ['user' => $user])->send();
 */
class Response
{
    private int    $status  = 200;
    private array  $headers = [];
    private string $body    = '';

    // -------------------------------------------------------------------------
    // Builder methods (all return $this for chaining)
    // -------------------------------------------------------------------------

    /**
     * Set the HTTP status code.
     */
    public function setStatus(int $code): self
    {
        $this->status = $code;
        return $this;
    }

    /**
     * Add or overwrite a single response header.
     */
    public function header(string $name, string $value): self
    {
        $this->headers[$name] = $value;
        return $this;
    }

    /**
     * Encode $data as JSON and set the appropriate Content-Type header.
     *
     * @param  mixed $data   Any JSON-serialisable value
     * @param  int   $status HTTP status code (default 200)
     * @throws \JsonException on serialisation failure
     */
    public function json(mixed $data, int $status = 200): self
    {
        $this->status                  = $status;
        $this->headers['Content-Type'] = 'application/json; charset=utf-8';
        $this->body                    = json_encode(
            $data,
            JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR
        );
        return $this;
    }

    /**
     * Issue an HTTP redirect.
     *
     * @param  string $url    Destination URL (absolute or relative)
     * @param  int    $status 301 (permanent), 302 (found/temporary), 303, 307, 308
     */
    public function redirect(string $url, int $status = 302): self
    {
        $this->status              = $status;
        $this->headers['Location'] = $url;
        return $this;
    }

    /**
     * Render a PHP view template and capture its output as the response body.
     *
     * $template is relative to app/Views/, without the .php extension:
     *   $response->view('home/index', ['title' => 'Welcome'])
     *   → app/Views/home/index.php
     *
     * All keys in $data are extracted as local variables inside the template.
     *
     * @param  string               $template Relative template path (no extension)
     * @param  array<string, mixed> $data     Variables made available in the template
     * @param  int                  $status   HTTP status code (default 200)
     * @throws \RuntimeException if the template file cannot be found
     */
    public function view(string $template, array $data = [], int $status = 200): self
    {
        $base = defined('BASE_PATH') ? BASE_PATH : dirname(__DIR__, 2);
        $file = $base . '/app/Views/' . ltrim($template, '/') . '.php';

        if (!is_file($file)) {
            throw new \RuntimeException("View template not found: {$template}");
        }

        extract($data, EXTR_SKIP);

        ob_start();
        require $file;
        $this->body                    = ob_get_clean() ?: '';
        $this->status                  = $status;
        $this->headers['Content-Type'] = 'text/html; charset=utf-8';

        return $this;
    }

    // -------------------------------------------------------------------------
    // Sending
    // -------------------------------------------------------------------------

    /**
     * Send the status code, headers, and body to the client.
     * Call this exactly once at the end of the request lifecycle.
     */
    public function send(): void
    {
        http_response_code($this->status);

        foreach ($this->headers as $name => $value) {
            header("{$name}: {$value}");
        }

        echo $this->body;
    }

    // -------------------------------------------------------------------------
    // Accessors (for middleware and tests)
    // -------------------------------------------------------------------------

    public function getStatus(): int    { return $this->status; }
    public function getBody(): string   { return $this->body; }
    /** @return array<string, string> */
    public function getHeaders(): array { return $this->headers; }
}
