<?php
$e  = fn($v) => htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8');
// Helper: read a setting value from the grouped $settings array
$sv = fn(string $key, string $default = '') => (string) ($settings[explode('.', $key, 2)[0]][$key] ?? $default);
// Helper: read and escape a setting value
$se = fn(string $key, string $default = '') => $e($settings[explode('.', $key, 2)[0]][$key] ?? $default);

$content = (function () use (
    $e, $sv, $se,
    $activeTab, $settings, $categories, $categoryFees, $boardRoles,
    $languages, $memberCurrentMax, $currentUser, $isSuperAdmin
): string {
    ob_start();
    ?>

    <h1 class="uk-heading-small">
        <span uk-icon="icon: settings; ratio: 1.4" class="uk-margin-small-right"></span>
        <?= $e(__('settings.settings')) ?>
    </h1>

    <!-- =====================================================================
         Tab navigation
         ===================================================================== -->
    <ul id="settings-tabs" uk-tab>
        <li data-tab="association"><a href="#"><?= $e(__('settings.tab_association')) ?></a></li>
        <li data-tab="social_year"><a href="#"><?= $e(__('settings.tab_social_year')) ?></a></li>
        <li data-tab="categories"><a href="#"><?= $e(__('settings.tab_categories')) ?></a></li>
        <li data-tab="board_roles"><a href="#"><?= $e(__('settings.tab_board_roles')) ?></a></li>
        <li data-tab="interface"><a href="#"><?= $e(__('settings.tab_interface')) ?></a></li>
        <li data-tab="email"><a href="#"><?= $e(__('settings.tab_email')) ?></a></li>
        <li data-tab="member_number"><a href="#"><?= $e(__('settings.tab_member_number')) ?></a></li>
    </ul>

    <ul class="uk-switcher uk-margin">

        <!-- =================================================================
             TAB 1 — ASSOCIAZIONE
             ================================================================= -->
        <li>
        <form method="post" action="settings.php?tab=association" enctype="multipart/form-data">
            <?= csrf_field() ?>
            <div class="uk-grid uk-grid-medium" uk-grid>

                <!-- Column 1: Identity -->
                <div class="uk-width-1-2@m">
                    <div class="uk-card uk-card-default uk-card-body uk-border-rounded uk-margin-bottom">
                        <h3 class="uk-card-title"><?= $e(__('settings.tab_association')) ?></h3>

                        <div class="uk-margin">
                            <label class="uk-form-label"><?= $e(__('settings.assoc_name')) ?></label>
                            <input class="uk-input" type="text" name="association_name"
                                   value="<?= $se('association.name') ?>">
                        </div>
                        <div class="uk-margin">
                            <label class="uk-form-label"><?= $e(__('settings.assoc_fiscal_code')) ?></label>
                            <input class="uk-input" type="text" name="association_fiscal_code"
                                   value="<?= $se('association.fiscal_code') ?>">
                        </div>
                        <div class="uk-margin">
                            <label class="uk-form-label"><?= $e(__('settings.assoc_vat_number')) ?></label>
                            <input class="uk-input" type="text" name="association_vat_number"
                                   value="<?= $se('association.vat_number') ?>">
                        </div>
                    </div>

                    <div class="uk-card uk-card-default uk-card-body uk-border-rounded uk-margin-bottom">
                        <h3 class="uk-card-title"><?= $e(__('settings.assoc_logo')) ?></h3>

                        <?php $logoPath = $sv('association.logo_path'); ?>
                        <?php if ($logoPath !== ''): ?>
                            <div class="uk-margin">
                                <p class="uk-text-muted uk-text-small"><?= $e(__('settings.assoc_logo_current')) ?></p>
                                <img src="<?= $e($logoPath) ?>"
                                     alt="Logo" style="max-height:80px; max-width:200px; display:block; margin-bottom:8px">
                                <label class="uk-flex uk-flex-middle" style="gap:.4rem; cursor:pointer">
                                    <input class="uk-checkbox" type="checkbox" name="remove_logo" value="1">
                                    <span class="uk-text-small uk-text-danger">Rimuovi logo</span>
                                </label>
                            </div>
                        <?php else: ?>
                            <p class="uk-text-muted uk-text-small"><?= $e(__('settings.assoc_logo_remove')) ?></p>
                        <?php endif; ?>

                        <div class="uk-margin">
                            <label class="uk-form-label"><?= $e(__('settings.assoc_logo_upload')) ?></label>
                            <input class="uk-input" type="file" name="logo" accept=".png,.jpg,.jpeg,.svg">
                            <p class="uk-text-small uk-text-muted uk-margin-remove-top">
                                <?= $e(__('settings.assoc_logo_hint')) ?>
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Column 2: Address & Contacts -->
                <div class="uk-width-1-2@m">
                    <div class="uk-card uk-card-default uk-card-body uk-border-rounded uk-margin-bottom">
                        <h3 class="uk-card-title"><?= $e(__('members.box_contacts')) ?></h3>

                        <div class="uk-margin">
                            <label class="uk-form-label"><?= $e(__('settings.assoc_address')) ?></label>
                            <input class="uk-input" type="text" name="association_address"
                                   value="<?= $se('association.address') ?>">
                        </div>
                        <div class="uk-grid uk-grid-small" uk-grid>
                            <div class="uk-width-1-4">
                                <label class="uk-form-label"><?= $e(__('members.postal_code')) ?></label>
                                <input class="uk-input" type="text" name="association_postal_code"
                                       value="<?= $se('association.postal_code') ?>">
                            </div>
                            <div class="uk-width-expand">
                                <label class="uk-form-label"><?= $e(__('settings.assoc_city')) ?></label>
                                <input class="uk-input" type="text" name="association_city"
                                       value="<?= $se('association.city') ?>">
                            </div>
                            <div class="uk-width-1-5">
                                <label class="uk-form-label"><?= $e(__('members.province')) ?></label>
                                <input class="uk-input" type="text" name="association_province" maxlength="5"
                                       value="<?= $se('association.province') ?>">
                            </div>
                        </div>
                        <div class="uk-margin">
                            <label class="uk-form-label"><?= $e(__('settings.assoc_country')) ?></label>
                            <input class="uk-input" type="text" name="association_country" maxlength="2"
                                   value="<?= $e($sv('association.country', 'IT')) ?>">
                        </div>
                        <div class="uk-margin">
                            <label class="uk-form-label"><?= $e(__('settings.assoc_email')) ?></label>
                            <input class="uk-input" type="email" name="association_email"
                                   value="<?= $se('association.email') ?>">
                        </div>
                        <div class="uk-margin">
                            <label class="uk-form-label"><?= $e(__('settings.assoc_phone')) ?></label>
                            <input class="uk-input" type="tel" name="association_phone"
                                   value="<?= $se('association.phone') ?>">
                        </div>
                        <div class="uk-margin">
                            <label class="uk-form-label"><?= $e(__('settings.assoc_website')) ?></label>
                            <input class="uk-input" type="url" name="association_website"
                                   value="<?= $se('association.website') ?>">
                        </div>
                    </div>
                </div>
            </div>

            <button type="submit" class="uk-button uk-button-primary">
                <span uk-icon="check"></span> <?= $e(__('settings.save')) ?>
            </button>
        </form>
        </li>

        <!-- =================================================================
             TAB 2 — ANNO SOCIALE
             ================================================================= -->
        <li>
        <form method="post" action="settings.php?tab=social_year">
            <?= csrf_field() ?>
            <div class="uk-grid uk-grid-medium" uk-grid>

                <div class="uk-width-1-2@m">
                    <div class="uk-card uk-card-default uk-card-body uk-border-rounded uk-margin-bottom">
                        <h3 class="uk-card-title"><?= $e(__('settings.tab_social_year')) ?></h3>
                        <p class="uk-text-small uk-text-muted">
                            Seleziona mese e giorno per ogni scadenza. L'anno non è rilevante.
                        </p>

                        <?php
                        $renewalFields = [
                            'renewal.date_open'            => 'renewal_open',
                            'renewal.date_first_reminder'  => 'renewal_first_reminder',
                            'renewal.date_second_reminder' => 'renewal_second_reminder',
                            'renewal.date_third_reminder'  => 'renewal_third_reminder',
                            'renewal.date_close'           => 'renewal_close',
                            'renewal.date_lapse'           => 'renewal_lapse',
                        ];
                        $renewalDefaults = [
                            'renewal.date_open'            => '11-15',
                            'renewal.date_first_reminder'  => '02-15',
                            'renewal.date_second_reminder' => '03-15',
                            'renewal.date_third_reminder'  => '04-15',
                            'renewal.date_close'           => '04-15',
                            'renewal.date_lapse'           => '12-31',
                        ];
                        // Convert stored MM-DD to a full date value for type="date" using year 2000
                        $toDateValue = static function (string $mmdd): string {
                            if (preg_match('/^(\d{2})-(\d{2})$/', $mmdd, $m)) {
                                return '2000-' . $m[1] . '-' . $m[2];
                            }
                            return '';
                        };
                        $monthNames = [
                            1=>'Gennaio',2=>'Febbraio',3=>'Marzo',4=>'Aprile',
                            5=>'Maggio',6=>'Giugno',7=>'Luglio',8=>'Agosto',
                            9=>'Settembre',10=>'Ottobre',11=>'Novembre',12=>'Dicembre',
                        ];
                        foreach ($renewalFields as $key => $langKey):
                            $fieldName  = str_replace('renewal.date_', 'renewal_date_', $key);
                            $mmdd       = $sv($key, $renewalDefaults[$key]);
                            $dateValue  = $toDateValue($mmdd);
                            // Parse current value for display label
                            $parts = explode('-', $mmdd);
                            $displayLabel = (count($parts) === 2 && (int)$parts[0] >= 1 && (int)$parts[0] <= 12)
                                ? (int)$parts[1] . ' ' . $monthNames[(int)$parts[0]]
                                : $mmdd;
                        ?>
                        <div class="uk-margin">
                            <label class="uk-form-label">
                                <?= $e(__("settings.{$langKey}")) ?>
                                <?php if ($mmdd !== ''): ?>
                                    <span class="uk-text-muted uk-text-small uk-margin-small-left">(<?= $e($displayLabel) ?>)</span>
                                <?php endif; ?>
                            </label>
                            <input class="uk-input" type="date" name="<?= $e($fieldName) ?>"
                                   value="<?= $e($dateValue) ?>">
                        </div>
                        <?php endforeach; ?>

                        <div class="uk-margin">
                            <label class="uk-form-label"><?= $e(__('settings.renewal_approval')) ?></label>
                            <label class="uk-flex uk-flex-middle" style="gap:.5rem; cursor:pointer">
                                <input class="uk-checkbox" type="checkbox" name="renewal_reminder_approval"
                                       <?= $sv('renewal.reminder_approval', 'true') === 'true' ? 'checked' : '' ?>>
                                <span class="uk-text-small"><?= $e(__('settings.renewal_approval_desc')) ?></span>
                            </label>
                        </div>
                    </div>
                </div>

                <!-- Visual timeline -->
                <div class="uk-width-1-2@m">
                    <div class="uk-card uk-card-default uk-card-body uk-border-rounded uk-margin-bottom">
                        <h3 class="uk-card-title"><?= $e(__('settings.renewal_calendar_title')) ?></h3>
                        <?php
                        $year = (int) date('Y');
                        $cycle = [
                            ['key' => 'renewal.date_open',            'label' => __('settings.renewal_open'),            'color' => '#1e87f0'],
                            ['key' => 'renewal.date_first_reminder',  'label' => __('settings.renewal_first_reminder'),  'color' => '#f0c060'],
                            ['key' => 'renewal.date_second_reminder', 'label' => __('settings.renewal_second_reminder'), 'color' => '#f08030'],
                            ['key' => 'renewal.date_third_reminder',  'label' => __('settings.renewal_third_reminder'),  'color' => '#e06020'],
                            ['key' => 'renewal.date_close',           'label' => __('settings.renewal_close'),           'color' => '#e05030'],
                            ['key' => 'renewal.date_lapse',           'label' => __('settings.renewal_lapse'),           'color' => '#888'],
                        ];
                        $defaults = ['11-15','02-15','03-15','04-15','04-15','12-31'];
                        foreach ($cycle as $i => $step):
                            $mmdd  = $sv($step['key'], $defaults[$i]);
                            $parts = explode('-', $mmdd);
                            $label = (count($parts) === 2)
                                ? date('j M', mktime(0, 0, 0, (int) $parts[0], (int) $parts[1], $year))
                                : $mmdd;
                        ?>
                        <div class="uk-flex uk-flex-middle uk-margin-small-bottom">
                            <div style="width:12px; height:12px; border-radius:50%; background:<?= $e($step['color']) ?>; flex-shrink:0; margin-right:10px"></div>
                            <div class="uk-text-small">
                                <strong><?= $e($step['label']) ?></strong>
                                <span class="uk-text-muted uk-margin-small-left"><?= $e($label) ?></span>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <button type="submit" class="uk-button uk-button-primary">
                <span uk-icon="check"></span> <?= $e(__('settings.save')) ?>
            </button>
        </form>
        </li>

        <!-- =================================================================
             TAB 3 — CATEGORIE SOCI
             ================================================================= -->
        <li>
        <?php
        // Category edit modal trigger
        $editCatId = 0;
        ?>
        <!-- Create/Edit form (modal) -->
        <div id="modal-category" uk-modal>
            <div class="uk-modal-dialog uk-modal-body">
                <button class="uk-modal-close-default" type="button" uk-close></button>
                <h3 class="uk-modal-title" id="modal-cat-title"><?= $e(__('settings.cat_new')) ?></h3>
                <form method="post" action="settings.php?tab=categories">
                    <?= csrf_field() ?>
                    <input type="hidden" name="_action" value="save_category">
                    <input type="hidden" name="category_id" id="cat-edit-id" value="0">

                    <div class="uk-grid uk-grid-small" uk-grid>
                        <div class="uk-width-1-2@s">
                            <label class="uk-form-label"><?= $e(__('settings.cat_slug')) ?> *</label>
                            <input class="uk-input" type="text" name="cat_name" id="cat-name" required
                                   pattern="^[a-z_]+$" placeholder="es. ordinario">
                        </div>
                        <div class="uk-width-1-2@s">
                            <label class="uk-form-label"><?= $e(__('settings.cat_label')) ?> *</label>
                            <input class="uk-input" type="text" name="cat_label" id="cat-label" required>
                        </div>
                        <div class="uk-width-1-1">
                            <label class="uk-form-label"><?= $e(__('settings.cat_description')) ?></label>
                            <input class="uk-input" type="text" name="cat_description" id="cat-description">
                        </div>
                        <div class="uk-width-1-2@s">
                            <label class="uk-form-label"><?= $e(__('settings.cat_annual_fee')) ?></label>
                            <input class="uk-input" type="number" step="0.01" min="0" name="cat_annual_fee"
                                   id="cat-annual-fee" value="0">
                        </div>
                        <div class="uk-width-1-2@s">
                            <label class="uk-form-label"><?= $e(__('settings.cat_sort_order')) ?></label>
                            <input class="uk-input" type="number" min="0" name="cat_sort_order"
                                   id="cat-sort-order" value="0">
                        </div>
                        <div class="uk-width-1-3@s">
                            <label class="uk-flex uk-flex-middle" style="gap:.4rem; cursor:pointer">
                                <input class="uk-checkbox" type="checkbox" name="cat_is_free" id="cat-is-free">
                                <span class="uk-text-small"><?= $e(__('settings.cat_is_free')) ?></span>
                            </label>
                        </div>
                        <div class="uk-width-1-3@s">
                            <label class="uk-flex uk-flex-middle" style="gap:.4rem; cursor:pointer">
                                <input class="uk-checkbox" type="checkbox" name="cat_is_exempt" id="cat-is-exempt">
                                <span class="uk-text-small"><?= $e(__('settings.cat_is_exempt')) ?></span>
                            </label>
                        </div>
                        <div class="uk-width-1-3@s">
                            <label class="uk-flex uk-flex-middle" style="gap:.4rem; cursor:pointer">
                                <input class="uk-checkbox" type="checkbox" name="cat_requires_approval" id="cat-requires-approval">
                                <span class="uk-text-small"><?= $e(__('settings.cat_requires_approval')) ?></span>
                            </label>
                        </div>
                        <div class="uk-width-1-2@s">
                            <label class="uk-form-label"><?= $e(__('settings.cat_valid_from')) ?></label>
                            <input class="uk-input" type="date" name="cat_valid_from" id="cat-valid-from">
                        </div>
                        <div class="uk-width-1-2@s">
                            <label class="uk-form-label"><?= $e(__('settings.cat_valid_until')) ?></label>
                            <input class="uk-input" type="date" name="cat_valid_until" id="cat-valid-until">
                        </div>
                    </div>

                    <div class="uk-margin-top">
                        <button type="submit" class="uk-button uk-button-primary">
                            <span uk-icon="check"></span> <?= $e(__('settings.save')) ?>
                        </button>
                        <button type="button" class="uk-button uk-button-default uk-modal-close">
                            <?= $e(__('settings.cancel')) ?>
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Fee modal -->
        <div id="modal-fee" uk-modal>
            <div class="uk-modal-dialog uk-modal-body">
                <button class="uk-modal-close-default" type="button" uk-close></button>
                <h3 class="uk-modal-title"><?= $e(__('settings.cat_fee_add')) ?></h3>
                <form method="post" action="settings.php?tab=categories">
                    <?= csrf_field() ?>
                    <input type="hidden" name="_action" value="save_fee">
                    <input type="hidden" name="category_id" id="fee-cat-id" value="0">
                    <div class="uk-grid uk-grid-small" uk-grid>
                        <div class="uk-width-1-3@s">
                            <label class="uk-form-label"><?= $e(__('settings.cat_fee_year')) ?></label>
                            <input class="uk-input" type="number" name="fee_year" id="fee-year"
                                   value="<?= $e(date('Y')) ?>" min="2000" max="2099">
                        </div>
                        <div class="uk-width-1-3@s">
                            <label class="uk-form-label"><?= $e(__('settings.cat_fee_amount')) ?></label>
                            <input class="uk-input" type="number" step="0.01" min="0" name="fee_amount" id="fee-amount" value="0">
                        </div>
                        <div class="uk-width-1-3@s">
                            <label class="uk-form-label"><?= $e(__('settings.cat_fee_note')) ?></label>
                            <input class="uk-input" type="text" name="fee_note" id="fee-note">
                        </div>
                    </div>
                    <div class="uk-margin-top">
                        <button type="submit" class="uk-button uk-button-primary">
                            <span uk-icon="check"></span> <?= $e(__('settings.save')) ?>
                        </button>
                        <button type="button" class="uk-button uk-button-default uk-modal-close">
                            <?= $e(__('settings.cancel')) ?>
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Categories list -->
        <div class="uk-flex uk-flex-between uk-flex-middle uk-margin-bottom">
            <h3 class="uk-margin-remove"><?= $e(__('settings.tab_categories')) ?></h3>
            <button type="button" class="uk-button uk-button-primary uk-button-small"
                    uk-toggle="target: #modal-category"
                    onclick="openNewCategory()">
                <span uk-icon="plus"></span> <?= $e(__('settings.cat_new')) ?>
            </button>
        </div>

        <?php if (empty($categories)): ?>
            <div class="uk-alert-warning" uk-alert>
                <p><?= $e(__('settings.cat_none')) ?></p>
            </div>
        <?php else: ?>
        <table class="uk-table uk-table-divider uk-table-small uk-table-hover">
            <thead>
                <tr>
                    <th><?= $e(__('settings.cat_label')) ?></th>
                    <th><?= $e(__('settings.cat_annual_fee')) ?></th>
                    <th><?= $e(__('settings.cat_is_free')) ?></th>
                    <th><?= $e(__('settings.cat_is_exempt')) ?></th>
                    <th><?= $e(__('settings.cat_is_active')) ?></th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($categories as $cat): ?>
            <tr>
                <td>
                    <strong><?= $e($cat['label']) ?></strong>
                    <br><code class="uk-text-small"><?= $e($cat['name']) ?></code>
                    <?php if (!empty($cat['description'])): ?>
                        <br><span class="uk-text-muted uk-text-small"><?= $e($cat['description']) ?></span>
                    <?php endif; ?>
                </td>
                <td>
                    <?= (int) $cat['is_free'] ? '<span class="uk-label">Gratuita</span>' : $e('€ ' . number_format((float) $cat['annual_fee'], 2, ',', '.')) ?>
                </td>
                <td>
                    <?= (int) $cat['is_free'] ? '<span uk-icon="check" class="uk-text-success"></span>' : '<span uk-icon="minus" class="uk-text-muted"></span>' ?>
                </td>
                <td>
                    <?= (int) $cat['is_exempt_from_renewal'] ? '<span uk-icon="check" class="uk-text-success"></span>' : '<span uk-icon="minus" class="uk-text-muted"></span>' ?>
                </td>
                <td>
                    <?php if ((int) $cat['is_active']): ?>
                        <span class="uk-label uk-label-success"><?= $e(__('settings.active')) ?></span>
                    <?php else: ?>
                        <span class="uk-label"><?= $e(__('settings.inactive')) ?></span>
                    <?php endif; ?>
                </td>
                <td class="uk-text-right" style="white-space:nowrap">
                    <button type="button" class="uk-button uk-button-default uk-button-small"
                            uk-toggle="target: #modal-category"
                            onclick="openEditCategory(<?= htmlspecialchars(json_encode($cat), ENT_QUOTES) ?>)">
                        <?= $e(__('settings.edit')) ?>
                    </button>
                    <form method="post" action="settings.php?tab=categories" style="display:inline">
                        <?= csrf_field() ?>
                        <input type="hidden" name="_action" value="toggle_category">
                        <input type="hidden" name="category_id" value="<?= (int) $cat['id'] ?>">
                        <button type="submit" class="uk-button uk-button-default uk-button-small">
                            <?= $e(__('settings.cat_toggle_active')) ?>
                        </button>
                    </form>
                    <button type="button" class="uk-button uk-button-secondary uk-button-small"
                            uk-toggle="target: #modal-fee"
                            onclick="openFeeModal(<?= (int) $cat['id'] ?>, <?= $e((float) $cat['annual_fee']) ?>)">
                        <?= $e(__('settings.cat_fee_history')) ?>
                    </button>
                </td>
            </tr>
            <!-- Fee history row -->
            <?php if (!empty($categoryFees[(int) $cat['id']])): ?>
            <tr class="uk-background-muted">
                <td colspan="6">
                    <p class="uk-text-small uk-text-muted uk-margin-remove-bottom"><strong><?= $e(__('settings.cat_fee_history')) ?>:</strong></p>
                    <table class="uk-table uk-table-small uk-margin-remove">
                        <thead>
                            <tr>
                                <th><?= $e(__('settings.cat_fee_year')) ?></th>
                                <th><?= $e(__('settings.cat_fee_amount')) ?></th>
                                <th><?= $e(__('settings.cat_fee_note')) ?></th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($categoryFees[(int) $cat['id']] as $fee): ?>
                        <tr>
                            <td><?= $e($fee['year']) ?></td>
                            <td>€ <?= $e(number_format((float) $fee['fee'], 2, ',', '.')) ?></td>
                            <td class="uk-text-muted"><?= $e($fee['note'] ?? '') ?></td>
                        </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </td>
            </tr>
            <?php elseif (!empty($cat['is_active'])): ?>
            <tr class="uk-background-muted">
                <td colspan="6">
                    <span class="uk-text-small uk-text-muted"><?= $e(__('settings.cat_no_fees')) ?></span>
                </td>
            </tr>
            <?php endif; ?>
            <?php endforeach; ?>
            </tbody>
        </table>
        <?php endif; ?>
        </li>

        <!-- =================================================================
             TAB 4 — RUOLI DIRETTIVO
             ================================================================= -->
        <li>
        <!-- Role modal -->
        <div id="modal-role" uk-modal>
            <div class="uk-modal-dialog uk-modal-body">
                <button class="uk-modal-close-default" type="button" uk-close></button>
                <h3 class="uk-modal-title" id="modal-role-title"><?= $e(__('settings.role_new')) ?></h3>
                <form method="post" action="settings.php?tab=board_roles">
                    <?= csrf_field() ?>
                    <input type="hidden" name="_action" value="save_role">
                    <input type="hidden" name="role_id" id="role-edit-id" value="0">

                    <div class="uk-grid uk-grid-small" uk-grid>
                        <div class="uk-width-1-2@s">
                            <label class="uk-form-label"><?= $e(__('settings.role_slug')) ?> *</label>
                            <input class="uk-input" type="text" name="role_name" id="role-name" required
                                   pattern="^[a-z_]+$" placeholder="es. president">
                        </div>
                        <div class="uk-width-1-2@s">
                            <label class="uk-form-label"><?= $e(__('settings.role_label')) ?> *</label>
                            <input class="uk-input" type="text" name="role_label" id="role-label" required>
                        </div>
                        <div class="uk-width-1-1">
                            <label class="uk-form-label"><?= $e(__('settings.role_description')) ?></label>
                            <input class="uk-input" type="text" name="role_description" id="role-description">
                        </div>
                        <div class="uk-width-1-4@s">
                            <label class="uk-form-label"><?= $e(__('settings.cat_sort_order')) ?></label>
                            <input class="uk-input" type="number" min="0" name="role_sort_order" id="role-sort-order" value="0">
                        </div>
                        <div class="uk-width-3-8@s">
                            <label class="uk-flex uk-flex-middle" style="gap:.4rem; cursor:pointer; margin-top:28px">
                                <input class="uk-checkbox" type="checkbox" name="role_is_board_member" id="role-is-board-member" checked>
                                <span class="uk-text-small"><?= $e(__('settings.role_is_board_member')) ?></span>
                            </label>
                        </div>
                        <div class="uk-width-3-8@s">
                            <label class="uk-flex uk-flex-middle" style="gap:.4rem; cursor:pointer; margin-top:28px">
                                <input class="uk-checkbox" type="checkbox" name="role_can_sign" id="role-can-sign">
                                <span class="uk-text-small"><?= $e(__('settings.role_can_sign')) ?></span>
                            </label>
                        </div>
                    </div>

                    <div class="uk-margin-top">
                        <button type="submit" class="uk-button uk-button-primary">
                            <span uk-icon="check"></span> <?= $e(__('settings.save')) ?>
                        </button>
                        <button type="button" class="uk-button uk-button-default uk-modal-close">
                            <?= $e(__('settings.cancel')) ?>
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <div class="uk-flex uk-flex-between uk-flex-middle uk-margin-bottom">
            <h3 class="uk-margin-remove"><?= $e(__('settings.tab_board_roles')) ?></h3>
            <button type="button" class="uk-button uk-button-primary uk-button-small"
                    uk-toggle="target: #modal-role"
                    onclick="openNewRole()">
                <span uk-icon="plus"></span> <?= $e(__('settings.role_new')) ?>
            </button>
        </div>

        <?php if (empty($boardRoles)): ?>
            <div class="uk-alert-warning" uk-alert>
                <p><?= $e(__('settings.role_none')) ?></p>
            </div>
        <?php else: ?>
        <table class="uk-table uk-table-divider uk-table-small uk-table-hover">
            <thead>
                <tr>
                    <th><?= $e(__('settings.role_label')) ?></th>
                    <th><?= $e(__('settings.role_is_board_member')) ?></th>
                    <th><?= $e(__('settings.role_can_sign')) ?></th>
                    <th><?= $e(__('settings.role_is_active')) ?></th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($boardRoles as $role): ?>
            <tr>
                <td>
                    <strong><?= $e($role['label']) ?></strong>
                    <br><code class="uk-text-small"><?= $e($role['name']) ?></code>
                    <?php if (!empty($role['description'])): ?>
                        <br><span class="uk-text-muted uk-text-small"><?= $e($role['description']) ?></span>
                    <?php endif; ?>
                </td>
                <td>
                    <?= (int) $role['is_board_member'] ? '<span uk-icon="check" class="uk-text-success"></span>' : '<span uk-icon="minus" class="uk-text-muted"></span>' ?>
                </td>
                <td>
                    <?= (int) $role['can_sign'] ? '<span uk-icon="check" class="uk-text-success"></span>' : '<span uk-icon="minus" class="uk-text-muted"></span>' ?>
                </td>
                <td>
                    <?php if ((int) $role['is_active']): ?>
                        <span class="uk-label uk-label-success"><?= $e(__('settings.active')) ?></span>
                    <?php else: ?>
                        <span class="uk-label"><?= $e(__('settings.inactive')) ?></span>
                    <?php endif; ?>
                </td>
                <td class="uk-text-right" style="white-space:nowrap">
                    <button type="button" class="uk-button uk-button-default uk-button-small"
                            uk-toggle="target: #modal-role"
                            onclick="openEditRole(<?= htmlspecialchars(json_encode($role), ENT_QUOTES) ?>)">
                        <?= $e(__('settings.edit')) ?>
                    </button>
                    <form method="post" action="settings.php?tab=board_roles" style="display:inline">
                        <?= csrf_field() ?>
                        <input type="hidden" name="_action" value="toggle_role">
                        <input type="hidden" name="role_id" value="<?= (int) $role['id'] ?>">
                        <button type="submit" class="uk-button uk-button-default uk-button-small">
                            <?= $e(__('settings.role_toggle_active')) ?>
                        </button>
                    </form>
                </td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        <?php endif; ?>
        </li>

        <!-- =================================================================
             TAB 5 — INTERFACCIA
             ================================================================= -->
        <li>
        <form method="post" action="settings.php?tab=interface">
            <?= csrf_field() ?>
            <div class="uk-card uk-card-default uk-card-body uk-border-rounded" style="max-width:540px">
                <h3 class="uk-card-title"><?= $e(__('settings.tab_interface')) ?></h3>

                <div class="uk-margin">
                    <label class="uk-form-label"><?= $e(__('settings.theme')) ?></label>
                    <select class="uk-select" name="ui_theme">
                        <?php
                        $themes = [
                            'uikit'     => __('settings.theme_uikit'),
                            'bootstrap' => __('settings.theme_bootstrap'),
                            'tailwind'  => __('settings.theme_tailwind'),
                        ];
                        $curTheme = $sv('ui.theme', 'uikit');
                        foreach ($themes as $val => $lbl): ?>
                        <option value="<?= $e($val) ?>" <?= $curTheme === $val ? 'selected' : '' ?>>
                            <?= $e($lbl) ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="uk-margin">
                    <label class="uk-form-label"><?= $e(__('settings.language')) ?></label>
                    <select class="uk-select" name="ui_language">
                        <?php
                        $curLang = $sv('ui.locale', 'it');
                        foreach ($languages as $code => $name): ?>
                        <option value="<?= $e($code) ?>" <?= $curLang === $code ? 'selected' : '' ?>>
                            <?= $e($name) ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="uk-margin">
                    <label class="uk-form-label"><?= $e(__('settings.date_format')) ?></label>
                    <select class="uk-select" name="ui_date_format">
                        <?php
                        $formats    = ['d/m/Y' => 'GG/MM/AAAA', 'm/d/Y' => 'MM/GG/AAAA', 'Y-m-d' => 'AAAA-MM-GG'];
                        $curFormat  = $sv('ui.date_format', 'd/m/Y');
                        foreach ($formats as $val => $lbl): ?>
                        <option value="<?= $e($val) ?>" <?= $curFormat === $val ? 'selected' : '' ?>>
                            <?= $e($lbl) ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="uk-margin">
                    <label class="uk-form-label"><?= $e(__('settings.timezone')) ?></label>
                    <select class="uk-select" name="ui_timezone">
                        <?php
                        $tzones = ['Europe/Rome' => 'Europe/Rome (CET/CEST)', 'Europe/London' => 'Europe/London (GMT/BST)', 'UTC' => 'UTC', 'America/New_York' => 'America/New_York (EST/EDT)', 'America/Los_Angeles' => 'America/Los_Angeles (PST/PDT)'];
                        $curTz  = $sv('ui.timezone', 'Europe/Rome');
                        foreach ($tzones as $val => $lbl): ?>
                        <option value="<?= $e($val) ?>" <?= $curTz === $val ? 'selected' : '' ?>>
                            <?= $e($lbl) ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <button type="submit" class="uk-button uk-button-primary">
                    <span uk-icon="check"></span> <?= $e(__('settings.save')) ?>
                </button>
            </div>
        </form>
        </li>

        <!-- =================================================================
             TAB 6 — EMAIL SMTP
             ================================================================= -->
        <li>
        <div class="uk-card uk-card-default uk-card-body uk-border-rounded" style="max-width:600px">
            <h3 class="uk-card-title"><?= $e(__('settings.tab_email')) ?></h3>

            <form method="post" action="settings.php?tab=email" id="form-smtp">
                <?= csrf_field() ?>
                <input type="hidden" name="_action" value="save">

                <div class="uk-grid uk-grid-small" uk-grid>
                    <div class="uk-width-expand">
                        <label class="uk-form-label"><?= $e(__('settings.smtp_host')) ?></label>
                        <input class="uk-input" type="text" name="smtp_host" id="smtp_host"
                               value="<?= $se('smtp.host') ?>" placeholder="smtp.esempio.it">
                    </div>
                    <div class="uk-width-1-4">
                        <label class="uk-form-label"><?= $e(__('settings.smtp_port')) ?></label>
                        <input class="uk-input" type="number" name="smtp_port" id="smtp_port"
                               value="<?= $e($sv('smtp.port', '587')) ?>" min="1" max="65535">
                    </div>
                    <div class="uk-width-1-4">
                        <label class="uk-form-label"><?= $e(__('settings.smtp_encryption')) ?></label>
                        <select class="uk-select" name="smtp_encryption" id="smtp_encryption">
                            <?php
                            $encs    = ['tls' => __('settings.smtp_encryption_tls'), 'ssl' => __('settings.smtp_encryption_ssl'), 'none' => __('settings.smtp_encryption_none')];
                            $curEnc  = $sv('smtp.encryption', 'tls');
                            foreach ($encs as $val => $lbl): ?>
                            <option value="<?= $e($val) ?>" <?= $curEnc === $val ? 'selected' : '' ?>>
                                <?= $e($lbl) ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="uk-width-1-2@s">
                        <label class="uk-form-label"><?= $e(__('settings.smtp_username')) ?></label>
                        <input class="uk-input" type="text" name="smtp_username" id="smtp_username"
                               autocomplete="username"
                               value="<?= $se('smtp.username') ?>">
                    </div>
                    <div class="uk-width-1-2@s">
                        <label class="uk-form-label"><?= $e(__('settings.smtp_password')) ?></label>
                        <input class="uk-input" type="password" name="smtp_password" id="smtp_password"
                               autocomplete="new-password"
                               placeholder="<?= $sv('smtp.password') !== '' ? '••••••••' : '' ?>">
                        <p class="uk-text-small uk-text-muted uk-margin-remove-top">
                            <?= $e(__('settings.smtp_password_hint')) ?>
                        </p>
                    </div>
                    <div class="uk-width-1-2@s">
                        <label class="uk-form-label"><?= $e(__('settings.smtp_from')) ?></label>
                        <input class="uk-input" type="email" name="smtp_from_address" id="smtp_from_address"
                               value="<?= $se('smtp.from_address') ?>">
                    </div>
                    <div class="uk-width-1-2@s">
                        <label class="uk-form-label"><?= $e(__('settings.smtp_from_name')) ?></label>
                        <input class="uk-input" type="text" name="smtp_from_name" id="smtp_from_name"
                               value="<?= $se('smtp.from_name') ?>">
                    </div>
                </div>

                <div class="uk-margin-top uk-flex" style="gap:.5rem; flex-wrap:wrap">
                    <button type="submit" class="uk-button uk-button-primary">
                        <span uk-icon="check"></span> <?= $e(__('settings.save')) ?>
                    </button>
                    <button type="submit" name="_action" value="test"
                            class="uk-button uk-button-secondary"
                            form="form-smtp"
                            onclick="document.getElementById('form-smtp').elements['_action'].value='test'">
                        <span uk-icon="mail"></span> <?= $e(__('settings.smtp_test')) ?>
                    </button>
                </div>
            </form>
        </div>
        </li>

        <!-- =================================================================
             TAB 7 — NUMERO SOCIO
             ================================================================= -->
        <li>
        <div class="uk-card uk-card-default uk-card-body uk-border-rounded" style="max-width:480px">
            <h3 class="uk-card-title"><?= $e(__('settings.tab_member_number')) ?></h3>

            <div class="uk-margin">
                <p class="uk-text-muted uk-text-small uk-margin-remove-bottom">
                    <?= $e(__('settings.number_current_max')) ?>
                </p>
                <p class="uk-margin-remove-top" style="font-size:1.8rem; font-weight:600">
                    <?= (int) $memberCurrentMax > 0 ? $e((string) $memberCurrentMax) : '<span class="uk-text-muted" style="font-size:1rem">—</span>' ?>
                </p>
            </div>

            <hr>

            <div class="uk-margin">
                <p class="uk-text-small uk-text-muted"><?= $e(__('settings.number_reset_desc')) ?></p>
            </div>

            <form method="post" action="settings.php?tab=member_number" id="form-number-reset"
                  onsubmit="return confirm(<?= $e("'" . addslashes(__('settings.number_reset_confirm')) . "'") ?>)">
                <?= csrf_field() ?>
                <div class="uk-margin">
                    <label class="uk-form-label"><?= $e(__('settings.number_start')) ?></label>
                    <input class="uk-input" type="number" name="number_start" min="1"
                           value="<?= $e((string) max(1, $memberCurrentMax + 1)) ?>">
                </div>
                <div class="uk-alert-warning uk-margin" uk-alert>
                    <p class="uk-text-small">
                        <span uk-icon="warning"></span>
                        <?= $e(__('settings.number_reset_warn')) ?>
                    </p>
                </div>
                <button type="submit" class="uk-button uk-button-danger">
                    <span uk-icon="refresh"></span> <?= $e(__('settings.number_reset')) ?>
                </button>
            </form>
        </div>
        </li>

    </ul><!-- end uk-switcher -->

    <script>
    // Activate correct tab from server-side $activeTab
    document.addEventListener('DOMContentLoaded', function () {
        var tabs = document.querySelectorAll('#settings-tabs > li[data-tab]');
        var active = <?= json_encode($activeTab) ?>;
        tabs.forEach(function (li, i) {
            if (li.dataset.tab === active) {
                UIkit.tab(document.getElementById('settings-tabs')).show(i);
            }
        });
    });

    // Category modal helpers
    function openNewCategory() {
        document.getElementById('modal-cat-title').textContent = <?= json_encode(__('settings.cat_new')) ?>;
        document.getElementById('cat-edit-id').value    = '0';
        document.getElementById('cat-name').value       = '';
        document.getElementById('cat-label').value      = '';
        document.getElementById('cat-description').value= '';
        document.getElementById('cat-annual-fee').value = '0';
        document.getElementById('cat-sort-order').value = '0';
        document.getElementById('cat-is-free').checked  = false;
        document.getElementById('cat-is-exempt').checked= false;
        document.getElementById('cat-requires-approval').checked = false;
        document.getElementById('cat-valid-from').value = '';
        document.getElementById('cat-valid-until').value= '';
    }

    function openEditCategory(cat) {
        document.getElementById('modal-cat-title').textContent = <?= json_encode(__('settings.cat_edit')) ?>;
        document.getElementById('cat-edit-id').value     = cat.id;
        document.getElementById('cat-name').value        = cat.name      || '';
        document.getElementById('cat-label').value       = cat.label     || '';
        document.getElementById('cat-description').value = cat.description|| '';
        document.getElementById('cat-annual-fee').value  = cat.annual_fee|| '0';
        document.getElementById('cat-sort-order').value  = cat.sort_order|| '0';
        document.getElementById('cat-is-free').checked   = parseInt(cat.is_free)                === 1;
        document.getElementById('cat-is-exempt').checked = parseInt(cat.is_exempt_from_renewal) === 1;
        document.getElementById('cat-requires-approval').checked = parseInt(cat.requires_approval) === 1;
        document.getElementById('cat-valid-from').value  = cat.valid_from  || '';
        document.getElementById('cat-valid-until').value = cat.valid_until || '';
    }

    function openFeeModal(catId, defaultFee) {
        document.getElementById('fee-cat-id').value   = catId;
        document.getElementById('fee-year').value     = new Date().getFullYear();
        document.getElementById('fee-amount').value   = defaultFee || '0';
        document.getElementById('fee-note').value     = '';
    }

    // Role modal helpers
    function openNewRole() {
        document.getElementById('modal-role-title').textContent = <?= json_encode(__('settings.role_new')) ?>;
        document.getElementById('role-edit-id').value      = '0';
        document.getElementById('role-name').value         = '';
        document.getElementById('role-label').value        = '';
        document.getElementById('role-description').value  = '';
        document.getElementById('role-sort-order').value   = '0';
        document.getElementById('role-is-board-member').checked = true;
        document.getElementById('role-can-sign').checked   = false;
    }

    function openEditRole(role) {
        document.getElementById('modal-role-title').textContent = <?= json_encode(__('settings.role_edit')) ?>;
        document.getElementById('role-edit-id').value      = role.id;
        document.getElementById('role-name').value         = role.name        || '';
        document.getElementById('role-label').value        = role.label       || '';
        document.getElementById('role-description').value  = role.description || '';
        document.getElementById('role-sort-order').value   = role.sort_order  || '0';
        document.getElementById('role-is-board-member').checked = parseInt(role.is_board_member) === 1;
        document.getElementById('role-can-sign').checked   = parseInt(role.can_sign) === 1;
    }

    // SMTP test button: override hidden _action field before submit
    (function () {
        var form  = document.getElementById('form-smtp');
        var testBtn = form ? form.querySelector('button[onclick]') : null;
        if (testBtn) {
            testBtn.addEventListener('click', function (e) {
                form.querySelector('input[name="_action"]').value = 'test';
            });
        }
    })();
    </script>

    <?php
    return (string) ob_get_clean();
})();

require __DIR__ . '/layout.php';
