-- ARMS 
-- CRM-8148, rename uf field 'activity_status' to 'activity_status_id'
UPDATE civicrm_uf_field SET field_name = 'activity_type_id' WHERE field_name= 'activity_type';

-- CRM-8148, we are not deleting uf fields 'activity_type', 'activity_is_deleted', 'activity_engagement_level', 'activity_is_test'

-- CRM-8209
SELECT @option_group_id_adv_search_opts := max(id) from civicrm_option_group where name = 'advanced_search_options';

INSERT INTO 
    `civicrm_option_value` (`option_group_id`, {localize field='label'}label{/localize}, `value`, `name`, `filter`, `weight`)
VALUES
    (@option_group_id_adv_search_opts, '{localize}Mailing{/localize}', '19', 'CiviMail', 0, 21);

-- CRM-8150
CREATE TABLE IF NOT EXISTS `civicrm_action_mapping` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `entity` varchar(64) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'Entity for which the reminder is created',
  `entity_value` varchar(64) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'Entity value',
  `entity_value_label` varchar(64) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'Entity value label',
  `entity_status` varchar(64) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'Entity status',
  `entity_status_label` varchar(64) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'Entity status label',
  `entity_date_start` varchar(64) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'Entity date',
  `entity_date_end` varchar(64) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'Entity date',
  `entity_recipient` varchar(64) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'Entity recipient',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


INSERT INTO civicrm_action_mapping 
        (entity, entity_value, entity_value_label, entity_status, entity_status_label, entity_date_start, entity_date_end, entity_recipient) 
VALUES
	('civicrm_activity', 'activity_type', 'Type', 'activity_status', 'Status', 'activity_date_time', NULL, 'activity_contacts'),
	('civicrm_participant', 'event_type', 'Type', 'civicrm_participant_status_type', 'Status', 'event_start_date', 'event_end_date', 'civicrm_participant_status_type'),
	('civicrm_participant', 'civicrm_event', 'Type', 'civicrm_participant_status_type', 'Status', 'event_start_date', 'event_end_date', 'civicrm_participant_status_type');


CREATE TABLE IF NOT EXISTS `civicrm_action_schedule` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(64) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'Name of the action(reminder)',
  `title` varchar(64) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'Title of the action(reminder)',
  `recipient` varchar(64) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'Recipient',
  `entity_value` varchar(64) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'Entity value',
  `entity_status` varchar(64) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'Entity status',
  `start_action_offset` int(10) unsigned DEFAULT NULL COMMENT 'Reminder Interval.',
  `start_action_unit` enum('hour','day','week','month','year') COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'Time units for reminder.',
  `start_action_condition` varchar(32) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'Reminder Action',
  `start_action_date` varchar(64) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'Entity date',
  `is_repeat` tinyint(4) DEFAULT '0',
  `repetition_frequency_unit` enum('hour','day','week','month','year') COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'Time units for repetition of reminder.',
  `repetition_frequency_interval` int(10) unsigned DEFAULT NULL COMMENT 'Time interval for repeating the reminder.',
  `end_frequency_unit` enum('hour','day','week','month','year') COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'Time units till repetition of reminder.',
  `end_frequency_interval` int(10) unsigned DEFAULT NULL COMMENT 'Time interval till repeating the reminder.',
  `end_action` varchar(32) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'Reminder Action till repeating the reminder.',
  `end_date` varchar(64) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'Entity end date',
  `is_active` tinyint(4) DEFAULT '1' COMMENT 'Is this option active?',
  `recipient_manual` varchar(128) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'Contact IDs to which reminder should be sent.',
  `body_text` longtext COLLATE utf8_unicode_ci COMMENT 'Body of the mailing in text format.',
  `body_html` longtext COLLATE utf8_unicode_ci COMMENT 'Body of the mailing in html format.',
  `subject` varchar(128) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'Subject of mailing',
  `record_activity` tinyint(4) DEFAULT '1' COMMENT 'Record Activity for this reminder?',
  `mapping_id` int(10) unsigned DEFAULT NULL COMMENT 'FK to mapping which is being used by this reminder',
  `group_id` int(10) unsigned DEFAULT NULL COMMENT 'FK to Group',
  PRIMARY KEY (`id`),
  CONSTRAINT `FK_civicrm_action_schedule_mapping_id` FOREIGN KEY (`mapping_id`) REFERENCES civicrm_action_mapping(id) ON DELETE SET NULL,
  CONSTRAINT `FK_civicrm_action_schedule_group_id` FOREIGN KEY (`group_id`) REFERENCES civicrm_group(id) ON DELETE SET NULL
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


INSERT INTO civicrm_option_group
      (name, {localize field='description'}description{/localize}, is_reserved, is_active)
VALUES
      ('activity_contacts', {localize}'{ts escape="sql"}Activity Contacts{/ts}'{/localize}, 0, 1);

SELECT @option_group_id_aco := max(id) from civicrm_option_group where name = 'activity_contacts';

INSERT INTO civicrm_option_value 
   (option_group_id, {localize field='label'}label{/localize}, value, name, grouping, filter, is_default, weight, description, is_optgroup, is_reserved, is_active, component_id, visibility_id) 
VALUES
   (@option_group_id_aco, {localize}'{ts escape="sql"}Activity Assignees{/ts}'{/localize}, 1, 'Activity Assignees', NULL, 0, NULL, 1, NULL, 0, 0, 1, NULL, NULL),
   (@option_group_id_aco, {localize}'{ts escape="sql"}Activity Source{/ts}'{/localize}, 2, 'Activity Source', NULL, 0, NULL, 2, NULL, 0, 0, 1, NULL, NULL),
   (@option_group_id_aco, {localize}'{ts escape="sql"}Activity Targets{/ts}'{/localize}, 3, 'Activity Targets', NULL, 0, NULL, 3, NULL, 0, 0, 1, NULL, NULL);

SELECT @domainID := min(id) FROM civicrm_domain;
SELECT @configureID := max(id) FROM civicrm_navigation WHERE name = 'Configure';
SELECT @nav_c_wt := max(weight) from civicrm_navigation WHERE parent_id = @configureID;

INSERT INTO civicrm_navigation
    ( domain_id, url, label, name, permission, permission_operator, parent_id, is_active, has_separator, weight )
VALUES
    ( @domainID, 'civicrm/admin/scheduleReminders&reset=1', '{ts escape="sql" skip="true"}Schedule Reminders{/ts}', 'Schedule Reminders', 'administer CiviCRM', '',  @configureID, '1', NULL, @nav_c_wt );
