<?php 

function activity_create_example(){
    $params = array(
    
                  'source_contact_id' 		=> '17',
                  'subject' 		=> 'Make-it-Happen Meeting',
                  'activity_date_time' 		=> '20110208',
                  'duration' 		=> '120',
                  'location' 		=> 'Pensulvania',
                  'details' 		=> 'a test activity',
                  'status_id' 		=> '1',
                  'activity_name' 		=> 'Test activity type',
                  'version' 		=> '3',

  );
  require_once 'api/api.php';
  $result = civicrm_api_legacy( 'civicrm_activity_create','Activity',$params );

  return $result;
}

/*
 * Function returns array of result expected from previous function
 */
function activity_create_expectedresult(){

  $expectedResult = 
            array(
                  'is_error' 		=> '1',
                  'error_message' 		=> 'Undefined index: id',

  );

  return $expectedResult  ;
}

