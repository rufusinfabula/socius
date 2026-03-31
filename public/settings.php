<?php
/*
 * Socius - Open Source Association Management System
 * Copyright (C) 2026 Fabio Ranfi
 */

declare(strict_types=1);

require_once __DIR__ . '/_init.php';

requireAuth();

use Socius\Models\Setting;
use Socius\Models\MembershipCategory;
use Socius\Models\BoardRole;

$currentUser  = current_user();
$isSuperAdmin = (int) ($currentUser['role_id'] ?? 4) === 1;

// Only admin (role_id <= 2) can access settings
if ((int) ($currentUser['role_id'] ?? 4) > 2) {
    redirect('dashboard.php');
}

$validTabs = ['association', 'social_year', 'categories', 'board_roles', 'interface', 'email', 'member_number'];
$activeTab = trim((string) ($_GET['tab'] ?? 'association'));
if (!in_array($activeTab, $validTabs, true)) {
    $activeTab = 'association';
}

// =========================================================================
// POST handling
// =========================================================================

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_verify()) {
        flash_set('error', __('members.delete_csrf_invalid'));
        redirect("settings.php?tab={$activeTab}");
    }

    $action = trim((string) ($_POST['_action'] ?? 'save'));

    switch ($activeTab) {

        // -----------------------------------------------------------------
        case 'association':
            $logoPath = Setting::get('association.logo_path', '');
            // Remove logo if requested
            if (isset($_POST['remove_logo'])) {
                if ($logoPath !== '') {
                    $absPath = BASE_PATH . '/public/' . $logoPath;
                    if (is_file($absPath)) {
                        @unlink($absPath);
                    }
                }
                $logoPath = '';
            } elseif (!empty($_FILES['logo']['name'])) {
                $uploadResult = _settings_upload_logo($_FILES['logo']);
                if ($uploadResult['ok']) {
                    $logoPath = $uploadResult['path'];
                } else {
                    flash_set('error', $uploadResult['error']);
                    redirect('settings.php?tab=association');
                }
            }
            try {
                Setting::setMultiple([
                    'association.name'        => trim((string) ($_POST['association_name'] ?? '')),
                    'association.fiscal_code' => trim((string) ($_POST['association_fiscal_code'] ?? '')),
                    'association.vat_number'  => trim((string) ($_POST['association_vat_number'] ?? '')),
                    'association.address'     => trim((string) ($_POST['association_address'] ?? '')),
                    'association.city'        => trim((string) ($_POST['association_city'] ?? '')),
                    'association.postal_code' => trim((string) ($_POST['association_postal_code'] ?? '')),
                    'association.province'    => strtoupper(trim((string) ($_POST['association_province'] ?? ''))),
                    'association.country'     => strtoupper(trim((string) ($_POST['association_country'] ?? 'IT'))),
                    'association.email'       => trim((string) ($_POST['association_email'] ?? '')),
                    'association.phone'       => trim((string) ($_POST['association_phone'] ?? '')),
                    'association.website'     => trim((string) ($_POST['association_website'] ?? '')),
                    'association.logo_path'   => $logoPath,
                ]);
                csrf_regenerate();
                flash_set('success', __('settings.saved_ok'));
            } catch (\Throwable $ex) {
                flash_set('error', $ex->getMessage());
            }
            redirect('settings.php?tab=association');

        // -----------------------------------------------------------------
        case 'social_year':
            try {
                // Input type="date" sends YYYY-MM-DD; store only MM-DD
                $toMMDD = static function (string $raw, string $default): string {
                    $raw = trim($raw);
                    if (preg_match('/^\d{4}-(\d{2}-\d{2})$/', $raw, $m)) {
                        return $m[1];
                    }
                    if (preg_match('/^\d{2}-\d{2}$/', $raw)) {
                        return $raw;
                    }
                    return $default;
                };
                Setting::setMultiple([
                    'renewal.date_open'            => $toMMDD((string) ($_POST['renewal_date_open'] ?? ''), '11-15'),
                    'renewal.date_first_reminder'  => $toMMDD((string) ($_POST['renewal_date_first_reminder'] ?? ''), '02-15'),
                    'renewal.date_second_reminder' => $toMMDD((string) ($_POST['renewal_date_second_reminder'] ?? ''), '03-15'),
                    'renewal.date_third_reminder'  => $toMMDD((string) ($_POST['renewal_date_third_reminder'] ?? ''), '04-15'),
                    'renewal.date_close'           => $toMMDD((string) ($_POST['renewal_date_close'] ?? ''), '04-15'),
                    'renewal.date_lapse'           => $toMMDD((string) ($_POST['renewal_date_lapse'] ?? ''), '12-31'),
                    'renewal.reminder_approval'    => isset($_POST['renewal_reminder_approval']) ? 'true' : 'false',
                ]);
                csrf_regenerate();
                flash_set('success', __('settings.saved_ok'));
            } catch (\Throwable $ex) {
                flash_set('error', $ex->getMessage());
            }
            redirect('settings.php?tab=social_year');

        // -----------------------------------------------------------------
        case 'categories':
            if ($action === 'save_category') {
                $catId = (int) ($_POST['category_id'] ?? 0);
                $slug  = preg_replace('/[^a-z_]/', '', strtolower(trim((string) ($_POST['cat_name'] ?? ''))));
                $label = trim((string) ($_POST['cat_label'] ?? ''));
                if ($slug === '' || $label === '') {
                    flash_set('error', 'Slug e nome visualizzato sono obbligatori.');
                    redirect('settings.php?tab=categories');
                }
                $data = [
                    'name'                   => $slug,
                    'label'                  => $label,
                    'description'            => trim((string) ($_POST['cat_description'] ?? '')) ?: null,
                    'annual_fee'             => (float) str_replace(',', '.', (string) ($_POST['cat_annual_fee'] ?? '0')),
                    'is_free'                => isset($_POST['cat_is_free']) ? 1 : 0,
                    'is_exempt_from_renewal' => isset($_POST['cat_is_exempt']) ? 1 : 0,
                    'requires_approval'      => isset($_POST['cat_requires_approval']) ? 1 : 0,
                    'valid_from'             => ($_POST['cat_valid_from'] ?? '') ?: null,
                    'valid_until'            => ($_POST['cat_valid_until'] ?? '') ?: null,
                    'sort_order'             => (int) ($_POST['cat_sort_order'] ?? 0),
                    'is_active'              => 1,
                ];
                try {
                    if ($catId > 0) {
                        MembershipCategory::update($catId, $data);
                    } else {
                        MembershipCategory::create($data);
                    }
                    csrf_regenerate();
                    flash_set('success', __('settings.saved_ok'));
                } catch (\Throwable $ex) {
                    flash_set('error', $ex->getMessage());
                }

            } elseif ($action === 'toggle_category') {
                $catId = (int) ($_POST['category_id'] ?? 0);
                $cat   = MembershipCategory::findById($catId);
                if ($cat) {
                    MembershipCategory::update($catId, ['is_active' => ((int) $cat['is_active'] === 1 ? 0 : 1)]);
                    flash_set('success', __('settings.saved_ok'));
                }

            } elseif ($action === 'save_fee') {
                $catId = (int) ($_POST['category_id'] ?? 0);
                $year  = (int) ($_POST['fee_year'] ?? date('Y'));
                $fee   = (float) str_replace(',', '.', (string) ($_POST['fee_amount'] ?? '0'));
                $note  = trim((string) ($_POST['fee_note'] ?? ''));
                try {
                    MembershipCategory::setFeeForYear($catId, $year, $fee, $note, current_user_id());
                    flash_set('success', __('settings.saved_ok'));
                } catch (\Throwable $ex) {
                    flash_set('error', $ex->getMessage());
                }
            }
            redirect('settings.php?tab=categories');

        // -----------------------------------------------------------------
        case 'board_roles':
            if ($action === 'save_role') {
                $roleId = (int) ($_POST['role_id'] ?? 0);
                $slug   = preg_replace('/[^a-z_]/', '', strtolower(trim((string) ($_POST['role_name'] ?? ''))));
                $label  = trim((string) ($_POST['role_label'] ?? ''));
                if ($slug === '' || $label === '') {
                    flash_set('error', 'Slug e nome visualizzato sono obbligatori.');
                    redirect('settings.php?tab=board_roles');
                }
                $data = [
                    'name'            => $slug,
                    'label'           => $label,
                    'description'     => trim((string) ($_POST['role_description'] ?? '')) ?: null,
                    'is_board_member' => isset($_POST['role_is_board_member']) ? 1 : 0,
                    'can_sign'        => isset($_POST['role_can_sign']) ? 1 : 0,
                    'sort_order'      => (int) ($_POST['role_sort_order'] ?? 0),
                    'is_active'       => 1,
                ];
                try {
                    if ($roleId > 0) {
                        BoardRole::update($roleId, $data);
                    } else {
                        BoardRole::create($data);
                    }
                    csrf_regenerate();
                    flash_set('success', __('settings.saved_ok'));
                } catch (\Throwable $ex) {
                    flash_set('error', $ex->getMessage());
                }

            } elseif ($action === 'toggle_role') {
                $roleId = (int) ($_POST['role_id'] ?? 0);
                $role   = BoardRole::findById($roleId);
                if ($role) {
                    BoardRole::update($roleId, ['is_active' => ((int) $role['is_active'] === 1 ? 0 : 1)]);
                    flash_set('success', __('settings.saved_ok'));
                }
            }
            redirect('settings.php?tab=board_roles');

        // -----------------------------------------------------------------
        case 'interface':
            try {
                Setting::setMultiple([
                    'ui.theme'       => trim((string) ($_POST['ui_theme'] ?? 'uikit')),
                    'ui.locale'      => trim((string) ($_POST['ui_language'] ?? 'it')),
                    'ui.date_format' => trim((string) ($_POST['ui_date_format'] ?? 'd/m/Y')),
                    'ui.timezone'    => trim((string) ($_POST['ui_timezone'] ?? 'Europe/Rome')),
                ]);
                csrf_regenerate();
                flash_set('success', __('settings.saved_ok'));
            } catch (\Throwable $ex) {
                flash_set('error', $ex->getMessage());
            }
            redirect('settings.php?tab=interface');

        // -----------------------------------------------------------------
        case 'email':
            if ($action === 'test') {
                $cfg = [
                    'host'       => trim((string) ($_POST['smtp_host'] ?? '')),
                    'port'       => (int) ($_POST['smtp_port'] ?? 587),
                    'encryption' => trim((string) ($_POST['smtp_encryption'] ?? 'tls')),
                    'username'   => trim((string) ($_POST['smtp_username'] ?? '')),
                    'password'   => trim((string) ($_POST['smtp_password'] ?? '')),
                    'from'       => trim((string) ($_POST['smtp_from_address'] ?? '')),
                    'from_name'  => trim((string) ($_POST['smtp_from_name'] ?? '')),
                ];
                // If password field left as placeholder, use stored password
                if ($cfg['password'] === '' || $cfg['password'] === '••••••••') {
                    $cfg['password'] = Setting::decryptPassword(
                        (string) Setting::get('smtp.password', '')
                    );
                }
                $result = _settings_test_smtp($cfg, (string) ($currentUser['email'] ?? ''));
                if ($result['ok']) {
                    flash_set('success', __('settings.smtp_test_ok'));
                } else {
                    flash_set('error', __('settings.smtp_test_fail', ['error' => $result['message']]));
                }
            } else {
                // Save SMTP settings
                $newPwd = trim((string) ($_POST['smtp_password'] ?? ''));
                if ($newPwd === '' || $newPwd === '••••••••') {
                    $encPwd = (string) Setting::get('smtp.password', '');
                } else {
                    $encPwd = Setting::encryptPassword($newPwd);
                }
                try {
                    Setting::setMultiple([
                        'smtp.host'         => trim((string) ($_POST['smtp_host'] ?? '')),
                        'smtp.port'         => trim((string) ($_POST['smtp_port'] ?? '587')),
                        'smtp.encryption'   => trim((string) ($_POST['smtp_encryption'] ?? 'tls')),
                        'smtp.username'     => trim((string) ($_POST['smtp_username'] ?? '')),
                        'smtp.password'     => $encPwd,
                        'smtp.from_address' => trim((string) ($_POST['smtp_from_address'] ?? '')),
                        'smtp.from_name'    => trim((string) ($_POST['smtp_from_name'] ?? '')),
                    ]);
                    csrf_regenerate();
                    flash_set('success', __('settings.saved_ok'));
                } catch (\Throwable $ex) {
                    flash_set('error', $ex->getMessage());
                }
            }
            redirect('settings.php?tab=email');

        // -----------------------------------------------------------------
        case 'member_number':
            $newStart   = (int) ($_POST['number_start'] ?? 1);
            $currentMax = max(0, (int) Setting::get('members.next_number', '1') - 1);
            if ($newStart < 1 || $newStart <= $currentMax) {
                flash_set('error', __('settings.number_reset_warn'));
            } else {
                try {
                    Setting::set('members.next_number', (string) $newStart);
                    Setting::set('members.number_start', (string) $newStart);
                    csrf_regenerate();
                    flash_set('success', __('settings.saved_ok'));
                } catch (\Throwable $ex) {
                    flash_set('error', $ex->getMessage());
                }
            }
            redirect('settings.php?tab=member_number');
    }
}

