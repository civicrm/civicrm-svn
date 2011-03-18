<?php 

function uf_group_create_example(){
    $params = array(
    
                  'add_captcha' 		=> '1',
                  'add_contact_to_group' 		=> '2',
                  'cancel_URL' 		=> 'http://example.org/cancel',
                  'created_date' 		=> '2009-06-27',
                  'created_id' 		=> '69',
                  'group' 		=> '2',
                  'group_type' 		=> 'Individual,Contact',
                  'help_post' 		=> 'help post',
                  'help_pre' 		=> 'help pre',
                  'is_active' 		=> '0',
                  'is_cms_user' 		=> '1',
                  'is_edit_link' 		=> '1',
                  'is_map' 		=> '1',
                  'is_reserved' 		=> '1',
                  'is_uf_link' 		=> '1',
                  'is_update_dupe' 		=> '1',
                  'name' 		=> 'Test_Group',
                  'notify' 		=> 'admin@example.org',
                  'post_URL' 		=> 'http://example.org/post',
                  'title' 		=> 'Test Group',
                  'version' 		=> '3',

  );
  require_once 'api/api.php';
  $result = civicrm_api( 'uf_group','create',$params );

  return $result;
}

/*
 * Function returns array of result expected from previous function
 */
function uf_group_create_expectedresult(){

  $expectedResult = 
     array(
           'is_error' 		=> '0',
           'version' 		=> '3',
           'count' 		=> '1',
           'id' 		=> '12',
           'values' 		=> array(           '12' =>  array(
                      'id' => '12',
                      'is_active' => '0',
                      'group_type' => 'Individual,Contact',
                      'title' => 'Test Group',
                      'help_pre' => 'help pre',
                      'help_post' => 'help post',
                      'limit_listings_group_id' => '2',
                      'post_URL' => 'http://example.org/post',
                      'add_to_group_id' => '2',
                      'add_captcha' => '1',
                      'is_map' => '1',
                      'is_edit_link' => '1',
                      'is_uf_link' => '1',
                      'is_update_dupe' => '1',
                      'cancel_URL' => 'http://example.org/cancel',
                      'is_cms_user' => '1',
                      'notify' => 'admin@example.org',
                      'is_reserved' => '1',
                      'name' => 'Test_Group',
                      'created_id' => '69',
                      'created_date' => '2009-06-27',
                      'is_proximity_search' => '',
           ),           ),
      );

  return $expectedResult  ;
}

