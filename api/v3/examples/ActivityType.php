<?php 

function civicrm_activity_type__example(){
    $params = array(
    
                  'weight' 		=> '2',
                  'version' 		=> '',

  );
  require_once 'api/api.php';
  $result = civicrm_api( 'civicrm_civicrm_activity_type_','Activity',$params );

  return $result;


}



function civicrm_activity_type__expectedresult(){

  $expectedResult = array(
                  'is_error' 		=> '1',
                  'error_message' 		=> 'Required parameter "label / weight" not found',

  );

  return $expectedResult  ;
}

