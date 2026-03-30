<!DOCTYPE html>
<html lang="<?= htmlspecialchars(\Socius\Core\Lang::getLocale(), ENT_QUOTES, 'UTF-8') ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Accedi — <?= htmlspecialchars((string) \Socius\Core\Config::get('app.name', 'Socius'), ENT_QUOTES, 'UTF-8') ?></title>
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
            <h2 class="uk-card-title uk-margin-small-top">Accedi al pannello</h2>
        </div>

        <?php if (!empty($error)): ?>
            <div class="uk-alert-danger" uk-alert>
                <a class="uk-alert-close" uk-close></a>
                <p><?= htmlspecialchars((string) $error, ENT_QUOTES, 'UTF-8') ?></p>
            </div>
        <?php endif; ?>

        <?php if (!empty($info)): ?>
            <div class="uk-alert-primary" uk-alert>
                <a class="uk-alert-close" uk-close></a>
                <p><?= htmlspecialchars((string) $info, ENT_QUOTES, 'UTF-8') ?></p>
            </div>
        <?php endif; ?>

        <?php if (!empty($success)): ?>
            <div class="uk-alert-success" uk-alert>
                <a class="uk-alert-close" uk-close></a>
                <p><?= htmlspecialchars((string) $success, ENT_QUOTES, 'UTF-8') ?></p>
            </div>
        <?php endif; ?>

        <form method="POST" action="login.php" novalidate>
            <?= csrf_field() ?>

            <div class="uk-margin">
                <label class="uk-form-label" for="email">Indirizzo email</label>
                <div class="uk-form-controls">
                    <input
                        class="uk-input"
                        type="email"
                        id="email"
                        name="email"
                        placeholder="nome@esempio.it"
                        value="<?= htmlspecialchars((string) ($emailVal ?? ''), ENT_QUOTES, 'UTF-8') ?>"
                        required
                        autofocus
                        autocomplete="email"
                    >
                </div>
            </div>

            <div class="uk-margin">
                <label class="uk-form-label" for="password">Password</label>
                <div class="uk-form-controls">
                    <input
                        class="uk-input"
                        type="password"
                        id="password"
                        name="password"
                        required
                        autocomplete="current-password"
                    >
                </div>
            </div>

            <div class="uk-margin uk-text-right">
                <a href="forgot-password.php" class="uk-text-small">Password dimenticata?</a>
            </div>

            <div class="uk-margin">
                <button class="uk-button uk-button-primary uk-width-1-1" type="submit">
                    Accedi
                </button>
            </div>
        </form>

        <div class="uk-text-center uk-text-small uk-text-muted uk-margin-top">
            Non hai un account? Contatta l'amministratore.
        </div>

    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/uikit@3/dist/js/uikit.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/uikit@3/dist/js/uikit-icons.min.js"></script>
</body>
</html>
