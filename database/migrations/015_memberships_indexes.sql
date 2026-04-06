-- Migration 015: add composite index and payment columns to memberships
--
-- idx_year and idx_status already exist from 001_initial_schema.
-- This migration adds the combined idx_member_year index (not covered by
-- the unique constraint uq_member_year for plain lookup queries) and the
-- payment_method / payment_reference columns needed for manual card creation.

-- 1. Add composite index for member+year lookups (if not already covered)
ALTER TABLE `memberships`
  ADD KEY `idx_member_year` (`member_id`, `year`);

-- 2. Add payment_method and payment_reference columns
ALTER TABLE `memberships`
  ADD COLUMN `payment_method`
    ENUM('cash','bank_transfer','paypal','satispay','waived','other')
    NULL DEFAULT NULL
    COMMENT 'Payment method recorded at card creation or edit'
    AFTER `paid_on`,
  ADD COLUMN `payment_reference` VARCHAR(255) NULL DEFAULT NULL
    COMMENT 'Receipt number, bank transfer reference, etc.'
    AFTER `payment_method`;
