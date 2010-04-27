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