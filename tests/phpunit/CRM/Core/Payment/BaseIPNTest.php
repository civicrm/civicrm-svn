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
require_once 'CRM/Core/Payment/AuthorizeNetIPN.php';

class CRM_Core_Payment_BaseIPNTest extends CiviUnitTestCase 
{
  protected $_contributionTypeId;
  protected $_contributionParams;
  protected $_contactId;
  protected $_contributionId;
  protected $_contributionRecurParams;
  protected $_paymentProcessor;
  protected $IPN;
  protected $_recurId;
  protected $_membershipId;
  protected $_membershipTypeID;
  
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

        $this->IPN = new CRM_Core_Payment_AuthorizeNetIPN();
        
        $this->_contactId = $this->individualCreate( ) ;
        require_once 'CRM/Core/BAO/PaymentProcessor.php';
        $this->paymentProcessorType  = new CRM_Core_BAO_PaymentProcessor();
        $this->processorParams   = $this->paymentProcessorType->create( );
        require_once 'CRM/Core/Payment/AuthorizeNet.php';
        $paymentProcessorParams = array( 'user_name' => 'user_name',
                                   'password'  => 'password',
                                   'url_recur' => 'url_recur' );  
        $this->_paymentProcessor = new CRM_Core_Payment_AuthorizeNet( 'Contribute', $paymentProcessorParams );
        $this->_contributionTypeId = $this->contributionTypeCreate( );
        $ids = array();
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

          'payment_processor_id'   => $this->processor->id );
        $this->_recurId = civicrm_api( 'contribution_recur','create',$this->_contributionRecurParams );
        $this->assertAPISuccess($this->_recurId,'line ' . __LINE__ . ' set-up of recurring contrib');
        
        $this->_recurId = $this->_recurId['id'];
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
        $this->_membershipTypeID    = $this->membershipTypeCreate( $this->_contactId  );        
        $this->_membershipStatusID  = $this->membershipStatusCreate( 'test status' );
        
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
                        'version'			 => 3
                        );

        $membership  = civicrm_api('membership', 'create', $this->_membershipParams);
        $this->assertAPISuccess($membership,'line ' . __LINE__ . ' set-up of membership');
        
        $this->_membershipId = $membership['id'];
    
    }

    function tearDown( )
    {
      //  $this->_paymentProcessor->delete( );
        $tablesToTruncate = array( 
          'civicrm_contribution_type', 
          'civicrm_contribution', 
          'civicrm_contribution_recur',
          'civicrm_membership',
          'civicrm_payment_processor',
        );
        $this->quickCleanup( $tablesToTruncate );
    }
    
    /**
     * create a single post dated payment as a recurring transaction.
     * 
     * Test works but not both due to some form of caching going on in the SmartySingleton 
     */
    function testLoadObjects( )
    {

        $ids       = array();
        //we'll create membership payment here because to make setup more re-usable
        civicrm_api('membership_payment', 'create', array(
          'version' => 3, 
          'contribution_id' => $this->_contributionId,
          'membership_id' => $this->membershipId));
        $contribution = new CRM_Contribute_BAO_Contribution();
        $contribution->id = $this->_contributionId;
        $contribution->find();
        $objects['contribution'] = $contribution; 
        $input = array(
          'component' => 'contribute',
          'total_amount' => 150.00,
          'invoiceID'  => "c8acb91e080ad7bd8a2adc119c192885",
          'contactID' => $this->_contactId,
          'contributionID' => $contribution->id,
          'contributionRecurID' => $this->_recurId,
          'membershipID' => $this->_membershipId,
         );

         $ids = array(
           'membership' => $this->_membershipId,
           'contributionRecur' => $this->_recurId,
         );
         $this->IPN->loadObjects( $input, $ids, &$objects, $required, $paymentProcessorID );

         $this->assertFalse(empty($objects['membership']), 'in line ' . __LINE__);
         $this->assertFalse(empty($objects['contributionRecur']));
    }  
        
    /**
     * create a single post dated payment as a recurring transaction
     */

}
 ?>
