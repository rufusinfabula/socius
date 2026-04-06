# Settings Module

**Socius v0.2.x — Module documentation**

---

## Overview

The Settings module is the configuration centre of Socius. It allows administrators to configure every aspect of the system from the back-end without touching any file. All settings are stored in the `settings` table as key-value pairs and are applied system-wide at runtime.

---

## Access

Only users with role `admin` (role_id ≤ 2) can access the settings panel. The `super_admin` role has full access including the Member Number section.

URL: `settings.php` or `settings.php?tab=section_name`

---

## Sections

The panel is organised in seven tabs:

| Tab | URL parameter | Description |
|---|---|---|
| Association | `?tab=association` | Name, fiscal code, VAT, address, logo |
| Social Year | `?tab=social_year` | Renewal cycle dates |
| Member Categories | `?tab=categories` | Category management and annual fees |
| Board Roles | `?tab=board_roles` | Board role management |
| Interface | `?tab=interface` | Theme, language, date format, timezone |
| Email | `?tab=email` | SMTP configuration and test |
| Member Number | `?tab=member_number` | Sequential number counter |

---

## Section details

### Association

Stores the association's identity data. All fields are optional — the system works without them, but they are used in communications, minutes headers, and invoices.

| Setting key | Description |
|---|---|
| `association.name` | Association name |
| `association.fiscal_code` | Fiscal code (codice fiscale) |
| `association.vat_number` | VAT number (partita IVA) — only if applicable |
| `association.address` | Registered address |
| `association.city` | City |
| `association.postal_code` | Postal code |
| `association.province` | Province (2 letters) |
| `association.country` | Country ISO code (default: IT) |
| `association.email` | Official email |
| `association.phone` | Phone number |
| `association.website` | Website URL |
| `association.logo_path` | Relative path to uploaded logo |

**Logo upload**: accepts PNG, JPG, SVG up to 2MB. Saved as `public/storage/uploads/logo/logo.{ext}`. The navbar displays the logo if present, otherwise shows the association name as text.

To remove the logo, check the "Remove logo" checkbox before saving.

---

### Social Year

Configures the dates of the annual renewal cycle. All dates are stored as `MM-DD` (month-day without year) — the system applies the current year at runtime.

| Setting key | Default | Description |
|---|---|---|
| `renewal.date_open` | `11-15` | Renewal period opens |
| `renewal.date_first_reminder` | `02-15` | First renewal communication |
| `renewal.date_second_reminder` | `03-15` | Second reminder |
| `renewal.date_third_reminder` | `04-15` | Third reminder / last notice |
| `renewal.date_close` | `04-15` | Renewal period closes |
| `renewal.date_lapse` | `12-31` | Automatic lapse for non-renewed members |
| `renewal.reminder_approval` | `true` | Require admin approval before sending reminders |

**Input interface**: each date uses a month select (in the current interface language) and a day input with − and + buttons. The right-hand summary shows the dates currently saved in the database — it updates after saving, not in real time.

**How dates work in the renewal cycle**:
- Between `date_open` and `date_close`: member status → `in_renewal`
- After `date_close` until `date_lapse`: member status → `not_renewed`
- After `date_lapse`: member status → `lapsed`, membership number released

---

### Member Categories

Full CRUD management of membership categories. Each association defines its own categories — there are no mandatory categories. Categories created here appear in the member form and in the renewal system.

**Category fields**:

| Field | Description |
|---|---|
| `name` | Internal slug (lowercase letters and underscore only) |
| `label` | Display name shown in the UI |
| `description` | Shown in the registration form |
| `annual_fee` | Default fee for this category |
| `is_free` | If true, no payment required (e.g. Honorary) |
| `is_exempt_from_renewal` | If true, renewal system skips this category |
| `requires_approval` | Registration requires board approval |
| `valid_from` | Category available from this date |
| `valid_until` | Category expires on this date (e.g. Under 30) |
| `is_active` | Hidden from registration if false |
| `sort_order` | Display order in selects |

**Annual fee history**: each category can have different fees per year. The system resolves the applicable fee in this order:
1. Record in `membership_category_fees` for the current year
2. Most recent year available in `membership_category_fees`
3. `annual_fee` from `membership_categories`

To add or update the fee for a specific year, use the fee history panel within each category row.

---

### Board Roles

Full CRUD management of board roles. Default roles are seeded at installation but can be modified or deleted. Each association can define its own role structure.

**Role fields**:

| Field | Description |
|---|---|
| `name` | Internal slug |
| `label` | Display name |
| `description` | Optional description |
| `is_board_member` | TRUE = board member, FALSE = technical role (e.g. auditor) |
| `can_sign` | TRUE = can sign official documents |
| `is_active` | Hidden from member form if false |
| `sort_order` | Display order |

