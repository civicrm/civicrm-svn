<?php



/*
 
 */
function campaign_create_example(){
$params = array( 
  'version' => 3,
  'title' => 'campaign title',
  'activity_type_id' => '',
  'max_number_of_contacts' => 12,
  'instructions' => 'Call people, ask for money',
);

  require_once 'api/api.php';
  $result = civicrm_api( 'campaign','create',$params );

  return $result;
}

/*
 * Function returns array of result expected from previous function
 */
function campaign_create_expectedresult(){

  $expectedResult = array( 
  'is_error' => 0,
  'version' => 3,
  'count' => 1,
  'id' => 1,
  'values' => array( 
      '1' => array( 
          'id' => 1,
          'name' => 'campaign_title',
          'title' => 'campaign title',
          'description' => '',
          'start_date' => '',
          'end_date' => '',
          'campaign_type_id' => '',
          'status_id' => '',
          'external_identifier' => '',
          'parent_id' => '',
          'is_active' => '',
          'created_id' => '',
          'created_date' => '20110711194832',
          'last_modified_id' => '',
          'last_modified_date' => '',
          'goal_general' => '',
          'goal_revenue' => '',
        ),
    ),
);

  return $expectedResult  ;
}




/*
* This example has been generated from the API test suite. The test that created it is called
* campaign_create 
* You can see the outcome of the API tests at 
* http://tests.dev.civicrm.org/trunk/results-api_v3
* and review the wiki at
* http://wiki.civicrm.org/confluence/display/CRMDOC40/CiviCRM+Public+APIs
* Read more about testing here
* http://wiki.civicrm.org/confluence/display/CRM/Testing
*/