-- CRM-7088 giving respect to 'gotv campaign contacts' permission.
UPDATE   civicrm_navigation 
   SET   permission = CONCAT( permission, ',gotv campaign contacts' )
 WHERE   name in ( 'Other', 'Campaigns', 'Voter Listing' );

-- CRM-7151
SELECT @domainID        := min(id) FROM civicrm_domain;
SELECT @reportlastID    := id FROM civicrm_navigation where name = 'Reports';
SELECT @nav_max_weight  := MAX(ROUND(weight)) from civicrm_navigation WHERE parent_id = @reportlastID;

INSERT INTO `civicrm_report_instance`
    ( `domain_id`, `title`, `report_id`, `description`, `permission`, `form_values`)
VALUES 
    ( @domainID, 'Mail Bounce Report', 'Mailing/bounce', 'Bounce Report for mailings', 'access CiviMail', '{literal}a:30:{s:6:"fields";a:4:{s:2:"id";s:1:"1";s:10:"first_name";s:1:"1";s:9:"last_name";s:1:"1";s:5:"email";s:1:"1";}s:12:"sort_name_op";s:3:"has";s:15:"sort_name_value";s:0:"";s:9:"source_op";s:3:"has";s:12:"source_value";s:0:"";s:6:"id_min";s:0:"";s:6:"id_max";s:0:"";s:5:"id_op";s:3:"lte";s:8:"id_value";s:0:"";s:15:"mailing_name_op";s:2:"eq";s:18:"mailing_name_value";s:0:"";s:19:"bounce_type_name_op";s:2:"eq";s:22:"bounce_type_name_value";s:0:"";s:6:"gid_op";s:2:"in";s:9:"gid_value";a:0:{}s:8:"tagid_op";s:2:"in";s:11:"tagid_value";a:0:{}s:11:"custom_1_op";s:2:"in";s:14:"custom_1_value";a:0:{}s:11:"custom_2_op";s:2:"in";s:14:"custom_2_value";a:0:{}s:17:"custom_3_relative";s:1:"0";s:13:"custom_3_from";s:0:"";s:11:"custom_3_to";s:0:"";s:11:"description";s:26:"Bounce Report for mailings";s:13:"email_subject";s:0:"";s:8:"email_to";s:0:"";s:8:"email_cc";s:0:"";s:10:"permission";s:15:"access CiviMail";s:9:"domain_id";i:1;}{/literal}');

SET @instanceID:=LAST_INSERT_ID( );
INSERT INTO civicrm_navigation
    ( domain_id, url, label, name, permission, permission_operator, parent_id, is_active, has_separator, weight )
VALUES
    ( @domainID, CONCAT('civicrm/report/instance/', @instanceID,'&reset=1'), '{ts escape="sql"}Mail Bounce Report{/ts}', '{literal}Mail Bounce Report {/literal}', 'access CiviMail', '',@reportlastID, '1', NULL,@nav_max_weight+1  );

UPDATE civicrm_report_instance SET navigation_id = LAST_INSERT_ID() WHERE id = @instanceID;

INSERT INTO `civicrm_report_instance`
    ( `domain_id`, `title`, `report_id`, `description`, `permission`, `form_values`)
VALUES 
    ( @domainID, 'Mail Summary Report', 'Mailing/summary','Summary statistics for mailings','access CiviMail','{literal}a:21:{s:6:"fields";a:1:{s:4:"name";s:1:"1";}s:15:"is_completed_op";s:2:"eq";s:18:"is_completed_value";s:1:"1";s:9:"status_op";s:3:"has";s:12:"status_value";s:8:"Complete";s:11:"is_test_min";s:0:"";s:11:"is_test_max";s:0:"";s:10:"is_test_op";s:3:"lte";s:13:"is_test_value";s:1:"0";s:19:"start_date_relative";s:9:"this.year";s:15:"start_date_from";s:0:"";s:13:"start_date_to";s:0:"";s:17:"end_date_relative";s:9:"this.year";s:13:"end_date_from";s:0:"";s:11:"end_date_to";s:0:"";s:11:"description";s:31:"Summary statistics for mailings";s:13:"email_subject";s:0:"";s:8:"email_to";s:0:"";s:8:"email_cc";s:0:"";s:10:"permission";s:15:"access CiviMail";s:9:"domain_id";i:1;}{/literal}');

SET @instanceID:=LAST_INSERT_ID( );
INSERT INTO civicrm_navigation
    ( domain_id, url, label, name, permission, permission_operator, parent_id, is_active, has_separator, weight )
VALUES
    ( @domainID, CONCAT('civicrm/report/instance/', @instanceID,'&reset=1'), '{ts escape="sql"}Mail Summary Report{/ts}', '{literal}Mail Summary Report{/literal}', 'access CiviMail', '',@reportlastID, '1', NULL,@nav_max_weight+2 );

