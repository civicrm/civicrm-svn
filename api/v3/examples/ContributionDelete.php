<?php 

function contribution_delete_example(){
    $params = array(
    
                  'contribution_id' 		=> '1',
                  'version' 		=> '3',

  );
  require_once 'api/api.php';
  $result = civicrm_api_legacy( 'civicrm_contribution_delete','Contribution',$params );

  return $result;
}

/*
 * Function returns array of result expected from previous function
 */
function contribution_delete_expectedresult(){

  $expectedResult = 
            array(

  );

  return $expectedResult  ;
}

