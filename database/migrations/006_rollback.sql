-- Migration 006 rollback: ripristina il vecchio ENUM di members.status

ALTER TABLE `members`
    MODIFY COLUMN `status` ENUM(
        'active',
        'suspended',
        'expired',
        'resigned',
        'deceased'
    ) NOT NULL DEFAULT 'active';
