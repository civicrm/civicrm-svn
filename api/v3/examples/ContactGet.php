<?php

function contact_get_example(){
 $params = 
     array(
           'version' 		=> '3',
           'id' 		=> '1',
           'api.activity' 		=> array(           ),
      );

  require_once 'api/api.php';
  $result = civicrm_api( 'contact','get',$params );

  return $result;
}

/*
 * Function returns array of result expected from previous function
 */
function contact_get_expectedresult(){

  $expectedResult =
     array(
           'is_error' 		=> '0',
           'version' 		=> '3',
           'count' 		=> '1',
           'id' 		=> '1',
           'values' 		=> array(           '1' =>  array(
                      'contact_id' => '1',
           'contact_type' => 'Individual',
           'sort_name' => 'man2@yahoo.com',
           'display_name' => 'man2@yahoo.com',
           'do_not_email' => '0',
           'do_not_phone' => '0',
           'do_not_mail' => '0',
           'do_not_sms' => '0',
           'do_not_trade' => '0',
           'is_opt_out' => '0',
           'preferred_mail_format' => 'Both',
           'is_deceased' => '0',
           'contact_is_deleted' => '0',
           'email_id' => '2',
           'email' => 'man2@yahoo.com',
           'on_hold' => '0',
           'api.activity' =>  array(
                            'is_error' => '0',
                            'version' => '3',
                            'count' => '1',
                            'id' => '1',
                            'values' =>  array(
                                '0' =>  array(
                                            'source_contact_id' => '1',
                                            'activity_type_id' => '6',
                                            'subject' => '$ 100 - SSF',
                                            'location' => '',
                                            'activity_date_time' => '2010-01-01 00:00:00',
                                            'details' => '',
                                            'status_id' => '2',
                                            'activity_name' => 'Contribution',
                                            'status' => 'Completed',
									),
                            ),
        ),
           ),           ),
      );

  return $expectedResult  ;
}

