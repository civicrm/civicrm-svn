<?php



/*
 /*this demonstrates the usage of multi-level chained arrays 
 */
function contact_get_example(){
$params = array( 
  'version' => 3,
  'api.relationship.get' => 1,
);

  require_once 'api/api.php';
  $result = civicrm_api( 'contact','get',$params );

  return $result;
}

/*
 * Function returns array of result expected from previous function
 */
function contact_get_expectedresult(){

  $expectedResult = array( 
  'is_error' => 0,
  'version' => 3,
  'count' => 0,
  'values' => array( 
      '' => array( 
          'api.relationship.get' => array( 
              'is_error' => 1,
              'error_message' => 'Mandatory key(s) missing from params array: contact_id',
            ),
        ),
    ),
);

  return $expectedResult  ;
}




/*
* This example has been generated from the API test suite. The test that created it is called
* contact_get 
* You can see the outcome of the API tests at 
* http://tests.dev.civicrm.org/trunk/results-api_v3
* and review the wiki at
* http://wiki.civicrm.org/confluence/display/CRMDOC40/CiviCRM+Public+APIs
* Read more about testing here
* http://wiki.civicrm.org/confluence/display/CRM/Testing
*/