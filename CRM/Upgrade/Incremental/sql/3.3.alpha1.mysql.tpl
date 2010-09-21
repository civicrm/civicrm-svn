-- CRM-6696
ALTER TABLE civicrm_option_value {localize field='description'}MODIFY COLUMN description text{/localize};

-- CRM-6157
INSERT INTO civicrm_payment_processor_type
        ( name, title, description, is_active, is_default, user_name_label, password_label, signature_label, subject_label,  class_name, url_site_default, url_api_default, url_recur_default, url_button_default, url_site_test_default, url_api_test_default, url_recur_test_default, url_button_test_default, billing_mode, is_recur, payment_type)
   VALUES
        ( 'Flo2CashDonate','{ts escape="sql"}Flo2CashDonate{/ts}',NULL,1,0,'Account ID', NULL, NULL, NULL,'Payment_Flo2CashDonate', 'https://secure.flo2cash.co.nz/web2pay/default.aspx', NULL, 'https://secure.flo2cash.co.nz/web2pay/default.aspx', NULL,'http://demo.flo2cash.co.nz/web2pay/default.aspx',NULL,'http://demo.flo2cash.co.nz/web2pay/default.aspx',NULL,4,1,1);

-- CRM-6442
SELECT @option_group_id_website := MAX(id) from civicrm_option_group where name = 'website_type';
SELECT @max_value               := MAX(ROUND(value)) from civicrm_option_value where option_group_id = @option_group_id_website;
SELECT @max_weight              := MAX(ROUND(weight)) from civicrm_option_value where option_group_id = @option_group_id_website;;

INSERT INTO civicrm_option_value
        (option_group_id, {localize field='label'}label{/localize}, value, name, grouping, filter, is_default, weight, {localize field='description'}description{/localize}, is_optgroup, is_reserved, is_active, component_id, visibility_id)
VALUES
	(@option_group_id_website, {localize}'Main'{/localize}, @max_value+1, 'Main', NULL, 0, NULL, @max_weight+1, {localize}NULL{/localize}, 0, 0, 1, NULL, NULL);
	
-- CRM-6763
UPDATE civicrm_option_group 
   SET is_reserved = 0
 WHERE civicrm_option_group.name = 'encounter_medium';

-- CRM-6846
ALTER TABLE civicrm_custom_field
  ADD name varchar(255) collate utf8_unicode_ci default NULL;

-- CRM-6814
ALTER TABLE `civicrm_note` 
  ADD `privacy` INT( 10 ) NOT NULL COMMENT 'Foreign Key to Note Privacy Level (which is an option value pair and hence an implicit FK)';

-- CRM-6748
UPDATE civicrm_navigation SET url = 'civicrm/admin/contribute/add&reset=1&action=add'
        WHERE civicrm_navigation.name = 'New Contribution Page';