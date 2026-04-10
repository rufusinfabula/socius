<?php
$e = fn($v) => htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8');

$isEdit        = $isEdit        ?? false;
$comm          = $communication ?? [];
$categories    = $categories    ?? [];
$currentPeriod = $currentPeriod ?? '';
$existingRecs  = $recipients    ?? [];

$heading = $isEdit
    ? __('communications.edit_communication')
    : __('communications.new_communication');

$v = static function (string $field, mixed $default = '') use ($comm): mixed {
    return $comm[$field] ?? $default;
};

$periods = ['open', 'first_reminder', 'second_reminder', 'third_reminder', 'close', 'lapse'];

// Serialize existing recipients for JS initialisation
$existingRecsJs = json_encode(
    array_map(static function (array $r): array {
        return [
            'member_id'     => (int) $r['member_id'],
            'member_number' => format_member_number((int) ($r['member_number'] ?? 0)),
            'name'          => (string) ($r['name'] ?? ''),
            'surname'       => (string) ($r['surname'] ?? ''),
            'email'         => (string) ($r['email'] ?? ''),
            'status'        => (string) ($r['status'] ?? ''),
            'included'      => (bool) $r['included'],
        ];
    }, $existingRecs),
    JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT
);

$formAction = $isEdit
    ? 'communication-edit.php?id=' . (int) $v('id')
    : 'communication-new.php';