// =========================================================================
// Load data for template
// =========================================================================

$settings         = [];
$categories       = [];
$categoryFees     = [];
$boardRoles       = [];
$languages        = [];
$memberCurrentMax = 0;

try { $settings = Setting::getAllGroups(); } catch (\Throwable) {}
try { $categories = MembershipCategory::findAll(); } catch (\Throwable) {}
try { $boardRoles = BoardRole::findAll(); } catch (\Throwable) {}

try {
    foreach ($categories as $cat) {
        $catId = (int) $cat['id'];
        $fees  = MembershipCategory::getFeesHistory($catId);
        // Limit to last 3 years
        $categoryFees[$catId] = array_slice($fees, 0, 3);
    }
} catch (\Throwable) {}

// Detect available languages from /lang/ directory
$langBaseDir = BASE_PATH . '/lang';
$langNames   = ['it' => 'Italiano', 'en' => 'English', 'de' => 'Deutsch', 'fr' => 'Français', 'es' => 'Español', 'pt' => 'Português'];
if (is_dir($langBaseDir)) {
    foreach (scandir($langBaseDir) as $entry) {
        if ($entry !== '.' && $entry !== '..' && is_dir($langBaseDir . '/' . $entry)) {
            $languages[$entry] = $langNames[$entry] ?? strtoupper($entry);
        }
    }
}

