<?php
$e    = fn($v) => htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8');
$name = trim((string) ($currentUser['name'] ?? '') . ' ' . (string) ($currentUser['surname'] ?? ''));

$statusLabels = [
    'active'    => 'Attivi',
    'suspended' => 'Sospesi',
    'expired'   => 'Scaduti',
    'resigned'  => 'Dimessi',
    'deceased'  => 'Deceduti',
];
$statusColors = [
    'active'    => '#32d296',
    'suspended' => '#999',
    'expired'   => '#f0506e',
    'resigned'  => '#faa05a',
    'deceased'  => '#666',
];

$content = (function () use ($stats, $name, $e, $statusLabels, $statusColors): string {
    ob_start();
    ?>
    <h1 class="uk-heading-small">Dashboard</h1>

    <div class="uk-card uk-card-primary uk-card-body uk-border-rounded uk-margin-bottom">
        <h3 class="uk-card-title">
            Benvenuto in Socius<?= $name !== '' ? ', ' . $e($name) : '' ?>!
        </h3>
        <p class="uk-text-lead uk-margin-remove">Il pannello di gestione dell'associazione.</p>
    </div>

    <?php if (!empty($stats)): ?>
    <div class="uk-margin-bottom">
        <?php foreach ($stats as $s => $cnt): ?>
            <span class="uk-badge uk-margin-small-right"
                  style="background:<?= $statusColors[$s] ?? '#1e87f0' ?>">
                <?= $e($statusLabels[$s] ?? $s) ?>: <?= (int) $cnt ?>
            </span>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>

    <div class="uk-grid uk-grid-small uk-child-width-1-3@m" uk-grid>
        <div>
            <div class="uk-card uk-card-default uk-card-body uk-border-rounded uk-text-center">
                <span class="uk-icon uk-icon-large" uk-icon="users"></span>
                <h4 class="uk-margin-small-top uk-margin-remove-bottom">Soci</h4>
                <p class="uk-text-muted uk-text-small uk-margin-remove">Gestisci i soci</p>
                <a href="members.php" class="uk-button uk-button-default uk-button-small uk-margin-small-top">
                    Vai ai soci
                </a>
            </div>
        </div>
        <div>
            <div class="uk-card uk-card-default uk-card-body uk-border-rounded uk-text-center">
                <span class="uk-icon uk-icon-large" uk-icon="calendar"></span>
                <h4 class="uk-margin-small-top uk-margin-remove-bottom">Eventi</h4>
                <p class="uk-text-muted uk-text-small uk-margin-remove">Gestisci gli eventi</p>
                <a href="events.php" class="uk-button uk-button-default uk-button-small uk-margin-small-top">
                    Vai agli eventi
                </a>
            </div>
        </div>
        <div>
            <div class="uk-card uk-card-default uk-card-body uk-border-rounded uk-text-center">
                <span class="uk-icon uk-icon-large" uk-icon="mail"></span>
                <h4 class="uk-margin-small-top uk-margin-remove-bottom">Comunicazioni</h4>
                <p class="uk-text-muted uk-text-small uk-margin-remove">Gestisci le comunicazioni</p>
                <a href="communications.php" class="uk-button uk-button-default uk-button-small uk-margin-small-top">
                    Vai alle comunicazioni
                </a>
            </div>
        </div>
    </div>
    <?php
    return (string) ob_get_clean();
})();

require __DIR__ . '/layout.php';
