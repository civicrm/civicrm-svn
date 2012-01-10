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


 
class WebTest_Contact_CustomDataAddTest extends CiviSeleniumTestCase {

  protected function setUp()
  {
      parent::setUp();
  }

  function testCustomDataAdd( )
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
      
      // Go directly to the URL of the screen that you will be testing (New Custom Group).
      $this->open($this->sboxPath . "civicrm/admin/custom/group?action=add&reset=1");

      $this->waitForPageToLoad("30000");
      
      //fill custom group title
      $customGroupTitle = 'custom_'.substr(sha1(rand()), 0, 7);
      $this->click("title");
      $this->type("title", $customGroupTitle);

      //custom group extends 
      $this->click("extends[0]");
      $this->select("extends[0]", "value=Contact");
      $this->click("//option[@value='Contact']");
      $this->click("_qf_Group_next-bottom");
      $this->waitForElementPresent("_qf_Field_cancel-bottom");

      //Is custom group created?
      $this->assertTrue($this->isTextPresent("Your custom field set '{$customGroupTitle}' has been added. You can add custom fields now."));

      //add custom field - alphanumeric checkbox
      $checkboxFieldLabel = 'custom_field'.substr(sha1(rand()), 0, 4);
      $this->click("label");
      $this->type("label", $checkboxFieldLabel);
      $this->click("data_type[1]");
      $this->select("data_type[1]", "value=CheckBox");
      $this->click("//option[@value='CheckBox']");
      $checkboxOptionLabel1 = 'optionLabel_'.substr(sha1(rand()), 0, 5);
      $this->type("option_label_1", $checkboxOptionLabel1);
      $this->type("option_value_1", "1");
      $checkboxOptionLabel2 = 'optionLabel_'.substr(sha1(rand()), 0, 5);
      $this->type("option_label_2", $checkboxOptionLabel2);
      $this->type("option_value_2", "2");
      $this->click("link=another choice");
      $checkboxOptionLabel3 = 'optionLabel_'.substr(sha1(rand()), 0, 5);
      $this->type("option_label_3", $checkboxOptionLabel3);
      $this->type("option_value_3", "3");
      

      //enter options per line
      $this->type("options_per_line", "2");
      
      //enter pre help message
      $this->type("help_pre", "this is field pre help");

      //enter post help message
      $this->type("help_post", "this field post help");

      //Is searchable?
      $this->click("is_searchable");

      //clicking save
      $this->click("_qf_Field_next");
      $this->waitForPageToLoad("30000");

      //Is custom field created?
      $this->assertTrue($this->isTextPresent("Your custom field '$checkboxFieldLabel' has been saved."));

      //create another custom field - Integer Radio
      $this->click("//a[@id='newCustomField']/span");
      $this->waitForPageToLoad("30000");
      $this->click("data_type[0]");
      $this->select("data_type[0]", "value=1");
      $this->click("//option[@value='1']");
      $this->click("data_type[1]");
      $this->select("data_type[1]", "value=Radio");
      $this->click("//option[@value='Radio']");
      
      $radioFieldLabel = 'custom_field'.substr(sha1(rand()), 0, 4);
      $this->type("label", $radioFieldLabel);
      $radioOptionLabel1 = 'optionLabel_'.substr(sha1(rand()), 0, 5);
      $this->type("option_label_1", $radioOptionLabel1);
      $this->type("option_value_1", "1");
      $radioOptionLabel2 = 'optionLabel_'.substr(sha1(rand()), 0, 5);
      $this->type("option_label_2", $radioOptionLabel2);
      $this->type("option_value_2", "2");
      $this->click("link=another choice");
      $radioOptionLabel3 = 'optionLabel_'.substr(sha1(rand()), 0, 5);
      $this->type("option_label_3", $radioOptionLabel3);
      $this->type("option_value_3", "3");     

      //select options per line
      $this->type("options_per_line", "3");
      
      //enter pre help msg
      $this->type("help_pre", "this is field pre help");
      
      //enter post help msg
      $this->type("help_post", "this is field post help");

      //Is searchable?
      $this->click("is_searchable");
      
      //clicking save
      $this->click("_qf_Field_next");
      $this->waitForPageToLoad("30000");
      
      //Is custom field created
      $this->assertTrue($this->isTextPresent("Your custom field '$radioFieldLabel' has been saved."));

