<?php
/*
 * Socius - Open Source Association Management System
 * Copyright (C) 2026 Fabio Ranfi
 */

declare(strict_types=1);

require_once __DIR__ . '/_init.php';

requireStaff();

use Socius\Core\Database;
use Socius\Models\Communication;
use Socius\Models\Setting;

$currentUser = current_user();
$db          = Database::getInstance();

// Load categories for recipient filter
$categories = [];
try {
    $categories = $db->fetchAll(
        'SELECT id, label FROM membership_categories WHERE is_active = 1 ORDER BY sort_order ASC, label ASC'
    );
} catch (\Throwable) {}

$error    = null;
$formData = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_verify()) {
        flash_set('error', 'Token di sicurezza non valido. Ricarica la pagina.');
        redirect('communication-new.php');
    }

    $title         = trim((string) ($_POST['title']          ?? ''));
    $subject       = trim((string) ($_POST['subject']        ?? ''));
    $format        = in_array(trim((string) ($_POST['format'] ?? 'text')), ['text', 'markdown'], true)
                     ? trim((string) ($_POST['format'] ?? 'text')) : 'text';
    $type          = in_array(trim((string) ($_POST['type'] ?? 'general')), ['general', 'renewal', 'board', 'direct'], true)
                     ? trim((string) ($_POST['type'] ?? 'general')) : 'general';
    $renewalPeriod = trim((string) ($_POST['renewal_period'] ?? ''));
    $memberIds     = array_filter(array_map('intval', (array) ($_POST['member_ids'] ?? [])));

    // body_text comes from body_text (plain) or body_md (markdown) depending on format
    if ($format === 'markdown') {
        $bodyText = trim((string) ($_POST['body_md']   ?? ''));
        $bodyMd   = $bodyText;
    } else {
        $bodyText = trim((string) ($_POST['body_text'] ?? ''));
        $bodyMd   = null;
    }

    // Preserve keys matching the template's $v() accessors
    $formData = [
        'title'          => $title,
        'subject'        => $subject,
        'body_text'      => $bodyText,
        'format'         => $format,
        'type'           => $type,
        'renewal_period' => $renewalPeriod,
    ];

    // Validation
    if ($title === '') {
        $error = __('communications.error_title_required');
    } elseif ($subject === '') {
        $error = __('communications.error_subject_required');
    } elseif ($bodyText === '') {
        $error = __('communications.error_body_required');
    }

    if ($error === null) {
        try {
            $newId = Communication::create([
                'title'          => $title,
                'subject'        => $subject,
                'body_text'      => $bodyText,
                'body_md'        => $bodyMd,
                'format'         => $format,
                'status'         => 'draft',
                'type'           => $type,
                'renewal_period' => $renewalPeriod !== '' ? $renewalPeriod : null,
                'created_by'     => (int) ($currentUser['id'] ?? 0),
            ]);

            // Add recipients submitted via form (JS-managed list)
            if ($memberIds) {
                Communication::addRecipients($newId, array_values($memberIds));
            }

            flash_set('success', __('communications.created_ok'));
            redirect('communication-edit.php?id=' . $newId);
        } catch (\Throwable $ex) {
            error_log('[Socius] communication-new create error: ' . $ex->getMessage());
            $error = __('communications.error_save_generic');
        }
    }
}

$currentPeriod = Setting::get('system.current_period', '');

theme('communication-form', [
    'activeNav'     => 'communications',
    'currentUser'   => $currentUser,
    'isEdit'        => false,
    'communication' => $formData,
    'categories'    => $categories,
    'currentPeriod' => $currentPeriod,
    'recipients'    => [],
    'error'         => $error,
    'flashSuccess'  => flash_get('success'),
    'flashError'    => flash_get('error'),
]);
