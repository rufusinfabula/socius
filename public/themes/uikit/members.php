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

    <!-- Filters — the search field also drives the live search below -->
    <form method="get" action="members.php" class="uk-form-small uk-margin-bottom" id="members-filter-form">
        <div class="uk-grid-small uk-flex-middle" uk-grid>
            <div class="uk-width-expand@s">
                <input class="uk-input uk-form-small" type="text"
                       id="members-search-input"
                       name="search"
                       value="<?= $e($filters['search']) ?>"
                       placeholder="Cerca per nome, cognome, email, numero tessera…"
                       autocomplete="off">
            </div>
            <div class="uk-width-auto">
                <select class="uk-select uk-form-small" name="status" id="members-status-filter">
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
                <select class="uk-select uk-form-small" name="category" id="members-category-filter">
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

    <!-- Table — initial content from PHP; replaced by live search when typing -->
    <div id="members-table-wrapper">
    <?php if (empty($members['items'])): ?>
        <p class="uk-text-muted">Nessun socio trovato.</p>
    <?php else: ?>

    <div class="uk-overflow-auto">
        <table class="uk-table uk-table-striped uk-table-hover uk-table-small">
            <thead>
                <tr>
                    <th><?= $e(__('members.member_number_label')) ?></th>
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
                    <!-- badge-member-number: permanent M00001 identifier (blue) -->
                    <td>
                        <span class="badge-member-number">
                            <?= $e(format_member_number(isset($m['member_number']) ? (int) $m['member_number'] : null)) ?>
                        </span>
                    </td>
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
    </div><!-- /#members-table-wrapper -->

    <script>
    // Live search: replaces table contents via api/members-search.php when typing
    // Falls back to normal page load when query is cleared
    (function() {
        var searchInput    = document.getElementById('members-search-input');
        var tableWrapper   = document.getElementById('members-table-wrapper');
        var statusFilter   = document.getElementById('members-status-filter');
        var searchTimeout  = null;
        var lastQuery      = '';

        if (!searchInput || !tableWrapper) return;

        var statusLabels = <?= json_encode(array_map(fn($v) => (string) $v, $statusLabel)) ?>;
        var statusColors = <?= json_encode($statusColor) ?>;

        function buildRow(m) {
            var color = statusColors[m.status] || '#1e87f0';
            return '<tr>'
                + '<td><span class="badge-member-number">' + m.member_number + '</span></td>'
                + '<td>' + esc(m.surname) + '</td>'
                + '<td>' + esc(m.name) + '</td>'
                + '<td>' + esc(m.email) + '</td>'
                + '<td><span class="uk-label" style="background:' + color + ';color:#fff">'
                + esc(m.status_label) + '</span></td>'
                + '<td>'
                + '<a href="member.php?id=' + m.id + '" class="uk-icon-button" uk-icon="eye" title="Visualizza"></a>'
                + '<a href="member-edit.php?id=' + m.id + '" class="uk-icon-button uk-margin-small-left" uk-icon="pencil" title="Modifica"></a>'
                + '</td>'
                + '</tr>';
        }

        function esc(s) {
            return String(s)
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;');
        }

        function renderResults(data) {
            var members = data.members || [];
            if (members.length === 0) {
                tableWrapper.innerHTML = '<p class="uk-text-muted">Nessun socio trovato.</p>';
                return;
            }
            var rows = members.map(buildRow).join('');
            tableWrapper.innerHTML = '<div class="uk-overflow-auto">'
                + '<table class="uk-table uk-table-striped uk-table-hover uk-table-small">'
                + '<thead><tr>'
                + '<th>N. Socio</th><th>Cognome</th><th>Nome</th>'
                + '<th>Email</th><th>Stato</th><th>Azioni</th>'
                + '</tr></thead>'
                + '<tbody>' + rows + '</tbody>'
                + '</table></div>'
                + '<p class="uk-text-small uk-text-muted uk-text-center">'
                + data.total + ' risultati per &ldquo;' + esc(data.query) + '&rdquo;</p>';
        }

        function doSearch(q) {
            var status = statusFilter ? statusFilter.value : '';
            var url = 'api/members-search.php?q=' + encodeURIComponent(q)
                + '&limit=50'
                + (status ? '&status=' + encodeURIComponent(status) : '');
            fetch(url)
                .then(function(r) { return r.json(); })
                .then(renderResults)
                .catch(function() {});
        }

        searchInput.addEventListener('input', function() {
            clearTimeout(searchTimeout);
            var q = this.value.trim();
            if (q === lastQuery) return;
            lastQuery = q;
            if (q.length < 2) {
                // Reload page without search param to restore PHP-rendered list
                if (q.length === 0) window.location.href = 'members.php';
                return;
            }
            searchTimeout = setTimeout(function() { doSearch(q); }, 300);
        });
    })();
    </script>

    <?php
    return (string) ob_get_clean();
})();

require __DIR__ . '/layout.php';
