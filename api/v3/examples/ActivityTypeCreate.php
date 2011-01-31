<?php 

function activity_type_create_example(){
    $params = array(
    
                  'weight' 		=> '2',
                  'label' 		=> 'send out letters',
                  'version' 		=> '3',
                  'is_active' 		=> '',
                  'option_group_id' 		=> '2',
                  'value' 		=> '32',
                  'name' 		=> 'send out letters',
                  'is_default' 		=> '',
                  'is_optgroup' 		=> '',
                  'filter' 		=> '',

  );
  require_once 'api/api.php';
  $result = civicrm_api( 'civicrm_activity_type_create','ActivityType',$params );

  return $result;
}

/*
 * Function returns array of result expected from previous function
 */
function activity_type_create_expectedresult(){

  $expectedResult = 
            array(
                  'id' 		=> '554',
                  'option_group_id' 		=> '2',
                  'label' 		=> 'send out letters',
                  'value' 		=> '32',
                  'name' 		=> 'send out letters',
                  'grouping' 		=> '',
                  'filter' 		=> '',
                  'is_default' 		=> '',
                  'weight' 		=> '2',
                  'description' 		=> '',
                  'is_optgroup' 		=> '',
                  'is_reserved' 		=> '',
                  'is_active' 		=> '',
                  'component_id' 		=> '',
                  'domain_id' 		=> '',
                  'visibility_id' 		=> '',

  );

  return $expectedResult  ;
}

