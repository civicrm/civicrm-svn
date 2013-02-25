<?php

/*
 Get entities and location block in 1 api call
 */
function loc_block_get_example(){
$params = array( 
  'version' => 3,
  'id' => 3,
  'return' => 'all',
);

  $result = civicrm_api( 'loc_block','get',$params );

  return $result;
}

/*
 * Function returns array of result expected from previous function
 */
function loc_block_get_expectedresult(){

  $expectedResult = array( 
  'id' => '3',
  'address_id' => '3',
  'email_id' => '4',
  'phone_id' => '3',
  'phone_2_id' => '4',
  'address' => array( 
      'id' => '3',
      'location_type_id' => '1',
      'is_primary' => 0,
      'is_billing' => 0,
      'street_address' => '987654321',
      'manual_geo_code' => 0,
    ),
  'email' => array( 
      'id' => '4',
      'location_type_id' => '1',
      'email' => 'test2@loc.block',
      'is_primary' => 0,
      'is_billing' => 0,
      'on_hold' => 0,
      'is_bulkmail' => 0,
    ),
  'phone' => array( 
      'id' => '3',
      'location_type_id' => '1',
      'is_primary' => 0,
      'is_billing' => 0,
      'phone' => '987654321',
      'phone_numeric' => '987654321',
    ),
  'phone_2' => array( 
      'id' => '4',
      'location_type_id' => '1',
      'is_primary' => 0,
      'is_billing' => 0,
      'phone' => '456-7890',
      'phone_numeric' => '4567890',
    ),
);

  return $expectedResult  ;
}


/*
* This example has been generated from the API test suite. The test that created it is called
*
* testCreateLocBlockEntities and can be found in
* http://svn.civicrm.org/civicrm/trunk/tests/phpunit/CiviTest/api/v3/LocBlockTest.php
*
* You can see the outcome of the API tests at
* http://tests.dev.civicrm.org/trunk/results-api_v3
*
* To Learn about the API read
* http://book.civicrm.org/developer/current/techniques/api/
*
* and review the wiki at
* http://wiki.civicrm.org/confluence/display/CRMDOC/CiviCRM+Public+APIs
*
* Read more about testing here
* http://wiki.civicrm.org/confluence/display/CRM/Testing
*
* API Standards documentation:
* http://wiki.civicrm.org/confluence/display/CRM/API+Architecture+Standards
*/