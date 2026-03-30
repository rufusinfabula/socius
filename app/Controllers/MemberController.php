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

namespace Socius\Controllers;

use Socius\Core\Middleware;
use Socius\Core\Request;
use Socius\Core\Response;
use Socius\Models\Member;
use Socius\Models\User;

/**
 * Member CRUD and listing.
 *
 * Role conventions used here:
 *   super_admin — role_id 1 — full access including emergency delete
 *   admin       — role_id 2 — full CRUD, no delete
 *   segreteria  — role_id 3 — full CRUD, no delete
 *   socio       — role_id 4 — read own profile only
 */
class MemberController extends BaseController
{
    // =========================================================================
    // List
    // =========================================================================

    public function index(Request $request, array $params): Response
    {
        if (!Middleware::isAuthenticated()) {
            return $this->redirect('/login');
        }

        $currentUser = User::findById((int) Middleware::authUserId());
        if ($currentUser === null) {
            return $this->redirect('/login');
        }

        // Soci (role 4) can only see their own profile
        if ((int) $currentUser['role_id'] === 4) {
            return $this->redirect('/members/' . $currentUser['member_id']);
        }

        $page    = max(1, (int) $request->get('page', 1));
        $filters = [
            'status'   => (string) $request->get('status', ''),
            'category' => (int)    $request->get('category', 0),
            'search'   => (string) $request->get('search', ''),
        ];

        $result = Member::findAll($filters, $page);
        $stats  = Member::getStatsByStatus();

        $categories = \Socius\Core\Database::getInstance()->fetchAll(
            'SELECT id, label FROM membership_categories WHERE is_active = 1 ORDER BY label ASC'
        );

        return $this->view('themes/uikit/members/index', [
            'activeNav'   => 'members',
            'members'     => $result,
            'stats'       => $stats,
            'filters'     => $filters,
            'categories'  => $categories,
            'currentUser' => $currentUser,
            'flash'       => $this->getFlash(),
        ]);
    }

    // =========================================================================
    // Show
    // =========================================================================

    public function show(Request $request, array $params): Response
    {
        if (!Middleware::isAuthenticated()) {
            return $this->redirect('/login');
        }

        $currentUser = User::findById((int) Middleware::authUserId());
        if ($currentUser === null) {
            return $this->redirect('/login');
        }

        $id     = (int) ($params['id'] ?? 0);
        $member = Member::findById($id);

        if ($member === null) {
            return $this->view('themes/uikit/members/index', [
                'activeNav'   => 'members',
                'members'     => Member::findAll(),
                'stats'       => Member::getStatsByStatus(),
                'filters'     => [],
                'categories'  => [],
                'currentUser' => $currentUser,
                'flash'       => ['error' => __('members.not_found')],
            ]);
        }

        // Soci can only view their own profile
        if ((int) $currentUser['role_id'] === 4
            && (int) $currentUser['member_id'] !== $id) {
            return $this->view('themes/uikit/members/show', [
                'activeNav'   => 'members',
                'member'      => $member,
                'memberships' => [],
                'payments'    => [],
                'currentUser' => $currentUser,
                'flash'       => ['error' => __('members.forbidden')],
            ]);
        }

        try {
            $memberships = Member::getMemberships($id);
            $payments    = Member::getPayments($id);
        } catch (\Throwable $e) {
            $memberships = [];
            $payments    = [];
        }

        return $this->view('themes/uikit/members/show', [
            'activeNav'   => 'members',
            'member'      => $member,
            'memberships' => $memberships,
            'payments'    => $payments,
            'currentUser' => $currentUser,
            'flash'       => $this->getFlash(),
        ]);
    }

    // =========================================================================
    // Create / Store
    // =========================================================================

    public function create(Request $request, array $params): Response
    {
        if (!Middleware::isAuthenticated()) {
            return $this->redirect('/login');
        }

        $currentUser = User::findById((int) Middleware::authUserId());
        if ($currentUser === null || (int) $currentUser['role_id'] === 4) {
            return $this->redirect('/members');
        }

        $categories = \Socius\Core\Database::getInstance()->fetchAll(
            'SELECT id, label FROM membership_categories WHERE is_active = 1 ORDER BY label ASC'
        );

        return $this->view('themes/uikit/members/form', [
            'activeNav'   => 'members',
            'member'      => null,
            'categories'  => $categories,
            'currentUser' => $currentUser,
            'csrf'        => Middleware::csrfToken(),
            'flash'       => $this->getFlash(),
        ]);
    }

