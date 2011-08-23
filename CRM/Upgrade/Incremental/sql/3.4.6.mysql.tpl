-- CRM-8483

ALTER TABLE `civicrm_price_set`
   ADD `contribution_type_id` int(10) unsigned default NULL COMMENT 'Conditional foreign key to civicrm_contribution_type.id.',
   ADD CONSTRAINT `FK_civicrm_price_set_contribution_type_id` FOREIGN KEY (`contribution_type_id`) REFERENCES `civicrm_contribution_type` (`id`) ON DELETE SET NULL;


{if $multilingual}
  INSERT INTO civicrm_option_group (name, {foreach from=$locales item=locale}label_{$locale},{/foreach}{foreach from=$locales item=locale}description_{$locale},{/foreach} is_reserved, is_active) VALUES ('auto_renew_options', {foreach from=$locales item=locale}NULL,{/foreach}{foreach from=$locales item=locale}'auto_renew_options',{/foreach} 0, 1);

{else}

INSERT INTO civicrm_option_group
    ( name, label, description, is_reserved, is_active)
VALUES            
    ( 'auto_renew_options', NULL, 'Auto Renew Options', 0, 1);
{/if}

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
   
