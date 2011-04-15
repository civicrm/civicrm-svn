<?php 

function membership_get_example(){
    $params = array(
    
                  'contact_id' 		=> '1',
                  'version' 		=> '3',

  );
  require_once 'api/api.php';
  $result = civicrm_api( 'membership','get',$params );

  return $result;
}

/*
 * Function returns array of result expected from previous function
 */
function membership_get_expectedresult(){

  $expectedResult = 
     array(
           'id' 		=> '1',
           'membership_id' 		=> '1',
           'contact_id' 		=> '1',
           'membership_contact_id' 		=> '1',
           'membership_type_id' 		=> '1',
           'join_date' 		=> '2009-01-21',
           'start_date' 		=> '2009-01-21',
           'membership_start_date' 		=> '2009-01-21',
           'end_date' 		=> '2009-12-21',
           'membership_end_date' 		=> '2009-12-21',
           'source' 		=> 'Payment',
           'membership_source' 		=> 'Payment',
           'status_id' 		=> '8',
           'is_override' 		=> '1',
           'is_test' 		=> '0',
           'member_is_test' 		=> '0',
           'is_pay_later' 		=> '0',
           'member_is_pay_later' 		=> '0',
           'membership_name' 		=> 'General',
           'relationship_name' 		=> 'Child of',
      );

  return $expectedResult  ;
}

