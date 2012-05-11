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
class WebTest_Grant_CustomFieldsetTest extends CiviSeleniumTestCase {

  protected function setUp() {
    parent::setUp();
  }

  function testCustomFieldsetTest() {
    // This is the path where our testing install resides.
    // The rest of URL is defined in CiviSeleniumTestCase base class, in
    // class attributes.
    $this->open($this->sboxPath);

    // Log in as admin first to verify permissions for CiviGrant
    $this->webtestLogin(TRUE);

    // Enable CiviGrant module if necessary
    $this->open($this->sboxPath . 'civicrm/admin/setting/component?reset=1');
    $this->waitForPageToLoad('30000');
    $this->waitForElementPresent('_qf_Component_next-bottom');
    $enabledComponents = $this->getSelectOptions('enableComponents-t');
    if (!in_array('CiviGrant', $enabledComponents)) {
      $this->addSelection('enableComponents-f', 'label=CiviGrant');
      $this->click("//option[@value='CiviGrant']");
      $this->click('add');
      $this->click('_qf_Component_next-bottom');
      $this->waitForPageToLoad('30000');
      $this->waitForElementPresent('css=div.messages');
      $this->assertTrue($this->isTextPresent('Your changes have been saved.'));
    }

    // let's give full CiviGrant permissions to demo user (registered user).
    $permission = array('edit-2-access-civigrant', 'edit-2-edit-grants', 'edit-2-delete-in-civigrant');
    $this->changePermissions($permission);

    // Create unique identifier for names
    $rand = substr(sha1(rand()), 0, 7);

    // Add new Grant Type
    $this->open($this->sboxPath . 'civicrm/admin/options/grant_type?group=grant_type&reset=1');
    $this->waitForPageToLoad("30000");
    $this->click("css=#grant_type > div.action-link > #new > span");
    $this->waitForPageToLoad("30000");
    $grantType = 'GrantType' . $rand;
    $this->type("id=label", $grantType);
    $this->click("id=_qf_Options_next-top");
    $this->waitForPageToLoad("30000");
    $this->assertTrue($this->isTextPresent("The Grant Type '$grantType' has been saved."));

    // Create new Custom Field Set that extends the grant type
    $this->open($this->sboxPath . 'civicrm/admin/custom/group?reset=1');
    $this->waitForPageToLoad("30000");
    $this->click("css=#newCustomDataGroup > span");
    $this->waitForElementPresent('_qf_Group_next-bottom');
    $grantFieldSet = 'Fieldset' . $rand;
    $this->type("id=title", $grantFieldSet);
    $this->select("id=extends[0]", "label=Grants");
    $this->addSelection("id=extends[1]", "label=$grantType");
    $this->click("id=collapse_display");
    $this->click("id=_qf_Group_next-bottom");
    $this->waitForElementPresent('_qf_Field_next-bottom');
    $this->assertTrue($this->isTextPresent("Your custom field set '$grantFieldSet' has been added."));

    // Add field to fieldset
    $grantField = 'GrantField' . $rand;
    $this->type("id=label", $grantField);
    $this->select("id=data_type[0]", "label=Money");
    $this->click("id=_qf_Field_next-bottom");
    $this->waitForPageToLoad("30000");
    $this->assertTrue($this->isTextPresent("Your custom field '$grantField' has been saved."));

    // Create new Grant
    $this->open($this->sboxPath . 'civicrm/grant/add?reset=1&action=add&context=standalone');
    $this->waitForElementPresent('_qf_Grant_upload-bottom');
    $this->select("id=profiles_1", "label=New Individual");
    $this->waitForElementPresent('_qf_Edit_next');
    $firstName = 'First' . $rand;
    $lastName = 'Last' . $rand;
    $this->type("id=first_name", $firstName);
    $this->type("id=last_name", $lastName);
    $this->click("id=_qf_Edit_next");
    $this->select("id=status_id", "label=Granted");
    $this->select("id=grant_type_id", "label=$grantType");
    $this->assertTrue($this->isTextPresent($grantField));
    $this->type("id=amount_total", "100.00");
    $this->type("css=div#$grantFieldSet input.form-text", "99.99");
    $this->click("id=_qf_Grant_upload-bottom");
    $this->waitForPageToLoad("30000");

    // Verify grant is created with presence of view link
    $this->waitForElementPresent("xpath=//div[@id='Grants']//table/tbody/tr[1]/td[8]/span/a[text()='View']");

    // Click through to the Grant view screen
    $this->click("xpath=//div[@id='Grants']//table/tbody/tr[1]/td[8]/span/a[text()='View']");
    $this->waitForElementPresent('_qf_GrantView_cancel-bottom');

    // verify tabular data for grant view
    $this->webtestVerifyTabularData(array(
        'Name' => "$firstName $lastName",
        'Grant Status' => 'Granted',
        'Grant Type' => $grantType,
        $grantField => "$ 99.99",
      )
    );
  }
}

