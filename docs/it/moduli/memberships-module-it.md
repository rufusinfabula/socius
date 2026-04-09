# Modulo Tessere

**Socius v0.3.x — Documentazione del modulo**

---

## Panoramica

Il modulo Tessere gestisce le tessere annuali dei soci. Ogni record tessera rappresenta la partecipazione di un socio per un anno sociale. Il modulo gestisce la creazione manuale delle tessere, la registrazione dei pagamenti, la gestione dei numeri tessera e la zona pericolosa per le correzioni amministrative.

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
| `waived` | Quota condonata (es. soci onorari, delibera direttivo) |
| `cancelled` | Annullata — errore di inserimento o altro motivo |

### Ciclo di vita del numero tessera

```
Socio creato → membership_number = NULL su members
               (nessuna tessera ancora)

Tessera creata → numero assegnato (es. C00001)
                 salvato in memberships.membership_number
                 copiato su members.membership_number

Socio rinnova → stesso numero mantenuto
                nuovo record tessera per il nuovo anno

Socio decade → members.membership_number = NULL
               la tessera storica mantiene C00001
               numero disponibile per riassegnazione

Cancellazione emergenza → tutti i dati rimossi
                          tessere incluse
```

---

## Tabelle del database

### `memberships`

Una riga per socio per anno.

| Colonna | Tipo | Note |
|---|---|---|
| `id` | INT PK | |
| `member_id` | INT FK → members | ON DELETE CASCADE |
| `membership_number` | VARCHAR(10) NULL | Fonte primaria del numero tessera |
| `category_id` | INT FK → membership_categories | |
| `year` | YEAR | Anno sociale |
| `fee` | DECIMAL(8,2) | Quota applicata per questo anno |
| `status` | ENUM | pending, paid, waived, cancelled |
| `valid_from` | DATE | Inizio validità |
| `valid_until` | DATE | Fine validità (di solito 31 dicembre) |
| `paid_on` | DATE NULL | Data ricezione pagamento |
| `payment_method` | ENUM NULL | cash, bank_transfer, paypal, satispay, waived, other |
| `payment_reference` | VARCHAR(255) NULL | Numero ricevuta, causale bonifico |
| `notes` | TEXT NULL | Note interne |

### `reserved_member_numbers`

Numeri tessera che non devono mai essere riassegnati.

| Colonna | Tipo | Note |
|---|---|---|
| `id` | INT PK | |
| `membership_number` | VARCHAR(20) UNIQUE | es. C00001 |
| `reserved_at` | TIMESTAMP | |
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

## Lista tessere

URL: `memberships.php`

Filtri: anno (default: corrente), status, categoria.
Colonne: N. Socio | Cognome Nome | Categoria | Anno | N. Tessera | Quota | Status | Pagato il | Azioni.

Entrambi i numeri M e C vengono mostrati con i rispettivi badge CSS.

---

## Form nuova tessera

URL: `membership-new.php` oppure `membership-new.php?member_id=N`

**Selezione socio**: campo di ricerca live che usa `api/members-search.php`. Ricerca per nome, cognome o numero socio (M00001). Minimo 2 caratteri, debounce 300ms. I risultati mostrano nome e badge M. Se `?member_id` è presente nell'URL (arrivo dal profilo socio), il campo è pre-compilato.

**Campi del form**:

*Box Tessera:*
- Socio (ricerca o pre-compilato)
- Anno sociale (corrente o successivo)
- Numero tessera (proposto automaticamente, modificabile)
- Categoria (solo categorie attive)
- Quota (pre-compilata dalla categoria, modificabile)
- Status (default: pending)

*Box Pagamento (nascosto se status = waived o cancelled):*
- Metodo pagamento
- Data pagamento (default: oggi)
- Riferimento (numero ricevuta, causale bonifico)
- Note

**Assegnazione numero tessera**: `next_card_number()` trova `MAX(parte numerica del membership_number)` tra `members` e `reserved_member_numbers`, poi restituisce prefisso + (max + 1). Il numero proposto viene mostrato con lo stile `.badge-card-number` e può essere modificato dall'admin.

**Dopo la creazione**:
- Se il metodo di pagamento non è "nessuno": crea record `payment_requests` e `payments`, imposta status tessera a `paid`
- Se la categoria ha `is_exempt_from_renewal = true`: imposta status a `waived`
- Aggiorna `members.membership_number` con il nuovo numero tessera
- Aggiorna `members.status` a `active` se la tessera è paid o waived

---

## Modifica tessera e zona pericolosa

URL: `membership-edit.php?id=N`

**Modifica normale** (admin e segreteria): status, quota, data pagamento, metodo pagamento, note.

**Zona pericolosa** (solo super_admin) — UIkit Accordion, chiuso di default:

Ogni operazione richiede una motivazione obbligatoria (min 10 caratteri) e viene registrata in `audit_logs`.

