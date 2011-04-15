<?php 

function contact_create_example(){
    $params = array(
    
                  'first_name' 		=> 'abc4',
                  'last_name' 		=> 'xyz4',
                  'email' 		=> 'man4@yahoo.com',
                  'contact_type' 		=> 'Individual',
                  'location_type_id' 		=> '1',
                  'version' 		=> '3',

  );
  require_once 'api/api.php';
  $result = civicrm_api( 'contact','create',$params );

  return $result;
}

/*
 * Function returns array of result expected from previous function
 */
function contact_create_expectedresult(){

  $expectedResult = 
     array(
           'is_error' 		=> '0',
           'version' 		=> '3',
           'count' 		=> '1',
           'id' 		=> '1',
           'values' 		=> array(           '1' =>  array(
                      'id' => '1',
                      'contact_type' => 'Individual',
                      'contact_sub_type' => '',
                      'do_not_email' => '',
                      'do_not_phone' => '',
                      'do_not_mail' => '',
                      'do_not_sms' => '',
                      'do_not_trade' => '',
                      'is_opt_out' => '',
                      'legal_identifier' => '',
                      'external_identifier' => '',
                      'sort_name' => 'xyz4, abc4',
                      'display_name' => 'abc4 xyz4',
                      'nick_name' => '',
                      'legal_name' => '',
                      'image_URL' => '',
                      'preferred_communication_method' => '',
                      'preferred_language' => 'en_US',
                      'preferred_mail_format' => '',
                      'hash' => '4a39b45d2f47f8003010be470e95dac2',
                      'api_key' => '',
                      'first_name' => 'abc4',
                      'middle_name' => '',
                      'last_name' => 'xyz4',
                      'prefix_id' => '',
                      'suffix_id' => '',
                      'email_greeting_id' => '',
                      'email_greeting_custom' => '',
                      'email_greeting_display' => '',
                      'postal_greeting_id' => '',
                      'postal_greeting_custom' => '',
                      'postal_greeting_display' => '',
                      'addressee_id' => '',
                      'addressee_custom' => '',
                      'addressee_display' => '',
                      'job_title' => '',
                      'gender_id' => '',
                      'birth_date' => '',
                      'is_deceased' => '',
                      'deceased_date' => '',
                      'household_name' => '',
                      'primary_contact_id' => '',
                      'organization_name' => '',
                      'sic_code' => '',
                      'user_unique_id' => '',
           ),           ),
      );

  return $expectedResult  ;
}

