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


 
class WebTest_Generic_GeneralClickAroundTest extends CiviSeleniumTestCase {

  protected function setUp()
  {
      parent::setUp();
  }

  function testSearchMenu()
  {

      $this->open( $this->sboxPath );

      // Log in using webtestLogin() method
      $this->webtestLogin();
      $this->waitForPageToLoad('50000');
      $this->click("link=CiviCRM");
      $this->waitForPageToLoad('50000');

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

}
