-- get domain id 
SELECT  @domainID := min(id) FROM civicrm_domain;

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
  
-- CRM-8902
    UPDATE civicrm_navigation SET permission ='add cases,access all cases and activities', permission_operator = 'OR'
    WHERE civicrm_navigation.name= 'New Case';

-- CRM-8780

-- add the settings table
 CREATE TABLE `civicrm_setting` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `group_name` varchar(64) COLLATE utf8_unicode_ci NOT NULL COMMENT 'group name for setting element, useful in caching setting elements',
  `name` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'Unique name for setting',
  `value` text COLLATE utf8_unicode_ci COMMENT 'data associated with this group / name combo',
  `domain_id` int(10) unsigned NOT NULL COMMENT 'Which Domain is this menu item for',
  `contact_id` int(10) unsigned DEFAULT NULL COMMENT 'FK to Contact ID if the setting is localized to a contact',
  `is_domain` tinyint(4) DEFAULT NULL COMMENT 'Is this setting a contact specific or site wide setting?',
  `component_id` int(10) unsigned DEFAULT NULL COMMENT 'Component that this menu item belongs to',
  `created_date` datetime DEFAULT NULL COMMENT 'When was the setting created',
  `created_id` int(10) unsigned DEFAULT NULL COMMENT 'FK to civicrm_contact, who created this setting',
  PRIMARY KEY (`id`),
  KEY `index_group_name` (`group_name`,`name`),
  KEY `FK_civicrm_setting_domain_id` (`domain_id`),
  KEY `FK_civicrm_setting_contact_id` (`contact_id`),
  KEY `FK_civicrm_setting_component_id` (`component_id`),
  KEY `FK_civicrm_setting_created_id` (`created_id`),
  CONSTRAINT `FK_civicrm_setting_domain_id` FOREIGN KEY (`domain_id`) REFERENCES `civicrm_domain` (`id`) ON DELETE CASCADE,
  CONSTRAINT `FK_civicrm_setting_contact_id` FOREIGN KEY (`contact_id`) REFERENCES `civicrm_contact` (`id`) ON DELETE CASCADE,
  CONSTRAINT `FK_civicrm_setting_component_id` FOREIGN KEY (`component_id`) REFERENCES `civicrm_component` (`id`),
  CONSTRAINT `FK_civicrm_setting_created_id` FOREIGN KEY (`created_id`) REFERENCES `civicrm_contact` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

-- TODO
-- copy over all the current settings to the settings table
-- since we need to serialize the values from the DB into the new table
-- we do this in PHP
-- finally, drop the preferences table
-- When we are done with it, we'll also drop the preferences table from PHP


-- CRM-8508
    SELECT @caseCompId := id FROM `civicrm_component` where `name` like 'CiviCase';

    SELECT @option_group_id_activity_type := max(id) from civicrm_option_group where name = 'activity_type';
    SELECT @max_val    := MAX(ROUND(op.value)) FROM civicrm_option_value op WHERE op.option_group_id  = @option_group_id_activity_type;
    SELECT @max_wt     := max(weight) from civicrm_option_value where option_group_id=@option_group_id_activity_type;

    INSERT INTO civicrm_option_value
      (option_group_id,                {localize field='label'}label{/localize}, {localize field='description'}description{/localize}, value,                           name,               weight,                        filter, component_id)
    VALUES
        (@option_group_id_activity_type, {localize}'Change Custom Data'{/localize},{localize}''{/localize},                              (SELECT @max_val := @max_val+1), 'Change Custom Data', (SELECT @max_wt := @max_wt+1), 0, @caseCompId);

-- CRM-8739
    Update civicrm_navigation set label =  '{ts escape="sql" skip="true"}Cleanup Caches and Update Paths{/ts}', name = 'Cleanup Caches and Update Paths' where name = 'Update Directory Path and URL';
    
    Update civicrm_menu set title = 'Cleanup Caches and Update Paths' where path = 'civicrm/admin/setting/updateConfigBackend';
    
-- CRM-8855
    SELECT @option_group_id_udOpt := max(id) from civicrm_option_group where name = 'user_dashboard_options';
    SELECT @max_val    := MAX(ROUND(op.value)) FROM civicrm_option_value op WHERE op.option_group_id  = @option_group_id_udOpt;
    SELECT @max_wt     := max(weight) from civicrm_option_value where option_group_id=@option_group_id_udOpt;

    INSERT INTO civicrm_option_value
      (option_group_id,                {localize field='label'}label{/localize}, value, name, weight, filter, is_default, component_id)
    VALUES
        (@option_group_id_udOpt, {localize}'Assigned Activities'{/localize},  (SELECT @max_val := @max_val+1), 'Assigned Activities', (SELECT @max_wt := @max_wt+1), 0, NULL, NULL);

-- CRM-8737
   ALTER TABLE `civicrm_event` ADD `is_share` TINYINT( 4 ) NULL DEFAULT '1' COMMENT 'Can people share the event through social media?';
   ALTER TABLE `civicrm_contribution_page` ADD `is_share` TINYINT(4) NULL DEFAULT '1' COMMENT 'Can people share the contribution page through social media?'; 

-- CRM-8357
ALTER TABLE `civicrm_contact` CHANGE `contact_sub_type` `contact_sub_type` VARCHAR( 255 ) CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL DEFAULT NULL COMMENT 'May be used to over-ride contact view and edit templates.'

UPDATE civicrm_contact SET contact_sub_type = CONCAT('', contact_sub_type, '');

-- CRM-6811
INSERT INTO `civicrm_dashboard` 
    ( `domain_id`, {localize field='label'}`label`{/localize}, `url`, `permission`, `permission_operator`, `column_no`, `is_minimized`, `is_active`, `weight`, `fullscreen_url`, `is_fullscreen`, `is_reserved`) 
    VALUES 
    ( @domainID, '{localize}Case Dashboard Dashlet{/localize}', 'civicrm/dashlet/casedashboard&reset=1&snippet=4', 'access CiviCase', NULL , 0, 0, 1, 4, 'civicrm/dashlet/casedashboard&reset=1&snippet=4&context=dashletFullscreen', 1, 1);
