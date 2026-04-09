# Memberships Module

**Socius v0.3.x — Module documentation (updated to v0.3.9)**

---

## Overview

The Memberships module manages annual membership cards for association members. Each membership record represents one member's participation for one social year. The module handles manual card creation, payment registration, card number management, member status synchronisation, and the danger zone for administrative corrections.

---

## Key concepts

### Member number vs card number

Two distinct identifiers coexist in Socius:

**Member number** (`members.member_number`) — permanent sequential integer. Format: `M00001`. Never changes, even after lapse or rejoining.

**Card number** (`memberships.membership_number`) — alphanumeric code assigned when a membership is created. Format: `C00001`. Stable as long as the member renews. Released (set to NULL on `members`) if the member lapses. Historical membership records always retain their card number.

The card number in `memberships.membership_number` is the **source of truth**. The field `members.membership_number` is a denormalized copy updated automatically — never modify it directly.

### Membership status

| DB value | Meaning |
|---|---|
| `pending` | Created but payment not yet received |
| `paid` | Payment confirmed, card active |
| `waived` | Fee waived by board decision (e.g. honorary members) |
| `cancelled` | Cancelled — insertion error or other reason |

`paid` and `waived` are equivalent for all status calculation purposes — both make a member `active`.

### Member status and the renewal cycle

Member status is calculated automatically by the Sync system. The logic is based on the member's most recent membership and today's position in the social year cycle.

| Condition | Member status |
|---|---|
| Has `paid`/`waived` membership for current social year | `active` |
| Has `pending` membership for current year, inside renewal window | `in_renewal` |
| Has `pending` membership for current year, after renewal close | `not_renewed` |
| Has `pending` membership for current year, after lapse date | `lapsed` |
| Had `paid`/`waived` last year, before renewal opens | `active` |
| Had `paid`/`waived` last year, inside renewal window | `in_renewal` |
| Had `paid`/`waived` last year, after renewal close | `not_renewed` |
| Had `paid`/`waived` last year, after lapse date | `lapsed` |
| No recent valid membership | `lapsed` |

The statuses `suspended`, `resigned`, and `deceased` are **never touched by the sync** — they are set manually and always take priority.

### Social year determination

The social year is the year whose memberships are currently being managed. It advances to the next calendar year only after the lapse date has passed.

The `renewal_open` date may belong to the previous calendar year (e.g. November opens renewals for the following year). This is determined by comparing `renewal_open` MM-DD with `renewal_close` MM-DD: if `open > close` as strings, the opening date belongs to `socialYear - 1`. This logic handles any configuration without arbitrary month thresholds.

Examples:
- Nov → Apr cycle: `'11-15' > '04-15'` → renewal_open is in socialYear - 1
- Sep → Jun cycle: `'09-01' > '06-30'` → renewal_open is in socialYear - 1
- Jan → Jun cycle: `'01-02' < '06-30'` → renewal_open is in socialYear itself

---

## Database tables

### `memberships`

One row per member per year.

| Column | Type | Notes |
|---|---|---|
| `id` | INT PK | |
| `member_id` | INT FK → members | ON DELETE CASCADE |
| `membership_number` | VARCHAR(10) NULL | Source of truth for card number |
| `category_id` | INT FK → membership_categories | Category belongs to the membership, not the member |
| `year` | YEAR | Social year |
| `fee` | DECIMAL(8,2) | Fee applied for this year |
| `status` | ENUM | pending, paid, waived, cancelled |
| `valid_from` | DATE | Start of validity |
| `valid_until` | DATE | End of validity |
| `paid_on` | DATE NULL | Date payment was received |
| `payment_method` | ENUM NULL | cash, bank_transfer, paypal, satispay, waived, other |
| `payment_reference` | VARCHAR(255) NULL | Receipt number, bank transfer reference |
| `notes` | TEXT NULL | Internal notes |

Note: the category is linked to the membership record, not to the member. A member can have different categories in different years (e.g. Ordinary in 2024, Honorary in 2025).

### `reserved_member_numbers`

Card numbers permanently reserved — never reassigned.

| Column | Type | Notes |
|---|---|---|
| `membership_number` | VARCHAR(20) UNIQUE | e.g. C00001 |
| `reserved_by` | INT | users.id — no FK, survives user deletion |
| `reason` | VARCHAR(500) NULL | Motivation for reservation |

---

## Pages

| File | Description |
|---|---|
| `public/memberships.php` | Global membership list with filters |
| `public/membership.php?id=N` | Membership detail — read only |
| `public/membership-new.php` | New membership form |
| `public/membership-edit.php?id=N` | Edit membership + danger zone |

---

## Available years for new membership

The year select in the membership form is populated dynamically from two sources:

