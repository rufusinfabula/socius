-- Migration 012: fix membership_categories — add missing columns and seed defaults
--
-- Background: migration 007 failed silently because its ALTER TABLE referenced
-- AFTER `is_free`, a column that did not exist yet. This migration adds the
-- missing columns using correct AFTER references and inserts the 4 default
-- categories with the column names that match the actual schema.

-- 1. Add missing columns using correct AFTER references
ALTER TABLE `membership_categories`
    ADD COLUMN `is_free`               BOOLEAN NOT NULL DEFAULT FALSE
        COMMENT 'True when no annual fee is required'
        AFTER `is_active`,
    ADD COLUMN `is_exempt_from_renewal` BOOLEAN NOT NULL DEFAULT FALSE
        COMMENT 'True when the member is not subject to annual renewal'
        AFTER `is_free`,
    ADD COLUMN `requires_approval`      BOOLEAN NOT NULL DEFAULT FALSE
        COMMENT 'Membership request requires board approval'
        AFTER `is_exempt_from_renewal`,
    ADD COLUMN `valid_from`             DATE NULL DEFAULT NULL
        COMMENT 'Date from which this category is available'
        AFTER `requires_approval`,
    ADD COLUMN `valid_until`            DATE NULL DEFAULT NULL
        COMMENT 'Date until which this category is available (e.g. Under 30)'
        AFTER `valid_from`;

-- 2. Seed 4 default categories (only when the table is empty)
INSERT INTO `membership_categories`
    (`label`, `annual_fee`, `is_free`, `is_exempt_from_renewal`,
     `requires_approval`, `is_active`, `sort_order`, `description`)
SELECT * FROM (SELECT
    'Ordinario'   AS label, 50.00 AS annual_fee, 0 AS is_free, 0 AS is_exempt_from_renewal,
    0 AS requires_approval, 1 AS is_active, 1 AS sort_order,
    'Socio ordinario con tutti i diritti associativi.' AS description
UNION ALL SELECT
    'Sostenitore', 100.00, 0, 0, 0, 1, 2,
    'Socio sostenitore con contributo maggiorato.'
UNION ALL SELECT
    'Under 30', 25.00, 0, 0, 0, 1, 3,
    'Riservato a chi non ha ancora compiuto 30 anni.'
UNION ALL SELECT
    'Onorario', 0.00, 1, 1, 1, 1, 4,
    'Nomina onoraria deliberata dal direttivo. Esente da quota e rinnovo.'
) AS rows
WHERE (SELECT COUNT(*) FROM `membership_categories`) = 0;
