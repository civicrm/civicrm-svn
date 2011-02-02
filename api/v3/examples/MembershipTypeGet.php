<?php 

function membership_type_get_example(){
    $params = array(
    
                  'id' 		=> '1',
                  'version' 		=> '3',

  );
  require_once 'api/api.php';
  $result = civicrm_api( 'civicrm_membership_type_get','MembershipType',$params );

  return $result;
}

/*
 * Function returns array of result expected from previous function
 */
function membership_type_get_expectedresult(){

  $expectedResult = 
            array(
                  'is_error' 		=> '0',
                  'version' 		=> '3',
                  'count' 		=> '1',
                  'id' 		=> '1',
                  'values' 		=>                   array(                  '1' =>  array(
                                    'id' => '1'
                  ,                  'domain_id' => '1'
                  ,                  'name' => 'General'
                  ,                  'description' => ''
                  ,                  'member_of_contact_id' => '1'
                  ,                  'contribution_type_id' => '1'
                  ,                  'minimum_fee' => '0.00'
                  ,                  'duration_unit' => 'year'
                  ,                  'duration_interval' => '1'
                  ,                  'period_type' => 'rolling'
                  ,                  'fixed_period_start_day' => ''
                  ,                  'fixed_period_rollover_day' => ''
                  ,                  'relationship_type_id' => ''
                  ,                  'relationship_direction' => ''
                  ,                  'visibility' => '1'
                  ,                  'weight' => ''
                  ,                  'renewal_msg_id' => ''
                  ,                  'renewal_reminder_day' => ''
                  ,                  'receipt_text_signup' => ''
                  ,                  'receipt_text_renewal' => ''
                  ,                  'is_active' => '1'
                  ,),                  ),

  );

  return $expectedResult  ;
}

