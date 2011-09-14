-- CRM-8356
-- Add filter column 'filter' for 'civicrm_custom_field'
ALTER TABLE `civicrm_custom_field` ADD `filter` VARCHAR(255) NULL COMMENT 'Stores Contact Get API params contact reference custom fields. May be used for other filters in the future.';

-- CRM-8062
ALTER TABLE `civicrm_subscription_history` CHANGE `status` `status` ENUM( 'Added', 'Removed', 'Pending', 'Deleted' ) CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL DEFAULT NULL COMMENT 'The state of the contact within the group';

-- CRM-8510
ALTER TABLE civicrm_currency
ADD UNIQUE INDEX UI_name ( name );

-- CRM-8616
DELETE FROM civicrm_currency WHERE name = 'EEK';

-- CRM-8769
INSERT INTO civicrm_state_province
  (`name`, `abbreviation`, `country_id`)
VALUES
  ('Metropolitan Manila' , 'MNL', '1170');