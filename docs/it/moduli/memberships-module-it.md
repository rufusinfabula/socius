# Modulo Tessere

**Socius v0.3.x — Documentazione del modulo (aggiornata alla v0.3.9)**

---

## Panoramica

Il modulo Tessere gestisce le tessere annuali dei soci. Ogni record tessera rappresenta la partecipazione di un socio per un anno sociale. Il modulo gestisce la creazione manuale delle tessere, la registrazione dei pagamenti, la gestione dei numeri tessera, la sincronizzazione automatica degli status soci e la zona pericolosa per le correzioni amministrative.

---

## Concetti chiave

### Numero socio e numero tessera

Due identificatori distinti coesistono in Socius:

**Numero socio** (`members.member_number`) — intero progressivo permanente. Formato: `M00001`. Non cambia mai, nemmeno dopo decadenza o reiscrizione.

**Numero tessera** (`memberships.membership_number`) — codice alfanumerico assegnato alla creazione della tessera. Formato: `C00001`. Stabile finché il socio rinnova. Liberato (impostato a NULL su `members`) se il socio decade. I record tessera storici mantengono sempre il loro numero.

Il numero tessera in `memberships.membership_number` è la **fonte primaria**. Il campo `members.membership_number` è una copia denormalizzata aggiornata automaticamente dal sistema — non modificarlo mai direttamente.

### Status della tessera

| Valore DB | Significato |
|---|---|
| `pending` | Creata ma pagamento non ancora ricevuto |
| `paid` | Pagamento confermato, tessera attiva |
| `waived` | Quota condonata per delibera del direttivo (es. soci onorari) |
| `cancelled` | Annullata — errore di inserimento o altro motivo |

`paid` e `waived` sono equivalenti ai fini del calcolo dello status socio — entrambi rendono il socio `active`.

### Status del socio e ciclo di rinnovo

Lo status del socio viene calcolato automaticamente dal sistema Sync. La logica si basa sulla tessera più recente del socio e dalla posizione di oggi nel ciclo dell'anno sociale.

| Condizione | Status socio |
|---|---|
| Ha tessera `paid`/`waived` per l'anno sociale corrente | `active` |
| Ha tessera `pending` per l'anno corrente, dentro la finestra di rinnovo | `in_renewal` |
| Ha tessera `pending` per l'anno corrente, dopo la chiusura rinnovi | `not_renewed` |
| Ha tessera `pending` per l'anno corrente, dopo la data di decadenza | `lapsed` |
| Ha avuto tessera `paid`/`waived` l'anno scorso, prima dell'apertura rinnovi | `active` |
| Ha avuto tessera `paid`/`waived` l'anno scorso, dentro la finestra di rinnovo | `in_renewal` |
| Ha avuto tessera `paid`/`waived` l'anno scorso, dopo la chiusura rinnovi | `not_renewed` |
| Ha avuto tessera `paid`/`waived` l'anno scorso, dopo la decadenza | `lapsed` |
| Nessuna tessera recente valida | `lapsed` |

Gli status `suspended`, `resigned` e `deceased` non vengono **mai toccati dal sync** — vengono impostati manualmente e hanno sempre priorità assoluta.

### Determinazione dell'anno sociale

L'anno sociale è l'anno per cui si stanno gestendo le tessere. Avanza all'anno solare successivo solo dopo che la data di decadenza è passata.

La data di `renewal_open` può appartenere all'anno solare precedente (es. novembre apre i rinnovi per l'anno successivo). Questo viene determinato confrontando `renewal_open` MM-GG con `renewal_close` MM-GG: se `open > close` come stringhe, la data di apertura appartiene a `socialYear - 1`. Questa logica funziona per qualsiasi configurazione senza soglie arbitrarie.

Esempi:
- Ciclo Nov → Apr: `'11-15' > '04-15'` → renewal_open è nell'anno prima del social year
- Ciclo Set → Giu: `'09-01' > '06-30'` → renewal_open è nell'anno prima del social year
- Ciclo Gen → Giu: `'01-02' < '06-30'` → renewal_open è nello stesso social year

---

## Tabelle del database

### `memberships`

