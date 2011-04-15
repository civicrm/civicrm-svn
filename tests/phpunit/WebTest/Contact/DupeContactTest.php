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


 
class WebTest_Contact_DupeContactTest extends CiviSeleniumTestCase {

  protected function setUp()
  {
      parent::setUp();
  }

  function testIndividualAdd( )
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
      
      // Go directly to the URL of New Individual.
      $this->open($this->sboxPath . "civicrm/contact/add&reset=1&ct=Individual");

      //contact details section
      //select prefix
      $this->click( "prefix_id" );
      $this->select( "prefix_id", "value=3" );
      
      //fill in first name
      $this->type( "first_name", "Robin" );
      
      //fill in last name
      $this->type( "last_name", "Hood" );
      
      //fill in email
      $this->type( "email_1_email", "robin@example.com" );
      
      //check for matching contact
      //$this->click("_qf_Contact_refresh_dedupe");
      //$this->waitForPageToLoad("30000");
      
      // Clicking save.
      $this->click("_qf_Contact_upload_view");
      $this->waitForPageToLoad("30000");
      
      $this->isTextPresent("Your Individual contact record has been saved.");
     
      // Go directly to the URL of New Individual.
      $this->open($this->sboxPath . "civicrm/contact/add&reset=1&ct=Individual");
      
      //contact details section
      
      
      //fill in first name
      $this->type( "first_name", "Robin" );
      
      //fill in last name
      $this->type( "last_name", "Hood" );
      
      //fill in email
      $this->type( "email_1_email", "robin@example.com" );
    
      // Clicking save.
      $this->click( "_qf_Contact_upload_view" );
      $this->waitForPageToLoad( "30000" );
      
      $this->isTextPresent( "Please correct the following errors in the form fields below: One matching contact was found. You can View or Edit the existing contact, or Merge this contact with an existing contact." );

      // edit the default Fuzzy rule
      $this->open( $this->sboxPath . "civicrm/contact/deduperules?action=update&id=1" );
      $this->click( "threshold" );
      $this->type( "threshold", "10" );
      $this->click( "_qf_DedupeRules_next-bottom" );
      $this->waitForPageToLoad( "30000" );
      
      // Go directly to the URL of New Individual.
      $this->open($this->sboxPath . "civicrm/contact/add&reset=1&ct=Individual");
      
      //fill in first name
      $this->type( "first_name", "Robin" );
      
      //fill in last name
      $this->type( "last_name", "Dale" );
      
      //fill in email
      $this->type( "email_1_email", "robin@example.com" );
    
      // Clicking save.
      $this->click( "_qf_Contact_upload_view" );
      $this->waitForPageToLoad( "30000" );
      $this->isTextPresent( "Please correct the following errors in the form fields below: One matching contact was found. You can View or Edit the existing contact, or Merge this contact with an existing contact." );
      $this->click( "_qf_Contact_upload_duplicate" );
  }  
}
?>
