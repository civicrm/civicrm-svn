<?php 

function survey_create_example(){
    $params = array(
    
                  'version' 		=> '3',
                  'title' 		=> 'survey title',
                  'activity_type_id' 		=> '',
                  'max_number_of_contacts' 		=> '12',
                  'instructions' 		=> 'Call people, ask for money',

  );
  require_once 'api/api.php';
  $result = civicrm_api( 'survey','create',$params );

  return $result;
}

/*
 * Function returns array of result expected from previous function
 */
function survey_create_expectedresult(){

  $expectedResult = 
     array(
           'is_error' 		=> '0',
           'version' 		=> '3',
           'count' 		=> '1',
           'id' 		=> '6',
           'values' 		=> array(           '6' =>  array(
                      'id' => '6',
                      'title' => 'survey title',
                      'campaign_id' => '',
                      'activity_type_id' => '',
                      'recontact_interval' => '',
                      'instructions' => 'Call people, ask for money',
                      'release_frequency' => '',
                      'max_number_of_contacts' => '12',
                      'default_number_of_contacts' => '',
                      'is_active' => '',
                      'is_default' => '',
                      'created_id' => '',
                      'created_date' => '20110415101150',
                      'last_modified_id' => '',
                      'last_modified_date' => '',
                      'result_id' => '',
           ),           ),
      );

  return $expectedResult  ;
}

