-- CRM-11847


UPDATE civicrm_dedupe_rule_group
  SET name = 'IndividualGeneral'
  WHERE name = 'IndividualComplete';