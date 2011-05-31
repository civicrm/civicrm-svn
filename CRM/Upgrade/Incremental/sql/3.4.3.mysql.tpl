-- ARMS 
-- CRM-8148, rename uf field 'activity_status' to 'activity_status_id'
UPDATE civicrm_uf_field SET field_name = 'activity_type_id' WHERE field_name= 'activity_type';

-- CRM-8148, we are not deleting uf fields 'activity_type', 'activity_is_deleted', 'activity_engagement_level', 'activity_is_test'

-- CRM-8209
SELECT @option_group_id_adv_search_opts := max(id) from civicrm_option_group where name = 'advanced_search_options';

INSERT INTO 
    `civicrm_option_value` (`option_group_id`, {localize field='label'}label{/localize}, `value`, `name`, `filter`, `weight`)
VALUES
    (@option_group_id_adv_search_opts, '{localize}Mailing{/localize}', '19', 'CiviMail', 0, 21);