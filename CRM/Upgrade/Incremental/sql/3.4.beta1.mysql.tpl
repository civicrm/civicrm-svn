-- CRM-7646
SELECT @option_group_id_pi := max(id) from civicrm_option_group where name = 'payment_instrument';
UPDATE civicrm_option_value
    SET is_reserved = 1
    WHERE option_group_id = @option_group_id_pi AND name = 'Check';
UPDATE civicrm_option_value
    SET is_reserved = 1
    WHERE option_group_id = @option_group_id_pi AND name = 'Debit Card';