-- CRM-6696
ALTER TABLE civicrm_option_value MODIFY COLUMN description text;

-- CRM-6157
INSERT INTO civicrm_payment_processor_type
        ( name, title, description, is_active, is_default, user_name_label, password_label, signature_label, subject_label,  class_name, url_site_default, url_api_default, url_recur_default, url_button_default, url_site_test_default, url_api_test_default, url_recur_test_default, url_button_test_default, billing_mode, is_recur, payment_type)
   VALUES
        ( 'Flo2CashDonate','{ts escape="sql"}Flo2CashDonate{/ts}',NULL,1,0,'Account ID', NULL, NULL, NULL,'Payment_Flo2CashDonate', 'https://secure.flo2cash.co.nz/web2pay/default.aspx', NULL, 'https://secure.flo2cash.co.nz/web2pay/default.aspx', NULL,'http://demo.flo2cash.co.nz/web2pay/default.aspx',NULL,'http://demo.flo2cash.co.nz/web2pay/default.aspx',NULL,4,1,1);