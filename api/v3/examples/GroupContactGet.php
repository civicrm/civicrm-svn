<?php 

function group_contact_get_example(){
    $params = array(
    
                  'contact_id' 		=> '1',
                  'version' 		=> '3',

  );
  require_once 'api/api.php';
  $result = civicrm_api( 'group_contact','get',$params );

  return $result;
}

/*
 * Function returns array of result expected from previous function
 */
function group_contact_get_expectedresult(){

  $expectedResult = 
     array(
           'is_error' 		=> '0',
           'version' 		=> '3',
           'count' 		=> '1',
           'id' 		=> '1',
           'values' 		=> array(           '1' =>  array(
                      'id' => '1',
                      'group_id' => '1',
                      'title' => 'New Test Group Created',
                      'visibility' => 'Public Pages',
                      'in_date' => '2011-03-16 21:12:31',
                      'in_method' => 'API',
           ),           ),
      );

  return $expectedResult  ;
}

