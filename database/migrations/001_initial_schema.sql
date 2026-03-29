-- =============================================================================
-- Socius — Initial Database Schema
-- Migration: 001_initial_schema.sql
-- Run rollback with: 001_rollback.sql
-- =============================================================================

SET FOREIGN_KEY_CHECKS = 0;
SET NAMES utf8mb4;
SET time_zone = '+00:00';

-- =============================================================================
-- 1. settings
-- Key-value store for application configuration.
-- =============================================================================
CREATE TABLE `settings` (
  `id`         INT UNSIGNED     NOT NULL AUTO_INCREMENT,
  `key`        VARCHAR(100)     NOT NULL,
  `value`      TEXT,
  `type`       ENUM('string','integer','boolean','json','date') NOT NULL DEFAULT 'string',
  `group`      VARCHAR(50)      NOT NULL DEFAULT 'general',
  `label`      VARCHAR(255)     NOT NULL DEFAULT '',
  `created_at` TIMESTAMP        NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP        NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_key` (`key`),
  KEY `idx_group` (`group`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================================================
-- 2. roles
-- =============================================================================
CREATE TABLE `roles` (
  `id`          TINYINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name`        VARCHAR(50)      NOT NULL,
  `label`       VARCHAR(100)     NOT NULL,
  `permissions` JSON,
  `created_at`  TIMESTAMP        NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================================================
-- 3. users
-- NOTE: member_id FK to members is added via ALTER TABLE after members is created.
-- =============================================================================
CREATE TABLE `users` (
  `id`                      INT UNSIGNED  NOT NULL AUTO_INCREMENT,
  `role_id`                 TINYINT UNSIGNED NOT NULL,
  `member_id`               INT UNSIGNED  DEFAULT NULL,
  `name`                    VARCHAR(100)  NOT NULL,
  `surname`                 VARCHAR(100)  NOT NULL,
  `email`                   VARCHAR(255)  NOT NULL,
  `password_hash`           VARCHAR(255)  NOT NULL,
  `is_active`               TINYINT(1)    NOT NULL DEFAULT 1,
  `email_verified_at`       TIMESTAMP     NULL DEFAULT NULL,
  `remember_token`          VARCHAR(100)  NULL DEFAULT NULL,
  `password_reset_token`    VARCHAR(100)  NULL DEFAULT NULL,
  `password_reset_expires`  TIMESTAMP     NULL DEFAULT NULL,
  `last_login_at`           TIMESTAMP     NULL DEFAULT NULL,
  `last_login_ip`           VARCHAR(45)   NULL DEFAULT NULL,
  `created_at`              TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`              TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_email` (`email`),
  KEY `idx_role` (`role_id`),
  KEY `idx_member` (`member_id`),
  KEY `idx_active` (`is_active`),
  CONSTRAINT `fk_users_role` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================================================
-- 4. membership_categories
-- =============================================================================
CREATE TABLE `membership_categories` (
  `id`          INT UNSIGNED    NOT NULL AUTO_INCREMENT,
  `name`        VARCHAR(100)    NOT NULL,
  `label`       VARCHAR(255)    NOT NULL,
  `description` TEXT,
  `annual_fee`  DECIMAL(10,2)   NOT NULL DEFAULT 0.00,
  `is_active`   TINYINT(1)      NOT NULL DEFAULT 1,
  `sort_order`  TINYINT UNSIGNED NOT NULL DEFAULT 0,
  `created_at`  TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`  TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_name` (`name`),
  KEY `idx_active` (`is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================================================
-- 5. members
-- =============================================================================
CREATE TABLE `members` (
  `id`                INT UNSIGNED  NOT NULL AUTO_INCREMENT,
  `membership_number` VARCHAR(20)   NOT NULL,
  `name`              VARCHAR(100)  NOT NULL,
  `surname`           VARCHAR(100)  NOT NULL,
  `email`             VARCHAR(255)  NOT NULL,
  `phone`             VARCHAR(30)   NULL DEFAULT NULL,
  `birth_date`        DATE          NULL DEFAULT NULL,
  `birth_place`       VARCHAR(100)  NULL DEFAULT NULL,
  `fiscal_code`       VARCHAR(16)   NULL DEFAULT NULL,
  `address`           VARCHAR(255)  NULL DEFAULT NULL,
  `city`              VARCHAR(100)  NULL DEFAULT NULL,
  `postal_code`       VARCHAR(10)   NULL DEFAULT NULL,
  `province`          VARCHAR(5)    NULL DEFAULT NULL,
  `country`           CHAR(2)       NOT NULL DEFAULT 'IT',
  `category_id`       INT UNSIGNED  NULL DEFAULT NULL,
  `status`            ENUM('active','suspended','expired','resigned','deceased')
                                    NOT NULL DEFAULT 'active',
  `joined_on`         DATE          NOT NULL,
  `resigned_on`       DATE          NULL DEFAULT NULL,
  `notes`             TEXT          NULL DEFAULT NULL,
  `created_by`        INT UNSIGNED  NULL DEFAULT NULL,
  `created_at`        TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`        TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_membership_number` (`membership_number`),
  UNIQUE KEY `uq_email` (`email`),
  UNIQUE KEY `uq_fiscal_code` (`fiscal_code`),
  KEY `idx_status` (`status`),
  KEY `idx_surname_name` (`surname`, `name`),
  KEY `idx_category` (`category_id`),
  KEY `idx_created_by` (`created_by`),
  CONSTRAINT `fk_members_category`   FOREIGN KEY (`category_id`) REFERENCES `membership_categories` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_members_created_by` FOREIGN KEY (`created_by`)  REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Add FK from users.member_id → members.id (circular dependency resolved here)
ALTER TABLE `users`
  ADD CONSTRAINT `fk_users_member`
  FOREIGN KEY (`member_id`) REFERENCES `members` (`id`) ON DELETE SET NULL;

-- =============================================================================
-- 6. memberships
-- Annual membership record per member per year.
-- =============================================================================
CREATE TABLE `memberships` (
  `id`          INT UNSIGNED  NOT NULL AUTO_INCREMENT,
  `member_id`   INT UNSIGNED  NOT NULL,
  `category_id` INT UNSIGNED  NOT NULL,
  `year`        YEAR          NOT NULL,
  `fee`         DECIMAL(10,2) NOT NULL,
  `status`      ENUM('pending','paid','waived','cancelled') NOT NULL DEFAULT 'pending',
  `valid_from`  DATE          NOT NULL,
  `valid_until` DATE          NOT NULL,
  `paid_on`     DATE          NULL DEFAULT NULL,
  `notes`       TEXT          NULL DEFAULT NULL,
  `created_at`  TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`  TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_member_year` (`member_id`, `year`),
  KEY `idx_year` (`year`),
  KEY `idx_status` (`status`),
  KEY `idx_valid_until` (`valid_until`),
  KEY `idx_category` (`category_id`),
  CONSTRAINT `fk_memberships_member`   FOREIGN KEY (`member_id`)   REFERENCES `members` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_memberships_category` FOREIGN KEY (`category_id`) REFERENCES `membership_categories` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================================================
-- 7. payment_requests
-- =============================================================================
CREATE TABLE `payment_requests` (
  `id`               INT UNSIGNED  NOT NULL AUTO_INCREMENT,
  `member_id`        INT UNSIGNED  NOT NULL,
  `membership_id`    INT UNSIGNED  NULL DEFAULT NULL,
  `amount`           DECIMAL(10,2) NOT NULL,
  `description`      VARCHAR(255)  NOT NULL,
  `status`           ENUM('pending','paid','cancelled','expired') NOT NULL DEFAULT 'pending',
  `gateway`          ENUM('paypal','satispay','bank_transfer','cash','waived') NOT NULL,
  `gateway_order_id` VARCHAR(255)  NULL DEFAULT NULL,
  `expires_at`       TIMESTAMP     NULL DEFAULT NULL,
  `created_at`       TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`       TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_member` (`member_id`),
  KEY `idx_status` (`status`),
  KEY `idx_gateway_order` (`gateway_order_id`),
  KEY `idx_membership` (`membership_id`),
  CONSTRAINT `fk_payment_requests_member`     FOREIGN KEY (`member_id`)     REFERENCES `members` (`id`),
  CONSTRAINT `fk_payment_requests_membership` FOREIGN KEY (`membership_id`) REFERENCES `memberships` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================================================
-- 8. payments
-- =============================================================================
CREATE TABLE `payments` (
  `id`                   INT UNSIGNED  NOT NULL AUTO_INCREMENT,
  `payment_request_id`   INT UNSIGNED  NOT NULL,
  `member_id`            INT UNSIGNED  NOT NULL,
  `amount`               DECIMAL(10,2) NOT NULL,
  `gateway`              ENUM('paypal','satispay','bank_transfer','cash','waived') NOT NULL,
  `gateway_transaction_id` VARCHAR(255) NULL DEFAULT NULL,
  `gateway_response`     JSON          NULL DEFAULT NULL,
  `status`               ENUM('completed','refunded','failed') NOT NULL DEFAULT 'completed',
  `paid_at`              TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `receipt_sent_at`      TIMESTAMP     NULL DEFAULT NULL,
  `notes`                TEXT          NULL DEFAULT NULL,
  `created_at`           TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_member` (`member_id`),
  KEY `idx_paid_at` (`paid_at`),
  KEY `idx_gateway_tx` (`gateway_transaction_id`),
  KEY `idx_request` (`payment_request_id`),
  CONSTRAINT `fk_payments_request` FOREIGN KEY (`payment_request_id`) REFERENCES `payment_requests` (`id`),
  CONSTRAINT `fk_payments_member`  FOREIGN KEY (`member_id`)           REFERENCES `members` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================================================
-- 9. renewal_campaigns
-- =============================================================================
CREATE TABLE `renewal_campaigns` (
  `id`                      INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `year`                    YEAR         NOT NULL,
  `name`                    VARCHAR(255) NOT NULL,
  `status`                  ENUM('draft','active','completed','cancelled') NOT NULL DEFAULT 'draft',
  `opens_on`                DATE         NOT NULL,
  `closes_on`               DATE         NOT NULL,
  `reminder_intervals`      JSON         NULL COMMENT 'Days before deadline: e.g. [30,14,7,1]',
  `email_template_first`    TEXT         NULL,
  `email_template_reminder` TEXT         NULL,
  `email_template_last`     TEXT         NULL,
  `created_by`              INT UNSIGNED NULL DEFAULT NULL,
  `created_at`              TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`              TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_year` (`year`),
  KEY `idx_status` (`status`),
  KEY `idx_created_by` (`created_by`),
  CONSTRAINT `fk_renewal_campaigns_user` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================================================
-- 10. renewal_reminders
-- =============================================================================
CREATE TABLE `renewal_reminders` (
  `id`          INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `campaign_id` INT UNSIGNED NOT NULL,
  `member_id`   INT UNSIGNED NOT NULL,
  `type`        ENUM('first','reminder','last') NOT NULL DEFAULT 'reminder',
  `email`       VARCHAR(255) NOT NULL,
  `status`      ENUM('sent','delivered','bounced','failed') NOT NULL DEFAULT 'sent',
  `sent_at`     TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_campaign` (`campaign_id`),
  KEY `idx_member` (`member_id`),
  KEY `idx_sent_at` (`sent_at`),
  CONSTRAINT `fk_renewal_reminders_campaign` FOREIGN KEY (`campaign_id`) REFERENCES `renewal_campaigns` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_renewal_reminders_member`   FOREIGN KEY (`member_id`)   REFERENCES `members` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================================================
-- 11. communications
-- =============================================================================
CREATE TABLE `communications` (
  `id`                 INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `subject`            VARCHAR(255) NOT NULL,
  `body`               TEXT         NOT NULL,
  `type`               ENUM('newsletter','notice','circular','custom') NOT NULL DEFAULT 'custom',
  `status`             ENUM('draft','scheduled','sending','sent','cancelled') NOT NULL DEFAULT 'draft',
  `target`             ENUM('all','active','category','custom') NOT NULL DEFAULT 'all',
  `target_category_id` INT UNSIGNED NULL DEFAULT NULL,
  `scheduled_at`       TIMESTAMP    NULL DEFAULT NULL,
  `sent_at`            TIMESTAMP    NULL DEFAULT NULL,
  `sent_count`         INT UNSIGNED NOT NULL DEFAULT 0,
  `created_by`         INT UNSIGNED NULL DEFAULT NULL,
  `created_at`         TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`         TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_status` (`status`),
  KEY `idx_sent_at` (`sent_at`),
  KEY `idx_target_category` (`target_category_id`),
  KEY `idx_created_by` (`created_by`),
  CONSTRAINT `fk_communications_category` FOREIGN KEY (`target_category_id`) REFERENCES `membership_categories` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_communications_user`     FOREIGN KEY (`created_by`)          REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================================================
-- 12. communication_recipients
-- =============================================================================
CREATE TABLE `communication_recipients` (
  `id`               INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `communication_id` INT UNSIGNED NOT NULL,
  `member_id`        INT UNSIGNED NOT NULL,
  `email`            VARCHAR(255) NOT NULL,
  `status`           ENUM('pending','sent','delivered','opened','bounced','failed') NOT NULL DEFAULT 'pending',
  `sent_at`          TIMESTAMP    NULL DEFAULT NULL,
  `opened_at`        TIMESTAMP    NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_comm_member` (`communication_id`, `member_id`),
  KEY `idx_status` (`status`),
  KEY `idx_member` (`member_id`),
  CONSTRAINT `fk_comm_recipients_comm`   FOREIGN KEY (`communication_id`) REFERENCES `communications` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_comm_recipients_member` FOREIGN KEY (`member_id`)        REFERENCES `members` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================================================
-- 13. events
-- =============================================================================
CREATE TABLE `events` (
  `id`                      INT UNSIGNED  NOT NULL AUTO_INCREMENT,
  `title`                   VARCHAR(255)  NOT NULL,
  `description`             TEXT          NULL DEFAULT NULL,
  `type`                    ENUM('public','members_only','board') NOT NULL DEFAULT 'public',
  `status`                  ENUM('draft','published','cancelled','completed') NOT NULL DEFAULT 'draft',
  `location`                VARCHAR(255)  NULL DEFAULT NULL,
  `online_url`              VARCHAR(512)  NULL DEFAULT NULL,
  `starts_at`               DATETIME      NOT NULL,
  `ends_at`                 DATETIME      NOT NULL,
  `registration_opens_at`   DATETIME      NULL DEFAULT NULL,
  `registration_closes_at`  DATETIME      NULL DEFAULT NULL,
  `max_attendees`           INT UNSIGNED  NULL DEFAULT NULL,
  `fee`                     DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `requires_payment`        TINYINT(1)    NOT NULL DEFAULT 0,
  `created_by`              INT UNSIGNED  NULL DEFAULT NULL,
  `created_at`              TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`              TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_starts_at` (`starts_at`),
  KEY `idx_status` (`status`),
  KEY `idx_type` (`type`),
  KEY `idx_created_by` (`created_by`),
  CONSTRAINT `fk_events_user` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================================================
-- 14. event_registrations
-- =============================================================================
CREATE TABLE `event_registrations` (
  `id`                 INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `event_id`           INT UNSIGNED NOT NULL,
  `member_id`          INT UNSIGNED NOT NULL,
  `status`             ENUM('registered','waitlisted','cancelled','attended') NOT NULL DEFAULT 'registered',
  `payment_request_id` INT UNSIGNED NULL DEFAULT NULL,
  `notes`              TEXT         NULL DEFAULT NULL,
  `registered_at`      TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `cancelled_at`       TIMESTAMP    NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_event_member` (`event_id`, `member_id`),
  KEY `idx_status` (`status`),
  KEY `idx_member` (`member_id`),
  KEY `idx_payment_request` (`payment_request_id`),
  CONSTRAINT `fk_event_reg_event`   FOREIGN KEY (`event_id`)           REFERENCES `events` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_event_reg_member`  FOREIGN KEY (`member_id`)          REFERENCES `members` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_event_reg_payment` FOREIGN KEY (`payment_request_id`) REFERENCES `payment_requests` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================================================
-- 15. assemblies
-- =============================================================================
CREATE TABLE `assemblies` (
  `id`                INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `title`             VARCHAR(255) NOT NULL,
  `type`              ENUM('ordinary','extraordinary') NOT NULL DEFAULT 'ordinary',
  `status`            ENUM('scheduled','open','closed','cancelled') NOT NULL DEFAULT 'scheduled',
  `call_date`         DATE         NOT NULL COMMENT 'Date the assembly was formally called',
  `first_call_at`     DATETIME     NOT NULL,
  `second_call_at`    DATETIME     NULL DEFAULT NULL,
  `location`          VARCHAR(255) NULL DEFAULT NULL,
  `online_url`        VARCHAR(512) NULL DEFAULT NULL,
  `quorum_percentage` TINYINT UNSIGNED NOT NULL DEFAULT 50,
  `agenda`            TEXT         NULL DEFAULT NULL,
  `notes`             TEXT         NULL DEFAULT NULL,
  `closed_at`         TIMESTAMP    NULL DEFAULT NULL,
  `created_by`        INT UNSIGNED NULL DEFAULT NULL,
  `created_at`        TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`        TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_status` (`status`),
  KEY `idx_first_call_at` (`first_call_at`),
  KEY `idx_created_by` (`created_by`),
  CONSTRAINT `fk_assemblies_user` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================================================
-- 16. assembly_attendees
-- =============================================================================
CREATE TABLE `assembly_attendees` (
  `id`           INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `assembly_id`  INT UNSIGNED NOT NULL,
  `member_id`    INT UNSIGNED NOT NULL,
  `attended_as`  ENUM('in_person','online','proxy') NOT NULL DEFAULT 'in_person',
  `checked_in_at` TIMESTAMP   NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_assembly_member` (`assembly_id`, `member_id`),
  KEY `idx_member` (`member_id`),
  CONSTRAINT `fk_assembly_att_assembly` FOREIGN KEY (`assembly_id`) REFERENCES `assemblies` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_assembly_att_member`   FOREIGN KEY (`member_id`)   REFERENCES `members` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================================================
-- 17. assembly_delegates
-- Proxy voting: delegator gives their vote to delegate.
-- =============================================================================
CREATE TABLE `assembly_delegates` (
  `id`                   INT UNSIGNED  NOT NULL AUTO_INCREMENT,
  `assembly_id`          INT UNSIGNED  NOT NULL,
  `delegator_member_id`  INT UNSIGNED  NOT NULL,
  `delegate_member_id`   INT UNSIGNED  NOT NULL,
  `delegation_document`  VARCHAR(512)  NULL DEFAULT NULL COMMENT 'Path to signed delegation form',
  `created_at`           TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_assembly_delegator` (`assembly_id`, `delegator_member_id`),
  KEY `idx_delegate` (`assembly_id`, `delegate_member_id`),
  KEY `idx_delegator_member` (`delegator_member_id`),
  KEY `idx_delegate_member` (`delegate_member_id`),
  CONSTRAINT `fk_delegates_assembly`  FOREIGN KEY (`assembly_id`)         REFERENCES `assemblies` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_delegates_delegator` FOREIGN KEY (`delegator_member_id`) REFERENCES `members` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_delegates_delegate`  FOREIGN KEY (`delegate_member_id`)  REFERENCES `members` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================================================
-- 18. minutes
-- Meeting minutes (verbali) for assemblies or events.
-- =============================================================================
CREATE TABLE `minutes` (
  `id`           INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `assembly_id`  INT UNSIGNED NULL DEFAULT NULL,
  `event_id`     INT UNSIGNED NULL DEFAULT NULL,
  `title`        VARCHAR(255) NOT NULL,
  `body`         TEXT         NOT NULL,
  `attachments`  JSON         NULL COMMENT 'Array of stored file paths',
  `status`       ENUM('draft','approved','published') NOT NULL DEFAULT 'draft',
  `approved_at`  DATE         NULL DEFAULT NULL,
  `approved_by`  INT UNSIGNED NULL DEFAULT NULL,
  `created_by`   INT UNSIGNED NULL DEFAULT NULL,
  `created_at`   TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`   TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_status` (`status`),
  KEY `idx_assembly` (`assembly_id`),
  KEY `idx_event` (`event_id`),
  KEY `idx_approved_by` (`approved_by`),
  KEY `idx_created_by` (`created_by`),
  CONSTRAINT `fk_minutes_assembly`    FOREIGN KEY (`assembly_id`) REFERENCES `assemblies` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_minutes_event`       FOREIGN KEY (`event_id`)    REFERENCES `events` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_minutes_approved_by` FOREIGN KEY (`approved_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_minutes_created_by`  FOREIGN KEY (`created_by`)  REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================================================
-- 19. gdpr_consents
-- =============================================================================
CREATE TABLE `gdpr_consents` (
  `id`         INT UNSIGNED  NOT NULL AUTO_INCREMENT,
  `member_id`  INT UNSIGNED  NOT NULL,
  `type`       ENUM('data_processing','newsletter','third_party','profiling') NOT NULL,
  `granted`    TINYINT(1)    NOT NULL DEFAULT 1,
  `version`    VARCHAR(20)   NOT NULL COMMENT 'Privacy policy version at time of consent',
  `ip_address` VARCHAR(45)   NULL DEFAULT NULL,
  `user_agent` VARCHAR(512)  NULL DEFAULT NULL,
  `granted_at` TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `revoked_at` TIMESTAMP     NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_member_type` (`member_id`, `type`),
  KEY `idx_granted_at` (`granted_at`),
  CONSTRAINT `fk_gdpr_member` FOREIGN KEY (`member_id`) REFERENCES `members` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================================================
-- 20. audit_logs
-- Immutable record of all create/update/delete actions.
-- =============================================================================
CREATE TABLE `audit_logs` (
  `id`          BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id`     INT UNSIGNED    NULL DEFAULT NULL,
  `action`      VARCHAR(100)    NOT NULL,
  `entity_type` VARCHAR(100)    NULL DEFAULT NULL,
  `entity_id`   INT UNSIGNED    NULL DEFAULT NULL,
  `old_values`  JSON            NULL DEFAULT NULL,
  `new_values`  JSON            NULL DEFAULT NULL,
  `ip_address`  VARCHAR(45)     NULL DEFAULT NULL,
  `user_agent`  VARCHAR(512)    NULL DEFAULT NULL,
  `created_at`  TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_user` (`user_id`),
  KEY `idx_entity` (`entity_type`, `entity_id`),
  KEY `idx_action` (`action`),
  KEY `idx_created_at` (`created_at`)
  -- No FK on user_id intentionally: audit logs must survive user deletion
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================================================
-- 21. imports
-- Tracks CSV/Excel import jobs.
-- =============================================================================
CREATE TABLE `imports` (
  `id`             INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `type`           ENUM('members','payments','events') NOT NULL,
  `status`         ENUM('pending','processing','completed','failed') NOT NULL DEFAULT 'pending',
  `filename`       VARCHAR(255) NOT NULL,
  `file_path`      VARCHAR(512) NOT NULL,
  `total_rows`     INT UNSIGNED NOT NULL DEFAULT 0,
  `processed_rows` INT UNSIGNED NOT NULL DEFAULT 0,
  `success_rows`   INT UNSIGNED NOT NULL DEFAULT 0,
  `error_rows`     INT UNSIGNED NOT NULL DEFAULT 0,
  `errors`         JSON         NULL DEFAULT NULL,
  `started_at`     TIMESTAMP    NULL DEFAULT NULL,
  `completed_at`   TIMESTAMP    NULL DEFAULT NULL,
  `created_by`     INT UNSIGNED NULL DEFAULT NULL,
  `created_at`     TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_status` (`status`),
  KEY `idx_type` (`type`),
  KEY `idx_created_by` (`created_by`),
  CONSTRAINT `fk_imports_user` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS = 1;

-- =============================================================================
-- SEED DATA
-- =============================================================================

-- -----------------------------------------------------------------------------
-- roles
-- -----------------------------------------------------------------------------
INSERT INTO `roles` (`id`, `name`, `label`, `permissions`) VALUES
(1, 'super_admin', 'Super Amministratore', JSON_OBJECT('*', true)),
(2, 'admin',       'Amministratore',       JSON_OBJECT(
    'members',        JSON_ARRAY('read','write','delete'),
    'payments',       JSON_ARRAY('read','write','delete'),
    'events',         JSON_ARRAY('read','write','delete'),
    'assemblies',     JSON_ARRAY('read','write','delete'),
    'communications', JSON_ARRAY('read','write','delete'),
    'settings',       JSON_ARRAY('read','write'),
    'imports',        JSON_ARRAY('read','write')
)),
(3, 'segreteria',  'Segreteria',           JSON_OBJECT(
    'members',        JSON_ARRAY('read','write'),
    'payments',       JSON_ARRAY('read','write'),
    'events',         JSON_ARRAY('read','write'),
    'assemblies',     JSON_ARRAY('read'),
    'communications', JSON_ARRAY('read','write'),
    'imports',        JSON_ARRAY('read','write')
)),
(4, 'socio',       'Socio',                JSON_OBJECT(
    'members',  JSON_ARRAY('read_own'),
    'payments', JSON_ARRAY('read_own'),
    'events',   JSON_ARRAY('read','register')
));

-- -----------------------------------------------------------------------------
-- settings
-- -----------------------------------------------------------------------------
INSERT INTO `settings` (`key`, `value`, `type`, `group`, `label`) VALUES
-- Association identity
('association.name',         'Associazione',         'string',  'association', 'Nome associazione'),
('association.short_name',   '',                     'string',  'association', 'Nome breve / acronimo'),
('association.fiscal_code',  '',                     'string',  'association', 'Codice fiscale'),
('association.address',      '',                     'string',  'association', 'Sede legale'),
('association.city',         '',                     'string',  'association', 'Città'),
('association.postal_code',  '',                     'string',  'association', 'CAP'),
('association.province',     '',                     'string',  'association', 'Provincia'),
('association.email',        '',                     'string',  'association', 'Email'),
('association.phone',        '',                     'string',  'association', 'Telefono'),
('association.website',      '',                     'string',  'association', 'Sito web'),
('association.iban',         '',                     'string',  'association', 'IBAN per bonifici'),
('association.bank_name',    '',                     'string',  'association', 'Nome banca'),

-- Membership numbering
('members.number_prefix',    'SOC',                  'string',  'members',     'Prefisso numero socio'),
('members.number_padding',   '4',                    'integer', 'members',     'Cifre numero socio (padding)'),
('members.next_number',      '1',                    'integer', 'members',     'Prossimo numero progressivo'),

-- Renewal cycle
('renewal.year',             YEAR(CURDATE()),        'integer', 'renewal',     'Anno di rinnovo corrente'),
('renewal.opens_on',         DATE_FORMAT(DATE(CONCAT(YEAR(CURDATE()), '-10-01')), '%Y-%m-%d'), 'date', 'renewal', 'Apertura campagna rinnovi'),
('renewal.closes_on',        DATE_FORMAT(DATE(CONCAT(YEAR(CURDATE())+1, '-03-31')), '%Y-%m-%d'), 'date', 'renewal', 'Chiusura campagna rinnovi'),
('renewal.reminder_days',    '[30,14,7,1]',          'json',    'renewal',     'Giorni prima della scadenza per i promemoria'),
('renewal.grace_days',       '30',                   'integer', 'renewal',     'Giorni di grazia dopo la scadenza'),
('renewal.membership_valid_from', DATE_FORMAT(DATE(CONCAT(YEAR(CURDATE()), '-01-01')), '%Y-%m-%d'), 'date', 'renewal', 'Inizio validità tessera'),
('renewal.membership_valid_until', DATE_FORMAT(DATE(CONCAT(YEAR(CURDATE()), '-12-31')), '%Y-%m-%d'), 'date', 'renewal', 'Fine validità tessera'),

-- Privacy
('gdpr.privacy_policy_version', '1.0',              'string',  'gdpr',        'Versione informativa privacy'),
('gdpr.privacy_policy_url',     '',                 'string',  'gdpr',        'URL informativa privacy'),
('gdpr.dpo_email',              '',                 'string',  'gdpr',        'Email DPO'),

-- Email
('mail.from_name',           'Socius',               'string',  'mail',        'Nome mittente email'),
('mail.from_address',        '',                     'string',  'mail',        'Indirizzo mittente email'),
('mail.reply_to',            '',                     'string',  'mail',        'Reply-to email'),

-- UI
('ui.locale',                'it',                   'string',  'ui',          'Lingua interfaccia (it|en)'),
('ui.date_format',           'd/m/Y',                'string',  'ui',          'Formato data'),
('ui.currency',              'EUR',                  'string',  'ui',          'Valuta'),
('ui.currency_symbol',       '€',                    'string',  'ui',          'Simbolo valuta'),

-- Payments
('payments.paypal_enabled',    'false',              'boolean', 'payments',    'Abilita PayPal'),
('payments.satispay_enabled',  'false',              'boolean', 'payments',    'Abilita Satispay'),
('payments.bank_transfer_enabled', 'true',           'boolean', 'payments',    'Abilita bonifico bancario'),
('payments.cash_enabled',      'true',               'boolean', 'payments',    'Abilita pagamento in contanti');
