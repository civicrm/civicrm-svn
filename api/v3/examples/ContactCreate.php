<?php 

function contact_create_example(){
    $params = array(
    
                  'first_name' 		=> 'abc4',
                  'last_name' 		=> 'xyz4',
                  'email' 		=> 'Array',
                  'contact_type' 		=> 'Individual',
                  'location_type_id' 		=> '1',
                  'version' 		=> '3',
                  'custom' 		=> 'Array',
                  'preferred_language' 		=> 'en_US',
                  'is_deceased' 		=> '',
                  'contact_id' 		=> '1',
                  'website' 		=> '',

  );
  require_once 'api/api.php';
  $result = civicrm_api( 'civicrm_contact_create','Contact',$params );

  return $result;
}

/*
 * Function returns array of result expected from previous function
 */
function contact_create_expectedresult(){

  $expectedResult = 
            array(
                  'contact_id' 		=> '1',
                  'is_error' 		=> '0',

  );

  return $expectedResult  ;
}

