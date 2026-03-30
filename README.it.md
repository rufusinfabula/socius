# Socius

🇬🇧 [Read in English](README.md)

**Sistema open source per la gestione di associazioni — installabile come WordPress.**

Socius è un'applicazione web per la gestione di associazioni e organizzazioni con soci. Progettato per girare su qualsiasi hosting PHP standard, senza requisiti particolari.

## Funzionalità

- Anagrafica soci con numero socio permanente e tessera annuale
- Gestione rinnovi con date configurabili e reminder automatici
- Pagamenti online via PayPal e Satispay (riconciliazione via webhook)
- Pagina profilo socio (visibile agli admin e al socio stesso)
- Gestione eventi con landing page pubbliche
- Verbali di assemblee e direttivi (semi-automatici, esportazione PDF/DOCX)
- Multi-utente con ruoli: super admin, admin, segreteria, socio
- Gestione consensi GDPR
- Log di audit completo
- Interfaccia multilingua (italiano e inglese inclusi)
- Supporto multi-tema (UIkit di default, Bootstrap e Tailwind pronti)
- Importazione soci da CSV/Excel
- Notifiche automatiche via email e WhatsApp

## Requisiti

Identici a WordPress:
- PHP 8.1+
- MySQL 8.0+ o MariaDB 10.6+
- Qualsiasi hosting condiviso standard

## Installazione

1. Scarica l'ultima release dalla pagina Releases
2. Carica i file nella document root del tuo server
3. Visita tuo-dominio.it/install e segui il wizard guidato
4. Rimuovi o blocca la cartella /install dopo la configurazione

## Struttura URL

Socius funziona immediatamente su qualsiasi hosting senza configurazione server:

```
tuo-dominio.it/members.php
tuo-dominio.it/member.php?id=1
tuo-dominio.it/member-edit.php?id=1
```

Non richiede mod_rewrite o regole Nginx.

## Temi

Il tema attivo è configurabile dal pannello impostazioni del back-end.

| Tema | Stato |
|---|---|
| UIkit 3 | Default |
| Bootstrap 5 | Placeholder — contributi benvenuti |
| Tailwind CSS | Placeholder — contributi benvenuti |

Per creare un nuovo tema copia `public/themes/uikit/`, rinominala
e sostituisci l'HTML con il tuo framework.

## Lingue

Italiano e inglese sono inclusi. Per aggiungere una nuova lingua:
1. Copia `lang/en/`
2. Rinomina la cartella con il codice ISO (es. `lang/de/`)
3. Traduci i valori in ogni file PHP
4. La lingua appare automaticamente nel selettore del back-end

## Contribuire

- Traduci l'interfaccia in una nuova lingua
- Costruisci un nuovo tema (Bootstrap, Tailwind o altro)
- Segnala bug tramite Issues
- Invia pull request

Qualsiasi versione modificata distribuita ad altri deve essere
rilasciata anch'essa sotto GPL v3.

## Licenza

GNU General Public License v3.0
