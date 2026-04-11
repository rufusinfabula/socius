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

$id = (int) ($_GET['id'] ?? 0);
if ($id <= 0) {
    flash_set('error', __('communications.not_found'));
    redirect('communications.php');
}

$communication = Communication::findById($id);
if (!$communication) {
    flash_set('error', __('communications.not_found'));
    redirect('communications.php');
}

if ((string) ($communication['status'] ?? '') !== 'draft') {
    flash_set('error', __('communications.only_draft_editable'));
    redirect('communication.php?id=' . $id);
}

// Load categories for recipient filter
$categories = [];
try {
    $categories = $db->fetchAll(
        'SELECT id, label FROM membership_categories WHERE is_active = 1 ORDER BY sort_order ASC, label ASC'
    );
} catch (\Throwable) {}

// Load existing recipients
$recipients = Communication::getRecipients($id, false);

$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_verify()) {
        flash_set('error', 'Token di sicurezza non valido. Ricarica la pagina.');
        redirect('communication-edit.php?id=' . $id);
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

    if ($title === '') {
        $error = __('communications.error_title_required');
    } elseif ($subject === '') {
        $error = __('communications.error_subject_required');
    } elseif ($bodyText === '') {
        $error = __('communications.error_body_required');
    }

    if ($error === null) {
        try {
            Communication::update($id, [
                'title'          => $title,
                'subject'        => $subject,
                'body_text'      => $bodyText,
                'body_md'        => $bodyMd,
                'format'         => $format,
                'type'           => $type,
                'renewal_period' => $renewalPeriod !== '' ? $renewalPeriod : null,
            ]);

            // Replace recipient list with what was submitted
            Communication::replaceRecipients($id, array_values($memberIds));

            // Reload communication
            $communication = Communication::findById($id);
            $recipients    = Communication::getRecipients($id, false);

            flash_set('success', __('communications.updated_ok'));
            redirect('communication.php?id=' . $id);
        } catch (\Throwable $ex) {
            error_log('[Socius] communication-edit update error: ' . $ex->getMessage());
            $error = __('communications.error_save_generic');
        }
    }

    if ($error !== null) {
        $communication['title']          = $title;
        $communication['subject']        = $subject;
        $communication['body_text']      = $bodyText;
        $communication['format']         = $format;
        $communication['type']           = $type;
        $communication['renewal_period'] = $renewalPeriod ?: null;
    }
}

$currentPeriod = Setting::get('system.current_period', '');

theme('communication-form', [
    'activeNav'     => 'communications',
    'currentUser'   => $currentUser,
    'isEdit'        => true,
    'communication' => $communication,
    'categories'    => $categories,
    'currentPeriod' => $currentPeriod,
    'recipients'    => $recipients,
    'error'         => $error,
    'flashSuccess'  => flash_get('success'),
    'flashError'    => flash_get('error'),
]);
