# Refactoring in corso

## Obiettivo
Sostituire il router custom con struttura a file separati e sistema temi multipli.

## Struttura target
Vedi il prompt completo nella conversazione con Claude.ai

## Stato attuale
- Il router custom ha problemi con parametri URL tipo /members/{id}
- I Controller e Views esistono in app/Controllers/ e app/Views/
- I Model in app/Models/ vanno mantenuti identici
- Il Core in app/Core/ va mantenuto (Config, Database, ecc.)

## Cosa fare
1. Creare public/_init.php
2. Creare file flat in public/ (members.php, member.php, ecc.)
3. Creare public/themes/uikit/ con tutti i template
4. Creare public/themes/bootstrap/ e public/themes/tailwind/ placeholder
5. Eliminare app/Controllers/, app/Views/, config/routes.php, app/Core/Router.php
6. Commit: "refactor: replace router with flat file structure and multi-theme system"
