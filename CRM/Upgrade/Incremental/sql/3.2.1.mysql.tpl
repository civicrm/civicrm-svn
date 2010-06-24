--CRM-6232
CREATE TABLE `civicrm_campaign` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Unique Campaign ID.',
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL COMMENT 'Name of the Campaign.',
  `title` varchar(255) NULL DEFAULT NULL COMMENT 'Title of the Campaign.',
  `description` text collate utf8_unicode_ci default NULL COMMENT 'Full description of Campaign.',
  `start_date` datetime default NULL COMMENT 'Date and time that Campaign starts.',
  `end_date` datetime default NULL COMMENT 'Date and time that Campaign ends.',
  `campaign_type_id` int unsigned DEFAULT NULL COMMENT 'Campaign Type ID.Implicit FK to civicrm_option_value where option_group = campaign_type',
  `status_id` int unsigned DEFAULT NULL COMMENT 'Campaign status ID.Implicit FK to civicrm_option_value where option_group = campaign_status',
  `external_identifier` int unsigned NULL DEFAULT NULL COMMENT 'Unique trusted external ID (generally from a legacy app/datasource). Particularly useful for deduping operations.',
  `parent_id` int unsigned NULL DEFAULT NULL COMMENT 'Optional parent id for this Campaign.',
  `is_active` boolean NOT NULL DEFAULT 1 COMMENT 'Is this Campaign enabled or disabled/cancelled?',
  `created_id` int unsigned NULL DEFAULT NULL COMMENT 'FK to civicrm_contact, who created this Campaign.',
  `created_date` datetime default NULL COMMENT 'Date and time that Campaign was created.',
  `last_modified_id` int unsigned NULL DEFAULT NULL COMMENT 'FK to civicrm_contact, who recently edited this Campaign.',
  `last_modified_date` datetime default NULL COMMENT 'Date and time that Campaign was edited last time.',
  PRIMARY KEY ( id ),
  INDEX UI_campaign_type_id (campaign_type_id),
  INDEX UI_campaign_status_id (status_id),
  UNIQUE INDEX UI_external_identifier (external_identifier),
  CONSTRAINT FK_civicrm_campaign_created_id FOREIGN KEY (created_id) REFERENCES civicrm_contact(id) ON DELETE SET NULL,
  CONSTRAINT FK_civicrm_campaign_last_modified_id FOREIGN KEY (last_modified_id) REFERENCES civicrm_contact(id) ON DELETE SET NULL,
  CONSTRAINT FK_civicrm_campaign_parent_id FOREIGN KEY (parent_id) REFERENCES civicrm_campaign(id) ON DELETE SET NULL
)ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


CREATE TABLE `civicrm_campaign_group` ( 
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Campaign Group id.',
  `campaign_id` int unsigned NOT NULL COMMENT 'Foreign key to the activity Campaign.',
  `group_type` enum('Include','Exclude') NULL DEFAULT NULL COMMENT 'Type of Group.',
  `entity_table` varchar(64) NULL DEFAULT NULL COMMENT 'Name of table where item being referenced is stored.',
  `entity_id` int unsigned DEFAULT NULL COMMENT 'Entity id of referenced table.',
  PRIMARY KEY ( id ),
  CONSTRAINT FK_civicrm_campaign_group_campaign_id FOREIGN KEY (campaign_id) REFERENCES civicrm_campaign(id) ON DELETE CASCADE
)ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


