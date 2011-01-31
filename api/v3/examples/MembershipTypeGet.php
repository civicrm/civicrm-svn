<?php 

function membership_type_get_example(){
    $params = array(
    
                  'id' 		=> '1',
                  'version' 		=> '3',

  );
  require_once 'api/api.php';
  $result = civicrm_api( 'civicrm_membership_type_get','MembershipType',$params );

  return $result;
}

/*
 * Function returns array of result expected from previous function
 */
function membership_type_get_expectedresult(){

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

