-- CRM-9542 mailing detail report template
SELECT @option_group_id_report := MAX(id)     FROM civicrm_option_group WHERE name = 'report_template';
SELECT @weight                 := MAX(weight) FROM civicrm_option_value WHERE option_group_id = @option_group_id_report;
SELECT @mailCompId       := MAX(id)     FROM civicrm_component where name = 'CiviMail';
INSERT INTO civicrm_option_value
  (option_group_id, {localize field='label'}label{/localize}, value, name, weight, {localize field='description'}description{/localize}, is_active, component_id) VALUES
  (@option_group_id_report, {localize}'Mail Detail Report'{/localize}, 'mailing/detail', 'CRM_Report_Form_Mailing_Detail', @weight := @weight + 1, {localize}'Provides reporting on Intended and Successful Deliveries, Unsubscribes and Opt-outs, Replies and Forwards.'{/localize}, 0, @mailCompId);


INSERT INTO `civicrm_report_instance` (`id`, `domain_id`, `title`, `report_id`, `description`, `permission`, `grouprole`, `form_values`, `is_active`, `email_subject`, `email_to`, `email_cc`, `header`, `footer`, `navigation_id`) VALUES

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