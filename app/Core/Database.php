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

use PDO;
use PDOStatement;

/**
 * Thin PDO wrapper providing a singleton MySQL connection and CRUD helpers.
 *
 * All queries use prepared statements; user-supplied values are always bound
 * as parameters and never interpolated directly into SQL strings.
 *
 * Usage:
 *   $db  = Database::getInstance();
 *   $row = $db->fetch('SELECT * FROM members WHERE id = ?', [$id]);
 *   $id  = $db->insert('members', ['name' => 'Mario', 'email' => 'mario@example.com']);
 *   $db->update('members', ['email' => 'new@example.com'], ['id' => 42]);
 *   $db->delete('members', ['id' => 42]);
 */
class Database
{
    private static ?self $instance = null;
    private PDO $pdo;

    private function __construct(array $config)
    {
        $dsn = sprintf(
            'mysql:host=%s;port=%d;dbname=%s;charset=%s',
            $config['host']     ?? '127.0.0.1',
            (int) ($config['port'] ?? 3306),
            $config['database'] ?? '',
            $config['charset']  ?? 'utf8mb4'
        );

        $options = $config['options'] ?? [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];

        $this->pdo = new PDO(
            $dsn,
            $config['username'] ?? '',
            $config['password'] ?? '',
            $options
        );
    }

    // -------------------------------------------------------------------------
    // Singleton
    // -------------------------------------------------------------------------

    /**
     * Return the shared Database instance, creating it on the first call.
     *
     * @throws \RuntimeException if configuration is missing or the connection fails.
     */
    public static function getInstance(): self
    {
        if (self::$instance === null) {
            $config = Config::get('database');

            if (!is_array($config) || empty($config['database'])) {
                throw new \RuntimeException(
                    'Database configuration is missing or incomplete. '
                    . 'Check config/database.php and your .env file.'
                );
            }

            self::$instance = new self($config);
        }

        return self::$instance;
    }

    /**
     * Inject an alternative instance — useful for tests.
     */
    public static function setInstance(self $instance): void
    {
        self::$instance = $instance;
    }

    // -------------------------------------------------------------------------
    // Query helpers
    // -------------------------------------------------------------------------

    /**
     * Prepare and execute an arbitrary SQL statement.
     * Returns the executed PDOStatement so the caller can call rowCount(), etc.
     */
    public function query(string $sql, array $params = []): PDOStatement
    {
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    }

    /**
     * Execute a SELECT and return the first matching row, or false when empty.
     *
     * @return array<string, mixed>|false
     */
    public function fetch(string $sql, array $params = []): array|false
    {
        return $this->query($sql, $params)->fetch();
    }

    /**
     * Execute a SELECT and return all matching rows.
     *
     * @return array<int, array<string, mixed>>
     */
    public function fetchAll(string $sql, array $params = []): array
    {
        return $this->query($sql, $params)->fetchAll();
    }

    // -------------------------------------------------------------------------
    // CRUD helpers
    // -------------------------------------------------------------------------

    /**
     * INSERT a single row into $table.
     *
     * @param  array<string, mixed> $data  Column → value pairs
     * @return string|false  Last insert ID on success, false on failure
     */
    public function insert(string $table, array $data): string|false
    {
        $columns      = implode(', ', array_keys($data));
        $placeholders = implode(', ', array_fill(0, count($data), '?'));

        $this->query(
            "INSERT INTO `{$table}` ({$columns}) VALUES ({$placeholders})",
            array_values($data)
        );

        return $this->pdo->lastInsertId();
    }

    /**
     * UPDATE rows in $table matching all $where conditions.
     *
     * @param  array<string, mixed> $data   Columns to update
     * @param  array<string, mixed> $where  WHERE conditions (AND-ed together)
     * @return int  Number of affected rows
     */
    public function update(string $table, array $data, array $where): int
    {
        $set  = implode(', ', array_map(fn($k) => "`{$k}` = ?", array_keys($data)));
        $cond = implode(' AND ', array_map(fn($k) => "`{$k}` = ?", array_keys($where)));

        return $this->query(
            "UPDATE `{$table}` SET {$set} WHERE {$cond}",
            [...array_values($data), ...array_values($where)]
        )->rowCount();
    }

    /**
     * DELETE rows from $table matching all $where conditions.
     *
     * @param  array<string, mixed> $where  WHERE conditions (AND-ed together)
     * @return int  Number of affected rows
     */
    public function delete(string $table, array $where): int
    {
        $cond = implode(' AND ', array_map(fn($k) => "`{$k}` = ?", array_keys($where)));

        return $this->query(
            "DELETE FROM `{$table}` WHERE {$cond}",
            array_values($where)
        )->rowCount();
    }

    // -------------------------------------------------------------------------
    // Transactions
    // -------------------------------------------------------------------------

    public function beginTransaction(): void { $this->pdo->beginTransaction(); }
    public function commit(): void           { $this->pdo->commit(); }
    public function rollBack(): void         { $this->pdo->rollBack(); }

    /**
     * Execute $callback inside a transaction.
     * Automatically rolls back and re-throws if an exception is raised.
     */
    public function transaction(callable $callback): mixed
    {
        $this->beginTransaction();
        try {
            $result = $callback($this);
            $this->commit();
            return $result;
        } catch (\Throwable $e) {
            $this->rollBack();
            throw $e;
        }
    }

    /**
     * Expose the raw PDO instance for operations not covered by this wrapper.
     */
    public function getPdo(): PDO
    {
        return $this->pdo;
    }
}
