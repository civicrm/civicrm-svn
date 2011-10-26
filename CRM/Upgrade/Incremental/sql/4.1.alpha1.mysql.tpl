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

-- CRM-9059 Admin menu rebuild
SELECT @domainID := min(id) FROM civicrm_domain;
SELECT @adminlastID := id   FROM civicrm_navigation where name = 'Administer';
SELECT @customizeOld := id  FROM civicrm_navigation where name = 'Customize';
SELECT @configureOld := id  FROM civicrm_navigation where name = 'Configure';
SELECT @globalOld := id     FROM civicrm_navigation where name = 'Global Settings';
SELECT @manageOld := id     FROM civicrm_navigation where name = 'Manage';
SELECT @optionsOld := id    FROM civicrm_navigation where name = 'Option Lists';
SELECT @customizeOld := id  FROM civicrm_navigation where name = 'Customize';

DELETE from civicrm_navigation WHERE parent_id = @globalOld;
DELETE from civicrm_navigation WHERE parent_id IN (@customizeOld, @configureOld, @manageOld, @optionsOld);
DELETE from civicrm_navigation WHERE id IN (@customizeOld, @configureOld, @manageOld, @optionsOld);
UPDATE civicrm_navigation SET weight = 9 WHERE name = 'CiviCampaign' AND parent_id = @adminlastID;
UPDATE civicrm_navigation SET weight = 10 WHERE name = 'CiviCase' AND parent_id = @adminlastID;
UPDATE civicrm_navigation SET weight = 11 WHERE name = 'CiviContribute' AND parent_id = @adminlastID;
UPDATE civicrm_navigation SET weight = 12 WHERE name = 'CiviEvent' AND parent_id = @adminlastID;
UPDATE civicrm_navigation SET weight = 13 WHERE name = 'CiviGrant' AND parent_id = @adminlastID;
UPDATE civicrm_navigation SET weight = 14 WHERE name = 'CiviMail' AND parent_id = @adminlastID;
UPDATE civicrm_navigation SET weight = 15 WHERE name = 'CiviMember' AND parent_id = @adminlastID;
UPDATE civicrm_navigation SET weight = 16 WHERE name = 'CiviReport' AND parent_id = @adminlastID;

INSERT INTO civicrm_navigation
    ( domain_id, url, label, name, permission, permission_operator, parent_id, is_active, has_separator, weight )
VALUES    
    ( @domainID, 'civicrm/admin&reset=1', '{ts escape="sql" skip="true"}Administration Console{/ts}', 'Administration Console', 'administer CiviCRM', '', @adminlastID, '1', NULL, 1 );

SET @adminConsolelastID:=LAST_INSERT_ID();
INSERT INTO civicrm_navigation
    ( domain_id, url, label, name, permission, permission_operator, parent_id, is_active, has_separator, weight )
VALUES    
    ( @domainID, 'civicrm/admin/configtask&reset=1', '{ts escape="sql" skip="true"}Configuration Checklist{/ts}', 'Configuration Checklist', 'administer CiviCRM', '', @adminConsolelastID, '1', NULL, 1 );

INSERT INTO civicrm_navigation
    ( domain_id, url, label, name, permission, permission_operator, parent_id, is_active, has_separator, weight )
VALUES     
    ( @domainID, NULL, '{ts escape="sql" skip="true"}Customize Data and Screens{/ts}', 'Customize Data and Screens', 'administer CiviCRM', '', @adminlastID, '1', NULL, 3 );

SET @CustomizelastID:=LAST_INSERT_ID();
INSERT INTO civicrm_navigation
    ( domain_id, url, label, name, permission, permission_operator, parent_id, is_active, has_separator, weight )
