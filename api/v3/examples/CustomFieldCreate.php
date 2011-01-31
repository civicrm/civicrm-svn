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
                  'version' 		=> '',

  );
  require_once 'api/api.php';
  $result = civicrm_api( 'civicrm_custom_field_create','Activity',$params );

  return $result;


}



function custom_field_create_expectedresult(){

  $expectedResult = array(
                  'is_error' 		=> '0',
                  'version' 		=> '3',
                  'count' 		=> '1',
                  'values' 		=> array('customFieldId' => '1',
                        )

  );

  return $expectedResult  ;
}

