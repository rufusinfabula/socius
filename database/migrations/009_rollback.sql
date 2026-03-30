-- Migration 009 rollback

ALTER TABLE `members`
    DROP KEY `uq_member_number`,
    DROP COLUMN `member_number`;

ALTER TABLE `assemblies`
    MODIFY COLUMN `type` ENUM('ordinary','extraordinary')
        NOT NULL DEFAULT 'ordinary';

UPDATE `settings`
    SET `key` = 'members.numero_socio_start'
    WHERE `key` = 'members.number_start';