      //create Individual contact
      $this->click("//ul[@id='civicrm-menu']/li[4]");
      $this->click("//div[@id='root-menu-div']/div[5]/ul/li[1]/div/a");
      $this->waitForPageToLoad("30000");

      //expand all tabs
      $this->click("expand");
      $this->waitForElementPresent("address_1_street_address");
      
      //fill first name, last name, email id
      $firstName = 'Ma'.substr(sha1(rand()), 0, 4);
      $lastName  = 'An'.substr(sha1(rand()), 0, 7);
      $emailId   = substr(sha1(rand()), 0, 7).'@web.com';
      $this->click("first_name");
      $this->type("first_name", $firstName);
      $this->type("last_name", $lastName);
      $this->type("email_1_email", $emailId);
      
      //fill custom values for the contact
      $this->click("xpath=//table//tr/td/label[text()=\"$checkboxOptionLabel2\"]");
      $this->click("xpath=//table//tr/td/label[text()=\"$radioOptionLabel3\"]");
      $this->click("_qf_Contact_upload_view");
      $this->waitForPageToLoad("30000");
  }
  
  function testCustomDataMoneyAdd( )
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
      $this->waitForPageToLoad("30000");

      // Go directly to the URL of the screen that you will be testing (New Custom Group).
      $this->open($this->sboxPath . "civicrm/admin/custom/group?action=add&reset=1");

      $this->waitForPageToLoad("30000");
      
      //fill custom group title
      $customGroupTitle = 'custom_'.substr(sha1(rand()), 0, 7);
      $this->waitForElementPresent("title");
      $this->click("title");
      $this->type("title", $customGroupTitle);

      //custom group extends 
      $this->click("extends[0]");
      $this->select("extends[0]", "value=Contact");
      $this->click("//option[@value='Contact']");
      $this->click("_qf_Group_next-bottom");
      $this->waitForElementPresent("_qf_Field_cancel-bottom");

      //Is custom group created?
      $this->assertTrue($this->isTextPresent("Your custom field set '{$customGroupTitle}' has been added. You can add custom fields now."));

      //add custom field - money text
      $moneyTextFieldLabel = 'money'.substr(sha1(rand()), 0, 4);
      $this->click("label");
      $this->type("label", $moneyTextFieldLabel);
      $this->waitForElementPresent("data_type[0]");	
      $this->click("data_type[0]");
      $this->select("data_type[0]", "label=Money");

      $this->click("data_type[1]");
      $this->select("data_type[1]", "value=Text");
 
      //enter pre help message
      $this->type("help_pre", "this is field pre help");

      //enter post help message
      $this->type("help_post", "this field post help");

      //Is searchable?
      $this->click("is_searchable");

      //clicking save
      $this->click("_qf_Field_next");
      $this->waitForPageToLoad("30000");

      //Is custom field created?
      $this->assertTrue($this->isTextPresent("Your custom field '$moneyTextFieldLabel' has been saved."));

     
      //create Individual contact
      $this->click("//ul[@id='civicrm-menu']/li[4]");
      $this->click("//div[@id='root-menu-div']/div[5]/ul/li[1]/div/a");
      $this->waitForPageToLoad("30000");

      //expand all tabs
      $this->click("expand");
      $this->waitForElementPresent("address_1_street_address");
      
      //fill first name, last name, email id
      $firstName = 'Ma'.substr(sha1(rand()), 0, 4);
      $lastName  = 'An'.substr(sha1(rand()), 0, 7);
      $emailId   = substr(sha1(rand()), 0, 7).'@web.com';
      $this->click("first_name");
      $this->type("first_name", $firstName);
      $this->type("last_name", $lastName);
      $this->type("email_1_email", $emailId);
      
      //fill custom values for the contact
      $this->click("xpath=//table//tr/td/label[text()=\"$moneyTextFieldLabel\"]");
      $this->type("xpath=//table//tr/td/label[text()=\"$moneyTextFieldLabel\"]/../following-sibling::td/input", "12345678.98");
      $this->click("_qf_Contact_upload_view");
      $this->waitForPageToLoad("30000");

      //verify the money custom field value in the proper format
      $this->verifyText("xpath=//table//tbody/tr/td[text()='$moneyTextFieldLabel']/following-sibling::td", '12,345,678.98' );
  }  
}
?>