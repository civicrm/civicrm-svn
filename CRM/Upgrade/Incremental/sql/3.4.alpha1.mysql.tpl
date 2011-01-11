-- CRM-7346
ALTER TABLE `civicrm_campaign` ADD `goal_general` TEXT CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL COMMENT 'General goals for Campaign.';
ALTER TABLE `civicrm_campaign` ADD `goal_revenue` DECIMAL( 20, 2 ) NULL COMMENT 'The target revenue for this campaign.';

-- CRM-7345
ALTER TABLE `civicrm_custom_group` CHANGE `extends` `extends` ENUM( 'Contact', 'Individual', 'Household', 'Organization', 'Location', 'Address', 'Contribution', 'Activity', 'Relationship', 'Group', 'Membership', 'Participant', 'Event', 'Grant', 'Pledge', 'Case', 'Campaign' ) CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL DEFAULT 'Contact' COMMENT 'Type of object this group extends (can add other options later e.g. contact_address, etc.).';

