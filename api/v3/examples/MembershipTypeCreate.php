<?php 

function membership_type_create_example(){
    $params = array(
    
                  'name' 		=> '40+ Membership',
                  'description' 		=> 'people above 40 are given health instructions',
                  'member_of_contact_id' 		=> '1',
                  'contribution_type_id' 		=> '1',
                  'domain_id' 		=> '1',
                  'minimum_fee' 		=> '200',
                  'duration_unit' 		=> 'month',
                  'duration_interval' 		=> '10',
                  'period_type' 		=> 'rolling',
                  'visibility' 		=> 'public',
                  'version' 		=> '3',

  );
  require_once 'api/api.php';
  $result = civicrm_api( 'civicrm_membership_type_create','MembershipType',$params );

  return $result;
}

/*
 * Function returns array of result expected from previous function
 */
function membership_type_create_expectedresult(){

  $expectedResult = 
            array(
                  'is_error' 		=> '0',
                  'version' 		=> '3',
                  'count' 		=> '1',
                  'values' 		=>                   array('1' =>                   array('0' => '1',
                  ),                        ),

  );

  return $expectedResult  ;
}

