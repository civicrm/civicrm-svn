-- CRM-5536, CRM-5535

INSERT INTO civicrm_payment_processor_type 
( name, title, description, is_active, is_default, user_name_label, password_label, signature_label, subject_label, class_name, url_site_default, url_api_default, url_recur_default, url_button_default, url_site_test_default, url_api_test_default, url_recur_test_default, url_button_test_default, billing_mode, is_recur, payment_type) 
VALUES
( 'PayflowPro', '{ts escape="sql"}PayflowPro{/ts}', NULL, 1, 0, 'Vendor ID', 'Password', 'Partner (merchant)', 'User', 'Payment_PayflowPro', 'https://Payflowpro.paypal.com', NULL, NULL, NULL, 'https://pilot-Payflowpro.paypal.com', NULL, NULL, NULL, 1, 0, 1),
( 'FirstData', '{ts escape="sql"}FirstData (aka linkpoint){/ts}', '{ts escape="sql"}FirstData (aka linkpoint){/ts}', 1, 0, 'Store Name', 'Certificate Path', NULL, NULL, 'Payment_FirstData', 'https://secure.linkpt.net', NULL, NULL, NULL, 'https://staging.linkpt.net', NULL, NULL, NULL, 1, NULL, 1);

-- CRM-5461 
    SELECT @option_group_id_act := max(id) from civicrm_option_group where name = 'activity_type';

    INSERT INTO civicrm_option_value
        ( `option_group_id`,{localize field='label'}`label`{/localize},`value`, `name`, `grouping`, `filter`, `is_default`, `weight`, `description`, `is_optgroup`, `is_reserved`, `is_active`, `component_id`, `domain_id`, `visibility_id`)
    VALUES
        ( @option_group_id_act, {localize}'Print PDF Letter'{/localize}, '23', 'Print PDF Letter', NULL, 1, NULL, 23, 'Print PDF Letter.', 0, 1, 1, NULL, NULL, NULL);

-- CRM-5344
    ALTER TABLE civicrm_uf_group
    MODIFY notify text;

-- CRM-5598

SELECT @option_group_id_activity_type := max(id) from civicrm_option_group where name = 'activity_type';

SELECT @atOpt_max_val := MAX(ROUND(op.value)) FROM civicrm_option_value op WHERE op.option_group_id = @option_group_id_activity_type;

SELECT @atOpt_max_wt  := MAX(ROUND(val.weight)) FROM civicrm_option_value val where val.option_group_id = @option_group_id_activity_type;

SELECT @caseCompId    := max(id) FROM civicrm_component where name = 'CiviCase';

INSERT INTO 
   civicrm_option_value(`option_group_id`, {localize field='label'}`label`{/localize}, `value`, `name`, `grouping`, `filter`, `is_default`, `weight`, `is_optgroup`, `is_reserved`, `is_active`, `component_id`, `visibility_id`) 
VALUES(@option_group_id_activity_type, {localize}'Merge Case'{/localize}, (SELECT @atOpt_max_val := @atOpt_max_val+1), 'Merge Case', NULL, 0, NULL, (SELECT @atOpt_max_wt := @atOpt_max_wt + 1 ), 0, 1, 1, @caseCompId, NULL ), 
      (@option_group_id_activity_type, {localize}'Reassigned Case'{/localize}, (SELECT @atOpt_max_val := @atOpt_max_val+1), 'Reassigned Case', NULL, 0, NULL, (SELECT @atOpt_max_wt := @atOpt_max_wt + 1 ), 0, 1, 1, @caseCompId, NULL ),
      (@option_group_id_activity_type, {localize}'Link Cases'{/localize}, (SELECT @atOpt_max_val := @atOpt_max_val+1), 'Link Cases', NULL, 0, NULL, (SELECT @atOpt_max_wt := @atOpt_max_wt + 1 ), 0, 1, 1, @caseCompId, NULL );
      

-- CRM-5752
    UPDATE civicrm_option_value val 
        LEFT JOIN civicrm_option_group gr ON ( gr.id = val.option_group_id ) 
        SET val.is_reserved = 1
        WHERE gr.name = 'contribution_status' AND val.name IN ( 'Completed', 'Pending', 'Cancelled', 'Failed', 'In Progress', 'Overdue' );

-- CRM-5831
    ALTER TABLE civicrm_email 
    	ADD `signature_text` text COLLATE utf8_unicode_ci COMMENT 'Text formatted signature for the email.',
	ADD `signature_html` text COLLATE utf8_unicode_ci COMMENT 'HTML formatted signature for the email.';

-- CRM-5787
   UPDATE civicrm_option_value val
       	INNER JOIN civicrm_option_group gr ON ( gr.id = val.option_group_id )   
	SET val.grouping = 'Opened' 
	WHERE gr.name = 'case_status' AND val.name IN ( 'Open', 'Urgent' );
   
   UPDATE civicrm_option_value val
       	INNER JOIN civicrm_option_group gr ON ( gr.id = val.option_group_id )  	 
	SET val.grouping = 'Closed'  
	WHERE gr.name = 'case_status' AND val.name = 'Closed';

   SELECT @domain_id := min(id) FROM civicrm_domain;
   SELECT @nav_case    := id FROM civicrm_navigation WHERE name = 'CiviCase';
   SELECT @nav_case_weight := MAX(ROUND(weight)) from civicrm_navigation WHERE parent_id = @nav_case;

   INSERT INTO civicrm_navigation
        ( domain_id, url, label, name, permission, permission_operator, parent_id, is_active, has_separator, weight )
   VALUES
	( @domain_id, 'civicrm/admin/options/case_status&group=case_status&reset=1', '{ts escape="sql"}Case Statuses{/ts}','Case Statuses',  'administer CiviCase', NULL, @nav_case, '1', NULL, @nav_case_weight+1 );

-- CRM-5766
   ALTER TABLE civicrm_price_field
   ADD `visibility_id` int(10) unsigned default 1 COMMENT 'Implicit FK to civicrm_option_group with name = visibility.';

-- CRM-5612
   ALTER TABLE civicrm_cache
   MODIFY path varchar(255) COMMENT 'Unique path name for cache element';
   
-- CRM-5874
   ALTER TABLE civicrm_uf_group
   ADD `is_proximity_search` tinyint(4) unsigned default 0 COMMENT 'Should proximity search be included in profile search form?';
