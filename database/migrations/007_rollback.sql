-- Migration 007 rollback

DROP TABLE IF EXISTS `membership_category_fees`;

ALTER TABLE `membership_categories`
    DROP COLUMN IF EXISTS `is_exempt_from_renewal`,
    DROP COLUMN IF EXISTS `requires_approval`,
    DROP COLUMN IF EXISTS `valid_from`,
    DROP COLUMN IF EXISTS `valid_until`,
    DROP COLUMN IF EXISTS `description`;
