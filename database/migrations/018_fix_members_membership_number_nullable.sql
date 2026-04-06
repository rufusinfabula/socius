-- =============================================================================
-- Migration 018: Make members.membership_number nullable
-- =============================================================================
--
-- The membership_number column on the members table was defined as NOT NULL
-- in the original schema. This prevented creating new members because
-- Member::create() correctly sets membership_number = NULL at creation time
-- (a card number is only assigned when the first membership record is issued).
--
-- This migration makes the column nullable so that:
--   INSERT INTO members (...) VALUES (...)   -- without membership_number
--   works correctly — the column defaults to NULL.
--
-- The denormalized copy on members.membership_number is updated automatically
-- by Membership::create() via Member::updateCardNumber() when the first
-- membership record is created for the member.
-- =============================================================================

ALTER TABLE `members`
MODIFY COLUMN `membership_number` VARCHAR(20) NULL DEFAULT NULL
  COMMENT 'Denormalized copy of current card number — NULL until first membership issued. Source of truth: memberships.membership_number';
