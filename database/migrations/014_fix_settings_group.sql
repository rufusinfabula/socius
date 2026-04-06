-- Fix settings rows whose `group` column doesn't match the key prefix.
-- This repairs records inserted before v0.2.1 when the group column defaulted to 'general'.
UPDATE `settings`
SET `group` = SUBSTRING_INDEX(`key`, '.', 1)
WHERE `group` != SUBSTRING_INDEX(`key`, '.', 1);
