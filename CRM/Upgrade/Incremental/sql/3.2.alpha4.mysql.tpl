-- schema changes after 3.2 alpha4 tag
--  change is_hidden to is_tagset in civicrm_tag
ALTER TABLE `civicrm_tag` CHANGE `is_hidden` `is_tagset` TINYINT( 4 ) NULL DEFAULT '0';

-- CRM-6229
ALTER TABLE `civicrm_event` CHANGE `is_template` `is_template` TINYINT( 4 ) NULL DEFAULT '0' COMMENT 'whether the event has template';

UPDATE `civicrm_event` SET `is_template` = 0 WHERE `is_template` IS NULL ;