<?php
/*
 +--------------------------------------------------------------------+
 | CiviCRM version 4.2                                                |
 +--------------------------------------------------------------------+
 | Copyright CiviCRM LLC (c) 2004-2012                                |
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
 | License along with this program; if not, contact CiviCRM LLC       |
 | at info[AT]civicrm[DOT]org. If you have questions about the        |
 | GNU Affero General Public License or the licensing of CiviCRM,     |
 | see the CiviCRM license FAQ at http://civicrm.org/licensing        |
 +--------------------------------------------------------------------+
*/


require_once 'CiviTest/CiviSeleniumTestCase.php';
class WebTest_Contribute_UpdateContributionTest extends CiviSeleniumTestCase {

 protected function setUp() {
    parent::setUp();
  }

 function testChangeContributionAmount() {
   // This is the path where our testing install resides.
   // The rest of URL is defined in CiviSeleniumTestCase base class, in
   // class attributes.
   $this->open($this->sboxPath);
   // Logging in. Remember to wait for page to load. In most cases,
   // you can rely on 30000 as the value that allows your test to pass, however,
   // sometimes your test might fail because of this. In such cases, it's better to pick one element
   // somewhere at the end of page and use waitForElementPresent on it - this assures you, that whole
   // page contents loaded and you can continue your test execution.
   $this->webtestLogin();
   $firstName = substr(sha1(rand()), 0, 7);
   $lastName  = 'Contributor';
   $email     = $firstName . "@example.com";
   $amount = 100;
   //Offline Pay Later Contribution
   $this->_testOfflineContribution($firstName, $lastName, $email, $amount, "Pending");

   $this->open($this->sboxPath . "civicrm/contribute/search?reset=1");

   $this->waitForElementPresent("contribution_date_low");

   $this->type("sort_name", "$lastName, $firstName");
   $this->click("_qf_Search_refresh");

   $this->waitForPageToLoad('30000');
   $contriIDOff = explode('&', $this->getAttribute("xpath=//div[@id='contributionSearch']/table/tbody/tr[1]/td[11]/span/a@href"));
   if (!empty($contriIDOff)) {
     $contriIDOff = substr($contriIDOff[1], (strrpos($contriIDOff[1], '=') + 1));
   }

   $this->click("xpath=//tr[@id='rowid{$contriIDOff}']/td[11]/span/a[2]");
   $this->waitForElementPresent("total_amount");
   $this->type("total_amount", "90");
   $this->click('_qf_Contribution_upload');
   $this->waitForPageToLoad("30000");

   // Is status message correct?
   $this->assertTrue($this->isTextPresent("The contribution record has been saved."), "Status message didn't show up after saving!");
   //For Contribution
   $searchParams = array('id' => $contriIDOff);
   $compareParams = array('total_amount' => '90.00');
   //For LineItem
   $lineItemSearchParams = array('entity_id' => $contriIDOff);
   $lineItemCompareParams = array('line_total' => '90.00');
   
   $this->assertDBCompareValues('CRM_Contribute_DAO_Contribution', $searchParams, $compareParams);
   $this->assertDBCompareValues('CRM_Price_DAO_LineItem', $lineItemSearchParams, $lineItemCompareParams);
   
   $total = $this->_getTotalContributedAmount($contriIDOff);
   $compare = array('total_amount' => $total);
   $this->assertDBCompareValues('CRM_Contribute_DAO_Contribution', $searchParams, $compare);

   
   $amount = $this->_getFinancialItemAmount($contriIDOff);
   $compare = array('total_amount' => $amount);
   $this->assertDBCompareValues('CRM_Contribute_DAO_Contribution', $searchParams, $compare);

   $financialTrxnAmount = $this->_getFinancialTrxnAmount($contriIDOff);
   $compare = array('total_amount' => $financialTrxnAmount);
   $this->assertDBCompareValues('CRM_Contribute_DAO_Contribution', $searchParams, $compare);
 }

