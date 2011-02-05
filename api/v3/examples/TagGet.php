<?php 

function tag_get_example(){
    $params = array(
    
                  'id' 		=> '6',
                  'name' 		=> 'New Tag326644',
                  'version' 		=> '3',

  );
  require_once 'api/api.php';
  $result = civicrm_api( 'civicrm_tag_get','Tag',$params );

  return $result;
}

/*
 * Function returns array of result expected from previous function
 */
function tag_get_expectedresult(){

  $expectedResult = 
            array(
                  'is_error' 		=> '0',
                  'version' 		=> '3',
                  'count' 		=> '1',
                  'id' 		=> '0',
                  'values' 		=>                   array(                  '0' =>  array(
                                    'id' => '6'
                  ,                  'name' => 'New Tag326644'
                  ,                  'description' => 'This is description for New Tag 6333'
                  ,                  'parent_id' => ''
                  ,                  'is_selectable' => '1'
                  ,                  'is_reserved' => '0'
                  ,                  'is_tagset' => '0'
                  ,                  'used_for' => 'civicrm_contact'
                  ,),                  ),

  );

  return $expectedResult  ;
}

