<?php
$e = fn($v) => htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8');

$comms         = $communications ?? [];
$pagination    = $pagination     ?? [];
$currentPeriod = $currentPeriod  ?? '';
$filterStatus  = $filterStatus   ?? '';
$filterType    = $filterType     ?? '';

$periodColors = [
    'open'             => '#17a2b8',
    'first_reminder'   => '#fd7e14',
    'second_reminder'  => '#fd7e14',
    'third_reminder'   => '#dc3545',
    'close'            => '#dc3545',
    'lapse'            => '#6c757d',
];

$typeColors = [
    'general' => '#1e87f0',
    'renewal' => '#fd7e14',
    'board'   => '#9c27b0',
    'direct'  => '#28a745',
];

$statusColors = [
    'draft' => '#6c757d',
    'ready' => '#fd7e14',
    'sent'  => '#28a745',
];

$content = (function () use (
    $e, $comms, $pagination, $currentPeriod, $periodColors,
    $typeColors, $statusColors, $filterStatus, $filterType
): string {
    ob_start();
    ?>

    <?php if ($currentPeriod !== '' && isset($periodColors[$currentPeriod])): ?>
    <div style="background:<?= $e($periodColors[$currentPeriod]) ?>;
                color:#fff; padding:12px 18px; border-radius:6px; margin-bottom:20px;
                display:flex; align-items:center; justify-content:space-between">
        <div>
            <strong><?= $e(__('communications.period_' . $currentPeriod)) ?></strong>
            — <?= $e(__('communications.period_active_desc')) ?>
        </div>
        <a href="sync-run.php?return=<?= urlencode('communications.php') ?>"
           style="color:#fff; font-size:0.85em; text-decoration:underline">
            <?= $e(__('communications.force_period_check')) ?>
        </a>
    </div>
    <?php endif; ?>

    <!-- Header -->
    <div class="uk-flex uk-flex-between uk-flex-middle uk-margin-bottom">
        <h2 class="uk-heading-small uk-margin-remove">
            <?= $e(__('communications.communications')) ?>
        </h2>
        <a href="communication-new.php" class="uk-button uk-button-primary">
            <span uk-icon="plus" class="uk-margin-small-right"></span>
            <?= $e(__('communications.new_communication')) ?>
        </a>
    </div>

    <!-- Filters -->
    <form method="get" action="communications.php" class="uk-form-small uk-margin-bottom">
        <div class="uk-grid uk-grid-small uk-flex-middle" uk-grid>
            <div class="uk-width-auto">
                <select name="status" class="uk-select uk-form-small" style="min-width:140px"
                        onchange="this.form.submit()">
                    <option value=""><?= $e(__('communications.status')) ?>: tutti</option>
                    <?php foreach (['draft','ready','sent'] as $st): ?>
                    <option value="<?= $e($st) ?>" <?= $filterStatus === $st ? 'selected' : '' ?>>
                        <?= $e(__('communications.status_' . $st)) ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="uk-width-auto">
                <select name="type" class="uk-select uk-form-small" style="min-width:160px"
                        onchange="this.form.submit()">
                    <option value=""><?= $e(__('communications.type')) ?>: tutti</option>
                    <?php foreach (['general','renewal','board','direct'] as $tp): ?>
                    <option value="<?= $e($tp) ?>" <?= $filterType === $tp ? 'selected' : '' ?>>
                        <?= $e(__('communications.type_' . $tp)) ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <?php if ($filterStatus !== '' || $filterType !== ''): ?>
            <div class="uk-width-auto">
                <a href="communications.php" class="uk-button uk-button-text uk-button-small">✕ Reset</a>
            </div>
            <?php endif; ?>
        </div>
    </form>

    <!-- Table -->
    <?php if (empty($comms)): ?>
    <div class="uk-alert-default" uk-alert>
        <p><?= $e(__('communications.no_communications')) ?></p>
    </div>
    <?php else: ?>
    <div class="uk-overflow-auto">
        <table class="uk-table uk-table-divider uk-table-hover uk-table-small">
            <thead>
                <tr>
                    <th><?= $e(__('communications.col_title')) ?></th>
                    <th><?= $e(__('communications.col_type')) ?></th>
                    <th><?= $e(__('communications.col_period')) ?></th>
                    <th><?= $e(__('communications.col_recipients')) ?></th>
                    <th><?= $e(__('communications.col_status')) ?></th>
                    <th><?= $e(__('communications.col_date')) ?></th>
                    <th><?= $e(__('communications.col_actions')) ?></th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($comms as $comm): ?>
            <tr>
                <td>
                    <a href="communication.php?id=<?= (int) $comm['id'] ?>">
                        <?= $e($comm['title']) ?>
                    </a>
                    <div class="uk-text-small uk-text-muted"><?= $e($comm['subject']) ?></div>
                </td>
                <td>
                    <?php $tp = (string) ($comm['type'] ?? 'general'); ?>
                    <span class="uk-label uk-label-small"
                          style="background:<?= $e($typeColors[$tp] ?? '#999') ?>; font-size:0.75em">
                        <?= $e(__('communications.type_' . $tp)) ?>
                    </span>
                </td>
                <td>
                    <?php $rp = (string) ($comm['renewal_period'] ?? ''); ?>
                    <?php if ($rp !== ''): ?>
                    <span class="uk-text-small"
                          style="color:<?= $e($periodColors[$rp] ?? '#999') ?>; font-weight:600">
                        <?= $e(__('communications.period_' . $rp)) ?>
                    </span>
                    <?php else: ?>
                    <span class="uk-text-muted">—</span>
                    <?php endif; ?>
                </td>
                <td><?= (int) $comm['recipient_count'] ?></td>
                <td>
                    <?php $st = (string) ($comm['status'] ?? 'draft'); ?>
                    <span style="display:inline-block; padding:2px 8px; border-radius:4px; font-size:0.8em;
                                 background:<?= $e($statusColors[$st] ?? '#999') ?>; color:#fff">
                        <?= $e(__('communications.status_' . $st)) ?>
                    </span>
                </td>
                <td class="uk-text-small uk-text-muted">
                    <?php if (!empty($comm['sent_at'])): ?>
                        <?= $e(format_date((string) $comm['sent_at'], true)) ?>
                    <?php else: ?>
                        <?= $e(format_date((string) ($comm['created_at'] ?? ''), false)) ?>
                    <?php endif; ?>
                </td>
                <td>
                    <a href="communication.php?id=<?= (int) $comm['id'] ?>"
                       class="uk-button uk-button-default uk-button-small">
                        <?= $e(__('communications.communication')) ?>
                    </a>
                    <?php if ((string) ($comm['status'] ?? '') === 'draft'): ?>
                    <a href="communication-edit.php?id=<?= (int) $comm['id'] ?>"
                       class="uk-button uk-button-default uk-button-small">
                        <?= $e(__('communications.action_edit')) ?>
                    </a>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <?php if ((int) ($pagination['pages'] ?? 1) > 1): ?>
    <ul class="uk-pagination uk-margin-top">
        <?php for ($p = 1; $p <= (int) $pagination['pages']; $p++): ?>
        <li class="<?= (int) ($pagination['page'] ?? 1) === $p ? 'uk-active' : '' ?>">
            <a href="communications.php?page=<?= $p ?>
                <?= $filterStatus ? '&status=' . urlencode($filterStatus) : '' ?>
                <?= $filterType   ? '&type='   . urlencode($filterType)   : '' ?>">
                <?= $p ?>
            </a>
        </li>
        <?php endfor; ?>
    </ul>
    <?php endif; ?>
    <?php endif; ?>

    <?php
    return (string) ob_get_clean();
})();

require __DIR__ . '/layout.php';
