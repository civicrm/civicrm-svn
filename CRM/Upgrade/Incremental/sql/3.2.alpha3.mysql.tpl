-- CRM-6144
   DELETE civicrm_activity.* FROM civicrm_activity 
       LEFT JOIN civicrm_option_value ON ( civicrm_option_value.value = civicrm_activity.activity_type_id )
      INNER JOIN civicrm_option_group ON ( civicrm_option_group.id = civicrm_option_value.option_group_id ) 
      WHERE civicrm_option_group.name = 'activity_type' 
        AND civicrm_option_value.name = 'Close Case';
           
   DELETE civicrm_option_value.* FROM civicrm_option_value 
      INNER JOIN civicrm_option_group ON ( civicrm_option_group.id = civicrm_option_value.option_group_id ) 
      WHERE civicrm_option_group.name = 'activity_type'
        AND civicrm_option_value.name = 'Close Case';

-- CRM-6102
ALTER TABLE civicrm_preferences 
    ADD sort_name_format TEXT COMMENT 'Format to display contact sort name' AFTER mailing_format,
    ADD display_name_format TEXT COMMENT 'Format to display the contact display name' AFTER  mailing_format;

UPDATE civicrm_preferences 
    SET display_name_format = '{literal}{contact.individual_prefix}{ }{contact.first_name}{ }{contact.last_name}{ }{contact.individual_suffix}{/literal}', 
        sort_name_format    = '{literal}{contact.last_name}{, }{contact.first_name}{/literal}'
    WHERE is_domain = 1;
