-- CRM-7346
ALTER TABLE `civicrm_campaign` ADD `goal_general` TEXT CHARACTER SET utf8 COLLATE utf8_unicode_ci default NULL NULL COMMENT 'General goals for Campaign.';
ALTER TABLE `civicrm_campaign` ADD `goal_revenue` DECIMAL( 20, 2 ) default NULL NULL COMMENT 'The target revenue for this campaign.';

-- CRM-7345
ALTER TABLE `civicrm_custom_group` CHANGE `extends` `extends` ENUM( 'Contact', 'Individual', 'Household', 'Organization', 'Location', 'Address', 'Contribution', 'Activity', 'Relationship', 'Group', 'Membership', 'Participant', 'Event', 'Grant', 'Pledge', 'Case', 'Campaign' ) CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL DEFAULT 'Contact' COMMENT 'Type of object this group extends (can add other options later e.g. contact_address, etc.).';

-- CRM-7362
ALTER TABLE `civicrm_contribution`
ADD `campaign_id` int(10) unsigned default NULL COMMENT 'The campaign for which this contribution has been triggered.',
ADD CONSTRAINT FK_civicrm_contribution_campaign_id FOREIGN KEY (campaign_id) REFERENCES civicrm_campaign(id) ON DELETE SET NULL;

ALTER TABLE `civicrm_contribution_page`
ADD `campaign_id` int(10) unsigned default NULL COMMENT 'The campaign for which we are collecting contributions with this page.',
ADD CONSTRAINT FK_civicrm_contribution_page_campaign_id FOREIGN KEY (campaign_id) REFERENCES civicrm_campaign(id) ON DELETE SET NULL;

ALTER TABLE `civicrm_membership`
ADD `campaign_id` int(10) unsigned default NULL COMMENT 'The campaign for which this membership is attached.',
ADD CONSTRAINT FK_civicrm_membership_campaign_id FOREIGN KEY (campaign_id) REFERENCES civicrm_campaign(id) ON DELETE SET NULL;

ALTER TABLE `civicrm_pledge`
ADD `campaign_id` int(10) unsigned default NULL COMMENT 'The campaign for which this pledge has been initiated.',
ADD CONSTRAINT FK_civicrm_pledge_campaign_id FOREIGN KEY (campaign_id) REFERENCES civicrm_campaign(id) ON DELETE SET NULL;

ALTER TABLE `civicrm_activity`
ADD `campaign_id` int(10) unsigned default NULL COMMENT 'The campaign for which this activity has been triggered.',
ADD CONSTRAINT FK_civicrm_activity_campaign_id FOREIGN KEY (campaign_id) REFERENCES civicrm_campaign(id) ON DELETE SET NULL;

ALTER TABLE `civicrm_participant`
ADD `campaign_id` int(10) unsigned default NULL COMMENT 'The campaign for which this participant has been registered.',
ADD CONSTRAINT FK_civicrm_participant_campaign_id FOREIGN KEY (campaign_id) REFERENCES civicrm_campaign(id) ON DELETE SET NULL;

ALTER TABLE `civicrm_event`
ADD `campaign_id` int(10) unsigned default NULL COMMENT 'The campaign for which this event has been created.',
ADD CONSTRAINT FK_civicrm_event_campaign_id FOREIGN KEY (campaign_id) REFERENCES civicrm_campaign(id) ON DELETE SET NULL;

ALTER TABLE `civicrm_mailing`
ADD `campaign_id` int(10) unsigned default NULL COMMENT 'The campaign for which this mailing has been initiated.',
ADD CONSTRAINT FK_civicrm_mailing_campaign_id FOREIGN KEY (campaign_id) REFERENCES civicrm_campaign(id) ON DELETE SET NULL,
ADD `domain_id` int(10) unsigned default NULL COMMENT 'Which site is this mailing for.' AFTER id,
ADD CONSTRAINT FK_civicrm_mailing_domain_id FOREIGN KEY (domain_id) REFERENCES civicrm_domain(id) ON DELETE SET NULL;

