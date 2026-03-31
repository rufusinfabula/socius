-- Rollback for migration 012
ALTER TABLE `membership_categories`
    DROP COLUMN `valid_until`,
    DROP COLUMN `valid_from`,
    DROP COLUMN `requires_approval`,
    DROP COLUMN `is_exempt_from_renewal`,
    DROP COLUMN `is_free`;

DELETE FROM `membership_categories`
WHERE `label` IN ('Ordinario', 'Sostenitore', 'Under 30', 'Onorario');
