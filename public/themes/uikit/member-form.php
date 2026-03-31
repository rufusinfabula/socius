<?php
$e       = fn($v) => htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8');
$isEdit  = $isEdit ?? false;
$heading = $isEdit ? __('members.edit_member') : __('members.new_member');
$action  = $isEdit
    ? 'member-edit.php?id=' . (int) ($member['id'] ?? 0)
    : 'member-new.php';
$v = fn(string $field, mixed $default = '') => $e($member[$field] ?? $default);

$statusOptions = [
    'active'      => __('members.status_active'),
    'in_renewal'  => __('members.status_in_renewal'),
    'not_renewed' => __('members.status_not_renewed'),
    'lapsed'      => __('members.status_lapsed'),
    'suspended'   => __('members.status_suspended'),
    'resigned'    => __('members.status_resigned'),
    'deceased'    => __('members.status_deceased'),
];

$content = (function () use (
    $member, $categories, $boardRoles, $currentBoardRole, $currentUser,
    $isEdit, $heading, $action, $error, $errorDebug, $e, $v, $statusOptions
): string {
    ob_start();
    $isStaff = (int) ($currentUser['role_id'] ?? 4) <= 3;
    ?>

    <?php if (!empty($error)): ?>
        <div class="uk-alert-danger" uk-alert>
            <a class="uk-alert-close" uk-close></a>
            <p><?= $e($error) ?></p>
            <?php if (!empty($errorDebug)): ?>
                <p class="uk-text-small uk-text-muted uk-margin-remove-top">
                    <?= $e(__('members.error_debug_info')) ?>
                    <code><?= $e($errorDebug) ?></code>
                </p>
            <?php endif; ?>
        </div>
    <?php endif; ?>

    <!-- Breadcrumb -->
    <ul class="uk-breadcrumb uk-margin-small-bottom">
        <li><a href="members.php"><?= $e(__('members.member_list')) ?></a></li>
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

    <form method="POST" action="<?= $e($action) ?>" novalidate class="uk-form-stacked" id="member-form">
        <?= csrf_field() ?>

        <!-- =====================================================================
             ROW 1: Anagrafica (2/3) + Socio (1/3)
        ====================================================================== -->
        <div class="uk-grid uk-grid-medium uk-margin-bottom" uk-grid>

            <!-- BOX ANAGRAFICA (2/3) -->
            <div class="uk-width-2-3@m">
                <div class="uk-card uk-card-default uk-card-body uk-border-rounded">
                    <h3 class="uk-card-title">
                        <span uk-icon="icon: user; ratio: 1.1" class="uk-margin-small-right"></span>
                        <?= $e(__('members.box_registry')) ?>
                    </h3>

                    <div class="uk-grid uk-grid-small" uk-grid>

                        <!-- Cognome -->
                        <div class="uk-width-1-2@s">
                            <label class="uk-form-label" for="surname"><?= $e(__('members.surname')) ?> *</label>
                            <input class="uk-input" type="text" id="surname" name="surname"
                                   value="<?= $v('surname') ?>" required autofocus>
                        </div>

                        <!-- Nome -->
                        <div class="uk-width-1-2@s">
                            <label class="uk-form-label" for="name"><?= $e(__('members.name')) ?> *</label>
                            <input class="uk-input" type="text" id="name" name="name"
                                   value="<?= $v('name') ?>" required>
                        </div>

                        <!-- Sex -->
                        <div class="uk-width-1-3@s">
                            <label class="uk-form-label" for="sex"><?= $e(__('members.sex')) ?></label>
                            <select class="uk-select" id="sex" name="sex">
                                <option value="">—</option>
                                <option value="M" <?= ($member['sex'] ?? '') === 'M' ? 'selected' : '' ?>>
                                    <?= $e(__('members.sex_m')) ?>
                                </option>
                                <option value="F" <?= ($member['sex'] ?? '') === 'F' ? 'selected' : '' ?>>
                                    <?= $e(__('members.sex_f')) ?>
                                </option>
                            </select>
                        </div>

                        <!-- Gender -->
                        <div class="uk-width-2-3@s">
                            <label class="uk-form-label" for="gender"><?= $e(__('members.gender')) ?></label>
                            <select class="uk-select" id="gender-select">
                                <option value="">—</option>
                                <option value="Uomo"><?= $e(__('members.gender_man')) ?></option>
                                <option value="Donna"><?= $e(__('members.gender_woman')) ?></option>
                                <option value="Non binario"><?= $e(__('members.gender_nonbinary')) ?></option>
                                <option value="Fluido"><?= $e(__('members.gender_fluid')) ?></option>
                                <option value="Preferisco non specificare"><?= $e(__('members.gender_not_specified')) ?></option>
                                <option value="__other__"><?= $e(__('members.gender_other')) ?></option>
                            </select>
                            <input class="uk-input uk-margin-small-top" type="text" id="gender" name="gender"
                                   value="<?= $v('gender') ?>"
                                   placeholder="<?= $e(__('members.gender')) ?>">
                            <p class="uk-text-small uk-text-muted uk-margin-remove-top" style="font-size:0.8rem">
                                <?= $e(__('members.gender_gdpr_note')) ?>
                            </p>
                        </div>

                        <!-- Data di nascita -->
                        <div class="uk-width-1-2@s">
                            <label class="uk-form-label" for="birth_date"><?= $e(__('members.birth_date')) ?></label>
                            <input class="uk-input" type="date" id="birth_date" name="birth_date"
                                   value="<?= $v('birth_date') ?>">
                        </div>

                        <!-- Luogo di nascita -->
                        <div class="uk-width-1-2@s">
                            <label class="uk-form-label" for="birth_place"><?= $e(__('members.birth_place')) ?></label>
                            <input class="uk-input" type="text" id="birth_place" name="birth_place"
                                   value="<?= $v('birth_place') ?>">
                        </div>

                        <!-- Codice fiscale + pulsante Calcola -->
                        <div class="uk-width-1-1">
                            <label class="uk-form-label" for="fiscal_code"><?= $e(__('members.fiscal_code')) ?></label>
                            <div class="uk-flex uk-flex-middle" style="gap:8px">
                                <input class="uk-input" type="text" id="fiscal_code" name="fiscal_code"
                                       value="<?= $v('fiscal_code') ?>" maxlength="16"
                                       style="text-transform:uppercase; flex:1">
                                <button type="button" class="uk-button uk-button-default uk-button-small"
                                        id="btn-calc-cf" title="<?= $e(__('members.cf_calculate')) ?>">
                                    <span uk-icon="refresh"></span>
                                    <?= $e(__('members.cf_calculate')) ?>
                                </button>
                            </div>
                            <p class="uk-text-small uk-text-muted uk-margin-remove-top" style="font-size:0.8rem" id="cf-note" hidden>
                                <?= $e(__('members.cf_calculate_note')) ?>
                            </p>
                        </div>

                    </div><!-- /grid -->
                </div><!-- /card anagrafica -->
            </div><!-- /col 2/3 -->

            <!-- BOX SOCIO (1/3) -->
            <div class="uk-width-1-3@m">
                <div class="uk-card uk-card-default uk-card-body uk-border-rounded">
                    <h3 class="uk-card-title">
                        <span uk-icon="icon: tag; ratio: 1.1" class="uk-margin-small-right"></span>
                        <?= $e(__('members.box_member')) ?>
                    </h3>

                    <!-- N. Socio + N. Tessera (read-only in edit mode) -->
                    <?php if ($isEdit): ?>

                    <?php if (!empty($member['member_number'])): ?>
                    <div class="uk-margin">
                        <label class="uk-form-label"><?= $e(__('members.member_number')) ?></label>
                        <div class="uk-input" style="background:#f8f8f8;cursor:default;color:#666">
                            <strong><?= (int) $member['member_number'] ?></strong>
                        </div>
                        <p class="uk-text-small uk-text-muted uk-margin-remove-top" style="font-size:0.8rem">
                            <?= $e(__('members.member_number_permanent')) ?>
                        </p>
                    </div>
                    <?php endif; ?>

                    <?php if (!empty($member['membership_number'])): ?>
                    <div class="uk-margin">
                        <label class="uk-form-label"><?= $e(__('members.membership_number')) ?></label>
                        <div class="uk-input" style="background:#f8f8f8;cursor:default;color:#666">
                            <code><?= $v('membership_number') ?></code>
                        </div>
                    </div>
                    <?php endif; ?>

                    <?php else: ?>
                    <div class="uk-margin">
                        <label class="uk-form-label"><?= $e(__('members.membership_number')) ?></label>
                        <p class="uk-text-muted uk-text-small uk-margin-remove">
                            Assegnato automaticamente
                        </p>
                    </div>
                    <?php endif; ?>

                    <!-- Categoria (required) -->
                    <?php if (!empty($categories)): ?>
                    <div class="uk-margin">
                        <label class="uk-form-label" for="category_id"><?= $e(__('members.category')) ?> *</label>
                        <select class="uk-select" id="category_id" name="category_id" required>
                            <option value=""><?= $e(__('members.select_category')) ?></option>
                            <?php foreach ($categories as $cat): ?>
                                <option value="<?= (int) $cat['id'] ?>"
                                    <?= (int) ($member['category_id'] ?? 0) === (int) $cat['id'] ? 'selected' : '' ?>>
                                    <?= $e($cat['label']) ?><?= ((float) ($cat['annual_fee'] ?? 0) > 0) ? ' (€ ' . number_format((float) $cat['annual_fee'], 2, ',', '.') . ')' : '' ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <?php else: ?>
                    <div class="uk-margin">
                        <label class="uk-form-label"><?= $e(__('members.category')) ?></label>
                        <p class="uk-text-muted uk-text-small">
                            <?= $e(__('members.no_categories_available')) ?>
                        </p>
                    </div>
                    <?php endif; ?>

                    <!-- Status (staff only) -->
                    <?php if ($isStaff): ?>
                    <div class="uk-margin">
                        <label class="uk-form-label" for="status"><?= $e(__('members.status')) ?></label>
                        <select class="uk-select" id="status" name="status">
                            <?php foreach ($statusOptions as $val => $label): ?>
                                <option value="<?= $e($val) ?>"
                                    <?= ($member['status'] ?? 'active') === $val ? 'selected' : '' ?>>
                                    <?= $e($label) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <?php endif; ?>

                    <!-- Data iscrizione -->
                    <div class="uk-margin">
                        <label class="uk-form-label" for="joined_on"><?= $e(__('members.joined_on')) ?> *</label>
                        <input class="uk-input" type="date" id="joined_on" name="joined_on"
                               value="<?= $v('joined_on', date('Y-m-d')) ?>" required>
                    </div>

                    <!-- Note interne (staff only) -->
                    <?php if ($isStaff): ?>
                    <div class="uk-margin">
                        <label class="uk-form-label" for="notes">
                            <?= $e(__('members.notes')) ?>
                            <span class="uk-text-muted uk-text-small"> — solo staff</span>
                        </label>
                        <textarea class="uk-textarea" id="notes" name="notes"
                                  rows="4" style="resize:vertical"><?= $v('notes') ?></textarea>
                    </div>
                    <?php endif; ?>

                </div><!-- /card socio -->
            </div><!-- /col 1/3 -->

        </div><!-- /row 1 -->

        <!-- =====================================================================
             ROW 2: Contatti (larghezza piena)
        ====================================================================== -->
        <div class="uk-card uk-card-default uk-card-body uk-border-rounded uk-margin-bottom">
            <h3 class="uk-card-title">
                <span uk-icon="icon: mail; ratio: 1.1" class="uk-margin-small-right"></span>
                <?= $e(__('members.box_contacts')) ?>
            </h3>

            <div class="uk-grid uk-grid-medium" uk-grid>

                <!-- Colonna sinistra: contatti -->
                <div class="uk-width-1-2@m">
                    <div class="uk-margin">
                        <label class="uk-form-label" for="email"><?= $e(__('members.email')) ?> *</label>
                        <input class="uk-input" type="email" id="email" name="email"
                               value="<?= $v('email') ?>" required autocomplete="email">
                    </div>
                    <div class="uk-margin">
                        <label class="uk-form-label" for="phone1"><?= $e(__('members.phone1')) ?></label>
                        <input class="uk-input" type="tel" id="phone1" name="phone1"
                               value="<?= $v('phone1') ?>">
                    </div>
                    <div class="uk-margin">
                        <label class="uk-form-label" for="phone2"><?= $e(__('members.phone2')) ?></label>
                        <input class="uk-input" type="tel" id="phone2" name="phone2"
                               value="<?= $v('phone2') ?>">
                    </div>
                </div>

                <!-- Colonna destra: indirizzo -->
                <div class="uk-width-1-2@m">
                    <div class="uk-margin">
                        <label class="uk-form-label" for="address"><?= $e(__('members.address')) ?></label>
                        <input class="uk-input" type="text" id="address" name="address"
                               value="<?= $v('address') ?>">
                    </div>
                    <div class="uk-grid uk-grid-small" uk-grid>
                        <div class="uk-width-1-3">
                            <label class="uk-form-label" for="postal_code"><?= $e(__('members.postal_code')) ?></label>
                            <input class="uk-input" type="text" id="postal_code" name="postal_code"
                                   value="<?= $v('postal_code') ?>" maxlength="10">
                        </div>
                        <div class="uk-width-expand">
                            <label class="uk-form-label" for="city"><?= $e(__('members.city')) ?></label>
                            <input class="uk-input" type="text" id="city" name="city"
                                   value="<?= $v('city') ?>">
                        </div>
                    </div>
                    <div class="uk-grid uk-grid-small uk-margin-small-top" uk-grid>
                        <div class="uk-width-1-3">
                            <label class="uk-form-label" for="province"><?= $e(__('members.province')) ?></label>
                            <input class="uk-input" type="text" id="province" name="province"
                                   value="<?= $v('province') ?>" maxlength="5"
                                   style="text-transform:uppercase">
                        </div>
                        <div class="uk-width-1-3">
                            <label class="uk-form-label" for="country"><?= $e(__('members.country')) ?></label>
                            <input class="uk-input" type="text" id="country" name="country"
                                   value="<?= $v('country', 'IT') ?>" maxlength="2"
                                   style="text-transform:uppercase">
                        </div>
                    </div>
                </div>

            </div><!-- /contacts grid -->
        </div><!-- /box contatti -->

        <!-- =====================================================================
             BOX RUOLO NEL DIRETTIVO
        ====================================================================== -->
        <?php if (!empty($boardRoles)): ?>
        <div class="uk-card uk-card-default uk-card-body uk-border-rounded uk-margin-bottom">
            <h3 class="uk-card-title">
                <span uk-icon="icon: star; ratio: 1.1" class="uk-margin-small-right"></span>
                <?= $e(__('members.board_role_box')) ?>
            </h3>

            <div class="uk-grid uk-grid-medium" uk-grid>

                <div class="uk-width-1-3@m">
                    <div class="uk-margin">
                        <label class="uk-form-label" for="board_role_id"><?= $e(__('members.board_role')) ?></label>
                        <select class="uk-select" id="board_role_id" name="board_role_id">
                            <option value="0">— <?= $e(__('members.no_board_role')) ?> —</option>
                            <?php foreach ($boardRoles as $br): ?>
                                <option value="<?= (int) $br['id'] ?>"
                                    <?= (int) ($currentBoardRole['role_id'] ?? 0) === (int) $br['id'] ? 'selected' : '' ?>>
                                    <?= $e($br['label']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="uk-width-1-3@m" id="board-elected-field"
                     style="<?= empty($currentBoardRole) ? 'display:none' : '' ?>">
                    <div class="uk-margin">
                        <label class="uk-form-label" for="board_elected_on"><?= $e(__('members.board_role_from')) ?></label>
                        <input class="uk-input" type="date" id="board_elected_on" name="board_elected_on"
                               value="<?= $e($currentBoardRole['elected_on'] ?? date('Y-m-d')) ?>">
                    </div>
                </div>

                <div class="uk-width-1-3@m" id="board-notes-field"
                     style="<?= empty($currentBoardRole) ? 'display:none' : '' ?>">
                    <div class="uk-margin">
                        <label class="uk-form-label" for="board_notes"><?= $e(__('members.board_role_notes')) ?></label>
                        <input class="uk-input" type="text" id="board_notes" name="board_notes"
                               value="<?= $e($currentBoardRole['notes'] ?? '') ?>">
                    </div>
                </div>

            </div>
        </div>
        <?php endif; ?>

        <!-- Actions -->
        <div class="uk-flex uk-flex-between uk-margin-top">
            <a href="<?= $isEdit && !empty($member['id']) ? 'member.php?id=' . (int) $member['id'] : 'members.php' ?>"
               class="uk-button uk-button-default">
                <?= $e(__('members.cancel')) ?>
            </a>
            <button class="uk-button uk-button-primary" type="submit">
                <?= $e(__('members.save')) ?>
            </button>
        </div>

    </form>

    <script>
    (function () {
        // --- Gender: sync select → text input ---
        var selSesso   = document.getElementById('sex');
        var selGenere  = document.getElementById('gender-select');
        var inpGenere  = document.getElementById('gender');

        // Init: mark select if current value matches a known option
        var knownValues = ['Uomo','Donna','Non binario','Fluido','Preferisco non specificare'];
        var current = inpGenere.value;
        if (knownValues.indexOf(current) !== -1) {
            selGenere.value = current;
        } else if (current !== '') {
            selGenere.value = '__other__';
        }

        selGenere.addEventListener('change', function () {
            if (this.value === '__other__') {
                inpGenere.value = '';
                inpGenere.focus();
            } else if (this.value !== '') {
                inpGenere.value = this.value;
            }
        });

        // --- Sesso → precompila Genere se non già valorizzato ---
        selSesso.addEventListener('change', function () {
            var map = { 'M': 'Uomo', 'F': 'Donna' };
            var suggestion = map[this.value];
            if (suggestion && inpGenere.value === '') {
                inpGenere.value = suggestion;
                selGenere.value = suggestion;
            }
        });

        // --- Calcola CF (placeholder) ---
        var btnCf = document.getElementById('btn-calc-cf');
        var cfNote = document.getElementById('cf-note');
        if (btnCf) {
            btnCf.addEventListener('click', function () {
                cfNote.hidden = false;
            });
        }

        // --- Board role: show/hide extra fields ---
        var selRole = document.getElementById('board_role_id');
        var electedField = document.getElementById('board-elected-field');
        var notesField   = document.getElementById('board-notes-field');
        if (selRole && electedField && notesField) {
            selRole.addEventListener('change', function () {
                var show = this.value !== '0' && this.value !== '';
                electedField.style.display = show ? '' : 'none';
                notesField.style.display   = show ? '' : 'none';
                if (show && !document.getElementById('board_elected_on').value) {
                    var today = new Date().toISOString().slice(0, 10);
                    document.getElementById('board_elected_on').value = today;
                }
            });
        }
    })();
    </script>

    <?php
    return (string) ob_get_clean();
})();

require __DIR__ . '/layout.php';
