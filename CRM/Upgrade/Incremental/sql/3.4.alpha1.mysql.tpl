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

