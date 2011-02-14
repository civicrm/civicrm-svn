<?php 

function group_contact__example(){
    $params = array(
    
                  'contact_id.1' 		=> '1',
                  'group_id' 		=> '1',
                  'version' 		=> '3',

  );
  require_once 'api/api.php';
  $result = civicrm_api_legacy( 'civicrm_group_contact_','GroupContact',$params );

  return $result;
}

/*
 * Function returns array of result expected from previous function
 */
function group_contact__expectedresult(){

  $expectedResult = 
            array(
                  'is_error' 		=> '0',
                  'not_removed' 		=> '1',
                  'removed' 		=> '0',
                  'total_count' 		=> '1',

  );

  return $expectedResult  ;
}

