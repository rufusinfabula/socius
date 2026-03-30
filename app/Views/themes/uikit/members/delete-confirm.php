<?php
// Variables: $member, $memberships, $payments, $currentUser, $csrf, $flash, $activeNav
$membershipStatusLabels = [
    'pending' => 'In attesa', 'paid' => 'Pagata', 'waived' => 'Esonerata', 'cancelled' => 'Annullata',
];

$content = (function () use ($member, $memberships, $payments, $currentUser, $csrf, $flash, $membershipStatusLabels): string {
    ob_start();
    ?>

    <!-- Warning banner -->
    <div class="uk-alert-danger uk-border-rounded uk-padding-small uk-margin-bottom" uk-alert>
        <div class="uk-flex uk-flex-middle">
            <span uk-icon="icon: warning; ratio: 2" class="uk-margin-right"></span>
            <div>
                <strong><?= e(__('members.emergency_delete_warning')) ?></strong><br>
                <?= e(__('members.emergency_delete_desc')) ?>
            </div>
        </div>
    </div>

    <?php if (!empty($flash['error'])): ?>
        <div class="uk-alert-danger" uk-alert><a class="uk-alert-close" uk-close></a><p><?= e($flash['error']) ?></p></div>
    <?php endif; ?>

    <h1 class="uk-heading-small"><?= e(__('members.delete_confirm_heading')) ?></h1>

    <div class="uk-grid uk-grid-medium" uk-grid>

        <!-- Member summary -->
        <div class="uk-width-1-2@m">
            <div class="uk-card uk-card-default uk-card-body uk-border-rounded">
                <h3 class="uk-card-title"><?= e($member['surname'] . ' ' . $member['name']) ?></h3>
                <dl class="uk-description-list uk-description-list-divider">
                    <dt><?= e(__('members.membership_number')) ?></dt>
                    <dd><code><?= e($member['membership_number']) ?></code></dd>
                    <dt><?= e(__('members.email')) ?></dt>
                    <dd><?= e($member['email']) ?></dd>
                    <dt><?= e(__('members.status')) ?></dt>
                    <dd><?= e($member['status']) ?></dd>
                    <dt><?= e(__('members.joined_on')) ?></dt>
                    <dd><?= e($member['joined_on'] ?? '—') ?></dd>
                </dl>
            </div>
        </div>

        <!-- Tessere da cancellare -->
        <div class="uk-width-1-2@m">
            <div class="uk-card uk-card-danger uk-card-body uk-border-rounded" style="border:2px solid #f0506e">
                <h3 class="uk-card-title uk-text-danger"><?= e(__('members.memberships_to_delete')) ?></h3>
                <?php if (empty($memberships)): ?>
                    <p class="uk-text-muted"><?= e(__('members.no_memberships')) ?></p>
                <?php else: ?>
                <ul class="uk-list uk-list-divider">
                    <?php foreach ($memberships as $ms): ?>
                    <li>
                        <strong><?= (int) $ms['year'] ?></strong>
                        — <?= e($ms['category_name'] ?? '—') ?>
                        — €<?= number_format((float) $ms['fee'], 2) ?>
                        — <em><?= e($membershipStatusLabels[$ms['status']] ?? $ms['status']) ?></em>
                    </li>
                    <?php endforeach; ?>
                </ul>
                <?php endif; ?>
            </div>
        </div>

        <!-- Pagamenti mantenuti -->
        <div class="uk-width-1-1">
            <div class="uk-card uk-card-default uk-card-body uk-border-rounded" style="border:2px solid #32d296">
                <h3 class="uk-card-title uk-text-success"><?= e(__('members.payments_kept')) ?></h3>
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

    </div>

    <!-- Confirmation form -->
    <div class="uk-card uk-card-default uk-card-body uk-border-rounded uk-margin-top"
         style="border: 2px solid #f0506e">

        <form method="POST"
              action="/index.php?route=members/<?= (int) $member['id'] ?>/delete"
              id="delete-form">
            <input type="hidden" name="_csrf_token" value="<?= e($csrf) ?>">

            <!-- Free number radio -->
            <div class="uk-margin">
                <p class="uk-text-bold"><?= e(__('members.free_number_label')) ?></p>
                <label>
                    <input class="uk-radio" type="radio" name="free_number" value="1" checked>
                    <?= e(__('members.free_number_yes', ['number' => $member['membership_number']])) ?>
                </label><br>
                <label class="uk-margin-small-top">
                    <input class="uk-radio" type="radio" name="free_number" value="0">
                    <?= e(__('members.free_number_no', ['number' => $member['membership_number']])) ?>
                </label>
            </div>

            <!-- Confirmation word -->
            <div class="uk-margin">
                <label class="uk-form-label" for="confirm_word">
                    <strong><?= e(__('members.delete_type_confirm')) ?></strong>
                </label>
                <input
                    class="uk-input"
                    type="text"
                    id="confirm_word"
                    name="confirm_word"
                    placeholder="<?= e(__('members.delete_confirm_placeholder')) ?>"
                    autocomplete="off"
                    style="max-width: 300px"
                >
            </div>

            <div class="uk-flex uk-flex-between uk-margin-top">
                <a href="/index.php?route=members/<?= (int) $member['id'] ?>"
                   class="uk-button uk-button-default">
                    ← <?= e(__('members.back_to_profile')) ?>
                </a>
                <button
                    id="delete-btn"
                    class="uk-button uk-button-danger"
                    type="submit"
                    disabled>
                    <span uk-icon="trash"></span>
                    <?= e(__('members.delete_execute')) ?>
                </button>
            </div>

        </form>
    </div>

    <script>
    (function () {
        var input = document.getElementById('confirm_word');
        var btn   = document.getElementById('delete-btn');
        input.addEventListener('input', function () {
            btn.disabled = (input.value !== 'DELETE');
        });
    })();
    </script>

    <?php
    return (string) ob_get_clean();
})();

require __DIR__ . '/../layouts/main.php';
