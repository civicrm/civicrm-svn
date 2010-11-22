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


 
class WebTest_Generic_GeneralClickAroundTest extends CiviSeleniumTestCase {

  protected function setUp()
  {
      parent::setUp();
  }

  function login()
  {
      $this->open($this->sboxPath);
      $this->webtestLogin();
      $this->waitForPageToLoad();
      $this->click("//a[contains(text(),'CiviCRM')]");
      $this->waitForPageToLoad();
  }

  function testSearchMenu()
  {
      $this->login();

      // click Search -> Find Contacts
      $this->click("//ul[@id='civicrm-menu']/li[3]");
      $this->click("//div[@id='root-menu-div']/div[2]/ul/li[1]/div/a");
      $this->waitForElementPresent("tag");

      $this->click("contact_type");
      $this->select("contact_type", "label=Individual");
      $this->select("group", "label=Newsletter Subscribers");
      $this->select("tag", "label=Major Donor");
      $this->click("_qf_Basic_refresh");
      $this->waitForElementPresent("search-status");
      $this->assertText("search-status","Contacts IN Newsletter Subscribers ...AND...");
      
      // Advanced Search by Tag
      $this->click("//ul[@id='civicrm-menu']/li[3]");
      $this->click("//div[@id='root-menu-div']/div[2]/ul/li[2]/div/a");
      $this->waitForElementPresent("_qf_Advanced_refresh");
      $this->click("crmasmSelect2");
      $this->select("crmasmSelect2", "label=Major Donor");
      $this->waitForElementPresent("//ul[@id='crmasmList2']/li/span");
      $this->click("_qf_Advanced_refresh");
      $this->waitForElementPresent("search-status");
      $this->assertText("search-status","Tagged IN Major Donor");
  }

  function testNewIndividual()
  {
      $this->login();

      // Create New → Individual
      $this->click("crm-create-new-link");
      $this->click("link=Individual");
      $this->waitForPageToLoad();

      $this->assertElementPresent("frist_name");
      $this->assertElementPresent("email_1_email");
      $this->assertElementPresent("phone_1_phone");
      $this->assertElementPresent("contact_source");
      $this->assertTextPresent("Constituent Information");
      $this->click("//form[@id='Contact']/div[2]/div[4]/div[1]");
      $this->click("//div[@id='customData1']/table/tbody/tr[1]/td[1]/label");
      $this->assertTextPresent("Most Important Issue");
      $this->click("//form[@id='Contact']/div[2]/div[6]/div[1]");
      $this->assertTextPresent("Communication Preferences");
      $this->assertTextPresent("Do not phone");
  }

  function testManageGroups()
  {
      $this->login();

      // Contacts → Manage Groups
      $this->click("//ul[@id='civicrm-menu']/li[4]");
      $this->click("//div[@id='root-menu-div']/div[5]/ul/li[11]/div/a");
      $this->waitForPageToLoad();

      $this->assertTextPresent("Find Groups");
      $this->assertElementPresent("title");
      $this->assertTextPresent("Access Control");
      $this->assertTextPresent("Newsletter Subscribers");
      $this->assertTextPresent("Add Group");
  }

  function testContributionDashboard()
  {
      $this->login();

      // Contributions → Dashboard
      $this->click("//ul[@id='civicrm-menu']/li[5]");
      $this->click("//div[@id='root-menu-div']/div[7]/ul/li[1]/div/a");
      $this->waitForPageToLoad();

      $this->assertTextPresent("Contribution Summary");
      $this->assertTextPresent("Select Year (for monthly breakdown)");
      $this->assertTextPresent("Recent Contributions");
      $this->assertTextPresent("Find more contributions...");
  }

  function testEventDashboard()
  {
      $this->login();

      // Events → Dashboard
      $this->click("//ul[@id='civicrm-menu']/li[6]");
      $this->click("//div[@id='root-menu-div']/div[8]/ul/li[1]/div/a");
      $this->waitForPageToLoad();

      $this->assertTextPresent("Event Summary");
      $this->assertTextPresent("Fall Fundraiser Dinner");
      $this->assertTextPresent("Counted:");
      $this->assertTextPresent("Not Counted:");
      $this->assertTextPresent("Not Counted Due To Status:");
      $this->assertTextPresent("Not Counted Due To Role:");
      $this->assertTextPresent("Registered:");
      $this->assertTextPresent("Attended:");
      $this->assertTextPresent("No-show:");
      $this->assertTextPresent("Cancelled:");
      $this->assertTextPresent("Recent Registrations");
      $this->assertTextPresent("Find more event participants...");
  }

  function testMembershipsDashboard()
  {
      $this->login();

      // Memberships → Dashboard
      $this->click("//ul[@id='civicrm-menu']/li[8]");
      $this->click("//div[@id='root-menu-div']/div[10]/ul/li[1]/div/a");
      $this->waitForPageToLoad();

      $this->assertTextPresent("Membership Summary");
      $this->assertTextPresent("Members by Type");
      $this->assertTextPresent("Recent Memberships");
      $this->assertTextPresent("Find more members...");
  }

  function testFindContributions()
  {
      $this->login();

      // Search → Find Contributions
      $this->click("//ul[@id='civicrm-menu']/li[3]");
      $this->click("//div[@id='root-menu-div']/div[2]/ul/li[6]/div/a");
      $this->waitForPageToLoad();

      $this->assertTextPresent("Edit Search Criteria");
      $this->assertElementPresent("sort_name");
      $this->assertElementPresent("contribution_date_low");
      $this->assertElementPresent("contribution_amount_low");
      $this->assertElementPresent("contribution_check_number");
      $this->assertTextPresent("Contribution Type");
      $this->assertTextPresent("Contribution Page");
      $this->assertElementPresent("contribution_in_honor_of");
      $this->assertElementPresent("contribution_source");
      $this->assertTextPresent("Personal Campaign Page");
      $this->assertTextPresent("Display In Roll");
      $this->assertTextPresent("Currency Type");
  }
}
