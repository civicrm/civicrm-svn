<?php
/*
 +--------------------------------------------------------------------+
 | CiviCRM version 4.2                                                |
 +--------------------------------------------------------------------+
 | Copyright CiviCRM LLC (c) 2004-2012                                |
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
class WebTest_Contact_InlineFieldsEditTest extends CiviSeleniumTestCase {
  
  protected function setUp() {
    parent::setUp();
  }
  
  function testAddAndEditField() {
    // This is the path where our testing install resides.
    // The rest of URL is defined in CiviSeleniumTestCase base class, in
    // class attributes.
    $this->open($this->sboxPath);
    
    // Logging in. Remember to wait for page to load. In most cases,
    // you can rely on 30000 as the value that allows your test to pass, however,
    // sometimes your test might fail because of this. In such cases, it's better to pick one element
    // somewhere at the end of page and use waitForElementPresent on it - this assures you, that whole
    // page contents loaded and you can continue your test execution.
    $this->webtestLogin();
    
    //adding a contact
    $firstName = 'Anthony' . substr(sha1(rand()), 0, 7);
    $lastName  = 'Anderson' . substr(sha1(rand()), 0, 7);
    $this->webtestAddContact($firstName, $lastName);
  
    //email block check
    $this->_addEditPhoneEmail();
    
    //phone block check
    $this->_addEditPhoneEmail('phone');
  }
  
  function _addEditPhoneEmail($field = "email") {
    $isEmail = $isPhone = FALSE;
    if ($field == "email") {
      $isEmail = TRUE;
    } elseif ($field == "phone") {
      $isPhone = TRUE;
    }
    $linkText = "add {$field}";
    $this->_checkClickLink($linkText, $field);
    
    //fill the field data
    $loc = array( 1 => 'Home', 2 => 'Work', 3 => 'Main');
    $phoneType = array( 1 => 'Phone', 2 => 'Mobile', 3 => 'Fax');
    //add / delete link check
    $moreFields = 3;
    for ($i = 1; $i <= $moreFields; $i++) {
      $this->click("xpath=//div[@id='{$field}-block']/div/form/table[@class='crm-inline-edit-form']/tbody/tr[2]/td/span[@id='add-more-{$field}']/a");
    }
    $this->click("xpath=//div[@id='{$field}-block']/div/form/table[@class='crm-inline-edit-form']/tbody/tr[5]/td[5]/a");

    $assertValues = array( );
    for ($i = 1; $i <= $moreFields; $i++) {
      $randNumber = rand();
      $inputVal = ($field == "email") ? $randNumber . 'an@example.org' : $randNumber;
          
      if ($isEmail) {
        $this->assertTrue($this->isElementPresent("email[{$i}][on_hold]"));
        $this->assertTrue($this->isElementPresent("Email_{$i}_IsBulkmail"));
        $this->assertTrue($this->isElementPresent("Email_{$i}_IsPrimary"));
      } elseif ($isPhone) {
        $this->assertTrue($this->isElementPresent("phone_2_phone_ext"));
        $this->assertTrue($this->isElementPresent("phone_1_phone_type_id"));
      }
      
      $assertValues[$loc[$i]] = $inputVal;
      $this->select("{$field}_{$i}_location_type_id", "label={$loc[$i]}");
      $this->type("{$field}_{$i}_{$field}", $inputVal);
    }
    $ucFieldName = ucfirst($field);
    $this->click("_qf_{$ucFieldName}_upload");
    
    //checking done for location values
    $i = 1;
    foreach ($assertValues as $location => $value) {
      $this->verifyText("xpath=//div[@id='crm-{$field}-content']/div[@class='crm-clear']/div[@class='crm-label'][$i]", $location ." ". $ucFieldName);
      if ($isEmail) {
        $this->verifyText("xpath=//div[@id='crm-{$field}-content']/div[@class='crm-clear']/div[@class='crm-content crm-contact_email'][$i]/span/a", $value);
      } else {
        $primaryClass = "";
        if($i == 1) {
          $key = 1;
          $primaryClass = "primary";
        } else {
          $key = $i - 1;  
        }
        $this->verifyText("xpath=//div[@id='crm-{$field}-content']/div[@class='crm-clear']/div[@class='crm-content {$primaryClass}'][$key]/span", $value);
      }
      $i++;
    }
    
    $linkText = "add or edit {$field}";      
    $this->_checkClickLink($linkText, $field);
    
    //check for values present in edit mode
    for ($i = 1; $i <= $moreFields; $i++) {
      $this->verifySelectedValue("{$field}_{$i}_location_type_id", "{$i}");
      $this->assertTrue(($this->getValue("{$field}_{$i}_{$field}") == $assertValues[$loc[$i]]), "Failed assertion for {$field} field value present in edit mode");
    }
    
    if ($isEmail) {
      $this->click('email[3][on_hold]');
      $this->click('Email_3_IsBulkmail');
      $this->click('_qf_Email_upload');
      sleep(2);
      $this->verifyText("xpath=//div[@id='crm-{$field}-content']/div[@class='crm-clear']/div[@class='crm-label'][3]", "Main Email");
      $this->verifyText("xpath=//div[@id='crm-{$field}-content']/div[@class='crm-clear']/div[@class='crm-content crm-contact_email'][3]/span[@class='email-hold']", preg_quote($assertValues[$loc[3]] .' (On Hold) (Bulk)'));
    } else {
      $this->type("{$field}_2_{$field}_ext", 543);
      $this->select("{$field}_1_{$field}_type_id", "label={$phoneType[2]}");
      $this->click('_qf_Phone_upload');
      sleep(2);
      $this->verifyText("xpath=//div[@id='crm-{$field}-content']/div[@class='crm-clear']/div[@class='crm-label'][1]", "Home " . $phoneType[2]);
      $this->verifyText("xpath=//div[@id='crm-{$field}-content']/div[@class='crm-clear']/div[@class='crm-content '][1]/span", preg_quote($assertValues['Work'] ."  ext. ". 543));
    }
  }
  
  function _checkClickLink($linkText, $field) {
    //check element presence
    $text = $this->getText("xpath=//div[@id='{$field}-block']/div[@id='crm-{$field}-content']//a[@id='edit-{$field}']");
    $this->assertTrue((($text == $linkText) && $this->isElementPresent("xpath=//div[@id='{$field}-block']/div[@id='crm-{$field}-content']//a[@id='edit-{$field}']")), "'{$linkText}' link text: {$text} missing on contact summary page");
    $this->click("xpath=//div[@id='{$field}-block']/div[@id='crm-{$field}-content']//a[@id='edit-{$field}']");
  }
}