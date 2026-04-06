-- =============================================================================
-- Migration 015: Fix membership_number column to allow NULL
-- =============================================================================
--
-- The membership_number in the memberships table must be nullable
-- because it is assigned only when the membership record is created,
-- not when the member is created. A member exists without a card
-- until their first membership is issued.
--
-- This migration makes the column explicitly NULL DEFAULT NULL.
-- =============================================================================

ALTER TABLE `memberships`
MODIFY COLUMN `membership_number` VARCHAR(10) NULL DEFAULT NULL
  COMMENT 'Card number assigned at membership creation — NULL until issued';
