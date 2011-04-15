<?php 

function domain_create_example(){
    $params = array(
    
                  'name' 		=> 'A-team domain',
                  'description' 		=> 'domain of chaos',
                  'version' 		=> '3',
                  'domain_version' 		=> '3.4.1',

  );
  require_once 'api/api.php';
  $result = civicrm_api( 'domain','create',$params );

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
           'count' 		=> '1',
           'id' 		=> '2',
           'values' 		=> array(           '2' =>  array(
                      'id' => '2',
                      'name' => 'A-team domain',
                      'description' => 'domain of chaos',
                      'config_backend' => '',
                      'version' => '',
                      'loc_block_id' => '',
                      'locales' => '',
                      'locale_custom_strings' => '',
           ),           ),
      );

  return $expectedResult  ;
}

