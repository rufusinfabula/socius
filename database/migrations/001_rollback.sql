-- =============================================================================
-- Socius — Rollback Migration 001
-- Drops all tables created by 001_initial_schema.sql in reverse order.
-- WARNING: all data will be permanently lost.
-- =============================================================================

SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS `imports`;
DROP TABLE IF EXISTS `audit_logs`;
DROP TABLE IF EXISTS `gdpr_consents`;
DROP TABLE IF EXISTS `minutes`;
DROP TABLE IF EXISTS `assembly_delegates`;
DROP TABLE IF EXISTS `assembly_attendees`;
DROP TABLE IF EXISTS `assemblies`;
DROP TABLE IF EXISTS `event_registrations`;
DROP TABLE IF EXISTS `events`;
DROP TABLE IF EXISTS `communication_recipients`;
DROP TABLE IF EXISTS `communications`;
DROP TABLE IF EXISTS `renewal_reminders`;
DROP TABLE IF EXISTS `renewal_campaigns`;
DROP TABLE IF EXISTS `payments`;
DROP TABLE IF EXISTS `payment_requests`;
DROP TABLE IF EXISTS `memberships`;

-- Remove circular FK before dropping members / users
ALTER TABLE `users` DROP FOREIGN KEY IF EXISTS `fk_users_member`;

DROP TABLE IF EXISTS `members`;
DROP TABLE IF EXISTS `users`;
DROP TABLE IF EXISTS `membership_categories`;
DROP TABLE IF EXISTS `roles`;
DROP TABLE IF EXISTS `settings`;

SET FOREIGN_KEY_CHECKS = 1;
