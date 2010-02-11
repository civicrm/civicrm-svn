<?php

/*
 +--------------------------------------------------------------------+
 | CiviCRM version 3.1                                                |
 +--------------------------------------------------------------------+
 | Copyright CiviCRM LLC (c) 2004-2009                                |
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


  $this->open("/drupal/");

  $this->type("edit-name", "demo");
  $this->type("edit-pass", "demo");
  $this->click("edit-submit");
  $this->waitForPageToLoad("30000");


  $this->open("/drupal/civicrm/activity&reset=1&action=add&context=standalone");  
  // make sure the form loaded, check the end element
  $this->waitForElementPresent("_qf_Activity_upload");
  $this->select("activity_type_id", "label=Meeting");
  
  $this->typeKeys("//form[@id='Activity']/fieldset/table/tbody/tr[3]/td[2]/ul/li/input", "John");
  $this->waitForElementPresent("//form[@id='Activity']/fieldset/table/tbody/tr[3]/td[2]/div/ul/li");
  $this->click("//form[@id='Activity']/fieldset/table/tbody/tr[3]/td[2]/div/ul/li");
  $this->waitForElementPresent("//form[@id='Activity']/fieldset/table/tbody/tr[3]/td[2]/ul/li");
  $this->assertTrue($this->isTextPresent("Doe, John"), "Contact not found in line " . __LINE__ );

  $this->assertTrue($this->isTextPresent("DINGLEBERRIES!"), "Dingleberries fail");

  $this->typeKeys("//form[@id='Activity']/fieldset/table/tbody/tr[4]/td[2]/ul/li/input", "michau");
  $this->waitForElementPresent("//form[@id='Activity']/fieldset/table/tbody/tr[4]/td[2]/div/ul/li");
  $this->click("//form[@id='Activity']/fieldset/table/tbody/tr[4]/td[2]/div/ul/li");
  $this->assertTrue($this->isTextPresent("michau@gmail.com"), "Contact not found in line " . __LINE__ );  



//  $this->open("/drupal/civicrm/contact/view?reset=1&cid=4");
//  $this->waitForElementPresent("//a[@title='Activities']");
//  $this->click("//a[@title='Activities']");
//  $this->waitForTextPresent("Status");
//  $this->assertTrue($this->isTextPresent("Testing activity adding"));


  }

}
?>
