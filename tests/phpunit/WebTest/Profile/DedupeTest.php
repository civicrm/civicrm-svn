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

class WebTest_Profile_DedupeTest extends CiviSeleniumTestCase {

     protected function setUp()
     {
         parent::setUp();
     }

     function testProfileCreateDupeStrictDefault( ) 
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

         // Go directly to the URL of the screen that you will beadding New Individual.
         $this->open( $this->sboxPath . "civicrm/contact/add?reset=1&ct=Individual" );
         
         $firstName = "John" . substr(sha1(rand()), 0, 7);
         $lastName  = "Smith" . substr(sha1(rand()), 0, 7);
         $email     = $firstName."@".$lastName.".com";
         // fill in first name
         $this->type( "first_name", $firstName );
        
         // fill in last name
         $this->type( "last_name", $lastName );
         
         // fill in email
         $this->type( "email_1_email", $email );

         // Clicking save.
         $this->click( "_qf_Contact_upload_view" );
         $this->waitForPageToLoad( "30000" );
         
         $this->assertTrue( $this->isTextPresent( "Your Individual contact record has been saved." ) );

         // lets give profile related permision to anonymous user.
         $this->changeAdminLinks();
         $this->waitForElementPresent("edit-submit");

         $this->check( "edit-1-profile-create" );
         $this->check( "edit-1-profile-edit" );
         $this->check( "edit-1-profile-listings" );
         $this->check( "edit-1-profile-view" );
         
         // save permission
         $this->click("edit-submit");
         $this->waitForPageToLoad("30000");
         $this->assertTrue( $this->isTextPresent( "The changes have been saved." ) );

         // logout and sign as anonymous.
         $this->open( $this->sboxPath ."civicrm/logout?reset=1" );

         // submit dupe using profile/create as anonymous
         $this->open( $this->sboxPath . "civicrm/profile/create?gid=4&reset=1" );
         $this->waitForPageToLoad( "30000" );
         $this->waitForElementPresent( "_qf_Edit_next" );

         $firstName = "John" . substr(sha1(rand()), 0, 7);
         $lastName  = "Smith" . substr(sha1(rand()), 0, 7);

         // fill in first name
         $this->type( "first_name", $firstName );
        
         // fill in last name
         $this->type( "last_name", $lastName );
         
         // fill in email
         $this->type( "email-Primary", $email );

         // click save
         $this->click( "_qf_Edit_next" );
         $this->waitForTextPresent( "A record already exists with the same information." );
     }

     function testProfileCreateDupeCustomStrictRules( ) 
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

         // Go directly to the URL of the screen that you will beadding New Individual.
         $this->open( $this->sboxPath . "civicrm/contact/add?reset=1&ct=Individual" );
         
         $firstName = "John" . substr(sha1(rand()), 0, 7);
         $lastName  = "Smith" . substr(sha1(rand()), 0, 7);
         $email     = $firstName."@".$lastName.".com";
         // fill in first name
         $this->type( "first_name", $firstName );
        
         // fill in last name
         $this->type( "last_name", $lastName );
         
         // fill in email
         $this->type( "email_1_email", $email );

         // Clicking save.
         $this->click( "_qf_Contact_upload_view" );
         $this->waitForPageToLoad( "30000" );
         
         $this->assertTrue( $this->isTextPresent( "Your Individual contact record has been saved." ) );
         
         // logout and sign as anonymous.
         $this->open( $this->sboxPath ."civicrm/logout?reset=1" );

         // submit dupe using profile/create as anonymous
         $this->open( $this->sboxPath . "civicrm/profile/create?gid=4&reset=1" );
         $this->waitForPageToLoad( "30000" );
         $this->waitForElementPresent( "_qf_Edit_next" );
         
         // lets keep the first_name and last_name duplicate.
         $email = "first@last.com";
         
         // fill in first name
         $this->type( "first_name", $firstName );
        
         // fill in last name
         $this->type( "last_name", $lastName );
         
         // fill in email
         $this->type( "email-Primary", $email );

         // click save
         $this->click( "_qf_Edit_next" );
         $this->waitForTextPresent( "A record already exists with the same information." );
     }
}
?>