VALUES
    ( @domainID, 'civicrm/admin/custom/group&reset=1',      '{ts escape="sql" skip="true"}Custom Fields{/ts}', 'Custom Fields',                             'administer CiviCRM', '',   @CustomizelastID, '1', NULL, 1 ), 
    ( @domainID, 'civicrm/admin/uf/group&reset=1',          '{ts escape="sql" skip="true"}Profiles{/ts}', 'Profiles',                                       'administer CiviCRM', '',   @CustomizelastID, '1', NULL, 2 ), 
    ( @domainID, 'civicrm/admin/tag&reset=1',               '{ts escape="sql" skip="true"}Tags (Categories){/ts}', 'Tags (Categories)',                     'administer CiviCRM', '',   @CustomizelastID, '1', NULL, 3 ), 
    ( @domainID, 'civicrm/admin/options/activity_type&reset=1&group=activity_type', '{ts escape="sql" skip="true"}Activity Types{/ts}', 'Activity Types',   'administer CiviCRM', '',   @CustomizelastID, '1', NULL, 4 ), 
    ( @domainID, 'civicrm/admin/reltype&reset=1',           '{ts escape="sql" skip="true"}Relationship Types{/ts}', 'Relationship Types',                   'administer CiviCRM', '',   @CustomizelastID, '1', NULL, 5 ), 
    ( @domainID, 'civicrm/admin/options/subtype&reset=1',   '{ts escape="sql" skip="true"}Contact Types{/ts}','Contact Types',                              'administer CiviCRM', '',   @CustomizelastID, '1', NULL, 6 ),
    ( @domainID, 'civicrm/admin/setting/preferences/display&reset=1',   '{ts escape="sql" skip="true"}Display Preferences{/ts}', 'Display Preferences',     'administer CiviCRM', '',   @CustomizelastID, '1', NULL, 9 ), 
    ( @domainID, 'civicrm/admin/setting/search&reset=1',    '{ts escape="sql" skip="true"}Search Preferences{/ts}',    'Search Preferences',                'administer CiviCRM', '',   @CustomizelastID, '1', NULL, 10 ), 
    ( @domainID, 'civicrm/admin/menu&reset=1',              '{ts escape="sql" skip="true"}Navigation Menu{/ts}', 'Navigation Menu',                         'administer CiviCRM', '',   @CustomizelastID, '1', NULL, 11 ), 
    ( @domainID, 'civicrm/admin/options/wordreplacements&reset=1','{ts escape="sql" skip="true"}Word Replacements{/ts}','Word Replacements',                'administer CiviCRM', '',   @CustomizelastID, '1', NULL, 12 ),
    ( @domainID, 'civicrm/admin/options/custom_search&reset=1&group=custom_search', '{ts escape="sql" skip="true"}Manage Custom Searches{/ts}', 'Manage Custom Searches', 'administer CiviCRM', '', @CustomizelastID, '1', NULL, 13 ),
    ( @domainID, 'civicrm/admin/extensions&reset=1',        '{ts escape="sql" skip="true"}Manage Extensions{/ts}', 'Manage Extensions', 'administer CiviCRM', '', @CustomizelastID, '1', NULL, 14 );

INSERT INTO civicrm_navigation
    ( domain_id, url, label, name, permission, permission_operator, parent_id, is_active, has_separator, weight )
VALUES     
    ( @domainID, NULL, '{ts escape="sql" skip="true"}Dropdown Options{/ts}', 'Dropdown Options', 'administer CiviCRM', '', @CustomizelastID, '1', NULL, 8 );

SET @optionListlastID:=LAST_INSERT_ID();
INSERT INTO civicrm_navigation
    ( domain_id, url, label, name, permission, permission_operator, parent_id, is_active, has_separator, weight )
