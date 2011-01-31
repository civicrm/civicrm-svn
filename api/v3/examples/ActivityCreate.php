<?php 

function activity_create_example(){
    $params = array(
    
                  'source_contact_id' 		=> '17',
                  'subject' 		=> 'Make-it-Happen Meeting',
                  'activity_date_time' 		=> '20110131000000',
                  'duration' 		=> '120',
                  'location' 		=> 'Pensulvania',
                  'details' 		=> 'a test activity',
                  'status_id' 		=> '1',
                  'activity_name' 		=> 'Test activity type',
                  'activity_type_id' 		=> '1',
                  'activity_status_id' 		=> '1',
                  'priority_id' 		=> '',

  );
  require_once 'api/api.php';
  $result = civicrm_api( 'civicrm_activity_create','Activity',$params );

  return $result;
}

/*
 * Function returns array of result expected from previous function
 */
function activity_create_expectedresult(){

  $expectedResult = 
            array(
                  'is_error' 		=> '0',
                  'version' 		=> '3',
                  'count' 		=> '22',
                  'values' 		=>                   array(                  'id' => '1',                                    'source_contact_id' => '17',                                    'source_record_id' => '',                                    'activity_type_id' => '1',                                    'subject' => 'Make-it-Happen Meeting',                                    'activity_date_time' => '20110131000000',                                    'duration' => '120',                                    'location' => 'Pensulvania',                                    'phone_id' => '',                                    'phone_number' => '',                                    'details' => 'a test activity',                                    'status_id' => '1',                                    'priority_id' => '',                                    'parent_id' => '',                                    'is_test' => '',                                    'medium_id' => '',                                    'is_auto' => '',                                    'relationship_id' => '',                                    'is_current_revision' => '',                                    'original_id' => '',                                    'result' => '',                                    'is_deleted' => '',                  ),

  );

  return $expectedResult  ;
}