1. Years present in `membership_category_fees` (years for which fees have been configured in settings)
2. The current year — always included even without configured fees

This means an admin should configure fees for a year before creating memberships for that year. The current year is always available as a fallback using the category default fee.

Duplicate check: if a membership already exists for the selected member and year, the system blocks creation and shows a link to the existing record.

---

## New membership form

URL: `membership-new.php` or `membership-new.php?member_id=N`

**Member selection**: live search field using `api/members-search.php`. Searches by name, surname, or member number (M00001). Minimum 2 characters, debounce 300ms.

**After creation**:
- If payment method is not "none": creates `payment_requests` and `payments` records, sets status to `paid`
- If category `is_exempt_from_renewal = true`: sets status to `waived`
- Updates `members.membership_number` with the new card number
- Recalculates and updates `members.status` immediately for this specific member

---

## Membership edit and danger zone

URL: `membership-edit.php?id=N`

**Normal edit** (admin and secretary): status, fee, payment date, payment method, notes.

**Danger zone** (super_admin only) — UIkit accordion, closed by default. Each operation requires mandatory motivation (min 10 chars) and is recorded in `audit_logs`.

| Operation | Description |
|---|---|
| Reserve card number | Permanently reserves the card number — never reassigned. Saved to `reserved_member_numbers`. |
| Change card number | Assigns a different available card number |
| Force membership status | Changes status bypassing normal flow |
| Correct paid fee | Fixes the recorded amount — use only for insertion errors |
| Force member status | Changes the member's status bypassing the renewal cycle |

---

## Sync system

The Sync system recalculates member statuses automatically. It runs once per day — triggered by the first login of the day. It can also be forced manually at any time via the navbar icon.

### How it works

On every login, the system checks `system.last_sync_date` in settings. If the date differs from today, it redirects to `sync-run.php` which calls `sync.php?action=run` via AJAX, shows a spinner while processing, then redirects back to the page the user was on (or to dashboard).

### sync.php endpoints

| Action | Description |
|---|---|
| `?action=run` | Execute full recalculation for all members |
| `?action=status` | Return current sync metadata as JSON |

Response format:
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

### Sync navbar indicator

The navbar shows a Lucide icon indicating the current sync state. Lucide is loaded via CDN in `layout.php`.

| State | Icon | Colour |
|---|---|---|
| Synced today | `cloud-check` | Green (#28a745) |
| Not yet synced | `cloud` | Orange (#fd7e14) |

Clicking the icon triggers `sync-run.php?return={current_url}` — after sync the user returns to the page they were on.

### Settings keys used by sync

| Key | Description |
|---|---|
| `system.last_sync_date` | Date of last recalculation (Y-m-d) |
| `system.last_sync_count` | Number of members updated in last sync |
| `system.last_sync_duration_ms` | Duration of last sync in milliseconds |

### Per-member immediate recalculation

When a membership is created or its status changes, the system immediately recalculates the status for that specific member without waiting for the daily sync.

---

## calculate_member_status()

Defined in `public/_init.php`. Takes a member record (with most recent membership data joined) and the full settings array. Returns the calculated status string.

Members with status `suspended`, `resigned`, or `deceased` are excluded from sync — these statuses are never overwritten automatically.

---

## Internal API family

All endpoints require authentication. All return JSON.

| Endpoint | Parameters | Used by |
|---|---|---|
| `api/members-search.php` | `?q=&limit=&status=` | Member search in forms |
| `api/member.php` | `?id=` | Pre-fill form after member selection |
| `api/members-list.php` | `?status=&category_id=&board=&year=&page=` | Filtered list for future modules |
| `api/member-stats.php` | `?year=` | Aggregate statistics (cached 5 min) |

---

## CSS badges

Defined globally in `public/themes/uikit/layout.php`:

```css
.badge-member-number { font-family: monospace; background: #E8F0FE; color: #1A3A6B; }
.badge-card-number   { font-family: monospace; background: #E6F4EA; color: #1B5E2F; }
```

---

## Foreign key constraints

All tables linked to `members` use `ON DELETE CASCADE`. Emergency deletion of a member triggers all cascades in a single transaction, removing all linked records including memberships, payments, and payment requests.

---

## Migrations

| File | Description |
|---|---|
| `013_memberships_indexes.sql` | Indexes, payment_method, payment_reference columns |
| `014_membership_number_restructure.sql` | membership_number in memberships, M/C format, prefix settings |
| `015_fix_membership_number_nullable.sql` | membership_number nullable in memberships |
| `016_cascade_foreign_keys.sql` | ON DELETE CASCADE on all member-linked FK constraints |
| `017_member_status_sync.sql` | email nullable on members, remove category_id from members, sync settings keys |
