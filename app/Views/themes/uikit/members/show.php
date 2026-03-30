<?php
// Variables: $member, $memberships, $payments, $currentUser, $flash, $activeNav

$statusColors = [
    'active'    => 'success',
    'suspended' => 'default',
    'expired'   => 'danger',
    'resigned'  => 'warning',
    'deceased'  => 'secondary',
];

$membershipStatusColors = [
    'pending'   => 'warning',
    'paid'      => 'success',
    'waived'    => 'default',
    'cancelled' => 'danger',
];

$content = (function () use ($member, $memberships, $payments, $currentUser, $flash, $statusColors, $membershipStatusColors): string {
    ob_start();
    $isSuperAdmin = (int) $currentUser['role_id'] === 1;
    $s = $member['status'] ?? 'active';
    ?>

    <?php if (!empty($flash['success'])): ?>
        <div class="uk-alert-success" uk-alert><a class="uk-alert-close" uk-close></a><p><?= e($flash['success']) ?></p></div>
    <?php endif; ?>
    <?php if (!empty($flash['error'])): ?>
        <div class="uk-alert-danger" uk-alert><a class="uk-alert-close" uk-close></a><p><?= e($flash['error']) ?></p></div>
    <?php endif; ?>

    <!-- Breadcrumb -->
    <ul class="uk-breadcrumb uk-margin-small-bottom">
        <li><a href="/index.php?route=members"><?= e(__('members.member_list')) ?></a></li>
        <li><span><?= e($member['surname'] . ' ' . $member['name']) ?></span></li>
    </ul>

    <!-- Profile header -->
    <div class="uk-card uk-card-default uk-card-body uk-border-rounded uk-margin-bottom">
        <div class="uk-flex uk-flex-between uk-flex-middle">
            <div>
                <h2 class="uk-card-title uk-margin-remove">
                    <?= e($member['surname'] . ' ' . $member['name']) ?>
                    <span class="uk-label uk-label-<?= e($statusColors[$s] ?? 'default') ?> uk-margin-small-left">
                        <?= e(__('members.status_' . $s)) ?>
                    </span>
                </h2>
                <p class="uk-text-muted uk-margin-remove">
                    <code><?= e($member['membership_number']) ?></code>
                    <?php if ($member['category_name']): ?>
                        &nbsp;·&nbsp; <?= e($member['category_name']) ?>
                    <?php endif; ?>
                    &nbsp;·&nbsp; <?= e(__('members.joined_on')) ?>: <?= e($member['joined_on'] ?? '—') ?>
                </p>
            </div>
            <div>
                <?php if ((int) $currentUser['role_id'] <= 3): ?>
                    <a href="/index.php?route=members/<?= (int) $member['id'] ?>/edit"
                       class="uk-button uk-button-default uk-button-small">
                        <span uk-icon="pencil"></span> <?= e(__('members.edit')) ?>
                    </a>
                <?php endif; ?>
                <?php if ($isSuperAdmin): ?>
                    <a href="/index.php?route=members/<?= (int) $member['id'] ?>/delete-confirm"
                       class="uk-button uk-button-danger uk-button-small uk-margin-small-left">
                        <span uk-icon="warning"></span> <?= e(__('members.emergency_delete')) ?>
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="uk-grid uk-grid-medium" uk-grid>

        <!-- Personal data -->
        <div class="uk-width-1-2@m">
            <div class="uk-card uk-card-default uk-card-body uk-border-rounded uk-height-1-1">
                <h3 class="uk-card-title"><?= e(__('members.member_profile')) ?></h3>
                <dl class="uk-description-list">
                    <dt><?= e(__('members.email')) ?></dt>
                    <dd><?= e($member['email']) ?></dd>

                    <?php if ($member['phone']): ?>
                    <dt><?= e(__('members.phone')) ?></dt>
                    <dd><?= e($member['phone']) ?></dd>
                    <?php endif; ?>

                    <?php if ($member['fiscal_code']): ?>
                    <dt><?= e(__('members.fiscal_code')) ?></dt>
                    <dd><code><?= e($member['fiscal_code']) ?></code></dd>
                    <?php endif; ?>

                    <?php if ($member['birth_date']): ?>
                    <dt><?= e(__('members.birth_date')) ?></dt>
                    <dd><?= e($member['birth_date']) ?><?= $member['birth_place'] ? ' — ' . e($member['birth_place']) : '' ?></dd>
                    <?php endif; ?>

                    <?php if ($member['address']): ?>
                    <dt><?= e(__('members.address')) ?></dt>
                    <dd>
                        <?= e($member['address']) ?><br>
                        <?= e(trim($member['postal_code'] . ' ' . $member['city'] . ' ' . $member['province'])) ?>
                        <?php if ($member['country'] && $member['country'] !== 'IT'): ?>
                            (<?= e($member['country']) ?>)
                        <?php endif; ?>
                    </dd>
                    <?php endif; ?>

                    <?php if ($member['resigned_on']): ?>
                    <dt><?= e(__('members.resigned_on')) ?></dt>
                    <dd><?= e($member['resigned_on']) ?></dd>
                    <?php endif; ?>
                </dl>

                <?php if ((int) $currentUser['role_id'] <= 3 && !empty($member['notes'])): ?>
                    <hr>
                    <p class="uk-text-small uk-text-muted"><strong><?= e(__('members.notes')) ?>:</strong><br>
                    <?= nl2br(e($member['notes'])) ?></p>
                <?php endif; ?>
            </div>
        </div>

        <!-- Memberships -->
        <div class="uk-width-1-2@m">
            <div class="uk-card uk-card-default uk-card-body uk-border-rounded">
                <h3 class="uk-card-title"><?= e(__('members.memberships_history')) ?></h3>
                <?php if (empty($memberships)): ?>
                    <p class="uk-text-muted"><?= e(__('members.no_memberships')) ?></p>
                <?php else: ?>
                <table class="uk-table uk-table-small uk-table-striped">
                    <thead>
                        <tr>
                            <th><?= e(__('members.membership_year')) ?></th>
                            <th><?= e(__('members.membership_fee')) ?></th>
                            <th><?= e(__('members.membership_valid')) ?></th>
                            <th><?= e(__('members.membership_status')) ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($memberships as $ms): ?>
                        <tr>
                            <td><?= (int) $ms['year'] ?></td>
                            <td>€ <?= number_format((float) $ms['fee'], 2, ',', '.') ?></td>
                            <td><?= e($ms['valid_from'] . ' / ' . $ms['valid_until']) ?></td>
                            <td>
                                <span class="uk-label uk-label-<?= e($membershipStatusColors[$ms['status']] ?? 'default') ?>">
                                    <?= e($ms['status']) ?>
                                </span>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <?php endif; ?>
            </div>
        </div>

        <!-- Payments -->
        <?php if ((int) $currentUser['role_id'] <= 3): ?>
        <div class="uk-width-1-1">
            <div class="uk-card uk-card-default uk-card-body uk-border-rounded">
                <h3 class="uk-card-title"><?= e(__('members.payments_linked')) ?></h3>
                <?php if (empty($payments)): ?>
                    <p class="uk-text-muted"><?= e(__('members.no_payments')) ?></p>
                <?php else: ?>
                <table class="uk-table uk-table-small uk-table-striped">
                    <thead>
                        <tr>
                            <th><?= e(__('members.payment_date')) ?></th>
                            <th><?= e(__('members.payment_amount')) ?></th>
                            <th><?= e(__('members.payment_gateway')) ?></th>
                            <th><?= e(__('members.payment_status')) ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($payments as $pay): ?>
                        <tr>
                            <td><?= e($pay['paid_at']) ?></td>
                            <td>€ <?= number_format((float) $pay['amount'], 2, ',', '.') ?></td>
                            <td><?= e($pay['gateway']) ?></td>
                            <td><?= e($pay['status']) ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>

    </div>

    <div class="uk-margin-top">
        <a href="/index.php?route=members" class="uk-button uk-button-text">
            ← <?= e(__('members.back_to_list')) ?>
        </a>
    </div>

    <?php
    return (string) ob_get_clean();
})();

require __DIR__ . '/../layouts/main.php';
