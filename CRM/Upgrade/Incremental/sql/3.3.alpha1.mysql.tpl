-- CRM-6696
ALTER TABLE civicrm_option_value {localize field='description'}MODIFY COLUMN description text{/localize};

-- CRM-6442
SELECT @option_group_id_website := MAX(id) from civicrm_option_group where name = 'website_type';
SELECT @max_value               := MAX(ROUND(value)) from civicrm_option_value where option_group_id = @option_group_id_website;
SELECT @max_weight              := MAX(ROUND(weight)) from civicrm_option_value where option_group_id = @option_group_id_website;;

INSERT INTO civicrm_option_value
        (option_group_id, {localize field='label'}label{/localize}, value, name, grouping, filter, is_default, weight, {localize field='description'}description{/localize}, is_optgroup, is_reserved, is_active, component_id, visibility_id)
VALUES
	(@option_group_id_website, {localize}'Main'{/localize}, @max_value+1, 'Main', NULL, 0, NULL, @max_weight+1, {localize}NULL{/localize}, 0, 0, 1, NULL, NULL);
	
-- CRM-6763
UPDATE civicrm_option_group 
   SET is_reserved = 0
 WHERE civicrm_option_group.name = 'encounter_medium';

-- CRM-6814
ALTER TABLE `civicrm_note` 
  ADD `privacy` INT( 10 ) NOT NULL COMMENT 'Foreign Key to Note Privacy Level (which is an option value pair and hence an implicit FK)';

-- CRM-6748
UPDATE civicrm_navigation SET url = 'civicrm/admin/contribute/add&reset=1&action=add'
        WHERE civicrm_navigation.name = 'New Contribution Page';

-- CRM-6507
ALTER TABLE civicrm_participant 
   CHANGE role_id role_id varchar(128) collate utf8_unicode_ci NULL default NULL COMMENT 'Participant role ID. Implicit FK to civicrm_option_value where option_group = participant_role.';

--
-- Campaign upgrade.
--
-- CRM-6232
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
  `campaign_id` int unsigned DEFAULT NULL COMMENT 'Foreign key to the activity Campaign.',
  `activity_type_id` int unsigned DEFAULT NULL COMMENT 'Implicit FK to civicrm_option_value where option_group = activity_type',
  `recontact_interval` text collate utf8_unicode_ci DEFAULT NULL COMMENT 'Recontact intervals for each status.',
  `instructions` text collate utf8_unicode_ci DEFAULT NULL COMMENT 'Script instructions for volunteers to use for the survey.',
  `release_frequency` int unsigned DEFAULT NULL COMMENT 'Number of days for recurrence of release.',
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


--add result column to activity table.
ALTER TABLE `civicrm_activity` ADD `result` VARCHAR( 255 ) CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL COMMENT 'Currently being used to store result for survey activity. FK to option value.' AFTER `original_id`;

--insert campaign component.
INSERT INTO civicrm_component (name, namespace) VALUES ('CiviCampaign'  , 'CRM_Campaign' );

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
  (@option_group_id_campaignType, '{localize}Referral Program{/localize}', 2, 'Referral Program',  2,   1, NULL ),
  (@option_group_id_campaignType, '{localize}Voter Engagement{/localize}', 3, 'Voter Engagement',  3,   1, NULL ),

  (@option_group_id_campaignStatus, '{localize}Planned{/localize}',        1, 'Planned',           1,   1, NULL ), 
  (@option_group_id_campaignStatus, '{localize}In Progress{/localize}',    2, 'In Progress',       2,   1, NULL ),
  (@option_group_id_campaignStatus, '{localize}Completed{/localize}',      3, 'Completed',         3,   1, NULL ),
  (@option_group_id_campaignStatus, '{localize}Cancelled{/localize}',      4, 'Cancelled',         4,   1, NULL ),

  (@option_group_id_act, '{localize}Survey{/localize}',                   (SELECT @max_campaign_act_val := @max_campaign_act_val + 1 ), 'Survey',           (SELECT @max_campaign_act_wt := @max_campaign_act_wt + 1 ),   1, @campaignCompId ),
  (@option_group_id_act, '{localize}Canvass{/localize}',                  (SELECT @max_campaign_act_val := @max_campaign_act_val + 1 ), 'Canvass',          (SELECT @max_campaign_act_wt := @max_campaign_act_wt + 1 ),   1, @campaignCompId ),
  (@option_group_id_act, '{localize}PhoneBank{/localize}',                (SELECT @max_campaign_act_val := @max_campaign_act_val + 1 ), 'PhoneBank',        (SELECT @max_campaign_act_wt := @max_campaign_act_wt + 1 ),   1, @campaignCompId ),
  (@option_group_id_act, '{localize}WalkList{/localize}',                 (SELECT @max_campaign_act_val := @max_campaign_act_val + 1 ), 'WalkList',         (SELECT @max_campaign_act_wt := @max_campaign_act_wt + 1 ),   1, @campaignCompId ),
  (@option_group_id_act, '{localize}Petition{/localize}',                 (SELECT @max_campaign_act_val := @max_campaign_act_val + 1 ), 'Petition',         (SELECT @max_campaign_act_wt := @max_campaign_act_wt + 1 ),   1, @campaignCompId );

