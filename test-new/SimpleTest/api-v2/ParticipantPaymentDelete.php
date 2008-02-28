<?php

require_once 'api/v2/Participant.php';

class TestOfParticipantPaymentDeleteAPIV2 extends CiviUnitTestCase 
{
    protected $_contactID;
    protected $_participantID;
    protected $_participantPaymentID;
           
    function setUp() 
    {
        $this->_contactID     = $this->organizationCreate( );
        $this->_participantID = $this->participantCreate( $this->_contactID );
    }
    
    function BROKEN_testParticipantPaymentDeleteWithEmptyParams()
    {
        $params = array();        
        $deletePayment = & civicrm_participant_payment_delete( $params ); 
        $this->assertEqual( $deletePayment['is_error'], 1 );
        $this->assertEqual( $deletePayment['error_message'], 'Invalid or no value for Participant payment ID' );
    }
    
    function BROKEN_testParticipantPaymentDeleteWithWrongID()
    {
        $params = array( 'id' => 0 );        
        $deletePayment = & civicrm_participant_payment_delete( $params ); 
        $this->assertEqual( $deletePayment['is_error'], 1 );
        $this->assertEqual( $deletePayment['error_message'], 'Invalid or no value for Participant payment ID' );
    }

    function BROKEN_testParticipantPaymentDelete()
    {
        // create contribution type 
        
        $contributionTypeID = $this->contributionTypeCreate();
        
        // create contribution
        $contributionID     = $this->contributionCreate( $this->_contactID , $contributionTypeID );
        
        $this->_participantPaymentID = $this->participantPaymentCreate( $this->_participantID, $contributionID );
        
        $params = array( 'id' => $this->_participantPaymentID );         
        $deletePayment = & civicrm_participant_payment_delete( $params );   
        $this->assertEqual( $deletePayment['is_error'], 0 );
        
        $this->contributionDelete( $contributionID );
        $this->contributionTypeDelete( $contributionTypeID );
    }
    
    function tearDown() 
    {
        $this->participantDelete( $this->_participantID );
        $this->contactDelete( $this->_contactID );
    }
}
?>
