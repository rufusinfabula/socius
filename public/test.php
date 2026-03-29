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

/**
 * Core smoke-test.
 * Remove or block this file in production.
 *
 * Access via: https://your-domain/test.php
 */

declare(strict_types=1);

define('BASE_PATH', dirname(__DIR__));

require BASE_PATH . '/vendor/autoload.php';

use Socius\Core\Config;
use Socius\Core\Database;
use Socius\Core\Request;
use Socius\Core\Response;
use Socius\Core\Router;

// ─────────────────────────────────────────────────────────────────────────────
// Helpers
// ─────────────────────────────────────────────────────────────────────────────

function ok(string $label): void
{
    echo "[PASS] {$label}\n";
}

function fail(string $label, string $reason): void
{
    echo "[FAIL] {$label}: {$reason}\n";
}

function section(string $title): void
{
    echo "\n=== {$title} ===\n";
}

header('Content-Type: text/plain; charset=utf-8');
echo "Socius Core smoke-test\n";
echo "PHP " . PHP_VERSION . " | " . date('Y-m-d H:i:s') . "\n";

// ─────────────────────────────────────────────────────────────────────────────
// 1. Config / .env
// ─────────────────────────────────────────────────────────────────────────────

section('Config & .env');

try {
    Config::loadEnv(BASE_PATH . '/.env');
    ok('.env loaded (or not present — tolerated)');
} catch (\Throwable $e) {
    fail('.env load', $e->getMessage());
}

try {
    $appName = Config::get('app.name', 'Socius');
    if (is_string($appName) && $appName !== '') {
        ok("app.name = \"{$appName}\"");
    } else {
        fail('Config::get app.name', 'returned empty value');
    }
} catch (\Throwable $e) {
    fail('Config::get', $e->getMessage());
}

try {
    $dbHost = Config::get('database.host', '');
    ok("database.host = \"{$dbHost}\"");
} catch (\Throwable $e) {
    fail('Config::get database.host', $e->getMessage());
}

try {
    $missing = Config::get('nonexistent.key', 'DEFAULT');
    if ($missing === 'DEFAULT') {
        ok('Config::get returns default for missing key');
    } else {
        fail('Config::get default', "expected 'DEFAULT', got '{$missing}'");
    }
} catch (\Throwable $e) {
    fail('Config::get default', $e->getMessage());
}

// ─────────────────────────────────────────────────────────────────────────────
// 2. Database
// ─────────────────────────────────────────────────────────────────────────────

section('Database');

try {
    $db = Database::getInstance();
    ok('Database::getInstance() connected');

    // Basic connectivity check
    $row = $db->fetch('SELECT 1 AS ping');
    if (is_array($row) && ($row['ping'] ?? null) === 1) {
        ok('SELECT 1 returned expected result');
    } else {
        fail('SELECT 1', 'unexpected result: ' . json_encode($row));
    }

    // PDO instance accessible
    if ($db->getPdo() instanceof \PDO) {
        ok('getPdo() returns PDO instance');
    } else {
        fail('getPdo()', 'did not return a PDO instance');
    }
} catch (\Throwable $e) {
    fail('Database connection', $e->getMessage());
    echo "       (check DB_* variables in .env)\n";
}

// ─────────────────────────────────────────────────────────────────────────────
// 3. Request
// ─────────────────────────────────────────────────────────────────────────────

section('Request');

try {
    $req = new Request(
        queryParams:   ['id' => '42'],
        bodyParams:    ['name' => 'Mario'],
        uploadedFiles: [],
        serverParams:  ['REQUEST_METHOD' => 'POST', 'REQUEST_URI' => '/members/42'],
        cookieParams:  []
    );

    assert($req->get('id') === '42',      'get() failed');
    assert($req->post('name') === 'Mario','post() failed');
    assert($req->method() === 'POST',     'method() failed');
    assert($req->uri() === '/members/42', 'uri() failed');
    assert($req->isPost() === true,       'isPost() failed');
    assert($req->isAjax() === false,      'isAjax() false-case failed');
    assert($req->file('avatar') === null, 'file() null case failed');
    assert($req->ip() === '0.0.0.0',     'ip() fallback failed');

    ok('Request constructor, get/post/method/uri/isPost/isAjax/file/ip');
} catch (\Throwable $e) {
    fail('Request', $e->getMessage());
}

