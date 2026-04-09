# Memberships Module

**Socius v0.3.x — Module documentation**

---

## Overview

The Memberships module manages annual membership cards for association members. Each membership record represents one member's participation for one social year. The module handles manual card creation, payment registration, card number management, and the danger zone for administrative corrections.

---

## Key concepts

### Member number vs card number

Two distinct identifiers coexist in Socius:

**Member number** (`members.member_number`) — permanent sequential integer. Format: `M00001`. Never changes, even after lapse or rejoining.

**Card number** (`memberships.membership_number`) — alphanumeric code assigned when a membership is created. Format: `C00001`. Stable as long as the member renews. Released (set to NULL on `members`) if the member lapses. Historical membership records always retain their card number.

The card number in `memberships.membership_number` is the **source of truth**. The field `members.membership_number` is a denormalized copy updated automatically by the system — never modify it directly.

### Membership status

| DB value | Meaning |
|---|---|
| `pending` | Created but payment not yet received |
| `paid` | Payment confirmed, card active |
| `waived` | Fee waived (e.g. honorary members, board decision) |
| `cancelled` | Cancelled — insertion error or other reason |

### Card number lifecycle

```
Member created → membership_number = NULL on members
                 (no card yet)

Membership created → card number assigned (e.g. C00001)
                     stored in memberships.membership_number
                     copied to members.membership_number

Member renews → same card number maintained
                new membership record for new year

Member lapses → members.membership_number = NULL
                historical membership retains C00001
                number available for reassignment

Emergency deletion → all data removed including memberships
```

---

## Database tables

### `memberships`

One row per member per year.

| Column | Type | Notes |
|---|---|---|
| `id` | INT PK | |
| `member_id` | INT FK → members | ON DELETE CASCADE |
| `membership_number` | VARCHAR(10) NULL | Source of truth for card number |
| `category_id` | INT FK → membership_categories | |
| `year` | YEAR | Social year |
| `fee` | DECIMAL(8,2) | Fee applied for this year |
| `status` | ENUM | pending, paid, waived, cancelled |
| `valid_from` | DATE | Start of validity |
| `valid_until` | DATE | End of validity (usually 31 Dec) |
| `paid_on` | DATE NULL | Date payment was received |
| `payment_method` | ENUM NULL | cash, bank_transfer, paypal, satispay, waived, other |
| `payment_reference` | VARCHAR(255) NULL | Receipt number, bank transfer reference |
| `notes` | TEXT NULL | Internal notes |

### `reserved_member_numbers`

Card numbers that must never be reassigned.

| Column | Type | Notes |
|---|---|---|
| `id` | INT PK | |
| `membership_number` | VARCHAR(20) UNIQUE | e.g. C00001 |
| `reserved_at` | TIMESTAMP | |
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

## Membership list

URL: `memberships.php`

Filters: year (default: current), status, category.
Columns: Member No. | Surname Name | Category | Year | Card No. | Fee | Status | Paid on | Actions.

Both M and C numbers are displayed with their respective CSS badges.

---

## New membership form

URL: `membership-new.php` or `membership-new.php?member_id=N`

**Member selection**: live search field using `api/members-search.php`. Searches by name, surname, or member number (M00001). Minimum 2 characters, debounce 300ms. Results show name and M badge. If `?member_id` is present in the URL (arriving from member profile), the field is pre-filled.

**Form fields**:

*Membership box:*
- Member (search or pre-filled)
- Social year (current or next)
- Card number (proposed automatically, editable)
- Category (active categories only)
- Fee (pre-filled from category, editable)
- Status (default: pending)

*Payment box (hidden if status = waived or cancelled):*
- Payment method
- Payment date (default: today)
- Reference (receipt number, bank transfer reference)
- Notes

**Card number assignment**: `next_card_number()` finds `MAX(numeric part of membership_number)` across `members` and `reserved_member_numbers`, then returns prefix + (max + 1). The proposed number is shown with the `.badge-card-number` style and can be overridden by the admin.

**After creation**:
- If payment method is not "none": creates `payment_requests` and `payments` records, sets membership status to `paid`
- If category `is_exempt_from_renewal = true`: sets status to `waived`
- Updates `members.membership_number` with the new card number
- Updates `members.status` to `active` if membership is paid or waived

---

## Membership edit and danger zone

URL: `membership-edit.php?id=N`

**Normal edit** (admin and secretary): status, fee, payment date, payment method, notes.