 function testPayLater() {
   // This is the path where our testing install resides.
   // The rest of URL is defined in CiviSeleniumTestCase base class, in
   // class attributes.
   $this->open($this->sboxPath);
   // Logging in. Remember to wait for page to load. In most cases,
   // you can rely on 30000 as the value that allows your test to pass, however,
   // sometimes your test might fail because of this. In such cases, it's better to pick one element
   // somewhere at the end of page and use waitForElementPresent on it - this assures you, that whole
   // page contents loaded and you can continue your test execution.
   $this->webtestLogin();
   $firstName = substr(sha1(rand()), 0, 7);
   $lastName  = 'Contributor';
   $email     = $firstName . "@example.com";
   $amount = 100.00;
   //Offline Pay Later Contribution
   $this->_testOfflineContribution($firstName, $lastName, $email, $amount, "Pending");
   $this->click("xpath=//div[@id='Contributions']//table/tbody/tr[1]/td[8]/span/a[text()='Edit']");
   $this->waitForPageToLoad("30000");
   $elements = $this->parseURL();
   $contId = $elements['queryString']['id'];
   $this->select("contribution_status_id", "label=Completed");
   $this->click("_qf_Contribution_upload");
   $this->waitForPageToLoad("30000");
   //Assertions
   $search = array( 'id' => $contId );
   $compare = array( 'contribution_status_id' => 1 );
   $this->assertDBCompareValues('CRM_Contribute_DAO_Contribution', $search, $compare);

   $lineItem = key(CRM_Price_BAO_LineItem::getLineItems($contId, 'contribution'));
   $search = array( 'entity_id' => $lineItem );
   $compare = array( 'status_id' => 1 );
   $this->assertDBCompareValues("CRM_Financial_DAO_FinancialItem", $search, $compare);

   $status = $this->_getPremiumActualCost($contId, NULL, 6, NULL, "'civicrm_contribution'",  "ft.status_id as status");
   $this->assertEquals($status, '1', "Verify Completed Status"); 
 }

