<?php 

function tag_get_example(){
    $params = array(
    
                  'id' 		=> '6',
                  'name' 		=> 'New Tag326991',
                  'version' 		=> '3',

  );
  require_once 'api/api.php';
  $result = civicrm_api( 'Tag','get',$params );

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
                  'id' 		=> '6',
                  'values' 		=>                   array(                  '6' =>  array(
                                    'id' => '6'
                  ,                  'name' => 'New Tag326991'
                  ,                  'description' => 'This is description for New Tag 26236'
                  ,                  'parent_id' => ''
                  ,                  'is_selectable' => '1'
                  ,                  'is_reserved' => '0'
                  ,                  'is_tagset' => '0'
                  ,                  'used_for' => 'civicrm_contact'
                  ,                  'created_id' => ''
                  ,                  'created_date' => '2011-02-09 13:38:06'
                  ,),                  ),

  );

  return $expectedResult  ;
}

