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

/**
 *
 * @package CRM
 * @copyright CiviCRM LLC (c) 2004-2010
 * $Id$
 *
 */

require_once 'CRM/Core/DAO/PaymentProcessor.php';

/**
 * This class contains payment processor related functions.
 */
class CRM_Core_BAO_PaymentProcessor extends CRM_Core_DAO_PaymentProcessor 
{
    /**
     * static holder for the default payment processor
     */
    static $_defaultPaymentProcessor = null;

    /**
     * class constructor
     */
    function __construct( ) 
    {
        parent::__construct( );
    }

    /**
     * Takes a bunch of params that are needed to match certain criteria and
     * retrieves the relevant objects. It also stores all the retrieved
     * values in the default array
     *
     * @param array $params   (reference ) an assoc array of name/value pairs
     * @param array $defaults (reference ) an assoc array to hold the flattened values
     *
     * @return object CRM_Core_DAO_PaymentProcessor object on success, null otherwise
     * @access public
     * @static
     */
    static function retrieve( &$params, &$defaults ) 
    {
        $paymentProcessor = new CRM_Core_DAO_PaymentProcessor( );
        $paymentProcessor->copyValues( $params );
        if ( $paymentProcessor->find( true ) ) {
            CRM_Core_DAO::storeValues( $paymentProcessor, $defaults );
            return $paymentProcessor;
        }
        return null;
    }
    
    /**
     * update the is_active flag in the db
     *
     * @param int      $id        id of the database record
     * @param boolean  $is_active value we want to set the is_active field
     *
     * @return Object             DAO object on sucess, null otherwise
     * 
     * @access public
     * @static
     */
    static function setIsActive( $id, $is_active ) 
    {
        return CRM_Core_DAO::setFieldValue( 'CRM_Core_DAO_PaymentProcessor', $id, 'is_active', $is_active );
    }
    
    /**
     * retrieve the default payment processor
     * 
     * @param NULL
     * 
     * @return object           The default payment processor object on success,
     *                          null otherwise
     * @static
     * @access public
     */
    static function &getDefault( ) 
    {
        if (self::$_defaultPaymentProcessor == null) {
            $params = array( 'is_default' => 1 );
            $defaults = array();
            self::$_defaultPaymentProcessor = self::retrieve($params, $defaults);
        }
        return self::$_defaultPaymentProcessor;
    }
    
    /**
     * Function  to delete payment processor
     * 
     * @param  int  $paymentProcessorId     ID of the processor to be deleted.
     * 
     * @access public
     * @static
     */
    static function del( $paymentProcessorID ) {
        if ( ! $paymentProcessorID ) {
            CRM_Core_Error::fatal( ts( 'Invalid value passed to delete function' ) );
        }

        $dao            = new CRM_Core_DAO_PaymentProcessor( );
        $dao->id        =  $paymentProcessorID;
        if ( ! $dao->find( true ) ) {
            return null;
        }

        $testDAO            = new CRM_Core_DAO_PaymentProcessor( );
        $testDAO->name      =  $dao->name;
        $testDAO->is_test   =  1;
        $testDAO->delete( );

        $dao->delete( );
    }

    /**
     * Function to get the payment processor details
     * 
     * @param  int    $paymentProcessorID payment processor id
     * @param  string $mode               payment mode ie test or live  
     * 
     * @return array  associated array with payment processor related fields
     * @static
     * @access public 
     */
    static function getPayment( $paymentProcessorID, $mode ) 
    {
        if ( ! $paymentProcessorID ) {
            CRM_Core_Error::fatal( ts( 'Invalid value passed to getPayment function' ) );
        }

        $dao            = new CRM_Core_DAO_PaymentProcessor( );
        $dao->id        =  $paymentProcessorID;
        $dao->is_active =  1;
        if ( ! $dao->find( true ) ) {
            return null;
        }

        if ( $mode == 'test' ) {
            $testDAO = new CRM_Core_DAO_PaymentProcessor( );
            $testDAO->name      = $dao->name;
            $testDAO->is_active = 1;
            $testDAO->is_test   = 1;
            if ( ! $testDAO->find( true ) ) {
                CRM_Core_Error::fatal( ts( 'Could not retrieve payment processor details' ) );
            }
            return self::buildPayment( $testDAO );
        } else {
            return self::buildPayment( $dao );
        }
    }