 function testChangePremium() {
   // This is the path where our testing install resides.
   // The rest of URL is defined in CiviSeleniumTestCase base class, in
   // class attributes.
   $this->open($this->sboxPath);
   // Logging in. Remember to wait for page to load. In most cases,
   // you can rely on 30000 as the value that allows your test to pass, however,
   // sometimes your test might fail because of this. In such cases, it's better to pick one element
   // somewhere at the end of page and use waitForElementPresent on it - this assures you, that whole
   // page contents loaded and you can continue your test execution.
   $this->webtestLogin();
   $firstName = substr(sha1(rand()), 0, 7);
   $lastName  = 'Contributor';
   $email     = $firstName . "@example.com";
   $from = 9;
   $to = 10;
   $financialType = array(
     'name' => 'Test Financial'.substr(sha1(rand()), 0, 7),
     'is_reserved' => 1,
     'is_deductible' => 1,
   );
   $this->addeditFinancialType($financialType);
   $ogAccount = CRM_Core_DAO::getFieldValue('CRM_Core_DAO_OptionGroup', 'account_relationship', 'id', 'name');
   $ogParams = array(
     'option_group_id' => $ogAccount,
     'name' => 'Cost of Sales Account is',
   );
   $defaults = array();
   CRM_Core_BAO_OptionValue::retrieve($ogParams, $defaults);
   $acRel = $defaults['value'];
   $this->select("account_relationship", "value=$acRel");
   $this->select("financial_account_id", "value=$from");
   $this->click("_qf_FinancialTypeAccount_next_new-botttom");
   $this->waitForPageToLoad("30000");
   $ogParams = array(
     'option_group_id' => $ogAccount,
     'name' => 'Premiums Inventory Account is',
   );
   $defaults = $info = array();
   CRM_Core_BAO_OptionValue::retrieve($ogParams, $defaults);
   $fcRel = $defaults['value'];
   $this->select("account_relationship", "value=$fcRel");
   $this->select("financial_account_id", "value=$to");
   $this->click("_qf_FinancialTypeAccount_next-botttom");
   CRM_Financial_BAO_FinancialType::retrieve($financialType, $info);
   $premiumName = 'Premium'.substr(sha1(rand()), 0, 7);
   $amount = 500;
   $sku = 'SKU';
   $price = 300;
   $cost = 3.00;
   $this->open($this->sboxPath . "civicrm/admin/contribute/managePremiums?action=add&reset=1");
   // add premium
   $this->addPremium($premiumName, $sku, $amount, $price, $cost, $info['id']);

   //add second premium
   $premiumName2 = 'Premium'.substr(sha1(rand()), 0, 7);
   $amount2 = 600;
   $sku2 = 'SKU';
   $price2 = 200;
   $cost2 = 2.00;
   $this->open($this->sboxPath . "civicrm/admin/contribute/managePremiums?action=add&reset=1");
   $this->addPremium($premiumName2, $sku2, $amount2, $price2, $cost2, $info['id']);

   // add contribution with premium
   $this->open($this->sboxPath . "civicrm/contribute/add?reset=1&action=add&context=standalone");
   $this->waitForPageToLoad("30000");

   // create new contact using dialog
   $this->webtestNewDialogContact($firstName, $lastName, $email);
   // select financial type
   $this->select( "financial_type_id", "value=1" );
   // total amount
   $this->type("total_amount", "100");
   // fill Premium information
   $this->click("xpath=//div[@id='Premium']");
   $this->waitForElementPresent("product_name_0");
   $this->select('product_name_0', "label=$premiumName ( $sku )");
   // Clicking save.
   $this->click("_qf_Contribution_upload");
   $this->waitForPageToLoad("30000");
   // Is status message correct?
   $this->assertTrue($this->isTextPresent("The contribution record has been saved."), "Status message didn't show up after saving!");
   // verify if Membership is created
   $this->waitForElementPresent("xpath=//div[@id='Contributions']//table//tbody/tr[1]/td[8]/span/a[text()='View']");
   //click through to the Contribution edit screen
   $this->click("xpath=//div[@id='Contributions']//table/tbody/tr[1]/td[8]/span/a[text()='Edit']");
   $this->waitForElementPresent("_qf_Contribution_upload-bottom");
   $elements = $this->parseURL();
   $contId = $elements['queryString']['id'];
   $this->waitForElementPresent("product_name_0");
   $this->select('product_name_0', "label=$premiumName2 ( $sku2 )");
   // Clicking save.
   $this->click("_qf_Contribution_upload");
   $this->waitForPageToLoad("30000");

   //Assertions
   $financialTrxnIds = CRM_Core_BAO_FinancialTrxn::getFinancialTrxnIds($contId);
   $actualAmount = $this->_getPremiumActualCost($financialTrxnIds['financialTrxnId'], $to, $from, $cost2);
   $this->assertEquals($actualAmount, $cost2, "Verify actual cost for changed premium");

   $deletedAmount = $this->_getPremiumActualCost($financialTrxnIds['financialTrxnId'], $from, $to, $cost);
   $this->assertEquals($deletedAmount, $cost, "Verify actual cost for deleted premium");
 }

