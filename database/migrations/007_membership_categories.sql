-- Migration 007: aggiorna membership_categories e crea membership_category_fees

-- 1. Aggiunge nuovi campi alla tabella categorie
ALTER TABLE `membership_categories`
    ADD COLUMN `is_exempt_from_renewal` BOOLEAN NOT NULL DEFAULT FALSE
        COMMENT 'Se TRUE il socio non è soggetto a rinnovo (es. Onorario)'
        AFTER `is_free`,
    ADD COLUMN `requires_approval` BOOLEAN NOT NULL DEFAULT FALSE
        COMMENT 'La richiesta di iscrizione richiede approvazione direttivo'
        AFTER `is_exempt_from_renewal`,
    ADD COLUMN `valid_from` DATE NULL DEFAULT NULL
        COMMENT 'Data da cui la categoria è disponibile'
        AFTER `requires_approval`,
    ADD COLUMN `valid_until` DATE NULL DEFAULT NULL
        COMMENT 'Data fino a cui la categoria è disponibile (es. Under 30)'
        AFTER `valid_from`,
    ADD COLUMN `description` TEXT NULL DEFAULT NULL
        COMMENT 'Descrizione visibile nel form di iscrizione'
        AFTER `valid_until`;

-- 2. Tabella storico quote per anno
CREATE TABLE `membership_category_fees` (
    `id`            INT UNSIGNED    NOT NULL AUTO_INCREMENT,
    `category_id`   INT UNSIGNED    NOT NULL,
    `anno`          YEAR            NOT NULL,
    `quota`         DECIMAL(8,2)    NOT NULL,
    `note`          VARCHAR(500)    NULL DEFAULT NULL
                    COMMENT 'Es. Quota invariata, Aumento delibera del 15/01',
    `approvata_da`  INT UNSIGNED    NULL DEFAULT NULL
                    COMMENT 'users.id di chi ha confermato o modificato la quota',
    `created_at`    TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_category_anno` (`category_id`, `anno`),
    KEY `idx_category_id` (`category_id`),
    KEY `idx_anno` (`anno`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. Seed: 4 categorie di default
INSERT INTO `membership_categories`
    (`nome`, `quota_annuale`, `is_free`, `is_exempt_from_renewal`,
     `requires_approval`, `is_active`, `ordine`, `description`)
VALUES
    ('Ordinario',   50.00, 0, 0, 0, 1, 1,
     'Socio ordinario con tutti i diritti associativi.'),
    ('Sostenitore', 100.00, 0, 0, 0, 1, 2,
     'Socio sostenitore con contributo maggiorato.'),
    ('Under 30',    25.00, 0, 0, 0, 1, 3,
     'Riservato a chi non ha ancora compiuto 30 anni.'),
    ('Onorario',    0.00,  1, 1, 1, 1, 4,
     'Nomina onoraria deliberata dal direttivo. Esente da quota e rinnovo.');
