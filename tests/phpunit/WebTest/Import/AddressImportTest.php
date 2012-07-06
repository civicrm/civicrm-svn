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


require_once 'WebTest/Import/ImportCiviSeleniumTestCase.php';
class WebTest_Import_AddressImportTest extends ImportCiviSeleniumTestCase {

  protected function setUp() {
    parent::setUp();
  }

  function testCustomAddressDataImport() {
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

    $firstName1 = 'Ma_' . substr(sha1(rand()), 0, 7);
    // Add a custom group and custom field
    $customDataParams = $this->_addCustomData();
  
    // Get sample import data.
    list($headers, $rows) = $this->_individualCustomCSVData($customDataParams, $firstName1);

    $this->importContacts($headers, $rows, 'Individual', 'Skip', array());

    // Type search name in autocomplete.
    $this->click('sort_name_navigation');
    $this->type('css=input#sort_name_navigation', $firstName1);
    $this->typeKeys('css=input#sort_name_navigation', $firstName1);

    // Wait for result list.
    $this->waitForElementPresent("css=div.ac_results-inner li");
   
    // Visit contact summary page.
    $this->click("css=div.ac_results-inner li");
    $this->waitForPageToLoad("30000");
    $this->assertTrue($this->isTextPresent('This is a test field'));
  }


  /*
     *  Helper function to provide data for custom data import.
     */
  function _individualCustomCSVData($customDataParams, $firstName1) {
  
                          
    $headers = array(
      'first_name' => 'First Name',
      'last_name' => 'Last Name',
      'address_1' => 'Additional Address 1',
      'address_2' => 'Additional Address 2',
      'city' => 'City',
      'state' => 'State',
      'country' => 'Country',
      "custom_{$customDataParams['addressCustom']['alphanumeric']['text'][0]}" => "{$customDataParams['addressCustom']['alphanumeric']['text'][1]} :: {$customDataParams['addressCustom']['alphanumeric']['text'][2]}",
     "custom_{$customDataParams['addressCustom']['integer'][0]}" => "{$customDataParams['addressCustom']['integer'][1]} :: {$customDataParams['addressCustom']['integer'][2]}",
      "custom_{$customDataParams['addressCustom']['number'][0]}" => "{$customDataParams['addressCustom']['number'][1]} :: {$customDataParams['addressCustom']['number'][2]}",
       "custom_{$customDataParams['addressCustom']['alphanumeric']['select'][0]}" => "{$customDataParams['addressCustom']['alphanumeric']['select'][1]} :: {$customDataParams['addressCustom']['alphanumeric']['select'][2]}",
       "custom_{$customDataParams['addressCustom']['alphanumeric']['radio'][0]}" => "{$customDataParams['addressCustom']['alphanumeric']['radio'][1]} :: {$customDataParams['addressCustom']['alphanumeric']['radio'][2]}",
       "custom_{$customDataParams['addressCustom']['alphanumeric']['checkbox'][0]}" => "{$customDataParams['addressCustom']['alphanumeric']['checkbox'][1]} :: {$customDataParams['addressCustom']['alphanumeric']['checkbox'][2]}",
       "custom_{$customDataParams['addressCustom']['alphanumeric']['multi-select'][0]}" => "{$customDataParams['addressCustom']['alphanumeric']['multi-select'][1]} :: {$customDataParams['addressCustom']['alphanumeric']['multi-select'][2]}",
       "custom_{$customDataParams['addressCustom']['alphanumeric']['advmulti-select'][0]}" => "{$customDataParams['addressCustom']['alphanumeric']['advmulti-select'][1]} :: {$customDataParams['addressCustom']['alphanumeric']['advmulti-select'][2]}",
       "custom_{$customDataParams['addressCustom']['alphanumeric']['autocomplete-select'][0]}" => "{$customDataParams['addressCustom']['alphanumeric']['autocomplete-select'][1]} :: {$customDataParams['addressCustom']['alphanumeric']['autocomplete-select'][2]}",
      "custom_{$customDataParams['addressCustom']['money'][0]}" => "{$customDataParams['addressCustom']['money'][1]} :: {$customDataParams['addressCustom']['money'][2]}",
       "custom_{$customDataParams['addressCustom']['date'][0]}" => "{$customDataParams['addressCustom']['date'][1]} :: {$customDataParams['addressCustom']['date'][2]}",
    
    );

    $rows = array(
      array('first_name' => $firstName1,
        'last_name' => 'Anderson',
        'address_1' => 'Add 1',
        'address_2' => 'Add 2',
        'city' => 'Watson',
        'state' => 'NY',
        'country' => 'United States',
        "custom_{$customDataParams['addressCustom']['alphanumeric']['text'][0]}" => 'This is a test field',
        "custom_{$customDataParams['addressCustom']['integer'][0]}" => 1,
        "custom_{$customDataParams['addressCustom']['number'][0]}" => 12345,
        "custom_{$customDataParams['addressCustom']['alphanumeric']['select'][0]}" => 'label1',
        "custom_{$customDataParams['addressCustom']['alphanumeric']['radio'][0]}" => 'label1',
        "custom_{$customDataParams['addressCustom']['alphanumeric']['checkbox'][0]}" => 'label1',
        "custom_{$customDataParams['addressCustom']['alphanumeric']['multi-select'][0]}" => 'label1',
        "custom_{$customDataParams['addressCustom']['alphanumeric']['advmulti-select'][0]}" => 'label1',
        "custom_{$customDataParams['addressCustom']['alphanumeric']['autocomplete-select'][0]}" => 'label1',
        "custom_{$customDataParams['addressCustom']['money'][0]}" => 123456,
        "custom_{$customDataParams['addressCustom']['date'][0]}" => '2009-12-31',
       
      ),
    );

    return array($headers, $rows);
  }

