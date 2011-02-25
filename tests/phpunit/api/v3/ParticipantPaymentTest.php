<?php
/*
 +--------------------------------------------------------------------+
 | CiviCRM version 3.3                                                |
 +--------------------------------------------------------------------+
 | Copyright CiviCRM LLC (c) 2004-2010                                |
 +--------------------------------------------------------------------+
 | This file is a part of CiviCRM.                                    |
 |                                                                    |
 | CiviCRM is free software; you can copy, modify, and distribute it  |
 | under the terms of the GNU Affero General Public License           |
 | Version 3, 19 November 2007 and the CiviCRM Licensing Exception.   |
 |                                                                    |
 | CiviCRM is distributed in the hope that it will be useful, but     |
 | WITHOUT ANY WARRANTY; without even the implied warranty of         |
 | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.               |
 | See the GNU Affero General Public License for more details.        |
 |                                                                    |
 | You should have received a copy of the GNU Affero General Public   |
 | License and the CiviCRM Licensing Exception along                  |
 | with this program; if not, contact CiviCRM LLC                     |
 | at info[AT]civicrm[DOT]org. If you have questions about the        |
 | GNU Affero General Public License or the licensing of CiviCRM,     |
 | see the CiviCRM license FAQ at http://civicrm.org/licensing        |
 +--------------------------------------------------------------------+
 */


require_once 'api/v3/Participant.php';
require_once 'CiviTest/CiviUnitTestCase.php';
require_once 'api/v3/ParticipantPayment.php';
class api_v3_ParticipantPaymentTest extends CiviUnitTestCase
{

  protected $_apiversion;
  protected $_contactID;
  protected $_createdParticipants;
  protected $_participantID;
  protected $_eventID;

  function get_info( )
  {
    return array(
                     'name'        => 'Participant Create',
                     'description' => 'Test all Participant Create API methods.',
                     'group'       => 'CiviCRM API Tests',
    );
  }

  function setUp()
  {

    $this->_apiversion = 3;
    parent::setUp();

    $event = $this->eventCreate(null, $this->_apiversion);
    $this->_eventID = $event['id'];

    $this->_contactID = $this->individualCreate(null,$this->_apiversion ) ;

    $this->_createdParticipants = array( );
    $this->_individualId = $this->individualCreate(null,$this->_apiversion);

    $this->_participantID = $this->participantCreate( array('contactID' => $this->_contactID,'eventID' => $this->_eventID  ),$this->_apiversion);
    $this->_contactID2 = $this->individualCreate( null,$this->_apiversion) ;
    $this->_participantID2 = $this->participantCreate( array('contactID' => $this->_contactID2,'eventID' => $this->_eventID,'version' =>$this->_apiversion ),$this->_apiversion);
    $this->_participantID3 = $this->participantCreate( array ('contactID' => $this->_contactID2, 'eventID' => $this->_eventID,'version' =>$this->_apiversion ),$this->_apiversion);
  }

  function tearDown()
  {
    // _participant, _contact and _event tables cleaned up in truncate.xml
  }


  ///////////////// civicrm_participant_payment_create methods

  /**
   * Test civicrm_participant_payment_create with wrong params type
   */
  function testPaymentCreateWrongParamsType()
  {
    $params = 'a string';
    $result = & civicrm_api3_participant_payment_create($params);

    $this->assertEquals( 1, $result['is_error'], 'In line ' . __LINE__ );
  }

  /**
   * Test civicrm_participant_payment_create with empty params
   */
  function testPaymentCreateEmptyParams()
  {
    $params = array();
    $result = & civicrm_api3_participant_payment_create($params);

    $this->assertEquals( 1, $result['is_error'], 'In line ' . __LINE__ );
  }

  /**
   * check without participant_id
   */
  function testPaymentCreateMissingParticipantId( )
  {
    $contributionTypeID = 1;

    //Create Contribution & get entity ID
    $contributionID = $this->contributionCreate( $this->_contactID , $contributionTypeID ,$this->_apiversion );

    //WithoutParticipantId
    $params = array(
                        'contribution_id'    => $contributionID,
                             'version'							=> $this->_apiversion,
    );
    $participantPayment = & civicrm_api3_participant_payment_create( $params );
    $this->assertEquals( $participantPayment['is_error'], 1 );

    //delete created contribution
    $this->contributionDelete( $contributionID ,$this->_apiversion);

    // delete created contribution type
    $this->contributionTypeDelete( $contributionTypeID,$this->_apiversion );
  }

  /**
   * check without contribution_id
   */
  function testPaymentCreateMissingContributionId( )
  {
    //Without Payment EntityID
    $params = array(
                        'participant_id'       => $this->_participantID,
                        'version'							=>$this->_apiversion,
    );
    $participantPayment = & civicrm_api3_participant_payment_create( $params );
    $this->assertEquals( $participantPayment['is_error'], 1 );
  }

