-- CRM-8483

ALTER TABLE `civicrm_price_set`
   ADD `contribution_type_id` int(10) unsigned default NULL COMMENT 'Conditional foreign key to civicrm_contribution_type.id.',
   ADD CONSTRAINT `FK_civicrm_price_set_contribution_type_id` FOREIGN KEY (`contribution_type_id`) REFERENCES `civicrm_contribution_type` (`id`) ON DELETE SET NULL;

ALTER TABLE `civicrm_price_field_value`
   ADD `membership_type_id` int(10) unsigned default NULL COMMENT 'Conditional foreign key to civicrm_membership_type.id.',
   ADD CONSTRAINT `FK_civicrm_price_field_value_membership_type_id` FOREIGN KEY (`membership_type_id`) REFERENCES `civicrm_membership_type` (`id`) ON DELETE SET NULL;

ALTER TABLE `civicrm_price_field_value`
   ADD `auto_renew` tinyint(4) default NULL;

INSERT INTO civicrm_navigation
    ( domain_id, url, label, name, permission, permission_operator, parent_id, is_active, has_separator, weight )
VALUES            
    ( {$domainID}, 'civicrm/admin/price&reset=1&action=add',        '{ts escape="sql" skip="true"}New Price Set{/ts}', 'New Price Set', 'access CiviMember,administer CiviCRM', '', @customizeID, '1', NULL, @extensionsWeight );


INSERT INTO civicrm_navigation
    ( domain_id, url, label, name, permission, permission_operator, parent_id, is_active, has_separator, weight )
VALUES            
    ( {$domainID}, 'civicrm/admin/price&reset=1&action=add',        '{ts escape="sql" skip="true"}New Price Set{/ts}', 'New Price Set', 'access CiviMember,administer CiviCRM', '', @customizeID, '1', NULL, @extensionsWeight );
   
