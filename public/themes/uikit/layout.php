<!DOCTYPE html>
<html lang="<?= htmlspecialchars(\Socius\Core\Lang::getLocale(), ENT_QUOTES, 'UTF-8') ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars((string) ($pageTitle ?? \Socius\Core\Config::get('app.name', 'Socius')), ENT_QUOTES, 'UTF-8') ?> — <?= htmlspecialchars((string) \Socius\Core\Config::get('app.name', 'Socius'), ENT_QUOTES, 'UTF-8') ?></title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/uikit@3/dist/css/uikit.min.css">
    <?php if (!empty($pageHead)) echo $pageHead; ?>
    <style>
        .uk-sidebar { min-height: calc(100vh - 80px); background: #f8f8f8; border-right: 1px solid #e5e5e5; }
        .uk-sidebar .uk-nav-default > li > a { padding: 8px 20px; font-size: 0.9rem; }
        .uk-sidebar .uk-nav-default > li.uk-active > a { color: #1e87f0; font-weight: 600; }
        .main-content { padding: 30px; }

        /*
         * Socius — Global identifier badges
         *
         * Two CSS classes used consistently across ALL templates to display
         * member and card numbers. Never render these numbers as plain text.
         *
         * .badge-member-number  →  permanent member identifier (blue)
         *   Format: M00001 — assigned once at registration, never changes.
         *   Source: members.member_number (integer, formatted by format_member_number())
         *
         * .badge-card-number  →  annual card identifier (green)
         *   Format: C00001 — released (NULL) when member lapses.
         *   Source of truth: memberships.membership_number
         *   Denormalized copy: members.membership_number (synced automatically)
         *
         * Usage in templates:
         *   <span class="badge-member-number">
         *     <?= $e(format_member_number($member['member_number'])) ?>
         *   </span>
         *   <span class="badge-card-number">
         *     <?= $e(format_card_number($member['membership_number'])) ?>
         *   </span>
         */
        .badge-member-number {
            font-family: 'Courier New', Courier, monospace;
            font-size: 0.85em;
            font-weight: 600;
            background-color: #E8F0FE;
            color: #1A3A6B;
            padding: 2px 7px;
            border-radius: 4px;
            white-space: nowrap;
            letter-spacing: 0.03em;
        }
        .badge-card-number {
            font-family: 'Courier New', Courier, monospace;
            font-size: 0.85em;
            font-weight: 600;
            background-color: #E6F4EA;
            color: #1B5E2F;
            padding: 2px 7px;
            border-radius: 4px;
            white-space: nowrap;
            letter-spacing: 0.03em;
        }
    </style>
</head>
<body>

<!-- Navbar -->
<nav class="uk-navbar-container" uk-navbar>
    <div class="uk-navbar-left uk-margin-left">
        <a class="uk-navbar-item uk-logo" href="dashboard.php">
            <?php
            try {
                $db = \Socius\Core\Database::getInstance();
                $logoRow = $db->fetch("SELECT `value` FROM settings WHERE `key` = 'association.logo_path' LIMIT 1");
                $nameRow = $db->fetch("SELECT `value` FROM settings WHERE `key` = 'association.name' LIMIT 1");
                $logoPath = ($logoRow && !empty($logoRow['value'])) ? (string) $logoRow['value'] : '';
                $assocName = ($nameRow && !empty($nameRow['value']))
                    ? (string) $nameRow['value']
                    : (string) \Socius\Core\Config::get('app.name', 'Socius');
            } catch (\Throwable) {
                $logoPath  = '';
                $assocName = (string) \Socius\Core\Config::get('app.name', 'Socius');
            }
            if ($logoPath !== '') {
                echo '<img src="' . htmlspecialchars($logoPath, ENT_QUOTES, 'UTF-8')
                   . '" alt="' . htmlspecialchars($assocName, ENT_QUOTES, 'UTF-8')
                   . '" style="max-height:36px; max-width:160px; vertical-align:middle">';
            } else {
                echo htmlspecialchars($assocName, ENT_QUOTES, 'UTF-8');
            }
            ?>
        </a>
    </div>
    <div class="uk-navbar-right uk-margin-right">
        <ul class="uk-navbar-nav">
            <li>
                <a href="logout.php" class="uk-button uk-button-default uk-button-small">
                    <span uk-icon="sign-out"></span> Esci
                </a>
            </li>
        </ul>
    </div>
</nav>

<!-- Body -->
<div class="uk-grid uk-grid-collapse" uk-grid>

    <!-- Sidebar -->
    <div class="uk-width-1-6@m uk-sidebar">
        <ul class="uk-nav uk-nav-default uk-margin-top">
            <li class="<?= ($activeNav ?? '') === 'dashboard' ? 'uk-active' : '' ?>">
                <a href="dashboard.php">
                    <span uk-icon="home" class="uk-margin-small-right"></span>Dashboard
                </a>
            </li>
            <li class="uk-nav-divider"></li>
            <li class="<?= ($activeNav ?? '') === 'members' ? 'uk-active' : '' ?>">
                <a href="members.php">
                    <span uk-icon="users" class="uk-margin-small-right"></span>Soci
                </a>
            </li>
            <li class="<?= ($activeNav ?? '') === 'memberships' ? 'uk-active' : '' ?>">
                <a href="memberships.php">
                    <span uk-icon="tag" class="uk-margin-small-right"></span>Tessere
                </a>
            </li>
            <li class="<?= ($activeNav ?? '') === 'events' ? 'uk-active' : '' ?>">
                <a href="events.php">
                    <span uk-icon="calendar" class="uk-margin-small-right"></span>Eventi
                </a>
            </li>
            <li class="<?= ($activeNav ?? '') === 'communications' ? 'uk-active' : '' ?>">
                <a href="communications.php">
                    <span uk-icon="mail" class="uk-margin-small-right"></span>Comunicazioni
                </a>
            </li>
            <li class="uk-nav-divider"></li>
            <li class="<?= ($activeNav ?? '') === 'settings' ? 'uk-active' : '' ?>">
                <a href="settings.php">
                    <span uk-icon="settings" class="uk-margin-small-right"></span>Impostazioni
                </a>
            </li>
        </ul>
    </div>

    <!-- Main content -->
    <div class="uk-width-expand@m main-content">
        <?php if (!empty($flashSuccess)): ?>
            <div class="uk-alert-success" uk-alert>
                <a class="uk-alert-close" uk-close></a>
                <p><?= htmlspecialchars((string) $flashSuccess, ENT_QUOTES, 'UTF-8') ?></p>
            </div>
        <?php endif; ?>
        <?php if (!empty($flashError)): ?>
            <div class="uk-alert-danger" uk-alert>
                <a class="uk-alert-close" uk-close></a>
                <p><?= htmlspecialchars((string) $flashError, ENT_QUOTES, 'UTF-8') ?></p>
            </div>
        <?php endif; ?>

        <?= $content ?? '' ?>
    </div>

</div>

<!-- Footer -->
<footer class="uk-section uk-section-xsmall uk-background-muted uk-text-center uk-text-small uk-text-muted">
    <?= htmlspecialchars((string) \Socius\Core\Config::get('app.name', 'Socius'), ENT_QUOTES, 'UTF-8') ?> &copy; <?= date('Y') ?>
</footer>

<script src="https://cdn.jsdelivr.net/npm/uikit@3/dist/js/uikit.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/uikit@3/dist/js/uikit-icons.min.js"></script>
</body>
</html>
