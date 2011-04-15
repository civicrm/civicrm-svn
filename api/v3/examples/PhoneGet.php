<?php 

function phone_get_example(){
    $params = array(
    
                  'contact_id' 		=> '',
                  'phone' 		=> '',
                  'version' 		=> '3',

  );
  require_once 'api/api.php';
  $result = civicrm_api( 'phone','get',$params );

  return $result;
}

/*
 * Function returns array of result expected from previous function
 */
function phone_get_expectedresult(){

  $expectedResult = 
     array(
           'is_error' 		=> '0',
           'version' 		=> '3',
           'count' 		=> '2',
           'values' 		=> array(           '1' =>  array(
                      'id' => '1',
                      'location_type_id' => '1',
                      'is_primary' => '0',
                      'is_billing' => '0',
                      'phone' => '204 222-1001',
                      'phone_type_id' => '1',
           ),                      '2' =>  array(
                      'id' => '2',
                      'location_type_id' => '6',
                      'is_primary' => '1',
                      'is_billing' => '0',
                      'phone' => '021 512 755',
                      'phone_type_id' => '1',
                      'contact_id' => '1',
           ),           ),
      );

  return $expectedResult  ;
}

