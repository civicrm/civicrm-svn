<?php 

function event_get_example(){
    $params = array(
    
                  'title' 		=> 'Annual CiviCRM meet',
                  'version' 		=> '3',

  );
  require_once 'api/api.php';
  $result = civicrm_api( 'civicrm_event_get','Event',$params );

  return $result;
}

/*
 * Function returns array of result expected from previous function
 */
function event_get_expectedresult(){

  $expectedResult = 
            array(
                  'is_error' 		=> '0',
                  'version' 		=> '3',
                  'count' 		=> '1',
                  'values' 		=>                   array('1' =>                   array('0' => '1',
                  ),                        ),

  );

  return $expectedResult  ;
}