  function _addCustomData() {
    // Go directly to the URL of the screen that you will be testing (New Custom Group).
    $this->open($this->sboxPath . "civicrm/admin/custom/group?reset=1");

    //add new custom data
    $this->click("//a[@id='newCustomDataGroup']/span");
    $this->waitForPageToLoad("30000");

    //fill custom group title
    $customGroupTitle = 'Custom ' . substr(sha1(rand()), 0, 7);
    $this->click('title');
    $this->type('title', $customGroupTitle);

    //custom group extends
    $this->click('extends[0]');
    $this->select('extends[0]', "value=Address");
    $this->click("//option[@value='Address']");
    $this->click('_qf_Group_next-bottom');
    $this->waitForElementPresent('_qf_Field_cancel-bottom');

    //Is custom group created?
    $this->assertTrue($this->isTextPresent("Your custom field set '{$customGroupTitle}' has been added. You can add custom fields now."));
    $url = explode('gid=', $this->getLocation());
    $gid = $url[1];

    // create custom field "alphanumeric text"
    $customField = 'Custom field ' . substr(sha1(rand()), 0, 4);
    $this->type('label', $customField);

    // clicking save
    $this->click('_qf_Field_next-bottom');
    $this->waitForElementPresent('newCustomField');
    
    $this->assertTrue($this->isTextPresent("Your custom field '{$customField}' has been saved."));
    $customFieldId = explode('&id=', $this->getAttribute("xpath=//div[@id='field_page']//table/tbody//tr/td/span[text()='$customField']/../../td[8]/span/a@href"));
    $customFieldId = $customFieldId[1];

    // create custom field - Integer
    $this->click("newCustomField");
    $this->waitForPageToLoad("30000");
    $customField1 = 'Customfield_int ' . substr(sha1(rand()), 0, 4);
    $this->type('label', $customField1);
    $this->select("data_type[0]","value=1");

    // clicking save
    $this->click('_qf_Field_next-bottom');
    $this->waitForElementPresent('newCustomField');
    $this->assertTrue($this->isTextPresent("Your custom field '{$customField1}' has been saved."));
    $customFieldId1 = explode('&id=', $this->getAttribute("xpath=//div[@id='field_page']//table/tbody//tr/td/span[text()='$customField1']/../../td[8]/span/a@href"));
    $customFieldId1 = $customFieldId1[1];


    // create custom field - Number
    $this->click("newCustomField");
    $this->waitForPageToLoad("30000");
    $customField2 = 'Customfield_Number ' . substr(sha1(rand()), 0, 4);
    $this->type('label', $customField2);
    $this->select("data_type[0]","value=2");

    // clicking save
    $this->click('_qf_Field_next-bottom');
    $this->waitForElementPresent('newCustomField');
    $this->assertTrue($this->isTextPresent("Your custom field '{$customField2}' has been saved."));
    $customFieldId2 = explode('&id=', $this->getAttribute("xpath=//div[@id='field_page']//table/tbody//tr/td/span[text()='$customField2']/../../td[8]/span/a@href"));
    $customFieldId2 = $customFieldId2[1];

    // create custom field - "alphanumeric select"
    $this->click("newCustomField");
    $this->waitForPageToLoad("30000");
    $customField3 = 'Customfield_alp_select' . substr(sha1(rand()), 0, 4);
    $customFieldId3 = $this->_createMultipleValueCustomField($customField3,'Select');
  
    // create custom field - "alphanumeric radio"
    $this->click("newCustomField");
    $this->waitForPageToLoad("30000");
    $customField4 = 'Customfield_alp_radio' . substr(sha1(rand()), 0, 4);
    $customFieldId4 = $this->_createMultipleValueCustomField($customField4,'Radio');

    // create custom field - "alphanumeric checkbox"
    $this->click("newCustomField");
    $this->waitForPageToLoad("30000");
    $customField5 = 'Customfield_alp_checkbox' . substr(sha1(rand()), 0, 4);
    $customFieldId5 = $this->_createMultipleValueCustomField($customField5,'CheckBox');

    // create custom field - "alphanumeric multiselect"
    $this->click("newCustomField");
    $this->waitForPageToLoad("30000");
    $customField6 = 'Customfield_alp_multiselect' . substr(sha1(rand()), 0, 4);
    $customFieldId6 = $this->_createMultipleValueCustomField($customField6,'Multi-Select');
 
     // create custom field - "alphanumeric advmultiselect"
    $this->click("newCustomField");
    $this->waitForPageToLoad("30000");
    $customField7 = 'Customfield_alp_advmultiselect' . substr(sha1(rand()), 0, 4);
    $customFieldId7 = $this->_createMultipleValueCustomField($customField7,'AdvMulti-Select');

    // create custom field - "alphanumeric autocompleteselect"
    $this->click("newCustomField");
    $this->waitForPageToLoad("30000");
    $customField8 = 'Customfield_alp_autocompleteselect' . substr(sha1(rand()), 0, 4);
    $customFieldId8 = $this->_createMultipleValueCustomField($customField8,'Autocomplete-Select');
    
    // create custom field - Money
    $this->click("newCustomField");
    $this->waitForPageToLoad("30000");
    $customField9 = 'Customfield_Money' . substr(sha1(rand()), 0, 4);
    $this->type('label', $customField9);
    $this->select("data_type[0]","value=3");

    // clicking save
    $this->click('_qf_Field_next-bottom');
    $this->waitForElementPresent('newCustomField');
    $this->assertTrue($this->isTextPresent("Your custom field '{$customField9}' has been saved."));
    $customFieldId9 = explode('&id=', $this->getAttribute("xpath=//div[@id='field_page']//table/tbody//tr/td/span[text()='$customField9']/../../td[8]/span/a@href"));
    $customFieldId9 = $customFieldId9[1];

    // create custom field - Date
    $this->click("newCustomField");
    $this->waitForPageToLoad("30000");
    $customField10 = 'Customfield_Date' . substr(sha1(rand()), 0, 4);
    $this->type('label', $customField10);
    $this->select("data_type[0]","value=5");
    $this->select("date_format","value=yy-mm-dd");

    // clicking save
    $this->click('_qf_Field_next-bottom');
    $this->waitForElementPresent('newCustomField');
    $this->assertTrue($this->isTextPresent("Your custom field '{$customField10}' has been saved."));
    $customFieldId10 = explode('&id=', $this->getAttribute("xpath=//div[@id='field_page']//table/tbody//tr/td/span[text()='$customField10']/../../td[8]/span/a@href"));
    $customFieldId10 = $customFieldId10[1];
    
    return array('addressCustom' =>
                 array( 'alphanumeric' => 
                        array('text' => array("custom_{$customFieldId}", $customField, $customGroupTitle),
                              'select' => array("custom_{$customFieldId3}", $customField3, $customGroupTitle),
                              'radio' => array("custom_{$customFieldId4}", $customField4, $customGroupTitle),
                              'checkbox' => array("custom_{$customFieldId5}", $customField5, $customGroupTitle),
                              'multi-select' => array("custom_{$customFieldId6}", $customField6, $customGroupTitle),
                              'advmulti-select' => array("custom_{$customFieldId7}", $customField7, $customGroupTitle),
                              'autocomplete-select' => array("custom_{$customFieldId8}", $customField8, $customGroupTitle),
                              ),
                        'integer'      => array("custom_{$customFieldId1}", $customField1, $customGroupTitle),
                        'number'       => array("custom_{$customFieldId2}", $customField2, $customGroupTitle),
                        'money'        => array("custom_{$customFieldId9}", $customField9, $customGroupTitle),
                        'date'         => array("custom_{$customFieldId10}", $customField10, $customGroupTitle),
                        )
                 );
    
  }
  
  function _createMultipleValueCustomField( $customFieldName, $type ){
    $this->type('label', $customFieldName);
    $this->select("data_type[0]","value=0");
    $this->select("data_type[1]","value=".$type);
    $this->type("option_label_1","label1");
    $this->type("option_value_1","label1");
    $this->type("option_label_2","label2");
    $this->type("option_value_2","label2");
    
    // clicking save
    $this->click('_qf_Field_next-bottom');
    $this->waitForElementPresent('newCustomField');
    $this->assertTrue($this->isTextPresent("Your custom field '{$customFieldName}' has been saved."));
    $customFieldId = explode('&id=', $this->getAttribute("xpath=//div[@id='field_page']//table/tbody//tr/td/span[text()='$customFieldName']/../../td[8]/span/a@href"));
    $customFieldId = $customFieldId[1];
    return $customFieldId;
  }


}

