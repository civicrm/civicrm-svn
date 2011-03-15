-- CRM-7743
SELECT @option_group_id_languages := MAX(id) FROM civicrm_option_group WHERE name = 'languages';
UPDATE civicrm_option_value SET name = 'ce_RU' WHERE value = 'ce' AND option_group_id = @option_group_id_languages;

-- CRM-7750
SELECT @option_group_id_report := MAX(id)     FROM civicrm_option_group WHERE name = 'report_template';
SELECT @weight                 := MAX(weight) FROM civicrm_option_value WHERE option_group_id = @option_group_id_report;
SELECT @contributeCompId       := MAX(id)     FROM civicrm_component    WHERE name = 'CiviContribute';

INSERT INTO civicrm_option_value
  (option_group_id, {localize field='label'}label{/localize}, value, name, weight, {localize field='description'}description{/localize}, is_active, component_id) 
  VALUES
  (@option_group_id_report, {localize}'Personal Campaign Page Report'{/localize}, 'contribute/pcp', 'CRM_Report_Form_Contribute_PCP', @weight := @weight + 1, {localize}'Shows Personal Campaign Page Report.'{/localize}, 1, @contributeCompId );
  