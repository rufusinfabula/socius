<!DOCTYPE html>
<html lang="<?= htmlspecialchars(\Socius\Core\Lang::getLocale(), ENT_QUOTES, 'UTF-8') ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reimposta password — <?= htmlspecialchars((string) \Socius\Core\Config::get('app.name', 'Socius'), ENT_QUOTES, 'UTF-8') ?></title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/uikit@3/dist/css/uikit.min.css">
    <style>
        body { background: #f5f5f5; }
        .auth-card { max-width: 420px; margin: 80px auto; }
        .auth-logo { font-size: 1.6rem; font-weight: 700; color: #1e87f0; }
    </style>
</head>
<body>

<div class="auth-card uk-padding">
    <div class="uk-card uk-card-default uk-card-body uk-border-rounded">

        <div class="uk-text-center uk-margin-bottom">
            <div class="auth-logo"><?= htmlspecialchars((string) \Socius\Core\Config::get('app.name', 'Socius'), ENT_QUOTES, 'UTF-8') ?></div>
            <h2 class="uk-card-title uk-margin-small-top">Nuova password</h2>
        </div>

        <?php if ($tokenInvalid ?? false): ?>
            <div class="uk-alert-danger" uk-alert>
                <p>Il link di reset non è valido o è scaduto.
                   <a href="forgot-password.php">Richiedine uno nuovo</a>.
                </p>
            </div>
        <?php else: ?>

            <?php if (!empty($error)): ?>
                <div class="uk-alert-danger" uk-alert>
                    <a class="uk-alert-close" uk-close></a>
                    <p><?= htmlspecialchars((string) $error, ENT_QUOTES, 'UTF-8') ?></p>
                </div>
            <?php endif; ?>

            <form method="POST"
                  action="reset-password.php?token=<?= urlencode((string) ($token ?? '')) ?>"
                  novalidate>
                <?= csrf_field() ?>

                <div class="uk-margin">
                    <label class="uk-form-label" for="password">Nuova password</label>
                    <input class="uk-input" type="password" id="password" name="password"
                           required minlength="8" autocomplete="new-password" autofocus>
                    <p class="uk-text-small uk-text-muted uk-margin-remove">Almeno 8 caratteri.</p>
                </div>

                <div class="uk-margin">
                    <label class="uk-form-label" for="password_confirmation">Conferma password</label>
                    <input class="uk-input" type="password" id="password_confirmation"
                           name="password_confirmation" required autocomplete="new-password">
                </div>

                <div class="uk-margin">
                    <button class="uk-button uk-button-primary uk-width-1-1" type="submit">
                        Salva nuova password
                    </button>
                </div>
            </form>

        <?php endif; ?>

        <div class="uk-text-center uk-text-small uk-margin-top">
            <a href="login.php">← Torna al login</a>
        </div>

    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/uikit@3/dist/js/uikit.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/uikit@3/dist/js/uikit-icons.min.js"></script>
</body>
</html>
