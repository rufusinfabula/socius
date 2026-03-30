<?php
// Variables: $activeNav

$content = (function (): string {
    ob_start();
    ?>
    <h1 class="uk-heading-small">
        <span uk-icon="icon: calendar; ratio: 1.4" class="uk-margin-small-right"></span>
        Eventi
    </h1>

    <div class="uk-card uk-card-default uk-card-body uk-border-rounded uk-margin-bottom">
        <div class="uk-flex uk-flex-middle">
            <span uk-icon="icon: info; ratio: 1.5" class="uk-margin-right uk-text-muted"></span>
            <div>
                <h3 class="uk-card-title uk-margin-remove">Modulo eventi in arrivo</h3>
                <p class="uk-text-muted uk-margin-remove">
                    La gestione degli eventi sarà disponibile in una prossima versione.
                </p>
            </div>
        </div>
    </div>
    <?php
    return (string) ob_get_clean();
})();

require __DIR__ . '/../layouts/main.php';