  /**
   * check with valid array
   */
  function testPaymentCreate( )
  {

    $contributionTypeID = 1;

    //Create Contribution & get contribution ID
    $contributionID = $this->contributionCreate( $this->_contactID , $contributionTypeID ,$this->_apiversion );

    //Create Participant Payment record With Values
    $params = array(
                        'participant_id'  => $this->_participantID,
                        'contribution_id' => $contributionID,
                        'version'					=> $this->_apiversion,
    );
    $result  = & civicrm_api3_participant_payment_create( $params );
    $this->documentMe($params,$result ,__FUNCTION__,__FILE__);
    $this->assertEquals( $result ['is_error'], 0,'in line '. __LINE__ );
    $this->assertTrue( array_key_exists( 'id', $result ),'in line '. __LINE__ );

    //delete created contribution
    $this->contributionDelete( $contributionID );

    // delete created contribution type
    $this->contributionTypeDelete( $contributionTypeID );
  }


  ///////////////// civicrm_participant_payment_create methods

  /**
   * Test civicrm_participant_payment_create with wrong params type
   */
  function testPaymentUpdateWrongParamsType()
  {
    $params = 'a string';
    $result = & civicrm_api3_participant_payment_create($params);

    $this->assertEquals( 1, $result['is_error'], 'In line ' . __LINE__ );
    $this->assertEquals('Input variable `params` is not an array', $result['error_message'], 'In line ' . __LINE__);
  }

  /**
   * check with empty array
   */
  function testPaymentUpdateEmpty()
  {
    $params = array();
    $participantPayment = & civicrm_api3_participant_payment_create( $params );
    $this->assertEquals( $participantPayment['is_error'], 1 );
  }

  /**
   * check with missing participant_id
   */
  function testPaymentUpdateMissingParticipantId()
  {
    //WithoutParticipantId
    $params = array(
                        'contribution_id' => '3',
                         'version'				 =>$this->_apiversion,
    );
    $participantPayment = & civicrm_api3_participant_payment_create( $params );
    $this->assertEquals( $participantPayment['is_error'], 1 );
  }

  /**
   * check with missing contribution_id
   */
  function testPaymentUpdateMissingContributionId()
  {
    $params = array(
                        'participant_id' => $this->_participantID,
                        'version'				 =>$this->_apiversion,
    );
    $participantPayment = & civicrm_api3_participant_payment_create( $params );
    $this->assertEquals( $participantPayment['is_error'], 1 );
  }

  /**
   * check with complete array
   */
  function testPaymentUpdate()
  {
    $contributionTypeID = 1;

    // create contribution
    $contributionID     = $this->contributionCreate( $this->_contactID , $contributionTypeID,$this->_apiversion  );

    $this->_participantPaymentID = $this->participantPaymentCreate( $this->_participantID, $contributionID ,$this->_apiversion );
    $params = array(
                        'id'              => $this->_participantPaymentID,
                        'participant_id'  => $this->_participantID,
                        'contribution_id' => $contributionID,
                        'version'					=> $this->_apiversion,
    );

    // Update Payment
    $participantPayment = & civicrm_api3_participant_payment_create( $params );
    $this->assertEquals( $participantPayment['id'],$this->_participantPaymentID );
    $this->assertTrue ( array_key_exists( 'id', $participantPayment ) );

    $params = array( 'id' => $this->_participantPaymentID ,
                                 'version' => $this->_apiversion, );
    $deletePayment = & civicrm_api3_participant_payment_delete( $params );
    $this->assertEquals( $deletePayment['is_error'], 0 );

    $this->contributionDelete( $contributionID , $this->_apiversion );
    $this->contributionTypeDelete( $contributionTypeID , $this->_apiversion );
  }

  ///////////////// civicrm_participant_payment_delete methods

  /**
   * Test civicrm_participant_payment_delete with wrong params type
   */
  function testPaymentDeleteWrongParamsType()
  {
    $params = 'a string';
    $result = & civicrm_api3_participant_payment_delete($params);

    $this->assertEquals( 1, $result['is_error'], 'In line ' . __LINE__ );
  }

  /**
   * check with empty array
   */
  function testPaymentDeleteWithEmptyParams()
  {
    $params = array();
    $deletePayment = & civicrm_api3_participant_payment_delete( $params );
    $this->assertEquals( $deletePayment['is_error'], 1 );
    $this->assertEquals( $deletePayment['error_message'], 'Mandatory key(s) missing from params array: id, version' );
  }

  /**
   * check with wrong id
   */
  function testPaymentDeleteWithWrongID()
  {
    $params = array( 'id' => 0,
                         'version' => $this->_apiversion, );        
    $deletePayment = & civicrm_api3_participant_payment_delete( $params );
    $this->assertEquals( $deletePayment['is_error'], 1 );
    $this->assertEquals( $deletePayment['error_message'], 'Error while deleting participantPayment' );
  }

  /**
   * check with valid array
   */
  function testPaymentDelete()
  {
    $contributionTypeID = 1;

    // create contribution
    $contributionID     = $this->contributionCreate( $this->_contactID , $contributionTypeID ,$this->_apiversion );

    $this->_participantPaymentID = $this->participantPaymentCreate( $this->_participantID, $contributionID,$this->_apiversion  );

    $params = array( 'id' => $this->_participantPaymentID,
                          'version'				=> $this->_apiversion, );         
    $result = & civicrm_api3_participant_payment_delete( $params );
    $this->documentMe($params,$result,__FUNCTION__,__FILE__);
    $this->assertEquals( $result['is_error'], 0 );

  }

}

