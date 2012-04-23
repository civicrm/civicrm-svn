<?php
/*
 +--------------------------------------------------------------------+
 | CiviCRM version 4.1                                                |
 +--------------------------------------------------------------------+
 | Copyright CiviCRM LLC (c) 2004-2011                                |
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

require_once 'CiviTest/CiviUnitTestCase.php';

class CRM_Core_Payment_BaseIPNTest extends CiviUnitTestCase
{
  protected $_contributionTypeId;
  protected $_contributionParams;
  protected $_contactId;
  protected $_contributionId;
  protected $_participantId;
  protected $_pledgeId;
  protected $_eventId;
  protected $_processorId;
  protected $_contributionRecurParams;
  protected $_paymentProcessor;
  protected $IPN;
  protected $_recurId;
  protected $_membershipId;
  protected $input;
  protected $ids;
  protected $objects;
  public $DBResetRequired  = false;

    function get_info( )
    {
        return array(
                     'name'        => 'BaseIPN test',
                     'description' => 'Test BaseIPN methods (via subclass A.net).',
                     'group'       => 'Payment Processor Tests',
                     );
    }

  function setUp( )
    {
        parent::setUp();
        $this->input = $this->ids = $this->objects  = array();
        $this->IPN = new CRM_Core_Payment_AuthorizeNetIPN();

        $this->_contactId = $this->individualCreate( ) ;
        $this->ids['contact'] = $this->_contactId;
        require_once 'CRM/Core/BAO/PaymentProcessor.php';
        $this->paymentProcessor  = new CRM_Core_BAO_PaymentProcessor();

        require_once 'CRM/Core/Payment/AuthorizeNet.php';
        $paymentProcessorParams = array( 'user_name' => 'user_name',
                                   'password'  => 'password',
                                   'url_recur' => 'url_recur' );
        $this->_paymentProcessor = new CRM_Core_Payment_AuthorizeNet( 'Contribute', $paymentProcessorParams );
        $paymentProcessorParams['payment_processor_type'] = 'AuthorizeNet';
        $paymentProcessorParams['domain_id'] = 1;
        $paymentProcessorParams['is_active'] = 1;
        $paymentProcessorParams['is_test'] = 0;
        $processorEntity   = $this->paymentProcessor->create( $paymentProcessorParams);

        $this->_processorId = $processorEntity->id;
        $this->_contributionTypeId = 1;

        $this->_contributionParams = array(
         'contact_id'             => $this->_contactId,
         'version' => 3,
                                     'contribution_type_id'   => $this->_contributionTypeId,
                                     'recieve_date'           => date( 'Ymd' ),
                                     'total_amount'           => 150.00,
                                     'invoice_id'             => 'c8acb91e080ad7bd8a2adc119c192885',
                                     'currency'               => 'USD',
                                     'contribution_recur_id'  => $this->_recurId,
                                     'is_test'                => 1,
                                     'contribution_status_id' => 2,
                                     );
        $contribution = civicrm_api( 'contribution','create', $this->_contributionParams );
        $this->assertAPISuccess($contribution,'line ' . __LINE__ . ' set-up of contribution ');
        $this->_contributionId = $contribution['id'];

        $contribution = new CRM_Contribute_BAO_Contribution();
        $contribution->id = $this->_contributionId;
        $this->objects['contribution'] = $contribution;


    }

    function tearDown( )
    {
      //  $this->_paymentProcessor->delete( );
        $tablesToTruncate = array(
          'civicrm_contribution',
          'civicrm_contribution_recur',
          'civicrm_membership',
          'civicrm_membership_type',
          'civicrm_membership_payment',
          'civicrm_membership_status',
          'civicrm_payment_processor',
          'civicrm_event',
          'civicrm_participant',
          'civicrm_pledge',
        );
        $this->quickCleanup( $tablesToTruncate );
        CRM_Member_PseudoConstant::membershipType( $this->_membershipTypeID , true );
        CRM_Member_PseudoConstant::membershipStatus( null, null, 'name', true );
    }

    /**
     * Test the LoadObjects function with recurring membership data
     *
     */
    function testLoadMembershipObjects( )
    {
      $this->_setUpMembershipObjects();
      $this->_setUpRecurringContribution();
      $this->IPN->loadObjects( $this->input, $this->ids, $this->objects, FALSE, $this->_processorId );
      $this->assertFalse(empty($this->objects['membership']), 'in line ' . __LINE__);
      $this->assertArrayHasKey(0, $this->objects['membership'], 'in line ' . __LINE__);
      $this->assertTrue(is_a( $this->objects['membership'][0],'CRM_Member_BAO_Membership'));
      $this->assertTrue(is_a( $this->objects['contributionType'],'CRM_Contribute_BAO_ContributionType'));
      $this->assertFalse(empty($this->objects['contributionRecur']), __LINE__);
      $this->assertFalse(empty($this->objects['paymentProcessor']), __LINE__);
    }
    /**
     * Test the LoadObjects function with recurring membership data
     *
     */
    function testsendMailMembershipObjects( )
    {
      $this->_setUpMembershipObjects();
      $values = array();
      $this->IPN->loadObjects( $this->input, $this->ids, $this->objects, FALSE, $this->_processorId );
      $msg = $this->IPN->sendMail( $this->input, $this->ids, $this->objects, $values,false, true );
      $this->assertTrue(is_array($msg), "Message returned as an array in line" . __LINE__);
      $this->assertEquals('Mr. Anthony Anderson II', $msg['to']);
      $this->assertContains('<p>Please print this confirmation for your records.</p>',$msg['html']);
      $this->assertContains('Membership Type: General',$msg['body']);

    }
    /**
     * Test the LoadObjects function with recurring membership data
     *
     */
    function testsendMailMembershipWithoutLoadObjects( )
    {
      $this->_setUpMembershipObjects();
      $values = array();
      $msg = $this->IPN->sendMail( $this->input, $this->ids, $this->objects, $values,false, true );
      $this->assertTrue(is_array($msg), "Message returned as an array in line" . __LINE__);
      $this->assertEquals('Mr. Anthony Anderson II', $msg['to']);
      $this->assertContains('<p>Please print this confirmation for your records.</p>',$msg['html']);
      $this->assertContains('Membership Type: General',$msg['body']);

    }
    /*
     * Test that loadObjects works with participant values
     */
      function testLoadParticipantObjects( )
      {
      $this->_setUpParticipantObjects();
      $this->IPN->loadObjects( $this->input, $this->ids, $this->objects, FALSE, $paymentProcessorID );
      $this->assertFalse(empty($this->objects['participant']), 'in line ' . __LINE__);
      $this->assertTrue(is_a( $this->objects['participant'],'CRM_Event_BAO_Participant'));
      $this->assertTrue(is_a( $this->objects['contributionType'],'CRM_Contribute_BAO_ContributionType'));
      $this->assertFalse(empty($this->objects['event']));
      $this->assertTrue(is_a( $this->objects['event'],'CRM_Event_BAO_Event'));
      $this->assertTrue(is_a( $this->objects['contribution'],'CRM_Contribute_BAO_Contribution'));
      $this->assertFalse(empty($this->objects['event']->id));

    }
     /**
     * Test the LoadObjects function with a participant
     *
     */
    function testsendMailParticipant( )
    {
      $this->_setUpParticipantObjects();
      $this->IPN->loadObjects( $this->input, $this->ids, $this->objects, FALSE, $paymentProcessorID );
      $values = array();
      $this->assertFalse(empty($this->objects['event']));
      $msg = $this->IPN->sendMail($this->input, $this->ids,$this->objects, $values);
      $this->assertContains('registration has been received and your status has been updated to registered',$msg['body']);
      $this->assertContains('Annual CiviCRM meet',$msg['html']);

    }
        /*
     * Test that loadObjects works with participant values
     */
    function testLoadPledgeObjects( )
      {
      $this->_setUpPledgeObjects();
      $this->IPN->loadObjects( $this->input, $this->ids, $this->objects, FALSE, $paymentProcessorID );
      $this->assertFalse(empty($this->objects['pledge_payment'][0]), 'in line ' . __LINE__);
      $this->assertTrue(is_a( $this->objects['contributionType'],'CRM_Contribute_BAO_ContributionType'));
      $this->assertTrue(is_a( $this->objects['contribution'],'CRM_Contribute_BAO_Contribution'));
      $this->assertTrue(is_a( $this->objects['pledge_payment'][0],'CRM_Pledge_BAO_PledgePayment'));
      $this->assertFalse(empty($this->objects['pledge_payment'][0]->id));

    }
     /**
     * Test the LoadObjects function with a pledge
     *
     */
    function testsendMailPledge( )
    {
      $this->_setUpPledgeObjects();
      $values = array();
      $this->IPN->loadObjects( $this->input, $this->ids, $this->objects, FALSE, $paymentProcessorID );
      $msg = $this->IPN->sendMail($this->input, $this->ids,$this->objects, $values);
      $this->assertContains('Contribution Information',$msg['html']);

    }

    /*
     * Prepare for membership test
     */
    function _setUpMembershipObjects(){
      try{
          $this->_membershipTypeID    = $this->membershipTypeCreate( $this->_contactId  );
          $this->_membershipStatusID  = $this->membershipStatusCreate( 'test status' );
        }
        catch (Exception $e){
          echo $e->getMessage();
        }
        require_once 'CRM/Member/PseudoConstant.php';
        CRM_Member_PseudoConstant::membershipType( $this->_membershipTypeID , true );
        CRM_Member_PseudoConstant::membershipStatus( null, null, 'name', true );
        $this->_membershipParams = array(
                        'contact_id'         => $this->_contactId,
                        'membership_type_id' => $this->_membershipTypeID,
                        'join_date'          => '2009-01-21',
                        'start_date'         => '2009-01-21',
                        'end_date'           => '2009-12-21',
                        'source'             => 'Payment',
                        'is_override'        => 1,
                        'status_id'          => $this->_membershipStatusID,
                        'version' => 3
                        );

        $membership  = civicrm_api('membership', 'create', $this->_membershipParams);
        $this->assertAPISuccess($membership,'line ' . __LINE__ . ' set-up of membership');

        $this->_membershipId = $membership['id'];
        //we'll create membership payment here because to make setup more re-usable
        civicrm_api('membership_payment', 'create', array(
          'version' => 3,
          'contribution_id' => $this->_contributionId,
          'membership_id' => $this->_membershipId));

        $contribution = new CRM_Contribute_BAO_Contribution();
        $contribution->id = $this->_contributionId;
        $contribution->find();
        $this->objects['contribution'] = $contribution;
        $this->input = array(
          'component' => 'contribute',
          'total_amount' => 150.00,
          'invoiceID'  => "c8acb91e080ad7bd8a2adc119c192885",
          'contactID' => $this->_contactId,
          'contributionID' => $this->objects['contribution']->id,
          'membershipID' => $this->_membershipId,
         );

         $this->ids['membership'] = $this->_membershipId;
    }

    function _setUpRecurringContribution(){
        $this->_contributionRecurParams = array(
          'contact_id'             => $this->_contactId,
          'amount'                 => 150.00,
          'currency'               => 'USD',
          'frequency_unit'         => 'week',
          'frequency_interval'     => 1,
          'installments'           => 2,
          'start_date'             => date( 'Ymd' ),
          'create_date'            => date( 'Ymd' ),
          'invoice_id'             => 'c8acb91e080ad7bd8a2adc119c192885',
          'contribution_status_id' => 2,
          'is_test'                => 1,
          'contribution_type_id'   => $this->_contributionTypeId,
          'version' => 3,

          'payment_processor_id'   => $this->_processorId );
        $this->_recurId = civicrm_api( 'contribution_recur','create',$this->_contributionRecurParams );
        $this->assertAPISuccess($this->_recurId,'line ' . __LINE__ . ' set-up of recurring contrib');
        $this->_recurId = $this->_recurId['id'];
        $this->input['contributionRecurId'] = $this->_recurId;
        $this->ids['contributionRecur'] = $this->_recurId;
    }

    /*
     * Set up participant requirements for test
     */
    function _setUpParticipantObjects(){
        $event = $this->eventCreate();
        $this->assertAPISuccess($event,'line ' . __LINE__ . ' set-up of event');
        $this->_eventId = $event['id'];
        $this->_participantId  = $this->participantCreate(array(
          'event_id' => $this->_eventId,
          'contact_id' => $this->_contactId));
        //we'll create membership payment here because to make setup more re-usable
        $participantPayment = civicrm_api('participant_payment', 'create', array(
          'version' => 3,
          'contribution_id' => $this->_contributionId,
          'participant_id' => $this->_participantId));
        $this->assertAPISuccess($participantPayment,'line ' . __LINE__ . ' set-up of event');
        $contribution = new CRM_Contribute_BAO_Contribution();
        $contribution->id = $this->_contributionId;
        $contribution->find();
        $this->objects['contribution'] = $contribution;
        $this->input = array(
          'component' => 'event',
          'total_amount' => 150.00,
          'invoiceID'  => "c8acb91e080ad7bd8a2adc119c192885",
          'contactID' => $this->_contactId,
          'contributionID' => $contribution->id,
          'participantID' => $this->_participantId,
         );

         $this->ids['participant'] = $this->_participantId;
         $this->ids['event'] = $this->_eventId;
    }

    /*
     * Set up participant requirements for test
     */
    function _setUpPledgeObjects(){
        $this->_pledgeId = $this->pledgeCreate($this->_contactId);
        //we'll create membership payment here because to make setup more re-usable
        $pledgePayment = civicrm_api('pledge_payment', 'create', array(
          'version' => 3,
          'pledge_id' => $this->_pledgeId,
          'contribution_id' => $this->_contributionId,
          'status_id'  => 1,
          'actual_amount' => 50));
        $this->assertAPISuccess($pledgePayment,'line ' . __LINE__ . ' set-up of pledge payment');
        $this->input = array(
          'component' => 'contribute',
          'total_amount' => 150.00,
          'invoiceID'  => "c8acb91e080ad7bd8a2adc119c192885",
          'contactID' => $this->_contactId,
          'contributionID' => $this->_contributionId,
          'pledgeID' => $this->_pledgeId,
         );

         $this->ids['pledge_payment'][] = $input['id'];

    }
}
 ?>
