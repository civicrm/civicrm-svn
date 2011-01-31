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
  $result = civicrm_api( 'civicrm_uf_group_create','UFGroup',$params );

  return $result;
}

/*
 * Function returns array of result expected from previous function
 */
function uf_group_create_expectedresult(){

  $expectedResult = 
            array(
                  'is_error' 		=> '1',
                  'error_message' 		=> 'Mandatory key(s) missing from params array: id',

  );

  return $expectedResult  ;
}

