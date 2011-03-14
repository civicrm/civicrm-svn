-- CRM-7743
SELECT @option_group_id_languages := MAX(id) FROM civicrm_option_group WHERE name = 'languages';
UPDATE civicrm_option_value SET name = 'ce_RU' WHERE value = 'ce' AND option_group_id = @option_group_id_languages;