 function testDeletePremium() {
   // This is the path where our testing install resides.
   // The rest of URL is defined in CiviSeleniumTestCase base class, in
   // class attributes.
   $this->open($this->sboxPath);
   // Logging in. Remember to wait for page to load. In most cases,
   // you can rely on 30000 as the value that allows your test to pass, however,
   // sometimes your test might fail because of this. In such cases, it's better to pick one element
   // somewhere at the end of page and use waitForElementPresent on it - this assures you, that whole
   // page contents loaded and you can continue your test execution.
   $this->webtestLogin();
   $firstName = substr(sha1(rand()), 0, 7);
   $lastName  = 'Contributor';
   $email     = $firstName . "@example.com";
   $from = 9;
   $to = 10;
   $financialType = array(
     'name' => 'Test Financial'.substr(sha1(rand()), 0, 7),
     'is_reserved' => 1,
     'is_deductible' => 1,
   );
   $this->addeditFinancialType($financialType);
   $ogAccount = CRM_Core_DAO::getFieldValue('CRM_Core_DAO_OptionGroup', 'account_relationship', 'id', 'name');
   $ogParams = array(
     'option_group_id' => $ogAccount,
     'name' => 'Cost of Sales Account is',
   );
   $defaults = array();
   CRM_Core_BAO_OptionValue::retrieve($ogParams, $defaults);
   $acRel = $defaults['value'];
   $this->select("account_relationship", "value=$acRel");
   $this->select("financial_account_id", "value=$from");
   $this->click("_qf_FinancialTypeAccount_next_new-botttom");
   $this->waitForPageToLoad("30000");
   $ogParams = array(
     'option_group_id' => $ogAccount,
     'name' => 'Premiums Inventory Account is',
   );
   $defaults = $info = array();
   CRM_Core_BAO_OptionValue::retrieve($ogParams, $defaults);
   $fcRel = $defaults['value'];
   $this->select("account_relationship", "value=$fcRel");
   $this->select("financial_account_id", "value=$to");
   $this->click("_qf_FinancialTypeAccount_next-botttom");
   CRM_Financial_BAO_FinancialType::retrieve($financialType, $info);
   $premiumName = 'Premium'.substr(sha1(rand()), 0, 7);
   $amount = 500;
   $sku = 'SKU';
   $price = 300;
   $cost = 3.00;
   $this->open($this->sboxPath . "civicrm/admin/contribute/managePremiums?action=add&reset=1");
   // add premium
   $this->addPremium($premiumName, $sku, $amount, $price, $cost, $info['id']);

   // add contribution with premium
   $this->open($this->sboxPath . "civicrm/contribute/add?reset=1&action=add&context=standalone");
   $this->waitForPageToLoad("30000");

   // create new contact using dialog
   $this->webtestNewDialogContact($firstName, $lastName, $email);
   // select financial type
   $this->select( "financial_type_id", "value=1" );
   // total amount
   $this->type("total_amount", "100");
   // fill Premium information
   $this->click("xpath=//div[@id='Premium']");
   $this->waitForElementPresent("product_name_0");
   $this->select('product_name_0', "label=$premiumName ( $sku )");
   // Clicking save.
   $this->click("_qf_Contribution_upload");
   $this->waitForPageToLoad("30000");
   // Is status message correct?
   $this->assertTrue($this->isTextPresent("The contribution record has been saved."), "Status message didn't show up after saving!");
   // verify if Membership is created
   $this->waitForElementPresent("xpath=//div[@id='Contributions']//table//tbody/tr[1]/td[8]/span/a[text()='View']");
   //click through to the Contribution edit screen
   $this->click("xpath=//div[@id='Contributions']//table/tbody/tr[1]/td[8]/span/a[text()='Edit']");
   $this->waitForElementPresent("_qf_Contribution_upload-bottom");
   $elements = $this->parseURL();
   $contId = $elements['queryString']['id'];
   $this->waitForElementPresent("product_name_0");
   $this->select('product_name_0', "value=0");
   // Clicking save.
   $this->click("_qf_Contribution_upload");
   $this->waitForPageToLoad("30000");

   //Assertions
   $financialTrxnIds = CRM_Core_BAO_FinancialTrxn::getFinancialTrxnIds($contId);
   $actualAmount = $this->_getPremiumActualCost($financialTrxnIds['financialTrxnId'], $from, $to);
   $this->assertEquals($actualAmount, $cost, "Verify actual cost for deleted premium");
 }

