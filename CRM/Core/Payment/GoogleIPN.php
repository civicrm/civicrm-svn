<?php 
 
/**
 * Copyright (C) 2006 Google Inc.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *      http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

 /* This is the response handler code that will be invoked every time
  * a notification or request is sent by the Google Server
  *
  * To allow this code to receive responses, the url for this file
  * must be set on the seller page under Settings->Integration as the
  * "API Callback URL'
  * Order processing commands can be sent automatically by placing these
  * commands appropriately
  *
  * To use this code for merchant-calculated feedback, this url must be
  * set also as the merchant-calculations-url when the cart is posted
  * Depending on your calculations for shipping, taxes, coupons and gift
  * certificates update parts of the code as required
  *
  */

require_once 'CRM/Core/Payment/BaseIPN.php';

define( 'GOOGLE_DEBUG_PP', 1 );

class CRM_Core_Payment_GoogleIPN extends CRM_Core_Payment_BaseIPN {

    /**
     * We only need one instance of this object. So we use the singleton
     * pattern and cache the instance in this variable
     *
     * @var object
     * @static
     */
    static private $_singleton = null;

    /**
     * mode of operation: live or test
     *
     * @var object
     * @static
     */
    static protected $_mode = null;
    
    static function retrieve( $name, $type, $object, $abort = true ) 
    {
        $value = CRM_Utils_Array::value( $name, $object );
        if ( $abort && $value === null ) {
            CRM_Core_Error::debug_log_message( "Could not find an entry for $name" );
            echo "Failure: Missing Parameter<p>";
            exit( );
        }

        if ( $value ) {
            if ( ! CRM_Utils_Type::validate( $value, $type ) ) {
                CRM_Core_Error::debug_log_message( "Could not find a valid entry for $name" );
                echo "Failure: Invalid Parameter<p>";
                exit( );
            }
        }

        return $value;
    }


    /** 
     * Constructor 
     * 
     * @param string $mode the mode of operation: live or test
     *
     * @return void 
     */ 
    function __construct( $mode, &$paymentProcessor ) {
        parent::__construct( );
        
        $this->_mode = $mode;
        $this->_paymentProcessor = $paymentProcessor;
    }

