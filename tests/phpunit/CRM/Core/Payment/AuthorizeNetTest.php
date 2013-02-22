<?php
/*
 +--------------------------------------------------------------------+
 | CiviCRM version 4.3                                                |
 +--------------------------------------------------------------------+
 | Copyright CiviCRM LLC (c) 2004-2013                                |
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
require_once 'CiviTest/AuthorizeNet.php';
require_once 'CiviTest/Contact.php';
class CRM_Core_Payment_AuthorizeNetTest extends CiviUnitTestCase {
  function get_info() {
    return array(
      'name' => 'Authorize.net processing',
      'description' => 'Test Authorize.ne methods.',
      'group' => 'Payment Processor Tests',
    );
  }

  function setUp() {
    parent::setUp();
    $this->paymentProcessor = new AuthorizeNet();
    $this->processorParams = $this->paymentProcessor->create();

    $paymentProcessor = array(
      'user_name' => $this->processorParams->user_name,
      'password' => $this->processorParams->password,
      'url_recur' => $this->processorParams->url_recur,
    );

    $this->processor = new CRM_Core_Payment_AuthorizeNet('Contribute', $paymentProcessor);
    $this->_contributionTypeId = $this->contributionTypeCreate();

    // for some strange unknown reason, in batch mode this value gets set to null
    // so crude hack here to avoid an exception and hence an error
    $GLOBALS['_PEAR_ERRORSTACK_OVERRIDE_CALLBACK'] = array( );
  }

  function tearDown() {
    $this->paymentProcessor->delete($this->processorParams->id);
    $tablesToTruncate = array( 'civicrm_financial_type', 'civicrm_contribution', 'civicrm_contribution_recur', 'civicrm_line_item' );
    $this->quickCleanup($tablesToTruncate);
  }

  /**
   * create a single post dated payment as a recurring transaction.
   *
   * Test works but not both due to some form of caching going on in the SmartySingleton
   */
  function testCreateSingleNowDated() {
    $firstName  = 'John_' .  substr(sha1(rand()), 0, 7);
    $lastName   = 'Smith_' . substr(sha1(rand()), 0, 7);
    $nameParams = array('first_name' => $firstName, 'last_name' => $lastName);
    $contactId  = Contact::createIndividual($nameParams);

    $ids = array('contribution' => NULL);
    $invoiceID = sha1(rand());
    $amount    = rand(100, 1000) . '.00';

    $contributionRecurParams = array(
      'contact_id' => $contactId,
      'amount'     => $amount,
      'currency'   => 'USD',
      'frequency_unit' => 'week',
      'frequency_interval' => 1,
      'installments' => 2,
      'start_date'   => date('Ymd'),
      'create_date'  => date('Ymd'),
      'invoice_id'   => $invoiceID,
      'contribution_status_id' => 2,
      'is_test' => 1,
      'payment_processor_id' => $this->processorParams->id,
    );
    $recur = CRM_Contribute_BAO_ContributionRecur::add($contributionRecurParams, $ids);

    $contributionParams = array(
      'contact_id'   => $contactId,
      'financial_type_id'   => $this->_contributionTypeId,
      'receive_date' => date('Ymd'),
      'total_amount' => $amount,
      'invoice_id'   => $invoiceID,
      'currency'     => 'USD',
      'contribution_recur_id' => $recur->id,
      'is_test'      => 1,
      'contribution_status_id' => 2,
    );
    $contribution = CRM_Contribute_BAO_Contribution::add($contributionParams, $ids);

    $params = array(
      'qfKey' => '08ed21c7ca00a1f7d32fff2488596ef7_4454',
      'hidden_CreditCard' => 1,
      'billing_first_name' => $firstName,
      'billing_middle_name' => "",
      'billing_last_name' => $lastName,
      'billing_street_address-5' => '8 Hobbitton Road',
      'billing_city-5' => 'The Shire',
      'billing_state_province_id-5' => 1012,
      'billing_postal_code-5' => 5010,
      'billing_country_id-5' => 1228,
      'credit_card_number' => '4007000000027',
      'cvv2' => 123,
      'credit_card_exp_date' => array(
        'M' => 10,
        'Y' => 2019,
      ),
      'credit_card_type' => 'Visa',
      'is_recur' => 1,
      'frequency_interval' => 1,
      'frequency_unit' => 'month',
      'installments' => 12,
      'financial_type_id' => $this->_contributionTypeId,
      'is_email_receipt' => 1,
      'from_email_address' => "{$firstName}.{$lastName}@example.com",
      'receive_date' => date('Ymd'),
      'receipt_date_time' => '',
      'payment_processor_id' => $this->processorParams->id,
      'price_set_id' => '',
      'total_amount' => $amount,
      'currency' => 'USD',
      'source' => "Mordor",
      'soft_credit_to' => '',
      'soft_contact_id' => '',
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
      'invoiceID' => $invoiceID,
      'contribution_page_id' => "",
      'thankyou_date' => NULL,
      'honor_contact_id' => NULL,
      'first_name' => $firstName,
      'middle_name' => '',
      'last_name' => $lastName,
      'street_address' => '8 Hobbiton Road',
      'city' => 'The Shire',
      'state_province' => 'IL',
      'postal_code' => 5010,
      'country' => 'US',
      'contributionType_name' => 'My precious',
      'contributionType_accounting_code' => '',
      'contributionPageID' => '',
      'email' => "{$firstName}.{$lastName}@example.com",
      'contactID' => $contactId,
      'contributionID' => $contribution->id,
      'contributionTypeID' => $this->_contributionTypeId,
      'contributionRecurID' => $recur->id,
    );

    // turn verifySSL off
    CRM_Core_BAO_Setting::setItem('0', CRM_Core_BAO_Setting::SYSTEM_PREFERENCES_NAME, 'verifySSL');
    $result = $this->processor->doDirectPayment($params);
    // turn verifySSL on
    CRM_Core_BAO_Setting::setItem('0', CRM_Core_BAO_Setting::SYSTEM_PREFERENCES_NAME, 'verifySSL');

    // if subscription was successful, processor_id / subscription-id must not be null
    $this->assertDBNotNull('CRM_Contribute_DAO_ContributionRecur', $recur->id, 'processor_id',
      'id', 'Failed to create subscription with Authorize.'
    );

    // cancel it or the transaction will be rejected by A.net if the test is re-run
    $subscriptionID = CRM_Core_DAO::getFieldValue('CRM_Contribute_DAO_ContributionRecur', $recur->id, 'processor_id');
    $result = $this->processor->cancelSubscription($message, array('subscriptionId' => $subscriptionID));
    $this->assertTrue($result, 'Failed to cancel subscription with Authorize.');
      
    Contact::delete($contactId);
  }

  /**
   * create a single post dated payment as a recurring transaction
   */
  function testCreateSinglePostDated() {
    $start_date = date('Ymd', strtotime("+ 1 week"));

    $firstName  = 'John_' .  substr(sha1(rand()), 0, 7);
    $lastName   = 'Smith_' . substr(sha1(rand()), 0, 7);
    $nameParams = array('first_name' => $firstName, 'last_name' => $lastName);
    $contactId  = Contact::createIndividual($nameParams);

    $ids = array('contribution' => NULL);
    $invoiceID = sha1(rand());
    $amount    = rand(100, 1000) . '.00';

    $contributionRecurParams = array(
      'contact_id' => $contactId,
      'amount'     => $amount,
      'currency'   => 'USD',
      'frequency_unit' => 'month',
      'frequency_interval' => 1,
      'installments' => 3,
      'start_date'   => $start_date,
      'create_date'  => date('Ymd'),
      'invoice_id'   => $invoiceID,
      'contribution_status_id' => 2,
      'is_test' => 1,
      'payment_processor_id' => $this->processorParams->id,
    );
    $recur = CRM_Contribute_BAO_ContributionRecur::add($contributionRecurParams, $ids);

    $contributionParams = array(
      'contact_id'   => $contactId,
      'financial_type_id'   => $this->_contributionTypeId,
      'receive_date' => $start_date,
      'total_amount' => $amount,
      'invoice_id'   => $invoiceID,
      'currency'     => 'USD',
      'contribution_recur_id' => $recur->id,
      'is_test' => 1,
      'contribution_status_id' => 2,
    );
    $contribution = CRM_Contribute_BAO_Contribution::add($contributionParams, $ids);

    $params = array(
      'qfKey' => '00ed21c7ca00a1f7d555555596ef7_4454',
      'hidden_CreditCard' => 1,
      'billing_first_name' => $firstName,
      'billing_middle_name' => "",
      'billing_last_name' => $lastName,
      'billing_street_address-5' => '8 Hobbitton Road',
      'billing_city-5' => 'The Shire',
      'billing_state_province_id-5' => 1012,
      'billing_postal_code-5' => 5010,
      'billing_country_id-5' => 1228,
      'credit_card_number' => '4007000000027',
      'cvv2' => 123,
      'credit_card_exp_date' => array(
        'M' => 11,
        'Y' => 2019,
      ),
      'credit_card_type' => 'Visa',
      'is_recur' => 1,
      'frequency_interval' => 1,
      'frequency_unit' => 'month',
      'installments' => 3,
      'financial_type_id' => $this->_contributionTypeId,
      'is_email_receipt' => 1,
      'from_email_address' => "{$firstName}.{$lastName}@example.com",
      'receive_date' => $start_date,
      'receipt_date_time' => '',
      'payment_processor_id' => $this->processorParams->id,
      'price_set_id' => '',
      'total_amount' => $amount,
      'currency' => 'USD',
      'source' => "Mordor",
      'soft_credit_to' => '',
      'soft_contact_id' => '',
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
      'invoice_id' => "",
      'contribution_page_id' => "",
      'thankyou_date' => NULL,
      'honor_contact_id' => NULL,
      'invoiceID' => $invoiceID,
      'first_name' => $firstName,
      'middle_name' => 'bob',
      'last_name' => $lastName,
      'street_address' => '8 Hobbiton Road',
      'city' => 'The Shire',
      'state_province' => 'IL',
      'postal_code' => 5010,
      'country' => 'US',
      'contributionType_name' => 'My precious',
      'contributionType_accounting_code' => '',
      'contributionPageID' => '',
      'email' => "{$firstName}.{$lastName}@example.com",
      'contactID' => $contactId,
      'contributionID' => $contribution->id,
      'contributionTypeID' => $this->_contributionTypeId,
      'contributionRecurID' => $recur->id,
    );

    // if cancel-subscription has been called earlier 'subscriptionType' would be set to cancel.
    // to make a successful call for another trxn, we need to set it to something else.
    $smarty = CRM_Core_Smarty::singleton();
    $smarty->assign('subscriptionType', 'create');

    // turn verifySSL off
    CRM_Core_BAO_Setting::setItem('0', CRM_Core_BAO_Setting::SYSTEM_PREFERENCES_NAME, 'verifySSL');
    $result = $this->processor->doDirectPayment($params);
    // turn verifySSL on
    CRM_Core_BAO_Setting::setItem('0', CRM_Core_BAO_Setting::SYSTEM_PREFERENCES_NAME, 'verifySSL');

    // if subscription was successful, processor_id / subscription-id must not be null
    $this->assertDBNotNull('CRM_Contribute_DAO_ContributionRecur', $recur->id, 'processor_id',
      'id', 'Failed to create subscription with Authorize.'
    );

    // cancel it or the transaction will be rejected by A.net if the test is re-run
    $subscriptionID = CRM_Core_DAO::getFieldValue('CRM_Contribute_DAO_ContributionRecur', $recur->id, 'processor_id');
    $result = $this->processor->cancelSubscription($message, array('subscriptionId' => $subscriptionID));
    $this->assertTrue($result, 'Failed to cancel subscription with Authorize.');
      
    Contact::delete($contactId);
  }
}
