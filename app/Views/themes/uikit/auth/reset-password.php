<!DOCTYPE html>
<html lang="<?= e(\Socius\Core\Lang::getLocale()) ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e(__('auth.reset_heading')) ?> — <?= e(\Socius\Core\Config::get('app.name', 'Socius')) ?></title>
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
            <div class="auth-logo"><?= e(\Socius\Core\Config::get('app.name', 'Socius')) ?></div>
            <h2 class="uk-card-title uk-margin-small-top"><?= e(__('auth.reset_heading')) ?></h2>
            <?php if (!($tokenInvalid ?? false)): ?>
                <p class="uk-text-small uk-text-muted"><?= e(__('auth.reset_intro')) ?></p>
            <?php endif; ?>
        </div>

        <?php if ($tokenInvalid ?? false): ?>

            <div class="uk-alert-danger" uk-alert>
                <p><?= e(__('auth.reset_token_invalid')) ?></p>
            </div>
            <div class="uk-text-center uk-margin-top">
                <a href="/forgot-password" class="uk-button uk-button-default">
                    <?= e(__('auth.send_reset_link')) ?>
                </a>
            </div>

        <?php else: ?>

            <?php if (!empty($error)): ?>
                <div class="uk-alert-danger" uk-alert>
                    <a class="uk-alert-close" uk-close></a>
                    <p><?= e($error) ?></p>
                </div>
            <?php endif; ?>

            <form method="POST" action="/reset-password/<?= e($token) ?>" novalidate>
                <input type="hidden" name="_csrf_token" value="<?= e($csrf) ?>">

                <div class="uk-margin">
                    <label class="uk-form-label" for="password"><?= e(__('auth.new_password')) ?></label>
                    <div class="uk-form-controls">
                        <input
                            class="uk-input"
                            type="password"
                            id="password"
                            name="password"
                            required
                            autofocus
                            autocomplete="new-password"
                            minlength="8"
                        >
                    </div>
                </div>

                <div class="uk-margin">
                    <label class="uk-form-label" for="password_confirmation"><?= e(__('auth.confirm_password')) ?></label>
                    <div class="uk-form-controls">
                        <input
                            class="uk-input"
                            type="password"
                            id="password_confirmation"
                            name="password_confirmation"
                            required
                            autocomplete="new-password"
                            minlength="8"
                        >
                    </div>
                </div>

                <div class="uk-margin">
                    <button class="uk-button uk-button-primary uk-width-1-1" type="submit">
                        <?= e(__('auth.reset_password')) ?>
                    </button>
                </div>
            </form>

        <?php endif; ?>

        <div class="uk-text-center uk-margin-top">
            <a href="/login" class="uk-text-small"><?= e(__('auth.back_to_login')) ?></a>
        </div>

    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/uikit@3/dist/js/uikit.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/uikit@3/dist/js/uikit-icons.min.js"></script>
</body>
</html>
