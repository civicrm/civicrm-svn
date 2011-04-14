<?php 

function survey_get_example(){
    $params = array(
    
                  'version' 		=> '3',
                  'title' 		=> 'survey title',
                  'activity_type_id' 		=> '',
                  'max_number_of_contacts' 		=> '12',
                  'instructions' 		=> 'Call people, ask for money',

  );
  require_once 'api/api.php';
  $result = civicrm_api( 'survey','get',$params );

  return $result;
}

/*
 * Function returns array of result expected from previous function
 */
function survey_get_expectedresult(){

  $expectedResult = 
     array(
           'is_error' 		=> '0',
           'version' 		=> '3',
           'count' 		=> '1',
           'id' 		=> '5',
           'values' 		=> array(           '5' =>  array(
                      'id' => '5',
                      'title' => 'survey title',
                      'instructions' => 'Call people, ask for money',
                      'max_number_of_contacts' => '12',
                      'is_active' => '1',
                      'is_default' => '0',
                      'created_date' => '2011-04-14 23:20:14',
           ),           ),
      );

  return $expectedResult  ;
}

