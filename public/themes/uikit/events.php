<?php
$e = fn($v) => htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8');

$content = (function () use ($events, $flashSuccess, $flashError, $e): string {
    ob_start();
    ?>

    <?php if (!empty($flashSuccess)): ?>
        <div class="uk-alert-success" uk-alert><a class="uk-alert-close" uk-close></a><p><?= $e($flashSuccess) ?></p></div>
    <?php endif; ?>
    <?php if (!empty($flashError)): ?>
        <div class="uk-alert-danger" uk-alert><a class="uk-alert-close" uk-close></a><p><?= $e($flashError) ?></p></div>
    <?php endif; ?>

    <h1 class="uk-heading-small">
        <span uk-icon="icon: calendar; ratio: 1.4" class="uk-margin-small-right"></span>
        Eventi
    </h1>

    <?php if (empty($events)): ?>
        <div class="uk-card uk-card-default uk-card-body uk-border-rounded">
            <p class="uk-text-muted uk-margin-remove">Nessun evento registrato.</p>
        </div>
    <?php else: ?>
        <div class="uk-overflow-auto">
            <table class="uk-table uk-table-striped uk-table-hover uk-table-small">
                <thead>
                    <tr>
                        <th>Titolo</th><th>Inizio</th><th>Fine</th><th>Luogo</th><th>Azioni</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($events as $ev): ?>
                    <tr>
                        <td><?= $e($ev['title'] ?? $ev['name'] ?? '—') ?></td>
                        <td><?= $e($ev['starts_at'] ?? $ev['start_date'] ?? '—') ?></td>
                        <td><?= $e($ev['ends_at'] ?? $ev['end_date'] ?? '—') ?></td>
                        <td><?= $e($ev['location'] ?? '—') ?></td>
                        <td>
                            <a href="event.php?id=<?= (int) $ev['id'] ?>"
                               class="uk-icon-button" uk-icon="eye" title="Visualizza"></a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>

    <div class="uk-card uk-card-default uk-card-body uk-border-rounded uk-margin-top">
        <p class="uk-text-muted uk-margin-remove">
            La gestione completa degli eventi sarà disponibile in una prossima versione.
        </p>
    </div>

    <?php
    return (string) ob_get_clean();
})();

require __DIR__ . '/layout.php';
