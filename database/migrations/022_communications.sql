-- =============================================================================
-- Migration 022 — Communications module
-- =============================================================================

CREATE TABLE `communications` (
  `id`              INT UNSIGNED    NOT NULL AUTO_INCREMENT,
  `title`           VARCHAR(255)    NOT NULL
                    COMMENT 'Internal title — not shown to recipients',
  `subject`         VARCHAR(255)    NOT NULL
                    COMMENT 'Subject line — shown to recipients',
  `body_text`       TEXT            NOT NULL
                    COMMENT 'Plain text body with [key] placeholders',
  `body_md`         TEXT            NULL DEFAULT NULL
                    COMMENT 'Markdown version — optional',
  `format`          ENUM('text','markdown') NOT NULL DEFAULT 'text',
  `status`          ENUM('draft','ready','sent') NOT NULL DEFAULT 'draft',
  `type`            ENUM('general','renewal','board','direct')
                    NOT NULL DEFAULT 'general'
                    COMMENT 'general=circular, renewal=renewal period, board=board only, direct=individual',
  `renewal_period`  VARCHAR(50)     NULL DEFAULT NULL
                    COMMENT 'open, first_reminder, second_reminder, third_reminder, close, lapse',
  `recipient_count` INT UNSIGNED    NOT NULL DEFAULT 0
                    COMMENT 'Cached count — updated when recipients change',
  `sent_at`         TIMESTAMP       NULL DEFAULT NULL,
  `created_by`      INT UNSIGNED    NULL DEFAULT NULL,
  `created_at`      TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`      TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP
                    ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_status` (`status`),
  KEY `idx_type` (`type`),
  KEY `idx_renewal_period` (`renewal_period`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `communication_recipients` (
  `id`                INT UNSIGNED  NOT NULL AUTO_INCREMENT,
  `communication_id`  INT UNSIGNED  NOT NULL,
  `member_id`         INT UNSIGNED  NOT NULL,
  `personalised_body` TEXT          NULL DEFAULT NULL
                      COMMENT 'Body with [key] placeholders resolved for this specific recipient',
  `included`          BOOLEAN       NOT NULL DEFAULT TRUE
                      COMMENT 'FALSE = manually excluded from this send',
  `created_at`        TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_comm_member` (`communication_id`, `member_id`),
  KEY `idx_communication_id` (`communication_id`),
  KEY `idx_member_id` (`member_id`),
  CONSTRAINT `fk_comm_recipients_comm`
    FOREIGN KEY (`communication_id`) REFERENCES `communications`(`id`)
    ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_comm_recipients_member`
    FOREIGN KEY (`member_id`) REFERENCES `members`(`id`)
    ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Communication templates and period tracking settings
INSERT IGNORE INTO `settings` (`key`, `value`, `type`, `group`, `label`) VALUES
  ('comm.template_open',
   'Gentile [nome],\n\nSono aperti i rinnovi per l\'anno sociale [anno].\n\nPer rinnovare la tua tessera contatta la segreteria.\n\nCordiali saluti,\n[associazione]',
   'text', 'comm', 'Template apertura rinnovi'),
  ('comm.template_first_reminder',
   'Gentile [nome],\n\nTi ricordiamo che la tua tessera per l\'anno [anno] non è ancora stata rinnovata.\n\nScadenza: [data_chiusura].\n\nCordiali saluti,\n[associazione]',
   'text', 'comm', 'Template primo sollecito'),
  ('comm.template_second_reminder',
   'Gentile [nome],\n\nQuesto è un secondo sollecito per il rinnovo della tua tessera per l\'anno [anno].\n\nScadenza: [data_chiusura].\n\nCordiali saluti,\n[associazione]',
   'text', 'comm', 'Template secondo sollecito'),
  ('comm.template_third_reminder',
   'Gentile [nome],\n\nUltimo avviso: la scadenza per il rinnovo della tua tessera è [data_chiusura].\n\nDopo tale data la tessera verrà considerata decaduta.\n\nCordiali saluti,\n[associazione]',
   'text', 'comm', 'Template terzo sollecito'),
  ('comm.template_close',
   'Gentile [nome],\n\nIl periodo di rinnovo è terminato. La tua tessera per l\'anno [anno] risulta non rinnovata.\n\nPer informazioni contatta la segreteria.\n\nCordiali saluti,\n[associazione]',
   'text', 'comm', 'Template chiusura rinnovi'),
  ('system.last_period_check', '', 'string', 'system',
   'Date of last renewal period check'),
  ('system.current_period', '', 'string', 'system',
   'Current renewal period key or empty if outside cycle'),
  ('system.period_history', '[]', 'json', 'system',
   'History of renewal periods entered during current social year');
