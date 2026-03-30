<?php
// Variables injected by MemberController::index()
// $members, $stats, $filters, $categories, $currentUser, $flash, $activeNav, $csrf

$statusColors = [
    'active'    => 'success',
    'suspended' => 'default',
    'expired'   => 'danger',
    'resigned'  => 'warning',
    'deceased'  => 'secondary',
];

$content = (function () use ($members, $stats, $filters, $categories, $currentUser, $flash, $statusColors): string {
    ob_start();
    ?>

    <?php if (!empty($flash['success'])): ?>
        <div class="uk-alert-success" uk-alert>
            <a class="uk-alert-close" uk-close></a>
            <p><?= e($flash['success']) ?></p>
        </div>
    <?php endif; ?>
    <?php if (!empty($flash['error'])): ?>
        <div class="uk-alert-danger" uk-alert>
            <a class="uk-alert-close" uk-close></a>
            <p><?= e($flash['error']) ?></p>
        </div>
    <?php endif; ?>

    <!-- Header -->
    <div class="uk-flex uk-flex-between uk-flex-middle uk-margin-bottom">
        <h1 class="uk-heading-small uk-margin-remove"><?= e(__('members.member_list')) ?></h1>
        <a href="/index.php?route=members/create" class="uk-button uk-button-primary">
            <span uk-icon="plus"></span> <?= e(__('members.add_member')) ?>
        </a>
    </div>

    <!-- Stats badges -->
    <div class="uk-margin-small-bottom">
        <?php foreach ($stats as $s => $cnt): ?>
            <span class="uk-badge uk-margin-small-right"
                  style="background:<?= [
                      'active'   => '#32d296',
                      'suspended'=> '#999',
                      'expired'  => '#f0506e',
                      'resigned' => '#faa05a',
                      'deceased' => '#666',
                  ][$s] ?? '#1e87f0' ?>">
                <?= e(__('members.status_' . $s)) ?>: <?= (int) $cnt ?>
            </span>
        <?php endforeach; ?>
    </div>

    <!-- Filters -->
    <form method="get" action="/index.php" class="uk-form-small uk-margin-bottom">
        <input type="hidden" name="route" value="members">
        <div class="uk-grid-small uk-flex-middle" uk-grid>
            <div class="uk-width-expand@s">
                <input
                    class="uk-input uk-form-small"
                    type="text"
                    name="search"
                    value="<?= e($filters['search']) ?>"
                    placeholder="<?= e(__('members.search_placeholder')) ?>"
                >
            </div>
            <div class="uk-width-auto">
                <select class="uk-select uk-form-small" name="status">
                    <option value=""><?= e(__('members.filter_all_statuses')) ?></option>
                    <?php foreach (['active','suspended','expired','resigned','deceased'] as $s): ?>
                        <option value="<?= e($s) ?>" <?= $filters['status'] === $s ? 'selected' : '' ?>>
                            <?= e(__('members.status_' . $s)) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <?php if ($categories): ?>
            <div class="uk-width-auto">
                <select class="uk-select uk-form-small" name="category">
                    <option value=""><?= e(__('members.filter_all_categories')) ?></option>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?= (int) $cat['id'] ?>"
                            <?= (int) $filters['category'] === (int) $cat['id'] ? 'selected' : '' ?>>
                            <?= e($cat['label']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <?php endif; ?>
            <div class="uk-width-auto">
                <button class="uk-button uk-button-default uk-button-small" type="submit">
                    <?= e(__('members.search')) ?>
                </button>
                <a href="/index.php?route=members" class="uk-button uk-button-text uk-button-small uk-margin-small-left">
                    <?= e(__('members.reset_filters')) ?>
                </a>
            </div>
        </div>
    </form>

    <!-- Table -->
    <?php if (empty($members['items'])): ?>
        <p class="uk-text-muted"><?= e(__('members.no_members')) ?></p>
    <?php else: ?>

    <div class="uk-overflow-auto">
        <table class="uk-table uk-table-striped uk-table-hover uk-table-small">
            <thead>
                <tr>
                    <th><?= e(__('members.membership_number')) ?></th>
                    <th><?= e(__('members.surname')) ?></th>
                    <th><?= e(__('members.name')) ?></th>
                    <th><?= e(__('members.email')) ?></th>
                    <th><?= e(__('members.status')) ?></th>
                    <th><?= e(__('members.category')) ?></th>
                    <th><?= e(__('members.actions')) ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($members['items'] as $m): ?>
                <tr>
                    <td><code><?= e($m['membership_number']) ?></code></td>
                    <td><?= e($m['surname']) ?></td>
                    <td><?= e($m['name']) ?></td>
                    <td><?= e($m['email']) ?></td>
                    <td>
                        <?php
                        $s = $m['status'] ?? 'active';
                        $color = $statusColors[$s] ?? 'default';
                        $label = __('members.status_' . $s);
                        ?>
                        <span class="uk-label uk-label-<?= e($color) ?>"><?= e($label) ?></span>
                    </td>
                    <td><?= e($m['category_name'] ?? '—') ?></td>
                    <td>
                        <a href="/index.php?route=members/<?= (int) $m['id'] ?>"
                           class="uk-icon-button" uk-icon="eye" title="<?= e(__('members.view')) ?>"></a>
                        <a href="/index.php?route=members/<?= (int) $m['id'] ?>/edit"
                           class="uk-icon-button uk-margin-small-left" uk-icon="pencil" title="<?= e(__('members.edit')) ?>"></a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <?php if ($members['pages'] > 1): ?>
    <ul class="uk-pagination uk-flex-center uk-margin-top">
        <?php if ($members['page'] > 1): ?>
            <li>
                <a href="/index.php?route=members&page=<?= $members['page'] - 1 ?>&<?= http_build_query(array_filter($filters)) ?>">
                    <span uk-pagination-previous></span>
                </a>
            </li>
        <?php endif; ?>
        <?php for ($p = 1; $p <= $members['pages']; $p++): ?>
            <li class="<?= $p === $members['page'] ? 'uk-active' : '' ?>">
                <a href="/index.php?route=members&page=<?= $p ?>&<?= http_build_query(array_filter($filters)) ?>"><?= $p ?></a>
            </li>
        <?php endfor; ?>
        <?php if ($members['page'] < $members['pages']): ?>
            <li>
                <a href="/index.php?route=members&page=<?= $members['page'] + 1 ?>&<?= http_build_query(array_filter($filters)) ?>">
                    <span uk-pagination-next></span>
                </a>
            </li>
        <?php endif; ?>
    </ul>
    <p class="uk-text-center uk-text-small uk-text-muted">
        <?= e(__('members.showing', [
            'from'  => (($members['page'] - 1) * $members['per_page']) + 1,
            'to'    => min($members['page'] * $members['per_page'], $members['total']),
            'total' => $members['total'],
        ])) ?>
    </p>
    <?php endif; ?>

    <?php endif; ?>

    <?php
    return (string) ob_get_clean();
})();

require __DIR__ . '/../layouts/main.php';
