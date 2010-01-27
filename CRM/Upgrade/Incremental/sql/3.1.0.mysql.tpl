
-- CRM-5711

UPDATE civicrm_custom_group SET extends_entity_column_value = CONCAT(CHAR( 01 ), extends_entity_column_value, CHAR( 01 ))
WHERE LOCATE( char( 01 ), extends_entity_column_value ) <= 0;
