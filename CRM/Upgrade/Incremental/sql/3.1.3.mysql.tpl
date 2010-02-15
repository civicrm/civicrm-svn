--CRM-5824 
    UPDATE civicrm_option_value ov 
INNER JOIN civicrm_option_group og ON ( og.id = ov.option_group_id )
       SET ov.name = ov.label
     WHERE og.name = 'activity_type' 
       AND ov.name IS NULL;
