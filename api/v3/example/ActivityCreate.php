<?php
    function testActivityCreate( )
    {
        $params = array(
                        'source_contact_id'   => 17,
                        'subject'             => 'Make-it-Happen Meeting',
                        'activity_date_time'  => date('Ymd'),
                        'duration'            => 120,
                        'location'            => 'Pensulvania',
                        'details'             => 'a test activity',
                        'status_id'           => 1,
                        'activity_name'       => 'Test activity type'
                        );
        
        $result = & civicrm_activity_create( $params );
    }
    ?>