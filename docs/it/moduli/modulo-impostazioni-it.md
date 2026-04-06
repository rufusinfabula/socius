# Modulo Impostazioni

**Socius v0.2.x — Documentazione del modulo**

---

## Panoramica

Il modulo Impostazioni è il centro di configurazione di Socius. Permette agli amministratori di configurare ogni aspetto del sistema dal back-end senza toccare nessun file. Tutte le impostazioni sono salvate nella tabella `settings` come coppie chiave-valore e vengono applicate a tutto il sistema in tempo reale.

---

## Accesso

Solo gli utenti con ruolo `admin` (role_id ≤ 2) possono accedere al pannello impostazioni. Il ruolo `super_admin` ha accesso completo inclusa la sezione Numero Socio.

URL: `settings.php` oppure `settings.php?tab=nome_sezione`

---

## Sezioni

Il pannello è organizzato in sette tab:

| Tab | Parametro URL | Descrizione |
|---|---|---|
| Associazione | `?tab=association` | Nome, CF, P.IVA, indirizzo, logo |
| Anno Sociale | `?tab=social_year` | Date del ciclo di rinnovo |
| Categorie Soci | `?tab=categories` | Gestione categorie e quote annuali |
| Ruoli Direttivo | `?tab=board_roles` | Gestione ruoli del direttivo |
| Interfaccia | `?tab=interface` | Tema, lingua, formato data, fuso orario |
| Email | `?tab=email` | Configurazione SMTP e test |
| Numero Socio | `?tab=member_number` | Contatore numeri progressivi |

---

## Dettaglio sezioni

### Associazione

Contiene i dati identificativi dell'associazione. Tutti i campi sono opzionali — il sistema funziona senza di essi, ma vengono usati nelle comunicazioni, nelle intestazioni dei verbali e nelle ricevute.

| Chiave setting | Descrizione |
|---|---|
| `association.name` | Nome dell'associazione |
| `association.fiscal_code` | Codice fiscale |
| `association.vat_number` | Partita IVA — solo se applicabile |
| `association.address` | Sede legale |
| `association.city` | Città |
| `association.postal_code` | CAP |
| `association.province` | Provincia (2 lettere) |
| `association.country` | Codice paese ISO (default: IT) |
| `association.email` | Email ufficiale |
| `association.phone` | Telefono |
| `association.website` | Sito web |
| `association.logo_path` | Percorso relativo del logo caricato |

**Caricamento logo**: accetta PNG, JPG, SVG fino a 2MB. Salvato come `public/storage/uploads/logo/logo.{ext}`. La navbar mostra il logo se presente, altrimenti mostra il nome dell'associazione come testo.

Per rimuovere il logo, spuntare la casella "Rimuovi logo" prima di salvare.

---

### Anno Sociale

Configura le date del ciclo di rinnovo annuale. Tutte le date sono salvate come `MM-GG` (mese-giorno senza anno) — il sistema applica l'anno corrente in fase di elaborazione.

| Chiave setting | Default | Descrizione |
|---|---|---|
| `renewal.date_open` | `11-15` | Apertura periodo rinnovi |
| `renewal.date_first_reminder` | `02-15` | Prima comunicazione di rinnovo |
| `renewal.date_second_reminder` | `03-15` | Secondo sollecito |
| `renewal.date_third_reminder` | `04-15` | Terzo sollecito / ultimo avviso |
| `renewal.date_close` | `04-15` | Chiusura periodo rinnovi |
| `renewal.date_lapse` | `12-31` | Decadenza automatica soci non rinnovati |
| `renewal.reminder_approval` | `true` | Richiede approvazione admin prima dell'invio |

