-- CRM-9795 (fix duplicate option values)

SELECT @option_group_id_act := max(id) from civicrm_option_group where name = 'activity_type';
SELECT @maxValue            := MAX(ROUND(value)) FROM civicrm_option_value WHERE option_group_id = @option_group_id_act;
SELECT @clientCaseValue     := value FROM civicrm_option_value WHERE name = 'Add Client To Case' AND option_group_id = @option_group_id_act;

UPDATE civicrm_option_value SET value = @maxValue + 1 WHERE name = 'Add Client To Case' AND option_group_id = @option_group_id_act;

UPDATE civicrm_activity 
INNER JOIN civicrm_case_activity ON civicrm_activity.id = civicrm_case_activity.activity_id
SET   civicrm_activity.activity_type_id = @maxValue + 1
WHERE civicrm_activity.activity_type_id = @clientCaseValue;

