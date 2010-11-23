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


 
class WebTest_Campaign_SurveyUsageScenario extends CiviSeleniumTestCase {

  protected $captureScreenshotOnFailure = TRUE;
  protected $screenshotPath = '/tmp/';
  protected $screenshotUrl = 'http://api.dev.civicrm.org/sc/';
    
  protected function setUp()
  {
      parent::setUp();
  }
  
  function testSurveyUsageScenario()
  {
      // This is the path where our testing install resides. 
      // The rest of URL is defined in CiviSeleniumTestCase base class, in
      // class attributes.
      $this->open( $this->sboxPath );

      // Logging in. Remember to wait for page to load. In most cases,
      // you can rely on 30000 as the value that allows your test to pass, however,
      // sometimes your test might fail because of this. In such cases, it's better to pick one element
      // somewhere at the end of page and use waitForElementPresent on it - this assures you, that whole
      // page contents loaded and you can continue your test execution.
      $this->webtestLogin();


      // Enable CiviCampaign module if necessary
      $this->open($this->sboxPath . "civicrm/admin/setting/component?reset=1");
      $this->waitForPageToLoad('30000');
      $this->waitForElementPresent("_qf_Component_next-bottom");
      $enabledComponents = $this->getSelectOptions("enableComponents-t");
      if (! array_search( "CiviCampaign", $enabledComponents ) ) {
          $this->addSelection("enableComponents-f", "label=CiviCampaign");
          $this->click("//option[@value='CiviCampaign']");
          $this->click("add");
          $this->click("_qf_Component_next-bottom");
          $this->waitForPageToLoad("30000");          
          $this->assertTrue($this->isTextPresent("Your changes have been saved."));    
      }

      // Go directly to the URL of the screen that you will be testing
      $this->open($this->sboxPath . "civicrm/campaign/add&reset=1");

      // As mentioned before, waitForPageToLoad is not always reliable. Below, we're waiting for the submit
      // button at the end of this page to show up, to make sure it's fully loaded.
      $this->waitForElementPresent("_qf_Campaign_next-bottom");

      // Let's start filling the form with values.
      $title = substr(sha1(rand()), 0, 7);
      $this->type("title", "$title Campaign");

      // select the campaign type
      $this->select("campaign_type_id", "value=2");

      // fill in the description
      $this->type("description", "This is a test campaign");

      // include groups for the campaign
      $this->addSelection("includeGroups-f", "label=Advisory Board");
      $this->click("//option[@value=4]");
      $this->click("add");

      // fill the end date for campaign
      $this->webtestFillDate("end_date", "+1 year");
      
      // select campaign status
      $this->type("status_id", "value=2");

      // click save
      $this->click("_qf_Campaign_next-bottom");
      $this->waitForPageToLoad("30000");
      
      $this->assertTrue($this->isTextPresent("Campaign $title Campaign has been saved."), "Status message didn't show up after saving!");
          
  }
}