    /**  
     * The function gets called when a new order takes place.
     *  
     * @param xml   $dataRoot    response send by google in xml format
     * @param array $privateData contains the name value pair of <merchant-private-data>
     *  
     * @return void  
     *  
     */  
    function newOrderNotify( $dataRoot, $privateData, $component ) {
        $ids = $input = $params = array( );
        
        $input['component'] = strtolower($component);

        $ids['contact']          = self::retrieve( 'contactID'     , 'Integer', $privateData, true );
        $ids['contribution']     = self::retrieve( 'contributionID', 'Integer', $privateData, true );

        $ids['contributionRecur'] = $ids['contributionPage'] = null;
        if ( $input['component'] == "event" ) {
            $ids['event']       = self::retrieve( 'eventID'      , 'Integer', $privateData, true );
            $ids['participant'] = self::retrieve( 'participantID', 'Integer', $privateData, true );
            $ids['membership']  = null;
        } else {
            $ids['membership']          = self::retrieve( 'membershipID'  , 'Integer', $privateData, false );
            $ids['related_contact']     = self::retrieve( 'relatedContactID'  , 'Integer', $privateData, false );
            $ids['onbehalf_dupe_alert'] = self::retrieve( 'onBehalfDupeAlert'  , 'Integer', $privateData, false );
            $ids['contributionRecur']   = self::retrieve( 'contributionRecurID', 'Integer', $privateData, false );
        }

        if ( ! $this->validateData( $input, $ids, $objects ) ) {
            return false;
        }

        $input['invoice']    =  $privateData['invoiceID'];
        $input['newInvoice'] =  $dataRoot['google-order-number']['VALUE'];

        if ( $ids['contributionRecur'] ) {
            if ( $objects['contributionRecur']->invoice_id == $dataRoot['serial-number'] ) {
                CRM_Core_Error::debug_log_message( "This recurring payment has already been handled." );
                return;
            } else {
                CRM_Core_Error::debug_log_message( "Recurring payment initiated." );
                $recur =& $objects['contributionRecur'];

                // fix dates that already exist
                $dates = array( 'create', 'start', 'end', 'cancel', 'modified' );
                foreach ( $dates as $date ) {
                    $name = "{$date}_date";
                    if ( $recur->$name ) {
                        $recur->$name = CRM_Utils_Date::isoToMysql( $recur->$name );
                    }
                }
                $recur->invoice_id = $dataRoot['serial-number'];
                $recur->save( );

                if ( $objects['contribution']->contribution_status_id == 1 ) {
                    // create a contribution and then get it processed
                    $contribution = new CRM_Contribute_DAO_Contribution( );
                    $contribution->contact_id            = $ids['contact'];
                    $contribution->contribution_type_id  = $objects['contributionType']->id;
                    $contribution->contribution_page_id  = $objects['contribution']->contribution_page_id;
                    $contribution->contribution_recur_id = $ids['contributionRecur'];
                    $contribution->receive_date          = date( 'YmdHis' );
                    $contribution->currency              = $objects['contribution']->currency;
                    $contribution->payment_instrument_id = $objects['contribution']->payment_instrument_id;
                    $contribution->amount_level          = $objects['contribution']->amount_level;
                    $contribution->address_id            = $objects['contribution']->address_id;
                    $contribution->invoice_id            = $input['invoice'];
                    $contribution->total_amount          = $dataRoot['order-total']['VALUE'];
                    $objects['contribution'] = $contribution;
                    $input['newInvoice']     = $dataRoot['serial-number'];
                }
            }
        }

        // make sure the invoice is valid and matches what we have in the contribution record
        $contribution =& $objects['contribution'];

        if ( $contribution->invoice_id != $input['invoice'] ) {
            CRM_Core_Error::debug_log_message( "Invoice values dont match between database and IPN request" );
            echo "Failure: Invoice values dont match between database and IPN request<p>";
            return;
        }

        // lets replace invoice-id with google-order-number because thats what is common and unique 
        // in subsequent calls or notifications sent by google.
        $contribution->invoice_id = $input['newInvoice'];

        $input['amount'] = $dataRoot['order-total']['VALUE'];
        
        if ( $contribution->total_amount != $input['amount'] ) {
            CRM_Core_Error::debug_log_message( "Amount values dont match between database and IPN request" );
            echo "Failure: Amount values dont match between database and IPN request<p>";
            return;
        }

        if ( ! $this->getInput( $input, $ids ) ) {
            return false;
        }

        require_once 'CRM/Core/Transaction.php';
        $transaction = new CRM_Core_Transaction( );
        
        // fix for CRM-2842
        // if ( ! $this->createContact( $input, $ids, $objects ) ) {
        //     return false;
        // }

        // check if contribution is already completed, if so we ignore this ipn
        if ( $contribution->contribution_status_id == 1 ) {
            CRM_Core_Error::debug_log_message( "returning since contribution has already been handled" );
            return;
        } else {
            /* Since trxn_id hasn't got any use here, 
             * lets make use of it by passing the eventID/membershipTypeID to next level.
             * And change trxn_id to google-order-number before finishing db update */
            if ( $ids['event'] ) {
                $contribution->trxn_id =
                    $ids['event']       . CRM_Core_DAO::VALUE_SEPARATOR .
                    $ids['participant'] ;
            } else if ( $ids['membership'] ) {
                $contribution->trxn_id = 
                    $ids['membership'] . CRM_Core_DAO::VALUE_SEPARATOR .
                    $ids['related_contact'] . CRM_Core_DAO::VALUE_SEPARATOR .
                    $ids['onbehalf_dupe_alert'];
            }
        }

        // CRM_Core_Error::debug_var( 'c', $contribution );
        $contribution->save( );
        $transaction->commit( );

        return true;
    }
    
