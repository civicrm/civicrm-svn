<?php 

function domain_get_example(){
    $params = array(
    
                  'version' 		=> '3',
                  'current_domain' 		=> '1',

  );
  require_once 'api/api.php';
  $result = civicrm_api( 'domain','get',$params );

  return $result;
}

/*
 * Function returns array of result expected from previous function
 */
function domain_get_expectedresult(){

  $expectedResult = 
     array(
           'is_error' 		=> '0',
           'version' 		=> '3',
           'count' 		=> '1',
           'id' 		=> '1',
           'values' 		=> array(           '1' =>  array(
                      'id' => '1',
                      'name' => 'Default Domain Name',
                      'version' => '3.4.beta3',
                      'domain_email' => '',
                      'domain_phone' => 'Array',
                      'domain_address' => 'Array',
                      'from_email' => 'info@FIXME.ORG',
                      'from_name' => 'FIXME',
           ),           ),
      );

  return $expectedResult  ;
}

