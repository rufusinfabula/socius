-- =============================================================================
-- Rollback for Migration 018
-- =============================================================================
--
-- WARNING: this will fail if any row has NULL in membership_number.
-- Set all NULL values to '' or a valid number before running this rollback.
-- =============================================================================

ALTER TABLE `members`
MODIFY COLUMN `membership_number` VARCHAR(20) NOT NULL
  COMMENT 'Denormalized copy of current card number';
