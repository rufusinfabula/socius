-- Migration 011: create board_roles and board_memberships

-- Catalogo ruoli direttivo
CREATE TABLE `board_roles` (
    `id`              INT UNSIGNED     NOT NULL AUTO_INCREMENT,
    `name`            VARCHAR(50)      NOT NULL
                      COMMENT 'Slug interno — es. president, treasurer',
    `label`           VARCHAR(100)     NOT NULL
                      COMMENT 'Etichetta visibile — configurabile per ogni associazione',
    `description`     TEXT             NULL DEFAULT NULL,
    `is_board_member` BOOLEAN          NOT NULL DEFAULT TRUE
                      COMMENT 'TRUE = fa parte del direttivo, FALSE = ruolo tecnico (es. revisore)',
    `can_sign`        BOOLEAN          NOT NULL DEFAULT FALSE
                      COMMENT 'TRUE = può firmare atti ufficiali (presidente, segretario)',
    `is_active`       BOOLEAN          NOT NULL DEFAULT TRUE,
    `sort_order`      TINYINT UNSIGNED NOT NULL DEFAULT 0,
    `created_at`      TIMESTAMP        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`      TIMESTAMP        NOT NULL DEFAULT CURRENT_TIMESTAMP
                      ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Seed: ruoli standard
INSERT INTO `board_roles`
    (`name`, `label`, `description`, `is_board_member`, `can_sign`, `sort_order`)
VALUES
    ('president',        'Presidente',           'Legale rappresentante dell\'associazione',    1, 1, 1),
    ('vice_president',   'Vicepresidente',        NULL,                                         1, 0, 2),
    ('secretary',        'Segretario',            'Verbalizza le assemblee e gli atti ufficiali', 1, 1, 3),
    ('treasurer',        'Tesoriere',             'Gestione economica e rendiconto',             1, 0, 4),
    ('board_member',     'Consigliere',           NULL,                                         1, 0, 5),
    ('auditor',          'Revisore dei conti',    'Controllo della gestione finanziaria',        0, 0, 6);

-- Storico appartenenze al direttivo
CREATE TABLE `board_memberships` (
    `id`                     INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `member_id`              INT UNSIGNED NOT NULL,
    `role_id`                INT UNSIGNED NOT NULL,
    `elected_on`             DATE         NOT NULL
                             COMMENT 'Data elezione o nomina',
    `expires_on`             DATE         NULL DEFAULT NULL
                             COMMENT 'Data scadenza mandato — NULL = a tempo indeterminato',
    `resigned_on`            DATE         NULL DEFAULT NULL
                             COMMENT 'Data dimissioni dal ruolo',
    `elected_by_assembly_id` INT UNSIGNED NULL DEFAULT NULL
                             COMMENT 'Assemblea che ha deliberato la nomina',
    `notes`                  TEXT         NULL DEFAULT NULL,
    `created_by`             INT UNSIGNED NULL DEFAULT NULL,
    `created_at`             TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`             TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP
                             ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_member_id`  (`member_id`),
    KEY `idx_role_id`    (`role_id`),
    KEY `idx_elected_by` (`elected_by_assembly_id`),
    KEY `idx_active`     (`expires_on`, `resigned_on`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
