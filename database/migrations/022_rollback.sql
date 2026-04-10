-- =============================================================================
-- Rollback 022 — Communications module
-- =============================================================================

DROP TABLE IF EXISTS `communication_recipients`;
DROP TABLE IF EXISTS `communications`;

DELETE FROM `settings` WHERE `key` IN (
  'comm.template_open',
  'comm.template_first_reminder',
  'comm.template_second_reminder',
  'comm.template_third_reminder',
  'comm.template_close',
  'system.last_period_check',
  'system.current_period',
  'system.period_history'
);
