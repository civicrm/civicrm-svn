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


 
class WebTest_Contact_AddTest extends CiviSeleniumTestCase {

  protected $captureScreenshotOnFailure = TRUE;
  protected $screenshotPath = '/var/www/api.dev.civicrm.org/public/sc';
  protected $screenshotUrl = 'http://api.dev.civicrm.org/sc/';
    
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
      
      // Go directly to the URL of the screen that you will be testing (New Individual).
      $this->open($this->sboxPath . "civicrm/contact/add&reset=1&ct=Individual");

      //contact details section
      //select prefix
      $this->click("prefix_id");
      $this->select("prefix_id", "value=3");
      
      //fill in first name
      $this->type("first_name", "John");
      
      //fill in middle name
      $this->type("middle_name", "Bruce");
      
      //fill in last name
      $this->type("last_name", "Smith");
      
      //select suffix
      $this->select("suffix_id", "value=3");
      
      //fill in nick name
      $this->type("nick_name", "jsmith");
      
      //fill in email
      $this->type("email_1_email", "john@gmail.com");
      
      //fill in phone
      $this->type("phone_1_phone", "2222-4444");

      //fill in IM
      $this->type("im_1_name", "testYahoo");
      
      //fill in openID
      $this->type("openid_1_openid", "http://www.johnopenid.com");
      
      //fill in website
      $this->type("website_1_url", "http://www.john.com");
      
      //fill in source
      $this->type("contact_source", "johnSource");

      //fill in external identifier
      $indExternalId = substr( sha1( rand() ), 0, 4 );
      $this->type( "external_identifier", $indExternalId );
      
      //check for matching contact
      $this->click("_qf_Contact_refresh_dedupe");
      $this->waitForPageToLoad("30000");
      
      
      //address section    
      $this->click("addressBlock");
      $this->waitForElementPresent("address_1_street_address");
      //fill in address 1
      $this->type("address_1_street_address", "902C El Camino Way SW");
      $this->type("address_1_city", "Dumfries");
      $this->type("address_1_postal_code", "1234");
      $this->assertTrue($this->isTextPresent("- select - United States"));
      $this->select("address_1_state_province_id", "value=1019");
      $this->type("address_1_geo_code_1", "1234");
      $this->type("address_1_geo_code_2", "5678");
      
      //fill in address 2
      $this->click("link=add address");
      $this->waitForElementPresent("address_2_street_address");
      $this->type("address_2_street_address", "2782Y Dowlen Path W");
      $this->type("address_2_city", "Birmingham");
      $this->type("address_2_postal_code", "3456");
      $this->assertTrue($this->isTextPresent("- select - United States"));
      $this->select("address_2_state_province_id", "value=1002");
      $this->type("address_2_geo_code_1", "2678");
      $this->type("address_2_geo_code_2", "1456");
      
      
      //Communication Preferences section
      $this->click("commPrefs");
      
      //select greeting/addressee options
      $this->waitForElementPresent("email_greeting_id");
      $this->select("email_greeting_id", "value=2");
      $this->select("postal_greeting_id", "value=3");
      
      //Select preferred method for Privacy
      $this->click("privacy[do_not_trade]");
      $this->click("privacy[do_not_sms]");
      
      //Select preferred method(s) of communication
      $this->click("preferred_communication_method[1]");
      $this->click("preferred_communication_method[2]");
      
      //select preferred language
      $this->waitForElementPresent("preferred_language");
      $this->select("preferred_language", "value=en");
      
      
      //Notes section
      $this->click("notesBlock");
      $this->waitForElementPresent("subject");
      $this->type("subject", "test note");
      $this->type("note", "this is a test note contact webtest");
      $this->assertTrue($this->isTextPresent("Subject\n Notes"));
      
      //Demographics section
      $this->click("demographics");
      
      $this->click("CIVICRM_QFID_2_Male");
      $this->webtestFillDate('birth_date');
      
      //Tags and Groups section
      $this->click("tagGroup");
      
      $this->click("group[2]");
      $this->click("tag[4]");
      
      // Clicking save.
      $this->click("_qf_Contact_upload_view");
      $this->waitForPageToLoad("30000");
      