    /**
     * Function to build payment processor details
     *
     * @param object $dao payment processor object
     *
     * @return array  associated array with payment processor related fields
     * @static
     * @access public 
     */
    static function buildPayment( $dao ) 
    {
        $fields = array( 'id', 'name', 'payment_processor_type', 'user_name', 'password',
                         'signature', 'url_site', 'url_api', 'url_recur', 'url_button',
                         'subject', 'class_name', 'is_recur', 'billing_mode',
                         'payment_type' );
        $result = array( );
        foreach ( $fields as $name ) {
            $result[$name] = $dao->$name;
        }
        return $result;
    }

    /**
     * Function to retrieve payment processor id / info/ object based on component-id.
     *
     * @param int    $componentID id of a component
     * @param string $component   component
     * @param string $type        type of payment information to be retrieved
     *
     * @return id / array / object based on type
     * @static
     * @access public 
     */
    static function getProcessorForComponent( $componentID, $component = 'contribute', $type = 'id' ) 
    {
        if ( ! in_array( $component, array('membership', 'contribute') ) ) {
            return null;
        }

        if ( $component == 'membership' ) {
            $sql = " 
    SELECT cp.payment_processor_id, con.id, con.is_test 
      FROM civicrm_membership mem
INNER JOIN civicrm_membership_payment mp  ON ( mem.id = mp.membership_id ) 
INNER JOIN civicrm_contribution       con ON ( mp.contribution_id = con.id )
 LEFT JOIN civicrm_contribution_page   cp ON ( con.contribution_page_id = cp.id )
     WHERE mp.membership_id = %1";

        } else if ( $component == 'contribute' ) {
            $sql = " 
    SELECT cp.payment_processor_id, con.id, con.is_test 
      FROM civicrm_contribution      con
 LEFT JOIN civicrm_contribution_page cp ON ( con.contribution_page_id = cp.id )
     WHERE con.id = %1";
        }

        $params = array( 1 => array( $componentID, 'Integer' ) );
        $dao    = CRM_Core_DAO::executeQuery( $sql, $params );
        $dao->fetch();

        $ppID = $dao->payment_processor_id;
        $contributionID = $dao->id;
        
        if ( !$ppID && $contributionID ) {
            // it's probably a offline contribution
            // FIXME: dependency from financial_trxn should be removed, when we have added processor_id in recur table.
            $sql = " 
    SELECT ft.payment_processor 
      FROM civicrm_financial_trxn ft 
INNER JOIN civicrm_entity_financial_trxn eft ON ( eft.financial_trxn_id = ft.id AND eft.entity_table = 'civicrm_contribution' ) 
     WHERE eft.entity_id = %1";
            $params = array( 1 => array( $contributionID, 'Integer' ) );
            $processorType  = CRM_Core_DAO::singleValueQuery( $sql, $params );

            if ( $processorType ) {
                $params = array( 'payment_processor_type' => $processorType,
                                 'is_test'                => $dao->is_test ? 1 : 0 );
                CRM_Core_BAO_PaymentProcessor::retrieve( $params, $paymentProcessor );
                $ppID = $paymentProcessor['id'];
            }
        }

        $mode = ( $dao->is_test ) ? 'test' : 'live';
        if ( $type == 'id' ) {
            return $ppID;
        } else if ( $type == 'info' ) {
            return CRM_Core_BAO_PaymentProcessor::getPayment( $ppID, $mode );
        } else if ( $type == 'obj' ) {
            $payment = CRM_Core_BAO_PaymentProcessor::getPayment( $ppID, $mode );
            return CRM_Core_Payment::singleton( $mode, $payment );
        }
        return null;
    }
}
