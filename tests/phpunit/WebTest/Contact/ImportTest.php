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


 
class WebTest_Contact_ImportTest extends CiviSeleniumTestCase {

  protected function setUp()
  {
      parent::setUp();
  }

  function testStandaloneActivityAdd()
  {
      $this->open( $this->sboxPath );
      $this->webtestLogin();
      $this->open($this->sboxPath . "civicrm/import/contact&reset=1" );

      $this->type("uploadFile", "/Users/mover/Desktop/ImportContactTestWHdrs.csv");
      $this->click("skipColumnHeader");
      $this->click("_qf_DataSource_upload");
      $this->waitForPageToLoad("30000");
      $this->click("mapper[11][0]");
      $this->select("mapper[11][0]", "label=External Identifier *");
      $this->select("mapper[12][0]", "label=Website");
      $this->select("mapper[15][0]", "label=- do not import -");
      $this->click("_qf_MapField_next");
      $this->waitForPageToLoad("30000");
      $this->click("_qf_Preview_next");
      $this->assertTrue( (bool) preg_match('/^Are you sure you want to Import now[\s\S]$/', $this->getConfirmation() ));
      $this->assertTrue($this->isTextPresent("Import has completed successfully. The information below summarizes the results."));

  }

}
