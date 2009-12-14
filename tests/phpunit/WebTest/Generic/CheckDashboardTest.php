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
 
class WebTest_Generic_CheckDashboardTest extends CiviSeleniumTestCase
{
    
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
    $this->open("/drupal/civicrm/");
    $this->waitForPageToLoad("30000");
    $this->click("link=CiviCRM");
    $this->waitForPageToLoad("30000");
    // $this->assertTrue($this->isTextPresent("Activities"));
    $this->assertTrue($this->isElementPresent("link=My Contact Dashboard"));
  }

}
?>
