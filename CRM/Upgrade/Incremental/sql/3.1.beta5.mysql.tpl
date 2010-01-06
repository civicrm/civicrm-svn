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



