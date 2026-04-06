-- Rollback for migration 016
-- WARNING: this does NOT restore original SOCxxxx formats — run only on dev/test.

ALTER TABLE `memberships`
  DROP COLUMN `membership_number`;

UPDATE `settings` SET `value` = 'SOC' WHERE `key` = 'members.number_prefix';

DELETE FROM `settings` WHERE `key` IN ('members.card_prefix', 'members.number_digits');
