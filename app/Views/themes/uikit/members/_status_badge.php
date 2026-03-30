<?php
// Partial: status badge. Expects $status (string).
// Include via: include __DIR__ . '/_status_badge.php';
$badges = [
    'active'    => ['success', 'members.status_active'],
    'suspended' => ['default', 'members.status_suspended'],
    'expired'   => ['danger',  'members.status_expired'],
    'resigned'  => ['warning', 'members.status_resigned'],
    'deceased'  => ['secondary','members.status_deceased'],
];
$b     = $badges[$status ?? ''] ?? ['default', $status ?? ''];
$color = $b[0];
$label = $b[1];
?><span class="uk-badge uk-badge-<?= e($color) ?>"><?= e(__($label)) ?></span>
