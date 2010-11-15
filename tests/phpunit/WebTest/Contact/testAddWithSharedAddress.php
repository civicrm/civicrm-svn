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


 
class WebTest_Contact_testAddWithSharedAddress extends CiviSeleniumTestCase {

  protected function setUp()
  {
      parent::setUp();
  }

  function testIndividualAddWithSharedAddress( )
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

      // Go directly to the URL of the screen that you will be testing (New Individual).
      $this->open($this->sboxPath . "civicrm/contact/add&reset=1&ct=Individual");

      //contact details section
      //select prefix
      $this->click("prefix_id");
      $this->select("prefix_id", "value=3");

      //fill in first name
      $this->type("first_name", "John" . substr(sha1(rand()), 0, 7));

      //fill in middle name
      $this->type("middle_name", "Bruce");

      //fill in last name
      $this->type("last_name", "Smith" . substr(sha1(rand()), 0, 7));

      //create new current employer
      $currentEmployer = "Web Access" . substr(sha1(rand()), 0, 7);

      $this->type( 'current_employer', $currentEmployer );

      //fill in email
      $this->type("email_1_email", substr(sha1(rand()), 0, 7) . "john@gmail.com");

      //fill in phone
      $this->type("phone_1_phone", "2222-4444");

      //fill in source
      $this->type("contact_source", "johnSource");

      //address section    
      $this->click("addressBlock");
      $this->waitForElementPresent("address_1_street_address");

      $this->select( 'address_1_location_type_id', 'value=2');  

      $this->click('address[1][use_shared_address]');

      // create new organization with dialog
      $this->select("profiles_1", "value=5,8");

      // create new contact using dialog
      $this->waitForElementPresent("css=div#contact-dialog-1");
      $this->waitForElementPresent("_qf_Edit_next");

      $this->type( 'organization_name', $currentEmployer );
      $this->type( 'street_address-1', '902C El Camino Way SW' );
      $this->type( 'city-1', 'Dumfries' );
      $this->type( 'postal_code-1', '1234' );
      $this->select('state_province-1', 'value=1019');

      $this->click("_qf_Edit_next");

      // Is new contact created?
      $this->assertTrue($this->isTextPresent("New contact has been created."), "Status message didn't show up after saving!");

      //make sure shared address is selected
      $this->waitForElementPresent( 'selected_shared_address-1'); 

      //fill in address 2
      $this->click("//div[@id='addMoreAddress1']/a/span");
      $this->waitForElementPresent("address_2_street_address");

      $this->select( 'address_2_location_type_id', 'value=1' );  

      $this->click('address[2][use_shared_address]');

      // create new household with dialog
      $this->select( 'profiles_2', 'value=6,8');

      // create new contact using dialog
      $this->waitForElementPresent("css=div#contact-dialog-2");
      $this->waitForElementPresent("_qf_Edit_next");

      $sharedHousehold = 'Smith Household' . substr(sha1(rand()), 0, 7 ) ;  
      $this->type( 'household_name', $sharedHousehold );
      $this->type( 'street_address-1', '2782Y Dowlen Path W' );
      $this->type( 'city-1', 'Birmingham' );
      $this->type( 'postal_code-1', '3456' );
      $this->select('state_province-1', 'value=1002');

      $this->click("_qf_Edit_next");

      // Is new contact created?
      $this->assertTrue($this->isTextPresent("New contact has been created."), "Status message didn't show up after saving!");

      //make sure shared address is selected
      $this->waitForElementPresent( 'selected_shared_address-2'); 

      // Clicking save.
      $this->click("_qf_Contact_upload_view");
      $this->waitForPageToLoad("30000");

      $this->assertTrue($this->isTextPresent("Your Individual contact record has been saved."));
  
      //make sure current employer is set
      $this->verifyText("xpath=id('contactTopBar')/table/tbody/tr/td[3]", 'Employer' );
      $this->verifyText("xpath=id('contactTopBar')/table/tbody/tr/td[4]/a[text()]", $currentEmployer );
      
      //make sure both shared address are set.
      $this->verifyText("xpath=id('contact-summary')/x:div[2]/x:div[2]/x:div[1]/x:table/x:tbody/x:tr/x:td[2]/x:strong", 'Shared with:' );
      $this->verifyText("xpath=id('contact-summary')/x:div[2]/x:div[2]/x:div[1]/x:table/x:tbody/x:tr/x:td[2]/x:a[text()]", $currentEmployer );

      $this->verifyText("xpath=id('contact-summary')/x:div[2]/x:div[2]/x:div[2]/x:table/x:tbody/x:tr/x:td[2]/x:strong", 'Shared with:' );
      $this->verifyText("xpath=id('contact-summary')/x:div[2]/x:div[2]/x:div[2]/x:table/x:tbody/x:tr/x:td[2]/x:a[text()]", $sharedHousehold );

      // make sure relationships are created
      $this->click("xpath=id('tab_rel')/x:a"); 
      $this->isTextPresent( 'Employee of' );
      $this->isTextPresent( 'Household Member of' );    
  }  
}
?>
