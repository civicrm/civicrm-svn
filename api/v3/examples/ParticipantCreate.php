<?php 

function participant_create_example(){
    $params = array(
    
                  'contact_id' 		=> '2',
                  'event_id' 		=> '1',
                  'status_id' 		=> '1',
                  'role_id' 		=> '1',
                  'register_date' 		=> '20070721',
                  'source' 		=> 'Online Event Registration: API Testing',
                  'event_level' 		=> 'Tenor',
                  'version' 		=> '3',
                  'participant_id' 		=> '4',

  );
  require_once 'api/api.php';
  $result = civicrm_api_legacy( 'civicrm_participant_create','Participant',$params );

  return $result;
}

/*
 * Function returns array of result expected from previous function
 */
function participant_create_expectedresult(){

  $expectedResult = 
            array(
                  'is_error' 		=> '0',
                  'version' 		=> '3',
                  'count' 		=> '1',
                  'values' 		=> '4',

  );

  return $expectedResult  ;
}