CREATE TABLE `civicrm_survey` ( 
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Campaign Group id.',
  `title` varchar(255) NOT NULL COMMENT 'Title of the Survey.',
  `campaign_id` int unsigned NOT NULL COMMENT 'Foreign key to the activity Campaign.',
  `survey_type_id` int unsigned DEFAULT NULL COMMENT 'Survey Type ID.Implicit FK to civicrm_option_value where option_group = activity_type',
  `custom_group_id` int unsigned DEFAULT NULL COMMENT 'FK to civicrm_custom_group',
  `recontact_interval` text collate utf8_unicode_ci DEFAULT NULL COMMENT 'Recontact intervals for each status.',
  `instructions` text collate utf8_unicode_ci DEFAULT NULL COMMENT 'Script instructions for volunteers to use for the campaign.',
  `release_frequency_unit` enum('day','week','month','year') DEFAULT 'day' COMMENT 'Time units for recurrence of release.',
  `release_frequency_interval` int unsigned NOT NULL DEFAULT 1 COMMENT 'Number of time units for recurrence of release.',
  `max_number_of_contacts` int unsigned DEFAULT NULL COMMENT 'Maximum number of contacts to allow for survey.',
  `default_number_of_contacts` int unsigned DEFAULT NULL COMMENT 'Default number of contacts to allow for survey.',
  `is_active` boolean NOT NULL DEFAULT 1 COMMENT 'Is this survey enabled or disabled/cancelled?',
  `is_default` boolean NOT NULL DEFAULT 0 COMMENT 'Is this default survey?',
  `created_id` int unsigned NULL DEFAULT NULL COMMENT 'FK to civicrm_contact, who created this Survey.',
  `created_date` datetime default NULL COMMENT 'Date and time that Survey was created.',
  `last_modified_id` int unsigned NULL DEFAULT NULL COMMENT 'FK to civicrm_contact, who recently edited this Survey.',
  `last_modified_date` datetime default NULL COMMENT 'Date and time that Survey was edited last time.',
 PRIMARY KEY ( id ),
  CONSTRAINT FK_civicrm_survey_campaign_id FOREIGN KEY (campaign_id) REFERENCES civicrm_campaign(id) ON DELETE CASCADE,
  INDEX UI_survey_type_id (survey_type_id),
  CONSTRAINT FK_civicrm_survey_custom_group_id FOREIGN KEY (custom_group_id) REFERENCES civicrm_custom_group(id) ON DELETE SET NULL,
  CONSTRAINT FK_civicrm_survey_created_id FOREIGN KEY (created_id) REFERENCES civicrm_contact(id) ON DELETE SET NULL,
  CONSTRAINT FK_civicrm_survey_last_modified_id FOREIGN KEY (last_modified_id) REFERENCES civicrm_contact(id) ON DELETE SET NULL
)ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


 ALTER TABLE `civicrm_activity` 
 ADD `campaign_id` int unsigned DEFAULT NULL COMMENT 'Foreign key to the Campaign.' AFTER id,
 ADD CONSTRAINT FK_civicrm_activity_campaign_id  FOREIGN KEY (campaign_id) REFERENCES civicrm_campaign(id) ON DELETE CASCADE;

INSERT INTO civicrm_option_group
       	(`name`, `description`, `is_reserved`, `is_active`)
VALUES
	('campaign_type'                 , '{ts escape="sql"}Campaign Type{/ts}'                      , 0, 1),
   	('campaign_status'               , '{ts escape="sql"}Campaign Status{/ts}'                    , 0, 1);
   
SELECT @option_group_id_campaignType   := max(id) from civicrm_option_group where name = 'campaign_type';
SELECT @option_group_id_campaignStatus := max(id) from civicrm_option_group where name = 'campaign_status';
SELECT @option_group_id_act            := max(id) from civicrm_option_group where name = 'activity_type';

SELECT @campaignCompId                 := max(id) FROM civicrm_component where name    = 'CiviCampaign';

INSERT INTO 
   `civicrm_option_value` (`option_group_id`, `label`, `value`, `name`, `grouping`, `filter`, `is_default`, `weight`, `description`, `is_optgroup`, `is_reserved`, `is_active`, `component_id`, `visibility_id`) 
VALUES
--Compaign Types
  (@option_group_id_campaignType, '{ts escape="sql"}Direct Mail{/ts}', 1, 'Direct Mail',  NULL, 0, NULL, 1, NULL, 0, 0, 1, NULL, NULL),
  (@option_group_id_campaignType, '{ts escape="sql"}Referral Program{/ts}', 2, 'Referral Program',  NULL, 0, NULL, 1, NULL, 0, 0, 1, NULL, NULL),
  (@option_group_id_campaignType, '{ts escape="sql"}Voter Engagement{/ts}', 3, 'Voter Engagement',  NULL, 0, NULL, 1, NULL, 0, 0, 1, NULL, NULL),

--Campaign Status
  (@option_group_id_campaignStatus, '{ts escape="sql"}Planned{/ts}', 1, 'Planned',  NULL, 0, NULL, 1, NULL, 0, 0, 1, NULL, NULL), 
  (@option_group_id_campaignStatus, '{ts escape="sql"}In Progress{/ts}', 2, 'In Progress',  NULL, 0, NULL, 1, NULL, 0, 0, 1, NULL, NULL),
  (@option_group_id_campaignStatus, '{ts escape="sql"}Completed{/ts}', 3, 'Completed',  NULL, 0, NULL, 1, NULL, 0, 0, 1, NULL, NULL),
  (@option_group_id_campaignStatus, '{ts escape="sql"}Cancelled{/ts}', 4, 'Cancelled',  NULL, 0, NULL, 1, NULL, 0, 0, 1, NULL, NULL),

   (@option_group_id_act, '{ts escape="sql"}Survey{/ts}', 27, 'Survey', NULL,0, 0, 27, '', 0, 1, 1, @campaignCompId, NULL),
   (@option_group_id_act, '{ts escape="sql"}Canvass{/ts}', 28, 'Canvass', NULL,0, 0, 28, '', 0, 1, 1, @campaignCompId, NULL),
   (@option_group_id_act, '{ts escape="sql"}PhoneBank{/ts}', 29, 'PhoneBank', NULL,0, 0, 29, '', 0, 1, 1, @campaignCompId, NULL),
   (@option_group_id_act, '{ts escape="sql"}WalkList{/ts}', 30, 'WalkList', NULL,0, 0, 30, '', 0, 1, 1, @campaignCompId, NULL);

