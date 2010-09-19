--campaign upgrade.
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
  `activity_type_id` int unsigned DEFAULT NULL COMMENT 'Implicit FK to civicrm_option_value where option_group = activity_type',
  `recontact_interval` text collate utf8_unicode_ci DEFAULT NULL COMMENT 'Recontact intervals for each status.',
  `instructions` text collate utf8_unicode_ci DEFAULT NULL COMMENT 'Script instructions for volunteers to use for the survey.',
  `release_frequency` int unsigned NOT NULL DEFAULT NULL COMMENT 'Number of days for recurrence of release.',
  `max_number_of_contacts` int unsigned DEFAULT NULL COMMENT 'Maximum number of contacts to allow for survey.',
  `default_number_of_contacts` int unsigned DEFAULT NULL COMMENT 'Default number of contacts to allow for survey.',
  `is_active` boolean NOT NULL DEFAULT 1 COMMENT 'Is this survey enabled or disabled/cancelled?',
  `is_default` boolean NOT NULL DEFAULT 0 COMMENT 'Is this default survey?',
  `created_id` int unsigned NULL DEFAULT NULL COMMENT 'FK to civicrm_contact, who created this Survey.',
  `created_date` datetime default NULL COMMENT 'Date and time that Survey was created.',
  `last_modified_id` int unsigned NULL DEFAULT NULL COMMENT 'FK to civicrm_contact, who recently edited this Survey.',
  `last_modified_date` datetime default NULL COMMENT 'Date and time that Survey was edited last time.',
  `result_id` int unsigned NULL DEFAULT NULL COMMENT 'Used to store option group id.',
 PRIMARY KEY ( id ),
  CONSTRAINT FK_civicrm_survey_campaign_id FOREIGN KEY (campaign_id) REFERENCES civicrm_campaign(id) ON DELETE CASCADE,
  INDEX UI_activity_type_id (activity_type_id),
  CONSTRAINT FK_civicrm_survey_created_id FOREIGN KEY (created_id) REFERENCES civicrm_contact(id) ON DELETE SET NULL,
  CONSTRAINT FK_civicrm_survey_last_modified_id FOREIGN KEY (last_modified_id) REFERENCES civicrm_contact(id) ON DELETE SET NULL
)ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

 ALTER TABLE `civicrm_activity` 
 ADD `result` varchar(255) unsigned DEFAULT NULL COMMENT 'Currently being used to store result for survey activity, FK to option value.';

INSERT INTO civicrm_option_group
       	(`name`, {localize field='description'}description{/localize}, `is_active`)
VALUES
	('campaign_type'    , {localize}'Campaign Type'{/localize}     , 1 ),
   	('campaign_status'  , {localize}'Campaign Status'{/localize}   , 1 );

--insert values for Compaign Types, Campaign Status and Activity types.
   
SELECT @option_group_id_campaignType   := max(id) from civicrm_option_group where name = 'campaign_type';
SELECT @option_group_id_campaignStatus := max(id) from civicrm_option_group where name = 'campaign_status';
SELECT @option_group_id_act            := max(id) from civicrm_option_group where name = 'activity_type';
SELECT @campaignCompId                 := max(id) FROM civicrm_component where name    = 'CiviCampaign';
SELECT @max_campaign_act_val           := MAX(ROUND(value)) from civicrm_option_value where option_group_id = @option_group_id_act;
SELECT @max_campaign_act_wt            := MAX(ROUND(weight)) from civicrm_option_value where option_group_id = @option_group_id_act;

INSERT INTO 
   `civicrm_option_value` (`option_group_id`, {localize field='label'}label{/localize}, `value`, `name`, `weight`, `is_active`, `component_id` ) 
VALUES
  (@option_group_id_campaignType, '{localize}Direct Mail{/localize}',     1, 'Direct Mail',       1,   1, NULL ),
  (@option_group_id_campaignType, '{localize}Referral Program{localize}', 2, 'Referral Program',  2,   1, NULL ),
  (@option_group_id_campaignType, '{localize}Voter Engagement{localize}', 3, 'Voter Engagement',  3,   1, NULL ),

  (@option_group_id_campaignStatus, '{localize}Planned{localize}',        1, 'Planned',           1,   1, NULL ), 
  (@option_group_id_campaignStatus, '{localize}In Progress{localize}',    2, 'In Progress',       2,   1, NULL ),
  (@option_group_id_campaignStatus, '{localize}Completed{localize}',      3, 'Completed',         3,   1, NULL ),
  (@option_group_id_campaignStatus, '{localize}Cancelled{localize}',      4, 'Cancelled',         4,   1, NULL ),

  (@option_group_id_act, '{localize}Survey{localize}',                   (SELECT @max_campaign_act_val := @max_campaign_act_val + 1 ), 'Survey',           (SELECT @max_campaign_act_wt := @max_campaign_act_wt + 1 ),   1, @campaignCompId ),
  (@option_group_id_act, '{localize}Canvass{localize}',                  (SELECT @max_campaign_act_val := @max_campaign_act_val + 1 ), 'Canvass',          (SELECT @max_campaign_act_wt := @max_campaign_act_wt + 1 ),   1, @campaignCompId ),
  (@option_group_id_act, '{localize}PhoneBank{localize}',                (SELECT @max_campaign_act_val := @max_campaign_act_val + 1 ), 'PhoneBank',        (SELECT @max_campaign_act_wt := @max_campaign_act_wt + 1 ),   1, @campaignCompId ),
  (@option_group_id_act, '{localize}WalkList{localize}',                 (SELECT @max_campaign_act_val := @max_campaign_act_val + 1 ), 'WalkList',         (SELECT @max_campaign_act_wt := @max_campaign_act_wt + 1 ),   1, @campaignCompId ),
  (@option_group_id_act, '{localize}Petition{localize}',                 (SELECT @max_campaign_act_val := @max_campaign_act_val + 1 ), 'Petition',         (SELECT @max_campaign_act_wt := @max_campaign_act_wt + 1 ),   1, @campaignCompId );

