-- Migration 008: rename italian column names to english

ALTER TABLE `members`
    CHANGE `sesso` `sex` ENUM('M','F') NULL DEFAULT NULL
        COMMENT 'Biological sex for fiscal code calculation',
    CHANGE `genere` `gender` VARCHAR(50) NULL DEFAULT NULL
        COMMENT 'Gender identity — optional, sensitive data (GDPR)';

ALTER TABLE `membership_category_fees`
    CHANGE `anno`         `year`        YEAR              NOT NULL,
    CHANGE `quota`        `fee`         DECIMAL(8,2)      NOT NULL,
    CHANGE `approvata_da` `approved_by` INT(10) UNSIGNED  NULL DEFAULT NULL
        COMMENT 'users.id of the admin who approved or set the fee';
