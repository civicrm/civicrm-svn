<?php 

function domain_create_example(){
    $params = array(
    
                  'name' 		=> 'New Domain',
                  'description' 		=> 'Description of a new domain',
                  'version' 		=> '3',

  );
  require_once 'api/api.php';
  $result = civicrm_api( 'civicrm_domain_create','Domain',$params );

  return $result;
}

/*
 * Function returns array of result expected from previous function
 */
function domain_create_expectedresult(){

  $expectedResult = 
            array(
                  'is_error' 		=> '0',
                  'version' 		=> '3',
                  'count' 		=> '8',
                  'values' 		=>                   array('id' => '2',                        'name' => 'New Domain',                        'description' => 'Description of a new domain',                        'config_backend' => '',                        'version' => '3',                        'loc_block_id' => '',                        'locales' => '',                        'locale_custom_strings' => '',                        ),

  );

  return $expectedResult  ;
}

