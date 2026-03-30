-- Migration 009: add permanent member_number and board assembly type

-- 1. Permanent sequential member number
ALTER TABLE `members`
    ADD COLUMN `member_number` INT UNSIGNED NULL
        COMMENT 'Permanent sequential member number — never changes, even if member lapses'
        AFTER `id`,
    ADD UNIQUE KEY `uq_member_number` (`member_number`);

-- 2. Assign sequential numbers to existing members
SET @n = 0;
UPDATE `members` SET `member_number` = (@n := @n + 1) ORDER BY `id`;

-- 3. Add board type to assemblies
ALTER TABLE `assemblies`
    MODIFY COLUMN `type` ENUM('ordinary','extraordinary','board')
        NOT NULL DEFAULT 'ordinary'
        COMMENT 'ordinary=assemblea ordinaria, extraordinary=straordinaria, board=direttivo';

-- 4. Rename settings key for consistency
UPDATE `settings`
    SET `key` = 'members.number_start'
    WHERE `key` = 'members.numero_socio_start';
