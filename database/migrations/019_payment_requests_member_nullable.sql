-- =============================================================================
-- Migration 019: Make payment_requests.member_id nullable
-- =============================================================================
--
-- payment_requests.member_id was NOT NULL with a RESTRICT FK.
-- This caused emergencyDelete() to fail: it tried to SET member_id = NULL
-- before deleting the member, but the NOT NULL constraint blocked it.
--
-- Fix:
--   1. Drop the existing FK (RESTRICT — would block member deletion anyway)
--   2. Make member_id nullable
--   3. Re-add FK with ON DELETE SET NULL so member deletion auto-nullifies
--      the reference — preserving payment records for audit purposes.
--
-- After this migration, emergencyDelete() no longer needs to manually
-- UPDATE payment_requests before deleting the member row.
-- =============================================================================

-- 1. Drop the old RESTRICT FK
ALTER TABLE `payment_requests`
  DROP FOREIGN KEY `fk_payment_requests_member`;

-- 2. Make the column nullable
ALTER TABLE `payment_requests`
MODIFY COLUMN `member_id` INT UNSIGNED NULL DEFAULT NULL
  COMMENT 'NULL if member was emergency-deleted — payment record preserved for audit';

-- 3. Re-add FK with ON DELETE SET NULL
ALTER TABLE `payment_requests`
  ADD CONSTRAINT `fk_payment_requests_member`
  FOREIGN KEY (`member_id`) REFERENCES `members` (`id`)
  ON DELETE SET NULL;
