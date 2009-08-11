<?php

require_once 'api/v2/Participant.php';
require_once 'CiviTest/CiviUnitTestCase.php';

class api_v2_ParticipantPaymentDeleteTest extends CiviUnitTestCase 
{
    protected $_contactID;
    protected $_participantID;
    protected $_participantPaymentID;

    function get_info( )
    {
        return array(
                     'name'        => 'Participant Payment Delete',
                     'description' => 'Test all Participant Payment Delete API methods.',
                     'group'       => 'CiviCRM API Tests',
                     );
    }       

    function setUp() 
    {
        parent::setUp();
        
        $event = $this->eventCreate();
        $this->_eventID = $event['event_id'];

        $this->_contactID     = $this->organizationCreate( );
        $this->_participantID = $this->participantCreate( array ('contactID' => $this->_contactID, 'eventID' => $this->_eventID ) );
    }
    
    function testParticipantPaymentDeleteWithEmptyParams()
    {
        $params = array();        
        $deletePayment = & civicrm_participant_payment_delete( $params ); 
        $this->assertEquals( $deletePayment['is_error'], 1 );
        $this->assertEquals( $deletePayment['error_message'], 'Invalid or no value for Participant payment ID' );
    }
    
    function testParticipantPaymentDeleteWithWrongID()
    {
        $params = array( 'id' => 0 );        
        $deletePayment = & civicrm_participant_payment_delete( $params ); 
        $this->assertEquals( $deletePayment['is_error'], 1 );
        $this->assertEquals( $deletePayment['error_message'], 'Invalid or no value for Participant payment ID' );
    }

    function testParticipantPaymentDelete()
    {
        // create contribution type 
        
        $contributionTypeID = $this->contributionTypeCreate();
        
        // create contribution
        $contributionID     = $this->contributionCreate( $this->_contactID , $contributionTypeID );
        
        $this->_participantPaymentID = $this->participantPaymentCreate( $this->_participantID, $contributionID );
        
        $params = array( 'id' => $this->_participantPaymentID );         
        $deletePayment = & civicrm_participant_payment_delete( $params );   
        $this->assertEquals( $deletePayment['is_error'], 0 );
        
        $this->contributionDelete( $contributionID );
        $this->contributionTypeDelete( $contributionTypeID );
    }
    
    function tearDown() 
    {
        $this->participantDelete( $this->_participantID );
        $this->contactDelete( $this->_contactID );

        // Cleanup test event.
        $result = $this->eventDelete($this->_eventID);
    }
}

