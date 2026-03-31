# Members Module

**Socius v0.1.x — Module documentation**

---

## Overview

The Members module is the core of Socius. It manages the complete lifecycle of an association member, from first registration to lapse or resignation, including board roles, membership categories, and emergency deletion.

---

## Key concepts

### Member number vs membership number

Every member has two distinct identifiers:

**Member number** (`members.member_number`) — a permanent sequential integer assigned at first registration. It never changes, even if the member lapses and rejoins years later. This is the member's identity in the system.

**Membership number** (`members.membership_number`) — an alphanumeric code assigned at registration (format: `SOC0001`). It is released only if the member fails to renew by the final renewal deadline (e.g. 31 December). A member who renews regularly keeps the same membership number for years.

### Member status

Status values are stored in English in the database and translated only in the UI.

| DB value | Meaning |
|---|---|
| `active` | Has a valid membership for the current year |
| `in_renewal` | In the renewal period, not yet renewed |
| `not_renewed` | Renewal deadline passed, still within 31 December |
| `lapsed` | Did not renew by 31 December — membership number released |
| `suspended` | Suspended by board decision |
| `resigned` | Voluntary resignation |
| `deceased` | Deceased |

### Honorary members

Honorary members are not a status — they are a **category** (`membership_categories`) with `is_exempt_from_renewal = true` and `is_free = true`. The renewal system skips them entirely. Their status remains `active` permanently unless changed manually.

---

## Database tables

### `members`

Core table. Records are **never physically deleted**. Emergency deletion is the only exception and requires super admin confirmation.

| Column | Type | Notes |
|---|---|---|
| `id` | INT PK | Internal auto-increment |
| `member_number` | INT UNIQUE | Permanent sequential number — never changes |
| `membership_number` | VARCHAR(20) | Alphanumeric code — released on lapse |
| `name` | VARCHAR(100) | |
| `surname` | VARCHAR(100) | |
| `email` | VARCHAR(255) | |
| `phone1` | VARCHAR(30) | Primary phone |
| `phone2` | VARCHAR(30) | Secondary phone |
| `birth_date` | DATE | |
| `birth_place` | VARCHAR(100) | |
| `sex` | ENUM('M','F') | For fiscal code calculation |
| `gender` | VARCHAR(50) | Identity — optional, GDPR sensitive |
| `fiscal_code` | VARCHAR(16) UNIQUE | Nullable — multiple members may have no fiscal code |
| `address` | VARCHAR(255) | |
| `city` | VARCHAR(100) | |
| `postal_code` | VARCHAR(10) | |
| `province` | VARCHAR(5) | |
| `country` | CHAR(2) | ISO 3166-1 alpha-2, default 'IT' |
| `category_id` | INT FK | References `membership_categories` |
| `status` | ENUM | See status table above |
| `joined_on` | DATE | Date of first registration |
| `resigned_on` | DATE | Date of voluntary resignation |
| `notes` | TEXT | Internal notes — visible to admin/staff only |
| `created_by` | INT FK | users.id |

### `membership_categories`

Configurable from the back-end settings panel. Each association defines its own categories.

| Column | Type | Notes |
|---|---|---|
| `name` | VARCHAR(100) UNIQUE | Internal slug (e.g. `ordinary`, `honorary`) |
| `label` | VARCHAR(255) | Display name |
| `annual_fee` | DECIMAL(8,2) | Default fee |
| `is_free` | BOOLEAN | No payment required |
| `is_exempt_from_renewal` | BOOLEAN | Not subject to annual renewal |
| `requires_approval` | BOOLEAN | Registration requires board approval |
| `valid_from` | DATE | Category available from this date |
| `valid_until` | DATE | Category expires on this date (e.g. Under 30) |
| `is_active` | BOOLEAN | Hidden from registration if false |
| `sort_order` | TINYINT | Display order |

### `membership_category_fees`

Annual fee history per category. Allows the admin to confirm or change fees each year without losing historical data.

| Column | Type | Notes |
|---|---|---|
| `category_id` | INT FK | |
| `year` | YEAR | Social year |
| `fee` | DECIMAL(8,2) | Fee applied for this year |
| `approved_by` | INT FK | users.id of the admin who set the fee |
| UNIQUE | (category_id, year) | One fee per category per year |

Fee resolution order:
1. Look for a record in `membership_category_fees` for the current year
2. If not found, use the most recent year available
3. If not found, use `membership_categories.annual_fee`

### `board_roles`

Configurable catalog of board roles. Each association defines its own roles.

