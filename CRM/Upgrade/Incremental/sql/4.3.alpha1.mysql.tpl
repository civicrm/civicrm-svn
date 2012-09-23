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