      $this->assertTrue($this->isTextPresent("Your Individual contact record has been saved."));
  }  

  function testHouseholdAdd( )
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
      
      // Go directly to the URL of the screen that you will be testing (New Household).
      $this->open($this->sboxPath . "civicrm/contact/add&reset=1&ct=Household");
      
      //contact details section
      //fill in Household name
      $this->click("household_name");
      $this->type("household_name", "Fraddie Grant's home");
      
      //fill in nick name
      $this->type("nick_name", "Grant's home");

      //fill in email
      $this->type("email_1_email", "fraddiegrantshome@web.com");
      $this->click("Email_1_IsBulkmail");
      
      //fill in phone
      $this->type("phone_1_phone", "444-4444");
      $this->select("phone_1_phone_type_id", "value=4");
      
      
      //fill in IM
      $this->assertTrue($this->isTextPresent("Yahoo MSN AIM GTalk Jabber Skype"));
      $this->type("im_1_name", "testSkype");
      $this->select("im_1_location_type_id", "value=3");
      $this->select("im_1_provider_id", "value=6");
      
      //fill in openID
      $this->type("openid_1_openid", "http://www.grantshomeopenid.com");
      
      //fill in website url
      $this->type("website_1_url", "http://www.fraddiegrantshome.com");
      
      //fill in contact source
      $this->type("contact_source", "Grant's home source");
      
      //fill in external identifier
      $houExternalId = substr( sha1( rand() ), 0, 4 );
      $this->type( "external_identifier", $houExternalId );

      //check for duplicate contact
      $this->click("_qf_Contact_refresh_dedupe");
      $this->waitForPageToLoad("30000");
      
      //address section
      $this->click("addressBlock");
      $this->waitForElementPresent("address_1_street_address");
      $this->type("address_1_street_address", "938U Bay Rd E");
      $this->type("address_1_city", "Birmingham");
      $this->type("address_1_postal_code", "35278");
      $this->assertTrue($this->isTextPresent("Country\n - select - United States"));
      $this->select("address_1_state_province_id", "value=1030");
      $this->type("address_1_geo_code_1", "5647");
      $this->type("address_1_geo_code_2", "2843");
      
      
      //Communication Preferences section
      $this->click("commPrefs");
      
      //select greeting/addressee options
      $this->waitForElementPresent("addressee_id");
      $this->select("addressee_id", "value=4");
      $this->type("addressee_custom", "Grant's home");
      
      //Select preferred method(s) of communication
      $this->click("preferred_communication_method[1]");
      $this->click("preferred_communication_method[2]");
      $this->click("preferred_communication_method[5]");
      
      //Select preferred method for Privacy
      $this->click("privacy[do_not_sms]");
      
      //select preferred language
      $this->waitForElementPresent("preferred_language");
      $this->select("preferred_language", "value=fr");
      
      
      //Notes section
      $this->click("notesBlock");
      $this->waitForElementPresent("subject");
      $this->type("subject", "Grant's note");
      $this->type("note", "This is a household contact webtest note.");
      
      //Tags and Groups section
      $this->click("tagGroup");
      $this->click("group[3]");
      $this->click("tag[1]");
      
      // Clicking save.
      $this->click("_qf_Contact_upload_view");
      $this->waitForPageToLoad("30000");
      
      $this->assertTrue($this->isTextPresent("Your Household contact record has been saved."));    
  }
  
  function testOrganizationAdd( )
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
      
      // Go directly to the URL of the screen that you will be testing (New Organization).
      $this->open($this->sboxPath . "civicrm/contact/add&reset=1&ct=Organization");
      
      
      //contact details section
      //fill in Organization name
      $this->click("organization_name");
      $this->type("organization_name", "syntel tech");
      
      //fill in legal name
      $this->type("legal_name", "syntel tech Ltd");
      
      //fill in nick name
      $this->type("nick_name", "syntel");
      
      //fill in email
      $this->type("email_1_email", "info@syntel.com");
      
      //fill in phone
      $this->type("phone_1_phone", "222-7777");
      $this->select("phone_1_phone_type_id", "value=2");
      
      //fill in IM
      $this->type("im_1_name", "testGtalk");
      $this->select("im_1_location_type_id", "value=4");
      $this->select("im_1_provider_id", "value=4");
      
      //fill in openID
      $this->select("openid_1_location_type_id", "value=5");
      $this->type("openid_1_openid", "http://www.syntelOpenid.com");
      
      //fill in website url
      $this->type("website_1_url", "http://syntelglobal.com");
      
      //fill in contact source
      $this->type("contact_source", "syntel's source");
      
      //fill in external identifier
      $orgExternalId = substr( sha1( rand() ), 0, 4 );
      $this->type( "external_identifier", $orgExternalId );
      
      //check for duplicate contact
      $this->click("_qf_Contact_refresh_dedupe");
      $this->waitForPageToLoad("30000");
      
      //address section
      $this->click("addressBlock");
      $this->waitForElementPresent("address_1_street_address");
      $this->type("address_1_street_address", "928A Lincoln Way W");
      $this->type("address_1_city", "Madison");
      $this->type("address_1_postal_code", "68748");
      $this->assertTrue($this->isTextPresent("Country\n - select - United States"));
      $this->select("address_1_state_province_id", "value=1030");
      $this->type("address_1_geo_code_1", "5644");
      $this->type("address_1_geo_code_2", "3678");
      
      
      //Communication Preferences section
      $this->click("commPrefs");
      
      //Select preferred method(s) of communication
      $this->click("preferred_communication_method[2]");
      $this->click("preferred_communication_method[5]");
      
      //Select preferred method for Privacy
      $this->click("privacy[do_not_sms]");
      $this->click("privacy[do_not_mail]");
      //select preferred language
      $this->waitForElementPresent("preferred_language");
      $this->select("preferred_language", "value=de");
      
      //Notes section
      $this->click("notesBlock");
      $this->waitForElementPresent("subject");
      $this->type("subject", "syntel global note");
      $this->type("note", "This is a note for syntel global's contact webtest.");
      
      //Tags and Groups section
      $this->click("tagGroup");
      $this->click("group[3]");
      $this->click("tag[1]");
      
      // Clicking save.
      $this->click("_qf_Contact_upload_view");
      $this->waitForPageToLoad("30000");
      
      $this->assertTrue($this->isTextPresent("Your Organization contact record has been saved."));    
  }
}
?>
