-- CRM-5628
SELECT @country_id := id from civicrm_country where name = 'Haiti';

INSERT INTO civicrm_state_province ( country_id, abbreviation, name ) VALUES
( @country_id,  'AR',  'Artibonite' ),
( @country_id,  'CE',  'Centre'     ),
( @country_id,  'NI',  'Nippes'     ),
( @country_id,  'ND',  'Nord'       );

    UPDATE  civicrm_state_province state
INNER JOIN  civicrm_country country ON ( country.id = state.country_id ) 
       SET  state.name = 'Nord-Est'
     WHERE  state.name = 'Nord-Eat'
       AND  country.name = 'Haiti';

INSERT INTO civicrm_acl
    (name, deny, entity_table, entity_id, operation, object_table, object_id, acl_table, acl_id, is_active)
VALUES
    ('Core ACL', 0, 'civicrm_acl_role', 1, 'All', 'profile create',   NULL, NULL, NULL, 1),
    ('Core ACL', 0, 'civicrm_acl_role', 1, 'All', 'profile edit',     NULL, NULL, NULL, 1),
    ('Core ACL', 0, 'civicrm_acl_role', 1, 'All', 'profile listings', NULL, NULL, NULL, 1),
    ('Core ACL', 0, 'civicrm_acl_role', 1, 'All', 'profile view',     NULL, NULL, NULL, 1);
