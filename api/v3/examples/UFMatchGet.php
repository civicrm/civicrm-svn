<?php 

function uf_match_get_example(){
    $params = array(
    
                  'contact_id' 		=> '69',
                  'version' 		=> '3',

  );
  require_once 'api/api.php';
  $result = civicrm_api( 'civicrm_uf_match_get','UFMatch',$params );

  return $result;
}

/*
 * Function returns array of result expected from previous function
 */
function uf_match_get_expectedresult(){

  $expectedResult = 
            array(
                  'is_error' 		=> '0',
                  'version' 		=> '3',
                  'count' 		=> '1',
                  'values' 		=>                   array('uf_id' => '42',                        ),

  );

  return $expectedResult  ;
}

