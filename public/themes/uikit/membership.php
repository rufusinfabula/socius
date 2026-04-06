<?php
$e = fn($v) => htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8');

$statusUkLabel = [
    'pending'   => 'warning',
    'paid'      => 'success',
    'waived'    => '',
    'cancelled' => 'danger',
];
$statusStyle = [
    'waived' => 'background:#1e87f0; color:#fff',
];

$memberStatusUkLabel = [
    'active'      => 'success',
    'in_renewal'  => 'warning',
    'not_renewed' => '',
    'lapsed'      => 'danger',
    'suspended'   => '',
    'resigned'    => '',
    'deceased'    => '',
];
$memberStatusStyle = [
    'not_renewed' => 'background:#e67e22; color:#fff',
    'suspended'   => 'background:#999; color:#fff',
    'resigned'    => 'background:#666; color:#fff',
    'deceased'    => 'background:#222; color:#fff',
];

$content = (function () use (
    $membership, $currentUser,
    $e, $statusUkLabel, $statusStyle, $memberStatusUkLabel, $memberStatusStyle
): string {
    ob_start();
    $isStaff = (int) ($currentUser['role_id'] ?? 4) <= 3;
    $ms      = $membership;
    $st      = $ms['status'] ?? 'pending';
    $mst     = $ms['member_status'] ?? 'active';

    $methodLabels = [
        'cash'          => __('memberships.method_cash'),
        'bank_transfer' => __('memberships.method_bank'),
        'paypal'        => __('memberships.method_paypal'),
        'satispay'      => __('memberships.method_satispay'),
        'waived'        => __('memberships.method_waived'),
        'other'         => __('memberships.method_other'),
    ];
    ?>

    <!-- Breadcrumb -->
    <ul class="uk-breadcrumb uk-margin-small-bottom">
        <li><a href="memberships.php"><?= $e(__('memberships.memberships')) ?></a></li>
        <li><span><?= $e(__('memberships.membership_detail')) ?></span></li>
    </ul>

    <!-- Action buttons -->
    <div class="uk-flex uk-flex-right uk-margin-bottom" style="gap:8px">
        <?php if ($isStaff): ?>
        <a href="membership-edit.php?id=<?= (int) $ms['id'] ?>"
           class="uk-button uk-button-default uk-button-small">
            <span uk-icon="pencil"></span> <?= $e(__('memberships.action_edit')) ?>
        </a>
        <?php endif; ?>
        <a href="memberships.php" class="uk-button uk-button-text uk-button-small">
            ← <?= $e(__('memberships.action_back_list')) ?>
        </a>
    </div>

    <div class="uk-grid uk-grid-medium uk-margin-bottom" uk-grid>

        <!-- BOX TESSERA (2/3) -->
        <div class="uk-width-2-3@m">
            <div class="uk-card uk-card-default uk-card-body uk-border-rounded uk-height-1-1">
                <h3 class="uk-card-title">
                    <span uk-icon="icon: tag; ratio: 1.1" class="uk-margin-small-right"></span>
                    <?= $e(__('memberships.membership')) ?>
                    &nbsp;
                    <!-- badge-card-number prominent: source of truth from memberships.membership_number -->
                    <?php if (!empty($ms['membership_number'])): ?>
                    <span class="badge-card-number" style="font-size:1em">
                        <?= $e(format_card_number($ms['membership_number'])) ?>
                    </span>
                    &nbsp;
                    <?php endif; ?>
                    <?php
                    $ukSuffix = $statusUkLabel[$st] ?? '';
                    $stStyle  = $statusStyle[$st] ?? '';
                    ?>
                    <span class="uk-label<?= $ukSuffix ? ' uk-label-' . $e($ukSuffix) : '' ?>"
                          <?= $stStyle ? 'style="' . $e($stStyle) . '"' : '' ?>>
                        <?= $e(__('memberships.status_' . $st)) ?>
                    </span>
                </h3>

                <div class="uk-grid uk-grid-small" uk-grid>

                    <div class="uk-width-1-2@s">
                        <p class="uk-text-muted uk-text-small uk-margin-remove-bottom"><?= $e(__('memberships.social_year')) ?></p>
                        <p class="uk-margin-remove-top"><?= (int) $ms['year'] ?></p>
                    </div>

                    <!-- Card number field in grid (badge-card-number) -->
                    <div class="uk-width-1-2@s">
                        <p class="uk-text-muted uk-text-small uk-margin-remove-bottom"><?= $e(__('memberships.membership_number')) ?></p>
                        <p class="uk-margin-remove-top">
                            <?php if (!empty($ms['membership_number'])): ?>
                            <span class="badge-card-number">
                                <?= $e(format_card_number($ms['membership_number'])) ?>
                            </span>
                            <?php else: ?>
                            <span class="uk-text-muted">—</span>
                            <?php endif; ?>
                        </p>
                    </div>

                    <div class="uk-width-1-2@s">
                        <p class="uk-text-muted uk-text-small uk-margin-remove-bottom"><?= $e(__('memberships.col_category')) ?></p>
                        <p class="uk-margin-remove-top"><?= $e($ms['category_name'] ?? '—') ?></p>
                    </div>

                    <div class="uk-width-1-2@s">
                        <p class="uk-text-muted uk-text-small uk-margin-remove-bottom"><?= $e(__('memberships.fee')) ?></p>
                        <p class="uk-margin-remove-top">€&nbsp;<?= number_format((float) $ms['fee'], 2, ',', '.') ?></p>
                    </div>

                    <div class="uk-width-1-2@s">
                        <p class="uk-text-muted uk-text-small uk-margin-remove-bottom"><?= $e(__('memberships.paid_on')) ?></p>
                        <p class="uk-margin-remove-top"><?= $e($ms['paid_on'] ? format_date($ms['paid_on']) : '—') ?></p>
                    </div>

                    <div class="uk-width-1-2@s">
                        <p class="uk-text-muted uk-text-small uk-margin-remove-bottom"><?= $e(__('memberships.payment_method')) ?></p>
                        <p class="uk-margin-remove-top">
                            <?php
                            $pm = $ms['payment_method'] ?? '';
                            echo $e($pm !== '' ? ($methodLabels[$pm] ?? $pm) : '—');
                            ?>
                        </p>
                    </div>

                    <?php if (!empty($ms['payment_reference'])): ?>
                    <div class="uk-width-1-1">
                        <p class="uk-text-muted uk-text-small uk-margin-remove-bottom"><?= $e(__('memberships.payment_reference')) ?></p>
                        <p class="uk-margin-remove-top"><code><?= $e($ms['payment_reference']) ?></code></p>
                    </div>
                    <?php endif; ?>

                    <?php if (!empty($ms['notes'])): ?>
                    <div class="uk-width-1-1">
                        <hr>
                        <p class="uk-text-muted uk-text-small uk-margin-remove-bottom"><?= $e(__('members.notes')) ?></p>
                        <p class="uk-margin-remove-top uk-text-small"><?= nl2br($e($ms['notes'])) ?></p>
                    </div>
                    <?php endif; ?>

                </div>
            </div>
        </div>

        <!-- BOX SOCIO (1/3) -->
        <div class="uk-width-1-3@m">
            <div class="uk-card uk-card-default uk-card-body uk-border-rounded uk-height-1-1">
                <h3 class="uk-card-title">
                    <span uk-icon="icon: user; ratio: 1.1" class="uk-margin-small-right"></span>
                    <?= $e(__('memberships.box_member')) ?>
                </h3>

                <!-- Name + badge-member-number inline: "Fabio Ranfi  [M00001]" -->
                <div class="uk-margin">
                    <p class="uk-text-muted uk-text-small uk-margin-remove-bottom"><?= $e(__('members.full_name')) ?></p>
                    <p class="uk-margin-remove-top">
                        <a href="member.php?id=<?= (int) $ms['member_id'] ?>">
                            <?= $e(($ms['member_surname'] ?? '') . ' ' . ($ms['member_name'] ?? '')) ?>
                        </a>
                        &nbsp;
                        <!-- badge-member-number: permanent M00001 identifier (blue) -->
                        <span class="badge-member-number">
                            <?= $e(format_member_number(isset($ms['member_number']) ? (int) $ms['member_number'] : null)) ?>
                        </span>
                    </p>
                </div>

                <div class="uk-margin">
                    <p class="uk-text-muted uk-text-small uk-margin-remove-bottom"><?= $e(__('members.email')) ?></p>
                    <p class="uk-margin-remove-top">
                        <a href="mailto:<?= $e($ms['member_email'] ?? '') ?>">
                            <?= $e($ms['member_email'] ?? '—') ?>
                        </a>
                    </p>
                </div>

                <div class="uk-margin">
                    <p class="uk-text-muted uk-text-small uk-margin-remove-bottom"><?= $e(__('members.status')) ?></p>
                    <p class="uk-margin-remove-top">
                        <?php
                        $mUkSuffix = $memberStatusUkLabel[$mst] ?? '';
                        $mStStyle  = $memberStatusStyle[$mst] ?? '';
                        ?>
                        <span class="uk-label<?= $mUkSuffix ? ' uk-label-' . $e($mUkSuffix) : '' ?>"
                              <?= $mStStyle ? 'style="' . $e($mStStyle) . '"' : '' ?>>
                            <?= $e(__('members.status_' . $mst)) ?>
                        </span>
                    </p>
                </div>

            </div>
        </div>

    </div><!-- /grid -->

    <?php
    return (string) ob_get_clean();
})();

require __DIR__ . '/layout.php';