VALUES         
    ( @domainID, 'civicrm/admin/options/gender&reset=1&group=gender',                                                  '{ts escape="sql" skip="true"}Gender Options{/ts}',         'Gender Options',                           'administer CiviCRM', '',   @optionListlastID, '1', NULL,  1 ), 
    ( @domainID, 'civicrm/admin/options/individual_prefix&group=individual_prefix&reset=1',                            '{ts escape="sql" skip="true"}Individual Prefixes (Ms, Mr...){/ts}', 'Individual Prefixes (Ms, Mr...)', 'administer CiviCRM', '',   @optionListlastID, '1', NULL,  2 ), 
    ( @domainID, 'civicrm/admin/options/individual_suffix&group=individual_suffix&reset=1',                            '{ts escape="sql" skip="true"}Individual Suffixes (Jr, Sr...){/ts}', 'Individual Suffixes (Jr, Sr...)', 'administer CiviCRM', '',   @optionListlastID, '1', NULL,  3 ), 
    ( @domainID, 'civicrm/admin/options/instant_messenger_service&group=instant_messenger_service&reset=1',            '{ts escape="sql" skip="true"}Instant Messenger Services{/ts}',     'Instant Messenger Services',       'administer CiviCRM', '',   @optionListlastID, '1', NULL,  4 ), 
    ( @domainID, 'civicrm/admin/locationType&reset=1',                                                                 '{ts escape="sql" skip="true"}Location Types (Home, Work...){/ts}', 'Location Types (Home, Work...)',   'administer CiviCRM', '',   @optionListlastID, '1', NULL,  5 ), 
    ( @domainID, 'civicrm/admin/options/mobile_provider&group=mobile_provider&reset=1',                                '{ts escape="sql" skip="true"}Mobile Phone Providers{/ts}', 'Mobile Phone Providers',                   'administer CiviCRM', '',   @optionListlastID, '1', NULL,  6 ), 
    ( @domainID, 'civicrm/admin/options/phone_type&group=phone_type&reset=1',                                          '{ts escape="sql" skip="true"}Phone Types{/ts}',            'Phone Types',                              'administer CiviCRM', '',   @optionListlastID, '1', NULL,  7 ),  
    ( @domainID, 'civicrm/admin/options/website_type&group=website_type&reset=1',                                      '{ts escape="sql" skip="true"}Website Types{/ts}',          'Website Types',                            'administer CiviCRM', '',   @optionListlastID, '1', NULL,  8 );

INSERT INTO civicrm_navigation
    ( domain_id, url, label, name, permission, permission_operator, parent_id, is_active, has_separator, weight )
VALUES
    ( @domainID, NULL, '{ts escape="sql" skip="true"}Communications{/ts}', 'Communications', 'administer CiviCRM', '', @adminlastID, '1', NULL, 4 );

SET @communicationslastID:=LAST_INSERT_ID();
INSERT INTO civicrm_navigation
    ( domain_id, url, label, name, permission, permission_operator, parent_id, is_active, has_separator, weight )
VALUES    
    ( @domainID, 'civicrm/admin/domain&action=update&reset=1',         '{ts escape="sql" skip="true"}Organization Address and Contact Info{/ts}', 'Organization Address and Contact Info',                                      'administer CiviCRM', '', @communicationslastID, '1', NULL, 1 ), 
    ( @domainID, 'civicrm/admin/options/from_email_address&group=from_email_address&reset=1', '{ts escape="sql" skip="true"}FROM Email Addresses{/ts}', 'FROM Email Addresses',                                                 'administer CiviCRM', '', @communicationslastID, '1', NULL, 2 ), 
    ( @domainID, 'civicrm/admin/messageTemplates&reset=1',             '{ts escape="sql" skip="true"}Message Templates{/ts}',      'Message Templates',                                                                         'administer CiviCRM', '', @communicationslastID, '1', NULL, 3 ), 
    ( @domainID, 'civicrm/admin/scheduleReminders&reset=1',              '{ts escape="sql" skip="true"}Schedule Reminders{/ts}',    'Schedule Reminders',                                                                       'administer CiviCRM', '', @communicationslastID, '1', NULL, 4 ),
    ( @domainID, 'civicrm/admin/options/preferred_communication_method&group=preferred_communication_method&reset=1',  '{ts escape="sql" skip="true"}Preferred Communication Methods{/ts}', 'Preferred Communication Methods',  'administer CiviCRM', '', @communicationslastID, '1', NULL, 5 ),
    ( @domainID, 'civicrm/admin/labelFormats&reset=1',                                  '{ts escape="sql" skip="true"}Label Formats{/ts}',                 'Label Formats',                                                     'administer CiviCRM', '', @communicationslastID, '1', NULL, 6 ),
    ( @domainID, 'civicrm/admin/pdfFormats&reset=1',                                    '{ts escape="sql" skip="true"}Print Page (PDF) Formats{/ts}',      'Print Page (PDF) Formats',                                          'administer CiviCRM', '', @communicationslastID, '1', NULL, 7 ), 
    ( @domainID, 'civicrm/admin/options/email_greeting&group=email_greeting&reset=1',   '{ts escape="sql" skip="true"}Email Greeting Formats{/ts}',        'Email Greeting Formats',                                            'administer CiviCRM', '', @communicationslastID, '1', NULL, 8 ), 
    ( @domainID, 'civicrm/admin/options/postal_greeting&group=postal_greeting&reset=1', '{ts escape="sql" skip="true"}Postal Greeting Formats{/ts}',       'Postal Greeting Formats',                                           'administer CiviCRM', '', @communicationslastID, '1', NULL, 9 ), 
    ( @domainID, 'civicrm/admin/options/addressee&group=addressee&reset=1',             '{ts escape="sql" skip="true"}Addressee Formats{/ts}',             'Addressee Formats',                                                 'administer CiviCRM', '', @communicationslastID, '1', NULL, 10 );

