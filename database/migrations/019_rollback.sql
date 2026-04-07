-- =============================================================================
-- Rollback for Migration 019
-- =============================================================================
--
-- WARNING: will fail if any payment_requests row has member_id = NULL.
-- Set all NULL values to a valid member_id before running this rollback.
-- =============================================================================

ALTER TABLE `payment_requests`
  DROP FOREIGN KEY `fk_payment_requests_member`;

ALTER TABLE `payment_requests`
MODIFY COLUMN `member_id` INT UNSIGNED NOT NULL
  COMMENT 'Member who made this payment request';

ALTER TABLE `payment_requests`
  ADD CONSTRAINT `fk_payment_requests_member`
  FOREIGN KEY (`member_id`) REFERENCES `members` (`id`);