**Interfaccia di input**: ogni data usa un select del mese (nella lingua dell'interfaccia corrente) e un campo giorno con pulsanti − e +. Il riepilogo a destra mostra le date attualmente salvate nel database — si aggiorna dopo il salvataggio, non in tempo reale.

**Come funzionano le date nel ciclo di rinnovo**:
- Tra `date_open` e `date_close`: status socio → `in_renewal`
- Dopo `date_close` fino a `date_lapse`: status socio → `not_renewed`
- Dopo `date_lapse`: status socio → `lapsed`, numero tessera liberato

---

### Categorie Soci

Gestione completa (creazione, modifica, attivazione/disattivazione) delle categorie di iscrizione. Ogni associazione definisce le proprie categorie — non esistono categorie obbligatorie. Le categorie create qui appaiono nel form di inserimento socio e nel sistema di rinnovi.

**Campi della categoria**:

| Campo | Descrizione |
|---|---|
| `name` | Slug interno (solo lettere minuscole e underscore) |
| `label` | Nome visualizzato nell'interfaccia |
| `description` | Mostrata nel form di iscrizione |
| `annual_fee` | Quota di default per questa categoria |
| `is_free` | Se vero, nessun pagamento richiesto (es. Onorario) |
| `is_exempt_from_renewal` | Se vero, il sistema di rinnovo salta questa categoria |
| `requires_approval` | L'iscrizione richiede approvazione del direttivo |
| `valid_from` | Categoria disponibile da questa data |
| `valid_until` | Categoria scade a questa data (es. Under 30) |
| `is_active` | Nascosta dall'iscrizione se falso |
| `sort_order` | Ordine di visualizzazione nei select |

**Storico quote annuali**: ogni categoria può avere quote diverse per anno. Il sistema risolve la quota applicabile in questo ordine:
1. Record in `membership_category_fees` per l'anno corrente
2. Anno più recente disponibile in `membership_category_fees`
3. `annual_fee` da `membership_categories`

Per aggiungere o modificare la quota di un anno specifico, usare il pannello storico quote all'interno di ogni riga categoria.

---

### Ruoli Direttivo

Gestione completa dei ruoli del direttivo. I ruoli di default vengono precaricati all'installazione ma possono essere modificati o eliminati. Ogni associazione può definire la propria struttura di ruoli.

**Campi del ruolo**:

| Campo | Descrizione |
|---|---|
| `name` | Slug interno |
| `label` | Nome visualizzato |
| `description` | Descrizione opzionale |
| `is_board_member` | TRUE = membro del direttivo, FALSE = ruolo tecnico (es. revisore) |
| `can_sign` | TRUE = può firmare atti ufficiali |
| `is_active` | Nascosto dal form socio se falso |
| `sort_order` | Ordine di visualizzazione |

**Ruoli precaricati**: Presidente, Vicepresidente, Segretario, Tesoriere, Consigliere, Revisore dei conti.

---

### Interfaccia

| Chiave setting | Default | Descrizione |
|---|---|---|
| `ui.theme` | `uikit` | Tema grafico attivo |
| `ui.locale` | `it` | Lingua dell'interfaccia |
| `ui.date_format` | `d/m/Y` | Formato di visualizzazione delle date |
| `ui.timezone` | `Europe/Rome` | Fuso orario per date e orari |

**Rilevamento temi**: il sistema scansiona `public/themes/` cercando sottocartelle che contengono un file `layout.php`. Ogni tema può includere un file `theme.json` con i metadati:

```json
{
  "name": "UIkit 3",
  "description": "Tema di default",
  "version": "1.0.0",
  "author": "Socius Team",
  "status": "stable"
}
```

I temi con `"status": "wip"` vengono mostrati con l'etichetta "in sviluppo" e generano un avviso se selezionati.

**Rilevamento lingue**: il sistema scansiona `lang/` cercando sottocartelle che contengono un file `messages.php`. Per aggiungere una nuova lingua, creare una cartella con il codice ISO (es. `lang/de/`) e aggiungere i file di traduzione.

**Formato data**: si applica a tutte le date visualizzate nel sistema tramite l'helper `format_date()`. Formati disponibili:

| Formato | Esempio |
|---|---|
| `d/m/Y` | 15/11/2026 |
| `d/m/y` | 15/11/26 |
| `d F Y` | 15 Novembre 2026 |
| `m/d/Y` | 11/15/2026 |
| `Y-m-d` | 2026-11-15 (ISO) |

Il cambio di lingua viene applicato immediatamente alla sessione corrente senza richiedere un nuovo login.

---

### Email

Configurazione SMTP per le email in uscita. La password viene salvata cifrata usando l'`APP_KEY` dal file `.env`.

| Chiave setting | Default | Descrizione |
|---|---|---|
| `smtp.host` | — | Hostname del server SMTP |
| `smtp.port` | `587` | Porta (587 per TLS, 465 per SSL, 25 senza cifratura) |
| `smtp.encryption` | `tls` | Cifratura: `tls`, `ssl`, o `none` |
| `smtp.username` | — | Utente SMTP |
| `smtp.password` | — | Password SMTP (salvata cifrata) |
| `smtp.from_address` | — | Indirizzo email del mittente |
| `smtp.from_name` | — | Nome visualizzato del mittente |

**Email di test**: il pannello include un pulsante "Invia email di test" che si connette al server SMTP e invia un messaggio di prova all'indirizzo email dell'admin attualmente loggato. La connessione viene testata tramite socket PHP grezzo — nessuna libreria esterna richiesta.

---

### Numero Socio

Controlla il contatore progressivo dei numeri socio.

| Chiave setting | Descrizione |
|---|---|
| `members.number_start` | Numero di partenza per nuove installazioni |
| `members.next_number` | Prossimo numero da assegnare |

**Reset per la produzione**: quando si passa dallo sviluppo alla produzione, usare questa sezione per reimpostare il contatore a 1 (o al valore desiderato). Il nuovo valore deve essere superiore al massimo attualmente assegnato — il sistema impedisce la creazione di duplicati.

---

## Database

### Tabella `settings`

Tutte le impostazioni sono salvate nella tabella `settings` come coppie chiave-valore.

| Colonna | Tipo | Descrizione |
|---|---|---|
| `key` | VARCHAR(100) UNIQUE | Chiave in notazione puntata (es. `association.name`) |
| `value` | TEXT | Valore salvato |
| `type` | ENUM | `string`, `integer`, `boolean`, `json`, `date` |
| `group` | VARCHAR(50) | Gruppo logico (es. `association`, `renewal`, `ui`) |
| `label` | VARCHAR(255) | Etichetta leggibile per il back-end |

---

## Modello

**`app/Models/Setting.php`**

| Metodo | Descrizione |
|---|---|
| `get(string $key, mixed $default)` | Legge una singola impostazione con cache in memoria |
| `set(string $key, mixed $value)` | Scrive una singola impostazione |
| `setMultiple(array $keyValues)` | Scrive più impostazioni in una transazione |
| `getGroup(string $group)` | Tutte le impostazioni di un gruppo |
| `getAllGroups()` | Tutte le impostazioni organizzate per gruppo |
| `encryptPassword(string $plain)` | Cifra usando APP_KEY |
| `decryptPassword(string $encrypted)` | Decifra usando APP_KEY |

---

## Helper

**`format_date(string $date, bool $withTime = false): string`**

Formatta una data secondo `ui.date_format` dalle impostazioni. Restituisce `—` per date vuote o non valide. Usata in tutti i template per visualizzare le date in modo coerente.

```php
echo format_date($member['birth_date']);        // 15/11/1985
echo format_date($member['created_at'], true);  // 15/11/2026 14:30
```

**`format_date_iso(string $date): string`**

Restituisce una data in formato `Y-m-d` per l'attributo value dei campi `input type="date"`. Sempre ISO indipendentemente dall'impostazione del formato di visualizzazione.

**`format_month_day(string $mmdd, string $locale): string`**

Formatta una data MM-GG come "15 Novembre" o "15 November" secondo la lingua corrente. Usata nel riepilogo dell'Anno Sociale.

---

## Sistema temi

Per creare un nuovo tema:

1. Creare una cartella in `public/themes/nome-tema/`
2. Creare `layout.php` con la struttura HTML base (navbar, sidebar, area contenuto, footer)
3. Creare i file template per ogni pagina: `members.php`, `member.php`, `member-form.php`, ecc.
4. Creare `theme.json` con i metadati del tema
5. Il tema appare automaticamente nel pannello Interfaccia delle impostazioni

I template ricevono variabili PHP già elaborate — nessuna query al database nei template.

---

## Migration

| File | Descrizione |
|---|---|
| `012_date_format_setting.sql` | Aggiunta impostazione `ui.date_format` con default `d/m/Y` |