INSERT INTO civicrm_navigation
    ( domain_id, url, label, name, permission, permission_operator, parent_id, is_active, has_separator, weight )
VALUES     
    ( @domainID, NULL, '{ts escape="sql" skip="true"}Localization{/ts}', 'Localization', 'administer CiviCRM', '', @adminlastID, '1', NULL, 6 );

SET @locallastID:=LAST_INSERT_ID();
INSERT INTO civicrm_navigation
    ( domain_id, url, label, name, permission, permission_operator, parent_id, is_active, has_separator, weight )
VALUES    
    ( @domainID, 'civicrm/admin/setting/localization&reset=1',          '{ts escape="sql" skip="true"}Languages, Currency, Locations{/ts}', 'Languages, Currency, Locations',   'administer CiviCRM', '', @locallastID, '1', NULL, 1 ), 
    ( @domainID, 'civicrm/admin/setting/preferences/address&reset=1',   '{ts escape="sql" skip="true"}Address Settings{/ts}',               'Address Settings',                 'administer CiviCRM', '', @locallastID, '1', NULL, 2 ), 
    ( @domainID, 'civicrm/admin/setting/date&reset=1',                  '{ts escape="sql" skip="true"}Date Settings{/ts}',                  'Date Settings',                    'administer CiviCRM', '', @locallastID, '1', NULL, 3 );

INSERT INTO civicrm_navigation
    ( domain_id, url, label, name, permission, permission_operator, parent_id, is_active, has_separator, weight )
VALUES     
    ( @domainID, NULL,                                                  '{ts escape="sql" skip="true"}Users and Permissions{/ts}',          'Users and Permissions',            'administer CiviCRM', '', @adminlastID, '1', NULL, 7 );

SET @usersPermslastID:=LAST_INSERT_ID();
INSERT INTO civicrm_navigation
    ( domain_id, url, label, name, permission, permission_operator, parent_id, is_active, has_separator, weight )
VALUES    
    ( @domainID, 'civicrm/admin/access&reset=1',       '{ts escape="sql" skip="true"}Permissions (Access Control){/ts}',    'Permissions (Access Control)',     'administer CiviCRM', '', @usersPermslastID, '1', NULL, 1 ),
    ( @domainID, 'civicrm/admin/synchUser&reset=1',    '{ts escape="sql" skip="true"}Synchronize Users to Contacts{/ts}',   'Synchronize Users to Contacts',    'administer CiviCRM', '', @usersPermslastID, '1', NULL, 2 );

INSERT INTO civicrm_navigation
    ( domain_id, url, label, name, permission, permission_operator, parent_id, is_active, has_separator, weight )
