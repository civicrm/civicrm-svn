<?php 

function contact_get_example(){
    $params = array(
    
                  'email' 		=> 'man2@yahoo.com',
                  'version' 		=> '3',

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
           ),           ),
      );

  return $expectedResult  ;
}

