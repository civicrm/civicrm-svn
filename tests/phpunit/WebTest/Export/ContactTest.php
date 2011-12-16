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

require_once 'WebTest/Export/ExportCiviSeleniumTestCase.php';
 
class WebTest_Export_ContactTest extends ExportCiviSeleniumTestCase {

  protected $captureScreenshotOnFailure = TRUE;
  protected $screenshotPath = '/var/www/api.dev.civicrm.org/public/sc';
  protected $screenshotUrl = 'http://api.dev.civicrm.org/sc/';
    
  protected function setUp()
  {
      parent::setUp();
  }

  /**
   *  Test Contact Export.
   */
  function testContactExport()
  {
    $this->open( $this->sboxPath );
      
      // Logging in. Remember to wait for page to load. In most cases,
      // you can rely on 30000 as the value that allows your test to pass, however,
      // sometimes your test might fail because of this. In such cases, it's better to pick one element
      // somewhere at the end of page and use waitForElementPresent on it - this assures you, that whole
      // page contents loaded and you can continue your test execution.
      $this->webtestLogin( );

      // Create new  group
      $parentGroupName = 'Parentgroup_'.substr(sha1(rand()), 0, 7);
      $this->addContactGroup( $parentGroupName );
      
      // Create new group and select the previously selected group as parent group for this new group.
      $childGroupName = 'Childgroup_'.substr(sha1(rand()), 0, 7);
      $this->addContactGroup( $childGroupName, $parentGroupName );

      // Adding Parent group contact
      // We're using Quick Add block on the main page for this. 
      $firstName = 'a' . substr(sha1(rand()), 0, 7);
      $this->webtestAddContact( $firstName, "Smith", "$firstName.smith@example.org" );
      
      $sortName    = "Smith, $firstName";
      $displayName = "$firstName Smith";
      
      // Add contact to parent  group
      // visit group tab.
      $this->click("css=li#tab_group a");
      $this->waitForElementPresent("group_id");
      
      // Add to group.
      $this->select("group_id", "label=$parentGroupName");
      $this->click("_qf_GroupContact_next");
      $this->waitForPageToLoad("30000");
      
      // Adding child group contact
      // We're using Quick Add block on the main page for this.
      $childName = 'b' . substr(sha1(rand()), 0, 7);
      $this->webtestAddContact( $childName, "John", "$childName.john@example.org" );
      
      $childSortName    = "John, $childName";
      $childDisplayName = "$childName John";
      
      // Add contact to child group
      // visit group tab.
      $this->click("css=li#tab_group a");
      $this->waitForElementPresent("group_id");
      
      // Add to child group.
      $this->select("group_id", "label=$childGroupName");
      $this->click("_qf_GroupContact_next");
      $this->waitForPageToLoad("30000");

      // Visit contact search page.
      $this->open($this->sboxPath . "civicrm/contact/search?reset=1");
      $this->waitForPageToLoad("30000");

      // Select contact type as Indiividual.
      $this->select("contact_type", "value=Individual");

      // Select group.
      $this->select("group", "label=$parentGroupName");
      
      // Click to search.
      $this->click("_qf_Basic_refresh");
      $this->waitForPageToLoad("30000");

      // Is contact present in search result?
      $this->assertTrue($this->isTextPresent("$sortName"), "Contact did not found in search result!");
      
      // Is contact present in search result?
      $this->assertTrue($this->isTextPresent("$childSortName"), "Contact did not found in search result!");
           
      // select to export all the contasct from search result.
      $this->click("CIVICRM_QFID_ts_all_4");
      
      // Select the task action to export.
      $this->click("task");
      $this->select("task", "label=Export Contacts");
      $this->click("Go");
      $this->waitForPageToLoad("30000");
      
      $csvFile = $this->downloadCSV("_qf_Select_next-bottom");
      
      // Build header row for assertion.
      require_once 'CRM/Contact/BAO/Contact.php';
      $expotableFields = CRM_Contact_BAO_Contact::exportableFields('All', false, true);

      $checkHeaders = array();
      foreach ($expotableFields as $key => $field) {
        // Exclude custom fields.
        if ( $key && ( substr( $key, 0, 6 ) ==  'custom' ) ) {
          continue;
        }
        if ($field['title'] == 'External Identifier') {
          // Hack to check 'External Identifier' as 'External Identifier (match to contact)'
          $field['title'] = 'External Identifier (match to contact)';
        }
        $checkHeaders[] = $field['title'];
      }

      // All other rows to be check.
      $checkRows = array(
        1 => array(
          'First Name' => $firstName,
          'Last Name'  => 'Smith',
          'Email' => "$firstName.smith@example.org",
          'Sort Name' => $sortName,
          'Display Name' => $displayName,
        ),
        2 => array(
          'First Name' => $childName,
          'Last Name' => 'John',
          'Email' => "$childName.john@example.org",
          'Sort Name' => $childSortName,
          'Display Name' => $childDisplayName,
        ),
      );

      // Read CSV and fire assertions.
      $this->reviewCSV($csvFile, $checkHeaders, $checkRows, 2);
  }

  function addContactGroup( $groupName = 'New Group', $parentGroupName = "- select -") {
      $this->open($this->sboxPath . "civicrm/group/add?reset=1");

      // As mentioned before, waitForPageToLoad is not always reliable. Below, we're waiting for the submit
      // button at the end of this page to show up, to make sure it's fully loaded.
      $this->waitForElementPresent("_qf_Edit_upload");

      // Fill group name.
      $this->type("title", $groupName);

      // Fill description.
      $this->type("description", "Adding new group.");

      // Check Access Control.
      $this->click("group_type[1]");

      // Check Mailing List.
      $this->click("group_type[2]");

      // Select Visibility as Public Pages.
      $this->select("visibility", "value=Public Pages");

      // Select parent group.
      $this->select("parents", "label=$parentGroupName");

      // Clicking save.
      $this->click("_qf_Edit_upload");
      $this->waitForPageToLoad("30000");

      // Is status message correct?
      $this->assertTrue($this->isTextPresent("The Group '$groupName' has been saved."));
  }

}
