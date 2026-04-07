-- Migration 016: Cascade foreign keys on members
-- Documents the FK changes applied manually to the development database.
-- Ensures ON DELETE CASCADE for all tables referencing members,
-- so that Member::emergencyDelete() only needs to delete the member row.

-- Step 1: Drop existing FK constraints
ALTER TABLE `payments` DROP FOREIGN KEY IF EXISTS `fk_payments_request`;
ALTER TABLE `payments` DROP FOREIGN KEY IF EXISTS `fk_payments_member`;
ALTER TABLE `memberships` DROP FOREIGN KEY IF EXISTS `fk_memberships_member`;
ALTER TABLE `assembly_attendees` DROP FOREIGN KEY IF EXISTS `fk_assembly_att_member`;
ALTER TABLE `assembly_delegates` DROP FOREIGN KEY IF EXISTS `fk_delegates_delegator`;
ALTER TABLE `assembly_delegates` DROP FOREIGN KEY IF EXISTS `fk_delegates_delegate`;
ALTER TABLE `communication_recipients` DROP FOREIGN KEY IF EXISTS `fk_comm_recipients_member`;
ALTER TABLE `gdpr_consents` DROP FOREIGN KEY IF EXISTS `fk_gdpr_member`;
ALTER TABLE `event_registrations` DROP FOREIGN KEY IF EXISTS `fk_event_reg_member`;
ALTER TABLE `event_registrations` DROP FOREIGN KEY IF EXISTS `fk_event_reg_payment`;

-- Step 2: Clean orphan records
DELETE FROM `memberships`
WHERE `member_id` NOT IN (SELECT `id` FROM `members`);

-- Step 3: Recreate FK with CASCADE
ALTER TABLE `payments`
  ADD CONSTRAINT `fk_payments_request`
    FOREIGN KEY (`payment_request_id`) REFERENCES `payment_requests`(`id`)
    ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `payments`
  ADD CONSTRAINT `fk_payments_member`
    FOREIGN KEY (`member_id`) REFERENCES `members`(`id`)
    ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `memberships`
  ADD CONSTRAINT `fk_memberships_member`
    FOREIGN KEY (`member_id`) REFERENCES `members`(`id`)
    ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `assembly_attendees`
  ADD CONSTRAINT `fk_assembly_att_member`
    FOREIGN KEY (`member_id`) REFERENCES `members`(`id`)
    ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `assembly_delegates`
  ADD CONSTRAINT `fk_delegates_delegator`
    FOREIGN KEY (`delegator_member_id`) REFERENCES `members`(`id`)
    ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `assembly_delegates`
  ADD CONSTRAINT `fk_delegates_delegate`
    FOREIGN KEY (`delegate_member_id`) REFERENCES `members`(`id`)
    ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `communication_recipients`
  ADD CONSTRAINT `fk_comm_recipients_member`
    FOREIGN KEY (`member_id`) REFERENCES `members`(`id`)
    ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `gdpr_consents`
  ADD CONSTRAINT `fk_gdpr_member`
    FOREIGN KEY (`member_id`) REFERENCES `members`(`id`)
    ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `event_registrations`
  ADD CONSTRAINT `fk_event_reg_member`
    FOREIGN KEY (`member_id`) REFERENCES `members`(`id`)
    ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `event_registrations`
  ADD CONSTRAINT `fk_event_reg_payment`
    FOREIGN KEY (`payment_request_id`) REFERENCES `payment_requests`(`id`)
    ON DELETE SET NULL ON UPDATE CASCADE;
