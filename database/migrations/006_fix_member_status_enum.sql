-- Migration 006: fix members.status ENUM
-- Replaces legacy values with the canonical 7-value set.
-- 'honorary' is removed — it becomes a membership_category with is_exempt_from_renewal = true.

ALTER TABLE `members`
    MODIFY COLUMN `status` ENUM(
        'active',
        'in_renewal',
        'not_renewed',
        'lapsed',
        'suspended',
        'resigned',
        'deceased'
    ) NOT NULL DEFAULT 'active'
    COMMENT 'Stato socio — valori fissi in inglese, tradotti solo in UI';
