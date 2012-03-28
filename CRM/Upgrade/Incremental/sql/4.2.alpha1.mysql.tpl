-- CRM-9542 mailing detail report template
SELECT @option_group_id_report := MAX(id)     FROM civicrm_option_group WHERE name = 'report_template';
SELECT @weight                 := MAX(weight) FROM civicrm_option_value WHERE option_group_id = @option_group_id_report;
SELECT @mailCompId       := MAX(id)     FROM civicrm_component where name = 'CiviMail';
INSERT INTO civicrm_option_value
  (option_group_id, {localize field='label'}label{/localize}, value, name, weight, {localize field='description'}description{/localize}, is_active, component_id) VALUES
  (@option_group_id_report, {localize}'Mail Detail Report'{/localize}, 'mailing/detail', 'CRM_Report_Form_Mailing_Detail', @weight := @weight + 1, {localize}'Provides reporting on Intended and Successful Deliveries, Unsubscribes and Opt-outs, Replies and Forwards.'{/localize}, 0, @mailCompId);

INSERT INTO `civicrm_report_instance`
    ( `domain_id`, `title`, `report_id`, `description`, `permission`, `form_values`)
VALUES 
    ( {$domainID}, 'Mailing Detail Report', 'mailing/detail', 'Provides reporting on Intended and Successful Deliveries, Unsubscribes and Opt-outs, Replies and Forwards.', '', '{literal}a:30:{s:6:"fields";a:6:{s:9:"sort_name";s:1:"1";s:12:"mailing_name";s:1:"1";s:11:"delivery_id";s:1:"1";s:14:"unsubscribe_id";s:1:"1";s:9:"optout_id";s:1:"1";s:5:"email";s:1:"1";}s:12:"sort_name_op";s:3:"has";s:15:"sort_name_value";s:0:"";s:6:"id_min";s:0:"";s:6:"id_max";s:0:"";s:5:"id_op";s:3:"lte";s:8:"id_value";s:0:"";s:13:"mailing_id_op";s:2:"in";s:16:"mailing_id_value";a:0:{}s:18:"delivery_status_op";s:2:"eq";s:21:"delivery_status_value";s:0:"";s:18:"is_unsubscribed_op";s:2:"eq";s:21:"is_unsubscribed_value";s:0:"";s:12:"is_optout_op";s:2:"eq";s:15:"is_optout_value";s:0:"";s:13:"is_replied_op";s:2:"eq";s:16:"is_replied_value";s:0:"";s:15:"is_forwarded_op";s:2:"eq";s:18:"is_forwarded_value";s:0:"";s:6:"gid_op";s:2:"in";s:9:"gid_value";a:0:{}s:9:"order_bys";a:1:{i:1;a:2:{s:6:"column";s:9:"sort_name";s:5:"order";s:3:"ASC";}}s:11:"description";s:21:"Mailing Detail Report";s:13:"email_subject";s:0:"";s:8:"email_to";s:0:"";s:8:"email_cc";s:0:"";s:10:"permission";s:1:"0";s:9:"parent_id";s:0:"";s:6:"groups";s:0:"";s:9:"domain_id";i:1;}{/literal}');

SELECT @reportlastID       := MAX(id) FROM civicrm_navigation where name = 'Reports';
SELECT @nav_max_weight     := MAX(ROUND(weight)) from civicrm_navigation WHERE parent_id = @reportlastID;

SET @instanceID:=LAST_INSERT_ID();
INSERT INTO civicrm_navigation
    ( domain_id, url, label, name, permission, permission_operator, parent_id, is_active, has_separator, weight )
VALUES
    ( {$domainID}, CONCAT('civicrm/report/instance/', @instanceID,'&reset=1'), '{ts escape="sql"}Mailing Detail Report{/ts}', 'Mailing Detail Report', 'administer CiviMail', 'OR', @reportlastID, '1', NULL, @nav_max_weight+1 );
UPDATE civicrm_report_instance SET navigation_id = LAST_INSERT_ID() WHERE id = @instanceID;

-- CRM-9600
ALTER TABLE `civicrm_custom_group` CHANGE `extends_entity_column_value` `extends_entity_column_value` VARCHAR( 255 ) CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL DEFAULT NULL COMMENT 'linking custom group for dynamic object.';

-- CRM-9534
ALTER TABLE `civicrm_prevnext_cache` ADD COLUMN is_selected tinyint(4) DEFAULT '0';

-- CRM-9834
-- add civicrm_batch table changes
-- add batch type and batch status option groups
-- add default profile for contribution and membership batch entry

-- CRM-9686
INSERT INTO `civicrm_state_province`(`country_id`, `abbreviation`, `name`) VALUES(1097, "LP", "La Paz");

-- CRM-9905
ALTER TABLE civicrm_contribution_page CHANGE COLUMN is_email_receipt is_email_receipt TINYINT(4) DEFAULT 0;

-- CRM-9783
CREATE TABLE `civicrm_sms_provider` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'SMS Provider ID',
  `name` varchar(64) unsigned DEFAULT NULL COMMENT 'Provider internal name points to option_value of option_group sms_provider_name',
  `title` varchar(64) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'Provider name visible to user',
  `username` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `password` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `api_type` int(10) unsigned NOT NULL COMMENT 'points to value in civicrm_option_value for group sms_api_type',
  `api_url` varchar(128) COLLATE utf8_unicode_ci DEFAULT NULL,
  `api_params` text COLLATE utf8_unicode_ci COMMENT 'the api params in xml, http or smtp format',
  `is_default` tinyint(4) DEFAULT '0',
  `is_active` tinyint(4) DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1;