Una riga per socio per anno.

| Colonna | Tipo | Note |
|---|---|---|
| `id` | INT PK | |
| `member_id` | INT FK → members | ON DELETE CASCADE |
| `membership_number` | VARCHAR(10) NULL | Fonte primaria del numero tessera |
| `category_id` | INT FK → membership_categories | La categoria appartiene alla tessera, non al socio |
| `year` | YEAR | Anno sociale |
| `fee` | DECIMAL(8,2) | Quota applicata per questo anno |
| `status` | ENUM | pending, paid, waived, cancelled |
| `valid_from` | DATE | Inizio validità |
| `valid_until` | DATE | Fine validità |
| `paid_on` | DATE NULL | Data ricezione pagamento |
| `payment_method` | ENUM NULL | cash, bank_transfer, paypal, satispay, waived, other |
| `payment_reference` | VARCHAR(255) NULL | Numero ricevuta, causale bonifico |
| `notes` | TEXT NULL | Note interne |

Nota: la categoria è collegata alla tessera, non al socio. Un socio può avere categorie diverse in anni diversi (es. Ordinario nel 2024, Onorario nel 2025).

### `reserved_member_numbers`

Numeri tessera riservati permanentemente — non vengono mai riassegnati.

| Colonna | Tipo | Note |
|---|---|---|
| `membership_number` | VARCHAR(20) UNIQUE | es. C00001 |
| `reserved_by` | INT | users.id — senza FK, sopravvive alla cancellazione utente |
| `reason` | VARCHAR(500) NULL | Motivazione della riserva |

---

## Pagine

| File | Descrizione |
|---|---|
| `public/memberships.php` | Lista globale tessere con filtri |
| `public/membership.php?id=N` | Dettaglio tessera — sola lettura |
| `public/membership-new.php` | Form nuova tessera |
| `public/membership-edit.php?id=N` | Modifica tessera + zona pericolosa |

---

## Anni disponibili per nuova tessera

Il select anno nel form tessera viene popolato dinamicamente da due fonti:

1. Gli anni presenti in `membership_category_fees` (anni per cui sono state configurate le quote nelle impostazioni)
2. L'anno corrente — sempre incluso anche senza quote configurate

Questo significa che l'admin dovrebbe configurare le quote per un anno prima di creare tessere per quell'anno. L'anno corrente è sempre disponibile come fallback usando la quota di default della categoria.

Controllo duplicati: se esiste già una tessera per il socio selezionato e l'anno scelto, il sistema blocca la creazione e mostra un link alla tessera esistente.

---

## Form nuova tessera

URL: `membership-new.php` oppure `membership-new.php?member_id=N`

**Selezione socio**: campo di ricerca live che usa `api/members-search.php`. Ricerca per nome, cognome o numero socio (M00001). Minimo 2 caratteri, debounce 300ms.

**Dopo la creazione**:
- Se il metodo di pagamento non è "nessuno": crea record `payment_requests` e `payments`, imposta status a `paid`
- Se la categoria ha `is_exempt_from_renewal = true`: imposta status a `waived`
- Aggiorna `members.membership_number` con il nuovo numero tessera
- Ricalcola e aggiorna immediatamente `members.status` per questo socio

---

## Modifica tessera e zona pericolosa

URL: `membership-edit.php?id=N`

**Modifica normale** (admin e segreteria): status, quota, data pagamento, metodo pagamento, note.

**Zona pericolosa** (solo super_admin) — UIkit Accordion, chiuso di default. Ogni operazione richiede motivazione obbligatoria (min 10 caratteri) e viene registrata in `audit_logs`.

| Operazione | Descrizione |
|---|---|
| Riserva numero tessera | Riserva permanentemente il numero — mai riassegnato. Salvato in `reserved_member_numbers`. |
| Cambia numero tessera | Assegna un diverso numero tessera disponibile |
| Forza status tessera | Cambia lo status bypassando il flusso normale |
| Correggi quota pagata | Corregge l'importo registrato — solo per errori di inserimento |
| Forza status socio | Cambia lo status del socio bypassando il ciclo di rinnovo |

---

## Sistema Sync

