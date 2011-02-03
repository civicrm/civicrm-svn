<?php 

function note_create_example(){
    $params = array(
    
                  'entity_table' 		=> 'civicrm_contact',
                  'entity_id' 		=> '1',
                  'note' 		=> 'Hello!!! m testing Note',
                  'contact_id' 		=> '1',
                  'modified_date' 		=> '20110203',
                  'subject' 		=> 'Test Note',
                  'version' 		=> '3',
                  'privacy' 		=> '0',

  );
  require_once 'api/api.php';
  $result = civicrm_api( 'civicrm_note_create','Note',$params );

  return $result;
}

/*
 * Function returns array of result expected from previous function
 */
function note_create_expectedresult(){

  $expectedResult = 
            array(
                  'is_error' 		=> '0',
                  'version' 		=> '3',
                  'count' 		=> '8',
                  'id' 		=> '2',
                  'values' 		=>                   array(                  'id' => '2',                                    'entity_table' => 'civicrm_contact',                                    'entity_id' => '1',                                    'note' => 'Hello!!! m testing Note',                                    'contact_id' => '1',                                    'modified_date' => '20110203',                                    'subject' => 'Test Note',                                    'privacy' => '0',                  ),

  );

  return $expectedResult  ;
}