ALTER TABLE `civicrm_mailing` ADD `sms_provider_id` int(10) unsigned NULL COMMENT 'FK to civicrm_sms_provider id ';
ALTER TABLE `civicrm_mailing` ADD CONSTRAINT `FK_civicrm_mailing_sms_provider_id` FOREIGN KEY (`sms_provider_id`) REFERENCES `civicrm_sms_provider` (`id`) ON DELETE SET NULL;

INSERT INTO 
   `civicrm_option_group` (`name`, `title`, `is_reserved`, `is_active`) 
VALUES 
   ('sms_provider_name', '{ts escape="sql"}Sms provider Internal Name{/ts}' , 1, 1);
SELECT @option_group_id_sms_provider_name := max(id) from civicrm_option_group where name = 'sms_provider_name';
    
INSERT INTO civicrm_option_value
     (option_group_id, {localize field='label'}label{/localize}, value, name, weight, filter, is_default, component_id)
VALUES
     (@option_group_id_sms_provider_name, 'Clickatell', 'Clickatell', 'Clickatell', 1, 0, NULL, NULL);

INSERT INTO 
   `civicrm_option_group` (`name`, `title`, `is_reserved`, `is_active`) 
VALUES 
    ( 'sms_api_type',  '{ts escape="sql"}Api Type{/ts}' , 1, 1 );
SELECT @option_group_id_sms_api_type := max(id) from civicrm_option_group where name = 'sms_api_type';
    
INSERT INTO civicrm_option_value
     (option_group_id, {localize field='label'}label{/localize}, value, name, weight, filter, is_default, is_reserved, component_id)
VALUES
     (@option_group_id_sms_api_type, 'http',  1, 'http',  1, NULL, 0, 1, NULL),
     (@option_group_id_sms_api_type, 'xml',   2, 'xml',   2, NULL, 0, 1, NULL),
     (@option_group_id_sms_api_type, 'smtp',  3, 'smtp',  3, NULL, 0, 1, NULL);

-- CRM-9784

SELECT @adminSystemSettingsID := MAX(id) FROM civicrm_navigation where name = 'System Settings';

INSERT INTO civicrm_navigation
    ( domain_id, url, label, name, permission, permission_operator, parent_id, is_active, has_separator, weight )
VALUES
    ( @domainID, 'civicrm/admin/sms/provider?reset=1', '{ts escape="sql" skip="true"}SMS Providers{/ts}', 'SMS Providers', 'administer CiviCRM', '', @adminSystemSettingsID, '1', NULL, 16 );

-- CRM-9799

SELECT @mailingsID := MAX(id) FROM civicrm_navigation where name = 'Mailings';

INSERT INTO civicrm_navigation
    ( domain_id, url, label, name, permission, permission_operator, parent_id, is_active, has_separator, weight )
VALUES    
    ( @domainID, 'civicrm/sms/send?reset=1', '{ts escape="sql" skip="true"}New SMS{/ts}', 'New SMS', 'administer CiviCRM', NULL, @mailingsID, '1', 1, 8 );

SELECT @fromEmailAddressesID := MAX(id) FROM civicrm_navigation where name = 'From Email Addresses';

UPDATE civicrm_navigation SET has_separator = 1 WHERE parent_id = @mailingsID AND name = 'From Email Addresses';

SELECT @option_group_id_act := max(id) from civicrm_option_group where name = 'activity_type';

INSERT INTO 
   `civicrm_option_value` (`option_group_id`, `label`, `value`, `name`, `grouping`, `filter`, `is_default`, `weight`, `description`, `is_optgroup`, `is_reserved`, `is_active`, `component_id`, `visibility_id`) 
VALUES
   (@option_group_id_act, '{ts escape="sql"}BULK SMS{/ts}', 34, 'BULK SMS', NULL, 1, NULL, 34, '{ts escape="sql"}BULK SMS{/ts}', 0, 1, 1, NULL, NULL);

ALTER TABLE `civicrm_mailing_recipients` ADD `phone_id` int(10) unsigned DEFAULT NULL;

ALTER TABLE `civicrm_mailing_recipients` ADD CONSTRAINT `FK_civicrm_mailing_recipients_phone_id` FOREIGN KEY (`phone_id`) REFERENCES `civicrm_phone` (`id`) ON DELETE CASCADE;

ALTER TABLE `civicrm_mailing_event_queue` ADD `phone_id` int(10) unsigned DEFAULT NULL;

ALTER TABLE `civicrm_mailing_event_queue` ADD CONSTRAINT `FK_civicrm_mailing_event_queue_phone_id` FOREIGN KEY (`phone_id`) REFERENCES `civicrm_phone` (`id`) ON DELETE CASCADE;

ALTER TABLE `civicrm_mailing_event_queue` CHANGE `email_id` `email_id` int(10) unsigned DEFAULT NULL;
ALTER TABLE `civicrm_mailing_recipients` CHANGE `email_id` `email_id` int(10) unsigned DEFAULT NULL;

