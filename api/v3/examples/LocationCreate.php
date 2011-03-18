<?php 

function location_create_example(){
    $params = array(
    
                  'contact_id' 		=> '1',
                  'location_type' 		=> 'New Location Type',
                  'is_primary' 		=> '1',
                  'name' 		=> 'Saint Helier St',
                  'county' 		=> 'Marin',
                  'country' 		=> 'India',
                  'state_province' 		=> 'Michigan',
                  'street_address' 		=> 'B 103, Ram Maruti Road',
                  'supplemental_address_1' 		=> 'Hallmark Ct',
                  'supplemental_address_2' 		=> 'Jersey Village',
                  'version' 		=> '3',
                  'address' 		=> 'Array',

  );
  require_once 'api/api.php';
  $result = civicrm_api_legacy( 'civicrm_location_create','Location',$params );

  return $result;
}

/*
 * Function returns array of result expected from previous function
 */
function location_create_expectedresult(){

  $expectedResult = 
            array(
                  'is_error' 		=> '0',
                  'version' 		=> '3',
                  'count' 		=> '0',
                  'values' 		=> '',

  );

  return $expectedResult  ;
}

