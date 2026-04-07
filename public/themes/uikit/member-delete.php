<?php
$e = fn($v) => htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8');

$membershipStatusLabel = [
    'pending'   => __('memberships.status_pending'),
    'paid'      => __('memberships.status_paid'),
    'waived'    => __('memberships.status_waived'),
    'cancelled' => __('memberships.status_cancelled'),
];

$paymentStatusLabel = [
    'completed' => __('memberships.status_paid'),
    'paid'      => __('memberships.status_paid'),
    'pending'   => __('memberships.status_pending'),
    'failed'    => __('memberships.status_cancelled'),
    'refunded'  => 'Rimborsato',
];

$content = (function () use ($member, $memberships, $payments, $error, $e, $membershipStatusLabel, $paymentStatusLabel): string {
    ob_start();
    ?>

    <!-- Warning banner -->
    <div class="uk-alert-danger uk-border-rounded uk-padding-small uk-margin-bottom" uk-alert>
        <div class="uk-flex uk-flex-middle">
            <span uk-icon="icon: warning; ratio: 2" class="uk-margin-right"></span>
            <div>
                <strong>ATTENZIONE: Operazione irreversibile!</strong><br>
                Questa operazione eliminerà definitivamente il socio e tutte le sue tessere.
            </div>
        </div>
    </div>

    <?php if (!empty($error)): ?>
        <div class="uk-alert-danger" uk-alert>
            <a class="uk-alert-close" uk-close></a>
            <p><?= $e($error) ?></p>
        </div>
    <?php endif; ?>

    <h1 class="uk-heading-small">Elimina Socio (emergenza)</h1>

    <div class="uk-grid uk-grid-medium" uk-grid>

        <!-- Member summary -->
        <div class="uk-width-1-2@m">
            <div class="uk-card uk-card-default uk-card-body uk-border-rounded">
                <h3 class="uk-card-title"><?= $e($member['surname'] . ' ' . $member['name']) ?></h3>
                <dl class="uk-description-list uk-description-list-divider">
                    <dt>N. Tessera</dt>
                    <dd><code><?= $e($member['membership_number']) ?></code></dd>
                    <dt>Email</dt>
                    <dd><?= $e($member['email']) ?></dd>
                    <dt>Stato</dt>
                    <dd><?= $e(__('members.status_' . ($member['status'] ?? 'active'))) ?></dd>
                    <dt>Iscritto dal</dt>
                    <dd><?= $e($member['joined_on'] ?? '—') ?></dd>
                </dl>
            </div>
        </div>

        <!-- Tessere da eliminare -->
        <div class="uk-width-1-2@m">
            <div class="uk-card uk-card-body uk-border-rounded" style="border:2px solid #f0506e">
                <h3 class="uk-card-title uk-text-danger">Tessere che verranno eliminate</h3>
                <?php if (empty($memberships)): ?>
                    <p class="uk-text-muted">Nessuna tessera registrata.</p>
                <?php else: ?>
                <ul class="uk-list uk-list-divider">
                    <?php foreach ($memberships as $ms): ?>
                    <li>
                        <strong><?= (int) $ms['year'] ?></strong>
                        — <?= $e($ms['category_name'] ?? '—') ?>
                        — €<?= number_format((float) $ms['fee'], 2) ?>
                        — <em><?= $e($membershipStatusLabel[$ms['status']] ?? $ms['status']) ?></em>
                    </li>
                    <?php endforeach; ?>
                </ul>
                <?php endif; ?>
            </div>
        </div>

        <!-- Pagamenti mantenuti -->
        <div class="uk-width-1-1">
            <div class="uk-card uk-card-body uk-border-rounded" style="border:2px solid #32d296">
                <h3 class="uk-card-title uk-text-success"><?= $e(__('members.payments_kept_title')) ?></h3>
                <?php if (empty($payments)): ?>
                    <p class="uk-text-muted">Nessun pagamento registrato.</p>
                <?php else: ?>
                <table class="uk-table uk-table-small uk-table-striped">
                    <thead>
                        <tr><th>Data</th><th>Importo</th><th>Gateway</th><th>Stato</th></tr>
                    </thead>
                    <tbody>
                        <?php foreach ($payments as $pay): ?>
                        <tr>
                            <td><?= $e($pay['paid_at']) ?></td>
                            <td>€ <?= number_format((float) $pay['amount'], 2, ',', '.') ?></td>
                            <td><?= $e($pay['gateway']) ?></td>
                            <td><?= $e($paymentStatusLabel[$pay['status'] ?? ''] ?? ($pay['status'] ?? '')) ?></td>
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

        <form method="POST" action="member-delete.php?id=<?= (int) $member['id'] ?>" id="delete-form">
            <?= csrf_field() ?>

            <div class="uk-margin">
                <p class="uk-text-bold">Vuoi liberare il numero di tessera per un futuro utilizzo?</p>
                <label>
                    <input class="uk-radio" type="radio" name="free_number" value="1" checked>
                    Sì, libera il numero <?= $e($member['membership_number']) ?>
                </label><br>
                <label class="uk-margin-small-top">
                    <input class="uk-radio" type="radio" name="free_number" value="0">
                    No, mantieni <?= $e($member['membership_number']) ?> come riservato
                </label>
            </div>

            <div class="uk-margin">
                <label class="uk-form-label" for="confirm_word">
                    <strong>Digita DELETE per confermare la cancellazione definitiva:</strong>
                </label>
                <input class="uk-input" type="text" id="confirm_word" name="confirm_word"
                       placeholder="DELETE" autocomplete="off" style="max-width: 300px">
            </div>

            <div class="uk-flex uk-flex-between uk-margin-top">
                <a href="member.php?id=<?= (int) $member['id'] ?>" class="uk-button uk-button-default">
                    ← Torna al profilo
                </a>
                <button id="delete-btn" class="uk-button uk-button-danger" type="submit" disabled>
                    <span uk-icon="trash"></span> Elimina definitivamente
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

require __DIR__ . '/layout.php';
