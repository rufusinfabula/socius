-- v0.2.3: ensure ui.date_format setting exists with default value
INSERT IGNORE INTO `settings` (`key`, `value`, `type`, `group`, `label`)
VALUES ('ui.date_format', 'd/m/Y', 'string', 'ui', 'Date display format');
