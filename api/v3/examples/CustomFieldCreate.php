<?php



/*
 
 */
function custom_field_create_example(){
$params = array( 
  'custom_group_id' => 4,
  'name' => 'test_date',
  'label' => 'test_date',
  'html_type' => 'Select Date',
  'data_type' => 'Date',
  'default_value' => '20071212',
  'weight' => 4,
  'is_required' => 1,
  'is_searchable' => 0,
  'is_active' => 1,
  'version' => 3,
);

  require_once 'api/api.php';
  $result = civicrm_api( 'custom_field','create',$params );

  return $result;
}

/*
 * Function returns array of result expected from previous function
 */
function custom_field_create_expectedresult(){

  $expectedResult = array( 
  'is_error' => 0,
  'version' => 3,
  'count' => 1,
  'id' => 3,
  'values' => array( 
      '3' => array( 
          'id' => '3',
          'custom_group_id' => '4',
          'name' => 'test_date',
          'label' => 'test_date',
          'data_type' => 'Date',
          'html_type' => 'Select Date',
          'default_value' => '20071212',
          'is_required' => '1',
          'is_searchable' => '',
          'is_search_range' => '',
          'weight' => '4',
          'help_pre' => '',
          'help_post' => '',
          'mask' => '',
          'attributes' => '',
          'javascript' => '',
          'is_active' => '1',
          'is_view' => '',
          'options_per_line' => '',
          'text_length' => '',
          'start_date_years' => '',
          'end_date_years' => '',
          'date_format' => '',
          'time_format' => '',
          'note_columns' => '',
          'note_rows' => '',
          'column_name' => 'test_date_3',
          'option_group_id' => '',
        ),
    ),
);

  return $expectedResult  ;
}




/*
* This example has been generated from the API test suite. The test that created it is called
* custom_field_create 
* You can see the outcome of the API tests at 
* http://tests.dev.civicrm.org/trunk/results-api_v3
* and review the wiki at
* http://wiki.civicrm.org/confluence/display/CRMDOC40/CiviCRM+Public+APIs
* Read more about testing here
* http://wiki.civicrm.org/confluence/display/CRM/Testing
*/