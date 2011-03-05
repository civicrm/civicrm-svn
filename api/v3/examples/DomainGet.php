<?php 

function domain_get_example(){
    $params = array(
    
                  'version' 		=> '3',

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
           '1' 		=> array(           'id' => '1',                      'domain_name' => 'Default Domain Name',                      'description' => '',                      'domain_email' => '',                      'domain_phone' =>  array(
                      'phone_type' => '',
                      'phone' => '',
           ),                      'domain_address' =>  array(
                      'street_address' => '',
                      'supplemental_address_1' => '',
                      'supplemental_address_2' => '',
                      'city' => '',
                      'state_province_id' => '',
                      'postal_code' => '',
                      'country_id' => '',
                      'geo_code_1' => '',
                      'geo_code_2' => '',
           ),                      'from_email' => 'info@FIXME.ORG',                      'from_name' => 'FIXME',           ),
      );

  return $expectedResult  ;
}