UPDATE civicrm_report_instance SET navigation_id = LAST_INSERT_ID() WHERE id = @instanceID;

INSERT INTO `civicrm_report_instance`
    ( `domain_id`, `title`, `report_id`, `description`, `permission`, `form_values`)
VALUES 
    ( @domainID, 'Mail Opened Report', 'Mailing/opened', 'Display contacts who opened emails from a mailing', 'access CiviMail', '{literal}a:28:{s:6:"fields";a:4:{s:2:"id";s:1:"1";s:10:"first_name";s:1:"1";s:9:"last_name";s:1:"1";s:5:"email";s:1:"1";}s:12:"sort_name_op";s:3:"has";s:15:"sort_name_value";s:0:"";s:9:"source_op";s:3:"has";s:12:"source_value";s:0:"";s:6:"id_min";s:0:"";s:6:"id_max";s:0:"";s:5:"id_op";s:3:"lte";s:8:"id_value";s:0:"";s:15:"mailing_name_op";s:2:"eq";s:18:"mailing_name_value";s:0:"";s:6:"gid_op";s:2:"in";s:9:"gid_value";a:0:{}s:8:"tagid_op";s:2:"in";s:11:"tagid_value";a:0:{}s:11:"custom_1_op";s:2:"in";s:14:"custom_1_value";a:0:{}s:11:"custom_2_op";s:2:"in";s:14:"custom_2_value";a:0:{}s:17:"custom_3_relative";s:1:"0";s:13:"custom_3_from";s:0:"";s:11:"custom_3_to";s:0:"";s:11:"description";s:49:"Display contacts who opened emails from a mailing";s:13:"email_subject";s:0:"";s:8:"email_to";s:0:"";s:8:"email_cc";s:0:"";s:10:"permission";s:15:"access CiviMail";s:9:"domain_id";i:1;}{/literal}');

SET @instanceID:=LAST_INSERT_ID( );
INSERT INTO civicrm_navigation
    ( domain_id, url, label, name, permission, permission_operator, parent_id, is_active, has_separator, weight )
VALUES
    ( @domainID, CONCAT('civicrm/report/instance/', @instanceID,'&reset=1'), '{ts escape="sql"}Mail Opened Report{/ts}', '{literal}Mail Opened Report{/literal}', 'access CiviMail', '',@reportlastID, '1', NULL, @nav_max_weight+3 );

UPDATE civicrm_report_instance SET navigation_id = LAST_INSERT_ID() WHERE id = @instanceID;
INSERT INTO `civicrm_report_instance`
    ( `domain_id`, `title`, `report_id`, `description`, `permission`, `form_values`)
VALUES 
    ( @domainID, 'Mail Clickthrough Report', 'Mailing/clicks', 'Display clicks from each mailing', 'access CiviMail', '{literal}a:28:{s:6:"fields";a:4:{s:2:"id";s:1:"1";s:10:"first_name";s:1:"1";s:9:"last_name";s:1:"1";s:5:"email";s:1:"1";}s:12:"sort_name_op";s:3:"has";s:15:"sort_name_value";s:0:"";s:9:"source_op";s:3:"has";s:12:"source_value";s:0:"";s:6:"id_min";s:0:"";s:6:"id_max";s:0:"";s:5:"id_op";s:3:"lte";s:8:"id_value";s:0:"";s:15:"mailing_name_op";s:2:"eq";s:18:"mailing_name_value";s:0:"";s:6:"gid_op";s:2:"in";s:9:"gid_value";a:0:{}s:8:"tagid_op";s:2:"in";s:11:"tagid_value";a:0:{}s:11:"custom_1_op";s:2:"in";s:14:"custom_1_value";a:0:{}s:11:"custom_2_op";s:2:"in";s:14:"custom_2_value";a:0:{}s:17:"custom_3_relative";s:1:"0";s:13:"custom_3_from";s:0:"";s:11:"custom_3_to";s:0:"";s:11:"description";s:32:"Display clicks from each mailing";s:13:"email_subject";s:0:"";s:8:"email_to";s:0:"";s:8:"email_cc";s:0:"";s:10:"permission";s:15:"access CiviMail";s:9:"domain_id";i:1;}{/literal}');

SET @instanceID:=LAST_INSERT_ID( );
INSERT INTO civicrm_navigation
    ( domain_id, url, label, name, permission, permission_operator, parent_id, is_active, has_separator, weight )
VALUES
    ( @domainID, CONCAT('civicrm/report/instance/', @instanceID,'&reset=1'), '{ts escape="sql"}Mail Clickthrough Report{/ts}', '{literal}Mail Clickthrough Report{/literal}', 'access CiviMail', '',@reportlastID, '1', NULL, @nav_max_weight+4 );

UPDATE civicrm_report_instance SET navigation_id = LAST_INSERT_ID() WHERE id = @instanceID;

