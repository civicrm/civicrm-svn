<?php

/*
 +--------------------------------------------------------------------+
 | CiviCRM version 3.2                                                |
 +--------------------------------------------------------------------+
 | Copyright CiviCRM LLC (c) 2004-2009                                |
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


 
class WebTest_Contact_ContactSearch extends CiviSeleniumTestCase {

  protected $captureScreenshotOnFailure = TRUE;
  protected $screenshotPath = '/var/www/api.dev.civicrm.org/public/sc';
  protected $screenshotUrl = 'http://api.dev.civicrm.org/sc/';
    
  protected function setUp()
  {
      parent::setUp();
  }

  function testQuickSearch( )
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
      $this->webtestLogin( );

      // Adding contact
      // We're using Quick Add block on the main page for this.
      $firstName = substr(sha1(rand()), 0, 7);
      $this->webtestAddContact( $firstName, "Anderson", "$firstName.anderson@example.org" );
      
      $sortName    = "Anderson, $firstName";
      $displayName = "$firstName Anderson";
      
      // Go directly to the URL of the screen that you will be testing (New Tag).
      $this->open($this->sboxPath . "civicrm/dashboard?reset=1");
      $this->waitForPageToLoad("30000");

      // type sortname in autocomplete
      $this->typeKeys("css=input#sort_name", $sortName);
      $this->click("css=input#sort_name");

      // wait for result list
      $this->waitForElementPresent("css=div.ac_results-inner li");
      
      // visit contact summary page
      $this->click("css=div.ac_results-inner li");
      $this->waitForPageToLoad("30000");
      
      // Is contact present?
      $this->assertTrue($this->isTextPresent("$displayName"), "Contact did not find!");
  }

  function testContactSearch( )
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
      $this->webtestLogin( );

      // Create new tag.
      $tagName = 'tag_'.substr(sha1(rand()), 0, 7);    
      $this->addTag( $tagName );
      
      // Create new group
      $groupName = 'group_'.substr(sha1(rand()), 0, 7);
      $this->addGroup( $groupName );

      // Adding contact
      // We're using Quick Add block on the main page for this.
      $firstName = substr(sha1(rand()), 0, 7);
      $this->webtestAddContact( $firstName, "Smith", "$firstName.smith@example.org" );
     
      $sortName    = "Smith, $firstName";
      $displayName = "$firstName Smith";

      // add contact to group
      // visit group tab
      $this->click("css=li#tab_group a");
      $this->waitForElementPresent("group_id");

      // add to group
      $this->select("group_id", "label=$groupName");
      $this->click("_qf_GroupContact_next");
      $this->waitForPageToLoad("30000");

      // tag a contact
      // visit tag tab
      $this->click("css=li#tab_tag a");
      $this->waitForElementPresent("css=ul#tagtree");
      
      // select tag
      $this->click("xpath=//ul/li/label[text()=\"$tagName\"]");
      $this->waitForElementPresent("css=.msgok");

      // visit contact search page
      $this->open($this->sboxPath . "civicrm/contact/search&reset=1");
      $this->waitForPageToLoad("30000");

      // fill name as first_name
      $this->type("css=.crm-basic-criteria-form-block input#sort_name", $firstName);

      // select contact type as Indiividual
      $this->select("contact_type", "value=Individual");

      // select group
      $this->select("group", "label=$groupName");

      // select tag
      $this->select("tag", "label=$tagName");

      // click to search
      $this->click("_qf_Basic_refresh");
      $this->waitForPageToLoad("30000");

      // Is contact present in search result?
      $this->assertTrue($this->isTextPresent("$sortName"), "Contact did not found in search result!");

  }

  function addTag( $tagName = 'New Tag') {

      $this->open($this->sboxPath . "civicrm/admin/tag?action=add&reset=1");

      // As mentioned before, waitForPageToLoad is not always reliable. Below, we're waiting for the submit
      // button at the end of this page to show up, to make sure it's fully loaded.
      $this->waitForElementPresent("_qf_Tag_next");

      // fill tag name
      $this->type("name", $tagName);
      
      // fill description
      $this->type("description", "Adding new tag.");

      // select used for contact
      $this->select("used_for", "value=civicrm_contact");

      // check reserved
      $this->click("is_reserved");

      // Clicking save.
      $this->click("_qf_Tag_next");
      $this->waitForPageToLoad("30000");

      // Is status message correct?
      $this->assertTrue($this->isTextPresent("The tag '$tagName' has been saved."));
  }

  function addGroup( $groupName = 'New Group') {
      $this->open($this->sboxPath . "civicrm/group/add&reset=1");
      
      // As mentioned before, waitForPageToLoad is not always reliable. Below, we're waiting for the submit
      // button at the end of this page to show up, to make sure it's fully loaded.
      $this->waitForElementPresent("_qf_Edit_upload");

      // fill group name
      $this->type("title", $groupName);
      
      // fill description
      $this->type("description", "Adding new group.");

      // check Access Control
      $this->click("group_type[1]");

      // check Mailing List
      $this->click("group_type[2]");

      // select Visibility as Public Pages
      $this->select("visibility", "value=Public Pages");
      
      // Clicking save.
      $this->click("_qf_Edit_upload");
      $this->waitForPageToLoad("30000");

      // Is status message correct?
      $this->assertTrue($this->isTextPresent("The Group '$groupName' has been saved."));
  }
}
?>