-- done w/ CRM-7345

-- CRM-7223
CREATE TABLE civicrm_mailing_recipients (
     id int unsigned NOT NULL AUTO_INCREMENT  ,
     mailing_id int unsigned NOT NULL   COMMENT 'The ID of the mailing this Job will send.',
     contact_id int unsigned NOT NULL   COMMENT 'FK to Contact',
     email_id int unsigned NOT NULL   COMMENT 'FK to Email',
     PRIMARY KEY ( id ),
     CONSTRAINT FK_civicrm_mailing_recipients_mailing_id FOREIGN KEY (mailing_id) REFERENCES civicrm_mailing(id) ON DELETE CASCADE,      
     CONSTRAINT FK_civicrm_mailing_recipients_contact_id FOREIGN KEY (contact_id) REFERENCES civicrm_contact(id) ON DELETE CASCADE,      
     CONSTRAINT FK_civicrm_mailing_recipients_email_id FOREIGN KEY (email_id) REFERENCES civicrm_email(id) ON DELETE CASCADE  
)  ENGINE=InnoDB DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci  ;


-- CRM-3825
SELECT @option_group_id_acsOpt := max(id) FROM civicrm_option_group WHERE name = 'contact_autocomplete_options';
SELECT @value_acsOpt := max(value), @weight_acsOpt := max(weight) 
  FROM civicrm_option_value 
 WHERE civicrm_option_value.option_group_id = @option_group_id_acsOpt;

INSERT INTO 
       `civicrm_option_value` (`option_group_id`, `label`, `value`, `name`, `grouping`, `filter`, `is_default`, `weight`, `description`, `is_optgroup`, `is_reserved`, `is_active`, `component_id`, `visibility_id`) 
VALUES
	(@option_group_id_acsOpt, '{ts escape="sql"}Nick Name{/ts}', @value_acsOpt+1, 'nick_name', NULL, 0, NULL, @weight_acsOpt+1, NULL, 0, 0, 1, NULL, NULL);



-- CRM-7352 add logging report templates
SELECT @option_group_id_report := MAX(id)     FROM civicrm_option_group WHERE name = 'report_template';
SELECT @weight                 := MAX(weight) FROM civicrm_option_value WHERE option_group_id = @option_group_id_report;
SELECT @contributeCompId       := MAX(id)     FROM civicrm_component where name = 'CiviContribute';
INSERT INTO civicrm_option_value
  (option_group_id,         {localize field='label'}label{/localize},                   value,                        name,                                        weight,                 {localize field='description'}description{/localize},                                              is_active, component_id) VALUES
  (@option_group_id_report, {localize}'Contribute Logging Report (Summary)'{/localize}, 'logging/contribute/summary', 'CRM_Report_Form_Contribute_LoggingSummary', @weight := @weight + 1, {localize}'Contribution modification report for the logging infrastructure (summary).'{/localize}, 0,         @contributeCompId),
  (@option_group_id_report, {localize}'Contribute Logging Report (Detail)'{/localize},  'logging/contribute/detail',  'CRM_Report_Form_Contribute_LoggingDetail',  @weight := @weight + 1, {localize}'Contribute modification report for the logging infrastructure (detail).'{/localize},    0,         @contributeCompId);

-- CRM-7297 Membership Upsell
ALTER TABLE civicrm_membership_log ADD membership_type_id  INT UNSIGNED COMMENT 'FK to Membership Type.', 
ADD CONSTRAINT FK_civicrm_membership_log_membership_type_id FOREIGN KEY (membership_type_id) REFERENCES civicrm_membership_type(id) 
ON DELETE SET NULL;

UPDATE civicrm_membership_log cml INNER JOIN civicrm_membership cm 
ON cml.membership_id=cm.id SET cml.membership_type_id=cm.membership_type_id;