$content = (function () use (
    $e, $isEdit, $heading, $comm, $v, $categories, $currentPeriod,
    $periods, $existingRecsJs, $formAction, $error
): string {
    ob_start();
    ?>

    <?php if (!empty($error)): ?>
    <div class="uk-alert-danger" uk-alert>
        <a class="uk-alert-close" uk-close></a>
        <p><?= $e($error) ?></p>
    </div>
    <?php endif; ?>

    <!-- Breadcrumb -->
    <ul class="uk-breadcrumb uk-margin-small-bottom">
        <li><a href="communications.php"><?= $e(__('communications.communications')) ?></a></li>
        <?php if ($isEdit): ?>
        <li>
            <a href="communication.php?id=<?= (int) $v('id') ?>">
                <?= $e(__('communications.communication_detail')) ?>
            </a>
        </li>
        <?php endif; ?>
        <li><span><?= $e($heading) ?></span></li>
    </ul>

    <h2 class="uk-heading-small uk-margin-bottom"><?= $e($heading) ?></h2>

    <form method="post" action="<?= $e($formAction) ?>" id="comm-form">
        <?= csrf_field() ?>

        <div class="uk-grid uk-grid-medium" uk-grid>

            <!-- BOX COMUNICAZIONE -->
            <div class="uk-width-2-3@m">
                <div class="uk-card uk-card-default uk-card-body uk-border-rounded uk-margin-bottom">
                    <h3 class="uk-card-title"><?= $e(__('communications.communication')) ?></h3>

                    <!-- Titolo interno -->
                    <div class="uk-margin">
                        <label class="uk-form-label" for="title">
                            <?= $e(__('communications.title_internal')) ?> <span class="uk-text-danger">*</span>
                        </label>
                        <input type="text" id="title" name="title" class="uk-input"
                               value="<?= $e($v('title')) ?>" required>
                    </div>

                    <!-- Tipo -->
                    <div class="uk-margin">
                        <label class="uk-form-label" for="type">
                            <?= $e(__('communications.type')) ?>
                        </label>
                        <select id="type" name="type" class="uk-select" style="max-width:240px"
                                onchange="toggleRenewalPeriod(this.value)">
                            <?php foreach (['general','renewal','board','direct'] as $tp): ?>
                            <option value="<?= $e($tp) ?>"
                                    <?= (string) $v('type', 'general') === $tp ? 'selected' : '' ?>>
                                <?= $e(__('communications.type_' . $tp)) ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- Periodo rinnovo (visibile solo se tipo = renewal) -->
                    <?php
                    $currentType    = (string) $v('type', 'general');
                    // Pre-select: saved value if available, otherwise current system period
                    $selectedPeriod = (string) $v('renewal_period', '') ?: $currentPeriod;
                    ?>
                    <div id="renewal-period-box"
                         class="uk-margin"
                         <?= $currentType !== 'renewal' ? 'style="display:none"' : '' ?>>
                        <label class="uk-form-label" for="renewal_period">
                            <?= $e(__('communications.renewal_period')) ?>
                        </label>
                        <div class="uk-flex uk-flex-middle" style="gap:10px">
                            <select id="renewal_period" name="renewal_period"
                                    class="uk-select" style="max-width:240px">
                                <option value="">— nessuno —</option>
                                <?php
                                $periods = ['open','first_reminder','second_reminder','third_reminder','close','lapse'];
                                foreach ($periods as $per):
                                ?>
                                <option value="<?= $e($per) ?>"
                                        <?= $selectedPeriod === $per ? 'selected' : '' ?>>
                                    <?= $e(__('communications.period_' . $per)) ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                            <button type="button"
                                    class="uk-button uk-button-default uk-button-small"
                                    onclick="loadTemplate()">
                                <?= $e(__('communications.use_template')) ?>
                            </button>
                        </div>
                    </div>

                    <!-- Oggetto -->
                    <div class="uk-margin">
                        <label class="uk-form-label" for="subject">
                            <?= $e(__('communications.subject')) ?> <span class="uk-text-danger">*</span>
                        </label>
                        <input type="text" id="subject" name="subject" class="uk-input"
                               value="<?= $e($v('subject')) ?>" required>
                    </div>

                    <!-- Formato -->
                    <div class="uk-margin">
                        <label class="uk-form-label"><?= $e(__('communications.body')) ?></label>
                        <div class="uk-margin-small-bottom">
                            <?php $fmt = (string) $v('format', 'text'); ?>
                            <label class="uk-margin-small-right">
                                <input type="radio" name="format" value="text"
                                       <?= $fmt === 'text' ? 'checked' : '' ?>>
                                <?= $e(__('communications.format_text')) ?>
                            </label>
                            <label>
                                <input type="radio" name="format" value="markdown"
                                       <?= $fmt === 'markdown' ? 'checked' : '' ?>>
                                <?= $e(__('communications.format_markdown')) ?>
                            </label>
                        </div>
                        <textarea id="body_text" name="body_text" class="uk-textarea"
                                  rows="10" required
                        ><?= $e($v('body_text')) ?></textarea>
                        <p class="uk-text-small uk-text-muted uk-margin-small-top">
                            <strong><?= $e(__('communications.placeholders_available')) ?>:</strong>
                            [nome] [cognome] [nome_completo] [numero_socio] [numero_tessera]
                            [anno] [data_chiusura] [data_scadenza] [associazione] [email_associazione]
                        </p>
                    </div>

                </div><!-- /box comunicazione -->

                <!-- Submit -->
                <div class="uk-margin-bottom" style="display:flex; gap:12px">
                    <button type="submit" class="uk-button uk-button-primary">
                        <?= $e(__('communications.action_save')) ?>
                    </button>
                    <a href="<?= $isEdit ? 'communication.php?id=' . (int) $v('id') : 'communications.php' ?>"
                       class="uk-button uk-button-text">
                        <?= $e(__('communications.action_cancel')) ?>
                    </a>
                </div>
            </div>

            <!-- BOX DESTINATARI -->
            <div class="uk-width-1-3@m">
                <div class="uk-card uk-card-default uk-card-body uk-border-rounded uk-margin-bottom">
                    <h3 class="uk-card-title"><?= $e(__('communications.recipients')) ?></h3>

                    <!-- Counter -->
                    <p id="recipient-counter" class="uk-text-small uk-text-muted uk-margin-small-bottom">
                        0 destinatari
                    </p>

                    <!-- Accordion panels -->
                    <ul uk-accordion="multiple: true" class="uk-margin-small-bottom">

                        <!-- Panel 1: Per status -->
                        <li>
                            <a class="uk-accordion-title uk-text-small" href="#">
                                <?= $e(__('communications.add_by_status')) ?>
                            </a>
                            <div class="uk-accordion-content">
                                <?php
                                $statuses = ['active','in_renewal','not_renewed','lapsed','suspended','resigned'];
                                foreach ($statuses as $st):
                                ?>
                                <label class="uk-display-block uk-margin-small-bottom">
                                    <input type="checkbox" class="uk-checkbox status-checkbox"
                                           value="<?= $e($st) ?>">
                                    <?= $e(__('members.status_' . $st)) ?>
                                </label>
                                <?php endforeach; ?>
                                <button type="button"
                                        class="uk-button uk-button-default uk-button-small uk-margin-small-top"
                                        onclick="addByStatus()">
                                    <?= $e(__('communications.add_recipients')) ?>
                                </button>
                            </div>
                        </li>

                        <!-- Panel 2: Per categoria -->
                        <?php if (!empty($categories)): ?>
                        <li>
                            <a class="uk-accordion-title uk-text-small" href="#">
                                <?= $e(__('communications.add_by_category')) ?>
                            </a>
                            <div class="uk-accordion-content">
                                <select id="filter-category" class="uk-select uk-form-small uk-margin-small-bottom">
                                    <option value="">— seleziona categoria —</option>
                                    <?php foreach ($categories as $cat): ?>
                                    <option value="<?= (int) $cat['id'] ?>">
                                        <?= $e($cat['label']) ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                                <button type="button"
                                        class="uk-button uk-button-default uk-button-small"
                                        onclick="addByCategory()">
                                    <?= $e(__('communications.add_recipients')) ?>
                                </button>
                            </div>
                        </li>
                        <?php endif; ?>

                        <!-- Panel 3: Solo direttivo -->
                        <li>
                            <a class="uk-accordion-title uk-text-small" href="#">
                                <?= $e(__('communications.add_board')) ?>
                            </a>
                            <div class="uk-accordion-content">
                                <p class="uk-text-small uk-text-muted">
                                    Aggiunge tutti i membri del direttivo come destinatari.
                                </p>
                                <button type="button"
                                        class="uk-button uk-button-default uk-button-small"
                                        onclick="addBoard()">
                                    <?= $e(__('communications.add_board_btn')) ?>
                                </button>
                            </div>
                        </li>

                        <!-- Panel 4: Selezione manuale -->
                        <li>
                            <a class="uk-accordion-title uk-text-small" href="#">
                                <?= $e(__('communications.add_manual')) ?>
                            </a>
                            <div class="uk-accordion-content">
                                <div style="position:relative">
                                    <input type="text"
                                           id="manual-search"
                                           class="uk-input uk-form-small"
                                           placeholder="<?= $e(__('communications.search_member_placeholder')) ?>"
                                           autocomplete="off">
                                    <div id="manual-search-results"
                                         style="position:absolute; z-index:1000; width:100%;
                                                background:#fff; border:1px solid #e5e5e5;
                                                border-radius:4px; max-height:200px;
                                                overflow-y:auto; display:none;
                                                box-shadow:0 4px 12px rgba(0,0,0,0.1)">
                                    </div>
                                </div>
                            </div>
                        </li>

                    </ul><!-- /accordion -->

                    <!-- Recipient list -->
                    <div id="recipient-list-box" style="margin-top:12px">
                        <div id="no-recipients-msg"
                             class="uk-text-small uk-text-muted">
                            <?= $e(__('communications.no_recipients')) ?>
                        </div>
                        <table id="recipient-table"
                               class="uk-table uk-table-small uk-table-divider"
                               style="display:none; font-size:0.82em">
                            <thead>
                                <tr>
                                    <th>Socio</th>
                                    <th>Email</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody id="recipient-tbody"></tbody>
                        </table>
                    </div>

                    <!-- No-email warning -->
                    <div id="no-email-warning"
                         class="uk-alert-warning uk-margin-small-top"
                         uk-alert
                         style="display:none; font-size:0.85em">
                        <span id="no-email-count"></span>
                        <?= $e(__('communications.recipients_no_email')) ?>.
                    </div>

                    <!-- Hidden inputs populated by JS -->
                    <div id="member-ids-container"></div>

                </div><!-- /box destinatari -->
            </div>

        </div><!-- /grid -->

    </form>

    <script>
    // =========================================================================
    // Recipient management
    // =========================================================================
    const recipients = new Map(); // member_id → {member_number, name, surname, email, status}

    // Initialize from existing recipients (edit mode)
    (function() {
        const existing = <?= $existingRecsJs ?>;
        existing.forEach(function(r) {
            recipients.set(r.member_id, r);
        });
        renderRecipients();
    })();

    function renderRecipients() {
        const tbody   = document.getElementById('recipient-tbody');
        const table   = document.getElementById('recipient-table');
        const noMsg   = document.getElementById('no-recipients-msg');
        const counter = document.getElementById('recipient-counter');
        const warning = document.getElementById('no-email-warning');
        const noEmailCount = document.getElementById('no-email-count');
        const container = document.getElementById('member-ids-container');

        // Clear existing hidden inputs
        container.innerHTML = '';

        const arr = Array.from(recipients.values());
        const withEmail    = arr.filter(r => r.email && r.email.trim() !== '').length;
        const withoutEmail = arr.length - withEmail;

        counter.textContent = arr.length + ' destinatari — ' + withEmail + ' con email' +
            (withoutEmail > 0 ? ' — ' + withoutEmail + ' senza email' : '');

        if (arr.length === 0) {
            table.style.display = 'none';
            noMsg.style.display = '';
            warning.style.display = 'none';
        } else {
            table.style.display = '';
            noMsg.style.display = 'none';

            // Render rows
            tbody.innerHTML = arr.map(function(r) {
                return '<tr>'
                    + '<td>'
                    + '<span class="badge-member-number" style="font-size:0.8em">' + escHtml(r.member_number) + '</span>'
                    + ' <strong>' + escHtml(r.surname) + ' ' + escHtml(r.name) + '</strong>'
                    + '</td>'
                    + '<td style="color:' + (r.email ? '#333' : '#999') + '">'
                    + (r.email ? escHtml(r.email) : '—')
                    + '</td>'
                    + '<td>'
                    + '<button type="button" class="uk-button uk-button-link uk-text-danger"'
                    + ' style="font-size:0.8em" onclick="removeRecipient(' + r.member_id + ')">✕</button>'
                    + '</td>'
                    + '</tr>';
            }).join('');

            // Re-create hidden inputs
            arr.forEach(function(r) {
                const inp = document.createElement('input');
                inp.type  = 'hidden';
                inp.name  = 'member_ids[]';
                inp.value = r.member_id;
                container.appendChild(inp);
            });

            // No-email warning
            if (withoutEmail > 0) {
                noEmailCount.textContent = withoutEmail + ' soci';
                warning.style.display = '';
            } else {
                warning.style.display = 'none';
            }
        }
    }

    function addMembers(members) {
        let added = 0;
        members.forEach(function(m) {
            if (!recipients.has(m.id)) {
                recipients.set(m.id, {
                    member_id:     m.id,
                    member_number: m.member_number,
                    name:          m.name,
                    surname:       m.surname,
                    email:         m.email || '',
                    status:        m.status,
                });
                added++;
            }
        });
        if (added > 0) renderRecipients();
        return added;
    }

    function removeRecipient(memberId) {
        recipients.delete(memberId);
        renderRecipients();
    }

    function fetchMembers(params, callback) {
        const qs = Object.entries(params).flatMap(([k, v]) =>
            Array.isArray(v) ? v.map(vi => encodeURIComponent(k) + '=' + encodeURIComponent(vi))
                             : [encodeURIComponent(k) + '=' + encodeURIComponent(v)]
        ).join('&');
        fetch('api/members-list.php?' + qs + '&per_page=500')
            .then(r => r.json())
            .then(data => callback(data.members || []))
            .catch(() => UIkit.notification('Errore caricamento soci.', {status:'danger'}));
    }

    function addByStatus() {
        const checked = Array.from(
            document.querySelectorAll('.status-checkbox:checked')
        ).map(el => el.value);
        if (checked.length === 0) {
            UIkit.notification('Seleziona almeno uno status.', {status:'warning'});
            return;
        }
        fetchMembers({'statuses[]': checked}, function(members) {
            const added = addMembers(members);
            UIkit.notification(added + ' destinatari aggiunti.', {status:'success', timeout:2000});
        });
    }

    function addByCategory() {
        const catId = document.getElementById('filter-category').value;
        if (!catId) {
            UIkit.notification('Seleziona una categoria.', {status:'warning'});
            return;
        }
        fetchMembers({category_id: catId}, function(members) {
            const added = addMembers(members);
            UIkit.notification(added + ' destinatari aggiunti.', {status:'success', timeout:2000});
        });
    }

    function addBoard() {
        fetchMembers({board: '1'}, function(members) {
            const added = addMembers(members);
            UIkit.notification(added + ' destinatari aggiunti.', {status:'success', timeout:2000});
        });
    }

    // Manual search
    (function() {
        const input   = document.getElementById('manual-search');
        const results = document.getElementById('manual-search-results');
        let timeout   = null;

        input.addEventListener('input', function() {
            clearTimeout(timeout);
            const q = this.value.trim();
            if (q.length < 2) { results.style.display = 'none'; return; }
            timeout = setTimeout(function() {
                fetch('api/members-search.php?q=' + encodeURIComponent(q) + '&limit=10')
                    .then(r => r.json())
                    .then(data => {
                        if (!data.members || data.members.length === 0) {
                            results.innerHTML = '<div style="padding:10px; color:#999">Nessun socio trovato.</div>';
                            results.style.display = 'block';
                            return;
                        }
                        results.innerHTML = data.members.map(m =>
                            '<div style="padding:9px 12px; cursor:pointer; border-bottom:1px solid #f0f0f0"'
                            + ' onmouseover="this.style.background=\'#f5f5f5\'"'
                            + ' onmouseout="this.style.background=\'\'"'
                            + ' onclick="manualAdd(' + JSON.stringify(m) + ')">'
                            + '<strong>' + escHtml(m.surname) + ' ' + escHtml(m.name) + '</strong>'
                            + ' <span class="badge-member-number" style="font-size:0.8em">' + escHtml(m.member_number) + '</span>'
                            + ' <span style="color:#999; font-size:0.85em">' + escHtml(m.status_label) + '</span>'
                            + '</div>'
                        ).join('');
                        results.style.display = 'block';
                    });
            }, 300);
        });

        document.addEventListener('click', function(e) {
            if (!e.target.closest('#manual-search') && !e.target.closest('#manual-search-results')) {
                results.style.display = 'none';
            }
        });
    })();

    function manualAdd(m) {
        const added = addMembers([m]);
        document.getElementById('manual-search').value = '';
        document.getElementById('manual-search-results').style.display = 'none';
        if (added === 0) {
            UIkit.notification('Socio già presente.', {status:'warning', timeout:1500});
        }
    }

    function toggleRenewalPeriod(type) {
        const box = document.getElementById('renewal-period-box');
        box.style.display = (type === 'renewal') ? '' : 'none';
        if (type === 'renewal') {
            var periodSelect = document.getElementById('renewal_period');
            if (periodSelect && periodSelect.value === '') {
                periodSelect.value = '<?= $e($currentPeriod) ?>';
            }
        }
    }

    function loadTemplate() {
        const period = document.getElementById('renewal_period').value;
        if (!period) {
            UIkit.notification('Seleziona prima un periodo.', {status:'warning'});
            return;
        }
        fetch('api/comm-template.php?period=' + encodeURIComponent(period))
            .then(r => r.json())
            .then(data => {
                if (data.subject) document.getElementById('subject').value   = data.subject;
                if (data.body)    document.getElementById('body_text').value = data.body;
            })
            .catch(() => UIkit.notification('Errore caricamento template.', {status:'danger'}));
    }

    function escHtml(str) {
        return String(str).replace(/&/g,'&amp;').replace(/</g,'&lt;')
                          .replace(/>/g,'&gt;').replace(/"/g,'&quot;');
    }
    </script>

    <?php
    return (string) ob_get_clean();
})();

require __DIR__ . '/layout.php';
