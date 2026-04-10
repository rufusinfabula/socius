<?php
/*
 * Socius - Open Source Association Management System
 * Copyright (C) 2026 Fabio Ranfi
 */

declare(strict_types=1);

require_once __DIR__ . '/_init.php';

requireAuth();

use Socius\Core\Database;
use Socius\Models\Communication;

$currentUser = current_user();
$db          = Database::getInstance();
$isStaff     = (int) ($currentUser['role_id'] ?? 4) <= 3;

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

// ─── Export ─────────────────────────────────────────────────────────────────
$action = trim((string) ($_GET['action'] ?? ''));
if ($action === 'export') {
    $format = trim((string) ($_GET['format'] ?? 'csv'));
    $content = Communication::exportRecipients($id, $format);
    $filename = 'comunicazione_' . $id . '_' . date('Ymd');
    switch ($format) {
        case 'csv':
            header('Content-Type: text/csv; charset=utf-8');
            header('Content-Disposition: attachment; filename="' . $filename . '.csv"');
            echo "\xEF\xBB\xBF"; // UTF-8 BOM for Excel
            break;
        case 'txt':
        case 'txt_names':
            header('Content-Type: text/plain; charset=utf-8');
            header('Content-Disposition: attachment; filename="' . $filename . '.txt"');
            break;
    }
    echo $content;
    exit;
}

// ─── POST actions ────────────────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $isStaff) {
    if (!csrf_verify()) {
        flash_set('error', 'Token di sicurezza non valido.');
        redirect('communication.php?id=' . $id);
    }

    $postAction = trim((string) ($_POST['_action'] ?? ''));

    switch ($postAction) {
        case 'mark_ready':
            if (Communication::markAsReady($id)) {
                flash_set('success', __('communications.ready_ok'));
            } else {
                flash_set('error', __('communications.only_draft_editable'));
            }
            redirect('communication.php?id=' . $id);

        case 'mark_sent':
            if (Communication::markAsSent($id)) {
                flash_set('success', __('communications.sent_ok'));
            } else {
                flash_set('error', __('communications.error_save_generic'));
            }
            redirect('communication.php?id=' . $id);

        case 'duplicate':
            try {
                $newId = Communication::duplicate($id, (int) ($currentUser['id'] ?? 0));
                flash_set('success', __('communications.duplicate_ok'));
                redirect('communication-edit.php?id=' . $newId);
            } catch (\Throwable) {
                flash_set('error', __('communications.error_save_generic'));
                redirect('communication.php?id=' . $id);
            }

        case 'delete':
            if (Communication::delete($id)) {
                flash_set('success', __('communications.delete_ok'));
                redirect('communications.php');
            } else {
                flash_set('error', __('communications.only_draft_deletable'));
                redirect('communication.php?id=' . $id);
            }
    }
}

$recipients = Communication::getRecipients($id, false);

theme('communication', [
    'activeNav'     => 'communications',
    'currentUser'   => $currentUser,
    'isStaff'       => $isStaff,
    'communication' => $communication,
    'recipients'    => $recipients,
    'flashSuccess'  => flash_get('success'),
    'flashError'    => flash_get('error'),
]);
