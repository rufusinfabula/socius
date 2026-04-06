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

$content = (function () use (
    $memberships, $years, $categories, $filters, $currentYear, $currentUser,
    $e, $statusUkLabel, $statusStyle
): string {
    ob_start();
    $isStaff = (int) ($currentUser['role_id'] ?? 4) <= 3;
    $items   = $memberships['items'] ?? [];
    $total   = $memberships['total'] ?? 0;
    $page    = $memberships['page'] ?? 1;
    $perPage = $memberships['per_page'] ?? 25;
    $pages   = $memberships['pages'] ?? 0;
    $from    = $total > 0 ? (($page - 1) * $perPage + 1) : 0;
    $to      = min($page * $perPage, $total);
    ?>

    <div class="uk-flex uk-flex-between uk-flex-middle uk-margin-bottom">
        <h1 class="uk-heading-small uk-margin-remove"><?= $e(__('memberships.memberships')) ?></h1>
        <?php if ($isStaff): ?>
        <a href="membership-new.php" class="uk-button uk-button-primary uk-button-small">
            <span uk-icon="plus"></span> <?= $e(__('memberships.new_membership')) ?>
        </a>
        <?php endif; ?>
    </div>

    <!-- Filters -->
    <form method="get" action="memberships.php" class="uk-card uk-card-default uk-card-body uk-border-rounded uk-margin-bottom">
        <div class="uk-grid uk-grid-small uk-flex-middle" uk-grid>
            <div class="uk-width-auto">
                <select name="year" class="uk-select uk-form-small">
                    <option value=""><?= $e(__('memberships.filter_all_years')) ?></option>
                    <?php foreach ($years as $yr): ?>
                    <option value="<?= (int) $yr ?>" <?= (int) ($filters['year'] ?? 0) === (int) $yr ? 'selected' : '' ?>>
                        <?= (int) $yr ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="uk-width-auto">
                <select name="status" class="uk-select uk-form-small">
                    <option value=""><?= $e(__('memberships.filter_all_statuses')) ?></option>
                    <?php foreach (['pending', 'paid', 'waived', 'cancelled'] as $st): ?>
                    <option value="<?= $e($st) ?>" <?= ($filters['status'] ?? '') === $st ? 'selected' : '' ?>>
                        <?= $e(__('memberships.status_' . $st)) ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="uk-width-auto">
                <select name="category_id" class="uk-select uk-form-small">
                    <option value=""><?= $e(__('memberships.filter_all_categories')) ?></option>
                    <?php foreach ($categories as $cat): ?>
                    <option value="<?= (int) $cat['id'] ?>" <?= (int) ($filters['category_id'] ?? 0) === (int) $cat['id'] ? 'selected' : '' ?>>
                        <?= $e($cat['label']) ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="uk-width-auto">
                <button type="submit" class="uk-button uk-button-default uk-button-small">
                    <?= $e(__('memberships.filter')) ?>
                </button>
            </div>
            <div class="uk-width-auto">
                <a href="memberships.php" class="uk-button uk-button-text uk-button-small">
                    <?= $e(__('memberships.reset_filters')) ?>
                </a>
            </div>
        </div>
    </form>

    <!-- Count -->
    <?php if ($total > 0): ?>
    <p class="uk-text-muted uk-text-small uk-margin-small-bottom">
        <?= $e(str_replace([':from', ':to', ':total'], [$from, $to, $total], __('memberships.showing'))) ?>
    </p>
    <?php endif; ?>

    <!-- Table -->
    <?php if (empty($items)): ?>
        <div class="uk-card uk-card-default uk-card-body uk-border-rounded">
            <p class="uk-text-muted"><?= $e(__('memberships.no_memberships')) ?></p>
        </div>
    <?php else: ?>
    <div class="uk-card uk-card-default uk-border-rounded uk-overflow-auto">
        <table class="uk-table uk-table-small uk-table-striped uk-table-divider uk-table-middle uk-margin-remove">
            <thead>
                <tr>
                    <th><?= $e(__('memberships.col_member_number')) ?></th>
                    <th><?= $e(__('memberships.col_full_name')) ?></th>
                    <th><?= $e(__('memberships.col_category')) ?></th>
                    <th><?= $e(__('memberships.col_year')) ?></th>
                    <th><?= $e(__('memberships.col_tessera')) ?></th>
                    <th><?= $e(__('memberships.col_fee')) ?></th>
                    <th><?= $e(__('memberships.col_status')) ?></th>
                    <th><?= $e(__('memberships.col_paid_on')) ?></th>
                    <th><?= $e(__('memberships.col_actions')) ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($items as $ms): ?>
                <tr>
                    <td><code><?= $e($ms['member_number'] ?? '—') ?></code></td>
                    <td>
                        <a href="member.php?id=<?= (int) $ms['member_id'] ?>">
                            <?= $e($ms['surname'] . ' ' . $ms['member_name']) ?>
                        </a>
                    </td>
                    <td><?= $e($ms['category_name'] ?? '—') ?></td>
                    <td><?= (int) $ms['year'] ?></td>
                    <td><code><?= $e($ms['membership_number'] ?? '—') ?></code></td>
                    <td>€&nbsp;<?= number_format((float) $ms['fee'], 2, ',', '.') ?></td>
                    <td>
                        <?php
                        $st = $ms['status'] ?? 'pending';
                        $ukSuffix = $statusUkLabel[$st] ?? '';
                        $stStyle  = $statusStyle[$st] ?? '';
                        ?>
                        <span class="uk-label<?= $ukSuffix ? ' uk-label-' . $e($ukSuffix) : '' ?>"
                              <?= $stStyle ? 'style="' . $e($stStyle) . '"' : '' ?>>
                            <?= $e(__('memberships.status_' . $st)) ?>
                        </span>
                    </td>
                    <td><?= $e($ms['paid_on'] ? format_date($ms['paid_on']) : '—') ?></td>
                    <td>
                        <a href="membership.php?id=<?= (int) $ms['id'] ?>"
                           class="uk-button uk-button-default uk-button-small">
                            <?= $e(__('memberships.action_detail')) ?>
                        </a>
                        <?php if ($isStaff): ?>
                        <a href="membership-edit.php?id=<?= (int) $ms['id'] ?>"
                           class="uk-button uk-button-default uk-button-small">
                            <?= $e(__('memberships.action_edit')) ?>
                        </a>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <?php if ($pages > 1): ?>
    <ul class="uk-pagination uk-margin-top uk-flex-center">
        <?php if ($page > 1): ?>
        <li>
            <a href="?<?= http_build_query(array_merge($filters, ['page' => $page - 1])) ?>">
                <span uk-pagination-previous></span>
            </a>
        </li>
        <?php endif; ?>
        <?php for ($p = 1; $p <= $pages; $p++): ?>
        <li <?= $p === $page ? 'class="uk-active"' : '' ?>>
            <a href="?<?= http_build_query(array_merge($filters, ['page' => $p])) ?>"><?= $p ?></a>
        </li>
        <?php endfor; ?>
        <?php if ($page < $pages): ?>
        <li>
            <a href="?<?= http_build_query(array_merge($filters, ['page' => $page + 1])) ?>">
                <span uk-pagination-next></span>
            </a>
        </li>
        <?php endif; ?>
    </ul>
    <?php endif; ?>
    <?php endif; ?>

    <?php
    return (string) ob_get_clean();
})();

require __DIR__ . '/layout.php';
