<?php 

function custom_field_create_example(){
    $params = array(
    
                  'custom_group_id' 		=> '1',
                  'name' 		=> 'test_date',
                  'label' 		=> 'test_date',
                  'html_type' 		=> 'Select Date',
                  'data_type' 		=> 'Date',
                  'default_value' 		=> '20071212',
                  'weight' 		=> '4',
                  'is_required' 		=> '1',
                  'is_searchable' 		=> '0',
                  'is_active' 		=> '1',
                  'version' 		=> '3',

  );
  require_once 'api/api.php';
  $result = civicrm_api( 'custom_field','create',$params );

  return $result;
}

/*
 * Function returns array of result expected from previous function
 */
function custom_field_create_expectedresult(){

  $expectedResult = 
     array(
           'is_error' 		=> '0',
           'version' 		=> '3',
           'count' 		=> '1',
           'id' 		=> '1',
           'values' 		=> array(           '1' =>  array(
                      'id' => '1',
                      'custom_group_id' => '1',
                      'name' => 'test_date',
                      'label' => 'test_date',
                      'data_type' => 'Date',
                      'html_type' => 'Select Date',
                      'default_value' => '20071212',
                      'is_required' => '1',
                      'is_searchable' => '0',
                      'is_search_range' => '0',
                      'weight' => '4',
                      'help_pre' => '',
                      'help_post' => '',
                      'mask' => '',
                      'attributes' => '',
                      'javascript' => '',
                      'is_active' => '1',
                      'is_view' => '0',
                      'options_per_line' => '',
                      'text_length' => '',
                      'start_date_years' => '',
                      'end_date_years' => '',
                      'date_format' => '',
                      'time_format' => '',
                      'note_columns' => '',
                      'note_rows' => '',
                      'column_name' => 'test_date_1',
                      'option_group_id' => '',
           ),           ),
      );

  return $expectedResult  ;
}