    public function store(Request $request, array $params): Response
    {
        if (!Middleware::isAuthenticated()) {
            return $this->redirect('/login');
        }

        $currentUser = User::findById((int) Middleware::authUserId());
        if ($currentUser === null || (int) $currentUser['role_id'] === 4) {
            return $this->redirect('/members');
        }

        if (!Middleware::verifyCsrfToken($request)) {
            return $this->redirect('/members/create');
        }

        $data   = $this->extractMemberData($request, $currentUser);
        $errors = $this->validateMemberData($data);

        if ($errors) {
            $this->setFlash('error', implode(' ', $errors));
            return $this->redirect('/members/create');
        }

        $data['created_by'] = (int) $currentUser['id'];
        $newId = Member::create($data);
        $newMember = Member::findById($newId);

        Member::audit(
            (int) $currentUser['id'],
            'create',
            $newId,
            null,
            $newMember,
            $request->ip()
        );

        Middleware::regenerateCsrfToken();
        $this->setFlash('success', __('members.created_ok', [
            'number' => $newMember['membership_number'] ?? '',
        ]));
        return $this->redirect('/members/' . $newId);
    }

    // =========================================================================
    // Edit / Update
    // =========================================================================

    public function edit(Request $request, array $params): Response
    {
        if (!Middleware::isAuthenticated()) {
            return $this->redirect('/login');
        }

        $currentUser = User::findById((int) Middleware::authUserId());
        if ($currentUser === null || (int) $currentUser['role_id'] === 4) {
            return $this->redirect('/members');
        }

        $id     = (int) ($params['id'] ?? 0);
        $member = Member::findById($id);

        if ($member === null) {
            $this->setFlash('error', __('members.not_found'));
            return $this->redirect('/members');
        }

        $categories = \Socius\Core\Database::getInstance()->fetchAll(
            'SELECT id, label FROM membership_categories WHERE is_active = 1 ORDER BY label ASC'
        );

        return $this->view('themes/uikit/members/form', [
            'activeNav'   => 'members',
            'member'      => $member,
            'categories'  => $categories,
            'currentUser' => $currentUser,
            'csrf'        => Middleware::csrfToken(),
            'flash'       => $this->getFlash(),
        ]);
    }

    public function update(Request $request, array $params): Response
    {
        if (!Middleware::isAuthenticated()) {
            return $this->redirect('/login');
        }

        $currentUser = User::findById((int) Middleware::authUserId());
        if ($currentUser === null || (int) $currentUser['role_id'] === 4) {
            return $this->redirect('/members');
        }

        if (!Middleware::verifyCsrfToken($request)) {
            return $this->redirect('/members');
        }

        $id     = (int) ($params['id'] ?? 0);
        $member = Member::findById($id);

        if ($member === null) {
            $this->setFlash('error', __('members.not_found'));
            return $this->redirect('/members');
        }

        $data   = $this->extractMemberData($request, $currentUser);
        $errors = $this->validateMemberData($data, $id);

        if ($errors) {
            $this->setFlash('error', implode(' ', $errors));
            return $this->redirect('/members/' . $id . '/edit');
        }

        Member::audit(
            (int) $currentUser['id'],
            'update',
            $id,
            $member,
            $data,
            $request->ip()
        );

        Member::update($id, $data);
        Middleware::regenerateCsrfToken();
        $this->setFlash('success', __('members.updated_ok'));
        return $this->redirect('/members/' . $id);
    }

    // =========================================================================
    // Delete confirm / execute (super_admin only)
    // =========================================================================

    public function deleteConfirm(Request $request, array $params): Response
    {
        if (!Middleware::isAuthenticated()) {
            return $this->redirect('/login');
        }

        $currentUser = User::findById((int) Middleware::authUserId());
        if ($currentUser === null || (int) $currentUser['role_id'] !== 1) {
            $this->setFlash('error', __('members.delete_forbidden'));
            return $this->redirect('/members');
        }

        $id     = (int) ($params['id'] ?? 0);
        $member = Member::findById($id);

        if ($member === null) {
            $this->setFlash('error', __('members.not_found'));
            return $this->redirect('/members');
        }

        return $this->view('themes/uikit/members/delete-confirm', [
            'activeNav'   => 'members',
            'member'      => $member,
            'memberships' => Member::getMemberships($id),
            'payments'    => Member::getPayments($id),
            'currentUser' => $currentUser,
            'csrf'        => Middleware::csrfToken(),
            'flash'       => $this->getFlash(),
        ]);
    }

