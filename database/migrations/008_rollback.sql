-- Migration 008 rollback: restore italian column names

ALTER TABLE `members`
    CHANGE `sex`    `sesso`  ENUM('M','F')  NULL DEFAULT NULL,
    CHANGE `gender` `genere` VARCHAR(50)    NULL DEFAULT NULL;

ALTER TABLE `membership_category_fees`
    CHANGE `year`        `anno`         YEAR             NOT NULL,
    CHANGE `fee`         `quota`        DECIMAL(8,2)     NOT NULL,
    CHANGE `approved_by` `approvata_da` INT(10) UNSIGNED NULL DEFAULT NULL;