**Default roles**: President, Vice President, Secretary, Treasurer, Board Member, Auditor.

---

### Interface

| Setting key | Default | Description |
|---|---|---|
| `ui.theme` | `uikit` | Active CSS theme |
| `ui.locale` | `it` | Interface language |
| `ui.date_format` | `d/m/Y` | Date display format |
| `ui.timezone` | `Europe/Rome` | Timezone for dates and times |

**Theme detection**: the system scans `public/themes/` for subdirectories containing a `layout.php` file. Each theme can include a `theme.json` file with metadata:

```json
{
  "name": "UIkit 3",
  "description": "Default theme",
  "version": "1.0.0",
  "author": "Socius Team",
  "status": "stable"
}
```

Themes with `"status": "wip"` are shown with a "work in progress" label and trigger a warning if selected.

**Language detection**: the system scans `lang/` for subdirectories containing a `messages.php` file. To add a new language, create a folder with the ISO code (e.g. `lang/de/`) and add the translation files.

**Date format**: applies to all date displays in the system via the `format_date()` helper. Available formats:

| Format | Example |
|---|---|
| `d/m/Y` | 15/11/2026 |
| `d/m/y` | 15/11/26 |
| `d F Y` | 15 November 2026 |
| `m/d/Y` | 11/15/2026 |
| `Y-m-d` | 2026-11-15 (ISO) |

Language changes are applied immediately to the current session without requiring a new login.

---

### Email

SMTP configuration for outgoing emails. The password is stored encrypted using the `APP_KEY` from `.env`.

| Setting key | Default | Description |
|---|---|---|
| `smtp.host` | — | SMTP server hostname |
| `smtp.port` | `587` | Port (587 for TLS, 465 for SSL, 25 for plain) |
| `smtp.encryption` | `tls` | Encryption: `tls`, `ssl`, or `none` |
| `smtp.username` | — | SMTP username |
| `smtp.password` | — | SMTP password (stored encrypted) |
| `smtp.from_address` | — | Sender email address |
| `smtp.from_name` | — | Sender display name |

**Test email**: the panel includes a "Send test email" button that connects to the SMTP server and sends a test message to the currently logged-in admin's email address. The connection is tested using a raw PHP socket — no external library required.

---

### Member Number

Controls the sequential member number counter.

| Setting key | Description |
|---|---|
| `members.number_start` | Starting number for new installations |
| `members.next_number` | Next number to be assigned |

**Reset for production**: when moving from development to production, use this section to reset the counter to 1 (or any desired starting number). The new value must be higher than the current maximum assigned number — the system prevents creating duplicate numbers.

---

## Database

### `settings` table

All settings are stored in the `settings` table as key-value pairs.

| Column | Type | Description |
|---|---|---|
| `key` | VARCHAR(100) UNIQUE | Dot-notation key (e.g. `association.name`) |
| `value` | TEXT | Stored value |
| `type` | ENUM | `string`, `integer`, `boolean`, `json`, `date` |
| `group` | VARCHAR(50) | Logical group (e.g. `association`, `renewal`, `ui`) |
| `label` | VARCHAR(255) | Human-readable label for the back-end |

---

## Model

**`app/Models/Setting.php`**

| Method | Description |
|---|---|
| `get(string $key, mixed $default)` | Read a single setting with in-memory cache |
| `set(string $key, mixed $value)` | Write a single setting |
| `setMultiple(array $keyValues)` | Write multiple settings in a transaction |
| `getGroup(string $group)` | All settings for a group |
| `getAllGroups()` | All settings organised by group |
| `encryptPassword(string $plain)` | Encrypt using APP_KEY |
| `decryptPassword(string $encrypted)` | Decrypt using APP_KEY |

---

## Helpers

**`format_date(string $date, bool $withTime = false): string`**

Formats a date string according to `ui.date_format` from settings. Returns `—` for empty or invalid dates. Used in all templates to display dates consistently.

```php
echo format_date($member['birth_date']);        // 15/11/1985
echo format_date($member['created_at'], true);  // 15/11/2026 14:30
```

**`format_date_iso(string $date): string`**

Returns a date in `Y-m-d` format for use in `input type="date"` value attributes. Always ISO regardless of the display format setting.

---

## Theme system

To create a new theme:

1. Create a folder in `public/themes/your-theme-name/`
2. Create `layout.php` with the base HTML structure (navbar, sidebar, content area, footer)
3. Create template files for each page: `members.php`, `member.php`, `member-form.php`, etc.
4. Create `theme.json` with theme metadata
5. The theme appears automatically in the Interface settings panel

Templates receive pre-processed PHP variables — no database queries inside templates.

---

## Migrations

| File | Description |
|---|---|
| `012_date_format_setting.sql` | Added `ui.date_format` setting with default `d/m/Y` |