| Column | Type | Notes |
|---|---|---|
| `name` | VARCHAR(50) UNIQUE | Internal slug (e.g. `president`) |
| `label` | VARCHAR(100) | Display name |
| `description` | TEXT | Optional description |
| `is_board_member` | BOOLEAN | TRUE = board member, FALSE = technical role (e.g. auditor) |
| `can_sign` | BOOLEAN | TRUE = can sign official documents |
| `is_active` | BOOLEAN | |
| `sort_order` | TINYINT | |

Default roles seeded at installation: President, Vice President, Secretary, Treasurer, Board Member, Auditor.

### `board_memberships`

Historical record of who holds which board role and when.

| Column | Type | Notes |
|---|---|---|
| `member_id` | INT FK | |
| `role_id` | INT FK | |
| `elected_on` | DATE | Date of election or appointment |
| `expires_on` | DATE | End of mandate — NULL = indefinite |
| `resigned_on` | DATE | Date of resignation from role |
| `elected_by_assembly_id` | INT FK | Assembly that deliberated the appointment |
| `notes` | TEXT | |

A member is considered currently in a role when `expires_on IS NULL OR expires_on >= TODAY` AND `resigned_on IS NULL`.

### `reserved_member_numbers`

Tracks membership numbers that must not be reused after an emergency deletion where the operator chose to keep the number reserved.

---

## Pages

| File | Description |
|---|---|
| `public/members.php` | Member list with filters, status badges, pagination |
| `public/member.php?id=N` | Member profile — read only |
| `public/member-new.php` | New member form |
| `public/member-edit.php?id=N` | Edit member form |
| `public/member-delete.php?id=N` | Emergency deletion confirmation |

---

## Form layout

The member form is organised in three sections:

**Registry box** (left, 2/3 width): surname, name, sex, gender, birth date, birth place, fiscal code with calculate button.

**Member box** (right, 1/3 width): membership number (read-only), member number (read-only), status, category, joined on, internal notes.

**Contacts box** (full width, below): email, phone 1, phone 2, address, postal code, city, province, country.

**Board role box** (full width, below contacts): role select, start date, notes. Creates or updates a record in `board_memberships`.

**Danger zone** (full width, at the bottom, red border): emergency deletion button — visible to super admin only.

---

## Member profile layout

The profile page follows the same three-box structure in read-only mode.

At the top of the Member box, active board roles are shown as UIkit badges:
- `is_board_member = true` → primary colour badge
- `is_board_member = false` → grey badge (technical role)

---

## Emergency deletion

Emergency deletion is a controlled procedure accessible only to `super_admin`. It requires:

1. Review of all linked data (memberships, payments)
2. Choice: free the membership number or keep it reserved
3. Typing `DELETE` (case sensitive) in a confirmation field
4. CSRF token validation

The procedure: writes to `audit_logs` → deletes `memberships` → nullifies `payment_requests.member_id` → deletes `members`. Payments are preserved. If the number is kept reserved, a record is inserted in `reserved_member_numbers`.

---

## Models

| File | Key methods |
|---|---|
| `app/Models/Member.php` | `findAll()`, `findById()`, `create()`, `update()`, `getNextMemberNumber()`, `emergencyDelete()` |
| `app/Models/MembershipCategory.php` | `findAll()`, `getFeeForYear()`, `setFeeForYear()`, `getFeesHistory()` |
| `app/Models/BoardRole.php` | `findAll()`, `getCurrentBoard()`, `getMemberRoles()`, `isCurrentBoardMember()` |
| `app/Models/BoardMembership.php` | `create()`, `update()`, `findByMember()`, `findCurrent()` |

---

## Internationalisation

All member-related strings are in `lang/it/members.php` and `lang/en/members.php`. Board-specific strings are in `lang/it/board.php` and `lang/en/board.php`.

Database values (status, category name, role name) are always stored in English. Translation happens only at display time via `__('members.status_active')` etc.

---

## Migrations

| File | Description |
|---|---|
| `001_initial_schema.sql` | Full initial schema — all 21 tables |
| `004_add_sex_gender_to_members.sql` | Added sex and gender fields |
| `005_rename_phone_fields.sql` | phone → phone1, mobile → phone2 |
| `006_fix_member_status_enum.sql` | Status ENUM aligned with spec |
| `007_membership_categories.sql` | Extended categories, added fee history table |
| `008_rename_italian_fields.sql` | Renamed sesso→sex, genere→gender, anno→year etc. |
| `009_member_number_and_board.sql` | Added member_number, board type for assemblies |
| `010_fix_fiscal_code_unique.sql` | fiscal_code UNIQUE allows multiple NULLs |
| `011_board_roles.sql` | Created board_roles and board_memberships |
