-- CRM-5224
SELECT @country_id := id FROM civicrm_country WHERE name = 'United Kingdom';
INSERT INTO civicrm_state_province (country_id,  abbreviation, name) VALUES (@country_id, 'LIN', 'Lincolnshire');

-- CRM-5938

INSERT INTO `civicrm_dashboard` 
    ( `domain_id`, `label`, `url`, `content`, `permission`, `permission_operator`, `column_no`, `is_minimized`, `is_active`, `weight`, `created_date`, `is_fullscreen`, `is_reserved`) 
    VALUES 
    ( @domainID, '{ts escape="sql"}My Cases{/ts}', 'civicrm/dashlet/myCases&reset=1&snippet=4', NULL, 'access CiviCase', NULL , '0', '0', '1', '1', '1', '1', NULL),
    ( @domainID, '{ts escape="sql"}All Cases{/ts}', 'civicrm/dashlet/allCases&reset=1&snippet=4', NULL, 'access CiviCase', NULL , '0', '0', '1', '1', '1', '1', NULL);