    public function deleteExecute(Request $request, array $params): Response
    {
        if (!Middleware::isAuthenticated()) {
            return $this->redirect('/login');
        }

        $currentUser = User::findById((int) Middleware::authUserId());
        if ($currentUser === null || (int) $currentUser['role_id'] !== 1) {
            $this->setFlash('error', __('members.delete_forbidden'));
            return $this->redirect('/members');
        }

        if (!Middleware::verifyCsrfToken($request)) {
            $this->setFlash('error', __('members.delete_csrf_invalid'));
            return $this->redirect('/members');
        }

        $id     = (int) ($params['id'] ?? 0);
        $member = Member::findById($id);

        if ($member === null) {
            $this->setFlash('error', __('members.not_found'));
            return $this->redirect('/members');
        }

        // Case-sensitive confirmation word
        $confirm = (string) $request->post('confirm_word', '');
        if ($confirm !== 'DELETE') {
            $this->setFlash('error', __('members.delete_wrong_confirm'));
            return $this->redirect('/members/' . $id . '/delete-confirm');
        }

        $freeNumber = $request->post('free_number', '0') === '1';

        $deleted = Member::emergencyDelete(
            $id,
            $freeNumber,
            (int) $currentUser['id'],
            $request->ip()
        );

        if (!$deleted) {
            $this->setFlash('error', __('members.not_found'));
            return $this->redirect('/members');
        }

        Middleware::regenerateCsrfToken();
        $this->setFlash('success', __('members.deleted_ok', [
            'number' => $member['membership_number'],
        ]));
        return $this->redirect('/members');
    }

    // =========================================================================
    // Helpers
    // =========================================================================

    /**
     * Extract and sanitize member data from the POST request.
     *
     * @return array<string, mixed>
     */
    private function extractMemberData(Request $request, array $currentUser): array
    {
        $data = [
            'name'        => trim((string) $request->post('name', '')),
            'surname'     => trim((string) $request->post('surname', '')),
            'email'       => trim((string) $request->post('email', '')),
            'phone'       => trim((string) $request->post('phone', '')) ?: null,
            'birth_date'  => $request->post('birth_date', '') ?: null,
            'birth_place' => trim((string) $request->post('birth_place', '')) ?: null,
            'fiscal_code' => strtoupper(trim((string) $request->post('fiscal_code', ''))) ?: null,
            'address'     => trim((string) $request->post('address', '')) ?: null,
            'postal_code' => trim((string) $request->post('postal_code', '')) ?: null,
            'city'        => trim((string) $request->post('city', '')) ?: null,
            'province'    => strtoupper(trim((string) $request->post('province', ''))) ?: null,
            'country'     => strtoupper(trim((string) $request->post('country', 'IT'))) ?: 'IT',
            'category_id' => $request->post('category_id', '') !== '' ? (int) $request->post('category_id') : null,
            'status'      => (string) $request->post('status', 'active'),
            'joined_on'   => (string) $request->post('joined_on', date('Y-m-d')),
            'resigned_on' => $request->post('resigned_on', '') ?: null,
        ];

        // Notes only for admin+ (role 1-3)
        if ((int) $currentUser['role_id'] <= 3) {
            $data['notes'] = trim((string) $request->post('notes', '')) ?: null;
        }

        return $data;
    }

    /**
     * Validate member data, optionally excluding $excludeId from uniqueness checks.
     *
     * @return list<string>  List of error messages (empty = valid)
     */
    private function validateMemberData(array $data, ?int $excludeId = null): array
    {
        $errors = [];
        $db     = \Socius\Core\Database::getInstance();

        if (empty($data['name'])) {
            $errors[] = __('members.name_required');
        }
        if (empty($data['surname'])) {
            $errors[] = __('members.surname_required');
        }
        if (empty($data['email'])) {
            $errors[] = __('members.email_required');
        } elseif (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $errors[] = __('members.email_invalid');
        } else {
            $sql    = 'SELECT id FROM members WHERE email = ? LIMIT 1';
            $params = [$data['email']];
            if ($excludeId !== null) {
                $sql    = 'SELECT id FROM members WHERE email = ? AND id != ? LIMIT 1';
                $params = [$data['email'], $excludeId];
            }
            if ($db->fetch($sql, $params) !== false) {
                $errors[] = __('members.email_duplicate');
            }
        }

        if (!empty($data['fiscal_code'])) {
            $sql    = 'SELECT id FROM members WHERE fiscal_code = ? LIMIT 1';
            $params = [$data['fiscal_code']];
            if ($excludeId !== null) {
                $sql    = 'SELECT id FROM members WHERE fiscal_code = ? AND id != ? LIMIT 1';
                $params = [$data['fiscal_code'], $excludeId];
            }
            if ($db->fetch($sql, $params) !== false) {
                $errors[] = __('members.fiscal_code_duplicate');
            }
        }

        if (empty($data['joined_on'])) {
            $errors[] = __('members.joined_on_required');
        }

        return $errors;
    }

    // =========================================================================
    // Flash helpers
    // =========================================================================

    private function setFlash(string $type, string $message): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $_SESSION['_flash'][$type] = $message;
    }

    /** @return array{success?: string, error?: string} */
    private function getFlash(): array
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $flash = $_SESSION['_flash'] ?? [];
        unset($_SESSION['_flash']);
        return $flash;
    }
}