**Danger zone** (super_admin only) — UIkit accordion, closed by default:

Each operation requires a mandatory motivation (min 10 characters) and is recorded in `audit_logs`.

| Operation | Description |
|---|---|
| Reserve card number | Permanently reserves the card number — never reassigned |
| Change card number | Assigns a different available card number |
| Force membership status | Changes status bypassing normal flow |
| Correct paid fee | Fixes the recorded amount (insertion error correction) |
| Force member status | Changes the member's status bypassing the renewal cycle |

---

## Member profile — membership history

In `member.php`, a "Membership history" section shows all memberships for the member ordered by year descending.

Columns: Year | Card No. | Category | Fee | Status | Actions (Detail / Edit).

Button "New membership for this member" links to `membership-new.php?member_id=N`.

---

## Internal API

The membership module uses and contributes to the internal API family in `public/api/`:

| Endpoint | Used by |
|---|---|
| `api/members-search.php?q=` | membership-new.php member search field |
| `api/member.php?id=` | Pre-fill category after member selection |

---

## Models

**`app/Models/Membership.php`**

| Method | Description |
|---|---|
| `findAll(array $filters, int $page, int $perPage)` | Paginated list with filters |
| `findById(int $id)` | Single membership with member and category data |
| `findByMember(int $memberId)` | All memberships for one member |
| `getCurrentForMember(int $memberId)` | Current year membership for a member |
| `create(array $data)` | Create membership, assign card number, update member |
| `update(int $id, array $data)` | Update fields, sync member card number if current year |
| `releaseCardNumber(int $memberId)` | Set members.membership_number = NULL on lapse |
| `getNextAvailableNumber()` | Returns next available card number string |
| `getYearsWithMemberships()` | Distinct years present in memberships |

**`app/Models/Member.php`** (methods relevant to memberships)

| Method | Description |
|---|---|
| `updateCardNumber(int $memberId, ?string $cardNumber)` | Update denormalized card number on member record |

---

## CSS badges

Two global CSS classes defined in `public/themes/uikit/layout.php`:

```css
.badge-member-number {
    font-family: monospace;
    background: #E8F0FE;
    color: #1A3A6B;
    /* blue — permanent identifier */
}

.badge-card-number {
    font-family: monospace;
    background: #E6F4EA;
    color: #1B5E2F;
    /* green — active card */
}
```

Usage in templates:
```html
<span class="badge-member-number">M00001</span>
<span class="badge-card-number">C00001</span>
```

---

## Helper functions

**`next_card_number(): string`**
Generates the next available card number. Reads MAX from `members.membership_number` and `reserved_member_numbers`, returns prefix + (max + 1) zero-padded.

**`format_member_number(?int $number): string`**
Formats a raw integer as M00001. Returns `—` for null.

**`format_card_number(?string $number): string`**
Returns the card number string or `—` for null/empty.

Prefix and digit count are configurable in settings:
- `members.number_prefix` (default: M)
- `members.card_prefix` (default: C)
- `members.number_digits` (default: 5)

---

## Foreign key constraints

All tables linked to `members` use `ON DELETE CASCADE`:

| Table | Column | Behaviour on member delete |
|---|---|---|
| `memberships` | `member_id` | CASCADE — deleted |
| `payments` | `member_id` | CASCADE — deleted |
| `payment_requests` | `member_id` | CASCADE — deleted |
| `payments` | `payment_request_id` | CASCADE — deleted |
| `assembly_attendees` | `member_id` | CASCADE — deleted |
| `assembly_delegates` | `delegator_member_id` | CASCADE — deleted |
| `assembly_delegates` | `delegate_member_id` | CASCADE — deleted |
| `communication_recipients` | `member_id` | CASCADE — deleted |
| `gdpr_consents` | `member_id` | CASCADE — deleted |
| `event_registrations` | `member_id` | CASCADE — deleted |
| `event_registrations` | `payment_request_id` | SET NULL |

Emergency deletion is the only operation that triggers these cascades. Normal operations never delete member records.

---

## Migrations

| File | Description |
|---|---|
| `013_memberships_indexes.sql` | Added indexes, payment_method and payment_reference columns |
| `014_membership_number_restructure.sql` | Added membership_number to memberships, M/C format, settings prefixes |
| `015_fix_membership_number_nullable.sql` | Made memberships.membership_number nullable |
| `016_cascade_foreign_keys.sql` | Added ON DELETE CASCADE to all member-linked FK constraints |