    /**  
     * The function gets called when the state(CHARGED, CANCELLED..) changes for an order
     *  
     * @param string $status      status of the transaction send by google
     * @param array  $privateData contains the name value pair of <merchant-private-data>
     *  
     * @return void  
     *  
     */  
    function orderStateChange( $status, $dataRoot, $component ) {
        $input = $objects = $ids = array( );

        $input['component'] = strtolower($component);

        // CRM_Core_Error::debug_var( "$status, $component", $dataRoot );
        $orderNo   = $dataRoot['google-order-number']['VALUE'];
        
        require_once 'CRM/Contribute/DAO/Contribution.php';
        $contribution = new CRM_Contribute_DAO_Contribution( );
        $contribution->invoice_id = $orderNo;
        if ( ! $contribution->find( true ) ) {
            CRM_Core_Error::debug_log_message( "Could not find contribution record with invoice id: $orderNo" );
            echo "Failure: Could not find contribution record with invoice id: $orderNo <p>";
            exit( );
        }

        // Google sends the charged notification twice.
        // So to make sure, code is not executed again.
        if ( !$contribution->contribution_recur_id && $contribution->contribution_status_id == 1 ) {
            CRM_Core_Error::debug_log_message( "Contribution already handled (ContributionID = $contribution)." );
            return;
        }
        if ( $contribution->contribution_recur_id ) {
            $contribution = new CRM_Contribute_DAO_Contribution( );
            $contribution->invoice_id = $dataRoot['serial-number'];
            $contribution->find( true );
            $orderNo = $dataRoot['serial-number'];
        }
        
        $objects['contribution'] =& $contribution;
        $ids['contribution']     =  $contribution->id;
        $ids['contact']          =  $contribution->contact_id;

        $ids['event'] = $ids['participant'] = $ids['membership'] = null;
        $ids['contributionRecur'] = $ids['contributionPage'] = null;

        if ( $input['component'] == "event" ) {
            list( $ids['event'], $ids['participant'] ) =
                explode( CRM_Core_DAO::VALUE_SEPARATOR,
                         $contribution->trxn_id );
            
        } else {
            list( $ids['membership'], $ids['related_contact'], $ids['onbehalf_dupe_alert'] ) = 
                explode( CRM_Core_DAO::VALUE_SEPARATOR,
                         $contribution->trxn_id );

            foreach ( array('membership', 'related_contact', 'onbehalf_dupe_alert') as $fld ) {
                if ( ! is_numeric( $ids[$fld] ) ) {
                    unset( $ids[$fld] );
                }
            }
        }

        $this->loadObjects( $input, $ids, $objects, true );

        require_once 'CRM/Core/Transaction.php';
        $transaction = new CRM_Core_Transaction( );

        // CRM_Core_Error::debug_var( 'c', $contribution );        
        if ( $status == 'PAYMENT_DECLINED' || 
             $status == 'CANCELLED_BY_GOOGLE' || 
             $status == 'CANCELLED' ) {
            return $this->failed( $objects, $transaction );
        }

        $input['amount']     = $contribution->total_amount;
        $input['fee_amount'] = null;
        $input['net_amount'] = null;
        $input['trxn_id']    = $orderNo;
        $input['is_test']    = $contribution->is_test;

        $this->completeTransaction( $input, $ids, $objects, $transaction );

        if ( $ids['contributionRecur'] ) {
            $recur =& $objects['contributionRecur'];
            $contributionCount = CRM_Core_DAO::singleValueQuery( "SELECT count(*) FROM civicrm_contribution WHERE contribution_recur_id = {$ids['contributionRecur']}" );
            if ( $contributionCount >= $recur->installments ) {
                require_once 'CRM/Contribute/PseudoConstant.php';
                $contributionStatus = CRM_Contribute_PseudoConstant::contributionStatus( null, 'name' );

                $recur->end_date               = $now;
                $recur->modified_date          = $now;
                $recur->contribution_status_id = array_search( 'Completed', $contributionStatus );
                $recur->save( );

                $autoRenewMembership = false;
                if ( $recur->id && 
                     isset( $ids['membership'] ) && $ids['membership'] ) {
                    $autoRenewMembership = true;
                }
                
                //send recurring Notification email for user
                CRM_Contribute_BAO_ContributionPage::recurringNofify( CRM_Core_Payment::RECURRING_PAYMENT_END, 
                                                                      $ids['contact'], 
                                                                      $ids['contributionPage'], 
                                                                      $recur,
                                                                      $autoRenewMembership );
            }
        }
    }

