-- Set default participant role filter = 1, CRM-4924
   UPDATE   civicrm_option_value val
LEFT JOIN   civicrm_option_group gr ON ( gr.id = val.option_group_id ) 
      SET   val.filter = 1
    WHERE   gr.name = 'participant_role'
      AND   val.name IN ( 'Attendee', 'Host', 'Speaker', 'Volunteer' );




