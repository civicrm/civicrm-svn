<?php 

function activity_type_create_example(){
    $params = array(
    
                  'weight' 		=> '2',
                  'label' 		=> 'send out letters',
                  'version' 		=> '3',
                  'filter' 		=> '0',
                  'is_active' 		=> '1',
                  'is_optgroup' 		=> '1',
                  'is_default' 		=> '0',
                  'option_group_id' 		=> '2',
                  'value' 		=> '33',
                  'name' 		=> 'send out letters',

  );
  require_once 'api/api.php';
  $result = civicrm_api( 'activity_type','create',$params );

  return $result;
}

/*
 * Function returns array of result expected from previous function
 */
function activity_type_create_expectedresult(){

  $expectedResult = 
     array(
           'is_error' 		=> '0',
           'version' 		=> '3',
           'count' 		=> '1',
           'id' 		=> '562',
           'values' 		=> array(           '562' =>  array(
                      'id' => '562',
                      'option_group_id' => '2',
                      'label' => 'send out letters',
                      'value' => '33',
                      'name' => 'send out letters',
                      'grouping' => '',
                      'filter' => '0',
                      'is_default' => '0',
                      'weight' => '2',
                      'description' => '',
                      'is_optgroup' => '1',
                      'is_reserved' => '',
                      'is_active' => '1',
                      'component_id' => '',
                      'domain_id' => '',
                      'visibility_id' => '',
           ),           ),
      );

  return $expectedResult  ;
}

