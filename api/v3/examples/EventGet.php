<?php



/*
 
 */
function event_get_example(){
$params = array( 
  'title' => 'Annual CiviCRM meet',
  'version' => 3,
);

  require_once 'api/api.php';
  $result = civicrm_api( 'event','get',$params );

  return $result;
}

/*
 * Function returns array of result expected from previous function
 */
function event_get_expectedresult(){

  $expectedResult = array( 
  'is_error' => 0,
  'version' => 3,
  'count' => 1,
  'id' => 4,
  'values' => array( 
      '4' => array( 
          'id' => '4',
          'title' => 'Annual CiviCRM meet',
          'event_title' => 'Annual CiviCRM meet',
          'event_type_id' => '1',
          'participant_listing_id' => '',
          'is_public' => '1',
          'start_date' => '2008-10-21 00:00:00',
          'event_start_date' => '2008-10-21 00:00:00',
          'is_online_registration' => '',
          'is_monetary' => '',
          'contribution_type_id' => '',
          'is_map' => '',
          'is_active' => '',
          'is_show_location' => '1',
          'default_role_id' => '1',
          'is_email_confirm' => '',
          'is_pay_later' => '',
          'is_multiple_registrations' => '',
          'allow_same_participant_emails' => '',
          'is_template' => '',
        ),
    ),
);

  return $expectedResult  ;
}




/*
* This example has been generated from the API test suite. The test that created it is called
* event_get 
* You can see the outcome of the API tests at 
* http://tests.dev.civicrm.org/trunk/results-api_v3
* and review the wiki at
* http://wiki.civicrm.org/confluence/display/CRMDOC40/CiviCRM+Public+APIs
* Read more about testing here
* http://wiki.civicrm.org/confluence/display/CRM/Testing
*/