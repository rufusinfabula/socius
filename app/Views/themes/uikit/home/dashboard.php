<?php
$content = (function () use ($user): string {
    ob_start();
    $name = trim(($user['name'] ?? '') . ' ' . ($user['surname'] ?? ''));
    ?>
    <h1 class="uk-heading-small">Dashboard</h1>

    <div class="uk-card uk-card-primary uk-card-body uk-border-rounded uk-margin-bottom">
        <h3 class="uk-card-title">
            Benvenuto in Socius<?= $name !== '' ? ', ' . e($name) : '' ?>!
        </h3>
        <p class="uk-text-lead uk-margin-remove">
            Il pannello di gestione dell'associazione.
        </p>
    </div>

    <div class="uk-grid uk-grid-small uk-child-width-1-3@m" uk-grid>
        <div>
            <div class="uk-card uk-card-default uk-card-body uk-border-rounded uk-text-center">
                <span class="uk-icon uk-icon-large" uk-icon="users"></span>
                <h4 class="uk-margin-small-top uk-margin-remove-bottom">Soci</h4>
                <p class="uk-text-muted uk-text-small uk-margin-remove">Gestisci i soci</p>
                <a href="/members" class="uk-button uk-button-default uk-button-small uk-margin-small-top">
                    Vai ai soci
                </a>
            </div>
        </div>
        <div>
            <div class="uk-card uk-card-default uk-card-body uk-border-rounded uk-text-center">
                <span class="uk-icon uk-icon-large" uk-icon="calendar"></span>
                <h4 class="uk-margin-small-top uk-margin-remove-bottom">Eventi</h4>
                <p class="uk-text-muted uk-text-small uk-margin-remove">Gestisci gli eventi</p>
                <a href="/events" class="uk-button uk-button-default uk-button-small uk-margin-small-top">
                    Vai agli eventi
                </a>
            </div>
        </div>
        <div>
            <div class="uk-card uk-card-default uk-card-body uk-border-rounded uk-text-center">
                <span class="uk-icon uk-icon-large" uk-icon="credit-card"></span>
                <h4 class="uk-margin-small-top uk-margin-remove-bottom">Pagamenti</h4>
                <p class="uk-text-muted uk-text-small uk-margin-remove">Gestisci i pagamenti</p>
                <a href="/payments" class="uk-button uk-button-default uk-button-small uk-margin-small-top">
                    Vai ai pagamenti
                </a>
            </div>
        </div>
    </div>
    <?php
    return (string) ob_get_clean();
})();

require __DIR__ . '/../layouts/main.php';
