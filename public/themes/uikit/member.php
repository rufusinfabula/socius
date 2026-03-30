<?php
$e = fn($v) => htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8');

$statusUkLabel = [
    'active'    => 'success',
    'suspended' => 'default',
    'expired'   => 'danger',
    'resigned'  => 'warning',
    'deceased'  => 'secondary',
];
$statusLabel = [
    'active'    => 'Attivo',
    'suspended' => 'Sospeso',
    'expired'   => 'Scaduto',
    'resigned'  => 'Dimesso',
    'deceased'  => 'Deceduto',
];
$membershipStatusUkLabel = [
    'pending'   => 'warning',
    'paid'      => 'success',
    'waived'    => 'default',
    'cancelled' => 'danger',
];

$content = (function () use (
    $member, $memberships, $payments, $currentUser, $flashSuccess, $flashError,
    $e, $statusUkLabel, $statusLabel, $membershipStatusUkLabel
): string {
    ob_start();
    $isSuperAdmin = (int) ($currentUser['role_id'] ?? 4) === 1;
    $isStaff      = (int) ($currentUser['role_id'] ?? 4) <= 3;
    $s = $member['status'] ?? 'active';
    ?>

    <?php if (!empty($flashSuccess)): ?>
        <div class="uk-alert-success" uk-alert><a class="uk-alert-close" uk-close></a><p><?= $e($flashSuccess) ?></p></div>
    <?php endif; ?>
    <?php if (!empty($flashError)): ?>
        <div class="uk-alert-danger" uk-alert><a class="uk-alert-close" uk-close></a><p><?= $e($flashError) ?></p></div>
    <?php endif; ?>

    <!-- Breadcrumb -->
    <ul class="uk-breadcrumb uk-margin-small-bottom">
        <li><a href="members.php">Lista Soci</a></li>
        <li><span><?= $e($member['surname'] . ' ' . $member['name']) ?></span></li>
    </ul>

    <!-- Profile header -->
    <div class="uk-card uk-card-default uk-card-body uk-border-rounded uk-margin-bottom">
        <div class="uk-flex uk-flex-between uk-flex-middle">
            <div>
                <h2 class="uk-card-title uk-margin-remove">
                    <?= $e($member['surname'] . ' ' . $member['name']) ?>
                    <span class="uk-label uk-label-<?= $e($statusUkLabel[$s] ?? 'default') ?> uk-margin-small-left">
                        <?= $e($statusLabel[$s] ?? $s) ?>
                    </span>
                </h2>
                <p class="uk-text-muted uk-margin-remove">
                    <code><?= $e($member['membership_number']) ?></code>
                    <?php if (!empty($member['category_name'])): ?>
                        &nbsp;·&nbsp; <?= $e($member['category_name']) ?>
                    <?php endif; ?>
                    &nbsp;·&nbsp; Iscritto dal: <?= $e($member['joined_on'] ?? '—') ?>
                </p>
            </div>
            <div>
                <?php if ($isStaff): ?>
                    <a href="member-edit.php?id=<?= (int) $member['id'] ?>"
                       class="uk-button uk-button-default uk-button-small">
                        <span uk-icon="pencil"></span> Modifica
                    </a>
                <?php endif; ?>
                <?php if ($isSuperAdmin): ?>
                    <a href="member-delete.php?id=<?= (int) $member['id'] ?>"
                       class="uk-button uk-button-danger uk-button-small uk-margin-small-left">
                        <span uk-icon="warning"></span> Elimina
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="uk-grid uk-grid-medium" uk-grid>

        <!-- Personal data -->
        <div class="uk-width-1-2@m">
            <div class="uk-card uk-card-default uk-card-body uk-border-rounded uk-height-1-1">
                <h3 class="uk-card-title">Dati personali</h3>
                <dl class="uk-description-list">
                    <dt>Email</dt>
                    <dd><?= $e($member['email']) ?></dd>
                    <?php if (!empty($member['phone'])): ?>
                    <dt>Telefono</dt>
                    <dd><?= $e($member['phone']) ?></dd>
                    <?php endif; ?>
                    <?php if (!empty($member['fiscal_code'])): ?>
                    <dt>Codice fiscale</dt>
                    <dd><code><?= $e($member['fiscal_code']) ?></code></dd>
                    <?php endif; ?>
                    <?php if (!empty($member['birth_date'])): ?>
                    <dt>Data di nascita</dt>
                    <dd><?= $e($member['birth_date']) ?><?= !empty($member['birth_place']) ? ' — ' . $e($member['birth_place']) : '' ?></dd>
                    <?php endif; ?>
                    <?php if (!empty($member['address'])): ?>
                    <dt>Indirizzo</dt>
                    <dd>
                        <?= $e($member['address']) ?><br>
                        <?= $e(trim(($member['postal_code'] ?? '') . ' ' . ($member['city'] ?? '') . ' ' . ($member['province'] ?? ''))) ?>
                        <?php if (!empty($member['country']) && $member['country'] !== 'IT'): ?>
                            (<?= $e($member['country']) ?>)
                        <?php endif; ?>
                    </dd>
                    <?php endif; ?>
                    <?php if (!empty($member['resigned_on'])): ?>
                    <dt>Data dimissioni</dt>
                    <dd><?= $e($member['resigned_on']) ?></dd>
                    <?php endif; ?>
                </dl>
                <?php if ($isStaff && !empty($member['notes'])): ?>
                    <hr>
                    <p class="uk-text-small uk-text-muted">
                        <strong>Note:</strong><br>
                        <?= nl2br($e($member['notes'])) ?>
                    </p>
                <?php endif; ?>
            </div>
        </div>

        <!-- Memberships -->
        <div class="uk-width-1-2@m">
            <div class="uk-card uk-card-default uk-card-body uk-border-rounded">
                <h3 class="uk-card-title">Storico tessere</h3>
                <?php if (empty($memberships)): ?>
                    <p class="uk-text-muted">Nessuna tessera registrata.</p>
                <?php else: ?>
                <table class="uk-table uk-table-small uk-table-striped">
                    <thead>
                        <tr>
                            <th>Anno</th><th>Quota</th><th>Validità</th><th>Stato</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($memberships as $ms): ?>
                        <tr>
                            <td><?= (int) $ms['year'] ?></td>
                            <td>€ <?= number_format((float) $ms['fee'], 2, ',', '.') ?></td>
                            <td><?= $e(($ms['valid_from'] ?? '') . ' / ' . ($ms['valid_until'] ?? '')) ?></td>
                            <td>
                                <span class="uk-label uk-label-<?= $e($membershipStatusUkLabel[$ms['status']] ?? 'default') ?>">
                                    <?= $e($ms['status']) ?>
                                </span>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <?php endif; ?>
            </div>
        </div>

        <!-- Payments (staff only) -->
        <?php if ($isStaff): ?>
        <div class="uk-width-1-1">
            <div class="uk-card uk-card-default uk-card-body uk-border-rounded">
                <h3 class="uk-card-title">Pagamenti collegati</h3>
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
                            <td><?= $e($pay['status']) ?></td>
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
        <a href="members.php" class="uk-button uk-button-text">← Lista Soci</a>
    </div>

    <?php
    return (string) ob_get_clean();
})();

require __DIR__ . '/layout.php';
