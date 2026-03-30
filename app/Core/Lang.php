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
 * Minimal i18n helper.
 *
 * Translation files live in lang/{locale}/{file}.php and must return an array.
 * Keys use dot notation: the first segment is the file name, the rest is the
 * path inside the returned array.
 *
 *   __('auth.login')          →  lang/it/auth.php  →  $strings['login']
 *   __('auth.errors.invalid') →  lang/it/auth.php  →  $strings['errors']['invalid']
 *
 * Placeholders use :name syntax:
 *   __('auth.attempts_left', ['count' => 3])
 *   →  "Tentativi rimasti: 3"
 */
class Lang
{
    private static string $locale = 'it';
    /** @var array<string, array<string, mixed>> */
    private static array $cache = [];

    // -------------------------------------------------------------------------
    // Configuration
    // -------------------------------------------------------------------------

    public static function setLocale(string $locale): void
    {
        self::$locale = $locale;
    }

    public static function getLocale(): string
    {
        return self::$locale;
    }

    // -------------------------------------------------------------------------
    // Translation
    // -------------------------------------------------------------------------

    /**
     * Retrieve a translated string.
     *
     * @param  string               $key     Dot-separated key, e.g. 'auth.login'
     * @param  array<string, mixed> $replace Placeholder substitutions, e.g. ['name' => 'Mario']
     * @param  string               $default Returned when the key is not found
     */
    public static function get(string $key, array $replace = [], string $default = ''): string
    {
        [$file, $rest] = array_pad(explode('.', $key, 2), 2, null);

        $strings = self::load((string) $file);

        if ($rest === null) {
            $value = $strings[$file] ?? $default;
        } else {
            $value = self::dig($strings, (string) $rest, $default);
        }

        $value = is_string($value) ? $value : $default;

        // Replace :placeholder tokens
        foreach ($replace as $k => $v) {
            $value = str_replace(':' . $k, (string) $v, $value);
        }

        return $value;
    }

    // -------------------------------------------------------------------------
    // Internal helpers
    // -------------------------------------------------------------------------

    /**
     * Load and cache a language file.
     *
     * @return array<string, mixed>
     */
    private static function load(string $file): array
    {
        $cacheKey = self::$locale . '.' . $file;

        if (!isset(self::$cache[$cacheKey])) {
            $base = defined('BASE_PATH') ? BASE_PATH : dirname(__DIR__, 2);
            $path = $base . '/lang/' . self::$locale . '/' . $file . '.php';

            if (!is_file($path)) {
                // Fall back to 'it' if the requested locale file doesn't exist
                $path = $base . '/lang/it/' . $file . '.php';
            }

            self::$cache[$cacheKey] = is_file($path) ? (array) require $path : [];
        }

        return self::$cache[$cacheKey];
    }

    /**
     * Walk a nested array following dot-separated key segments.
     */
    private static function dig(array $data, string $key, string $default): string
    {
        foreach (explode('.', $key) as $segment) {
            if (!is_array($data) || !array_key_exists($segment, $data)) {
                return $default;
            }
            $data = $data[$segment];
        }

        return is_string($data) ? $data : $default;
    }

    /** Clear cache — for tests only. */
    public static function flush(): void
    {
        self::$cache = [];
    }
}
