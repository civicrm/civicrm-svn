<?php

    function test_api_v3_UF_match_get( )
    {
        $params   = array('uf_id' => 42,
                          'version' => 3);
        $result = civicrm_api( 'civicrm_UF_match_get','UFMatch',$params );
        return $result;
    }
    
    function test_api_v3_UF_match_get_expectedresult(){
      
      $expectedResult = array(
                    'is_error'           => 0,
                    'count' => 1,
      							'version' => 3,
                    'values' => Array  ('contact_id' => 69),
                     );

        return $expectedResult  ;
    }
    