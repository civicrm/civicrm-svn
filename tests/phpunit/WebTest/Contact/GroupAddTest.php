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

require_once 'CiviTest/CiviSeleniumTestCase.php';



class WebTest_Contact_GroupAddTest extends CiviSeleniumTestCase {

  protected function setUp()
  {
      parent::setUp();
  }

  function testGroupAdd( $params = array( ) )
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

      // create a new group with given parameters

      // Go directly to the URL of the screen that you will be testing (New Group).
      $this->open($this->sboxPath . "civicrm/group/add?&reset=1");

      // As mentioned before, waitForPageToLoad is not always reliable. Below, we're waiting for the submit
      // button at the end of this page to show up, to make sure it's fully loaded.
      $this->waitForElementPresent("_qf_Edit_upload");

      // take group name
      if ( empty( $params['name'] ) ) {
          $params['name'] = 'group_'.substr(sha1(rand()), 0, 7);
      }

      // fill group name
      $this->type("title", $params['name']);

      // fill description
      $this->type("description", "Adding new group.");

      // check Access Control
      if ( isset ( $params['type1'] ) && $params['type1'] !== FALSE ) {
          $this->click("group_type[1]");
      }

      // check Mailing List
      if ( isset ( $params['type2'] ) && $params['type2'] !== FALSE ) {
          $this->click("group_type[2]");
      }

      // select Visibility as Public Pages
      if ( empty( $params['visibility'] ) ) {
          $params['visibility'] = 'Public Pages';
      }

      $this->select("visibility", "value={$params['visibility']}");

      // Clicking save.
      $this->click("_qf_Edit_upload");
      $this->waitForPageToLoad("30000");

      // Is status message correct?
      $this->assertTrue($this->isTextPresent("The Group '{$params['name']}' has been saved."));

  }

}
?>