try {
    $memberCurrentMax = max(0, (int) Setting::get('members.next_number', '1') - 1);
} catch (\Throwable) {}

csrf_token();

theme('settings', [
    'activeNav'       => 'settings',
    'pageTitle'       => __('settings.settings'),
    'currentUser'     => $currentUser,
    'isSuperAdmin'    => $isSuperAdmin,
    'activeTab'       => $activeTab,
    'settings'        => $settings,
    'categories'      => $categories,
    'categoryFees'    => $categoryFees,
    'boardRoles'      => $boardRoles,
    'languages'       => $languages,
    'memberCurrentMax'=> $memberCurrentMax,
    'flashSuccess'    => flash_get('success'),
    'flashError'      => flash_get('error'),
]);

// =========================================================================
// Helper functions (private to this file)
// =========================================================================

/**
 * Handle logo file upload.
 * Returns ['ok' => bool, 'path' => string, 'error' => string]
 */
function _settings_upload_logo(array $file): array
{
    if ((int) $file['error'] !== UPLOAD_ERR_OK) {
        return ['ok' => false, 'error' => 'Errore durante il caricamento del file (' . (int) $file['error'] . ').'];
    }
    if ((int) $file['size'] > 2 * 1024 * 1024) {
        return ['ok' => false, 'error' => 'Il file supera i 2 MB.'];
    }

    $finfo    = new \finfo(FILEINFO_MIME_TYPE);
    $mime     = (string) $finfo->file($file['tmp_name']);
    $allowed  = ['image/png' => 'png', 'image/jpeg' => 'jpg', 'image/svg+xml' => 'svg'];

    if (!array_key_exists($mime, $allowed)) {
        return ['ok' => false, 'error' => 'Formato non supportato. Usa PNG, JPG o SVG.'];
    }

    $ext = $allowed[$mime];
    // Must be inside public/ to be web-accessible
    $dir = BASE_PATH . '/public/storage/uploads/logo';
    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
    }

    $dest = $dir . '/logo.' . $ext;
    if (!move_uploaded_file($file['tmp_name'], $dest)) {
        return ['ok' => false, 'error' => 'Impossibile salvare il file nel server.'];
    }

    return ['ok' => true, 'path' => 'storage/uploads/logo/logo.' . $ext];
}

