 -- CRM-5333
 -- Delete duplicate records in target and assignment exists if any
 
 DELETE cat.* FROM civicrm_activity_target cat 
              INNER JOIN ( SELECT id, activity_id, target_contact_id 
                           FROM civicrm_activity_target 
                           GROUP BY activity_id, target_contact_id HAVING count(*) > 1 ) dup_cat 
                      ON ( cat.activity_id = dup_cat.activity_id 
                           AND cat.target_contact_id = dup_cat.target_contact_id 
                           AND cat.id <> dup_cat.id );

 DELETE caa.* FROM civicrm_activity_assignment caa 
              INNER JOIN ( SELECT id, activity_id, assignee_contact_id 
                           FROM civicrm_activity_assignment 
                           GROUP BY activity_id, assignee_contact_id HAVING count(*) > 1 ) dup_caa 
                      ON ( caa.activity_id = dup_caa.activity_id 
                           AND caa.assignee_contact_id = dup_caa.assignee_contact_id 
                           AND caa.id <> dup_caa.id );

 -- Drop unique indexes of activity_target and activity_assignment

  ALTER TABLE  civicrm_activity_assignment 
  DROP INDEX `UI_activity_assignee_contact_id` ,
  ADD UNIQUE INDEX `UI_activity_assignee_contact_id` (`assignee_contact_id`,`activity_id`);

  ALTER TABLE  civicrm_activity_target 
  DROP INDEX `UI_activity_target_contact_id` ,
  ADD UNIQUE INDEX `UI_activity_target_contact_id` (`target_contact_id`,`activity_id`);


-- CRM-5322

  SELECT @option_group_id_sfe := max(id) from civicrm_option_group where name = 'safe_file_extension';
  SELECT @max_val             := MAX(ROUND(op.value)) FROM civicrm_option_value op WHERE op.option_group_id  = @option_group_id_sfe;
  SELECT @max_wt              := max(weight) from civicrm_option_value where option_group_id= @option_group_id_sfe;

  INSERT INTO civicrm_option_value
    (option_group_id,      {localize field='label'}label{/localize}, value,                           filter, weight) VALUES
    (@option_group_id_sfe, {localize}'docx'{/localize},              (SELECT @max_val := @max_val+1), 0,      (SELECT @max_wt := @max_wt+1)),
    (@option_group_id_sfe, {localize}'xlsx'{/localize},              (SELECT @max_val := @max_val+1), 0,      (SELECT @max_wt := @max_wt+1));
