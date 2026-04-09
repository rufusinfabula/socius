<?php
/*
 * Socius - Open Source Association Management System
 * Copyright (C) 2026 Fabio Ranfi
 *
 * Pagina di sincronizzazione in corso.
 * Viene mostrata al primo login del giorno oppure cliccando il tasto sync.
 *
 * Dopo il completamento redirige all'URL passato in ?return=
 * o alla dashboard se non specificato.
 */

declare(strict_types=1);

require_once __DIR__ . '/_init.php';

requireAuth();

// URL di ritorno: accettato solo se inizia con '/' (path assoluto interno).
// Qualsiasi altro valore (vuoto, URL esterno, path relativo) → dashboard.php.
$returnUrl = trim((string) ($_GET['return'] ?? ''));
if ($returnUrl === '' || $returnUrl[0] !== '/') {
    $returnUrl = 'dashboard.php';
}
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Socius — Sincronizzazione</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/uikit@3/dist/css/uikit.min.css">
</head>
<body style="display:flex;align-items:center;justify-content:center;min-height:100vh;background:#f5f5f5">

    <div style="text-align:center;max-width:400px;padding:40px">

        <?php
        // Logo o nome applicazione
        try {
            $db      = \Socius\Core\Database::getInstance();
            $logoRow = $db->fetch("SELECT `value` FROM settings WHERE `key` = 'association.logo_path' LIMIT 1");
            $nameRow = $db->fetch("SELECT `value` FROM settings WHERE `key` = 'association.name' LIMIT 1");
            $logoPath  = ($logoRow && !empty($logoRow['value'])) ? (string) $logoRow['value'] : '';
            $assocName = ($nameRow && !empty($nameRow['value']))
                ? (string) $nameRow['value']
                : (string) \Socius\Core\Config::get('app.name', 'Socius');
        } catch (\Throwable) {
            $logoPath  = '';
            $assocName = (string) \Socius\Core\Config::get('app.name', 'Socius');
        }

        if ($logoPath !== '') {
            echo '<img src="' . htmlspecialchars($logoPath, ENT_QUOTES, 'UTF-8')
               . '" alt="' . htmlspecialchars($assocName, ENT_QUOTES, 'UTF-8')
               . '" style="max-height:60px;max-width:200px;margin-bottom:24px"><br>';
        } else {
            echo '<h2 style="margin-bottom:24px">'
               . htmlspecialchars($assocName, ENT_QUOTES, 'UTF-8')
               . '</h2>';
        }
        ?>

        <p id="sync-message" style="font-size:1.1em;margin-bottom:16px">
            Sincronizzazione in corso...
        </p>

        <div uk-spinner="ratio: 2" id="sync-spinner"></div>

        <p id="sync-detail" style="color:#999;font-size:0.85em;margin-top:20px">
            Aggiornamento status soci
        </p>

    </div>

    <script src="https://cdn.jsdelivr.net/npm/uikit@3/dist/js/uikit.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/uikit@3/dist/js/uikit-icons.min.js"></script>
    <script>
    (function () {
        var returnUrl = <?= json_encode($returnUrl) ?>;

        fetch('sync.php?action=run')
            .then(function (r) { return r.json(); })
            .then(function (data) {
                if (data.ok) {
                    document.getElementById('sync-message').textContent =
                        'Sincronizzazione completata';
                    document.getElementById('sync-detail').textContent =
                        data.updated + ' soci aggiornati in ' + data.duration_ms + 'ms';
                    document.getElementById('sync-spinner').style.display = 'none';
                    setTimeout(function () {
                        window.location.href = returnUrl;
                    }, 1200);
                } else {
                    window.location.href = returnUrl;
                }
            })
            .catch(function () {
                window.location.href = returnUrl;
            });
    })();
    </script>

</body>
</html>
