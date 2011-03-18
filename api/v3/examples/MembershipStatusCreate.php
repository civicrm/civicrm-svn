<?php 

function membership_status_create_example(){
    $params = array(
    
                  'name' 		=> 'test membership status',
                  'version' 		=> '3',

  );
  require_once 'api/api.php';
  $result = civicrm_api( 'membership_status','create',$params );

  return $result;
}

/*
 * Function returns array of result expected from previous function
 */
function membership_status_create_expectedresult(){

  $expectedResult = 
     array(
           'is_error' 		=> '0',
           'version' 		=> '3',
           'count' 		=> '2',
           'id' 		=> '9',
           'values' 		=> array(           'id' => '9',                      'is_error' => '0',           ),
      );

  return $expectedResult  ;
}

