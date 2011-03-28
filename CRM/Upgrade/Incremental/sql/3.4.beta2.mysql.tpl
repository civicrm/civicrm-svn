{if $addPetitionOptionGroup}

SELECT @domainID := MIN(id) FROM civicrm_domain;

INSERT INTO `civicrm_option_group` 
    ( `name`, {localize field='label'}label{/localize}, {localize field='description'}description{/localize}, `is_reserved`, `is_active` ) 
VALUES 
    ( 'msg_tpl_workflow_petition', {localize}'Message Template Workflow for Petition'{/localize},{localize}'Message Template Workflow for Petition'{/localize}, 0, 1 );

SELECT @option_group_id := MAX(id) from civicrm_option_group WHERE name = 'msg_tpl_workflow_petition';

INSERT INTO `civicrm_option_value` 
    ( `option_group_id`, {localize field='label'}label{/localize}, `name`, `value`, `weight`, `is_active`, `domain_id` ) 
VALUES
        ( @option_group_id, {localize}'Petition - signature added'{/localize}, 'petition_sign', 1, 1, 1, @domainID ),
        ( @option_group_id, {localize}'Petition - need verification'{/localize}, 'petition_confirmation_needed', 2, 2, 1, @domainID );
{/if}


-- CRM-7801
SELECT @domain_id       := min(id) FROM civicrm_domain;
SELECT @nav_case        := id FROM civicrm_navigation WHERE name = 'CiviCase';
SELECT @nav_case_weight := MAX(ROUND(weight)) from civicrm_navigation WHERE parent_id = @nav_case;

INSERT INTO civicrm_navigation
        ( domain_id, url, label, name, permission, permission_operator, parent_id, is_active, has_separator, weight )
VALUES
	( @domain_id, 'civicrm/admin/options/encounter_medium&group=encounter_medium&reset=1', '{ts escape="sql"}Encounter Medium{/ts}','Encounter Medium',  'administer CiviCase', NULL, @nav_case, '1', NULL, @nav_case_weight+1 );
