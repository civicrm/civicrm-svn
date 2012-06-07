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
 | License along with this program; if not, contact CiviCRM LLC       |
 | at info[AT]civicrm[DOT]org. If you have questions about the        |
 | GNU Affero General Public License or the licensing of CiviCRM,     |
 | see the CiviCRM license FAQ at http://civicrm.org/licensing        |
 +--------------------------------------------------------------------+
*/


require_once 'CiviTest/CiviSeleniumTestCase.php';
class WebTest_Contribute_AddBatchesTest extends CiviSeleniumTestCase {

  protected $captureScreenshotOnFailure = TRUE;
  protected $screenshotPath = '/var/www/api.dev.civicrm.org/public/sc';
  protected $screenshotUrl = 'http://api.dev.civicrm.org/sc/';

  protected function setUp() {
    parent::setUp();
  }

  function testBatchAdd() {
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
    $itemCount = 5;
    // create contact
    $contact = array();


    //Open Live Contribution Page
    $this->open($this->sboxPath . "civicrm/batch?reset=1");
    $this->click("xpath=//div[@class='crm-submit-buttons']/a");
    $this->waitForElementPresent("_qf_Batch_next");
    $this->type("item_count", $itemCount);
    $this->type("total", 500);
    $this->click("_qf_Batch_next");
    $this->waitForPageToLoad('30000');

    // Add Contact Details
    $data = array();
    for ($i=1; $i<=$itemCount; $i++ ) {
      $data[$i] = array (
                     'first_name' => 'Ma'.substr(sha1(rand()), 0, 7),
                     'last_name' => 'An'.substr(sha1(rand()), 0, 7),
                     'contribution_type' => 'Donation',
                     'amount' => 100,           
                     );
      $this->FillData($data[$i], $i);
    }
    $this->click("_qf_Entry_upload");
    $this->waitForPageToLoad('30000');
    foreach ($data as $value) {
      $this->checkResult($value);
    }
  }

  function FillData ($data, $row) {
    $this->webtestNewDialogContact($data['first_name'], $data['last_name'], $data['first_name'] . '@example.com', 4, "primary_profiles_{$row}", "primary_{$row}");
    $this->select("field_{$row}_contribution_type", $data['contribution_type']);
    $this->type("field_{$row}_total_amount", $data['amount']);
    //$this->webtestFillDate('field_1_receive_date_display', 'now');
    $this->webtestFillDateTime("field_1_receive_date", "+1 week");
    $this->type("field_{$row}_contribution_source", substr(sha1(rand()), 0, 10));
    $this->select("field_{$row}_payment_instrument", "Check");
    $this->type("field_{$row}_check_number", rand());
    $this->click("field[{$row}][send_receipt]");
    $this->click("field_{$row}_invoice_id");
    $this->type("field_{$row}_invoice_id", substr(sha1(rand()), 0, 10));
  }

  function checkResult ($data) {
    $this->open($this->sboxPath . "civicrm/contribute/search?reset=1");
    $this->waitForElementPresent("contribution_date_low");
    $this->type("sort_name", "{$data['first_name']} {$data['last_name']}");
    $this->click("_qf_Search_refresh");
    $this->waitForPageToLoad('30000');

    $this->waitForElementPresent("xpath=//div[@id='contributionSearch']//table//tbody/tr[1]/td[11]/span/a[text()='View']");
    $this->click("xpath=//div[@id='contributionSearch']//table//tbody/tr[1]/td[11]/span/a[text()='View']");
    $this->waitForPageToLoad('30000');
    $this->waitForElementPresent("_qf_ContributionView_cancel-bottom");
    $expected = array(
      'From'                => "{$data['first_name']} {$data['last_name']}",
      'Contribution Type'   => $data['contribution_type'],
      'Total Amount'        => $data['amount'],
      'Contribution Status' => 'Completed',
    );   
    $this->webtestVerifyTabularData($expected);
  }
}