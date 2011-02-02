<?php 

function group_organization_get_example(){
    $params = array(
    
                  'organization_id' 		=> '1',
                  'group_id' 		=> '1',
                  'version' 		=> '3',

  );
  require_once 'api/api.php';
  $result = civicrm_api( 'civicrm_group_organization_get','GroupOrganization',$params );

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
                  'id' 		=> '1',
                  'values' 		=>                   array(                  'id' => '1',                                    'group_id' => '1',                                    'organization_id' => '1',                  ),

  );

  return $expectedResult  ;
}

