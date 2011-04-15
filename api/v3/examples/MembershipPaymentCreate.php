<?php 

function membership_payment_create_example(){
    $params = array(
    
                  'contribution_id' 		=> '1',
                  'membership_id' 		=> '1',
                  'version' 		=> '3',

  );
  require_once 'api/api.php';
  $result = civicrm_api( 'membership_payment','create',$params );

  return $result;
}

/*
 * Function returns array of result expected from previous function
 */
function membership_payment_create_expectedresult(){

  $expectedResult = 
     array(
           'is_error' 		=> '0',
           'version' 		=> '3',
           'count' 		=> '1',
           'id' 		=> '1',
           'values' 		=> array(           '1' =>  array(
                      'id' => '1',
                      'membership_id' => '1',
                      'contribution_id' => '1',
           ),           ),
      );

  return $expectedResult  ;
}

