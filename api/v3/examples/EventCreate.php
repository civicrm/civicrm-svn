<?php 

function event_create_example(){
    $params = array(
    

  );
  require_once 'api/api.php';
  $result = civicrm_api( 'civicrm_event_create','Event',$params );

  return $result;
}

/*
 * Function returns array of result expected from previous function
 */
function event_create_expectedresult(){

  $expectedResult = 
            array(
                  'is_error' 		=> '0',
                  'version' 		=> '3',
                  'count' 		=> '1',
                  'values' 		=>                   array('event_id' => '2',                        ),

  );

  return $expectedResult  ;
}

