# Modulo Soci

**Socius v0.1.x — Documentazione del modulo**

---

## Panoramica

Il modulo Soci è il cuore di Socius. Gestisce il ciclo di vita completo di un socio, dalla prima iscrizione alla decadenza o alle dimissioni, inclusi i ruoli nel direttivo, le categorie di iscrizione e la cancellazione di emergenza.

---

## Concetti chiave

### Numero socio e numero tessera

Ogni socio ha due identificatori distinti:

**Numero socio** (`members.member_number`) — intero progressivo permanente assegnato alla prima iscrizione. Non cambia mai, nemmeno se il socio decade e si reiscrive dopo anni. È l'identità del socio nel sistema.

**Numero tessera** (`members.membership_number`) — codice alfanumerico assegnato all'iscrizione (formato: `SOC0001`). Viene liberato solo se il socio non rinnova entro la scadenza finale del rinnovo (es. 31 dicembre). Un socio che rinnova regolarmente mantiene lo stesso numero tessera per anni.

### Status del socio

I valori di status sono salvati in inglese nel database e tradotti solo nell'interfaccia.

| Valore DB | Significato |
|---|---|
| `active` | Ha una tessera valida per l'anno in corso |
| `in_renewal` | Nel periodo di rinnovo, non ancora rinnovato |
| `not_renewed` | Scaduto il termine di rinnovo, ancora entro il 31 dicembre |
| `lapsed` | Non ha rinnovato entro il 31 dicembre — numero tessera liberato |
| `suspended` | Sospeso per delibera del direttivo |
| `resigned` | Dimissioni volontarie |
| `deceased` | Deceduto |

### Soci onorari

I soci onorari non sono uno status — sono una **categoria** (`membership_categories`) con `is_exempt_from_renewal = true` e `is_free = true`. Il sistema di rinnovo li salta completamente. Il loro status rimane `active` in modo permanente salvo modifica manuale.

---

## Tabelle del database

### `members`

Tabella principale. I record non vengono **mai eliminati fisicamente**. La cancellazione di emergenza è l'unica eccezione e richiede la conferma del super amministratore.

| Colonna | Tipo | Note |
|---|---|---|
| `id` | INT PK | Auto-increment interno |
| `member_number` | INT UNIQUE | Numero progressivo permanente — non cambia mai |
| `membership_number` | VARCHAR(20) | Codice alfanumerico — liberato in caso di decadenza |
| `name` | VARCHAR(100) | |
| `surname` | VARCHAR(100) | |
| `email` | VARCHAR(255) | |
| `phone1` | VARCHAR(30) | Telefono principale |
| `phone2` | VARCHAR(30) | Telefono secondario |
| `birth_date` | DATE | |
| `birth_place` | VARCHAR(100) | |
| `sex` | ENUM('M','F') | Per il calcolo del codice fiscale |
| `gender` | VARCHAR(50) | Identità di genere — opzionale, dato sensibile GDPR |
| `fiscal_code` | VARCHAR(16) UNIQUE | Nullable — più soci possono non averlo |
| `address` | VARCHAR(255) | |
| `city` | VARCHAR(100) | |
| `postal_code` | VARCHAR(10) | |
| `province` | VARCHAR(5) | |
| `country` | CHAR(2) | ISO 3166-1 alpha-2, default 'IT' |
| `category_id` | INT FK | Riferimento a `membership_categories` |
| `status` | ENUM | Vedi tabella status sopra |
| `joined_on` | DATE | Data della prima iscrizione |
| `resigned_on` | DATE | Data delle dimissioni volontarie |
| `notes` | TEXT | Note interne — visibili solo ad admin e segreteria |
| `created_by` | INT FK | users.id |

### `membership_categories`

Configurabile dal pannello impostazioni del back-end. Ogni associazione definisce le proprie categorie.

| Colonna | Tipo | Note |
|---|---|---|
| `name` | VARCHAR(100) UNIQUE | Slug interno (es. `ordinary`, `honorary`) |
| `label` | VARCHAR(255) | Nome visualizzato |
| `annual_fee` | DECIMAL(8,2) | Quota di default |
| `is_free` | BOOLEAN | Nessun pagamento richiesto |
| `is_exempt_from_renewal` | BOOLEAN | Non soggetta a rinnovo annuale |
| `requires_approval` | BOOLEAN | L'iscrizione richiede approvazione del direttivo |
| `valid_from` | DATE | Categoria disponibile da questa data |
| `valid_until` | DATE | Categoria scade a questa data (es. Under 30) |
| `is_active` | BOOLEAN | Nascosta dall'iscrizione se false |
| `sort_order` | TINYINT | Ordine di visualizzazione |

### `membership_category_fees`

Storico delle quote annuali per categoria. Permette all'admin di confermare o modificare le quote ogni anno senza perdere i dati storici.

| Colonna | Tipo | Note |
|---|---|---|
| `category_id` | INT FK | |
| `year` | YEAR | Anno sociale |
| `fee` | DECIMAL(8,2) | Quota applicata per questo anno |
| `approved_by` | INT FK | users.id dell'admin che ha impostato la quota |
| UNIQUE | (category_id, year) | Una quota per categoria per anno |

Ordine di risoluzione della quota:
1. Cerca un record in `membership_category_fees` per l'anno corrente
2. Se non trovato, usa l'anno più recente disponibile
3. Se non trovato, usa `membership_categories.annual_fee`

### `board_roles`

Catalogo configurabile dei ruoli del direttivo. Ogni associazione definisce i propri ruoli.

