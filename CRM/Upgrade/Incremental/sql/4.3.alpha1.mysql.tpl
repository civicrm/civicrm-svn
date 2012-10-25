-- CRM-8507
ALTER TABLE civicrm_custom_field
  ADD UNIQUE INDEX `UI_name_custom_group_id` (`name`, `custom_group_id`);

--CRM-10473 Added Missing Provinces of Ningxia Autonomous Region of China
INSERT INTO `civicrm_state_province`(`country_id`, `abbreviation`, `name`) VALUES
(1045, 'YN', 'Yinchuan'),
(1045, 'SZ', 'Shizuishan'),
(1045, 'WZ', 'Wuzhong'),
(1045, 'GY', 'Guyuan'),
(1045, 'ZW', 'Zhongwei');

-- CRM-10553
ALTER TABLE civicrm_contact
  ADD COLUMN `created_date` timestamp NULL DEFAULT NULL
  COMMENT 'When was the contact was created.';
ALTER TABLE civicrm_contact
  ADD COLUMN `modified_date` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
  COMMENT 'When was the contact (or closely related entity) was created or modified or deleted.';

-- CRM-10296
DELETE FROM civicrm_job WHERE `api_action` = 'process_membership_reminder_date';
ALTER TABLE civicrm_membership 			DROP COLUMN reminder_date;
ALTER TABLE civicrm_membership_log 	DROP COLUMN renewal_reminder_date;
ALTER TABLE civicrm_membership_type
	DROP COLUMN renewal_reminder_day,
	DROP FOREIGN KEY FK_civicrm_membership_type_renewal_msg_id,
	DROP INDEX FK_civicrm_membership_type_renewal_msg_id,
	DROP COLUMN renewal_msg_id,
	DROP COLUMN autorenewal_msg_id;

-- CRM-10738
ALTER TABLE civicrm_msg_template
      CHANGE msg_text msg_text LONGTEXT NULL COMMENT 'Text formatted message',
      CHANGE msg_html msg_html LONGTEXT NULL COMMENT 'HTML formatted message';

-- CRM-10860
ALTER TABLE civicrm_contribution_page ADD COLUMN is_recur_installments tinyint(4) DEFAULT '0';
UPDATE civicrm_contribution_page SET is_recur_installments='1';

-- CRM-10863
SELECT @country_id := id from civicrm_country where name = 'Luxembourg' AND iso_code = 'LU';
INSERT IGNORE INTO `civicrm_state_province`(`country_id`, `abbreviation`, `name`) VALUES
(@country_id, 'L', 'Luxembourg');

-- CRM-11047
ALTER TABLE civicrm_job DROP COLUMN api_prefix;

-- CRM-11068
ALTER TABLE civicrm_group
  ADD refresh_date datetime default NULL COMMENT 'Date and time when we need to refresh the cache next.' AFTER `cache_date`;

SELECT @domainID := min(id) FROM civicrm_domain;
INSERT INTO `civicrm_job`
    ( domain_id, run_frequency, last_run, name, description, api_entity, api_action, parameters, is_active )
VALUES
    ( @domainID, 'Always' , NULL, '{ts escape="sql" skip="true"}Rebuild Smart Group Cache{/ts}', '{ts escape="sql" skip="true"}Rebuilds the smart group cache.{/ts}', 'job', 'group_rebuild', '{ts escape="sql" skip="true"}limit=Number optional-Limit the number of smart groups rebuild{/ts}', 0);

-- CRM-11117
INSERT IGNORE INTO `civicrm_setting` (`group_name`, `name`, `value`, `domain_id`, `is_domain`) VALUES ('CiviCRM Preferences', 'activity_assignee_notification_ics', 's:1:"0";', {$domainID}, '1');

-- CRM-10885
ALTER TABLE civicrm_dedupe_rule_group 
  ADD used enum('Unsupervised','Supervised','General') COLLATE utf8_unicode_ci NOT NULL COMMENT 'Whether the rule should be used for cases where usage is Unsupervised, Supervised OR General(programatically)' AFTER threshold;
  
UPDATE civicrm_dedupe_rule_group 
  SET used = 'General' WHERE is_default = 0;

UPDATE civicrm_dedupe_rule_group
    SET used = CASE level
        WHEN 'Fuzzy' THEN 'Supervised'
        WHEN 'Strict'   THEN 'Unsupervised'
    END
WHERE is_default = 1;

UPDATE civicrm_dedupe_rule_group
  SET name = CONCAT_WS('', `contact_type`, `used`)
WHERE is_default = 1 OR is_reserved = 1;

UPDATE civicrm_dedupe_rule_group
  SET  title = 'Name and Email'
WHERE contact_type IN ('Organization', 'Household') AND used IN ('Unsupervised', 'Supervised');

UPDATE civicrm_dedupe_rule_group
    SET title = CASE used
        WHEN 'Supervised' THEN 'Name and Email (reserved)'
        WHEN 'Unsupervised'   THEN 'Email (reserved)'
         WHEN 'General' THEN 'Name and Address (reserved)'
    END
WHERE contact_type = 'Individual' AND is_reserved = 1;

ALTER TABLE civicrm_dedupe_rule_group DROP COLUMN level;