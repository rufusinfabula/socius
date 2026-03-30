-- =============================================================================
-- Socius — Reserved Member Numbers
-- Migration: 003_reserved_member_numbers.sql
-- Purpose: Tracks membership_numbers that must not be reused after an
--          emergency deletion where the operator chose to keep the number
--          reserved (free_numero_socio = false).
-- =============================================================================

CREATE TABLE `reserved_member_numbers` (
  `id`                INT UNSIGNED   NOT NULL AUTO_INCREMENT,
  `membership_number` VARCHAR(20)    NOT NULL,
  `reserved_at`       TIMESTAMP      NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `reserved_by`       INT UNSIGNED   NOT NULL COMMENT 'users.id of the super_admin who executed the delete',
  `reason`            VARCHAR(500)   NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_membership_number` (`membership_number`),
  KEY `idx_reserved_by` (`reserved_by`)
  -- No FK on reserved_by intentionally: must survive user deletion
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Add members.numero_socio_start setting if not already present
INSERT IGNORE INTO `settings` (`key`, `value`, `type`, `group`, `label`)
VALUES ('members.numero_socio_start', '1', 'integer', 'members', 'Numero socio iniziale');
