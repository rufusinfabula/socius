-- =============================================================================
-- Socius — Seed: Super Admin User
-- Migration: 002_seed_admin.sql
-- =============================================================================
-- Password: SociusAdmin2026!  (bcrypt cost 12)
-- Change this password immediately after first login in production.
-- =============================================================================

INSERT INTO `users`
    (`role_id`, `name`, `surname`, `email`, `password_hash`, `is_active`, `email_verified_at`)
SELECT
    r.`id`,
    'Super',
    'Admin',
    'admin@socius.test',
    '$2y$12$sjGEk4BYlOv71n42ch1u0OeTzCZSRUgwc6z5pHELt1aYApDiAtFJy',
    1,
    NOW()
FROM `roles` r
WHERE r.`name` = 'super_admin'
LIMIT 1;