| Colonna | Tipo | Note |
|---|---|---|
| `name` | VARCHAR(50) UNIQUE | Slug interno (es. `president`) |
| `label` | VARCHAR(100) | Nome visualizzato |
| `description` | TEXT | Descrizione opzionale |
| `is_board_member` | BOOLEAN | TRUE = membro del direttivo, FALSE = ruolo tecnico (es. revisore) |
| `can_sign` | BOOLEAN | TRUE = può firmare atti ufficiali |
| `is_active` | BOOLEAN | |
| `sort_order` | TINYINT | |

Ruoli precaricati all'installazione: Presidente, Vicepresidente, Segretario, Tesoriere, Consigliere, Revisore dei conti.

### `board_memberships`

Storico di chi ricopre quale ruolo nel direttivo e quando.

| Colonna | Tipo | Note |
|---|---|---|
| `member_id` | INT FK | |
| `role_id` | INT FK | |
| `elected_on` | DATE | Data di elezione o nomina |
| `expires_on` | DATE | Fine mandato — NULL = a tempo indeterminato |
| `resigned_on` | DATE | Data di dimissioni dal ruolo |
| `elected_by_assembly_id` | INT FK | Assemblea che ha deliberato la nomina |
| `notes` | TEXT | |

Un socio è considerato attualmente in carica quando `expires_on IS NULL OR expires_on >= OGGI` E `resigned_on IS NULL`.

### `reserved_member_numbers`

Traccia i numeri tessera che non devono essere riassegnati dopo una cancellazione di emergenza in cui l'operatore ha scelto di mantenere il numero riservato.

---

## Pagine

| File | Descrizione |
|---|---|
| `public/members.php` | Lista soci con filtri, badge status, paginazione |
| `public/member.php?id=N` | Profilo socio — sola lettura |
| `public/member-new.php` | Form nuovo socio |
| `public/member-edit.php?id=N` | Form modifica socio |
| `public/member-delete.php?id=N` | Conferma cancellazione di emergenza |

---

## Layout del form

Il form è organizzato in tre sezioni principali:

**Box Anagrafica** (sinistra, 2/3 larghezza): cognome, nome, sesso, genere, data di nascita, luogo di nascita, codice fiscale con pulsante calcola.

**Box Socio** (destra, 1/3 larghezza): numero tessera (sola lettura), numero socio (sola lettura), status, categoria, iscritto dal, note interne.

**Box Contatti** (larghezza piena, sotto): email, telefono 1, telefono 2, indirizzo, CAP, città, provincia, paese.

**Box Ruolo nel direttivo** (larghezza piena, sotto i contatti): select ruolo, data inizio incarico, note. Crea o aggiorna un record in `board_memberships`.

**Zona pericolosa** (larghezza piena, in fondo, bordo rosso): pulsante cancellazione di emergenza — visibile solo al super amministratore.

---

## Layout del profilo socio

La pagina profilo segue la stessa struttura a tre box in modalità sola lettura.

In cima al box Socio, i ruoli attivi nel direttivo sono mostrati come badge UIkit:
- `is_board_member = true` → badge colore primario
- `is_board_member = false` → badge grigio (ruolo tecnico)

---

## Cancellazione di emergenza

La cancellazione di emergenza è una procedura controllata accessibile solo al `super_admin`. Richiede:

1. Revisione di tutti i dati collegati (tessere, pagamenti)
2. Scelta: liberare il numero tessera o mantenerlo riservato
3. Digitazione di `DELETE` (maiuscolo) nel campo di conferma
4. Validazione del token CSRF

La procedura: scrive in `audit_logs` → elimina `memberships` → annulla `payment_requests.member_id` → elimina `members`. I pagamenti vengono preservati. Se il numero viene mantenuto riservato, viene inserito un record in `reserved_member_numbers`.

---

## Modelli

| File | Metodi principali |
|---|---|
| `app/Models/Member.php` | `findAll()`, `findById()`, `create()`, `update()`, `getNextMemberNumber()`, `emergencyDelete()` |
| `app/Models/MembershipCategory.php` | `findAll()`, `getFeeForYear()`, `setFeeForYear()`, `getFeesHistory()` |
| `app/Models/BoardRole.php` | `findAll()`, `getCurrentBoard()`, `getMemberRoles()`, `isCurrentBoardMember()` |
| `app/Models/BoardMembership.php` | `create()`, `update()`, `findByMember()`, `findCurrent()` |

---

## Internazionalizzazione

Tutte le stringhe relative ai soci sono in `lang/it/members.php` e `lang/en/members.php`. Le stringhe specifiche del direttivo sono in `lang/it/board.php` e `lang/en/board.php`.

I valori nel database (status, nome categoria, nome ruolo) sono sempre in inglese. La traduzione avviene solo al momento della visualizzazione tramite `__('members.status_active')` ecc.

---

## Migration

| File | Descrizione |
|---|---|
| `001_initial_schema.sql` | Schema iniziale completo — tutte le 21 tabelle |
| `004_add_sex_gender_to_members.sql` | Aggiunta campi sesso e genere |
| `005_rename_phone_fields.sql` | phone → phone1, mobile → phone2 |
| `006_fix_member_status_enum.sql` | ENUM status allineato alla specifica |
| `007_membership_categories.sql` | Categorie estese, tabella storico quote |
| `008_rename_italian_fields.sql` | Rinominati sesso→sex, genere→gender, anno→year ecc. |
| `009_member_number_and_board.sql` | Aggiunto member_number, tipo board per assemblee |
| `010_fix_fiscal_code_unique.sql` | fiscal_code UNIQUE permette più NULL |
| `011_board_roles.sql` | Create board_roles e board_memberships |
