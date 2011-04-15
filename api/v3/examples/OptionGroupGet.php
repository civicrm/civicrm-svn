<?php 

function option_group_get_example(){
    $params = array(
    
                  'name' 		=> 'preferred_communication_method',
                  'version' 		=> '3',

  );
  require_once 'api/api.php';
  $result = civicrm_api( 'option_group','get',$params );

  return $result;
}

/*
 * Function returns array of result expected from previous function
 */
function option_group_get_expectedresult(){

  $expectedResult = 
     array(
           'is_error' 		=> '0',
           'version' 		=> '3',
           'count' 		=> '1',
           'id' 		=> '1',
           'values' 		=> array(           '1' =>  array(
                      'id' => '1',
                      'name' => 'preferred_communication_method',
                      'description' => 'Preferred Communication Method',
                      'is_reserved' => '0',
                      'is_active' => '1',
           ),           ),
      );

  return $expectedResult  ;
}

