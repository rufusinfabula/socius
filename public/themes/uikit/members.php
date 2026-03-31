<?php
$e = fn($v) => htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8');

$statusUkLabel = [
    'active'      => 'success',
    'in_renewal'  => 'warning',
    'not_renewed' => '',
    'lapsed'      => 'danger',
    'suspended'   => '',
    'resigned'    => '',
    'deceased'    => '',
];
$statusStyle = [
    'not_renewed' => 'background:#e67e22; color:#fff',
    'suspended'   => 'background:#999; color:#fff',
    'resigned'    => 'background:#666; color:#fff',
    'deceased'    => 'background:#222; color:#fff',
];
$statusLabel = [
    'active'      => __('members.status_active'),
    'in_renewal'  => __('members.status_in_renewal'),
    'not_renewed' => __('members.status_not_renewed'),
    'lapsed'      => __('members.status_lapsed'),
    'suspended'   => __('members.status_suspended'),
    'resigned'    => __('members.status_resigned'),
    'deceased'    => __('members.status_deceased'),
];
$statusColor = [
    'active'      => '#32d296',
    'in_renewal'  => '#f0c060',
    'not_renewed' => '#e67e22',
    'lapsed'      => '#f0506e',
    'suspended'   => '#999',
    'resigned'    => '#666',
    'deceased'    => '#222',
];

$content = (function () use (
    $members, $stats, $filters, $categories,
    $e, $statusUkLabel, $statusStyle, $statusLabel, $statusColor
): string {
    ob_start();
    ?>

    <!-- Header -->
    <div class="uk-flex uk-flex-between uk-flex-middle uk-margin-bottom">
        <h1 class="uk-heading-small uk-margin-remove">Lista Soci</h1>
        <a href="member-new.php" class="uk-button uk-button-primary">
            <span uk-icon="plus"></span> Nuovo Socio
        </a>
    </div>

    <!-- Stats -->
    <?php if (!empty($stats)): ?>
    <div class="uk-margin-small-bottom">
        <?php foreach ($stats as $s => $cnt): ?>
            <span class="uk-badge uk-margin-small-right"
                  style="background:<?= $statusColor[$s] ?? '#1e87f0' ?>">
                <?= $e($statusLabel[$s] ?? $s) ?>: <?= (int) $cnt ?>
            </span>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>

    <!-- Filters -->
    <form method="get" action="members.php" class="uk-form-small uk-margin-bottom">
        <div class="uk-grid-small uk-flex-middle" uk-grid>
            <div class="uk-width-expand@s">
                <input class="uk-input uk-form-small" type="text" name="search"
                       value="<?= $e($filters['search']) ?>"
                       placeholder="Cerca per nome, cognome, email, numero tessera…">
            </div>
            <div class="uk-width-auto">
                <select class="uk-select uk-form-small" name="status">
                    <option value=""><?= $e(__('members.filter_all_statuses')) ?></option>
                    <?php foreach (array_keys($statusLabel) as $s): ?>
                        <option value="<?= $e($s) ?>" <?= $filters['status'] === $s ? 'selected' : '' ?>>
                            <?= $e($statusLabel[$s]) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <?php if (!empty($categories)): ?>
            <div class="uk-width-auto">
                <select class="uk-select uk-form-small" name="category">
                    <option value="">Tutte le categorie</option>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?= (int) $cat['id'] ?>"
                            <?= (int) $filters['category'] === (int) $cat['id'] ? 'selected' : '' ?>>
                            <?= $e($cat['label']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <?php endif; ?>
            <div class="uk-width-auto">
                <button class="uk-button uk-button-default uk-button-small" type="submit">Filtra</button>
                <a href="members.php" class="uk-button uk-button-text uk-button-small uk-margin-small-left">Reset</a>
            </div>
        </div>
    </form>

    <!-- Table -->
    <?php if (empty($members['items'])): ?>
        <p class="uk-text-muted">Nessun socio trovato.</p>
    <?php else: ?>

    <div class="uk-overflow-auto">
        <table class="uk-table uk-table-striped uk-table-hover uk-table-small">
            <thead>
                <tr>
                    <th><?= $e(__('members.member_number')) ?></th>
                    <th><?= $e(__('members.membership_number')) ?></th>
                    <th><?= $e(__('members.surname')) ?></th>
                    <th><?= $e(__('members.name')) ?></th>
                    <th><?= $e(__('members.email')) ?></th>
                    <th><?= $e(__('members.status')) ?></th>
                    <th><?= $e(__('members.actions')) ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($members['items'] as $m): ?>
                <tr>
                    <td><?= isset($m['member_number']) ? (int) $m['member_number'] : '—' ?></td>
                    <td><code><?= $e($m['membership_number']) ?></code></td>
                    <td><?= $e($m['surname']) ?></td>
                    <td><?= $e($m['name']) ?></td>
                    <td><?= $e($m['email']) ?></td>
                    <td>
                        <?php
                        $s = $m['status'] ?? 'active';
                        $ukSuffix  = $statusUkLabel[$s] ?? '';
                        $badgeStyle = $statusStyle[$s] ?? '';
                        ?>
                        <span class="uk-label<?= $ukSuffix ? ' uk-label-' . $e($ukSuffix) : '' ?>"
                              <?= $badgeStyle ? 'style="' . $e($badgeStyle) . '"' : '' ?>>
                            <?= $e($statusLabel[$s] ?? $s) ?>
                        </span>
                    </td>
                    <td>
                        <a href="member.php?id=<?= (int) $m['id'] ?>"
                           class="uk-icon-button" uk-icon="eye" title="Visualizza"></a>
                        <a href="member-edit.php?id=<?= (int) $m['id'] ?>"
                           class="uk-icon-button uk-margin-small-left" uk-icon="pencil" title="Modifica"></a>
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
                <a href="members.php?page=<?= $members['page'] - 1 ?>&<?= http_build_query(array_filter($filters)) ?>">
                    <span uk-pagination-previous></span>
                </a>
            </li>
        <?php endif; ?>
        <?php for ($p = 1; $p <= $members['pages']; $p++): ?>
            <li class="<?= $p === $members['page'] ? 'uk-active' : '' ?>">
                <a href="members.php?page=<?= $p ?>&<?= http_build_query(array_filter($filters)) ?>"><?= $p ?></a>
            </li>
        <?php endfor; ?>
        <?php if ($members['page'] < $members['pages']): ?>
            <li>
                <a href="members.php?page=<?= $members['page'] + 1 ?>&<?= http_build_query(array_filter($filters)) ?>">
                    <span uk-pagination-next></span>
                </a>
            </li>
        <?php endif; ?>
    </ul>
    <p class="uk-text-center uk-text-small uk-text-muted">
        Visualizzando
        <?= (($members['page'] - 1) * $members['per_page']) + 1 ?>–<?= min($members['page'] * $members['per_page'], $members['total']) ?>
        di <?= (int) $members['total'] ?> soci
    </p>
    <?php endif; ?>

    <?php endif; ?>

    <?php
    return (string) ob_get_clean();
})();

require __DIR__ . '/layout.php';
