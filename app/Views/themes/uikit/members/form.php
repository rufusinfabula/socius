<?php
// Variables: $member (null=create, array=edit), $categories, $currentUser, $csrf, $flash, $activeNav
$isEdit  = $member !== null;
$heading = $isEdit ? __('members.edit_member') : __('members.new_member');
$action  = $isEdit
    ? '/index.php?route=members/' . (int) $member['id'] . '/edit'
    : '/index.php?route=members';

$v = fn(string $field, mixed $default = '') => e($member[$field] ?? $default);

$content = (function () use ($member, $categories, $currentUser, $csrf, $flash, $isEdit, $heading, $action, $v): string {
    ob_start();
    ?>

    <?php if (!empty($flash['error'])): ?>
        <div class="uk-alert-danger" uk-alert><a class="uk-alert-close" uk-close></a><p><?= e($flash['error']) ?></p></div>
    <?php endif; ?>

    <!-- Breadcrumb -->
    <ul class="uk-breadcrumb uk-margin-small-bottom">
        <li><a href="/index.php?route=members"><?= e(__('members.member_list')) ?></a></li>
        <?php if ($isEdit): ?>
            <li><a href="/index.php?route=members/<?= (int) $member['id'] ?>"><?= e($member['surname'] . ' ' . $member['name']) ?></a></li>
        <?php endif; ?>
        <li><span><?= e($heading) ?></span></li>
    </ul>

    <h1 class="uk-heading-small uk-margin-bottom"><?= e($heading) ?></h1>

    <form method="POST" action="<?= e($action) ?>" novalidate class="uk-form-stacked">
        <input type="hidden" name="_csrf_token" value="<?= e($csrf) ?>">

        <div class="uk-grid uk-grid-medium" uk-grid>

            <!-- Anagrafica -->
            <div class="uk-width-1-2@m">
                <div class="uk-card uk-card-default uk-card-body uk-border-rounded">
                    <h3 class="uk-card-title">Anagrafica</h3>

                    <div class="uk-margin">
                        <label class="uk-form-label" for="surname"><?= e(__('members.surname')) ?> *</label>
                        <input class="uk-input" type="text" id="surname" name="surname"
                               value="<?= $v('surname') ?>" required autofocus>
                    </div>

                    <div class="uk-margin">
                        <label class="uk-form-label" for="name"><?= e(__('members.name')) ?> *</label>
                        <input class="uk-input" type="text" id="name" name="name"
                               value="<?= $v('name') ?>" required>
                    </div>

                    <div class="uk-margin">
                        <label class="uk-form-label" for="email"><?= e(__('members.email')) ?> *</label>
                        <input class="uk-input" type="email" id="email" name="email"
                               value="<?= $v('email') ?>" required autocomplete="email">
                    </div>

                    <div class="uk-margin">
                        <label class="uk-form-label" for="phone"><?= e(__('members.phone')) ?></label>
                        <input class="uk-input" type="tel" id="phone" name="phone"
                               value="<?= $v('phone') ?>">
                    </div>

                    <div class="uk-margin">
                        <label class="uk-form-label" for="fiscal_code"><?= e(__('members.fiscal_code')) ?></label>
                        <input class="uk-input" type="text" id="fiscal_code" name="fiscal_code"
                               value="<?= $v('fiscal_code') ?>" maxlength="16"
                               style="text-transform:uppercase">
                    </div>

                    <div class="uk-grid uk-grid-small" uk-grid>
                        <div class="uk-width-1-2">
                            <label class="uk-form-label" for="birth_date"><?= e(__('members.birth_date')) ?></label>
                            <input class="uk-input" type="date" id="birth_date" name="birth_date"
                                   value="<?= $v('birth_date') ?>">
                        </div>
                        <div class="uk-width-1-2">
                            <label class="uk-form-label" for="birth_place"><?= e(__('members.birth_place')) ?></label>
                            <input class="uk-input" type="text" id="birth_place" name="birth_place"
                                   value="<?= $v('birth_place') ?>">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Indirizzo e membership -->
            <div class="uk-width-1-2@m">
                <div class="uk-card uk-card-default uk-card-body uk-border-rounded uk-margin-bottom">
                    <h3 class="uk-card-title">Indirizzo</h3>

                    <div class="uk-margin">
                        <label class="uk-form-label" for="address"><?= e(__('members.address')) ?></label>
                        <input class="uk-input" type="text" id="address" name="address"
                               value="<?= $v('address') ?>">
                    </div>

                    <div class="uk-grid uk-grid-small" uk-grid>
                        <div class="uk-width-1-3">
                            <label class="uk-form-label" for="postal_code"><?= e(__('members.postal_code')) ?></label>
                            <input class="uk-input" type="text" id="postal_code" name="postal_code"
                                   value="<?= $v('postal_code') ?>" maxlength="10">
                        </div>
                        <div class="uk-width-1-3">
                            <label class="uk-form-label" for="city"><?= e(__('members.city')) ?></label>
                            <input class="uk-input" type="text" id="city" name="city"
                                   value="<?= $v('city') ?>">
                        </div>
                        <div class="uk-width-1-3">
                            <label class="uk-form-label" for="province"><?= e(__('members.province')) ?></label>
                            <input class="uk-input" type="text" id="province" name="province"
                                   value="<?= $v('province') ?>" maxlength="5"
                                   style="text-transform:uppercase">
                        </div>
                    </div>

                    <div class="uk-margin">
                        <label class="uk-form-label" for="country"><?= e(__('members.country')) ?></label>
                        <input class="uk-input" type="text" id="country" name="country"
                               value="<?= $v('country', 'IT') ?>" maxlength="2"
                               style="text-transform:uppercase">
                    </div>
                </div>

                <!-- Membership -->
                <div class="uk-card uk-card-default uk-card-body uk-border-rounded">
                    <h3 class="uk-card-title">Tessera</h3>

                    <div class="uk-margin">
                        <label class="uk-form-label" for="status"><?= e(__('members.status')) ?></label>
                        <select class="uk-select" id="status" name="status">
                            <?php foreach (['active','suspended','expired','resigned','deceased'] as $s): ?>
                                <option value="<?= e($s) ?>" <?= ($member['status'] ?? 'active') === $s ? 'selected' : '' ?>>
                                    <?= e(__('members.status_' . $s)) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="uk-margin">
                        <label class="uk-form-label" for="category_id"><?= e(__('members.category')) ?></label>
                        <select class="uk-select" id="category_id" name="category_id">
                            <option value=""><?= e(__('members.filter_all_categories')) ?></option>
                            <?php foreach ($categories as $cat): ?>
                                <option value="<?= (int) $cat['id'] ?>"
                                    <?= (int) ($member['category_id'] ?? 0) === (int) $cat['id'] ? 'selected' : '' ?>>
                                    <?= e($cat['label']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="uk-margin">
                        <label class="uk-form-label" for="joined_on"><?= e(__('members.joined_on')) ?> *</label>
                        <input class="uk-input" type="date" id="joined_on" name="joined_on"
                               value="<?= $v('joined_on', date('Y-m-d')) ?>" required>
                    </div>

                    <div class="uk-margin">
                        <label class="uk-form-label" for="resigned_on"><?= e(__('members.resigned_on')) ?></label>
                        <input class="uk-input" type="date" id="resigned_on" name="resigned_on"
                               value="<?= $v('resigned_on') ?>">
                    </div>
                </div>
            </div>

            <!-- Notes (admin only) -->
            <?php if ((int) $currentUser['role_id'] <= 3): ?>
            <div class="uk-width-1-1">
                <div class="uk-card uk-card-default uk-card-body uk-border-rounded">
                    <label class="uk-form-label" for="notes"><?= e(__('members.notes')) ?></label>
                    <textarea class="uk-textarea" id="notes" name="notes" rows="3"><?= $v('notes') ?></textarea>
                </div>
            </div>
            <?php endif; ?>

        </div>

        <!-- Actions -->
        <div class="uk-margin-top uk-flex uk-flex-between">
            <a href="<?= $isEdit ? '/index.php?route=members/' . (int) $member['id'] : '/index.php?route=members' ?>"
               class="uk-button uk-button-default">
                <?= e(__('members.cancel')) ?>
            </a>
            <button class="uk-button uk-button-primary" type="submit">
                <?= e(__('members.save')) ?>
            </button>
        </div>

    </form>

    <?php
    return (string) ob_get_clean();
})();

require __DIR__ . '/../layouts/main.php';
