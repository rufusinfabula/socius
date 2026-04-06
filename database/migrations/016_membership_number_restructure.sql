-- =============================================================================
-- Migration 016: membership number restructure
-- =============================================================================
--
-- MEMBER NUMBER vs CARD NUMBER
--
-- Member number (members.member_number):
--   - Permanent sequential integer, assigned once at first registration
--   - Never changes, even if the member lapses and rejoins
--   - Displayed as: M + 5 digits → M00001
--   - Prefix configurable: members.number_prefix (default 'M')
--
-- Card number (memberships.membership_number  ←  SOURCE OF TRUTH):
--   - Alphanumeric code assigned when a membership record is created
--   - Stored per-membership row so historical records keep their number
--   - Format: C + 5 digits → C00001
--   - Prefix configurable: members.card_prefix (default 'C')
--   - members.membership_number is a DENORMALIZED COPY updated automatically
--
-- Changes:
--   1. Add memberships.membership_number (source of truth)
--   2. Migrate members.membership_number format SOCxxxx → Cxxxxx
--   3. Backfill memberships.membership_number for current-year records
--   4. Add/update settings keys
-- =============================================================================

-- 1. Add card number column to memberships (source of truth)
ALTER TABLE `memberships`
  ADD COLUMN `membership_number` VARCHAR(10) NULL DEFAULT NULL
    COMMENT 'Card number for this membership record — format C00001. Source of truth.'
    AFTER `member_id`;

-- 2. Migrate members.membership_number from SOCxxxx → Cxxxxx (5-digit padding)
UPDATE `members`
SET `membership_number` = CONCAT(
  'C',
  LPAD(
    CAST(REGEXP_REPLACE(`membership_number`, '[^0-9]', '') AS UNSIGNED),
    5, '0'
  )
)
WHERE `membership_number` IS NOT NULL
  AND `membership_number` != ''
  AND `membership_number` REGEXP '[0-9]';

-- 3. Nullify any members.membership_number that did not convert to valid Cxxxxx format
UPDATE `members`
SET `membership_number` = NULL
WHERE `membership_number` IS NOT NULL
  AND `membership_number` NOT REGEXP '^C[0-9]{5}$';

-- 4. Backfill memberships.membership_number from members for current-year records
UPDATE `memberships` ms
  JOIN `members` m ON m.id = ms.member_id
SET ms.`membership_number` = m.`membership_number`
WHERE m.`membership_number` IS NOT NULL
  AND ms.year = YEAR(CURDATE());

-- 5. Add new settings keys; update number_prefix from 'SOC' to 'M'
UPDATE `settings`
  SET `value` = 'M', `group` = 'members'
WHERE `key` = 'members.number_prefix';

INSERT IGNORE INTO `settings` (`key`, `value`, `type`, `group`, `label`) VALUES
  ('members.card_prefix',    'C', 'string',  'members', 'Card number prefix (e.g. C → C00001)'),
  ('members.number_digits',  '5', 'integer', 'members', 'Digit count for member and card numbers (zero-padded)');
