<?php 

function activity_get_example(){
    $params = array(
    
                  'activity_id' 		=> '4',
                  'activity_type_id' 		=> '5',
                  'version' 		=> '3',
                  'sequential' 		=> '1',

  );
  require_once 'api/api.php';
  $result = civicrm_api( 'activity','get',$params );

  return $result;
}

/*
 * Function returns array of result expected from previous function
 */
function activity_get_expectedresult(){

  $expectedResult = 
     array(
           'is_error' 		=> '0',
           'version' 		=> '3',
           'count' 		=> '1',
           'id' 		=> '4',
           'values' 		=> array(           '0' =>  array(
                      'id' => '4',
                      'source_contact_id' => '17',
                      'source_record_id' => '',
                      'activity_type_id' => '1',
                      'subject' => 'test activity type id',
                      'activity_date_time' => '2009-01-23 12:34:56',
                      'duration' => '',
                      'location' => '',
                      'phone_id' => '',
                      'phone_number' => '',
                      'details' => '',
                      'status_id' => '1',
                      'priority_id' => '1',
                      'parent_id' => '',
                      'is_test' => '',
                      'medium_id' => '',
                      'is_auto' => '',
                      'relationship_id' => '',
                      'is_current_revision' => '',
                      'original_id' => '',
                      'result' => '',
                      'is_deleted' => '',
                      'campaign_id' => '',
           ),           ),
      );

  return $expectedResult  ;
}