 function testChangePaymentInstrument() {
   // This is the path where our testing install resides.
   // The rest of URL is defined in CiviSeleniumTestCase base class, in
   // class attributes.
   $this->open($this->sboxPath);
   // Logging in. Remember to wait for page to load. In most cases,
   // you can rely on 30000 as the value that allows your test to pass, however,
   // sometimes your test might fail because of this. In such cases, it's better to pick one element
   // somewhere at the end of page and use waitForElementPresent on it - this assures you, that whole
   // page contents loaded and you can continue your test execution.
   $this->webtestLogin();
   $firstName = substr(sha1(rand()), 0, 7);
   $lastName  = 'Contributor';
   $email     = $firstName . "@example.com";
   $label = 'TEST'.substr(sha1(rand()), 0, 7);
   $amount = 100.00;
   $fromParams = array( 'name' => 'Deposit Bank Account' );
   $toParams = array( 'name' => 'Accounts Receivable' );
   $to = $from = array();
   CRM_Financial_BAO_FinancialAccount::retrieve($toParams, $to);
   CRM_Financial_BAO_FinancialAccount::retrieve($fromParams, $from);

   $this->addPaymentInstrument($label, $to['id']);
   $this->_testOfflineContribution($firstName, $lastName, $email, $amount);
   $this->click("xpath=//div[@id='Contributions']//table/tbody/tr[1]/td[8]/span/a[text()='Edit']");
   $this->waitForPageToLoad("30000");
   $elements = $this->parseURL();
   $contId = $elements['queryString']['id'];
   //change payment processor to newly created value
   $this->select("payment_instrument_id", "label=$label");
   $this->click("_qf_Contribution_upload");
   $this->waitForPageToLoad("30000");
   //Assertions
   $totalAmount = $this->_getPremiumActualCost($contId, $from['id'], $to['id']);
   $this->assertEquals($totalAmount, $amount, "Verify amount for newly inserted values");
 }

 function testRefundContribution() {
   // This is the path where our testing install resides.
   // The rest of URL is defined in CiviSeleniumTestCase base class, in
   // class attributes.
   $this->open($this->sboxPath);
   // Logging in. Remember to wait for page to load. In most cases,
   // you can rely on 30000 as the value that allows your test to pass, however,
   // sometimes your test might fail because of this. In such cases, it's better to pick one element
   // somewhere at the end of page and use waitForElementPresent on it - this assures you, that whole
   // page contents loaded and you can continue your test execution.
   $this->webtestLogin();
   $firstName = substr(sha1(rand()), 0, 7);
   $lastName  = 'Contributor';
   $email     = $firstName . "@example.com";
   $label = 'TEST'.substr(sha1(rand()), 0, 7);
   $amount = 100.00;
   $this->_testOfflineContribution($firstName, $lastName, $email, $amount);
   $this->click("xpath=//div[@id='Contributions']//table/tbody/tr[1]/td[8]/span/a[text()='Edit']");
   $this->waitForPageToLoad("30000");
   //Contribution status
   $this->select("contribution_status_id", "label=Refunded");
   $elements = $this->parseURL();
   $contId = $elements['queryString']['id'];
   $this->click("_qf_Contribution_upload");
   $this->waitForPageToLoad("30000");

   //Assertions
   $lineItem = key(CRM_Price_BAO_LineItem::getLineItems($contId, 'contribution'));
   $search = array( 'entity_id' => $lineItem );
   $compare = array(
     'amount' => '100.00',
     'status_id' => 1,
   );
   $this->assertDBCompareValues("CRM_Financial_DAO_FinancialItem", $search, $compare);
   $amount = $this->_getPremiumActualCost($contId, NULL, 6, -100.00, "'civicrm_contribution'");
   $this->assertEquals($amount, '-100.00', 'Verify Financial Trxn Amount.');
 }

