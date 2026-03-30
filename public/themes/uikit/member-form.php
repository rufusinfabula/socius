<?php
$e       = fn($v) => htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8');
$isEdit  = $isEdit ?? false;
$heading = $isEdit ? 'Modifica Socio' : 'Nuovo Socio';
$action  = $isEdit
    ? 'member-edit.php?id=' . (int) ($member['id'] ?? 0)
    : 'member-new.php';
$v = fn(string $field, mixed $default = '') => $e($member[$field] ?? $default);

$statusLabels = [
    'active'    => 'Attivo',
    'suspended' => 'Sospeso',
    'expired'   => 'Scaduto',
    'resigned'  => 'Dimesso',
    'deceased'  => 'Deceduto',
];

$content = (function () use (
    $member, $categories, $currentUser, $isEdit, $heading, $action, $error,
    $e, $v, $statusLabels
): string {
    ob_start();
    $isStaff = (int) ($currentUser['role_id'] ?? 4) <= 3;
    ?>

    <?php if (!empty($error)): ?>
        <div class="uk-alert-danger" uk-alert>
            <a class="uk-alert-close" uk-close></a>
            <p><?= $e($error) ?></p>
        </div>
    <?php endif; ?>

    <!-- Breadcrumb -->
    <ul class="uk-breadcrumb uk-margin-small-bottom">
        <li><a href="members.php">Lista Soci</a></li>
        <?php if ($isEdit && !empty($member['id'])): ?>
            <li>
                <a href="member.php?id=<?= (int) $member['id'] ?>">
                    <?= $e(($member['surname'] ?? '') . ' ' . ($member['name'] ?? '')) ?>
                </a>
            </li>
        <?php endif; ?>
        <li><span><?= $e($heading) ?></span></li>
    </ul>

    <h1 class="uk-heading-small uk-margin-bottom"><?= $e($heading) ?></h1>

    <form method="POST" action="<?= $e($action) ?>" novalidate class="uk-form-stacked">
        <?= csrf_field() ?>

        <div class="uk-grid uk-grid-medium" uk-grid>

            <!-- Anagrafica -->
            <div class="uk-width-1-2@m">
                <div class="uk-card uk-card-default uk-card-body uk-border-rounded">
                    <h3 class="uk-card-title">Anagrafica</h3>

                    <div class="uk-margin">
                        <label class="uk-form-label" for="surname">Cognome *</label>
                        <input class="uk-input" type="text" id="surname" name="surname"
                               value="<?= $v('surname') ?>" required autofocus>
                    </div>
                    <div class="uk-margin">
                        <label class="uk-form-label" for="name">Nome *</label>
                        <input class="uk-input" type="text" id="name" name="name"
                               value="<?= $v('name') ?>" required>
                    </div>
                    <div class="uk-margin">
                        <label class="uk-form-label" for="email">Email *</label>
                        <input class="uk-input" type="email" id="email" name="email"
                               value="<?= $v('email') ?>" required autocomplete="email">
                    </div>
                    <div class="uk-margin">
                        <label class="uk-form-label" for="phone">Telefono</label>
                        <input class="uk-input" type="tel" id="phone" name="phone"
                               value="<?= $v('phone') ?>">
                    </div>
                    <div class="uk-margin">
                        <label class="uk-form-label" for="fiscal_code">Codice fiscale</label>
                        <input class="uk-input" type="text" id="fiscal_code" name="fiscal_code"
                               value="<?= $v('fiscal_code') ?>" maxlength="16"
                               style="text-transform:uppercase">
                    </div>
                    <div class="uk-grid uk-grid-small" uk-grid>
                        <div class="uk-width-1-2">
                            <label class="uk-form-label" for="birth_date">Data di nascita</label>
                            <input class="uk-input" type="date" id="birth_date" name="birth_date"
                                   value="<?= $v('birth_date') ?>">
                        </div>
                        <div class="uk-width-1-2">
                            <label class="uk-form-label" for="birth_place">Luogo di nascita</label>
                            <input class="uk-input" type="text" id="birth_place" name="birth_place"
                                   value="<?= $v('birth_place') ?>">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Indirizzo e tessera -->
            <div class="uk-width-1-2@m">
                <div class="uk-card uk-card-default uk-card-body uk-border-rounded uk-margin-bottom">
                    <h3 class="uk-card-title">Indirizzo</h3>

                    <div class="uk-margin">
                        <label class="uk-form-label" for="address">Via/Piazza</label>
                        <input class="uk-input" type="text" id="address" name="address"
                               value="<?= $v('address') ?>">
                    </div>
                    <div class="uk-grid uk-grid-small" uk-grid>
                        <div class="uk-width-1-3">
                            <label class="uk-form-label" for="postal_code">CAP</label>
                            <input class="uk-input" type="text" id="postal_code" name="postal_code"
                                   value="<?= $v('postal_code') ?>" maxlength="10">
                        </div>
                        <div class="uk-width-1-3">
                            <label class="uk-form-label" for="city">Città</label>
                            <input class="uk-input" type="text" id="city" name="city"
                                   value="<?= $v('city') ?>">
                        </div>
                        <div class="uk-width-1-3">
                            <label class="uk-form-label" for="province">Prov.</label>
                            <input class="uk-input" type="text" id="province" name="province"
                                   value="<?= $v('province') ?>" maxlength="5"
                                   style="text-transform:uppercase">
                        </div>
                    </div>
                    <div class="uk-margin">
                        <label class="uk-form-label" for="country">Paese</label>
                        <input class="uk-input" type="text" id="country" name="country"
                               value="<?= $v('country', 'IT') ?>" maxlength="2"
                               style="text-transform:uppercase">
                    </div>
                </div>

                <div class="uk-card uk-card-default uk-card-body uk-border-rounded">
                    <h3 class="uk-card-title">Tessera</h3>

                    <div class="uk-margin">
                        <label class="uk-form-label" for="status">Stato</label>
                        <select class="uk-select" id="status" name="status">
                            <?php foreach ($statusLabels as $s => $label): ?>
                                <option value="<?= $e($s) ?>"
                                    <?= ($member['status'] ?? 'active') === $s ? 'selected' : '' ?>>
                                    <?= $e($label) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <?php if (!empty($categories)): ?>
                    <div class="uk-margin">
                        <label class="uk-form-label" for="category_id">Categoria</label>
                        <select class="uk-select" id="category_id" name="category_id">
                            <option value="">— Nessuna categoria —</option>
                            <?php foreach ($categories as $cat): ?>
                                <option value="<?= (int) $cat['id'] ?>"
                                    <?= (int) ($member['category_id'] ?? 0) === (int) $cat['id'] ? 'selected' : '' ?>>
                                    <?= $e($cat['label']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <?php endif; ?>

                    <div class="uk-margin">
                        <label class="uk-form-label" for="joined_on">Data iscrizione *</label>
                        <input class="uk-input" type="date" id="joined_on" name="joined_on"
                               value="<?= $v('joined_on', date('Y-m-d')) ?>" required>
                    </div>
                    <div class="uk-margin">
                        <label class="uk-form-label" for="resigned_on">Data dimissioni</label>
                        <input class="uk-input" type="date" id="resigned_on" name="resigned_on"
                               value="<?= $v('resigned_on') ?>">
                    </div>
                </div>
            </div>

            <!-- Note (staff only) -->
            <?php if ($isStaff): ?>
            <div class="uk-width-1-1">
                <div class="uk-card uk-card-default uk-card-body uk-border-rounded">
                    <label class="uk-form-label" for="notes">
                        Note <span class="uk-text-muted uk-text-small">(visibili solo agli amministratori)</span>
                    </label>
                    <textarea class="uk-textarea" id="notes" name="notes" rows="3"><?= $v('notes') ?></textarea>
                </div>
            </div>
            <?php endif; ?>

        </div>

        <div class="uk-margin-top uk-flex uk-flex-between">
            <a href="<?= $isEdit && !empty($member['id']) ? 'member.php?id=' . (int) $member['id'] : 'members.php' ?>"
               class="uk-button uk-button-default">
                Annulla
            </a>
            <button class="uk-button uk-button-primary" type="submit">Salva</button>
        </div>

    </form>

    <?php
    return (string) ob_get_clean();
})();

require __DIR__ . '/layout.php';
