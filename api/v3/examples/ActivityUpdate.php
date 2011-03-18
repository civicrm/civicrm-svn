<?php 

function activity_update_example(){
    $params = array(
    
                  'id' 		=> '1',
                  'source_contact_id' 		=> '17',
                  'subject' 		=> 'Make-it-Happen',
                  'status_id' 		=> '1',
                  'activity_name' 		=> 'Test activity type',
                  'custom_11' 		=> 'Updated my test data',
                  'version' 		=> '3',

  );
  require_once 'api/api.php';
  $result = civicrm_api( 'activity','update',$params );

  return $result;
}

/*
 * Function returns array of result expected from previous function
 */
function activity_update_expectedresult(){

  $expectedResult = 
     array(
           'is_error' 		=> '0',
           'version' 		=> '3',
           'count' 		=> '1',
           'id' 		=> '1',
           'values' 		=> array(           '1' =>  array(
                      'id' => '1',
                      'source_contact_id' => '17',
                      'source_record_id' => '',
                      'activity_type_id' => '1',
                      'subject' => 'Make-it-Happen',
                      'activity_date_time' => '2011-03-16 00:00:00',
                      'duration' => '120',
                      'location' => 'Pensulvania',
                      'phone_id' => '',
                      'phone_number' => '',
                      'details' => 'a test activity to check the update api',
                      'status_id' => '1',
                      'priority_id' => '',
                      'parent_id' => '',
                      'is_test' => '0',
                      'medium_id' => '',
                      'is_auto' => '0',
                      'relationship_id' => '',
                      'is_current_revision' => '1',
                      'original_id' => '',
                      'result' => '',
                      'is_deleted' => '0',
                      'campaign_id' => '',
           ),           ),
      );

  return $expectedResult  ;
}

