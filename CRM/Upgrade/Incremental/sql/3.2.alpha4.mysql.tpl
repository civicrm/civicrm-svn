-- schema changes for 3.2 alpha4 tag
--  change is_hidden to is_tagset in civicrm_tag
ALTER TABLE `civicrm_tag` CHANGE `is_hidden` `is_tagset` TINYINT( 4 ) NULL DEFAULT '0';

-- CRM-6229
ALTER TABLE `civicrm_event` CHANGE `is_template` `is_template` TINYINT( 4 ) NULL DEFAULT '0' COMMENT 'whether the event has template';
UPDATE `civicrm_event` SET `is_template` = 0 WHERE `is_template` IS NULL ;

-- CRM-5970 
ALTER TABLE `civicrm_financial_account` ADD `contact_id` INT UNSIGNED NOT NULL COMMENT 'FK to civicrm_contact' AFTER `id` ;
ALTER TABLE `civicrm_financial_account`
     ADD CONSTRAINT `FK_civicrm_financial_account_contact_id` FOREIGN KEY (`contact_id`) REFERENCES `civicrm_contact` (`id`) ON DELETE CASCADE;
