<?php
$e = fn($v) => htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8');

$content = (function () use ($event, $e): string {
    ob_start();
    ?>
    <ul class="uk-breadcrumb uk-margin-small-bottom">
        <li><a href="events.php">Eventi</a></li>
        <li><span><?= $e($event['title'] ?? $event['name'] ?? 'Evento') ?></span></li>
    </ul>

    <h1 class="uk-heading-small"><?= $e($event['title'] ?? $event['name'] ?? 'Evento') ?></h1>

    <div class="uk-card uk-card-default uk-card-body uk-border-rounded uk-margin-bottom">
        <dl class="uk-description-list">
            <?php if (!empty($event['starts_at']) || !empty($event['start_date'])): ?>
            <dt>Inizio</dt>
            <dd><?= $e($event['starts_at'] ?? $event['start_date']) ?></dd>
            <?php endif; ?>
            <?php if (!empty($event['ends_at']) || !empty($event['end_date'])): ?>
            <dt>Fine</dt>
            <dd><?= $e($event['ends_at'] ?? $event['end_date']) ?></dd>
            <?php endif; ?>
            <?php if (!empty($event['location'])): ?>
            <dt>Luogo</dt>
            <dd><?= $e($event['location']) ?></dd>
            <?php endif; ?>
            <?php if (!empty($event['description'])): ?>
            <dt>Descrizione</dt>
            <dd><?= nl2br($e($event['description'])) ?></dd>
            <?php endif; ?>
        </dl>
    </div>

    <div class="uk-card uk-card-default uk-card-body uk-border-rounded">
        <p class="uk-text-muted uk-margin-remove">
            La gestione completa degli eventi sarà disponibile in una prossima versione.
        </p>
    </div>

    <div class="uk-margin-top">
        <a href="events.php" class="uk-button uk-button-text">← Lista eventi</a>
    </div>
    <?php
    return (string) ob_get_clean();
})();

require __DIR__ . '/layout.php';
