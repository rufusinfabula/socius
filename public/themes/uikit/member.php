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
    $member, $memberships, $payments, $boardRoles, $currentUser,
    $e, $statusUkLabel, $statusStyle, $membershipStatusUkLabel
): string {
    ob_start();
    $isSuperAdmin = (int) ($currentUser['role_id'] ?? 4) === 1;
    $isStaff      = (int) ($currentUser['role_id'] ?? 4) <= 3;
    $s = $member['status'] ?? 'active';
    $statusText = __('members.status_' . $s);
    ?>

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
                    &nbsp;·&nbsp; <?= $e(__('members.joined_on')) ?>: <?= $e(format_date($member['joined_on'] ?? '')) ?>
                </p>
            </div>
            <div>
                <?php if ($isStaff): ?>
                    <a href="member-edit.php?id=<?= (int) $member['id'] ?>"
                       class="uk-button uk-button-default uk-button-small">
                        <span uk-icon="pencil"></span> <?= $e(__('members.edit')) ?>
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
                        <p class="uk-margin-remove-top"><?= $e(format_date($member['birth_date'] ?? '')) ?></p>
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

                <!-- Ruolo direttivo attivo (badge in cima) -->
                <?php
                $today = date('Y-m-d');
                $activeRoles = array_filter($boardRoles ?? [], fn($r) =>
                    $r['resigned_on'] === null &&
                    ($r['expires_on'] === null || $r['expires_on'] >= $today)
                );
                ?>
                <?php if (!empty($activeRoles)): ?>
                <div class="uk-margin-small-bottom">
                    <?php foreach ($activeRoles as $ar): ?>
                        <span class="uk-label <?= (bool) $ar['is_board_member'] ? 'uk-label-primary' : '' ?>"
                              style="<?= (bool) $ar['is_board_member'] ? '' : 'background:#999; color:#fff' ?>">
                            <?= $e($ar['role_label']) ?>
                        </span>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>

                <!-- N. Tessera + N. Socio (stesso peso visivo, due colonne) -->
                <?php $isActive = in_array($s, ['active', 'in_renewal'], true); ?>
                <div class="uk-grid uk-grid-small uk-margin" uk-grid>

                    <div class="uk-width-1-2">
                        <p class="uk-text-muted uk-text-small uk-margin-remove-bottom"><?= $e(__('members.membership_number')) ?></p>
                        <p class="uk-margin-remove"><code><?= $e($member['membership_number'] ?? '—') ?></code></p>
                        <p class="uk-text-muted uk-margin-remove" style="font-size:0.75rem">
                            <?= $isActive ? $e(__('members.membership_active')) : $e(__('members.membership_not_active')) ?>
                        </p>
                    </div>

                    <div class="uk-width-1-2">
                        <p class="uk-text-muted uk-text-small uk-margin-remove-bottom"><?= $e(__('members.member_number')) ?></p>
                        <p class="uk-margin-remove"><code><?= isset($member['member_number']) ? (int) $member['member_number'] : '—' ?></code></p>
                        <p class="uk-text-muted uk-margin-remove" style="font-size:0.75rem">
                            <?= $e(__('members.member_number_permanent')) ?>
                        </p>
                    </div>

                </div>

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

                <!-- Data iscrizione -->
                <div class="uk-margin">
                    <p class="uk-text-muted uk-text-small uk-margin-remove-bottom"><?= $e(__('members.joined_on')) ?></p>
                    <p class="uk-margin-remove-top"><?= $e(format_date($member['joined_on'] ?? '')) ?></p>
                </div>

                <!-- Categoria -->
                <?php if (!empty($member['category_name'])): ?>
                <div class="uk-margin">
                    <p class="uk-text-muted uk-text-small uk-margin-remove-bottom"><?= $e(__('members.category')) ?></p>
                    <p class="uk-margin-remove-top"><?= $e($member['category_name']) ?></p>
                </div>
                <?php endif; ?>

                <!-- Data recesso -->
                <?php if (!empty($member['resigned_on'])): ?>
                <div class="uk-margin">
                    <p class="uk-text-muted uk-text-small uk-margin-remove-bottom"><?= $e(__('members.resigned_on')) ?></p>
                    <p class="uk-margin-remove-top"><?= $e(format_date($member['resigned_on'])) ?></p>
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
        <div class="uk-flex uk-flex-between uk-flex-middle uk-margin-bottom">
            <h3 class="uk-card-title uk-margin-remove">
                <span uk-icon="icon: bookmark; ratio: 1.1" class="uk-margin-small-right"></span>
                <?= $e(__('memberships.member_history')) ?>
            </h3>
            <?php if ($isStaff): ?>
            <a href="membership-new.php?member_id=<?= (int) $member['id'] ?>"
               class="uk-button uk-button-primary uk-button-small">
                <span uk-icon="plus"></span> <?= $e(__('memberships.new_for_member')) ?>
            </a>
            <?php endif; ?>
        </div>
        <?php if (empty($memberships)): ?>
            <p class="uk-text-muted"><?= $e(__('members.no_memberships')) ?></p>
        <?php else: ?>
        <table class="uk-table uk-table-small uk-table-striped uk-table-divider">
            <thead>
                <tr>
                    <th><?= $e(__('members.membership_year')) ?></th>
                    <th><?= $e(__('memberships.col_tessera')) ?></th>
                    <th><?= $e(__('memberships.col_category')) ?></th>
                    <th><?= $e(__('members.membership_fee')) ?></th>
                    <th><?= $e(__('members.membership_status')) ?></th>
                    <th><?= $e(__('members.actions')) ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($memberships as $ms): ?>
                <tr>
                    <td><?= (int) $ms['year'] ?></td>
                    <td><code><?= $e($member['membership_number'] ?? '—') ?></code></td>
                    <td><?= $e($ms['category_name'] ?? '—') ?></td>
                    <td>€&nbsp;<?= number_format((float) $ms['fee'], 2, ',', '.') ?></td>
                    <td>
                        <span class="uk-label uk-label-<?= $e($membershipStatusUkLabel[$ms['status']] ?? 'default') ?>">
                            <?= $e(__('memberships.status_' . ($ms['status'] ?? 'pending'))) ?>
                        </span>
                    </td>
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
        <?php endif; ?>
    </div>

    <!-- =====================================================================
         Ruoli nel direttivo (se presenti)
    ====================================================================== -->
    <?php if (!empty($boardRoles)): ?>
    <div class="uk-card uk-card-default uk-card-body uk-border-rounded uk-margin-bottom">
        <h3 class="uk-card-title">
            <span uk-icon="icon: star; ratio: 1.1" class="uk-margin-small-right"></span>
            <?= $e(__('members.board_roles')) ?>
        </h3>

        <?php
        $today = date('Y-m-d');
        $currentRoles = array_filter($boardRoles, fn($r) =>
            $r['resigned_on'] === null &&
            ($r['expires_on'] === null || $r['expires_on'] >= $today)
        );
        $pastRoles = array_filter($boardRoles, fn($r) =>
            $r['resigned_on'] !== null ||
            ($r['expires_on'] !== null && $r['expires_on'] < $today)
        );
        ?>

        <?php if (!empty($currentRoles)): ?>
        <ul class="uk-list uk-list-divider uk-margin-small-bottom">
            <?php foreach ($currentRoles as $br): ?>
            <li class="uk-flex uk-flex-between uk-flex-middle">
                <div>
                    <span class="uk-label <?= (bool) $br['is_board_member'] ? 'uk-label-primary' : '' ?>"
                          style="<?= (bool) $br['is_board_member'] ? '' : 'background:#999; color:#fff' ?>">
                        <?= $e(__('members.board_current')) ?>
                    </span>
                    <strong class="uk-margin-small-left"><?= $e($br['role_label']) ?></strong>
                    <?php if ((bool) $br['can_sign']): ?>
                        <span uk-icon="icon: pencil" class="uk-margin-small-left uk-text-muted" title="Firma atti"></span>
                    <?php endif; ?>
                </div>
                <div class="uk-text-small uk-text-muted">
                    <?= $e(__('members.board_elected_on')) ?> <?= $e(format_date($br['elected_on'])) ?>
                    <?php if (!empty($br['expires_on'])): ?>
                        &nbsp;–&nbsp; <?= $e(__('members.board_expires_on')) ?> <?= $e(format_date($br['expires_on'])) ?>
                    <?php endif; ?>
                </div>
            </li>
            <?php endforeach; ?>
        </ul>
        <?php endif; ?>

        <?php if (!empty($pastRoles)): ?>
        <details <?= empty($currentRoles) ? 'open' : '' ?>>
            <summary class="uk-text-small uk-text-muted" style="cursor:pointer">
                <?= $e(__('members.board_past')) ?> (<?= count($pastRoles) ?>)
            </summary>
            <ul class="uk-list uk-list-divider uk-margin-small-top">
                <?php foreach ($pastRoles as $br): ?>
                <li class="uk-text-small">
                    <span class="uk-text-muted"><?= $e(__('members.board_past')) ?></span>
                    <strong class="uk-margin-small-left"><?= $e($br['role_label']) ?></strong>
                    <span class="uk-text-muted uk-margin-small-left">
                        <?= $e(format_date($br['elected_on'])) ?>
                        <?php if (!empty($br['resigned_on'])): ?>
                            &nbsp;–&nbsp; <?= $e(__('members.board_resigned_on')) ?> <?= $e(format_date($br['resigned_on'])) ?>
                        <?php elseif (!empty($br['expires_on'])): ?>
                            &nbsp;–&nbsp; <?= $e(format_date($br['expires_on'])) ?>
                        <?php endif; ?>
                    </span>
                </li>
                <?php endforeach; ?>
            </ul>
        </details>
        <?php endif; ?>

    </div>
    <?php endif; ?>

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
                    <td><?= $e(format_date($pay['paid_at'] ?? '', true)) ?></td>
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

    <!-- =====================================================================
         Cancellazione emergenza (solo super_admin)
    ====================================================================== -->
    <?php if ($isSuperAdmin): ?>
    <div class="uk-card uk-card-body uk-border-rounded uk-margin-top"
         style="border: 2px solid #f0506e; background: #fff8f8">
        <h3 class="uk-card-title" style="color:#bf2222">
            <span uk-icon="icon: warning; ratio: 1.1" class="uk-margin-small-right"></span>
            <?= $e(__('members.emergency_box_title')) ?>
        </h3>
        <p class="uk-text-small uk-text-muted"><?= $e(__('members.emergency_box_desc')) ?></p>
        <a href="member-delete.php?id=<?= (int) $member['id'] ?>"
           class="uk-button uk-button-danger uk-button-small">
            <span uk-icon="trash"></span> <?= $e(__('members.emergency_delete')) ?>
        </a>
    </div>
    <?php endif; ?>

    <?php
    return (string) ob_get_clean();
})();

require __DIR__ . '/layout.php';
