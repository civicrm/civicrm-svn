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
           'count' 		=> '6',
           'values' 		=> array(           '1' =>  array(
                      'id' => '1',
                      'title' => 'survey title',
                      'instructions' => 'Call people, ask for money',
                      'max_number_of_contacts' => '12',
                      'is_active' => '1',
                      'is_default' => '0',
                      'created_date' => '2011-04-15 09:31:34',
           ),                      '2' =>  array(
                      'id' => '2',
                      'title' => 'survey title',
                      'instructions' => 'Call people, ask for money',
                      'max_number_of_contacts' => '12',
                      'is_active' => '1',
                      'is_default' => '0',
                      'created_date' => '2011-04-15 09:32:37',
           ),                      '3' =>  array(
                      'id' => '3',
                      'title' => 'survey title',
                      'instructions' => 'Call people, ask for money',
                      'max_number_of_contacts' => '12',
                      'is_active' => '1',
                      'is_default' => '0',
                      'created_date' => '2011-04-15 09:34:16',
           ),                      '4' =>  array(
                      'id' => '4',
                      'title' => 'survey title',
                      'instructions' => 'Call people, ask for money',
                      'max_number_of_contacts' => '12',
                      'is_active' => '1',
                      'is_default' => '0',
                      'created_date' => '2011-04-15 09:35:11',
           ),                      '5' =>  array(
                      'id' => '5',
                      'title' => 'survey title',
                      'instructions' => 'Call people, ask for money',
                      'max_number_of_contacts' => '12',
                      'is_active' => '1',
                      'is_default' => '0',
                      'created_date' => '2011-04-15 10:03:33',
           ),                      '6' =>  array(
                      'id' => '6',
                      'title' => 'survey title',
                      'instructions' => 'Call people, ask for money',
                      'max_number_of_contacts' => '12',
                      'is_active' => '1',
                      'is_default' => '0',
                      'created_date' => '2011-04-15 10:11:50',
           ),           ),
      );

  return $expectedResult  ;
}