Il sistema Sync ricalcola automaticamente gli status dei soci. Gira una volta al giorno — attivato dal primo login della giornata. Può essere forzato manualmente in qualsiasi momento tramite l'icona nella navbar.

### Come funziona

Ad ogni login, il sistema controlla `system.last_sync_date` nelle impostazioni. Se la data è diversa da oggi, redirige a `sync-run.php` che chiama `sync.php?action=run` via AJAX, mostra uno spinner durante l'elaborazione, poi redirige alla pagina in cui si trovava l'utente (o alla dashboard).

### Endpoint di sync.php

| Azione | Descrizione |
|---|---|
| `?action=run` | Esegue il ricalcolo completo per tutti i soci |
| `?action=status` | Restituisce i metadati del sync corrente come JSON |

Formato risposta:
```json
{
  "ok": true,
  "updated": 12,
  "total": 105,
  "duration_ms": 234,
  "last_sync_date": "09/04/2026",
  "is_synced": true
}
```

### Indicatore Sync nella navbar

La navbar mostra un'icona Lucide che indica lo stato del sync corrente. Lucide è caricato via CDN in `layout.php`.

| Stato | Icona | Colore |
|---|---|---|
| Sincronizzato oggi | `cloud-check` | Verde (#28a745) |
| Non ancora sincronizzato | `cloud` | Arancione (#fd7e14) |

Cliccando l'icona si attiva `sync-run.php?return={url_corrente}` — dopo il sync l'utente torna alla pagina in cui si trovava.

### Chiavi settings usate dal sync

| Chiave | Descrizione |
|---|---|
| `system.last_sync_date` | Data dell'ultimo ricalcolo (Y-m-d) |
| `system.last_sync_count` | Numero di soci aggiornati nell'ultimo sync |
| `system.last_sync_duration_ms` | Durata dell'ultimo sync in millisecondi |

### Ricalcolo immediato per singolo socio

Quando si crea una tessera o si cambia il suo status, il sistema ricalcola immediatamente lo status di quel singolo socio senza aspettare il sync giornaliero.

---

## calculate_member_status()

Definita in `public/_init.php`. Riceve un array socio (con i dati della tessera più recente in JOIN) e l'array completo delle impostazioni. Restituisce la stringa dello status calcolato.

I soci con status `suspended`, `resigned` o `deceased` vengono esclusi dal sync — questi status non vengono mai sovrascritti automaticamente.

---

## Famiglia API interna

Tutti gli endpoint richiedono autenticazione. Tutti restituiscono JSON.

| Endpoint | Parametri | Usato da |
|---|---|---|
| `api/members-search.php` | `?q=&limit=&status=` | Ricerca socio nei form |
| `api/member.php` | `?id=` | Pre-compilazione form dopo selezione socio |
| `api/members-list.php` | `?status=&category_id=&board=&year=&page=` | Lista filtrata per moduli futuri |
| `api/member-stats.php` | `?year=` | Statistiche aggregate (cache 5 min) |

---

## Badge CSS

Definiti globalmente in `public/themes/uikit/layout.php`:

```css
.badge-member-number { font-family: monospace; background: #E8F0FE; color: #1A3A6B; }
.badge-card-number   { font-family: monospace; background: #E6F4EA; color: #1B5E2F; }
```

---

## Vincoli di chiave esterna

Tutte le tabelle collegate a `members` usano `ON DELETE CASCADE`. La cancellazione di emergenza di un socio attiva tutti i CASCADE in una singola transazione, rimuovendo tutti i record collegati inclusi tessere, pagamenti e richieste di pagamento.

---

## Migration

| File | Descrizione |
|---|---|
| `013_memberships_indexes.sql` | Indici, colonne payment_method e payment_reference |
| `014_membership_number_restructure.sql` | membership_number in memberships, formato M/C, impostazioni prefissi |
| `015_fix_membership_number_nullable.sql` | membership_number nullable in memberships |
| `016_cascade_foreign_keys.sql` | ON DELETE CASCADE su tutte le FK collegate ai soci |
| `017_member_status_sync.sql` | email nullable su members, rimozione category_id da members, chiavi sync in settings |