    /**  
     * singleton function used to manage this object  
     *  
     * @param string $mode the mode of operation: live or test
     *  
     * @return object  
     * @static  
     */  
    static function &singleton( $mode, $component, &$paymentProcessor ) {
        if ( self::$_singleton === null ) {
            self::$_singleton = new CRM_Core_Payment_GoogleIPN( $mode, $paymentProcessor );
        }
        return self::$_singleton;
    }
    
    /**  
     * The function retrieves the amount the contribution is for, based on the order-no google sends
     *  
     * @param int $orderNo <order-total> send by google
     *  
     * @return amount  
     * @access public 
     */  
    function getAmount($orderNo) {
        require_once 'CRM/Contribute/DAO/Contribution.php';
        $contribution = new CRM_Contribute_DAO_Contribution( );
        $contribution->invoice_id = $orderNo;
        if ( ! $contribution->find( true ) ) {
            CRM_Core_Error::debug_log_message( "Could not find contribution record with invoice id: $orderNo" );
            echo "Failure: Could not find contribution record with invoice id: $orderNo <p>";
            exit( );
        }
        return $contribution->total_amount;
    }

    /**  
     * The function returns the component(Event/Contribute..), given the google-order-no and merchant-private-data
     *  
     * @param xml     $xml_response   response send by google in xml format
     * @param array   $privateData    contains the name value pair of <merchant-private-data>
     * @param int     $orderNo        <order-total> send by google
     * @param string  $root           root of xml-response
     *  
     * @return array context of this call (test, module, payment processor id)
     * @static  
     */  
    static function getContext($xml_response, $privateData, $orderNo, $root) {
        require_once 'CRM/Contribute/DAO/Contribution.php';

        $isTest = null;
        $module = null;
        if ($root == 'new-order-notification') {
            $contributionID   = $privateData['contributionID'];
            $contribution     = new CRM_Contribute_DAO_Contribution( );
            $contribution->id = $contributionID;
            if ( ! $contribution->find( true ) ) {
                CRM_Core_Error::debug_log_message( "Could not find contribution record: $contributionID" );
                echo "Failure: Could not find contribution record for $contributionID<p>";
                exit( );
            }
            if (stristr($contribution->source, ts('Online Contribution'))) {
                $module = 'Contribute';
            } elseif (stristr($contribution->source, ts('Online Event Registration'))) {
                $module = 'Event';
            }
            $isTest = $contribution->is_test;
        } else {
            $contribution = new CRM_Contribute_DAO_Contribution( );
            $contribution->invoice_id = $orderNo;
            if ( ! $contribution->find( true ) ) {
                CRM_Core_Error::debug_log_message( "Could not find contribution record with invoice id: $orderNo" );
                echo "Failure: Could not find contribution record with invoice id: $orderNo <p>";
                exit( );
            }
            if (stristr($contribution->source, ts('Online Contribution'))) {
                $module = 'Contribute';
            } elseif (stristr($contribution->source, ts('Online Event Registration'))) {
                $module = 'Event';
            }
            $isTest = $contribution->is_test;
        }

        if ( !$privateData['contributionRecurID'] && $contribution->contribution_status_id == 1 ) {
            //contribution already handled.
            return;
        }

        if ( $module == 'Contribute' ) {
            if ( ! $contribution->contribution_page_id ) {
                CRM_Core_Error::debug_log_message( "Could not find contribution page for contribution record: $contributionID" );
                echo "Failure: Could not find contribution page for contribution record: $contributionID<p>";
                exit( );
            }

            // get the payment processor id from contribution page
            $paymentProcessorID = CRM_Core_DAO::getFieldValue( 'CRM_Contribute_DAO_ContributionPage',
                                                               $contribution->contribution_page_id,
                                                               'payment_processor_id' );
        } else {
            if ($root == 'new-order-notification') {
                $eventID = $privateData['eventID'];
            } else {
                list( $eventID, $participantID ) =
                    explode( CRM_Core_DAO::VALUE_SEPARATOR,
                             $contribution->trxn_id );
            }
            if ( !$eventID ) {
                CRM_Core_Error::debug_log_message( "Could not find event ID" );
                echo "Failure: Could not find eventID<p>";
                exit( );
            }

            // we are in event mode
            // make sure event exists and is valid
            require_once 'CRM/Event/DAO/Event.php';
            $event = new CRM_Event_DAO_Event( );
            $event->id = $eventID;
            if ( ! $event->find( true ) ) {
                CRM_Core_Error::debug_log_message( "Could not find event: $eventID" );
                echo "Failure: Could not find event: $eventID<p>";
                exit( );
            }
            
            // get the payment processor id from contribution page
            $paymentProcessorID = $event->payment_processor_id;
        }

        if ( ! $paymentProcessorID ) {
            CRM_Core_Error::debug_log_message( "Could not find payment processor for contribution record: $contributionID" );
            echo "Failure: Could not find payment processor for contribution record: $contributionID<p>";
            exit( );
        }

        return array( $isTest, $module, $paymentProcessorID );
    }

