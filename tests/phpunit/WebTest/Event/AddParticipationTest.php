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
 
class WebTest_Event_AddParticipationTest extends CiviSeleniumTestCase {

  protected function setUp()
  {
      parent::setUp();
  }
  
  function testEventParticipationAdd()
  {
      // This is the path where our testing install resides. 
      // The rest of URL is defined in CiviSeleniumTestCase base class, in
      // class attributes.
      $this->open( $this->sboxPath );

      // Log in using webtestLogin() method
      $this->webtestLogin();

      // Adding contact with randomized first name (so we can then select that contact when creating event registration)
      // We're using Quick Add block on the main page for this.
      $firstName = substr(sha1(rand()), 0, 7);
      $this->webtestAddContact( $firstName, 'Anderson', true );
      $contactName = "Anderson, $firstName";
      $displayName = "$firstName Anderson";

      // Go directly to the URL of the screen that you will be testing (Register Participant for Event-standalone).
      $this->open($this->sboxPath . 'civicrm/participant/add?reset=1&action=add&context=standalone');

      // As mentioned before, waitForPageToLoad is not always reliable. Below, we're waiting for the submit
      // button at the end of this page to show up, to make sure it's fully loaded.
      $this->waitForElementPresent('_qf_Participant_upload-bottom');

      // Let's start filling the form with values.
      // Type contact last name in contact auto-complete, wait for dropdown and click first result
      $this->webtestFillAutocomplete( $firstName );

      // Select event. Based on label for now.
      $this->select('event_id', "label=regexp:Rain-forest Cup Youth Soccer Tournament.");
      
      // Select role
      $this->click('role_id[2]');

      // Choose Registration Date.
      // Using helper webtestFillDate function.
      $this->webtestFillDate('register_date', 'now');
      $today = date('F jS, Y', strtotime('now'));
      // May 5th, 2010

      // Select participant status
      $this->select('status_id', 'value=1');

      // Setting registration source
      $this->type('source', 'Event StandaloneAddTest Webtest');

      // Since we're here, let's check of screen help is being displayed properly
      $this->assertTrue($this->isTextPresent('Source for this registration (if applicable).'));

      // Select an event fee
      $feeHelp = 'Event Fee Level (if applicable).';
      $this->waitForTextPresent($feeHelp);

      $this->click("xpath=id('feeBlock')/table/tbody/tr[1]/td/table/tbody/tr/td[2]/label[1]");
      
      // Select 'Record Payment'
      $this->click('record_contribution');
      
      // Enter amount to be paid (note: this should default to selected fee level amount, s/b fixed during 3.2 cycle)
      $this->type('total_amount', '800');
      
      // Select payment method = Check and enter chk number
      $this->select('payment_instrument_id', 'value=4');
      $this->waitForElementPresent('check_number');
      $this->type('check_number', '1044');
      
      // go for the chicken combo (obviously)
//      $this->click('CIVICRM_QFID_chicken_Chicken');
      
      // Clicking save.
      $this->click('_qf_Participant_upload-bottom');
      $this->waitForPageToLoad('30000');
      
      // Is status message correct?
      $this->assertTrue($this->isTextPresent("Event registration for $displayName has been added"), "Status message didn't show up after saving!");
      
      $this->waitForElementPresent( "xpath=//div[@id='Events']//table//tbody/tr[1]/td[8]/span/a[text()='View']" );
      //click through to the participant view screen
      $this->click( "xpath=//div[@id='Events']//table//tbody/tr[1]/td[8]/span/a[text()='View']" );
      $this->waitForElementPresent( '_qf_ParticipantView_cancel-bottom' );
      
      
      $this->webtestVerifyTabularData( 
                                      array( 'Event'            => 'Rain-forest Cup Youth Soccer Tournament',
                                             'Participant Role' => 'Attendee',
                                             'Status'           => 'Registered',
                                             'Event Source'     => 'Event StandaloneAddTest Webtest', 
                                             'Event Level'      => 'Tiny-tots (ages 5-8) - $ 800.00',
                                             )
                                       );

      // check contribution record as well
      //click through to the contribution view screen
      $this->click( "xpath=id('ParticipantView')/div[2]/table[@class='selector']/tbody/tr[1]/td[8]/span/a[text()='View']" );
      $this->waitForElementPresent( '_qf_ContributionView_cancel-bottom' );
      
      $this->webtestVerifyTabularData( 
                                      array( 'From'                => $displayName,
                                             'Contribution Type'   => 'Event Fee',
                                             'Total Amount'        => '$ 800.00',
                                             'Contribution Status' => 'Completed',
                                             'Paid By'             => 'Check',
                                             'Check Number'        => '1044',
                                             )
                                       );
  }

