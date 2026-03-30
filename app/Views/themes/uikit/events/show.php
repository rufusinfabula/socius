<?php
// Variables: $activeNav, $id

$content = (function () use ($id): string {
    ob_start();
    ?>
    <ul class="uk-breadcrumb uk-margin-small-bottom">
        <li><a href="/index.php?route=events">Eventi</a></li>
        <li><span>Evento #<?= (int) $id ?></span></li>
    </ul>

    <h1 class="uk-heading-small">Dettaglio evento</h1>

    <div class="uk-card uk-card-default uk-card-body uk-border-rounded uk-margin-bottom">
        <div class="uk-flex uk-flex-middle">
            <span uk-icon="icon: info; ratio: 1.5" class="uk-margin-right uk-text-muted"></span>
            <div>
                <h3 class="uk-card-title uk-margin-remove">Modulo in sviluppo</h3>
                <p class="uk-text-muted uk-margin-remove">
                    Il dettaglio dell'evento sarà disponibile in una prossima versione.
                </p>
            </div>
        </div>
    </div>

    <a href="/index.php?route=events" class="uk-button uk-button-text">
        ← Torna agli eventi
    </a>
    <?php
    return (string) ob_get_clean();
})();

require __DIR__ . '/../layouts/main.php';