    /**
     * This method is handles the response that will be invoked (from extern/googleNotify) every time
     * a notification or request is sent by the Google Server.
     *
     */
    static function main( $xml_response ) 
    {
        require_once('Google/library/googleresponse.php');
        require_once('Google/library/googlerequest.php');
        require_once('Google/library/googlemerchantcalculations.php');
        require_once('Google/library/googleresult.php');
        require_once('Google/library/xml-processing/gc_xmlparser.php');
        
        $config = CRM_Core_Config::singleton();
               
        // Retrieve the XML sent in the HTTP POST request to the ResponseHandler
        if (get_magic_quotes_gpc()) {
            $xml_response = stripslashes($xml_response);
        }

        require_once 'CRM/Utils/System.php';
        $headers = CRM_Utils_System::getAllHeaders();

        if ( GOOGLE_DEBUG_PP ) {
            CRM_Core_Error::debug_var( 'RESPONSE', $xml_response, true, true, 'Google' );
        }
        
        // Retrieve the root and data from the xml response
        $response = new GoogleResponse( );
        list($root, $data) = $response->GetParsedXML($xml_response);

        // lets retrieve the private-data & order-no
        $privateData = $data[$root]['shopping-cart']['merchant-private-data']['VALUE'];
        if ( !$privateData ) {
            $privateData = $data[$root]['order-summary']['shopping-cart']['merchant-private-data']['VALUE'];
        }
        $privateData = $privateData ? self::stringToArray($privateData) : '';
        $orderNo     = $data[$root]['google-order-number']['VALUE'];
        $serial      = $data[$root]['serial-number'];
        
        list( $mode, $module, $paymentProcessorID ) = self::getContext($xml_response, $privateData, $orderNo, $root);
        if ( ! $paymentProcessorID ) {
            // if we didn't die and processor is not detected, payment is already complete or sth wrong.
            $response->SendAck($serial);
        }
        $mode = $mode ? 'test' : 'live';

        require_once 'CRM/Core/BAO/PaymentProcessor.php';
        $paymentProcessor = CRM_Core_BAO_PaymentProcessor::getPayment( $paymentProcessorID,
                                                                       $mode );
        $merchant_id  = $paymentProcessor['user_name'];
        $merchant_key = $paymentProcessor['password'];
        $response->SetMerchantAuthentication($merchant_id, $merchant_key);

        $server_type = ( $mode == 'test' ) ? 'sandbox' : 'production';
        $request = new GoogleRequest($merchant_id, $merchant_key, $server_type);

        $ipn = self::singleton( $mode, $module, $paymentProcessor );

        if ( GOOGLE_DEBUG_PP ) {
            CRM_Core_Error::debug_var( 'RESPONSE-ROOT', $response->root, true, true, 'Google' );
        }

        //Check status and take appropriate action
        $status = $response->HttpAuthentication($headers);
        
        switch ($root) {
            
        case "request-received":
        case "error":
        case "diagnosis":
        case "checkout-redirect":
        case "merchant-calculation-callback":
            break;

        case "new-order-notification": {
            $response->SendAck($serial, false);
            $ipn->newOrderNotify($data[$root], $privateData, $module);
            break;
        }

        case "order-state-change-notification": {
            $response->SendAck($serial, false);
            $new_financial_state = $data[$root]['new-financial-order-state']['VALUE'];
            $new_fulfillment_order = $data[$root]['new-fulfillment-order-state']['VALUE'];
            
            switch($new_financial_state) {

            case 'CHARGEABLE':
                break;

            case 'CHARGED':
            case 'PAYMENT_DECLINED':
            case 'CANCELLED':
            case 'CANCELLED_BY_GOOGLE':
                $ipn->orderStateChange($new_financial_state, $data[$root], $module);
                break;

            case 'REVIEWING':
            case 'CHARGING':
                break;

            default:
                break;
            }
            break;
        }

        case "authorization-amount-notification": {
            $response->SendAck($serial, false);
            $new_financial_state = $data[$root]['order-summary']['financial-order-state']['VALUE'];
            $new_fulfillment_order = $data[$root]['order-summary']['fulfillment-order-state']['VALUE'];
            
            switch($new_financial_state) {

            case 'CHARGEABLE':
                $request->SendProcessOrder($data[$root]['google-order-number']['VALUE']);
                $request->SendChargeOrder($data[$root]['google-order-number']['VALUE'],'');
                break;

            case 'CHARGED':
            case 'PAYMENT_DECLINED':
            case 'CANCELLED':
                break;

            case 'REVIEWING':
            case 'CHARGING':
            case 'CANCELLED_BY_GOOGLE':
                break;

            default:
                break;
            }
            break;
        }

        case "charge-amount-notification":
        case "chargeback-amount-notification":
        case "refund-amount-notification":
        case "risk-information-notification":
            $response->SendAck($serial);
            break;

        default:
            break;

        }
        
    }

    function getInput( &$input, &$ids ) {
        if ( ! $this->getBillingID( $ids ) ) {
            return false;
        }

        $billingID = $ids['billing'];
        $lookup = array( "first_name"                  => 'contact-name',
                         // "last-name" not available with google (every thing in contact-name)
                         "last_name"                   => 'last_name' , 
                         "street_address-{$billingID}" => 'address1',
                         "city-{$billingID}"           => 'city',
                         "state-{$billingID}"          => 'region',
                         "postal_code-{$billingID}"    => 'postal-code',
                         "country-{$billingID}"        => 'country-code' );

        foreach ( $lookup as $name => $googleName ) {
            $value = $dataRoot['buyer-billing-address'][$googleName]['VALUE'];
            $input[$name] = $value ? $value : null;
        }
        return true;
    }

    /**
     * Converts the comma separated name-value pairs in <merchant-private-data> 
     * to an array of name-value pairs.
     */
    static function stringToArray($str) {
        $vars = $labels = array();
        $labels = explode(',', $str);
        foreach ($labels as $label) {
            $terms = explode('=', $label);
            $vars[$terms[0]] = $terms[1];
        }
        return $vars;
    }

}