| Operazione | Descrizione |
|---|---|
| Riserva numero tessera | Riserva permanentemente il numero — non verrà mai riassegnato |
| Cambia numero tessera | Assegna un diverso numero tessera disponibile |
| Forza status tessera | Cambia lo status bypassando il flusso normale |
| Correggi quota pagata | Corregge l'importo registrato (correzione errori di inserimento) |
| Forza status socio | Cambia lo status del socio bypassando il ciclo di rinnovo |

---

## Profilo socio — storico tessere

In `member.php`, una sezione "Storico tessere" mostra tutte le tessere del socio ordinate per anno decrescente.

Colonne: Anno | N. Tessera | Categoria | Quota | Status | Azioni (Dettaglio / Modifica).

Pulsante "Nuova tessera per questo socio" porta a `membership-new.php?member_id=N`.

---

## API interna

Il modulo tessere usa la famiglia API interna in `public/api/`:

| Endpoint | Usato da |
|---|---|
| `api/members-search.php?q=` | Campo ricerca socio in membership-new.php |
| `api/member.php?id=` | Pre-compilazione categoria dopo selezione socio |

---

## Modelli

**`app/Models/Membership.php`**

| Metodo | Descrizione |
|---|---|
| `findAll(array $filters, int $page, int $perPage)` | Lista paginata con filtri |
| `findById(int $id)` | Singola tessera con dati socio e categoria |
| `findByMember(int $memberId)` | Tutte le tessere di un socio |
| `getCurrentForMember(int $memberId)` | Tessera anno corrente per un socio |
| `create(array $data)` | Crea tessera, assegna numero, aggiorna socio |
| `update(int $id, array $data)` | Aggiorna campi, sincronizza numero tessera se anno corrente |
| `releaseCardNumber(int $memberId)` | Imposta members.membership_number = NULL alla decadenza |
| `getNextAvailableNumber()` | Restituisce il prossimo numero tessera disponibile |
| `getYearsWithMemberships()` | Anni distinti presenti in memberships |

**`app/Models/Member.php`** (metodi rilevanti per le tessere)

| Metodo | Descrizione |
|---|---|
| `updateCardNumber(int $memberId, ?string $cardNumber)` | Aggiorna la copia denormalizzata del numero tessera sul socio |

---

## Badge CSS

Due classi CSS globali definite in `public/themes/uikit/layout.php`:

```css
.badge-member-number {
    font-family: monospace;
    background: #E8F0FE;
    color: #1A3A6B;
    /* blu — identificatore permanente */
}

.badge-card-number {
    font-family: monospace;
    background: #E6F4EA;
    color: #1B5E2F;
    /* verde — tessera attiva */
}
```

Uso nei template:
```html
<span class="badge-member-number">M00001</span>
<span class="badge-card-number">C00001</span>
```

---

## Funzioni helper

**`next_card_number(): string`**
Genera il prossimo numero tessera disponibile. Legge MAX da `members.membership_number` e `reserved_member_numbers`, restituisce prefisso + (max + 1) con zero padding.

**`format_member_number(?int $number): string`**
Formatta un intero grezzo come M00001. Restituisce `—` per null.

**`format_card_number(?string $number): string`**
Restituisce la stringa del numero tessera o `—` per null/vuoto.

Prefisso e numero di cifre sono configurabili nelle impostazioni:
- `members.number_prefix` (default: M)
- `members.card_prefix` (default: C)
- `members.number_digits` (default: 5)

---

## Vincoli di chiave esterna

Tutte le tabelle collegate a `members` usano `ON DELETE CASCADE`:

| Tabella | Colonna | Comportamento alla cancellazione socio |
|---|---|---|
| `memberships` | `member_id` | CASCADE — eliminata |
| `payments` | `member_id` | CASCADE — eliminato |
| `payment_requests` | `member_id` | CASCADE — eliminata |
| `payments` | `payment_request_id` | CASCADE — eliminato |
| `assembly_attendees` | `member_id` | CASCADE — eliminato |
| `assembly_delegates` | `delegator_member_id` | CASCADE — eliminato |
| `assembly_delegates` | `delegate_member_id` | CASCADE — eliminato |
| `communication_recipients` | `member_id` | CASCADE — eliminato |
| `gdpr_consents` | `member_id` | CASCADE — eliminato |
| `event_registrations` | `member_id` | CASCADE — eliminato |
| `event_registrations` | `payment_request_id` | SET NULL |

La cancellazione di emergenza è l'unica operazione che attiva questi CASCADE. Le operazioni normali non cancellano mai i record socio.

---

## Migration

| File | Descrizione |
|---|---|
| `013_memberships_indexes.sql` | Aggiunto indici, colonne payment_method e payment_reference |
| `014_membership_number_restructure.sql` | Aggiunto membership_number a memberships, formato M/C, impostazioni prefissi |
| `015_fix_membership_number_nullable.sql` | Reso nullable memberships.membership_number |
| `016_cascade_foreign_keys.sql` | Aggiunto ON DELETE CASCADE a tutte le FK collegate ai soci |
