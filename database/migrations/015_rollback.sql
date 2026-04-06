-- Rollback for migration 015
ALTER TABLE `memberships`
  DROP KEY `idx_member_year`,
  DROP COLUMN `payment_method`,
  DROP COLUMN `payment_reference`;
