-- Migration 010: fix fiscal_code UNIQUE to allow multiple NULL values
-- MySQL UNIQUE indexes permit multiple NULLs; empty string '' would collide.

-- Remove the old index (may be named differently — adjust if needed)
ALTER TABLE `members` DROP INDEX `uq_fiscal_code`;

-- Make column nullable and recreate the index
ALTER TABLE `members`
    MODIFY COLUMN `fiscal_code` VARCHAR(16) NULL DEFAULT NULL
        COMMENT 'Italian fiscal code (codice fiscale) — NULL when not provided',
    ADD UNIQUE KEY `uq_fiscal_code` (`fiscal_code`);

-- Convert any existing empty strings to NULL
UPDATE `members` SET `fiscal_code` = NULL WHERE `fiscal_code` = '';
