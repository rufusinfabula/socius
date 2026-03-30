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

$membershipStatusUkLabel = [
    'pending'   => 'warning',
    'paid'      => 'success',
    'waived'    => 'default',
    'cancelled' => 'danger',
];

$content = (function () use (
    $member, $memberships, $payments, $currentUser, $flashSuccess, $flashError,
    $e, $statusUkLabel, $statusStyle, $membershipStatusUkLabel
): string {
    ob_start();
    $isSuperAdmin = (int) ($currentUser['role_id'] ?? 4) === 1;
    $isStaff      = (int) ($currentUser['role_id'] ?? 4) <= 3;
    $s = $member['status'] ?? 'active';
    $statusText = __('members.status_' . $s);
    ?>

    <?php if (!empty($flashSuccess)): ?>
        <div class="uk-alert-success" uk-alert><a class="uk-alert-close" uk-close></a><p><?= $e($flashSuccess) ?></p></div>
    <?php endif; ?>
    <?php if (!empty($flashError)): ?>
        <div class="uk-alert-danger" uk-alert><a class="uk-alert-close" uk-close></a><p><?= $e($flashError) ?></p></div>
    <?php endif; ?>

    <!-- Breadcrumb -->
    <ul class="uk-breadcrumb uk-margin-small-bottom">
        <li><a href="members.php"><?= $e(__('members.member_list')) ?></a></li>
        <li><span><?= $e($member['surname'] . ' ' . $member['name']) ?></span></li>
    </ul>

    <!-- Profile header -->
    <div class="uk-card uk-card-default uk-card-body uk-border-rounded uk-margin-bottom">
        <div class="uk-flex uk-flex-between uk-flex-middle">
            <div>
                <h2 class="uk-card-title uk-margin-remove">
                    <?= $e($member['surname'] . ' ' . $member['name']) ?>
                    <?php $ukSuffix = $statusUkLabel[$s] ?? ''; $badgeStyle = $statusStyle[$s] ?? ''; ?>
                    <span class="uk-label<?= $ukSuffix ? ' uk-label-' . $e($ukSuffix) : '' ?> uk-margin-small-left"
                          <?= $badgeStyle ? 'style="' . $e($badgeStyle) . '"' : '' ?>>
                        <?= $e($statusText) ?>
                    </span>
                </h2>
                <p class="uk-text-muted uk-margin-remove">
                    <code><?= $e($member['membership_number'] ?? '—') ?></code>
                    <?php if (!empty($member['category_name'])): ?>
                        &nbsp;·&nbsp; <?= $e($member['category_name']) ?>
                    <?php endif; ?>
                    &nbsp;·&nbsp; <?= $e(__('members.joined_on')) ?>: <?= $e($member['joined_on'] ?? '—') ?>
                </p>
            </div>
            <div>
                <?php if ($isStaff): ?>
                    <a href="member-edit.php?id=<?= (int) $member['id'] ?>"
                       class="uk-button uk-button-default uk-button-small">
                        <span uk-icon="pencil"></span> <?= $e(__('members.edit')) ?>
                    </a>
                <?php endif; ?>
                <?php if ($isSuperAdmin): ?>
                    <a href="member-delete.php?id=<?= (int) $member['id'] ?>"
                       class="uk-button uk-button-danger uk-button-small uk-margin-small-left">
                        <span uk-icon="warning"></span> <?= $e(__('members.emergency_delete')) ?>
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- =====================================================================
         ROW 1: Anagrafica (2/3) + Socio (1/3)
    ====================================================================== -->
    <div class="uk-grid uk-grid-medium uk-margin-bottom" uk-grid>

        <!-- BOX ANAGRAFICA (2/3) -->
        <div class="uk-width-2-3@m">
            <div class="uk-card uk-card-default uk-card-body uk-border-rounded uk-height-1-1">
                <h3 class="uk-card-title">
                    <span uk-icon="icon: user; ratio: 1.1" class="uk-margin-small-right"></span>
                    <?= $e(__('members.box_registry')) ?>
                </h3>

                <div class="uk-grid uk-grid-small" uk-grid>

                    <!-- Cognome + Nome -->
                    <div class="uk-width-1-2@s">
                        <p class="uk-text-muted uk-text-small uk-margin-remove-bottom"><?= $e(__('members.surname')) ?></p>
                        <p class="uk-margin-remove-top"><?= $e($member['surname'] ?? '—') ?></p>
                    </div>
                    <div class="uk-width-1-2@s">
                        <p class="uk-text-muted uk-text-small uk-margin-remove-bottom"><?= $e(__('members.name')) ?></p>
                        <p class="uk-margin-remove-top"><?= $e($member['name'] ?? '—') ?></p>
                    </div>

                    <!-- Sesso -->
                    <div class="uk-width-1-3@s">
                        <p class="uk-text-muted uk-text-small uk-margin-remove-bottom"><?= $e(__('members.sex')) ?></p>
                        <p class="uk-margin-remove-top">
                            <?php
                            $sexMap = ['M' => __('members.sex_m'), 'F' => __('members.sex_f')];
                            echo $e($sexMap[$member['sex'] ?? ''] ?? '—');
                            ?>
                        </p>
                    </div>

                    <!-- Genere -->
                    <div class="uk-width-2-3@s">
                        <p class="uk-text-muted uk-text-small uk-margin-remove-bottom"><?= $e(__('members.gender')) ?></p>
                        <p class="uk-margin-remove-top"><?= $e($member['gender'] ?? '—') ?></p>
                    </div>

                    <!-- Data di nascita -->
                    <div class="uk-width-1-2@s">
                        <p class="uk-text-muted uk-text-small uk-margin-remove-bottom"><?= $e(__('members.birth_date')) ?></p>
                        <p class="uk-margin-remove-top"><?= $e($member['birth_date'] ?? '—') ?></p>
                    </div>

                    <!-- Luogo di nascita -->
                    <div class="uk-width-1-2@s">
                        <p class="uk-text-muted uk-text-small uk-margin-remove-bottom"><?= $e(__('members.birth_place')) ?></p>
                        <p class="uk-margin-remove-top"><?= $e($member['birth_place'] ?? '—') ?></p>
                    </div>

                    <!-- Codice fiscale -->
                    <div class="uk-width-1-1">
                        <p class="uk-text-muted uk-text-small uk-margin-remove-bottom"><?= $e(__('members.fiscal_code')) ?></p>
                        <p class="uk-margin-remove-top">
                            <?php if (!empty($member['fiscal_code'])): ?>
                                <code><?= $e($member['fiscal_code']) ?></code>
                            <?php else: ?>
                                —
                            <?php endif; ?>
                        </p>
                    </div>

                </div><!-- /grid -->
            </div><!-- /card anagrafica -->
        </div><!-- /col 2/3 -->

        <!-- BOX SOCIO (1/3) -->
        <div class="uk-width-1-3@m">
            <div class="uk-card uk-card-default uk-card-body uk-border-rounded uk-height-1-1">
                <h3 class="uk-card-title">
                    <span uk-icon="icon: tag; ratio: 1.1" class="uk-margin-small-right"></span>
                    <?= $e(__('members.box_member')) ?>
                </h3>

                <!-- N. Tessera (prominente) -->
                <div class="uk-margin">
                    <p class="uk-text-muted uk-text-small uk-margin-remove-bottom"><?= $e(__('members.membership_number')) ?></p>
                    <p class="uk-margin-remove-top">
                        <code style="font-size:1.1rem"><?= $e($member['membership_number'] ?? '—') ?></code>
                    </p>
                </div>

                <!-- N. Socio (identificatore permanente — meno prominente) -->
                <?php if (!empty($member['member_number'])): ?>
                <div class="uk-margin uk-margin-small-top">
                    <p class="uk-text-muted uk-text-small uk-margin-remove-bottom"><?= $e(__('members.member_number')) ?></p>
                    <p class="uk-margin-remove-top uk-text-small">
                        <?= (int) $member['member_number'] ?>
                        <span class="uk-text-muted" style="font-size:0.8rem">
                            — <?= $e(__('members.member_number_permanent')) ?>
                        </span>
                    </p>
                </div>
                <?php endif; ?>

                <!-- Stato -->
                <div class="uk-margin">
                    <p class="uk-text-muted uk-text-small uk-margin-remove-bottom"><?= $e(__('members.status')) ?></p>
                    <p class="uk-margin-remove-top">
                        <?php $ukSuffix = $statusUkLabel[$s] ?? ''; $badgeStyle = $statusStyle[$s] ?? ''; ?>
                        <span class="uk-label<?= $ukSuffix ? ' uk-label-' . $e($ukSuffix) : '' ?>"
                              <?= $badgeStyle ? 'style="' . $e($badgeStyle) . '"' : '' ?>>
                            <?= $e($statusText) ?>
                        </span>
                    </p>
                </div>

                <!-- Categoria -->
                <?php if (!empty($member['category_name'])): ?>
                <div class="uk-margin">
                    <p class="uk-text-muted uk-text-small uk-margin-remove-bottom"><?= $e(__('members.category')) ?></p>
                    <p class="uk-margin-remove-top"><?= $e($member['category_name']) ?></p>
                </div>
                <?php endif; ?>

                <!-- Data iscrizione -->
                <div class="uk-margin">
                    <p class="uk-text-muted uk-text-small uk-margin-remove-bottom"><?= $e(__('members.joined_on')) ?></p>
                    <p class="uk-margin-remove-top"><?= $e($member['joined_on'] ?? '—') ?></p>
                </div>

                <!-- Data recesso -->
                <?php if (!empty($member['resigned_on'])): ?>
                <div class="uk-margin">
                    <p class="uk-text-muted uk-text-small uk-margin-remove-bottom"><?= $e(__('members.resigned_on')) ?></p>
                    <p class="uk-margin-remove-top"><?= $e($member['resigned_on']) ?></p>
                </div>
                <?php endif; ?>

                <!-- Note interne (solo staff) -->
                <?php if ($isStaff && !empty($member['notes'])): ?>
                <div class="uk-margin">
                    <hr>
                    <p class="uk-text-muted uk-text-small uk-margin-remove-bottom">
                        <?= $e(__('members.notes')) ?>
                    </p>
                    <p class="uk-margin-remove-top uk-text-small"><?= nl2br($e($member['notes'])) ?></p>
                </div>
                <?php endif; ?>

            </div><!-- /card socio -->
        </div><!-- /col 1/3 -->

    </div><!-- /row 1 -->

    <!-- =====================================================================
         ROW 2: Contatti (larghezza piena)
    ====================================================================== -->
    <div class="uk-card uk-card-default uk-card-body uk-border-rounded uk-margin-bottom">
        <h3 class="uk-card-title">
            <span uk-icon="icon: mail; ratio: 1.1" class="uk-margin-small-right"></span>
            <?= $e(__('members.box_contacts')) ?>
        </h3>

        <div class="uk-grid uk-grid-medium" uk-grid>

            <!-- Colonna sinistra: email, telefono, cellulare -->
            <div class="uk-width-1-2@m">
                <div class="uk-margin">
                    <p class="uk-text-muted uk-text-small uk-margin-remove-bottom"><?= $e(__('members.email')) ?></p>
                    <p class="uk-margin-remove-top">
                        <a href="mailto:<?= $e($member['email'] ?? '') ?>"><?= $e($member['email'] ?? '—') ?></a>
                    </p>
                </div>
                <?php if (!empty($member['phone1'])): ?>
                <div class="uk-margin">
                    <p class="uk-text-muted uk-text-small uk-margin-remove-bottom"><?= $e(__('members.phone1')) ?></p>
                    <p class="uk-margin-remove-top"><?= $e($member['phone1']) ?></p>
                </div>
                <?php endif; ?>
                <?php if (!empty($member['phone2'])): ?>
                <div class="uk-margin">
                    <p class="uk-text-muted uk-text-small uk-margin-remove-bottom"><?= $e(__('members.phone2')) ?></p>
                    <p class="uk-margin-remove-top"><?= $e($member['phone2']) ?></p>
                </div>
                <?php endif; ?>
            </div>

            <!-- Colonna destra: indirizzo -->
            <div class="uk-width-1-2@m">
                <?php if (!empty($member['address'])): ?>
                <div class="uk-margin">
                    <p class="uk-text-muted uk-text-small uk-margin-remove-bottom"><?= $e(__('members.address')) ?></p>
                    <p class="uk-margin-remove-top"><?= $e($member['address']) ?></p>
                </div>
                <?php endif; ?>
                <?php
                $cityLine = trim(
                    ($member['postal_code'] ?? '') . ' ' .
                    ($member['city'] ?? '') . ' ' .
                    ($member['province'] ?? '')
                );
                ?>
                <?php if ($cityLine !== ''): ?>
                <div class="uk-margin">
                    <p class="uk-text-muted uk-text-small uk-margin-remove-bottom"><?= $e(__('members.city')) ?></p>
                    <p class="uk-margin-remove-top"><?= $e($cityLine) ?></p>
                </div>
                <?php endif; ?>
                <?php if (!empty($member['country']) && $member['country'] !== 'IT'): ?>
                <div class="uk-margin">
                    <p class="uk-text-muted uk-text-small uk-margin-remove-bottom"><?= $e(__('members.country')) ?></p>
                    <p class="uk-margin-remove-top"><?= $e($member['country']) ?></p>
                </div>
                <?php endif; ?>
            </div>

        </div><!-- /contacts grid -->
    </div><!-- /box contatti -->

    <!-- =====================================================================
         Storico tessere
    ====================================================================== -->
    <div class="uk-card uk-card-default uk-card-body uk-border-rounded uk-margin-bottom">
        <h3 class="uk-card-title">
            <span uk-icon="icon: bookmark; ratio: 1.1" class="uk-margin-small-right"></span>
            <?= $e(__('members.memberships_history')) ?>
        </h3>
        <?php if (empty($memberships)): ?>
            <p class="uk-text-muted"><?= $e(__('members.no_memberships')) ?></p>
        <?php else: ?>
        <table class="uk-table uk-table-small uk-table-striped uk-table-divider">
            <thead>
                <tr>
                    <th><?= $e(__('members.membership_year')) ?></th>
                    <th><?= $e(__('members.membership_fee')) ?></th>
                    <th><?= $e(__('members.membership_valid')) ?></th>
                    <th><?= $e(__('members.membership_status')) ?></th>
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

    <!-- =====================================================================
         Pagamenti (solo staff)
    ====================================================================== -->
    <?php if ($isStaff): ?>
    <div class="uk-card uk-card-default uk-card-body uk-border-rounded uk-margin-bottom">
        <h3 class="uk-card-title">
            <span uk-icon="icon: credit-card; ratio: 1.1" class="uk-margin-small-right"></span>
            <?= $e(__('members.payments_linked')) ?>
        </h3>
        <?php if (empty($payments)): ?>
            <p class="uk-text-muted"><?= $e(__('members.no_payments')) ?></p>
        <?php else: ?>
        <table class="uk-table uk-table-small uk-table-striped uk-table-divider">
            <thead>
                <tr>
                    <th><?= $e(__('members.payment_date')) ?></th>
                    <th><?= $e(__('members.payment_amount')) ?></th>
                    <th><?= $e(__('members.payment_gateway')) ?></th>
                    <th><?= $e(__('members.payment_status')) ?></th>
                </tr>
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
    <?php endif; ?>

    <div class="uk-margin-top">
        <a href="members.php" class="uk-button uk-button-text">
            ← <?= $e(__('members.back_to_list')) ?>
        </a>
    </div>

    <?php
    return (string) ob_get_clean();
})();

require __DIR__ . '/layout.php';
