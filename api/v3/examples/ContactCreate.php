<?php

function contact_create_example(){
 $params = 
	array(
           'first_name' 		=> 'abc3',
           'last_name' 		=> 'xyz3',
           'contact_type' 		=> 'Individual',
           'email' 		=> 'man3@yahoo.com',
           'version' 		=> '3',
           'api.contribution.create' 		=> array(           'receive_date' => '2010-01-01',
                      'total_amount' => '100',
                      'contribution_type_id' => '1',
                      'payment_instrument_id' => '1',
                      'non_deductible_amount' => '10',
                      'fee_amount' => '50',
                      'net_amount' => '90',
                      'trxn_id' => '15345',
                      'invoice_id' => '67990',
                      'source' => 'SSF',
                      'contribution_status_id' => '1',
           ),           'api.website.create' 		=> array(           'url' => 'http://civicrm.org',
           ),           'api.website.create.2' 		=> array(           'url' => 'http://chained.org',
           ),
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
                      'id' => '1',                      'contact_type' => 'Individual',                      'contact_sub_type' => '',                      'do_not_email' => '',                      'do_not_phone' => '',                      'do_not_mail' => '',                      'do_not_sms' => '',                      'do_not_trade' => '',                      'is_opt_out' => '',                      'legal_identifier' => '',                      'external_identifier' => '',                      'sort_name' => 'xyz3, abc3',                      'display_name' => 'abc3 xyz3',                      'nick_name' => '',                      'legal_name' => '',                      'image_URL' => '',                      'preferred_communication_method' => '',                      'preferred_language' => 'en_US',                      'preferred_mail_format' => '',                      'hash' => 'a2a7dfb2fafaf5c3909fbbb1e1243551',                      'api_key' => '',                      'first_name' => 'abc3',                      'middle_name' => '',                      'last_name' => 'xyz3',                      'prefix_id' => '',                      'suffix_id' => '',                      'email_greeting_id' => '',                      'email_greeting_custom' => '',                      'email_greeting_display' => '',                      'postal_greeting_id' => '',                      'postal_greeting_custom' => '',                      'postal_greeting_display' => '',                      'addressee_id' => '',                      'addressee_custom' => '',                      'addressee_display' => '',                      'job_title' => '',                      'gender_id' => '',                      'birth_date' => '',                      'is_deceased' => '',                      'deceased_date' => '',                      'household_name' => '',                      'primary_contact_id' => '',                      'organization_name' => '',                      'sic_code' => '',                      'user_unique_id' => '',                      'api.contribution.create' =>  array(
                      'is_error' => '0',
                      'version' => '3',
                      'count' => '1',
                      'id' => '1',
                      'values' => 'Array',
           ),                      'api.website.create' =>  array(
                      'is_error' => '0',
                      'version' => '3',
                      'count' => '1',
                      'id' => '1',
                      'values' => 'Array',
           ),                      'api.website.create.2' =>  array(
                      'is_error' => '0',
                      'version' => '3',
                      'count' => '1',
                      'id' => '2',
                      'values' => 'Array',
           ),           ),           ),
      );

  return $expectedResult  ;
}

