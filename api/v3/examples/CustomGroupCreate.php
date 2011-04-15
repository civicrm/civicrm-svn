<?php 

function custom_group_create_example(){
    $params = array(
    
                  'title' 		=> 'Test_Group_1',
                  'name' 		=> 'test_group_1',
                  'extends' 		=> 'Array',
                  'weight' 		=> '4',
                  'collapse_display' 		=> '1',
                  'style' 		=> 'Inline',
                  'help_pre' 		=> 'This is Pre Help For Test Group 1',
                  'help_post' 		=> 'This is Post Help For Test Group 1',
                  'is_active' 		=> '1',
                  'version' 		=> '3',

  );
  require_once 'api/api.php';
  $result = civicrm_api( 'custom_group','create',$params );

  return $result;
}

/*
 * Function returns array of result expected from previous function
 */
function custom_group_create_expectedresult(){

  $expectedResult = 
     array(
           'is_error' 		=> '0',
           'version' 		=> '3',
           'count' 		=> '1',
           'id' 		=> '1',
           'values' 		=> array(           '1' =>  array(
                      'id' => '1',
                      'name' => 'Test_Group_1',
                      'title' => 'Test_Group_1',
                      'extends' => 'Individual',
                      'extends_entity_column_id' => 'null',
                      'extends_entity_column_value' => 'null',
                      'style' => 'Inline',
                      'collapse_display' => '1',
                      'help_pre' => 'This is Pre Help For Test Group 1',
                      'help_post' => 'This is Post Help For Test Group 1',
                      'weight' => '2',
                      'is_active' => '1',
                      'table_name' => 'civicrm_value_test_group_1_1',
                      'is_multiple' => '',
                      'min_multiple' => '',
                      'max_multiple' => 'null',
                      'collapse_adv_display' => '',
                      'created_id' => '',
                      'created_date' => '',
           ),           ),
      );

  return $expectedResult  ;
}

