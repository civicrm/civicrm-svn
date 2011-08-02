<?php
/*
 +--------------------------------------------------------------------+
 | CiviCRM version 3.4                                                |
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

class CRM_Core_Payment_AuthorizeNetTest extends CiviUnitTestCase 
{
    function get_info( ) 
    {
        return array(
                     'name'        => 'Authorize.net processing',
                     'description' => 'Test Authorize.ne methods.',
                     'group'       => 'Payment Processor Tests',
                     );
    }
   
    function setUp( ) 
    {
        parent::setUp();
        require_once 'CRM/Core/Payment/AuthorizeNet.php';
        require_once 'CRM/Core/BAO/PaymentProcessorType.php';
        $paymentProcessor = array('user_name' => '5Z3f4eZ3v',
                                  'password' => '8CM3DVfy4z67vG4y',
                                  'signature' => 'signature',
                                  'url_recur' => 'https://apitest.authorize.net/xml/v1/request.api');      
        
        $values = array('is_active' => 1);
        $this->processor = new CRM_Core_Payment_AuthorizeNet( 'Contribute', $paymentProcessor ) ;                               

    }
    
    /**
     * create a single post dated payment as a recurring transaction.
     * 
     * Test works but not both due to some form of caching going on in the SmartySingleton 
     */
    function testCreateSingleNowDated( )
    {
        $params = array(     
                        'qfKey' => '08ed21c7ca00a1f7d32fff2488596ef7_4454',
                        'hidden_CreditCard' => 1,
                        'billing_first_name' => 'Frodo',
                        'billing_middle_name' => "",
                        'billing_last_name' => 'Baggins',
                        'billing_street_address-5' => '8 Hobbitton Road',
                        'billing_city-5'  => 'The Shire',
                        'billing_state_province_id-5' => 1012,
                        'billing_postal_code-5' => 5010,
                        'billing_country_id-5' => 1228,
                        'credit_card_number' => '4007000000027',
                        'cvv2' => 123,
                        'credit_card_exp_date' => Array(
                                                        'M' => 10,
                                                        'Y' => 2019
                                                        ),
                        
                        'credit_card_type' => 'Visa',
                        'is_recur' => 1,
                        'frequency_interval' => 1,
                        'frequency_unit' => 'month',
                        'installments' => 1,
                        'contribution_type_id' => $this->ids['contribution_type'],
                        'is_email_receipt' => 1,
                        'from_email_address' => 'gandalf',
                        'receive_date_time' => '11:57PM',
                        'receipt_date_time' => '',
                        'payment_processor_id' => $this->ids['payment_processor'],
                        'price_set_id' => '',
                        'total_amount' => 7,
                        'currency' => 'USD',
                        'source' => "Mordor",
                        'soft_credit_to' => '', 
                        'soft_contact_id' =>  '',
                        'billing_state_province-5' => 'IL',
                        'state_province-5' => 'IL',
                        'billing_country-5' => 'US',
                        'country-5' => 'US',
                        'year' => 2019,
                        'month' => 10,
                        'ip_address' => '127.0.0.1',
                        'amount' => 7,
                        'amount_level' => 0,
                        'currencyID' => 'USD',
                        'pcp_display_in_roll' => "",
                        'pcp_roll_nickname' => "",
                        'pcp_personal_note' => "",
                        'non_deductible_amount' => "",
                        'fee_amount' => "",
                        'net_amount' => "",
                        'invoice_id'  => "",
                        'contribution_page_id'  => "",
                        'thankyou_date' => null,
                        'honor_contact_id' => null,
                        'invoiceID' => 'c79064eb79bd9147c2466c19886ac8ff',
                        'first_name' => 'Frodo',
                        'middle_name' => 'bob',
                        'last_name' => 'Baggins',
                        'street_address' => '8 Hobbiton Road',
                        'city' => 'The Shire',
                        'state_province' => 'IL',
                        'postal_code' => 5010,
                        'country' => 'US',
                        'contributionType_name' => 'My precious',
                        'contributionType_accounting_code' => '',
                        'contributionPageID' => '',
                        'email' => 'enroute@tomordor.com',
                        'contactID' => 1,
                        'contributionID' => 1,
                        'contributionTypeID' => 1,
                        'contributionRecurID' => 1,
                             );
        $result = $this->processor->doDirectPayment($params);
        
        $this->assertNotType('CRM_Core_Error', $result,"In line " . __LINE__ . " " .$result->_errors[0]['message']);
        //cancel it or the transaction will be rejected by A.net if the test is re-run
        $this->processor->cancelSubscription( ) ;
    }  
        
    /**
     * create a single post dated payment as a recurring transaction
     */
    function testCreateSinglePostDated( )
    {
        $start_date = date('Ymd',strtotime("+ 1 week") );
        $params = array(     
                        'qfKey' => '00ed21c7ca00a1f7d555555596ef7_4454',
                        'hidden_CreditCard' => 1,
                        'billing_first_name' => 'Frodowina',
                        'billing_middle_name' => "",
                        'billing_last_name' => 'Baggins',
                        'billing_street_address-5' => '8 Hobbitton Road',
                        'billing_city-5'  => 'The Shire',
                        'billing_state_province_id-5' => 1012,
                        'billing_postal_code-5' => 5010,
                        'billing_country_id-5' => 1228,
                        'credit_card_number' => '4007000000027',
                        'cvv2' => 123,
                        'credit_card_exp_date' => array(
                                                        'M' => 11,
                                                        'Y' => 2019
                                                        ),
                        
                        'credit_card_type' => 'Visa',
                        'is_recur' => 1,
                        'frequency_interval' => 1,
                        'frequency_unit' => 'month',
                        'installments' => 1,
                        'contribution_type_id' => $this->ids['contribution_type'],
                        'is_email_receipt' => 1,
                        'from_email_address' => 'gandalf',
                        'receive_date_time' => '11:57PM',
                        'receive_date' => $start_date,
                        'receipt_date_time' => '',
                        'payment_processor_id' => $this->ids['payment_processor'],
                        'price_set_id' => '',
                        'total_amount' => 7,
                        'currency' => 'USD',
                        'source' => "Mordor",
                        'soft_credit_to' => '', 
                        'soft_contact_id' =>  '',
                        'billing_state_province-5' => 'IL',
                        'state_province-5' => 'IL',
                        'billing_country-5' => 'US',
                        'country-5' => 'US',
                        'year' => 2019,
                        'month' => 10,
                        'ip_address' => '127.0.0.1',
                        'amount' => 70,
                        'amount_level' => 0,
                        'currencyID' => 'USD',
                        'pcp_display_in_roll' => "",
                        'pcp_roll_nickname' => "",
                        'pcp_personal_note' => "",
                        'non_deductible_amount' => "",
                        'fee_amount' => "",
                        'net_amount' => "",
                        'invoice_id'  => "",
                        'contribution_page_id'  => "",
                        'thankyou_date' => null,
                        'honor_contact_id' => null,
                        'invoiceID' => '99999977777777',
                        'first_name' => 'Frodowina',
                        'middle_name' => 'bob',
                        'last_name' => 'Baggins',
                        'street_address' => '8 Hobbiton Road',
                        'city' => 'The Shire',
                        'state_province' => 'IL',
                        'postal_code' => 5010,
                        'country' => 'US',
                        'contributionType_name' => 'My precious',
                        'contributionType_accounting_code' => '',
                        'contributionPageID' => '',
                        'email' => 'backhome@frommordor.com',
                        'contactID' => 1,
                        'contributionID' => 1,
                        'contributionTypeID' => 1,
                        'contributionRecurID' => 1,
                             );
        $result = $this->processor->doDirectPayment($params);
        
        $this->assertNotType('CRM_Core_Error', $result,"In line " . __LINE__ . " " .$result->_errors[0]['message']);
        //cancel it or the transaction will be rejected by A.net if the test is re-run
        $this->processor->cancelSubscription( ) ;
    }  
}
 ?>
