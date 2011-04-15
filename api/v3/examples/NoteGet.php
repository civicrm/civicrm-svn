<?php 

function note_get_example(){
    $params = array(
    
                  'entity_table' 		=> 'civicrm_contact',
                  'entity_id' 		=> '1',
                  'note' 		=> 'Hello!!! m testing Note',
                  'contact_id' 		=> '1',
                  'modified_date' 		=> '2011-01-31',
                  'subject' 		=> 'Test Note',
                  'version' 		=> '3',

  );
  require_once 'api/api.php';
  $result = civicrm_api( 'note','get',$params );

  return $result;
}

/*
 * Function returns array of result expected from previous function
 */
function note_get_expectedresult(){

  $expectedResult = 
     array(
           'is_error' 		=> '0',
           'version' 		=> '3',
           'count' 		=> '1',
           'id' 		=> '1',
           'values' 		=> array(           '1' =>  array(
                      'id' => '1',
                      'entity_table' => 'civicrm_contact',
                      'entity_id' => '1',
                      'note' => 'hello I am testing Note',
                      'contact_id' => '1',
                      'modified_date' => '2011-03-16',
                      'subject' => 'Test Note',
                      'privacy' => '0',
           ),           ),
      );

  return $expectedResult  ;
}

