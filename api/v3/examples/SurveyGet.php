<?php



/*
 
 */
function survey_get_example(){
$params = array( 
  'version' => 3,
  'title' => 'survey title',
  'activity_type_id' => '',
  'max_number_of_contacts' => 12,
  'instructions' => 'Call people, ask for money',
);

  require_once 'api/api.php';
  $result = civicrm_api( 'survey','get',$params );

  return $result;
}

/*
 * Function returns array of result expected from previous function
 */
function survey_get_expectedresult(){

  $expectedResult = array( 
  'is_error' => 0,
  'version' => 3,
  'count' => 1,
  'id' => 1,
  'values' => array( 
      '1' => array( 
          'id' => '1',
          'title' => 'survey title',
          'instructions' => 'Call people, ask for money',
          'max_number_of_contacts' => '12',
          'is_active' => '1',
          'is_default' => '',
          'created_date' => '2011-06-02 02:38:30',
        ),
    ),
);

  return $expectedResult  ;
}




/*
* This example has been generated from the API test suite. The test that created it is called
* survey_get 
* You can see the outcome of the API tests at 
* http://tests.dev.civicrm.org/trunk/results-api_v3
* and review the wiki at
* http://wiki.civicrm.org/confluence/display/CRMDOC40/CiviCRM+Public+APIs
* Read more about testing here
* http://wiki.civicrm.org/confluence/display/CRM/Testing
*/