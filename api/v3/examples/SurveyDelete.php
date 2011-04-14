<?php 

function survey_delete_example(){
    $params = array(
    
                  'version' 		=> '3',
                  'title' 		=> 'survey title',
                  'activity_type_id' 		=> '',
                  'max_number_of_contacts' 		=> '12',
                  'instructions' 		=> 'Call people, ask for money',

  );
  require_once 'api/api.php';
  $result = civicrm_api( 'survey','delete',$params );

  return $result;
}

/*
 * Function returns array of result expected from previous function
 */
function survey_delete_expectedresult(){

  $expectedResult = 
     array(
           'is_error' 		=> '1',
           'error_message' 		=> 'Mandatory key(s) missing from params array: id',
      );

  return $expectedResult  ;
}

