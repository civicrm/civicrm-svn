<?php 

function tag_create_example(){
    $params = array(
    
                  'name' 		=> 'New Tag3',
                  'description' 		=> 'This is description for New Tag 02',
                  'version' 		=> '3',
                  'used_for' 		=> 'civicrm_contact',

  );
  require_once 'api/api.php';
  $result = civicrm_api_legacy( 'civicrm_tag_create','Tag',$params );

  return $result;
}

/*
 * Function returns array of result expected from previous function
 */
function tag_create_expectedresult(){

  $expectedResult = 
            array(
                  'is_error' 		=> '0',
                  'version' 		=> '3',
                  'count' 		=> '1',
                  'id' 		=> '6',
                  'values' 		=>                   array(                  '6' =>  array(
                                    'id' => '6'
                  ,                  'name' => 'New Tag3'
                  ,                  'description' => 'This is description for New Tag 02'
                  ,                  'parent_id' => ''
                  ,                  'is_selectable' => ''
                  ,                  'is_reserved' => ''
                  ,                  'is_tagset' => ''
                  ,                  'used_for' => 'civicrm_contact'
                  ,),                  ),

  );

  return $expectedResult  ;
}