/**
 * Test SMTP connection and optionally send a test email.
 *
 * Returns ['ok' => bool, 'message' => string]
 *
 * @param array<string, mixed> $cfg
 */
function _settings_test_smtp(array $cfg, string $toEmail): array
{
    $host = (string) ($cfg['host'] ?? '');
    $port = (int) ($cfg['port'] ?? 587);
    $enc  = (string) ($cfg['encryption'] ?? 'tls');
    $user = (string) ($cfg['username'] ?? '');
    $pass = (string) ($cfg['password'] ?? '');
    $from = (string) ($cfg['from'] ?? '');

    if ($host === '') {
        return ['ok' => false, 'message' => 'SMTP host not configured.'];
    }
    if ($from === '') {
        $from = 'socius@localhost';
    }

    $timeout = 10;
    $prefix  = ($enc === 'ssl') ? 'ssl://' : 'tcp://';
    $ctx     = stream_context_create(['ssl' => ['verify_peer' => false, 'verify_peer_name' => false]]);

    $fp = @stream_socket_client("{$prefix}{$host}:{$port}", $errno, $errstr, $timeout, STREAM_CLIENT_CONNECT, $ctx);
    if ($fp === false) {
        return ['ok' => false, 'message' => "Connection failed: {$errstr} ({$errno})"];
    }
    stream_set_timeout($fp, $timeout);

    $rd = static fn() => (string) fgets($fp, 4096);
    $wr = static fn(string $s) => fwrite($fp, $s . "\r\n");

    // Read greeting
    $banner = $rd();
    if (!str_starts_with(trim($banner), '220')) {
        fclose($fp);
        return ['ok' => false, 'message' => 'Unexpected banner: ' . trim($banner)];
    }

    // EHLO
    $wr('EHLO localhost');
    do { $line = $rd(); } while ($line !== '' && isset($line[3]) && $line[3] === '-');

    // STARTTLS
    if ($enc === 'tls') {
        $wr('STARTTLS');
        $resp = $rd();
        if (!str_starts_with(trim($resp), '220')) {
            fclose($fp);
            return ['ok' => false, 'message' => 'STARTTLS failed: ' . trim($resp)];
        }
        stream_socket_enable_crypto($fp, true, STREAM_CRYPTO_METHOD_TLS_CLIENT);
        $wr('EHLO localhost');
        do { $line = $rd(); } while ($line !== '' && isset($line[3]) && $line[3] === '-');
    }

    // AUTH LOGIN
    if ($user !== '') {
        $wr('AUTH LOGIN');
        $resp = $rd();
        if (!str_starts_with(trim($resp), '334')) {
            fclose($fp);
            return ['ok' => false, 'message' => 'AUTH LOGIN not accepted: ' . trim($resp)];
        }
        $wr(base64_encode($user));
        $resp = $rd();
        if (!str_starts_with(trim($resp), '334')) {
            fclose($fp);
            return ['ok' => false, 'message' => 'Username rejected: ' . trim($resp)];
        }
        $wr(base64_encode($pass));
        $resp = $rd();
        if (!str_starts_with(trim($resp), '235')) {
            fclose($fp);
            return ['ok' => false, 'message' => 'Authentication failed: ' . trim($resp)];
        }
    }

    // Send test message
    if ($toEmail !== '') {
        $wr("MAIL FROM:<{$from}>");
        $resp = $rd();
        if (!str_starts_with(trim($resp), '250')) {
            fclose($fp);
            return ['ok' => false, 'message' => 'MAIL FROM rejected: ' . trim($resp)];
        }

        $wr("RCPT TO:<{$toEmail}>");
        $resp = $rd();
        if (!str_starts_with(trim($resp), '250')) {
            fclose($fp);
            return ['ok' => false, 'message' => 'RCPT TO rejected: ' . trim($resp)];
        }

        $wr('DATA');
        $resp = $rd();
        if (!str_starts_with(trim($resp), '354')) {
            fclose($fp);
            return ['ok' => false, 'message' => 'DATA rejected: ' . trim($resp)];
        }

        $subject = '=?UTF-8?B?' . base64_encode('Socius SMTP Test') . '?=';
        $body    = "From: {$from}\r\nTo: {$toEmail}\r\nSubject: {$subject}\r\n"
                 . "MIME-Version: 1.0\r\nContent-Type: text/plain; charset=UTF-8\r\n\r\n"
                 . "This is a test email from the Socius settings panel.\r\n.";
        $wr($body);
        $resp = $rd();
        if (!str_starts_with(trim($resp), '2')) {
            fclose($fp);
            return ['ok' => false, 'message' => 'Message delivery failed: ' . trim($resp)];
        }
    }

    $wr('QUIT');
    fclose($fp);
    return ['ok' => true, 'message' => 'OK'];
}
