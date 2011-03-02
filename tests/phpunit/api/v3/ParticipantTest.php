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
class api_v3_ParticipantTest extends CiviUnitTestCase
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

  ///////////////// civicrm_participant_get methods

  /**
   * check with wrong params type
   */
  function testGetWrongParamsType()
  {
    $params = 'a string';
    $result = & civicrm_api3_participant_get($params);

    $this->assertEquals( 1, $result['is_error'], 'In line ' . __LINE__ );
  }

  /**
   * Test civicrm_participant_get with empty params
   */
  function testGetEmptyParams()
  {
    $params = array();
    $result = & civicrm_api3_participant_get($params);

    $this->assertEquals( 1, $result['is_error'], 'In line ' . __LINE__ );
  }

  /**
   * check with participant_id
   */
  function testGetParticipantIdOnly()
  {
    $params = array(
                        'participant_id'      => $this->_participantID,
                        'version'							=> $this->_apiversion,
    );
    $result =  civicrm_api3_participant_get($params);
      
    $this->assertEquals($result['values'][$this->_participantID]['event_id'], $this->_eventID);
    $this->assertEquals($result['values'][$this->_participantID]['participant_register_date'], '2007-02-19 00:00:00');
    $this->assertEquals($result['values'][$this->_participantID]['participant_source'],'Wimbeldon');
  }

  /**
   * check with params id
   */
  function testGetParamsAsIdOnly()
  {
    $params = array(
                        'id'      => $this->_participantID,
                         'version'							=> $this->_apiversion,
    );
    $result = & civicrm_api3_participant_get($params);
    $this->documentMe($params,$result ,__FUNCTION__,__FILE__);
    $this->assertEquals($result['is_error'], 0);
    $this->assertEquals($result['values'][$this->_participantID]['event_id'], $this->_eventID);
    $this->assertEquals($result['values'][$this->_participantID]['participant_register_date'], '2007-02-19 00:00:00');
    $this->assertEquals($result['values'][$this->_participantID]['participant_source'],'Wimbeldon');
  }


  /**
   * check with contact_id
   */
  function testGetContactIdOnly()
  {
    $params = array(
                        'contact_id'      => $this->_contactID,
                             'version'							=> $this->_apiversion,
    );
    $participant = & civicrm_api3_participant_get($params);

    $this->assertEquals($this->_participantID, $participant['id'],
                            "In line " . __LINE__);
    $this->assertEquals($this->_eventID,       $participant['values'][$participant['id']]['event_id'],
                            "In line " . __LINE__);
    $this->assertEquals('2007-02-19 00:00:00', $participant['values'][$participant['id']]['participant_register_date'],
                            "In line " . __LINE__);
    $this->assertEquals('Wimbeldon',          $participant['values'][$participant['id']]['participant_source'],
                            "In line " . __LINE__);
  }

  /**
   * check with event_id
   * fetch first record
   */
  function testGetMultiMatchReturnFirst()
  {
    $params = array(
                        'event_id'      => $this->_eventID,
                        'rowCount'   => 1,
                        'version'							=> $this->_apiversion,
    );

    $participant = & civicrm_api3_participant_get($params);
    $this->assertNotNull($participant['id']);
     
  }

  /**
   * check with event_id
   * in v3 this should return all participants
   */
  function testGetMultiMatchNoReturnFirst()
  {
    $params = array(
                        'event_id'      => $this->_eventID,
                         'version'							=> $this->_apiversion,
    );
    $participant = & civicrm_api3_participant_get($params);
    $this->assertEquals( $participant['is_error'],0 );
    $this->assertNotNull($participant['count'],3);
  }

  ///////////////// civicrm_participant_get methods

  /**
   * Test civicrm_participant_get with wrong params type
   */
  function testSearchWrongParamsType()
  {
    $params = 'a string';
    $result = & civicrm_api3_participant_get($params);

    $this->assertEquals( 1, $result['is_error'], 'In line ' . __LINE__ );
  }

  /**
   * Test civicrm_participant_get with empty params
   * In this case all the participant records are returned.
   */
  function testSearchEmptyParams()
  {
    $params = array('version' =>$this->_apiversion);
    $result = & civicrm_api3_participant_get($params);

    // expecting 3 participant records
    $this->assertEquals( $result['count'] , 3 );
  }

  /**
   * check with participant_id
   */
  function testSearchParticipantIdOnly()
  {
    $params = array(
                        'participant_id'      => $this->_participantID,
                             'version'							=> $this->_apiversion,
    );
    $participant = & civicrm_api3_participant_get($params);
    $this->assertEquals($participant['values'][$this->_participantID]['event_id'], $this->_eventID);
    $this->assertEquals($participant['values'][$this->_participantID]['participant_register_date'], '2007-02-19 00:00:00');
    $this->assertEquals($participant['values'][$this->_participantID]['participant_source'],'Wimbeldon');
  }

  /**
   * check with contact_id
   */
  function testSearchContactIdOnly()
  {
    // Should get 2 participant records for this contact.
    $params = array(
                        'contact_id'      => $this->_contactID2,
                        'version'							=> $this->_apiversion,
    );
    $participant = & civicrm_api3_participant_get($params);

    $this->assertEquals(  $participant['count'] , 2 );
  }

  /**
   * check with event_id
   */
  function testSearchByEvent()
  {
    // Should get >= 3 participant records for this event. Also testing that last_name and event_title are returned.
    $params = array(
                        'event_id'      => $this->_eventID,
                        'return.last_name' => 1,
                        'return.event_title' => 1,
                        'version'							=> $this->_apiversion,
    );
    $participant = & civicrm_api3_participant_get($params);
    if (  $participant['count']  < 3 ) {
      $this->fail("Event search returned less than expected miniumum of 3 records.");
    }

    $this->assertEquals($participant['values'][$this->_participantID]['last_name'],'Anderson');
    $this->assertEquals($participant['values'][$this->_participantID]['event_title'],'Annual CiviCRM meet');
  }

  /**
   * check with event_id
   * fetch with limit
   */
  function testSearchByEventWithLimit()
  {
    // Should 2 participant records since we're passing rowCount = 2.
    $params = array(
                        'event_id'      => $this->_eventID,
                        'rowCount'      => 3,
                             'version'							=> $this->_apiversion,
    );
    $participant = & civicrm_api3_participant_get($params);
     
    $this->assertEquals(  $participant['count'], 3,'in line ' . __LINE__);
  }

  ///////////////// civicrm_participant_create methods

  /**
   * Test civicrm_participant_create with wrong params type
   */
  function testCreateWrongParamsType()
  {
    $params = 'a string';
    $result = & civicrm_api3_participant_create($params);

    $this->assertEquals( 1, $result['is_error'], 'In line ' . __LINE__ );
  }


  /**
   * Test civicrm_participant_create with empty params
   */
  function testCreateEmptyParams()
  {
    $params = array();
    $result = & civicrm_api3_participant_create($params);

    $this->assertEquals( 1, $result['is_error'], 'In line ' . __LINE__ );
  }

  /**
   * check with event_id
   */
  function testCreateMissingContactID()
  {
    $params = array(
                        'event_id'      => $this->_eventID,
                             'version'							=> $this->_apiversion,
    );
    $participant = & civicrm_api3_participant_create($params);
    if ( CRM_Utils_Array::value('id', $participant) ) {
      $this->_createdParticipants[] = $participant['id'];
    }
    $this->assertEquals( $participant['is_error'],1 );
    $this->assertNotNull($participant['error_message']);
  }

  /**
   * check with contact_id
   * without event_id
   */
  function testCreateMissingEventID()
  {
    $params = array(
                        'contact_id'    => $this->_contactID,
                             'version'							=> $this->_apiversion,
    );
    $participant = & civicrm_api3_participant_create($params);
    if ( CRM_Utils_Array::value('id', $participant) ) {
      $this->_createdParticipants[] = $participant['id'];
    }
    $this->assertEquals( $participant['is_error'],1 );
    $this->assertNotNull($participant['error_message']);
  }

  /**
   * check with contact_id & event_id
   */
  function testCreateEventIdOnly()
  {
    $params = array(
                        'contact_id'    => $this->_contactID,
                        'event_id'      => $this->_eventID,
                             'version'							=> $this->_apiversion,
    );
    $participant = & civicrm_api3_participant_create($params);
    $this->assertNotEquals( $participant['is_error'],1 );
    $this->_participantID = $participant['id'];

    if ( ! $participant['is_error'] ) {
      $this->_createdParticipants[] = CRM_Utils_Array::value('result', $participant);
      // Create $match array with DAO Field Names and expected values
      $match = array(
                           'id' => CRM_Utils_Array::value('values', $participant)
      );
      // assertDBState compares expected values in $match to actual values in the DB
      $this->assertDBState( 'CRM_Event_DAO_Participant', $participant['result'], $match );
    }
  }

  /**
   * check with complete array
   */
  function testCreateAllParams()
  {
    $params = array(
                        'contact_id'    => $this->_contactID,
                        'event_id'      => $this->_eventID,
                        'status_id'     => 1,
                        'role_id'       => 1,
                        'register_date' => '2007-07-21',
                        'source'        => 'Online Event Registration: API Testing',
                        'event_level'   => 'Tenor',  
                        'version'				=> $this->_apiversion,                      
    );

    $participant =  civicrm_api3_participant_create($params);
    $this->documentMe($params,$participant ,__FUNCTION__,__FILE__);
    $this->assertNotEquals( $participant['is_error'],1 ,'in line ' . __LINE__);
    $this->_participantID = $participant['id'];
    if ( ! $participant['is_error'] ) {
      $this->_createdParticipants[] = CRM_Utils_Array::value('values', $participant);

      // Create $match array with DAO Field Names and expected values
      $match = array(
                           'id'         => CRM_Utils_Array::value('id', $participant)
      );
      // assertDBState compares expected values in $match to actual values in the DB
      $this->assertDBState( 'CRM_Event_DAO_Participant', $participant['values'][$this->_participantID], $match );
    }
  }

  ///////////////// civicrm_participant_update methods

  /**
   * Test civicrm_participant_update with wrong params type
   */
  function testUpdateWrongParamsType()
  {
    $params = 'a string';
    $result = & civicrm_api3_participant_create($params);
    $this->assertEquals( 1, $result['is_error'], 'In line ' . __LINE__ );
    $this->assertEquals( 'Input variable `params` is not an array', $result['error_message'], 'In line ' . __LINE__ );
  }

  /**
   * check with empty array
   */
  function testUpdateEmptyParams()
  {
    $params = array('version' => $this->_apiversion);
    $participant = & civicrm_api3_participant_create($params);
    $this->assertEquals( $participant['is_error'],1 );
    $this->assertEquals( $participant['error_message'],'Mandatory key(s) missing from params array: event_id, contact_id' );
  }

  /**
   * check without event_id
   */
  function testUpdateWithoutEventId()
  {
    $participantId = $this->participantCreate( array ('contactID' => $this->_individualId, 'eventID' => $this->_eventID  ) ,$this->_apiversion);
    $params = array(
                        'contact_id'    => $this->_individualId,
                        'status_id'     => 3,
                        'role_id'       => 3,
                        'register_date' => '2006-01-21',
                        'source'        => 'US Open',
                        'event_level'   => 'Donation' , 
                        'version'							=> $this->_apiversion,                      
    );
    $participant = & civicrm_api3_participant_create($params);
    $this->assertEquals( $participant['is_error'], 1 );
    $this->assertEquals( $participant['error_message'],'Mandatory key(s) missing from params array: event_id' );
    // Cleanup created participant records.
    $result = $this->participantDelete( $participantId ,     $this->_apiversion);
  }

  /**
   * check with Invalid participantId
   */
  function testUpdateWithWrongParticipantId()
  {
    $params = array(
                        'id'            => 1234,
                        'status_id'     => 3,
                        'role_id'       => 3,
                        'register_date' => '2006-01-21',
                        'source'        => 'US Open',
                        'event_level'   => 'Donation',
                             'version'							=> $this->_apiversion,                        
    );
    $participant = & civicrm_api3_participant_create($params);
    $this->assertEquals( $participant['is_error'], 1 );
    $this->assertEquals( $participant['error_message'],'Participant  id is not valid' );

  }

  /**
   * check with Invalid ContactId
   */
  function testUpdateWithWrongContactId()
  {
    $participantId = $this->participantCreate( array ('contactID' => $this->_individualId,
                                                          'eventID' => $this->_eventID ),$this->_apiversion );
    $params = array(
                        'id'            => $participantId,
                        'contact_id'    => 12345,
                        'status_id'     => 3,
                        'role_id'       => 3,
                        'register_date' => '2006-01-21',
                        'source'        => 'US Open',
                        'event_level'   => 'Donation',
                        'version'							=> $this->_apiversion,                        
    );
    $participant = & civicrm_api3_participant_create($params);
    $this->assertEquals( $participant['is_error'], 1 );
    $this->assertEquals( $participant['error_message'],'Mandatory key(s) missing from params array: event_id' );
    $result = $this->participantDelete( $participantId,  $this->_apiversion );
  }

  /**
   * check with complete array
   */
  function testUpdate()
  {
    $participantId = $this->participantCreate( array ('contactID' => $this->_individualId,'eventID' => $this->_eventID ),$this->_apiversion );
    $params = array(
                        'id'            => $participantId,
                        'contact_id'    => $this->_individualId,
                        'event_id'      => $this->_eventID,
                        'status_id'     => 3,
                        'role_id'       => 3,
                        'register_date' => '2006-01-21',
                        'source'        => 'US Open',
                        'event_level'   => 'Donation' ,
                             'version'							=> $this->_apiversion,                       
    );
    $participant = & civicrm_api3_participant_create($params);
    $this->assertNotEquals( $participant['is_error'],1 );


    if ( ! $participant['is_error'] ) {
      $params['id'] = CRM_Utils_Array::value('id', $participant);

      // Create $match array with DAO Field Names and expected values
      $match = array(
                           'id'         => CRM_Utils_Array::value('id', $participant)
      );
      // assertDBState compares expected values in $match to actual values in the DB
      $this->assertDBState( 'CRM_Event_DAO_Participant', $participant['id'], $match );

    }
    // Cleanup created participant records.
    $result = $this->participantDelete( $params['id'],$this->_apiversion );
  }



  ///////////////// civicrm_participant_delete methods

  /**
   * Test civicrm_participant_delete with wrong params type
   */
  function testDeleteWrongParamsType()
  {
    $params = 'a string';
    $result = & civicrm_api3_participant_delete($params);

    $this->assertEquals( 1, $result['is_error'], 'In line ' . __LINE__ );
  }

  /**
   * Test civicrm_participant_delete with empty params
   */
  function testDeleteEmptyParams()
  {
    $params = array();
    $result = & civicrm_api3_participant_delete($params);

    $this->assertEquals( 1, $result['is_error'], 'In line ' . __LINE__ );
  }

  /**
   * check with participant_id
   */
  function testParticipantDelete()
  {
    $params = array(
                        'id' => $this->_participantID,
                        'version' =>$this->_apiversion,
    );
    $participant = & civicrm_api3_participant_delete($params);
    $this->assertNotEquals( $participant['is_error'],1 );
    $this->assertDBState( 'CRM_Event_DAO_Participant', $this->_participantID, NULL, true );

  }

  /**
   * check without participant_id
   * and with event_id
   * This should return an error because required param is missing..
   */
  function testParticipantDeleteMissingID()
  {
    $params = array(
                        'event_id'      => $this->_eventID,
                             'version'							=> $this->_apiversion,
    );
    $participant = & civicrm_api3_participant_delete($params);
    $this->assertEquals( $participant['is_error'],1 );
    $this->assertNotNull($participant['error_message']);
  }

  ///////////////// civicrm_create_participant_formatted methods
  /**
  * Test civicrm_participant_formatted Empty  params type
  */
  function testParticipantFormattedEmptyParams()
  {
    $params = array();
    $onDuplicate = array();
    $participant = & civicrm_api3_create_participant_formatted($params,$onDuplicate );
    $this->assertEquals( $participant['error_message'] ,'Input Parameters empty' );
  }

  function testParticipantFormattedwithDuplicateParams()
  {
    $participantContact = $this->individualCreate(null,$this->_apiversion );
    $params = array(
                        'contact_id'    => $participantContact,
                        'event_id'      => $this->_eventID,
    );
    require_once 'CRM/Event/Import/Parser.php';
    $onDuplicate = CRM_Event_Import_Parser::DUPLICATE_NOCHECK;
    $participant = & civicrm_api3_create_participant_formatted($params,$onDuplicate );
    $this->assertEquals( $participant['is_error'],0);
  }

  /**
   * Test civicrm_participant_formatted with wrong $onDuplicate
   */
  function testParticipantFormattedwithWrongDuplicateConstant()
  {
    $participantContact = $this->individualCreate(null,$this->_apiversion );
    $params = array(
                        'contact_id'    => $participantContact,
                        'event_id'      => $this->_eventID,
    );
    $onDuplicate =11;
    $participant = & civicrm_api3_create_participant_formatted($params,$onDuplicate );
    $this->assertEquals( $participant['is_error'],0);
  }


  function testParticipantcheckWithParams()
  {
    $participantContact = $this->individualCreate( null,$this->_apiversion );
    $params = array(
                        'contact_id'    => $participantContact,
                        'event_id'      => $this->_eventID,
    );
    require_once 'CRM/Event/Import/Parser.php';
    $participant = & civicrm_api3_participant_check_params( $params );
    $this->assertEquals( $participant, true , 'Check the returned True');
  }
}