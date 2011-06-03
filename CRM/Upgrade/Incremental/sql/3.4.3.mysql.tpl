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
  KEY `FK_civicrm_action_schedule_mapping_id` (`mapping_id`),
  KEY `FK_civicrm_action_schedule_group_id` (`group_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;