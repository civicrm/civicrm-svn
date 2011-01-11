-- CRM-7346
ALTER TABLE `civicrm_campaign` ADD `goal_general` TEXT CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL COMMENT 'General goals for Campaign.';
ALTER TABLE `civicrm_campaign` ADD `goal_revenue` DECIMAL( 20, 2 ) NULL COMMENT 'The target revenue for this campaign.';

