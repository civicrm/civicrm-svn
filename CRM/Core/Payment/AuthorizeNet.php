<?php

/*
 * Copyright (C) 2007
 * Licensed to CiviCRM under the Academic Free License version 3.0.
 *
 * Written and contributed by Ideal Solution, LLC (http://www.idealso.com)
 *
 */

/**
 *
 * @package CRM
 * @author Marshal Newrock <marshal@idealso.com>
 * $Id$
 */

/* NOTE:
 * When looking up response codes in the Authorize.Net API, they
 * begin at one, so always delete one from the "Position in Response"
 */

require_once 'CRM/Core/Payment.php';

class CRM_Core_Payment_AuthorizeNet extends CRM_Core_Payment {
    const
        CHARSET = 'iso-8859-1';

    const AUTH_APPROVED = 1;
    const AUTH_DECLINED = 2;
    const AUTH_ERROR = 3;

    static protected $_mode = null;

    static protected $_params = array();
    
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

        $config =& CRM_Core_Config::singleton();
        $this->_setParam( 'apiLogin'   , $paymentProcessor['user_name'] );
        $this->_setParam( 'paymentKey' , $paymentProcessor['password']  );
        $this->_setParam( 'paymentType', 'AIM' );
        $this->_setParam( 'md5Hash'    , $paymentProcessor['signature'] );
        
