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

namespace Socius\Models;

/**
 * System user (staff / admin account).
 * Distinct from Member, which represents an association member.
 */
class User extends BaseModel
{
    /**
     * Find a user by email address.
     *
     * @return array<string, mixed>|null
     */
    public static function findByEmail(string $email): ?array
    {
        $row = self::db()->fetch(
            'SELECT * FROM users WHERE email = ? LIMIT 1',
            [$email]
        );
        return $row ?: null;
    }

    /**
     * Find a user by primary key.
     *
     * @return array<string, mixed>|null
     */
    public static function findById(int $id): ?array
    {
        $row = self::db()->fetch(
            'SELECT * FROM users WHERE id = ? LIMIT 1',
            [$id]
        );
        return $row ?: null;
    }

    /**
     * Verify a plain-text password against a stored bcrypt hash.
     */
    public static function verifyPassword(string $password, string $hash): bool
    {
        return password_verify($password, $hash);
    }

    /**
     * Update the password hash for a user (bcrypt, cost 12).
     */
    public static function updatePassword(int $id, string $newPassword): void
    {
        $hash = password_hash($newPassword, PASSWORD_BCRYPT, ['cost' => 12]);
        self::db()->update('users', ['password_hash' => $hash], ['id' => $id]);
    }

    /**
     * Persist a password-reset token and its expiry time.
     *
     * @param string $expiresAt MySQL DATETIME string, e.g. '2026-03-30 15:00:00'
     */
    public static function saveResetToken(int $id, string $token, string $expiresAt): void
    {
        self::db()->update('users', [
            'password_reset_token'   => $token,
            'password_reset_expires' => $expiresAt,
        ], ['id' => $id]);
    }

    /**
     * Find a user by a non-expired password-reset token.
     *
     * @return array<string, mixed>|null
     */
    public static function findByResetToken(string $token): ?array
    {
        $row = self::db()->fetch(
            'SELECT * FROM users
              WHERE password_reset_token = ?
                AND password_reset_expires > NOW()
              LIMIT 1',
            [$token]
        );
        return $row ?: null;
    }

    /**
     * Clear the password-reset token after a successful reset.
     */
    public static function clearResetToken(int $id): void
    {
        self::db()->update('users', [
            'password_reset_token'   => null,
            'password_reset_expires' => null,
        ], ['id' => $id]);
    }

    /**
     * Record the timestamp and IP of the last successful login.
     */
    public static function updateLastLogin(int $id, string $ip): void
    {
        self::db()->update('users', [
            'last_login_at' => date('Y-m-d H:i:s'),
            'last_login_ip' => $ip,
        ], ['id' => $id]);
    }
}