VALUES
    ( @domainID, NULL,                                 '{ts escape="sql" skip="true"}System Settings{/ts}',                 'System Settings',                      'administer CiviCRM', '', @adminlastID, '1', NULL, 8 );

SET @systemSettingslastID:=LAST_INSERT_ID();
INSERT INTO civicrm_navigation
    ( domain_id, url, label, name, permission, permission_operator, parent_id, is_active, has_separator, weight )
VALUES
    ( @domainID, 'civicrm/admin/setting/component&reset=1', '{ts escape="sql" skip="true"}Enable CiviCRM Components{/ts}', 'Enable Components',                                 'administer CiviCRM', '', @systemSettingslastID, '1', NULL, 1 ), 
    ( @domainID, 'civicrm/admin/setting/smtp&reset=1',                  '{ts escape="sql" skip="true"}Outbound Email (SMTP/Sendmail){/ts}', 'Outbound Email',                   'administer CiviCRM', '', @systemSettingslastID, '1', NULL, 2 ), 
    ( @domainID, 'civicrm/admin/paymentProcessor&reset=1',              '{ts escape="sql" skip="true"}Payment Processors{/ts}', 'Payment Processors',                           'administer CiviCRM', '', @systemSettingslastID, '1', NULL, 3 ), 
    ( @domainID, 'civicrm/admin/setting/mapping&reset=1',               '{ts escape="sql" skip="true"}Mapping and Geocoding{/ts}', 'Mapping and Geocoding',                     'administer CiviCRM', '', @systemSettingslastID, '1', NULL, 4 ), 
    ( @domainID, 'civicrm/admin/setting/misc&reset=1',                  '{ts escape="sql" skip="true"}Undelete, Logging and ReCAPTCHA{/ts}', 'Undelete, Logging and ReCAPTCHA', 'administer CiviCRM', '', @systemSettingslastID, '1', NULL, 5 ), 
    ( @domainID, 'civicrm/admin/setting/path&reset=1',                  '{ts escape="sql" skip="true"}Directories{/ts}',        'Directories',                                  'administer CiviCRM', '', @systemSettingslastID, '1', NULL, 6 ), 
    ( @domainID, 'civicrm/admin/setting/url&reset=1',                   '{ts escape="sql" skip="true"}Resource URLs{/ts}',      'Resource URLs',                                'administer CiviCRM', '', @systemSettingslastID, '1', NULL, 7 ), 
    ( @domainID, 'civicrm/admin/setting/updateConfigBackend&reset=1',   '{ts escape="sql" skip="true"}Cleanup Caches and Update Paths{/ts}', 'Cleanup Caches and Update Paths', 'administer CiviCRM', '', @systemSettingslastID, '1', NULL, 8 ),
    ( @domainID, 'civicrm/admin/setting/uf&reset=1',                    '{ts escape="sql" skip="true"}CMS Database Integration{/ts}',    'CMS Integration',                     'administer CiviCRM', '', @systemSettingslastID, '1', NULL, 9 ), 
    ( @domainID, 'civicrm/admin/options/safe_file_extension&group=safe_file_extension&reset=1', '{ts escape="sql" skip="true"}Safe File Extensions{/ts}', 'Safe File Extensions','administer CiviCRM', '',@systemSettingslastID, '1', NULL, 10 ), 
    ( @domainID, 'civicrm/admin/options?reset=1',                       '{ts escape="sql" skip="true"}Option Groups{/ts}', 'Option Groups',                                     'administer CiviCRM', '', @systemSettingslastID, '1', NULL, 11 ), 
    ( @domainID, 'civicrm/admin/mapping&reset=1',                       '{ts escape="sql" skip="true"}Import/Export Mappings{/ts}', 'Import/Export Mappings',                   'administer CiviCRM', '', @systemSettingslastID, '1', NULL, 12 ), 
    ( @domainID, 'civicrm/admin/setting/debug&reset=1',                 '{ts escape="sql" skip="true"}Debugging and Error Handling{/ts}','Debugging and Error Handling',        'administer CiviCRM', '', @systemSettingslastID, '1', NULL, 13 );
