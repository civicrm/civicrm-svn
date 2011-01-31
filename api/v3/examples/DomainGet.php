<?php 

function domain_get_example(){
    $params = array(
    
                  'entity_id' 		=> '1',
                  'entity_table' 		=> 'civicrm_domain',

  );
  require_once 'api/api.php';
  $result = civicrm_api( 'civicrm_domain_get','Domain',$params );

  return $result;
}

/*
 * Function returns array of result expected from previous function
 */
function domain_get_expectedresult(){

  $expectedResult = 
            array(
                  '1' 		=>                   array('id' => '1',                        'domain_name' => 'Default Domain Name',                        'description' => '',                        'domain_email' => '',                        'domain_phone' =>                   array('0' => 'domain_phone',
                  ),                        'domain_address' =>                   array('0' => 'domain_address',
                  ),                        'from_email' => 'info@FIXME.ORG',                        'from_name' => 'FIXME',                        ),

  );

  return $expectedResult  ;
}

