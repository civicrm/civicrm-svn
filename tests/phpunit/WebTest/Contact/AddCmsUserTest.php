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


 
class WebTest_Contact_AddCmsUserTest extends CiviSeleniumTestCase {
    
  protected $captureScreenshotOnFailure = TRUE;
  protected $screenshotPath = '/var/www/api.dev.civicrm.org/public/sc';
  protected $screenshotUrl = 'http://api.dev.civicrm.org/sc/';
    
  protected function setUp()
  {
      parent::setUp();
  }
  
  function testAuthenticAddUser( )
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
      
      // Go directly to the URL of the screen that will Create User Authentically.
      $this->open( $this->sboxPath . "admin/user/user/create" );
      
      
      $this->waitForElementPresent( "edit-submit" );
      
      $name = "TestUserAuthenticate";
      $this->type( "edit-name", $name );
      
      $emailId   = substr(sha1(rand()), 0, 7).'@web.com';
      $this->type( "edit-mail", $emailId );
      $this->type( "edit-pass-pass1", "Test12345" );
      $this->type( "edit-pass-pass2", "Test12345" );
      
      //Add profile Details 
      $firstName = 'Ma'.substr(sha1(rand()), 0, 4);
      $lastName  = 'An'.substr(sha1(rand()), 0, 7);
      
      $this->type( "first_name", $firstName );
      $this->type( "last_name", $lastName );
      
      //Address Details
      $this->type( "street_address-1", "902C El Camino Way SW" );
      $this->type( "city-1", "Dumfries" );
      $this->type( "postal_code-1", "1234" );
      $this->assertTrue( $this->isTextPresent( "- select - United States" ) );
      $this->select( "state_province-1", "value=1019" );
      
      $this->click( "edit-submit" );
      $this->waitForPageToLoad( "30000" );
      
      $this->assertTrue( $this->isTextPresent( "Created a new user account for '$name'" ) );
      
      
      
  }  
  function testAnonymousAddUser( )
  {
      // This is the path where our testing install resides. 
      // The rest of URL is defined in CiviSeleniumTestCase base class, in
      // class attributes.
      $this->open( $this->sboxPath );
      
      // Go directly to the URL of the screen that will Create User Anonymously.
      $this->open( $this->sboxPath . "user/register" );
      
      $this->waitForElementPresent( "edit-submit" );
      $name = "TestUserAnonymous";
      $this->type( "edit-name", $name );
      $emailId   = substr(sha1(rand()), 0, 7).'@web.com';
      $this->type( "edit-mail", $emailId );
      
      
      //Add profile Details 
      $firstName = 'Ma'.substr(sha1(rand()), 0, 4);
      $lastName  = 'An'.substr(sha1(rand()), 0, 7);
      $this->type( "first_name", $firstName );
      $this->type( "last_name", $lastName );
      
      //Address Details
      $this->type( "street_address-1", "902C El Camino Way SW" );
      $this->type( "city-1", "Dumfries" );
      $this->type("postal_code-1", "1234" );
      $this->assertTrue( $this->isTextPresent( "- select - United States" ) );
      $this->select( "state_province-1", "value=1019" );
      
      $this->click( "edit-submit" );
      $this->waitForPageToLoad( "30000" );
      
      $this->assertTrue( $this->isTextPresent( "Created a new user account for '$name'" ) );
      
      
      
  }  
}
?>
