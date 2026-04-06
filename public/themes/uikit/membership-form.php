<?php
$e       = fn($v) => htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8');
$isEdit  = $isEdit ?? false;
$heading = $isEdit ? __('memberships.edit_membership') : __('memberships.new_membership');

// In edit mode $membership is the full row from findById; in new mode it's partial $formData
$ms = $membership ?? [];

// Helper: read field value
$v = static function (string $field, mixed $default = '') use ($ms): mixed {
    return $ms[$field] ?? $default;
};

$statusOptions = [
    'pending'   => __('memberships.status_pending'),
    'paid'      => __('memberships.status_paid'),
    'waived'    => __('memberships.status_waived'),
    'cancelled' => __('memberships.status_cancelled'),
];

$memberStatusOptions = [
    'active'      => __('memberships.member_status_active'),
    'in_renewal'  => __('memberships.member_status_in_renewal'),
    'not_renewed' => __('memberships.member_status_not_renewed'),
    'lapsed'      => __('memberships.member_status_lapsed'),
    'suspended'   => __('memberships.member_status_suspended'),
    'resigned'    => __('memberships.member_status_resigned'),
    'deceased'    => __('memberships.member_status_deceased'),
];

$methodOptions = [
    'none'          => __('memberships.no_payment'),
    'cash'          => __('memberships.method_cash'),
    'bank_transfer' => __('memberships.method_bank'),
    'paypal'        => __('memberships.method_paypal'),
    'satispay'      => __('memberships.method_satispay'),
    'waived'        => __('memberships.method_waived'),
    'other'         => __('memberships.method_other'),
];

$isSuperAdmin = $isSuperAdmin ?? false;

// Category fees JSON for JS
$catFeesJson = json_encode($categoryFees ?? [], JSON_FORCE_OBJECT);

