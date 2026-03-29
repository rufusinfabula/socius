<?php
/*
 * Socius - Open Source Association Management System
 * Copyright (C) 2026 Fabio Ranfi
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 */
/**
 * Main HTML layout.
 *
 * @todo Implement full HTML5 shell with navigation, sidebar, flash messages,
 *       CSRF meta tag, and asset versioning.
 *
 * @var string $title
 * @var string $content
 */
?>
<!DOCTYPE html>
<html lang="<?= htmlspecialchars($locale ?? 'it') ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($title ?? 'Socius') ?></title>
</head>
<body>
<!-- placeholder layout -->
<?= $content ?? '' ?>
</body>
</html>
