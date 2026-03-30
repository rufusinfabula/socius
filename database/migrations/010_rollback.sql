-- Migration 010 rollback: restore fiscal_code as NOT NULL VARCHAR with UNIQUE

ALTER TABLE `members` DROP INDEX `uq_fiscal_code`;

ALTER TABLE `members`
    MODIFY COLUMN `fiscal_code` VARCHAR(16) NOT NULL DEFAULT '',
    ADD UNIQUE KEY `uq_fiscal_code` (`fiscal_code`);
