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
 * Configuration and .env loader.
 *
 * Config::loadEnv(BASE_PATH . '/.env');
 * Config::get('database.host');          // reads config/database.php → 'host'
 * Config::get('app.name', 'Socius');    // with fallback default
 * Config::get('app');                   // returns the full config array
 */
class Config
{
    /** @var array<string, mixed> Cached config arrays, keyed by file name */
    private static array $cache = [];
    private static bool  $envLoaded = false;

    // -------------------------------------------------------------------------
    // .env loading
    // -------------------------------------------------------------------------

    /**
     * Parse a .env file and populate $_ENV / $_SERVER / putenv().
     *
     * Rules:
     *  - Lines starting with # are comments and are ignored.
     *  - Values surrounded by " or ' have the quotes stripped.
     *  - Inline comments (value # remark) are stripped.
     *  - Variables already present in the environment are never overwritten.
     *  - A missing .env file is tolerated (hosting may inject env vars directly).
     */
    public static function loadEnv(string $path): void
    {
        if (self::$envLoaded) {
            return;
        }

        self::$envLoaded = true; // mark before reading to prevent recursive calls

        if (!is_file($path)) {
            return;
        }

        $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

        foreach ($lines as $line) {
            $line = trim($line);

            if ($line === '' || str_starts_with($line, '#') || !str_contains($line, '=')) {
                continue;
            }

            [$key, $value] = explode('=', $line, 2);
            $key   = trim($key);
            $value = trim($value);

            // Strip surrounding quotes (single or double)
            if (
                strlen($value) >= 2
                && (
                    ($value[0] === '"'  && $value[-1] === '"')
                    || ($value[0] === "'" && $value[-1] === "'")
                )
            ) {
                $value = substr($value, 1, -1);
            }

            // Strip inline comment: everything after first " #"
            if (str_contains($value, ' #')) {
                $value = rtrim((string) explode(' #', $value, 2)[0]);
            }

            // Never overwrite variables already in the environment
            if (!array_key_exists($key, $_ENV)) {
                $_ENV[$key]    = $value;
                $_SERVER[$key] = $value;
                putenv("{$key}={$value}");
            }
        }
    }

    // -------------------------------------------------------------------------
    // Config file access
    // -------------------------------------------------------------------------

    /**
     * Retrieve a configuration value using dot notation.
     *
     * The first segment is the name of a file in config/:
     *   Config::get('database.host')  →  config/database.php  →  $cfg['host']
     *   Config::get('app.debug')      →  config/app.php       →  $cfg['debug']
     *
     * Nested access:
     *   Config::get('payment.paypal.mode')
     *
     * @param  string $key     Dot-separated path
     * @param  mixed  $default Value returned when the key is absent
     */
    public static function get(string $key, mixed $default = null): mixed
    {
        [$file, $rest] = array_pad(explode('.', $key, 2), 2, null);

        if (!isset(self::$cache[$file])) {
            $base = defined('BASE_PATH') ? BASE_PATH : dirname(__DIR__, 2);
            $path = $base . '/config/' . $file . '.php';

            if (!is_file($path)) {
                return $default;
            }

            self::$cache[$file] = require $path;
        }

        if ($rest === null) {
            return self::$cache[$file] ?? $default;
        }

        return self::dig(self::$cache[$file], $rest, $default);
    }

    /**
     * Walk a nested array following dot-separated $key segments.
     */
    private static function dig(mixed $data, string $key, mixed $default): mixed
    {
        foreach (explode('.', $key) as $segment) {
            if (!is_array($data) || !array_key_exists($segment, $data)) {
                return $default;
            }
            $data = $data[$segment];
        }

        return $data;
    }

    /**
     * Clear the in-memory cache and allow .env to be reloaded.
     * Intended for use in tests only.
     */
    public static function flush(): void
    {
        self::$cache     = [];
        self::$envLoaded = false;
    }
}