--campaign navigation.
SELECT @domainID        := MIN(id) FROM civicrm_domain;
SELECT @nav_other_id    := id FROM civicrm_navigation WHERE name = 'Other';
SELECT @nav_other_wt    := MAX(ROUND(weight)) from civicrm_navigation WHERE parent_id = @nav_other_id;

--insert campaigns permissions in 'Other' navigation menu permissions.
UPDATE  civicrm_navigation 
   SET  permission = CONCAT( permission, ',administer CiviCampaign,manage campaign,reserve campaign contacts,release campaign contacts,interview campaign contacts' ) 
 WHERE  id = @nav_other_id;

INSERT INTO civicrm_navigation
    ( domain_id, url, label, name, permission, permission_operator, parent_id, is_active, has_separator, weight )
VALUES
    ( @domainID, NULL, '{ts escape="sql"}Campaigns{/ts}', 'Campaigns', 'interview campaign contacts,release campaign contacts,reserve campaign contacts,manage campaign,administer CiviCampaign', 'OR', @nav_other_id, '1', NULL, (SELECT @nav_other_wt := @nav_other_wt + 1) );

SELECT @nav_campaign_id    := id FROM civicrm_navigation WHERE name = 'Campaigns';

INSERT INTO civicrm_navigation
    ( domain_id, url, label, name, permission, permission_operator, parent_id, is_active, has_separator, weight )
VALUES    
    ( @domainID, 'civicrm/campaign&reset=1',        '{ts escape="sql"}Dashboard{/ts}', 'Dashboard', 'administer CiviCampaign', '', @nav_campaign_id, '1', NULL, 1 );

SET @campaigndashboardlastID:=LAST_INSERT_ID();

INSERT INTO civicrm_navigation
    ( domain_id, url, label, name, permission, permission_operator, parent_id, is_active, has_separator, weight )
VALUES    
    ( @domainID, 'civicrm/campaign&reset=1&subPage=survey',        '{ts escape="sql"}Surveys{/ts}', 'Survey Dashboard', 'administer CiviCampaign', '', @campaigndashboardlastID, '1', NULL, 1 ), 
    ( @domainID, 'civicrm/campaign&reset=1&subPage=campaign',        '{ts escape="sql"}Campaigns{/ts}', 'Campaign Dashboard', 'administer CiviCampaign', '', @campaigndashboardlastID, '1', NULL, 2 ),
    ( @domainID, 'civicrm/campaign/add&reset=1',        '{ts escape="sql"}New Campaign{/ts}', 'New Campaign', 'administer CiviCampaign', '', @nav_campaign_id, '1', NULL, 2 ), 
    ( @domainID, 'civicrm/survey/add&reset=1',        '{ts escape="sql"}New Survey{/ts}', 'New Survey', 'administer CiviCampaign', '', @nav_campaign_id, '1', NULL, 3 ),
    ( @domainID, 'civicrm/petition/add&reset=1',        '{ts escape="sql"}New Petition{/ts}', 'New Petition', 'administer CiviCampaign', '', @nav_campaign_id, '1', NULL, 4 ),
    ( @domainID, 'civicrm/survey/search&reset=1&op=reserve', '{ts escape="sql"}Reserve Voters{/ts}', 'Reserve Voters', 'administer CiviCampaign,manage campaign,reserve campaign contacts', 'OR', @nav_campaign_id, '1', NULL, 5 ),
    ( @domainID, 'civicrm/survey/search&reset=1&op=interview', '{ts escape="sql"}Interview Voters{/ts}', 'Interview Voters', 'administer CiviCampaign,manage campaign,interview campaign contacts', 'OR', @nav_campaign_id, '1', NULL, 6 ),
    ( @domainID, 'civicrm/survey/search&reset=1&op=release', '{ts escape="sql"}Release Voters{/ts}', 'Release Voters', 'administer CiviCampaign,manage campaign,release campaign contacts', 'OR', @nav_campaign_id, '1', NULL, 7 ),
    ( @domainID, 'civicrm/campaign/gotv&reset=1', '{ts escape="sql"}Voter Listing{/ts}', 'Voter Listing', 'administer CiviCampaign,manage campaign', 'OR', @nav_campaign_id, '1', NULL, 8 ),
    ( @domainID, 'civicrm/campaign/vote&reset=1', '{ts escape="sql"}Conduct Survey{/ts}', 'Conduct Survey', 'administer CiviCampaign,manage campaign,reserve campaign contacts,interview campaign contacts', 'OR', @nav_campaign_id, '1', NULL, 9 );

--
--Done w/ campaign db upgrade.
--

-- CRM-6208
insert into civicrm_option_group (name, is_active) values ('system_extensions', 1 );
