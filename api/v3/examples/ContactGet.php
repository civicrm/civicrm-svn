<?php 

function contact_get_example(){
    $params = array(
    
                  'email' 		=> 'man2',
                  'contact_is_deleted' 		=> '0',

  );
  require_once 'api/api.php';
  $result = civicrm_api( 'civicrm_contact_get','Contact',$params );

  return $result;
}

/*
 * Function returns array of result expected from previous function
 */
function contact_get_expectedresult(){

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

