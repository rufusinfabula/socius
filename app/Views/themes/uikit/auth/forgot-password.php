<!DOCTYPE html>
<html lang="<?= e(\Socius\Core\Lang::getLocale()) ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e(__('auth.forgot_heading')) ?> — <?= e(\Socius\Core\Config::get('app.name', 'Socius')) ?></title>
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
            <h2 class="uk-card-title uk-margin-small-top"><?= e(__('auth.forgot_heading')) ?></h2>
            <p class="uk-text-small uk-text-muted"><?= e(__('auth.forgot_intro')) ?></p>
        </div>

        <?php if (!empty($error)): ?>
            <div class="uk-alert-danger" uk-alert>
                <a class="uk-alert-close" uk-close></a>
                <p><?= e($error) ?></p>
            </div>
        <?php endif; ?>

        <?php if (!empty($success)): ?>
            <div class="uk-alert-success" uk-alert>
                <p><?= e($success) ?></p>
            </div>
        <?php else: ?>

        <form method="POST" action="/forgot-password" novalidate>
            <input type="hidden" name="_csrf_token" value="<?= e($csrf) ?>">

            <div class="uk-margin">
                <label class="uk-form-label" for="email"><?= e(__('auth.email')) ?></label>
                <div class="uk-form-controls">
                    <input
                        class="uk-input"
                        type="email"
                        id="email"
                        name="email"
                        placeholder="<?= e(__('auth.email_placeholder')) ?>"
                        required
                        autofocus
                        autocomplete="email"
                    >
                </div>
            </div>

            <div class="uk-margin">
                <button class="uk-button uk-button-primary uk-width-1-1" type="submit">
                    <?= e(__('auth.send_reset_link')) ?>
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
