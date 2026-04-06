-- =============================================================================
-- Rollback for Migration 017: revert membership_number to NOT NULL DEFAULT ''
-- =============================================================================
--
-- WARNING: this will fail if any rows have NULL in membership_number.
-- Clear or fill NULL values first before running this rollback.
-- =============================================================================

ALTER TABLE `memberships`
MODIFY COLUMN `membership_number` VARCHAR(10) NOT NULL DEFAULT ''
  COMMENT 'Card number assigned at membership creation';
