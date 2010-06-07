-- CRM-5224
SELECT @country_id := id FROM civicrm_country WHERE name = 'United Kingdom';
INSERT INTO civicrm_state_province (country_id,  abbreviation, name) VALUES (@country_id, 'LIN', 'Lincolnshire');
