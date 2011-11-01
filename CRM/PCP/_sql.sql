# Run these SQL statements

ALTER TABLE civicrm_pcp_block DROP FOREIGN KEY FK_civicrm_pcp_block_entity_id;
ALTER TABLE civicrm_pcp DROP FOREIGN KEY FK_civicrm_pcp_contribution_page_id;

ALTER TABLE `civicrm_pcp` ADD `page_type` VARCHAR( 64 ) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT 'contribute' AFTER `contribution_page_id`;
ALTER TABLE `civicrm_pcp` CHANGE `contribution_page_id` `page_id` INT( 10 ) UNSIGNED NOT NULL COMMENT 'The Page which triggered this pcp'; 
ALTER TABLE `civicrm_pcp` ADD `pcp_block_id` int(10) unsigned NOT NULL COMMENT 'The pcp block that this pcp page was created from' AFTER `page_type`;

UPDATE `civicrm_pcp` SET `page_type` = 'contribute' WHERE `page_type` = '' OR `page_type` IS NULL;
UPDATE `civicrm_pcp` `pcp` SET `pcp_block_id` = (SELECT `id` FROM `civicrm_pcp_block` `pb` WHERE `pb`.`entity_id` = `pcp`.`page_id`);

ALTER TABLE `civicrm_pcp_block` ADD `target_entity_type` VARCHAR( 64 ) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT 'contribute' AFTER `entity_id`;
ALTER TABLE `civicrm_pcp_block` ADD `target_entity_id` int(10) unsigned NOT NULL COMMENT 'The entity that this pcp targets' AFTER `target_entity_type`;
UPDATE `civicrm_pcp_block` SET `target_entity_id` = `entity_id` WHERE `target_entity_id` = '' OR `target_entity_id` IS NULL;

ALTER TABLE `civicrm_pcp` DROP COLUMN `referer`;

# ALTER TABLE `civicrm_pcp` ADD CONSTRAINT FK_civicrm_pcp_pcp_block_id FOREIGN KEY (pcp_block_id) REFERENCES civicrm_pcp_block;

#Other tasks
# 1) Change the civicrm/admin/pcp?reset=1 navigation item to civicrm/admin/pcp?reset=1&context=contribute
# 2) Add navigation item for civicrm/admin/pcp?reset=1&context=event

#TOTO
# 1) Need to get the default filtes to apply on the Manage Personal Contribution Pages forms (taken frrom the contect argument)
# 2) Lots of testing!