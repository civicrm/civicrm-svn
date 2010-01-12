-- CRM-5536, CRM-5535

INSERT INTO civicrm_payment_processor_type 
( name, title, description, is_active, is_default, user_name_label, password_label, signature_label, subject_label, class_name, url_site_default, url_api_default, url_recur_default, url_button_default, url_site_test_default, url_api_test_default, url_recur_test_default, url_button_test_default, billing_mode, is_recur, payment_type) 
VALUES
( 'PayflowPro', '{ts escape="sql"}PayflowPro{/ts}', NULL, 1, 0, 'Vendor ID', 'Password', 'Partner (merchant)', 'User', 'Payment_PayflowPro', 'https://Payflowpro.paypal.com', NULL, NULL, NULL, 'https://pilot-Payflowpro.paypal.com', NULL, NULL, NULL, 1, 0, 1),
( 'FirstData', '{ts escape="sql"}FirstData (aka linkpoint){/ts}', '{ts escape="sql"}FirstData (aka linkpoint){/ts}', 1, 0, 'Store Name', 'Certificate Path', NULL, NULL, 'Payment_FirstData', 'https://secure.linkpt.net', NULL, NULL, NULL, 'https://staging.linkpt.net', NULL, NULL, NULL, 1, NULL, 1);

--CRM-5461 
    SELECT @option_group_id_act := max(id) from civicrm_option_group where name = 'activity_type';     

    INSERT INTO civicrm_option_value
        ( `option_group_id`,{localize field='label'}`label`{/localize},`value`, `name`, `grouping`, `filter`, `is_default`, `weight`, `description`, `is_optgroup`, `is_reserved`, `is_active`, `component_id`, `domain_id`, `visibility_id`)
    VALUES
        ( @option_group_id_act, {localize}'Print PDF Letter'{/localize}, '23', 'Print PDF Letter', NULL, 1, NULL, 23, 'Print PDF Letter.', 0, 1, 1, NULL, NULL, NULL);
