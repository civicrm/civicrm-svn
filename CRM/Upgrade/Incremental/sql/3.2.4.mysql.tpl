--CRM-6889

UPDATE     civicrm_option_value ov 
INNER JOIN civicrm_option_group og ON ( og.id = ov.option_group_id )
       SET ov.filter = 1
WHERE      og.name = 'activity_type' 
       AND ov.name = 'Print PDF Letter';