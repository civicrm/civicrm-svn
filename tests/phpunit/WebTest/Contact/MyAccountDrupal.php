<?php

/*
 +--------------------------------------------------------------------+
 | CiviCRM version 3.4                                                |
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


 
class WebTest_Contact_MyAccountDrupalTest extends CiviSeleniumTestCase {
    
  protected function setUp()
  {
      parent::setUp();
  }
  
  function testEditMyAccountDrupal( )
  {
      $this->open( $this->sboxPath );
      
      $this->webtestLogin();
      
      // Go directly to the My Account screen.
      $this->open( $this->sboxPath . "user" );    
      $this->waitForPageToLoad( "30000" );
    
      // user name used for login should be present on the page
      $this->assertTrue($this->isTextPresent($this->settings->username), "Expected Drupal User Name - {$this->settings->username} - not found on My Account screen.");
      $this->assertTrue($this->isTextPresent("Member for"), "Expected text - 'Member for' - not found on My Account screen.");
      
  }  

}
