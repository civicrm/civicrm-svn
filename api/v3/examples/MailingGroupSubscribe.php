<?php 

function mailing_group_subscribe_example(){
    $params = array(
    
                  'email' 		=> 'test@test.test',
                  'group_id' 		=> '1',
                  'contact_id' 		=> '1',
                  'version' 		=> '3',
                  'hash' 		=> 'b15de8b64e2cec34',
                  'time_stamp' 		=> '20101212121212',

  );
  require_once 'api/api.php';
  $result = civicrm_api( 'mailing_group','subscribe',$params );

  return $result;
}

/*
 * Function returns array of result expected from previous function
 */
function mailing_group_subscribe_expectedresult(){

  $expectedResult = 
     array(
      );

  return $expectedResult  ;
}

