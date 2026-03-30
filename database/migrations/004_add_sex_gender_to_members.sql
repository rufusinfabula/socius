-- Socius migration 004
-- Add sex (anagrafico) and gender (identità) columns to members table

ALTER TABLE `members`
    ADD COLUMN `sesso` ENUM('M','F') NULL DEFAULT NULL
        COMMENT 'Sesso anagrafico per codice fiscale'
        AFTER `birth_date`,
    ADD COLUMN `genere` VARCHAR(50) NULL DEFAULT NULL
        COMMENT 'Identità di genere — dato sensibile GDPR'
        AFTER `sesso`,
    ADD COLUMN `mobile` VARCHAR(30) NULL DEFAULT NULL
        COMMENT 'Numero di cellulare'
        AFTER `phone`;
