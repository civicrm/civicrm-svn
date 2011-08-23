-- CRM-8483

ALTER TABLE `civicrm_price_set`
   ADD `contribution_type_id` int(10) unsigned default NULL COMMENT 'Conditional foreign key to civicrm_contribution_type.id.',
   ADD CONSTRAINT `FK_civicrm_price_set_contribution_type_id` FOREIGN KEY (`contribution_type_id`) REFERENCES `civicrm_contribution_type` (`id`) ON DELETE SET NULL;

INSERT INTO civicrm_option_group
      (name, {localize field='label'}label{/localize}, {localize field='description'}description{/localize}, is_reserved, is_active)
VALUES
      ('auto_renew_options', {localize}'{ts escape="sql"}NULL{/ts}'{/localize}, {localize}'{ts escape="sql"}Auto Renew Options{/ts}'{/localize}, 0, 1);
  
ALTER TABLE `civicrm_price_field_value`
   ADD `membership_type_id` int(10) unsigned default NULL COMMENT 'Conditional foreign key to civicrm_membership_type.id.',
   ADD CONSTRAINT `FK_civicrm_price_field_value_membership_type_id` FOREIGN KEY (`membership_type_id`) REFERENCES `civicrm_membership_type` (`id`) ON DELETE SET NULL;

ALTER TABLE `civicrm_price_field_value`
   ADD `auto_renew` tinyint(4) default NULL;

SELECT @customizeID      := MAX(id) FROM civicrm_navigation where name = 'Memberships';
SELECT @extensionsWeight := MAX(weight)+1 FROM civicrm_navigation where parent_id = @customizeID;

INSERT INTO civicrm_navigation
    ( domain_id, url, label, name, permission, permission_operator, parent_id, is_active, has_separator, weight )
VALUES            
    ( {$domainID}, 'civicrm/admin/price&reset=1&action=add',        '{ts escape="sql" skip="true"}New Price Set{/ts}', 'New Price Set', 'access CiviMember,administer CiviCRM', '', @customizeID, '1', NULL, @extensionsWeight );

SELECT @customizeID      := MAX(id) FROM civicrm_navigation where name = 'CiviMember';
SELECT @extensionsWeight := MAX(weight)+1 FROM civicrm_navigation where parent_id = @customizeID;

INSERT INTO civicrm_navigation
    ( domain_id, url, label, name, permission, permission_operator, parent_id, is_active, has_separator, weight )
VALUES            
    ( {$domainID}, 'civicrm/admin/price&reset=1&action=add',        '{ts escape="sql" skip="true"}New Price Set{/ts}', 'New Price Set', 'access CiviMember,administer CiviCRM', '', @customizeID, '1', NULL, @extensionsWeight );
   
