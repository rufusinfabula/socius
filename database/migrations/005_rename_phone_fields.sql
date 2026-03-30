-- Migration 005: rename phone → phone1, mobile → phone2
-- Run once; safe to re-check with SHOW COLUMNS before applying.

ALTER TABLE `members`
    CHANGE COLUMN `phone`  `phone1` VARCHAR(30) NULL DEFAULT NULL
        COMMENT 'Telefono 1',
    CHANGE COLUMN `mobile` `phone2` VARCHAR(30) NULL DEFAULT NULL
        COMMENT 'Telefono 2';
