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

  $this->typeKeys("//form[@id='Activity']/fieldset/table/tbody/tr[3]/td[2]/ul/li/input", "Anthony");
  $this->waitForElementPresent("//form[@id='Activity']/fieldset/table/tbody/tr[3]/td[2]/div/ul/li");
  $this->click("//form[@id='Activity']/fieldset/table/tbody/tr[3]/td[2]/div/ul/li");
  $this->waitForElementPresent("//form[@id='Activity']/fieldset/table/tbody/tr[3]/td[2]/ul/li");
  $this->assertTrue($this->isTextPresent("Anderson, Anthony"), "Contact not found in line " . __LINE__ );

  $this->typeKeys("//form[@id='Activity']/fieldset/table/tbody/tr[4]/td[2]/ul/li/input", "Samuel");
  $this->waitForElementPresent("//form[@id='Activity']/fieldset/table/tbody/tr[4]/td[2]/div/ul/li");
  $this->click("//form[@id='Activity']/fieldset/table/tbody/tr[4]/td[2]/div/ul/li");
  $this->waitForElementPresent("//form[@id='Activity']/fieldset/table/tbody/tr[4]/td[2]/div/ul/li");
  $this->assertTrue($this->isTextPresent("Summerson, Samuel"), "Contact not found in line " . __LINE__ );  

  $this->assertTrue($this->isTextPresent("DINGLEBERRIES!"), "Dingleberries fail");

  $this->type("subject", "Blah blah this is subject");
  $this->type("location", "Some location needs to be put in this field.");

  $this->click("activity_date_time");
  $this->click("link=16");

  $this->type("duration", "30");

  $this->type("details", "Details information.");

  $this->select("priority_id", "label=Urgent");                

  $this->click("_qf_Activity_upload");
  $this->waitForPageToLoad("30000");

  }

}
?>
