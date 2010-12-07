-- CRM-7127
ALTER TABLE civicrm_membership_type 
    DROP FOREIGN KEY `FK_civicrm_membership_type_relationship_type_id`;

ALTER TABLE civicrm_membership_type 
    DROP INDEX `FK_civicrm_membership_type_relationship_type_id`;

ALTER TABLE civicrm_membership_type 
    CHANGE relationship_type_id  relationship_type_id VARCHAR( 64 ) NULL DEFAULT NULL;

ALTER TABLE civicrm_membership_type 
    CHANGE relationship_direction  relationship_direction VARCHAR( 64 ) CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL DEFAULT NULL;