 function _getPremiumActualCost($entityId, $from = NULL, $to, $cost = NULL, $entityTable = NULL, $select = "ft.total_amount AS amount") {
   $query = "SELECT
     {$select}
     FROM civicrm_financial_trxn ft
     INNER JOIN civicrm_entity_financial_trxn eft ON eft.financial_trxn_id = ft.id AND eft.entity_id = {$entityId}
     WHERE  ft.to_financial_account_id = {$to}";
   if ($entityTable) {
     $query .= " AND eft.entity_table = {$entityTable}";
   }
   if (!empty($from)) {
     $query .= " AND ft.from_financial_account_id = {$from}";
   }
   if (!empty($cost)) {
     $query .= " AND eft.amount = {$cost}";
   }
   $result = CRM_Core_DAO::singleValueQuery($query);
   return $result;
 }

 function _getFinancialTrxnAmount($contId) {
   $query = "SELECT
     SUM( ft.total_amount ) AS total
     FROM civicrm_financial_trxn AS ft
     LEFT JOIN civicrm_entity_financial_trxn AS ceft ON ft.id = ceft.financial_trxn_id
     WHERE ceft.entity_table = 'civicrm_contribution'
     AND ceft.entity_id = {$contId}";
   $result = CRM_Core_DAO::singleValueQuery($query);
   return $result;
 }

 function _getFinancialItemAmount($contId) {
   $lineItem = key(CRM_Price_BAO_LineItem::getLineItems($contId, 'contribution'));
   $query = "SELECT
     SUM(amount)
     FROM civicrm_financial_item
     WHERE entity_table = 'civicrm_line_item'
     AND entity_id = {$lineItem}";
   $result = CRM_Core_DAO::singleValueQuery($query);
   return $result;
 }

 function _getTotalContributedAmount($contId) {
   $query = "SELECT
     SUM(amount)
     FROM civicrm_entity_financial_trxn
     WHERE entity_table = 'civicrm_contribution'
     AND entity_id = {$contId}";
   $result = CRM_Core_DAO::singleValueQuery($query);
   return $result;
 }

 function _testOfflineContribution($firstName, $lastName, $email, $amount, $status="Completed") {
   // Go directly to the URL of the screen that you will be testing (New Contribution-standalone).
   $this->open($this->sboxPath . "civicrm/contribute/add?reset=1&context=standalone");

   // As mentioned before, waitForPageToLoad is not always reliable. Below, we're waiting for the submit
   // button at the end of this page to show up, to make sure it's fully loaded.
   $this->waitForElementPresent("_qf_Contribution_upload");

   // Let's start filling the form with values.

   // create new contact using dialog
   $this->webtestNewDialogContact($firstName, $lastName, $email);

   // select financial type
   $this->select( "financial_type_id", "value=1" );

   //Contribution status
   $this->select("contribution_status_id", "label=$status");

   // total amount
   $this->type("total_amount", $amount);

   // select payment instrument type
   $this->select("payment_instrument_id", "label=Credit Card");
   
   $this->type("trxn_id", "P20901X1" . rand(100, 10000));
  
   //Custom Data
   //$this->click('CIVICRM_QFID_3_6');

   // Clicking save.
   $this->click("_qf_Contribution_upload");
   $this->waitForPageToLoad("30000");

   // Is status message correct?
   $this->assertTrue($this->isTextPresent("The contribution record has been saved."), "Status message didn't show up after saving!");

   // verify if Membership is created
   $this->waitForElementPresent("xpath=//div[@id='Contributions']//table//tbody/tr[1]/td[8]/span/a[text()='View']");

   //click through to the Membership view screen
   $this->click("xpath=//div[@id='Contributions']//table/tbody/tr[1]/td[8]/span/a[text()='View']");
   $this->waitForElementPresent("_qf_ContributionView_cancel-bottom");

   $expected = array(
     'Financial Type' => 'Donation',
     'Total Amount' => '100.00',
     'Contribution Status' => $status,
   );
   foreach ($expected as $label => $value) {
     $this->verifyText("xpath=id('ContributionView')/div[2]/table[1]/tbody//tr/td[1][text()='$label']/../td[2]", preg_quote($value));
   }
   $this->click("_qf_ContributionView_cancel-top");
   $this->waitForPageToLoad("30000");
   sleep(4);
 }
}

