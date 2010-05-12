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


 
class WebTest_Member_StandaloneAddTest extends CiviSeleniumTestCase {

  protected $captureScreenshotOnFailure = TRUE;
  protected $screenshotPath = '/var/www/api.dev.civicrm.org/public/sc';
  protected $screenshotUrl = 'http://api.dev.civicrm.org/sc/';
    
  protected function setUp()
  {
      parent::setUp();
  }

  function testStandaloneActivityAdd()
  {

      $this->open( $this->sboxPath );
      $this->webtestLogin();

      $firstName = substr(sha1(rand()), 0, 7);
      $this->webtestAddContact( $firstName, "Memberson", "memberino@memberson.name" );
      $contactName = "Memberson, $firstName";

      $this->open($this->sboxPath . "civicrm/member/add&reset=1&action=add&context=standalone");

      $this->waitForElementPresent("_qf_Membership_upload");

      // select contact
      $this->webtestFillAutocomplete( $firstName );

      // fill in Membership Organization and Type
      $this->select("membership_type_id[1]", "value=1");

      // fill in Source
      $this->type("source", "Membership StandaloneAddTest Webtest");

      // Let Join Date stay default

      // fill in Start Date
      $this->webtestFillDate('start_date');

      // Let End Date be auto computed

      // fill in Status Override?
      // fill in Record Membership Payment?

      //---      

      // Clicking save.
      $this->click("_qf_Membership_upload");
      $this->waitForPageToLoad("30000");

      // Is status message correct?
      $this->assertTrue($this->isTextPresent("membership for has been added."), "Status message didn't show up after saving!");
  }

}
?>
