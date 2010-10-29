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

require_once 'CiviTest/CiviSeleniumTestCase.php';


 
class WebTest_Generic_CheckActivityTest extends CiviSeleniumTestCase {

  protected $captureScreenshotOnFailure = TRUE;
  protected $screenshotPath = '/var/www/api.dev.civicrm.org/public/sc';
  protected $screenshotUrl = 'http://api.dev.civicrm.org/sc/';
    
  protected function setUp()
  {
      parent::setUp();
  }

  function testCheckDashboardElements()
  {
      // This is the path where our testing install resides. 
      // The rest of URL is defined in CiviSeleniumTestCase base class, in
      // class attributes. 
      $this->open( $this->sboxPath );

      // Log in using webtestLogin() method
      $this->webtestLogin();
      
      // Adding contact with randomized first name
      // We're using Quick Add block on the main page for this.
      $contactFirstName1 = substr(sha1(rand()), 0, 7);
      $this->webtestAddContact( $contactFirstName1, "Devis", true );

      // Adding another contact with randomized first name
      // We're using Quick Add block on the main page for this.
      $contactFirstName2 = substr(sha1(rand()), 0, 7);
      $this->webtestAddContact( $contactFirstName2, "Anderson", true );
      $this->open($this->sboxPath . "civicrm/activity&reset=1&action=add&context=standalone");
      
      // make sure the form loaded, check the end element
      $this->waitForElementPresent("_qf_Activity_upload");
      $this->select("activity_type_id", "label=Meeting");
      
      //select 'With Contact'
      $this->click("//form[@id='Activity']/div[2]/table/tbody/tr[3]/td[2]/ul/li/input");
      $this->typeKeys("//form[@id='Activity']/div[2]/table/tbody/tr[3]/td[2]/ul/li/input", $contactFirstName1);
      $this->waitForElementPresent("//form[@id='Activity']/div[2]/table/tbody/tr[3]/td[2]/div/ul/li");
      $this->click("//form[@id='Activity']/div[2]/table/tbody/tr[3]/td[2]/div/ul/li");
      $this->assertTrue($this->isTextPresent("Devis"));
      
      //select 'Assigned To'
      $this->click("//form[@id='Activity']/div[2]/table/tbody/tr[4]/td[2]/ul/li/input");
      $this->typeKeys("//form[@id='Activity']/div[2]/table/tbody/tr[4]/td[2]/ul/li/input", "Anderson");
      $this->waitForElementPresent("//form[@id='Activity']/div[2]/table/tbody/tr[4]/td[2]/div/ul/li");
      $this->click("//form[@id='Activity']/div[2]/table/tbody/tr[4]/td[2]/div/ul/li");
      $this->assertTrue($this->isTextPresent("Anderson"));
  }

}
?>
