<?php



/*
 Get all from group and display contacts
 */
function group_contact_get_example(){
$params = array( 
  'contact_id' => '',
  'version' => 3,
  'api.contact.get' => 1,
);

  require_once 'api/api.php';
  $result = civicrm_api( 'group_contact','get',$params );

  return $result;
}

/*
 * Function returns array of result expected from previous function
 */
function group_contact_get_expectedresult(){

  $expectedResult = array( 
  'is_error' => 0,
  'version' => 3,
  'count' => 1,
  'id' => 2,
  'values' => array( 
      '2' => array( 
          'id' => '2',
          'group_id' => '1',
          'contact_id' => '1',
          'status' => 'Added',
        ),
    ),
);

  return $expectedResult  ;
}




/*
* This example has been generated from the API test suite. The test that created it is called
* group_contact_get 
* You can see the outcome of the API tests at 
* http://tests.dev.civicrm.org/trunk/results-api_v3
* and review the wiki at
* http://wiki.civicrm.org/confluence/display/CRMDOC40/CiviCRM+Public+APIs
* Read more about testing here
* http://wiki.civicrm.org/confluence/display/CRM/Testing
*/