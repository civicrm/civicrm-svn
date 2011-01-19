<?php

    function test_api_v3_activity_create( )
    {
        $params = array(
                        'source_contact_id'   => 17,
                        'subject'             => 'Make-it-Happen Meeting',
                        'activity_date_time'  => date('Ymd'),
                        'duration'            => 120,
                        'location'            => 'Pensulvania',
                        'details'             => 'a test activity',
                        'status_id'           => 1,
                        'activity_name'       => 'Test activity type',
                        'version'							=>3,
                        );
        $result = civicrm_api( 'civicrm_activity_create','Activity',$params );

        return $result;
    }
    
    function test_api_v3_activity_create_expectedresult(){
      
      $expectedResult = array(
        						'is_error'           => 0,
                    'id'      		       =>1,
                    'source_contact_id'	 =>17,
             				'source_record_id'   => null,
    								'activity_type_id'   => 1,
    								'subject'            => 'Make-it-Happen Meeting',
                    'activity_date_time' => '20110115000000',
    								'duration'           => 120,
    								'location'           => 'Pensulvania',
                    'phone_id'           => null,
                    'phone_number'       =>null,
                    'details'            => 'a test activity',
                    'status_id'          => 1,
                    'priority_id'        => null,
                    'parent_id'          =>null,
                    'is_test'            =>null,
                    'medium_id'          =>null,
                    'is_auto'            =>null,
                    'relationship_id'    =>null,
                    'is_current_revision'=>null,
                    'original_id'        =>null,
                    'result'             =>null,
                    'is_deleted'         =>null,

        );

        return $expectedResult  ;
    }
    ?>