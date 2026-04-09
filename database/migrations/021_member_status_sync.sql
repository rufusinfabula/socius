-- Migration 021 — Member status sync
-- v0.3.6: email optional + non-unique, remove category_id from members, add sync settings
--
-- 1. Rendi email nullable e rimuovi vincolo UNIQUE
ALTER TABLE `members`
  MODIFY COLUMN `email` VARCHAR(255) NULL DEFAULT NULL;

ALTER TABLE `members`
  DROP INDEX IF EXISTS `uq_email`;

-- 2. Rimuovi category_id da members
--    La categoria appartiene alla tessera, non al socio
ALTER TABLE `members`
  DROP FOREIGN KEY IF EXISTS `fk_members_category`,
  DROP COLUMN IF EXISTS `category_id`;

-- 3. Aggiungi chiavi sync in settings
INSERT IGNORE INTO `settings`
  (`key`, `value`, `type`, `group`, `label`)
VALUES
  ('system.last_sync_date', '', 'string', 'system',
   'Date of last member status recalculation'),
  ('system.last_sync_count', '0', 'integer', 'system',
   'Number of members updated in last sync'),
  ('system.last_sync_duration_ms', '0', 'integer', 'system',
   'Duration of last sync in milliseconds');
