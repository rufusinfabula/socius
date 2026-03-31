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

namespace Socius\Models;

/**
 * Key-value settings store backed by the `settings` table.
 *
 * Keys use dot notation: the first segment is the group, e.g. `association.name`.
 * All values are stored as strings; type coercion is the caller's responsibility.
 */
class Setting extends BaseModel
{
    /** @var array<string, string> In-request value cache */
    private static array $cache = [];
    private static bool  $loaded = false;

    // =========================================================================
    // Read
    // =========================================================================

    /**
     * Return the value for $key, or $default when the key is absent.
     */
    public static function get(string $key, mixed $default = null): mixed
    {
        self::preload();
        return array_key_exists($key, self::$cache) ? self::$cache[$key] : $default;
    }

    /**
     * Return all key-value pairs belonging to $group.
     *
     * @return array<string, string>
     */
    public static function getGroup(string $group): array
    {
        $rows = self::db()->fetchAll(
            'SELECT `key`, `value` FROM `settings` WHERE `group` = ? ORDER BY `key` ASC',
            [$group]
        );
        $result = [];
        foreach ($rows as $row) {
            $result[(string) $row['key']] = (string) $row['value'];
        }
        return $result;
    }

    /**
     * Return all settings organised by group.
     *
     * @return array<string, array<string, string>>
     */
    public static function getAllGroups(): array
    {
        $rows = self::db()->fetchAll(
            'SELECT `key`, `value`, `group` FROM `settings` ORDER BY `group` ASC, `key` ASC'
        );
        $result = [];
        foreach ($rows as $row) {
            $result[(string) $row['group']][(string) $row['key']] = (string) $row['value'];
        }
        return $result;
    }

    // =========================================================================
    // Write
    // =========================================================================

    /**
     * Insert or update a single key.
     */
    public static function set(string $key, mixed $value): bool
    {
        $val = is_bool($value) ? ($value ? 'true' : 'false') : (string) $value;

        self::db()->query(
            'INSERT INTO `settings` (`key`, `value`) VALUES (?, ?)
             ON DUPLICATE KEY UPDATE `value` = VALUES(`value`)',
            [$key, $val]
        );

        self::$cache[$key] = $val;
        return true;
    }

    /**
     * Insert or update multiple keys inside a single transaction.
     *
     * @param array<string, mixed> $keyValues
     */
    public static function setMultiple(array $keyValues): bool
    {
        self::db()->transaction(function () use ($keyValues): void {
            foreach ($keyValues as $key => $value) {
                self::set((string) $key, $value);
            }
        });
        return true;
    }

    // =========================================================================
    // Password encryption helpers (uses APP_KEY via openssl AES-256-CBC)
    // =========================================================================

    public static function encryptPassword(string $plain): string
    {
        if ($plain === '') {
            return '';
        }
        $key = substr(hash('sha256', (string) \Socius\Core\Config::get('app.key', 'socius_fallback')), 0, 32);
        $iv  = random_bytes(16);
        $enc = (string) openssl_encrypt($plain, 'AES-256-CBC', $key, 0, $iv);
        return base64_encode($iv . $enc);
    }

    public static function decryptPassword(string $cipher): string
    {
        if ($cipher === '') {
            return '';
        }
        try {
            $key  = substr(hash('sha256', (string) \Socius\Core\Config::get('app.key', 'socius_fallback')), 0, 32);
            $data = (string) base64_decode($cipher, true);
            if (strlen($data) < 17) {
                return '';
            }
            $iv  = substr($data, 0, 16);
            $enc = substr($data, 16);
            return (string) openssl_decrypt($enc, 'AES-256-CBC', $key, 0, $iv);
        } catch (\Throwable) {
            return '';
        }
    }

    // =========================================================================
    // Internal
    // =========================================================================

    private static function preload(): void
    {
        if (self::$loaded) {
            return;
        }
        $rows = self::db()->fetchAll('SELECT `key`, `value` FROM `settings`');
        foreach ($rows as $row) {
            self::$cache[(string) $row['key']] = (string) $row['value'];
        }
        self::$loaded = true;
    }
}
