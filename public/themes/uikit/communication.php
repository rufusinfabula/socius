<?php
$e = fn($v) => htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8');

$comm       = $communication ?? [];
$recipients = $recipients    ?? [];
$isStaff    = $isStaff       ?? false;

$status = (string) ($comm['status'] ?? 'draft');
$type   = (string) ($comm['type']   ?? 'general');
$format = (string) ($comm['format'] ?? 'text');

$statusColors = [
    'draft' => '#6c757d',
    'ready' => '#fd7e14',
    'sent'  => '#28a745',
];

$typeLabels = [
    'general' => __('communications.type_general'),
    'renewal' => __('communications.type_renewal'),
    'board'   => __('communications.type_board'),
    'direct'  => __('communications.type_direct'),
];

// Recipient counts
$withEmail    = count(array_filter($recipients, fn($r) => !empty(trim((string)($r['email']??'')))));
$withoutEmail = count($recipients) - $withEmail;
$includedCount = count(array_filter($recipients, fn($r) => (bool)$r['included']));

$content = (function () use (
    $e, $comm, $recipients, $isStaff, $status, $type, $format,
    $statusColors, $typeLabels, $withEmail, $withoutEmail, $includedCount
): string {
    ob_start();
    ?>

    <!-- Breadcrumb -->
    <ul class="uk-breadcrumb uk-margin-small-bottom">
        <li><a href="communications.php"><?= $e(__('communications.communications')) ?></a></li>
        <li><span><?= $e($comm['title'] ?? '') ?></span></li>
    </ul>

    <div class="uk-flex uk-flex-between uk-flex-middle uk-margin-bottom">
        <h2 class="uk-heading-small uk-margin-remove">
            <?= $e($comm['title'] ?? '') ?>
            <span style="display:inline-block; padding:3px 10px; border-radius:4px; font-size:0.55em;
                         vertical-align:middle; background:<?= $e($statusColors[$status] ?? '#999') ?>; color:#fff">
                <?= $e(__('communications.status_' . $status)) ?>
            </span>
        </h2>
    </div>

    <div class="uk-grid uk-grid-medium" uk-grid>

        <!-- BOX COMUNICAZIONE -->
        <div class="uk-width-2-3@m">
            <div class="uk-card uk-card-default uk-card-body uk-border-rounded uk-margin-bottom">
                <h3 class="uk-card-title"><?= $e(__('communications.communication')) ?></h3>

                <table class="uk-table uk-table-small">
                    <tr>
                        <th style="width:160px; color:#999"><?= $e(__('communications.type')) ?></th>
                        <td><?= $e($typeLabels[$type] ?? $type) ?></td>
                    </tr>
                    <?php if (!empty($comm['renewal_period'])): ?>
                    <tr>
                        <th style="color:#999"><?= $e(__('communications.renewal_period')) ?></th>
                        <td><?= $e(__('communications.period_' . $comm['renewal_period'])) ?></td>
                    </tr>
                    <?php endif; ?>
                    <tr>
                        <th style="color:#999"><?= $e(__('communications.subject')) ?></th>
                        <td><strong><?= $e($comm['subject'] ?? '') ?></strong></td>
                    </tr>
                    <tr>
                        <th style="color:#999"><?= $e(__('communications.created_by')) ?></th>
                        <td>
                            <?= $e(trim(($comm['created_by_name'] ?? '') . ' ' . ($comm['created_by_surname'] ?? ''))) ?: '—' ?>
                            &nbsp;
                            <span class="uk-text-small uk-text-muted">
                                <?= $e(format_date((string) ($comm['created_at'] ?? ''))) ?>
                            </span>
                        </td>
                    </tr>
                    <?php if (!empty($comm['sent_at'])): ?>
                    <tr>
                        <th style="color:#999"><?= $e(__('communications.sent_at')) ?></th>
                        <td><?= $e(format_date((string) $comm['sent_at'], true)) ?></td>
                    </tr>
                    <?php endif; ?>
                </table>

                <!-- Body -->
                <div class="uk-margin-top">
                    <?php if ($format === 'markdown'): ?>
                    <div id="comm-body-rendered" style="border:1px solid #e5e5e5; border-radius:4px; padding:16px; background:#fafafa">
                        <?= $e($comm['body_text'] ?? '') ?>
                    </div>
                    <script>
                    document.addEventListener('DOMContentLoaded', function() {
                        if (typeof marked !== 'undefined') {
                            document.getElementById('comm-body-rendered').innerHTML =
                                marked.parse(<?= json_encode((string) ($comm['body_text'] ?? ''), JSON_HEX_TAG) ?>);
                        }
                    });
                    </script>
                    <?php else: ?>
                    <pre style="white-space:pre-wrap; font-family:inherit; font-size:0.9em;
                                border:1px solid #e5e5e5; border-radius:4px; padding:16px; background:#fafafa;
                                margin:0"><?= $e($comm['body_text'] ?? '') ?></pre>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- BOX DESTINATARI + AZIONI -->
        <div class="uk-width-1-3@m">

            <!-- BOX DESTINATARI -->
            <div class="uk-card uk-card-default uk-card-body uk-border-rounded uk-margin-bottom">
                <h3 class="uk-card-title"><?= $e(__('communications.recipients')) ?></h3>

                <p class="uk-text-small uk-text-muted uk-margin-small-bottom">
                    <?= count($recipients) ?> destinatari
                    — <?= $withEmail ?> con email
                    <?php if ($withoutEmail > 0): ?>
                    — <span class="uk-text-warning"><?= $withoutEmail ?> senza email</span>
                    <?php endif; ?>
                    <?php if ($includedCount < count($recipients)): ?>
                    — <span class="uk-text-muted"><?= count($recipients) - $includedCount ?> esclusi</span>
                    <?php endif; ?>
                </p>

                <!-- Export -->
                <?php if (!empty($recipients)): ?>
                <div class="uk-margin-small-bottom" style="display:flex; flex-wrap:wrap; gap:6px">
                    <a href="communication.php?id=<?= (int)($comm['id']??0) ?>&action=export&format=csv"
                       class="uk-button uk-button-default uk-button-small">
                        <?= $e(__('communications.export_csv')) ?>
                    </a>
                    <a href="communication.php?id=<?= (int)($comm['id']??0) ?>&action=export&format=txt"
                       class="uk-button uk-button-default uk-button-small">
                        <?= $e(__('communications.export_txt')) ?>
                    </a>
                    <a href="communication.php?id=<?= (int)($comm['id']??0) ?>&action=export&format=txt_names"
                       class="uk-button uk-button-default uk-button-small">
                        <?= $e(__('communications.export_txt_names')) ?>
                    </a>
                </div>
                <?php endif; ?>

                <?php if (empty($recipients)): ?>
                <p class="uk-text-small uk-text-muted"><?= $e(__('communications.no_recipients')) ?></p>
                <?php else: ?>
                <div class="uk-overflow-auto" style="max-height:350px">
                    <table class="uk-table uk-table-small uk-table-divider" style="font-size:0.82em">
                        <tbody>
                        <?php foreach ($recipients as $r): ?>
                        <tr id="row-<?= (int)$r['member_id'] ?>"
                            style="<?= !(bool)$r['included'] ? 'opacity:0.5' : '' ?>">
                            <td>
                                <span class="badge-member-number" style="font-size:0.78em">
                                    <?= $e(format_member_number((int)($r['member_number']??0))) ?>
                                </span>
                                <br>
                                <strong><?= $e($r['surname'] ?? '') ?> <?= $e($r['name'] ?? '') ?></strong>
                                <br>
                                <span style="color:<?= empty(trim((string)($r['email']??''))) ? '#999' : '#333' ?>; font-size:0.9em">
                                    <?= $e($r['email'] ?? '') ?: '—' ?>
                                </span>
                            </td>
                            <?php if ($isStaff && $status !== 'sent'): ?>
                            <td style="vertical-align:middle; white-space:nowrap">
                                <button type="button"
                                        class="uk-button uk-button-link uk-text-small"
                                        onclick="toggleRecipient(<?= (int)($comm['id']??0) ?>, <?= (int)$r['member_id'] ?>)"
                                        title="<?= (bool)$r['included'] ? 'Escludi' : 'Includi' ?>"
                                        id="toggle-<?= (int)$r['member_id'] ?>">
                                    <?= (bool)$r['included'] ? '✓' : '○' ?>
                                </button>
                            </td>
                            <?php endif; ?>
                        </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php endif; ?>
            </div>

            <!-- BOX AZIONI -->
            <?php if ($isStaff): ?>
            <div class="uk-card uk-card-default uk-card-body uk-border-rounded">
                <h3 class="uk-card-title">Azioni</h3>

                <?php if ($status === 'draft'): ?>
                <a href="communication-edit.php?id=<?= (int)($comm['id']??0) ?>"
                   class="uk-button uk-button-default uk-width-1-1 uk-margin-small-bottom">
                    <?= $e(__('communications.action_edit')) ?>
                </a>
                <form method="post" action="communication.php?id=<?= (int)($comm['id']??0) ?>">
                    <?= csrf_field() ?>
                    <input type="hidden" name="_action" value="mark_ready">
                    <button type="submit" class="uk-button uk-button-primary uk-width-1-1 uk-margin-small-bottom">
                        <?= $e(__('communications.mark_ready')) ?>
                    </button>
                </form>
                <form method="post" action="communication.php?id=<?= (int)($comm['id']??0) ?>"
                      onsubmit="return confirm('<?= $e(__('communications.confirm_delete')) ?>')">
                    <?= csrf_field() ?>
                    <input type="hidden" name="_action" value="delete">
                    <button type="submit" class="uk-button uk-button-danger uk-width-1-1 uk-margin-small-bottom">
                        Elimina bozza
                    </button>
                </form>
                <?php endif; ?>

                <?php if ($status === 'ready'): ?>
                <form method="post" action="communication.php?id=<?= (int)($comm['id']??0) ?>"
                      onsubmit="return confirm('<?= $e(__('communications.confirm_mark_sent')) ?>')">
                    <?= csrf_field() ?>
                    <input type="hidden" name="_action" value="mark_sent">
                    <button type="submit" class="uk-button uk-button-primary uk-width-1-1 uk-margin-small-bottom">
                        <?= $e(__('communications.mark_sent')) ?>
                    </button>
                </form>
                <?php endif; ?>

                <!-- Duplicate — always available -->
                <form method="post" action="communication.php?id=<?= (int)($comm['id']??0) ?>">
                    <?= csrf_field() ?>
                    <input type="hidden" name="_action" value="duplicate">
                    <button type="submit" class="uk-button uk-button-default uk-width-1-1">
                        <?= $e(__('communications.duplicate')) ?>
                    </button>
                </form>
            </div>
            <?php endif; ?>

        </div>
    </div>

    <?php if ($format === 'markdown'): ?>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/marked/9.1.6/marked.min.js"
            integrity="sha512-rqQlOPjRFd+mVhSCqH3nLM3VNWNeRdI1jNy2GUzLFh9TJgLjBGXcb4FDXiCHJ4h8PsEJ3kCkfq8nWkCOAJlg=="
            crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    <?php endif; ?>

    <script>
    function toggleRecipient(commId, memberId) {
        const row    = document.getElementById('row-' + memberId);
        const btn    = document.getElementById('toggle-' + memberId);
        const data   = new FormData();
        data.append('action', 'toggle');
        data.append('comm_id', commId);
        data.append('member_id', memberId);
        data.append('_csrf_token', '<?= htmlspecialchars(csrf_token(), ENT_QUOTES) ?>');

        fetch('api/comm-recipients.php', {method:'POST', body:data})
            .then(r => r.json())
            .then(function(res) {
                if (res.ok) {
                    const isIncluded = btn.textContent.trim() === '✓';
                    btn.textContent = isIncluded ? '○' : '✓';
                    row.style.opacity = isIncluded ? '0.5' : '1';
                }
            });
    }
    </script>

    <?php
    return (string) ob_get_clean();
})();

require __DIR__ . '/layout.php';