        $this->_setParam( 'emailCustomer', 'TRUE' );
        $this->_setParam( 'timestamp', time( ) );
        srand( time( ) );
        $this->_setParam( 'sequence', rand( 1, 1000 ) );
    }

    /**
     * Submit a payment using Advanced Integration Method
     *
     * @param  array $params assoc array of input parameters for this transaction
     * @return array the result in a nice formatted array (or an error object)
     * @public
     */
    function doDirectPayment ( &$params ) {
        if ( ! defined( 'CURLOPT_SSLCERT' ) ) {
            return self::error( 9001, 'Authorize.Net requires curl with SSL support' );
        }

        foreach ( $params as $field => $value ) {
            $this->_setParam( $field, $value );
        }

        if ( $params['is_recur'] && $params['installments'] > 1 ) {
            return $this->doRecurPayment( $params );
        }

        $postFields = array( );
        $authorizeNetFields = $this->_getAuthorizeNetFields( );
        foreach ( $authorizeNetFields as $field => $value ) {
            $postFields[] = $field . '=' . urlencode( $value );
        }

        $submit = curl_init( $this->_paymentProcessor['url_site'] );

        if ( !$submit ) {
            return self::error(9002, 'Could not initiate connection to payment gateway');
        }

        curl_setopt( $submit, CURLOPT_POST, true );
        curl_setopt( $submit, CURLOPT_RETURNTRANSFER, true );
        curl_setopt( $submit, CURLOPT_POSTFIELDS, implode( '&', $postFields ) );

        $response = curl_exec( $submit );

        if (!$response) {
            return self::error( curl_errno($submit), curl_error($submit) );
        }

        curl_close( $submit );

        $response_fields = $this->explode_csv( $response );

        // check gateway MD5 response
        if ( ! $this->checkMD5 ( $response_fields[37], $response_fields[6], $response_fields[9] ) ) {
            return self::error( 9003, 'MD5 Verification failed' );
        }

        // check for application errors
        // TODO:
        // AVS, CVV2, CAVV, and other verification results
        if ( $response_fields[0] != self::AUTH_APPROVED ) {
            $errormsg = $result_fields[2] . ' ' . $response_fields[3];
            return self::error( $response_fields[1], $errormsg );
        }

        // Success

        // test mode always returns trxn_id = 0
        if ( $this->_mode == 'test' ) {
            $query = "SELECT MAX(trxn_id) FROM civicrm_contribution WHERE trxn_id LIKE 'test%'";
            $p = array( );
            $trxn_id = strval( CRM_Core_Dao::singleValueQuery( $query, $p ) );
            $trxn_id = str_replace( 'test', '', $trxn_id );
            $trxn_id = intval($trxn_id) + 1;
            $params['trxn_id'] = sprintf('test%08d', $trxn_id);
        }
        else {
            $params['trxn_id'] = $response_fields[6];
        }
        $params['gross_amount'] = $response_fields[9];
        // TODO: include authorization code?
        return $params;

    }

    /**
     * Submit an Automated Recurring Billing subscription
     *
     * @param  array $params assoc array of input parameters for this transaction
     * @return array the result in a nice formatted array (or an error object)
     * @public
     */
    function doRecurPayment( &$params ) {
        $session =& CRM_Core_Session::singleton( );
        $template =& CRM_Core_Smarty::singleton( );

        $intervalLength = $this->_getParam('frequency_interval');
        $intervalUnit = $this->_getParam('frequency_unit');
        if ( $intervalUnit == 'week' ) {
            $intervalLength *= 7;
            $intervalUnit = 'days';
        } elseif ( $intervalUnit == 'year' ) {
            $intervalLength *= 12;
            $intervalUnit = 'months';
        } elseif ( $intervalUnit == 'day' ) {
            $intervalUnit = 'days';
        } elseif ( $intervalUnit == 'month' ) {
            $intervalUnit = 'months';
        }

        // interval cannot be less than 7 days or more than 1 year
        if ( $intervalUnit == 'days' ) {
            if ( $intervalLength < 7 ) {
                return self::error( 9001, 'Payment interval must be at least one week' );
            } elseif ( $intervalLength > 365 ) {
                return self::error( 9001, 'Payment interval may not be longer than one year' );
            }
        }
        elseif ( $intervalUnit == 'months' ) {
            if ( $intervalLength < 1 ) {
                return self::error( 9001, 'Payment interval must be at least one week' );
            } elseif ( $intervalLength > 12 ) {
                return self::error( 9001, 'Payment interval may not be longer than one year' );
            }
        }

        $template->assign( 'intervalLength', $intervalLength );
        $template->assign( 'intervalUnit', $intervalUnit );

        $template->assign( 'apiLogin', $this->_getParam( 'apiLogin' ) );
        $template->assign( 'paymentKey', $this->_getParam( 'paymentKey' ) );
        $template->assign( 'refId', substr( $this->_getParam( 'invoiceID' ), 0, 20 ) );
        // insert page title as name

        $template->assign( 'startDate', date('Y-m-d') );
        $template->assign( 'totalOccurrences', $this->_getParam('installments') );

        $template->assign( 'amount', $this->_getParam('amount') );

        $template->assign( 'cardNumber', $this->_getParam('credit_card_number') );
        $exp_month = str_pad( $this->_getParam( 'month' ), 2, '0', STR_PAD_LEFT );
        $exp_year = $this->_getParam( 'year' );
        $template->assign( 'expirationDate', $exp_year . '-' . $exp_month );

        $template->assign( 'description', $session->get( 'title' ) );

        $template->assign( 'email', $this->_getParam('email') );
        $template->assign( 'billingFirstName', $this->_getParam('billing_first_name') );
        $template->assign( 'billingLastName', $this->_getParam('billing_last_name') );
        $template->assign( 'billingAddress', $this->_getParam('street_address') );
        $template->assign( 'billingCity', $this->_getParam('city') );
        $template->assign( 'billingState', $this->_getParam('state_province') );
        $template->assign( 'billingZip', $this->_getParam('postal_code') );
        $template->assign( 'billingCountry', $this->_getParam('country') );

        $arbXML = $template->fetch( 'CRM/Contribute/Form/Contribution/AuthorizeNetARB.tpl' );

        // submit to authorize.net
        $submit = curl_init( $this->_paymentProcessor['url_recur'] );
        if ( !$submit ) {
            return self::error(9002, 'Could not initiate connection to payment gateway');
        }

        curl_setopt($submit, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($submit, CURLOPT_HTTPHEADER, Array("Content-Type: text/xml"));
        curl_setopt($submit, CURLOPT_HEADER, 1);
        curl_setopt($submit, CURLOPT_POSTFIELDS, $arbXML);
        curl_setopt($submit, CURLOPT_POST, 1);
        curl_setopt($submit, CURLOPT_SSL_VERIFYPEER, 0);
        
        $response = curl_exec($submit);

        if ( !$response ) {
            return self::error( curl_errno($submit), curl_error($submit) );
        }

        curl_close( $submit );

        $responseFields = $this->_ParseArbReturn( $response );

        if ( $responseFields['resultCode'] == 'Error' ) {
            return self::error( $responseFields['code'], $responseFields['text'] );
        }

        $params['trxn_id'] = $responseFields['subscriptionId'];
        return $params;
    }

    function _getAuthorizeNetFields ( ) {
        $fields = array();
        $fields['x_login'] = $this->_getParam( 'apiLogin' );
        $fields['x_tran_key'] = $this->_getParam( 'paymentKey' );
        $fields['x_email_customer'] = $this->_getParam( 'emailCustomer' );
        $fields['x_first_name'] = $this->_getParam( 'billing_first_name' );
        $fields['x_last_name'] = $this->_getParam( 'billing_last_name' );
        $fields['x_address'] = $this->_getParam( 'street_address' );
        $fields['x_city'] = $this->_getParam( 'city' );
        $fields['x_state'] = $this->_getParam( 'state' );
        $fields['x_zip'] = $this->_getParam( 'postal_code' );
        $fields['x_country'] = $this->_getParam( 'country' );
        $fields['x_customer_ip'] = $this->_getParam( 'ip_address' );
        $fields['x_email'] = $this->_getParam( 'email' );
        $fields['x_invoice_num'] = substr( $this->_getParam( 'invoiceID' ), 0, 20 );
        $fields['x_amount'] = $this->_getParam( 'amount' );
        $fields['x_currency_code'] = $this->_getParam( 'currencyID' );

        if ( $this->_getParam( 'paymentType' ) == 'AIM' ) {
            $fields['x_relay_response'] = 'FALSE';
            // request response in CSV format
            $fields['x_delim_data'] = 'TRUE';
            $fields['x_delim_char'] = ',';
            $fields['x_encap_char'] = '"';
            // cc info
            $fields['x_card_num'] = $this->_getParam( 'credit_card_number' );
            $fields['x_card_code'] = $this->_getParam( 'cvv2' );
            $exp_month = str_pad( $this->_getParam( 'month' ), 2, '0', STR_PAD_LEFT );
            $exp_year = $this->_getParam( 'year' );
            $fields['x_exp_date'] = "$exp_month/$exp_year";
        }

        //elseif ( $this->_getParam( 'paymentType' ) == 'SIM' ) {
        //    $fields['x_relay_response'] = 'TRUE';
        //    $fields['x_fp_hash'] = '';
        //    $fields['x_fp_sequence'] = '';
        //    $fields['x_fp_timestamp'] = '';
        //}

        if ( $this->_mode != 'live' ) {
            $fields['x_test_request'] = 'TRUE';
        }

        return $fields;

    }

    /**
     * Generate HMAC_MD5
     * @param string $key
     * @param string $data
     *
     * @return string the HMAC_MD5 encoding string
     **/
    function hmac( $key, $data ) {
        if ( function_exists( 'mhash' ) ) {
            // Use PHP mhash extension
            return ( bin2hex( mhash( MHASH_MD5, $data, $key ) ) );
        }

        else {
            // RFC 2104 HMAC implementation for php.
            // Creates an md5 HMAC.
            // Eliminates the need to install mhash to compute a HMAC
            // Hacked by Lance Rushing
            $b = 64; // byte length for md5
            if (strlen($key) > $b) {
                $key = pack("H*",md5($key));
            }
            $key  = str_pad($key, $b, chr(0x00));
            $ipad = str_pad('', $b, chr(0x36));
            $opad = str_pad('', $b, chr(0x5c));
            $k_ipad = $key ^ $ipad ;
            $k_opad = $key ^ $opad;
            return md5($k_opad  . pack("H*",md5($k_ipad . $data)));
        }
    }

    /**
     * Check the gateway MD5 response to make sure that this is a proper
     * gateway response
     *
     * @param string $responseMD5 MD5 hash generated by the gateway
     * @param string $transaction_id Transaction id generated by the gateway
     * @param string $amount Purchase amount
     *
     * @return bool
     */
    function checkMD5 ( $responseMD5, $transaction_id, $amount ) {
        // cannot check if no MD5 hash
        $md5Hash = $this->_getParam( 'md5Hash' );
        if ( $md5Hash == '' ) {
            return true;
        }
        $loginid = $this->_getParam( 'apiLogin' );
        $result = strtoupper ( md5( $md5Hash . $loginid . $transaction_id . $amount ) );
        if ( $result == $responseMD5 ) {
            return true;
        }
        else {
            return false;
        }
    }

    /**
     * Calculate and return the transaction fingerprint
     *
     * @return string fingerprint
     **/
    function CalculateFP () {
        $x_tran_key = $this->_getParam( 'paymentKey' );
        $loginid = $this->_getParam( 'apiLogin' );
        $sequence = $this->_getParam( 'sequence' );
        $timestamp = $this->_getParam( 'timestamp' );
        $amount = $this->_getParam( 'amount' );
        $currency = $this->_getParam( 'currencyID' );
        $transaction = "$loginid^$sequence^$timestamp^$amount^$currency";
        return $this->hmac( $x_tran_key, $transaction );
    }

    /**
     * Split a CSV file.  Requires , as delimiter and " as enclosure.
     * Based off notes from http://php.net/fgetcsv
     *
     * @param string $data a single CSV line
     * @return array CSV fields
     */
    function explode_csv ( $data ) {
        $data = trim( $data );
        //make it easier to parse fields with quotes in them
        $data = str_replace( '""', "''", $data );
        $fields = array( );

        while ( $data != '' ) {
            $matches = array( );
            if ( $data[0] == '"' ) {
                // handle quoted fields
                preg_match( '/^"(([^"]|\\")*?)",?(.*)$/', $data, $matches );

                $fields[] = str_replace( "''", '"', $matches[1] );
                $data = $matches[3];
            }
            else {
                preg_match( '/^([^,]*),?(.*)$/', $data, $matches );

                $fields[] = $matches[1];
                $data = $matches[2];
            }
        }

        return $fields;
    }

    /**
     * Extract variables from returned XML
     *
     * Function is from Authorize.Net sample code, and used for PHP4
     * compatibility, to prevent the requirement of XML functions.
     *
     * @param string $content XML reply from Authorize.Net
     * @return array refId, resultCode, code, text, subscriptionId
     */
    function _parseArbReturn( $content ) {
        $refId = $this->_substring_between($content,'<refId>','</refId>');
        $resultCode = $this->_substring_between($content,'<resultCode>','</resultCode>');
        $code = $this->_substring_between($content,'<code>','</code>');
        $text = $this->_substring_between($content,'<text>','</text>');
        $subscriptionId = $this->_substring_between($content,'<subscriptionId>','</subscriptionId>');
        return array ($refId, $resultCode, $code, $text, $subscriptionId);
    }

    /**
     * Helper function for _parseArbReturn
     *
     * Function is from Authorize.Net sample code, and used for PHP4
     * compatibility
     */
    function _substring_between( &$haystack, $start, $end ) {
        if (strpos($haystack,$start) === false || strpos($haystack,$end) === false) {
            return false;
        } else {
            $start_position = strpos($haystack,$start)+strlen($start);
            $end_position = strpos($haystack,$end);
            return substr($haystack,$start_position,$end_position-$start_position);
        }
    }


    /**
     * Get the value of a field if set
     *
     * @param string $field the field
     * @return mixed value of the field, or empty string if the field is
     * not set
     */
    function _getParam ( $field ) {
        if ( isset( $this->_params[$field] ) ) {
            return $this->_params[$field];
        }
        else {
            return '';
        }
    }

    function &error ( $errorCode = null, $errorMessage = null ) {
        $e =& CRM_Core_Error::singleton( );
        if ( $errorCode ) {
            $e->push( $errorCode, 0, null, $errorMessage );
        }
        else {
            $e->push( 9001, 0, null, 'Unknown System Error.' );
        }
        return $e;
    }

    /**
     * Set a field to the specified value.  Value must be a scalar (int,
     * float, string, or boolean)
     *
     * @param string $field
     * @param mixed $value
     * @return bool false if value is not a scalar, true if successful
     */ 
    function _setParam ( $field, $value ) {
        if ( ! is_scalar($value) ) {
            return false;
        }
        else {
            $this->_params[$field] = $value;
        }
    }

    /**
     * This function checks to see if we have the right config values 
     *
     * @param  string $mode the mode we are operating in (live or test)
     *
     * @return string the error message if any
     * @public
     */
    function checkConfig( $mode ) {
        $error = array();
        if ( empty( $this->_paymentProcessor['user_name'] ) ) {
            $error[] = ts( 'APILogin is not set for this payment processor' );
        }

        if ( empty( $this->_paymentProcessor['password'] ) ) {
            $error[] = ts( 'Key is not set for this payment processor' );
        }

        if ( ! empty( $error ) ) {
            return implode( '<p>', $error );
        } else {
            return null;
        }
    }

}         
