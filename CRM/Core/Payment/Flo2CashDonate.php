<?php 

/*
 +--------------------------------------------------------------------+
 | CiviCRM version 3.3                                                |
 +--------------------------------------------------------------------+
 | Copyright CiviCRM LLC (c) 2004-2007                                |
 +--------------------------------------------------------------------+
 | This file is a part of CiviCRM.                                    |
 |                                                                    |
 | CiviCRM is free software; you can copy, modify, and distribute it  |
 | under the terms of the GNU Affero General Public License           |
 | Version 3, 19 November 2007.                                       |
 |                                                                    |
 | CiviCRM is distributed in the hope that it will be useful, but     |
 | WITHOUT ANY WARRANTY; without even the implied warranty of         |
 | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.               |
 | See the GNU Affero General Public License for more details.        |
 |                                                                    |
 | You should have received a copy of the GNU Affero General Public   |
 | License along with this program; if not, contact CiviCRM LLC       |
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

require_once 'CRM/Core/Payment.php';

class CRM_Core_Payment_Flo2CashDonate extends CRM_Core_Payment { 
    /**
     * mode of operation: live or test
     *
     * @var object
     * @static
     */
    static protected $_mode = null;
    
    /** 
     * Constructor 
     *
     * @param string $mode the mode of operation: live or test
     * 
     * @return void 
     */ 
    function __construct( $mode, &$paymentProcessor ) {
        $this->_mode = $mode;
        $this->_paymentProcessor = $paymentProcessor;
    }
    
    /** 
     * This function checks to see if we have the right config values 
     * 
     * @return string the error message if any 
     * @public 
     */ 
    function checkConfig( ) {
        $config =& CRM_Core_Config::singleton( );
        $error = array( );
        
        if ( empty( $this->_paymentProcessor['user_name'] ) ) {
            $error[] = ts( 'Account ID is not set in the Administer CiviCRM &raquo; Payment Processor.' );
        }
        
        if ( ! empty( $error ) ) {
            return implode( '<p>', $error );
        } else {
            return null;
        }
    }
    
    function doDirectPayment( &$params ) {
        CRM_Core_Error::fatal( ts( 'This function is not implemented' ) );
    }
    
    /**  
     * Sets appropriate parameters for checking out to google
     *  
     * @param array $params  name value pair of contribution datat
     *  
     * @return void  
     * @access public 
     *  
     */  
    function doTransferCheckout( &$params, $component ) {
        $config =& CRM_Core_Config::singleton( );
        $component = strtolower( $component );
        
        $server_type  = ( $this->_mode == 'test' ) ? 'sandbox' : '';
        // return to appropriate page in system
        if ( $component == "event" ) {
            $returnURL = CRM_Utils_System::url( 'civicrm/event/register',
                                                "_qf_ThankYou_display=1&_qfKey={$params['qfKey']}", 
                                                true, null, false );
        } elseif ( $component == "contribute" ) {
            $returnURL = CRM_Utils_System::url( 'civicrm/contribute/transact',
                                                "_qf_ThankYou_display=1&_qfKey={$params['qfKey']}",
                                                true, null, false );
        }
        
        // could also use "$params['is_recur'] + 1"
        $donation_type = ( $params['is_recur'] ) ? 2 : 1 ;
        
        $frequencies = array( '1'  => 'day',
                              '2'  => 'week',
                              '7'  => 'month',
                              '13' => 'year' ) ;
        if ( !isset( $params['installments'] ) || empty( $params['installments'] ) ) {
            $params['installments'] = 0 ;
        }
        
        $notifyURL = 
            $config->userFrameworkResourceURL . 
            "extern/f2c_ipn.php?reset=1&contactID={$params['contactID']}" .
            "&contributionID={$params['contributionID']}" .
            "&module={$component}";

        $cancelURL = CIVICRM_UF_BASEURL . 'civicrm/contribute/transact?reset=1&id=' . $params['contributionPageID'] ;
        
        $form_vars = array( 
                           'Donation_ID'              => $params['contributionID'],
                           'First_Name'               => $params['first_name'],
                           'Last_Name'                => $params['last_name'],
                           'Amount'                   => $params['amount'],
                           'Donation_Type'            => $donation_type,
                           'Notification_URL'         => $notifyURL,
                           'Return_URL'               => $returnURL,
                           'Frequency_ID'             => array_search( $params['frequency_unit'], $frequencies ),
                           'Installment_Number'       => $params['installments'],
                           'Invoice_ID'               => $params['invoiceID'],
                           'Cancel_URL'               => $cancelURL,
                           //                      'account_id'               => $this->_paymentProcessor['user_name'],
                            );
        
        
        if ( empty( $form_vars['Frequency_ID'] ) ) {
            $form_vars['Frequency_ID'] = 1 ;
        }
        
        $url = $this->_paymentProcessor['url_site'] . '?' ;
        
        foreach ( $form_vars as $key => $value ) {
            $url .= "$key=" . urlencode($value) . '&' ;
        }
        
        // prob not required
        $url = trim($url,'&');
        
        /*
         print( '<pre>' ) ;
         print_r( $form_vars ) ;
         print_r( array( $params, $this ) ) ;
         die($url) ;
        */

        CRM_Utils_System::redirect( $url );
        exit( );
    }
}
?>