// ─────────────────────────────────────────────────────────────────────────────
// 4. Response
// ─────────────────────────────────────────────────────────────────────────────

section('Response');

try {
    $res = (new Response())->json(['hello' => 'world'], 201);
    assert($res->getStatus() === 201, 'json() status failed');
    assert(str_contains($res->getHeaders()['Content-Type'] ?? '', 'application/json'), 'json() content-type failed');
    $decoded = json_decode($res->getBody(), true);
    assert(($decoded['hello'] ?? '') === 'world', 'json() body failed');
    ok('Response::json() status / Content-Type / body');
} catch (\Throwable $e) {
    fail('Response::json()', $e->getMessage());
}

try {
    $res = (new Response())->redirect('/login', 302);
    assert($res->getStatus() === 302,                          'redirect() status failed');
    assert(($res->getHeaders()['Location'] ?? '') === '/login','redirect() location failed');
    ok('Response::redirect() status / Location header');
} catch (\Throwable $e) {
    fail('Response::redirect()', $e->getMessage());
}

try {
    $res = (new Response())->setStatus(404);
    assert($res->getStatus() === 404, 'setStatus() failed');
    ok('Response::setStatus(404)');
} catch (\Throwable $e) {
    fail('Response::setStatus()', $e->getMessage());
}

// ─────────────────────────────────────────────────────────────────────────────
// 5. Router
// ─────────────────────────────────────────────────────────────────────────────

section('Router');

// Minimal inline controller for testing
class _TestController
{
    public function index(Request $req, array $params): Response
    {
        return (new Response())->json(['action' => 'index', 'params' => $params]);
    }

    public function show(Request $req, array $params): Response
    {
        return (new Response())->json(['action' => 'show', 'id' => $params['id'] ?? null]);
    }
}

try {
    $router = new Router();
    $router->get('/',              _TestController::class, 'index');
    $router->get('/items/{id}',    _TestController::class, 'show');

    // Test: root path
    $req = new Request(serverParams: ['REQUEST_METHOD' => 'GET', 'REQUEST_URI' => '/']);
    $res = $router->dispatch($req);
    $body = json_decode($res->getBody(), true);
    assert($res->getStatus() === 200,              'GET / status failed');
    assert(($body['action'] ?? '') === 'index',    'GET / action failed');
    ok('Router dispatches GET /');

    // Test: parameterised path
    $req = new Request(serverParams: ['REQUEST_METHOD' => 'GET', 'REQUEST_URI' => '/items/99']);
    $res = $router->dispatch($req);
    $body = json_decode($res->getBody(), true);
    assert($res->getStatus() === 200,              'GET /items/99 status failed');
    assert(($body['id'] ?? '') === '99',           'GET /items/99 param failed');
    ok('Router dispatches GET /items/{id} with param id=99');

    // Test: 404 for unknown route
    $req = new Request(serverParams: ['REQUEST_METHOD' => 'GET', 'REQUEST_URI' => '/does-not-exist']);
    $res = $router->dispatch($req);
    assert($res->getStatus() === 404, '404 status failed');
    ok('Router returns 404 for unknown route');

    // Test: POST not matched by GET route
    $req = new Request(serverParams: ['REQUEST_METHOD' => 'POST', 'REQUEST_URI' => '/']);
    $res = $router->dispatch($req);
    assert($res->getStatus() === 404, 'method mismatch should 404');
    ok('Router returns 404 when method does not match');
} catch (\Throwable $e) {
    fail('Router', $e->getMessage());
}

// ─────────────────────────────────────────────────────────────────────────────
// Done
// ─────────────────────────────────────────────────────────────────────────────

echo "\nDone.\n";
echo "NOTE: Remove or block public/test.php before going to production.\n";
