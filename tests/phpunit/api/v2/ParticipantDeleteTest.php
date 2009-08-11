<?php

require_once 'api/v2/Participant.php';
require_once 'CiviTest/CiviUnitTestCase.php';

class api_v2_ParticipantDeleteTest extends CiviUnitTestCase 
{
    protected $_contactID;
    protected $_participantID;
    protected $_failureCase;
    protected $_eventID;
    
    function get_info( )
    {
        return array(
                     'name'        => 'Participant Delete',
                     'description' => 'Test all Participant Delete API methods.',
                     'group'       => 'CiviCRM API Tests',
                     );
    }
    
    function setUp() 
    {
        parent::setUp();

        $event = $this->eventCreate();
        $this->_eventID = $event['event_id'];
        
        $this->_contactID = $this->individualCreate( ) ;
        $this->_participantID = $this->participantCreate( array('contactID' => $this->_contactID,'eventID' => $this->_eventID  ));

        $this->_failureCase = 0;
    }
    
    function tearDown()
    {       
        // Cleanup test contact.
        $result = $this->contactDelete( $this->_contactID );
        
	// Cleanup test event
	if ( $this->_eventID ) {
	    $this->eventDelete( $this->_eventID );
	}
    }
    
    
    function testParticipantDelete()
    {
        $params = array(
                        'id' => $this->_participantID,
                        );
        $participant = & civicrm_participant_delete($params);
        $this->assertNotEquals( $participant['is_error'],1 );
        $this->assertDBState( 'CRM_Event_DAO_Participant', $this->_participantID, NULL, true ); 

    }
    
   
    // This should return an error because required param is missing.. 
    function testParticipantDeleteMissingID()
    {
        $params = array(
                        'event_id'      => $this->_eventID,
                        );
        $participant = & civicrm_participant_delete($params);
        $this->assertEquals( $participant['is_error'],1 );
        $this->assertNotNull($participant['error_message']);
        $this->_failureCase = 1;
    }
    
    
}

