-- http://forum.civicrm.org/?topic=12481
UPDATE civicrm_state_province SET name = 'Guizhou' WHERE name = 'Gulzhou';

-- CRM-5824
{if $multilingual}
    UPDATE civicrm_option_value ov 
INNER JOIN civicrm_option_group og ON ( og.id = ov.option_group_id )
       SET ov.name = ov.label_{$config->lcMessages}
     WHERE og.name = 'case_type' 
       AND ov.name IS NULL;
{else}
    UPDATE civicrm_option_value ov 
INNER JOIN civicrm_option_group og ON ( og.id = ov.option_group_id )
       SET ov.name = ov.label
     WHERE og.name = 'case_type' 
       AND ov.name IS NULL;
{/if}