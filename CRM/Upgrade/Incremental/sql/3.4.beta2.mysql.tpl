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

