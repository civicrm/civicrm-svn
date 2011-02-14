<?php 

function tag_get_example(){
    $params = array(
    
                  'id' 		=> '6',
                  'name' 		=> '',
                  'version' 		=> '3',

  );
  require_once 'api/api.php';
  $result = civicrm_api_legacy( 'civicrm_tag_get','Tag',$params );

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
                  'count' 		=> '0',
                  'values' 		=>                   array(),

  );

  return $expectedResult  ;
}

