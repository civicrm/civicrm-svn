-- CRM-11847

ALTER TABLE civicrm_dedupe_rule_group DROP COLUMN is_default;

UPDATE civicrm_dedupe_rule_group
  SET name = 'IndividualGeneral'
  WHERE name = 'IndividualComplete';