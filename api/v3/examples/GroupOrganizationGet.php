<?php 

function group_organization_get_example(){
    $params = array(
    
                  'organization_id' 		=> '1',
                  'version' 		=> '3',

  );
  require_once 'api/api.php';
  $result = civicrm_api( 'group_organization','get',$params );

  return $result;
}

/*
 * Function returns array of result expected from previous function
 */
function group_organization_get_expectedresult(){

  $expectedResult = 
     array(
           'is_error' 		=> '0',
           'version' 		=> '3',
           'count' 		=> '3',
           'values' 		=> array(           'id' => '',                      'group_id' => '',                      'organization_id' => '1',           ),
      );

  return $expectedResult  ;
}