$content = (function () use (
    $ms, $v, $isEdit, $heading, $currentUser, $isSuperAdmin,
    $preMember, $members, $categories, $nextNumber, $currentYear,
    $statusOptions, $memberStatusOptions, $methodOptions,
    $catFeesJson, $error, $e
): string {
    ob_start();
    $isStaff   = (int) ($currentUser['role_id'] ?? 4) <= 3;
    $memberId  = (int) $v('member_id', (int) ($preMember['id'] ?? 0));
    $formAction = $isEdit
        ? 'membership-edit.php?id=' . (int) $v('id')
        : 'membership-new.php' . ($preMember ? '?member_id=' . (int) $preMember['id'] : '');
    ?>

    <?php if (!empty($error)): ?>
        <div class="uk-alert-danger" uk-alert>
            <a class="uk-alert-close" uk-close></a>
            <p><?= $e($error) ?></p>
        </div>
    <?php endif; ?>

    <!-- Breadcrumb -->
    <ul class="uk-breadcrumb uk-margin-small-bottom">
        <li><a href="memberships.php"><?= $e(__('memberships.memberships')) ?></a></li>
        <?php if ($isEdit && !empty($ms['id'])): ?>
        <li>
            <a href="membership.php?id=<?= (int) $ms['id'] ?>">
                <?= $e(__('memberships.membership_detail')) ?>
            </a>
        </li>
        <?php endif; ?>
        <li><span><?= $e($heading) ?></span></li>
    </ul>

    <h2 class="uk-heading-small uk-margin-bottom"><?= $e($heading) ?></h2>

    <form method="post" action="<?= $e($formAction) ?>">
        <?= csrf_field() ?>
        <?php if ($isEdit): ?><input type="hidden" name="_action" value="save"><?php endif; ?>

        <div class="uk-grid uk-grid-medium" uk-grid>

            <!-- BOX TESSERA -->
            <div class="uk-width-2-3@m">
                <div class="uk-card uk-card-default uk-card-body uk-border-rounded uk-margin-bottom">
                    <h3 class="uk-card-title"><?= $e(__('memberships.box_membership')) ?></h3>

                    <!-- Socio -->
                    <?php if ($isEdit): ?>
                    <div class="uk-margin">
                        <label class="uk-form-label"><?= $e(__('memberships.box_member')) ?></label>
                        <p class="uk-text-bold uk-margin-remove">
                            <a href="member.php?id=<?= (int) $v('member_id') ?>">
                                <?= $e(($ms['member_surname'] ?? '') . ' ' . ($ms['member_name'] ?? '')) ?>
                            </a>
                            &nbsp;
                            <!-- badge-member-number: permanent M00001 -->
                            <span class="badge-member-number">
                                <?= $e(format_member_number(isset($ms['member_number']) ? (int) $ms['member_number'] : null)) ?>
                            </span>
                            &nbsp;
                            <!-- badge-card-number: current card C00001 -->
                            <span class="badge-card-number">
                                <?= $e(format_card_number($ms['membership_number'] ?? null)) ?>
                            </span>
                        </p>
                    </div>
                    <?php elseif ($preMember): ?>
                    <div class="uk-margin">
                        <label class="uk-form-label"><?= $e(__('memberships.box_member')) ?></label>
                        <p class="uk-text-bold uk-margin-remove">
                            <?= $e(($preMember['surname'] ?? '') . ' ' . ($preMember['name'] ?? '')) ?>
                            &nbsp;
                            <!-- badge-member-number: permanent M00001 -->
                            <span class="badge-member-number">
                                <?= $e(format_member_number(isset($preMember['member_number']) ? (int) $preMember['member_number'] : null)) ?>
                            </span>
                        </p>
                        <input type="hidden" name="member_id" value="<?= (int) $preMember['id'] ?>">
                    </div>
                    <?php else: ?>
                    <div class="uk-margin">
                        <label class="uk-form-label uk-form-label" for="member_id">
                            <?= $e(__('memberships.box_member')) ?> <span class="uk-text-danger">*</span>
                        </label>
                        <select id="member_id" name="member_id" class="uk-select" required>
                            <option value=""><?= $e(__('memberships.select_member')) ?></option>
                            <?php foreach ($members as $mbr): ?>
                            <option value="<?= (int) $mbr['id'] ?>"
                                    <?= $memberId === (int) $mbr['id'] ? 'selected' : '' ?>>
                                <?= $e($mbr['surname'] . ' ' . $mbr['name']) ?>
                                — <code><?= $e($mbr['membership_number'] ?? 'N/D') ?></code>
                                (N.<?= (int) $mbr['member_number'] ?>)
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <?php endif; ?>

                    <!-- Anno -->
                    <div class="uk-margin">
                        <label class="uk-form-label" for="year">
                            <?= $e(__('memberships.social_year')) ?> <span class="uk-text-danger">*</span>
                        </label>
                        <?php if ($isEdit): ?>
                        <p class="uk-text-bold uk-margin-remove"><?= (int) $v('year', $currentYear) ?></p>
                        <input type="hidden" name="year" value="<?= (int) $v('year', $currentYear) ?>">
                        <?php else: ?>
                        <select id="year" name="year" class="uk-select" required>
                            <option value=""><?= $e(__('memberships.select_year')) ?></option>
                            <?php foreach ([$currentYear, $currentYear + 1] as $yr): ?>
                            <option value="<?= $yr ?>" <?= (int) $v('year', $currentYear) === $yr ? 'selected' : '' ?>>
                                <?= $yr ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                        <?php endif; ?>
                    </div>

                    <!-- Numero tessera -->
                    <?php if (!$isEdit): ?>
                    <div class="uk-margin">
                        <label class="uk-form-label" for="membership_number">
                            <?= $e(__('memberships.membership_number')) ?>
                        </label>
                        <!-- badge-card-number: proposed next card number (source of truth: memberships.membership_number) -->
                        <div class="uk-margin-small-bottom">
                            <span class="badge-card-number" style="font-size:1em">
                                <?= $e(format_card_number($v('membership_number', $nextNumber) ?: $nextNumber)) ?>
                            </span>
                            <span class="uk-text-small uk-text-muted uk-margin-small-left">
                                (<?= $e(__('memberships.next_available')) ?>)
                            </span>
                        </div>
                        <input type="text" id="membership_number" name="membership_number"
                               class="uk-input"
                               value="<?= $e($v('membership_number', $nextNumber)) ?>"
                               placeholder="<?= $e($nextNumber) ?>">
                        <p class="uk-text-small uk-text-muted uk-margin-small-top">
                            <?= $e(__('memberships.membership_number_hint')) ?>
                        </p>
                    </div>
                    <?php endif; ?>

                    <!-- Categoria -->
                    <div class="uk-margin">
                        <label class="uk-form-label" for="category_id">
                            <?= $e(__('memberships.col_category')) ?> <span class="uk-text-danger">*</span>
                        </label>
                        <?php if ($isEdit): ?>
                        <p class="uk-text-bold uk-margin-remove"><?= $e($ms['category_name'] ?? '—') ?></p>
                        <?php else: ?>
                        <select id="category_id" name="category_id" class="uk-select" required
                                onchange="updateFeeFromCategory(this.value)">
                            <option value=""><?= $e(__('memberships.select_category')) ?></option>
                            <?php foreach ($categories as $cat): ?>
                            <option value="<?= (int) $cat['id'] ?>"
                                    <?= (int) $v('categoryId', 0) === (int) $cat['id'] ? 'selected' : '' ?>>
                                <?= $e($cat['label']) ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                        <?php endif; ?>
                    </div>

                    <!-- Quota -->
                    <div class="uk-margin">
                        <label class="uk-form-label" for="fee">
                            <?= $e(__('memberships.fee')) ?> (€) <span class="uk-text-danger">*</span>
                        </label>
                        <input type="number" id="fee" name="fee" class="uk-input" style="max-width:160px"
                               step="0.01" min="0"
                               value="<?= $e(number_format((float) $v('fee', 0), 2, '.', '')) ?>"
                               required>
                    </div>

                    <!-- Status -->
                    <div class="uk-margin">
                        <label class="uk-form-label" for="status">
                            <?= $e(__('memberships.col_status')) ?>
                        </label>
                        <select id="status" name="status" class="uk-select" style="max-width:220px"
                                onchange="togglePaymentBox(this.value)">
                            <?php foreach ($statusOptions as $val => $label): ?>
                            <option value="<?= $e($val) ?>" <?= $v('status', 'pending') === $val ? 'selected' : '' ?>>
                                <?= $e($label) ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- Note -->
                    <div class="uk-margin">
                        <label class="uk-form-label" for="notes"><?= $e(__('members.notes')) ?></label>
                        <textarea id="notes" name="notes" class="uk-textarea" rows="3"
                        ><?= $e($v('notes', '')) ?></textarea>
                    </div>

                </div><!-- /box tessera -->
            </div>

            <!-- BOX PAGAMENTO -->
            <div class="uk-width-1-3@m">
                <?php
                $currentStatus = (string) $v('status', 'pending');
                $boxVisible    = !in_array($currentStatus, ['waived', 'cancelled'], true);
                ?>
                <div id="payment-box"
                     class="uk-card uk-card-default uk-card-body uk-border-rounded"
                     <?= $boxVisible ? '' : 'style="display:none"' ?>>
                    <h3 class="uk-card-title"><?= $e(__('memberships.box_payment')) ?></h3>

                    <!-- Metodo -->
                    <div class="uk-margin">
                        <label class="uk-form-label" for="payment_method">
                            <?= $e(__('memberships.payment_method')) ?>
                        </label>
                        <select id="payment_method" name="payment_method" class="uk-select">
                            <?php foreach ($methodOptions as $val => $label): ?>
                            <option value="<?= $e($val) ?>"
                                    <?= ($v('method', $v('payment_method', 'none')) === $val) ? 'selected' : '' ?>>
                                <?= $e($label) ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- Data pagamento -->
                    <div class="uk-margin">
                        <label class="uk-form-label" for="paid_on"><?= $e(__('memberships.paid_on')) ?></label>
                        <?php
                        $paidOnVal = $v('paidOn', $v('paid_on', ''));
                        $paidOnIso = is_string($paidOnVal) && $paidOnVal !== '' ? format_date_iso($paidOnVal) : date('Y-m-d');
                        ?>
                        <input type="date" id="paid_on" name="paid_on" class="uk-input"
                               value="<?= $e($paidOnIso) ?>">
                    </div>

                    <!-- Riferimento -->
                    <div class="uk-margin">
                        <label class="uk-form-label" for="payment_reference">
                            <?= $e(__('memberships.payment_reference')) ?>
                        </label>
                        <input type="text" id="payment_reference" name="payment_reference"
                               class="uk-input"
                               value="<?= $e($v('reference', $v('payment_reference', ''))) ?>"
                               placeholder="N. ricevuta, causale...">
                    </div>

                </div><!-- /box pagamento -->
            </div>

        </div><!-- /grid -->

        <div class="uk-margin-top" style="display:flex; gap:12px">
            <button type="submit" class="uk-button uk-button-primary">
                <?= $e(__('memberships.action_save')) ?>
            </button>
            <?php if ($isEdit): ?>
            <a href="membership.php?id=<?= (int) $v('id') ?>"
               class="uk-button uk-button-text">
                <?= $e(__('memberships.action_cancel')) ?>
            </a>
            <?php else: ?>
            <a href="<?= $preMember ? 'member.php?id=' . (int) $preMember['id'] : 'memberships.php' ?>"
               class="uk-button uk-button-text">
                <?= $e(__('memberships.action_cancel')) ?>
            </a>
            <?php endif; ?>
        </div>

    </form>

    <!-- =====================================================================
         ZONA PERICOLOSA (solo super_admin, solo in edit mode)
    ====================================================================== -->
    <?php if ($isEdit && $isSuperAdmin): ?>
    <div class="uk-card uk-card-body uk-border-rounded uk-margin-top"
         style="border: 2px solid #f0506e; background: #fff8f8">
        <h3 class="uk-card-title" style="color:#bf2222">
            <span uk-icon="icon: warning; ratio: 1.1" class="uk-margin-small-right"></span>
            <?= $e(__('memberships.dangerous_zone')) ?>
        </h3>
        <p class="uk-text-small uk-text-muted"><?= $e(__('memberships.dangerous_zone_desc')) ?></p>

        <div class="uk-grid uk-grid-medium uk-margin-top" uk-grid>

            <!-- Op 1: Ritira numero tessera -->
            <div class="uk-width-1-2@m">
                <div class="uk-card uk-card-default uk-card-body uk-border-rounded"
                     style="border: 1px solid #f0506e">
                    <h4><?= $e(__('memberships.dangerous_reserve_number')) ?></h4>
                    <p class="uk-text-small uk-text-muted"><?= $e(__('memberships.dangerous_reserve_desc')) ?></p>
                    <form method="post"
                          action="membership-edit.php?id=<?= (int) $v('id') ?>"
                          onsubmit="return confirm('Confermi questa operazione?')">
                        <?= csrf_field() ?>
                        <input type="hidden" name="_action" value="dangerous">
                        <input type="hidden" name="operation" value="reserve_number">
                        <div class="uk-margin">
                            <label class="uk-form-label uk-text-small">
                                <?= $e(__('memberships.dangerous_motivation')) ?>
                            </label>
                            <textarea name="motivation" class="uk-textarea uk-form-small" rows="2"
                                      placeholder="<?= $e(__('memberships.dangerous_motivation_placeholder')) ?>"
                                      minlength="10" required></textarea>
                        </div>
                        <div class="uk-margin">
                            <label class="uk-form-label uk-text-small">
                                <?= $e(__('memberships.dangerous_reserve_confirm_label')) ?>:
                                <span class="badge-card-number"><?= $e(format_card_number($ms['membership_number'] ?? null)) ?></span>
                            </label>
                            <input type="text" name="confirm_number" class="uk-input uk-form-small"
                                   placeholder="<?= $e($ms['membership_number'] ?? '') ?>"
                                   required>
                        </div>
                        <button type="submit" class="uk-button uk-button-danger uk-button-small">
                            <?= $e(__('memberships.dangerous_execute')) ?>
                        </button>
                    </form>
                </div>
            </div>

            <!-- Op 2: Cambia numero tessera -->
            <div class="uk-width-1-2@m">
                <div class="uk-card uk-card-default uk-card-body uk-border-rounded"
                     style="border: 1px solid #f0506e">
                    <h4><?= $e(__('memberships.dangerous_change_number')) ?></h4>
                    <p class="uk-text-small uk-text-muted"><?= $e(__('memberships.dangerous_change_number_desc')) ?></p>
                    <form method="post"
                          action="membership-edit.php?id=<?= (int) $v('id') ?>"
                          onsubmit="return confirm('Confermi questa operazione?')">
                        <?= csrf_field() ?>
                        <input type="hidden" name="_action" value="dangerous">
                        <input type="hidden" name="operation" value="change_number">
                        <div class="uk-margin">
                            <label class="uk-form-label uk-text-small">
                                <?= $e(__('memberships.dangerous_new_number_label')) ?>
                            </label>
                            <input type="text" name="new_number" class="uk-input uk-form-small"
                                   placeholder="SOC0000" required>
                        </div>
                        <div class="uk-margin">
                            <label class="uk-form-label uk-text-small">
                                <?= $e(__('memberships.dangerous_motivation')) ?>
                            </label>
                            <textarea name="motivation" class="uk-textarea uk-form-small" rows="2"
                                      placeholder="<?= $e(__('memberships.dangerous_motivation_placeholder')) ?>"
                                      minlength="10" required></textarea>
                        </div>
                        <button type="submit" class="uk-button uk-button-danger uk-button-small">
                            <?= $e(__('memberships.dangerous_execute')) ?>
                        </button>
                    </form>
                </div>
            </div>

            <!-- Op 3: Forza status tessera -->
            <div class="uk-width-1-2@m">
                <div class="uk-card uk-card-default uk-card-body uk-border-rounded"
                     style="border: 1px solid #f0506e">
                    <h4><?= $e(__('memberships.dangerous_change_status')) ?></h4>
                    <p class="uk-text-small uk-text-muted"><?= $e(__('memberships.dangerous_change_status_desc')) ?></p>
                    <form method="post"
                          action="membership-edit.php?id=<?= (int) $v('id') ?>"
                          onsubmit="return confirm('Confermi questa operazione?')">
                        <?= csrf_field() ?>
                        <input type="hidden" name="_action" value="dangerous">
                        <input type="hidden" name="operation" value="change_status">
                        <div class="uk-margin">
                            <label class="uk-form-label uk-text-small"><?= $e(__('memberships.col_status')) ?></label>
                            <select name="new_status" class="uk-select uk-form-small">
                                <?php foreach (['pending', 'paid', 'waived', 'cancelled'] as $st): ?>
                                <option value="<?= $e($st) ?>"
                                        <?= ($ms['status'] ?? '') === $st ? 'selected' : '' ?>>
                                    <?= $e(__('memberships.status_' . $st)) ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="uk-margin">
                            <label class="uk-form-label uk-text-small"><?= $e(__('memberships.dangerous_motivation')) ?></label>
                            <textarea name="motivation" class="uk-textarea uk-form-small" rows="2"
                                      placeholder="<?= $e(__('memberships.dangerous_motivation_placeholder')) ?>"
                                      minlength="10" required></textarea>
                        </div>
                        <button type="submit" class="uk-button uk-button-danger uk-button-small">
                            <?= $e(__('memberships.dangerous_execute')) ?>
                        </button>
                    </form>
                </div>
            </div>

            <!-- Op 4: Correggi quota pagata -->
            <div class="uk-width-1-2@m">
                <div class="uk-card uk-card-default uk-card-body uk-border-rounded"
                     style="border: 1px solid #f0506e">
                    <h4><?= $e(__('memberships.dangerous_change_fee')) ?></h4>
                    <p class="uk-text-small uk-text-muted"><?= $e(__('memberships.dangerous_change_fee_desc')) ?></p>
                    <form method="post"
                          action="membership-edit.php?id=<?= (int) $v('id') ?>"
                          onsubmit="return confirm('Confermi questa operazione?')">
                        <?= csrf_field() ?>
                        <input type="hidden" name="_action" value="dangerous">
                        <input type="hidden" name="operation" value="change_fee">
                        <div class="uk-margin">
                            <label class="uk-form-label uk-text-small"><?= $e(__('memberships.fee')) ?> (€)</label>
                            <input type="number" name="new_fee" class="uk-input uk-form-small"
                                   step="0.01" min="0"
                                   value="<?= $e(number_format((float) ($ms['fee'] ?? 0), 2, '.', '')) ?>"
                                   required>
                        </div>
                        <div class="uk-margin">
                            <label class="uk-form-label uk-text-small"><?= $e(__('memberships.dangerous_motivation')) ?></label>
                            <textarea name="motivation" class="uk-textarea uk-form-small" rows="2"
                                      placeholder="<?= $e(__('memberships.dangerous_motivation_placeholder')) ?>"
                                      minlength="10" required></textarea>
                        </div>
                        <button type="submit" class="uk-button uk-button-danger uk-button-small">
                            <?= $e(__('memberships.dangerous_execute')) ?>
                        </button>
                    </form>
                </div>
            </div>

            <!-- Op 5: Forza status socio -->
            <div class="uk-width-1-2@m">
                <div class="uk-card uk-card-default uk-card-body uk-border-rounded"
                     style="border: 1px solid #f0506e">
                    <h4><?= $e(__('memberships.dangerous_force_member_status')) ?></h4>
                    <p class="uk-text-small uk-text-muted"><?= $e(__('memberships.dangerous_force_member_status_desc')) ?></p>
                    <form method="post"
                          action="membership-edit.php?id=<?= (int) $v('id') ?>"
                          onsubmit="return confirm('Confermi questa operazione?')">
                        <?= csrf_field() ?>
                        <input type="hidden" name="_action" value="dangerous">
                        <input type="hidden" name="operation" value="force_member_status">
                        <div class="uk-margin">
                            <label class="uk-form-label uk-text-small"><?= $e(__('members.status')) ?></label>
                            <select name="new_member_status" class="uk-select uk-form-small">
                                <?php foreach (['active','in_renewal','not_renewed','lapsed','suspended','resigned','deceased'] as $st): ?>
                                <option value="<?= $e($st) ?>"
                                        <?= ($ms['member_status'] ?? '') === $st ? 'selected' : '' ?>>
                                    <?= $e(__('members.status_' . $st)) ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="uk-margin">
                            <label class="uk-form-label uk-text-small"><?= $e(__('memberships.dangerous_motivation')) ?></label>
                            <textarea name="motivation" class="uk-textarea uk-form-small" rows="2"
                                      placeholder="<?= $e(__('memberships.dangerous_motivation_placeholder')) ?>"
                                      minlength="10" required></textarea>
                        </div>
                        <button type="submit" class="uk-button uk-button-danger uk-button-small">
                            <?= $e(__('memberships.dangerous_execute')) ?>
                        </button>
                    </form>
                </div>
            </div>

        </div><!-- /dangerous grid -->
    </div><!-- /dangerous zone -->
    <?php endif; ?>

    <script>
    const categoryFees = <?= $catFeesJson ?>;

    function updateFeeFromCategory(catId) {
        const fee = categoryFees[catId];
        if (fee !== undefined) {
            document.getElementById('fee').value = parseFloat(fee).toFixed(2);
        }
    }

    function togglePaymentBox(status) {
        const box = document.getElementById('payment-box');
        if (!box) return;
        if (status === 'waived' || status === 'cancelled') {
            box.style.display = 'none';
        } else {
            box.style.display = '';
        }
    }
    </script>

    <?php
    return (string) ob_get_clean();
})();

require __DIR__ . '/layout.php';
