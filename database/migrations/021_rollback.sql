-- Rollback 021 — Member status sync
-- Ripristina email NOT NULL + UNIQUE, ri-aggiunge category_id a members,
-- rimuove chiavi sync da settings.
--
-- ATTENZIONE: email NULL e righe senza categoria vengono perse.
-- Eseguire solo su ambiente di sviluppo.

-- 1. Ripristina email NOT NULL (i valori NULL diventano stringa vuota)
UPDATE `members` SET `email` = '' WHERE `email` IS NULL;
ALTER TABLE `members`
  MODIFY COLUMN `email` VARCHAR(255) NOT NULL DEFAULT '';

ALTER TABLE `members`
  ADD UNIQUE KEY `uq_email` (`email`);

-- 2. Ri-aggiungi category_id
ALTER TABLE `members`
  ADD COLUMN `category_id` INT(11) NULL DEFAULT NULL AFTER `membership_number`,
  ADD CONSTRAINT `fk_members_category`
    FOREIGN KEY (`category_id`) REFERENCES `membership_categories` (`id`)
    ON DELETE SET NULL;

-- 3. Rimuovi chiavi sync
DELETE FROM `settings`
WHERE `key` IN (
  'system.last_sync_date',
  'system.last_sync_count',
  'system.last_sync_duration_ms'
);
