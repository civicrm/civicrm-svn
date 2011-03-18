<?php 

function participant_get_example(){
    $params = array(
    
                  'id' 		=> '1',
                  'version' 		=> '3',

  );
  require_once 'api/api.php';
  $result = civicrm_api( 'participant','get',$params );

  return $result;
}

/*
 * Function returns array of result expected from previous function
 */
function participant_get_expectedresult(){

  $expectedResult = 
     array(
           'is_error' 		=> '0',
           'version' 		=> '3',
           'count' 		=> '1',
           'id' 		=> '1',
           'values' 		=> array(           '1' =>  array(
                      'contact_id' => '2',
                      'contact_type' => 'Individual',
                      'sort_name' => 'Anderson, Anthony',
                      'display_name' => 'Mr. Anthony Anderson II',
                      'event_id' => '1',
                      'event_title' => 'Annual CiviCRM meet',
                      'event_start_date' => '2008-10-21 00:00:00',
                      'event_end_date' => '2008-10-23 00:00:00',
                      'participant_id' => '1',
                      'event_type' => 'Conference',
                      'participant_status_id' => '1',
                      'participant_status' => 'Registered',
                      'participant_role_id' => '1',
                      'participant_register_date' => '2007-02-19 00:00:00',
                      'participant_source' => 'Wimbeldon',
                      'participant_is_pay_later' => '0',
                      'participant_is_test' => '0',
           ),           ),
      );

  return $expectedResult  ;
}

