{include file='../CRM/Upgrade/4.3.alpha1.msg_template/civicrm_msg_template.tpl'}
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

-- CRM-10771
ALTER TABLE civicrm_uf_field
  ADD `is_multi_summary` tinyint(4) DEFAULT '0' COMMENT 'Include in multi-record listing?';

-- CRM-1115
-- note that country names are not translated in the DB
SELECT @region_id   := max(id) from civicrm_worldregion where name = "Europe and Central Asia";
INSERT INTO civicrm_country (name,iso_code,region_id,is_province_abbreviated) VALUES("Kosovo", "XK", @region_id, 0);

UPDATE civicrm_country SET name = 'Libya' WHERE name LIKE 'Libyan%';
UPDATE civicrm_country SET name = 'Congo, Republic of the' WHERE name = 'Congo';

-- CRM-10621 Add component report links to reports menu for upgrade
SELECT @reportlastID       := MAX(id) FROM civicrm_navigation where name = 'Reports';
SELECT @max_weight     := MAX(ROUND(weight)) from civicrm_navigation WHERE parent_id = @reportlastID;

INSERT INTO civicrm_navigation
    ( domain_id, url, label, name, permission, permission_operator, parent_id, is_active, has_separator, weight )
VALUES    
    ( {$domainID}, 'civicrm/report/list&compid=99&reset=1', '{ts escape="sql" skip="true"}Contact Reports{/ts}', 'Contact Reports', 'administer CiviCRM', '', @reportlastID, '1', 0, (SELECT @max_weight := @max_weight+1) );
INSERT INTO civicrm_navigation
    ( domain_id, url, label, name, permission, permission_operator, parent_id, is_active, has_separator, weight )
VALUES    
    ( {$domainID}, 'civicrm/report/list&compid=2&reset=1', '{ts escape="sql" skip="true"}Contribution Reports{/ts}', 'Contribution Reports', 'access CiviContribute', '', @reportlastID, '1', 0, (SELECT @max_weight := @max_weight+1) );
INSERT INTO civicrm_navigation
    ( domain_id, url, label, name, permission, permission_operator, parent_id, is_active, has_separator, weight )
VALUES    
    ( {$domainID}, 'civicrm/report/list&compid=6&reset=1', '{ts escape="sql" skip="true"}Pledge Reports{/ts}', 'Pledge Reports', 'access CiviPledge', '', @reportlastID, '1', 0, (SELECT @max_weight := @max_weight+1) );
INSERT INTO civicrm_navigation
    ( domain_id, url, label, name, permission, permission_operator, parent_id, is_active, has_separator, weight )
VALUES    
    ( {$domainID}, 'civicrm/report/list&compid=1&reset=1', '{ts escape="sql" skip="true"}Event Reports{/ts}', 'Event Reports', 'access CiviEvent', '', @reportlastID, '1', 0, (SELECT @max_weight := @max_weight+1));
INSERT INTO civicrm_navigation
    ( domain_id, url, label, name, permission, permission_operator, parent_id, is_active, has_separator, weight )
VALUES    
    ( {$domainID}, 'civicrm/report/list&compid=4&reset=1', '{ts escape="sql" skip="true"}Mailing Reports{/ts}', 'Mailing Reports', 'access CiviMail', '', @reportlastID, '1', 0,   (SELECT @max_weight := @max_weight+1));
INSERT INTO civicrm_navigation
    ( domain_id, url, label, name, permission, permission_operator, parent_id, is_active, has_separator, weight )
VALUES    
    ( {$domainID}, 'civicrm/report/list&compid=3&reset=1', '{ts escape="sql" skip="true"}Membership Reports{/ts}', 'Membership Reports', 'access CiviMember', '', @reportlastID, '1', 0, (SELECT @max_weight := @max_weight+1));
INSERT INTO civicrm_navigation
    ( domain_id, url, label, name, permission, permission_operator, parent_id, is_active, has_separator, weight )
VALUES    
    ( {$domainID}, 'civicrm/report/list&compid=9&reset=1', '{ts escape="sql" skip="true"}Campaign Reports{/ts}', 'Campaign Reports', 'interview campaign contacts,release campaign contacts,reserve campaign contacts,manage campaign,administer CiviCampaign,gotv campaign contacts', 'OR', @reportlastID, '1', 0, (SELECT @max_weight := @max_weight+1));
INSERT INTO civicrm_navigation
    ( domain_id, url, label, name, permission, permission_operator, parent_id, is_active, has_separator, weight )
VALUES    
    ( {$domainID}, 'civicrm/report/list&compid=7&reset=1', '{ts escape="sql" skip="true"}Case Reports{/ts}', 'Case Reports', 'access my cases and activities,access all cases and activities,administer CiviCase', 'OR', @reportlastID, '1', 0, (SELECT @max_weight := @max_weight+1) );
INSERT INTO civicrm_navigation
    ( domain_id, url, label, name, permission, permission_operator, parent_id, is_active, has_separator, weight )
VALUES    
    ( {$domainID}, 'civicrm/report/list&compid=5&reset=1', '{ts escape="sql" skip="true"}Grant Reports{/ts}', 'Grant Reports', 'access CiviGrant', '', @reportlastID, '1', 0, (SELECT @max_weight := @max_weight+1) );

-- CRM-11148 Multiple terms membership signup and renewal via price set
ALTER TABLE `civicrm_price_field_value` ADD COLUMN `membership_num_terms` INT(10) NULL DEFAULT NULL COMMENT 'Maximum number of related memberships.' AFTER `membership_type_id`;