  function testEventParticipationAddWithMultipleRoles()
  {
      
      // This is the path where our testing install resides. 
      // The rest of URL is defined in CiviSeleniumTestCase base class, in
      // class attributes.
      $this->open( $this->sboxPath );

      // Log in using webtestLogin() method
      $this->webtestLogin();
      
      // Adding contact with randomized first name (so we can then select that contact when creating event registration)
      // We're using Quick Add block on the main page for this.
      $firstName = substr(sha1(rand()), 0, 7);
      $this->webtestAddContact( $firstName, 'Anderson', true );
      $contactName = "Anderson, $firstName";
      $displayName = "$firstName Anderson";
      
      // add custom data for participant role
      $this->open($this->sboxPath . 'civicrm/admin/custom/group?reset=1 ');
      $this->waitForPageToLoad('30000');

      //add new custom data
      $this->click("//a[@id='newCustomDataGroup']/span");
      $this->waitForPageToLoad('30000');
      
      //fill custom group title
      $customGroupTitle = 'custom_'.substr(sha1(rand()), 0, 7);
      $this->click('title');
      $this->type('title', $customGroupTitle);

      //custom group extends 
      $this->click('extends[0]');
      $this->select('extends[0]', 'value=ParticipantRole');

      $this->click('extends[1]');
      $this->select('extends[1]', 'value=2');

      $this->click("//option[@value='Contact']");
      $this->click('_qf_Group_next');
      $this->waitForPageToLoad('30000');

      //Is custom group created?
      $this->assertTrue($this->isTextPresent("Your custom field set '$customGroupTitle' has been added. You can add custom fields now."));

      //add custom field - alphanumeric checkbox
      $checkboxFieldLabel = 'custom_field'.substr(sha1(rand()), 0, 4);
      $this->click('label');
      $this->type('label', $checkboxFieldLabel);
      $this->click('data_type[1]');
      $this->select('data_type[1]', 'value=CheckBox');
      $this->click("//option[@value='CheckBox']");
      $checkboxOptionLabel1 = 'optionLabel_'.substr(sha1(rand()), 0, 5);
      $this->type('option_label_1', $checkboxOptionLabel1);
      $this->type('option_value_1', '1');
      $checkboxOptionLabel2 = 'optionLabel_'.substr(sha1(rand()), 0, 5);
      $this->type('option_label_2', $checkboxOptionLabel2);
      $this->type('option_value_2', '2');
      $this->click('link=another choice');
      $checkboxOptionLabel3 = 'optionLabel_'.substr(sha1(rand()), 0, 5);
      $this->type('option_label_3', $checkboxOptionLabel3);
      $this->type('option_value_3', '3');
      

      //enter options per line
      $this->type('options_per_line', '2');
      
      //enter pre help message
      $this->type('help_pre', 'this is field pre help');

      //enter post help message
      $this->type('help_post', 'this field post help');

      //Is searchable?
      $this->click('is_searchable');

      //clicking save
      $this->click('_qf_Field_next');
      $this->waitForPageToLoad('30000');

      //Is custom field created?
      $this->assertTrue($this->isTextPresent("Your custom field '$checkboxFieldLabel' has been saved."));

      //create another custom field - Integer Radio
      $this->click("//a[@id='newCustomField']/span");
      $this->waitForPageToLoad('30000');
      $this->click('data_type[0]');
      $this->select('data_type[0]', 'value=1');
      $this->click("//option[@value='1']");
      $this->click('data_type[1]');
      $this->select('data_type[1]', 'value=Radio');
      $this->click("//option[@value='Radio']");
      
      $radioFieldLabel = 'custom_field'.substr(sha1(rand()), 0, 4);
      $this->type('label', $radioFieldLabel);
      $radioOptionLabel1 = 'optionLabel_'.substr(sha1(rand()), 0, 5);
      $this->type('option_label_1', $radioOptionLabel1);
      $this->type('option_value_1', '1');
      $radioOptionLabel2 = 'optionLabel_'.substr(sha1(rand()), 0, 5);
      $this->type('option_label_2', $radioOptionLabel2);
      $this->type('option_value_2', '2');
      $this->click('link=another choice');
      $radioOptionLabel3 = 'optionLabel_'.substr(sha1(rand()), 0, 5);
      $this->type('option_label_3', $radioOptionLabel3);
      $this->type('option_value_3', '3');     

      //select options per line
      $this->type('options_per_line', '3');
      
      //enter pre help msg
      $this->type('help_pre', 'this is field pre help');
      
      //enter post help msg
      $this->type('help_post', 'this is field post help');

      //Is searchable?
      $this->click('is_searchable');
      
      //clicking save
      $this->click('_qf_Field_next');
      $this->waitForPageToLoad('30000');

      // Go directly to the URL of the screen that you will be testing (Register Participant for Event-standalone).
      $this->open($this->sboxPath . 'civicrm/participant/add?reset=1&action=add&context=standalone');

      // As mentioned before, waitForPageToLoad is not always reliable. Below, we're waiting for the submit
      // button at the end of this page to show up, to make sure it's fully loaded.
      $this->waitForElementPresent('_qf_Participant_upload-bottom');

      // Let's start filling the form with values.
      // Type contact last name in contact auto-complete, wait for dropdown and click first result
      $this->webtestFillAutocomplete( $firstName );

      // Select event. Based on label for now.
      $this->select('event_id', "label=regexp:Rain-forest Cup Youth Soccer Tournament.");
      
      // Select roles
      $this->click('role_id[2]');
      $this->click('role_id[3]');

      $this->waitForElementPresent( "xpath=//div[@id='$customGroupTitle']//div" );
      $this->click( "xpath=//div[@id='$customGroupTitle']//div[1]");
      $this->click( "xpath=//div[@id='$customGroupTitle']//div[2]//table//tbody//tr[2]//td[2]//table//tbody//tr[1]//td[1]//label" );
      $this->click( "xpath=//div[@id='$customGroupTitle']//div[2]//table//tbody//tr[4]//td[2]//table//tbody//tr[1]//td[1]//label" );

      // Choose Registration Date.
      // Using helper webtestFillDate function.
      $this->webtestFillDate('register_date', 'now');
      $today = date('F jS, Y', strtotime('now'));
      // May 5th, 2010

      // Select participant status
      $this->select('status_id', 'value=1');

      // Setting registration source
      $this->type('source', 'Event StandaloneAddTest Webtest');

      // Since we're here, let's check of screen help is being displayed properly
      $this->assertTrue($this->isTextPresent('Source for this registration (if applicable).'));

      // Select an event fee
      $feeHelp = 'Event Fee Level (if applicable).';
      $this->waitForTextPresent($feeHelp);
      
      $this->click("xpath=id('feeBlock')/table/tbody/tr[1]/td/table/tbody/tr/td[2]/label[1]");

      // Select 'Record Payment'
      $this->click('record_contribution');
      
      // Enter amount to be paid (note: this should default to selected fee level amount, s/b fixed during 3.2 cycle)
      $this->type('total_amount', '800');
      
      // Select payment method = Check and enter chk number
      $this->select('payment_instrument_id', 'value=4');
      $this->waitForElementPresent('check_number');
      $this->type('check_number', '1044');
 
      // Clicking save.
      $this->click('_qf_Participant_upload-bottom');
      $this->waitForPageToLoad('30000');
      
      // Is status message correct?
      $this->assertTrue($this->isTextPresent("Event registration for $displayName has been added"), 
                        "Status message didn't show up after saving!");
      
      $this->waitForElementPresent( "xpath=//div[@id='Events']//table//tbody/tr[1]/td[8]/span/a[text()='View']" );
      //click through to the participant view screen
      $this->click( "xpath=//div[@id='Events']//table//tbody/tr[1]/td[8]/span/a[text()='View']" );
      $this->waitForElementPresent( '_qf_ParticipantView_cancel-bottom' );
      
      $this->webtestVerifyTabularData( 
                                      array( 'Event'            => 'Rain-forest Cup Youth Soccer Tournament',
                                             'Participant Role' => 'Attendee, Volunteer, Host',
                                             'Status'           => 'Registered',
                                             'Event Source'     => 'Event StandaloneAddTest Webtest', 
                                             'Event Level'      => 'Tiny-tots (ages 5-8) - $ 800.00',
                                             )
                                       );

      $this->assertTrue($this->isTextPresent("$customGroupTitle"));
      $this->assertTrue($this->isTextPresent("$checkboxOptionLabel1"));
      $this->assertTrue($this->isTextPresent("$radioOptionLabel1"));

      // check contribution record as well
      //click through to the contribution view screen
      $this->click( "xpath=id('ParticipantView')/div[2]/table[@class='selector']/tbody/tr[1]/td[8]/span/a[text()='View']" );
      $this->waitForElementPresent( '_qf_ContributionView_cancel-bottom' );
      
      $this->webtestVerifyTabularData( 
                                      array( 'From'                => $displayName,
                                             'Contribution Type'   => 'Event Fee',
                                             'Total Amount'        => '$ 800.00',
                                             'Contribution Status' => 'Completed',
                                             'Paid By'             => 'Check',
                                             'Check Number'        => '1044',
                                             )
                                       );
  }
}
