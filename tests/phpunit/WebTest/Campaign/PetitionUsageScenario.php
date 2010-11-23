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


 
class WebTest_Campaign_PetitionUsageScenario extends CiviSeleniumTestCase {

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

      /////////////// Create Campaign ///////////////////////////////
      
      // Go directly to the URL of the screen that you will be add campaign
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
      $this->select("status_id", "value=2");

      // click save
      $this->click("_qf_Campaign_next-bottom");
      $this->waitForPageToLoad("30000");
      
      $this->assertTrue($this->isTextPresent("Campaign $title Campaign has been saved."), "Status message didn't show up after saving!");

      ////////////// Create petition using New Individual profile //////////////////////
      
      // Go directly to the URL of the screen that you will be add petition
      $this->open( $this->sboxPath . "civicrm/petition/add&reset=1" );
      
      // button at the end of this page to show up, to make sure it's fully loaded.
      $this->waitForElementPresent("_qf_Petition_next-bottom");

      // fill petition tile.
      $title = substr(sha1(rand()), 0, 7);
      $this->type("title", "$title Petition");

      // fill introdyction 
      //$this->type("cke_instructions", "This is introduction of $title Petition");
      
      // select campaign 
      $this->select("campaign_id", "value=1");
      
      // select profile
      $this->select("contact_profile_id", "value=4" );
      
      // click save
      $this->click("_qf_Petition_next-bottom");
      $this->waitForPageToLoad("30000");

      $this->assertTrue($this->isTextPresent("Petition has been saved."));
      $this->waitForElementPresent("xpath=//table/tbody//tr//td[1][text()='$title Petition']/../td[5]/span[2][text()='more ']/ul/li/a[text()='Sign']");
      $this->click("xpath=//table/tbody//tr//td[1][text()='$title Petition']/../td[5]/span[2][text()='more ']/ul/li/a[text()='Sign']");
      
      $this->waitForPageToLoad("30000");
      $url = $this->getLocation();
     
      ////////////// Retrieve Sign Petition Url /////////////////////////
      
      // let's give permission 'sign CiviCRM Petition' to anonymous user.
      $this->open( $this->sboxPath ."admin/user/permissions");
      $this->waitForElementPresent("edit-submit");
      $this->check("edit-1-sign-CiviCRM-Petition");
      
      // give profile related permision
      $this->check("edit-1-profile-create");
      $this->check("edit-1-profile-edit");
      $this->check("edit-1-profile-listings");
      $this->check("edit-1-profile-view");
      
      // save permission
      $this->click("edit-submit");
      $this->waitForPageToLoad("30000");
      $this->assertTrue($this->isTextPresent("The changes have been saved."));
      
      // logout and sign as anonymous.
      $this->open( $this->sboxPath ."logout" );
      
      // go to the link that you will be sign as anonymous
      $this->open($url);
      $this->waitForElementPresent("_qf_Signature_next-bottom");

      // fill first name
      $firstName = substr(sha1(rand()), 0, 7);
      $this->type("first_name", $firstName);

      // fill last name
      $lastName = substr(sha1(rand()), 0, 7);
      $this->type("last_name", $lastName);

      // fill email
      $email = $firstName ."@" . $lastName . ".com";
      $this->type("email-Primary", $email);

      // click Sign the petition.
      $this->click("_qf_Signature_next-bottom");
      $this->waitForPageToLoad("30000");
      $this->assertTrue($this->isTextPresent("Thank You"));
      
      // login 
      $this->open( $this->sboxPath );
      $this->webtestLogin();

      $this->open($this->sboxPath . "civicrm/campaign&reset=1&subPage=petition");
      $this->waitForPageToLoad("30000");
      $this->waitForElementPresent("link=Add Petition");

      // check for unconfirmed petition signature
      $this->waitForElementPresent("xpath=//table/tbody//tr//td[1][text()='$title Petition']/../td[5]/span[2][text()='more ']/ul/li/a[text()='Signatures']");
      $this->click("xpath=//table/tbody//tr//td[1][text()='$title Petition']/../td[5]/span[2][text()='more ']/ul/li/a[text()='Signatures']");
      $this->waitForPageToLoad("30000");
      
      // verify tabular data.
      $this->waitForElementPresent("xpath=//table/tbody//tr[2]//td[2][text()='Petition']");
      $this->waitForElementPresent("xpath=//table/tbody//tr[2]//td[3][text()='$title Petition']");
           
  }
}