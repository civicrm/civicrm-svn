-- CRM-11847
UPDATE civicrm_dedupe_rule_group
  SET name = 'IndividualGeneral'
  WHERE name = 'IndividualComplete';
  
-- CRM-11791
INSERT INTO civicrm_relationship_type ( name_a_b,label_a_b, name_b_a,label_b_a, description, contact_type_a, contact_type_b, is_reserved )
  VALUES
  ( 'Partner of', '{ts escape="sql"}Partner of{/ts}', 'Partner of', '{ts escape="sql"}Partner of{/ts}', '{ts escape="sql"}Partner relationship.{/ts}', 'Individual', 'Individual', 0 );

