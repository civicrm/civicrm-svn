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
 
class WebTest_Contact_MergeContactsTest extends CiviSeleniumTestCase {

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

        // edit the default Fuzzy rule
        $this->open( $this->sboxPath . "civicrm/contact/deduperules?action=update&id=1" );
        $this->waitForElementPresent( 'threshold' );
        $this->click( "threshold" );
        $this->type( "threshold", "10" );
        $this->click( "_qf_DedupeRules_next-bottom" );
        $this->waitForPageToLoad( "30000" );
        
        // Go directly to the URL of New Individual.
        $this->open( $this->sboxPath . "civicrm/contact/add?reset=1&ct=Individual" );
        $this->waitForPageToLoad( "30000" );

        // add contact1
        //select prefix
        $prefix = 'Mr.';
        $this->click( "prefix_id" );
        $this->select( "prefix_id", "label=$prefix" );
        
        //fill in first name
        $firstName = substr(sha1(rand()), 0, 7);
        $this->type( 'first_name', $firstName );
        
        //fill in last name
        $lastName = substr(sha1(rand()), 0, 7);
        $this->type( 'last_name', $lastName );
        
        //fill in email id
        $this->type( 'email_1_email', "{$firstName}.{$lastName}@example.com" );

        //fill in billing email id
        $this->click( 'addEmail' );
        $this->waitForElementPresent( 'email_2_email' );
        $this->type( 'email_2_email', "$firstName.$lastName@billing.com" );
        $this->select( 'email_2_location_type_id', 'value=5' );
        
        // Clicking save.
        $this->click("_qf_Contact_upload_view");
        $this->waitForPageToLoad("30000");
        $this->assertTrue( $this->isTextPresent( "Your Individual contact record has been saved." ) );

        // Add Contact to a group
        $group = 'Newsletter Subscribers';
        $this->click( 'css=li#tab_group a' );
        $this->waitForElementPresent( '_qf_GroupContact_next' );
        $this->select( 'group_id', "label=$group" );
        $this->click( '_qf_GroupContact_next' );
        $this->waitForPageToLoad("30000");
        $this->assertTrue( $this->isTextPresent( "Contact has been added to the selected group " ) );

        // Add Tags to the contact
        $tag = 'Government Entity';
        $this->click( "css=li#tab_tag a" );
        $this->waitForElementPresent( 'check_5' );
        $this->click( "xpath=//div[@id='tagtree']/ul//li/input/../label[text()='$tag']" );

        // Add an activity
        $subject = "This is subject of test activity being added through activity tab of contact summary screen.";
        $this->addActivity( $firstName, $lastName, $subject );
                
        // contact2: duplicate of contact1.
        $this->open( $this->sboxPath . "civicrm/contact/add?reset=1&ct=Individual" );
        
        //fill in first name
        $this->type( "first_name", $firstName );
        
        //fill in last name
        $this->type( "last_name", $lastName );
        
        //fill in email
        $this->type( "email_1_email", "{$firstName}.{$lastName}@example.com" );
        
        // Clicking save.
        $this->click( "_qf_Contact_refresh_dedupe" );
        $this->waitForPageToLoad("30000");
        
        $this->assertTrue( $this->isTextPresent( "One matching contact was found. You can View or Edit the existing contact." ) );
        $this->click( "_qf_Contact_upload_duplicate" );
        $this->waitForPageToLoad("30000");
        $this->assertTrue( $this->isTextPresent( "Your Individual contact record has been saved." ) );
        
        // Add second pair of dupes so we can test Merge and Goto Next Pair
        $fname2 = 'Janet';
        $lname2 = 'Rogers' . substr(sha1(rand()), 0, 7);
        $email2 = "{$fname2}.{$lname2}@example.org";
        $this->webtestAddContact( $fname2, $lname2, $email2 );

        // Can not use helper for 2nd contact since it is a dupe
        $this->open( $this->sboxPath . "civicrm/contact/add?reset=1&ct=Individual" );
        $this->waitForPageToLoad("30000");
        $this->type( "first_name", $fname2 );
        $this->type( "last_name", $lname2 );
        $this->type( "email_1_email", $email2  );
        $this->click( "_qf_Contact_refresh_dedupe" );
        $this->waitForPageToLoad("30000");        
        $this->assertTrue( $this->isTextPresent( "One matching contact was found. You can View or Edit the existing contact." ) );
        $this->click( "_qf_Contact_upload_duplicate" );
        $this->waitForPageToLoad("30000");
        $this->assertTrue( $this->isTextPresent( "Your Individual contact record has been saved." ) );

        // Find and Merge Contacts
        $this->open( $this->sboxPath . 'civicrm/contact/deduperules?reset=1' );
        $this->waitForPageToLoad("30000");

        // Use Fuzzy Rule
        $this->click( "xpath=//div[@id='browseValues']//table/tbody//tr/td[2][text()='Individual']/../td[3][text()='Fuzzy']/../td[5]/span/a[text()='Use Rule']" );
        $this->waitForElementPresent( "_qf_DedupeFind_submit-bottom" );
        $this->click( "_qf_DedupeFind_next-bottom" );
        $this->waitForPageToLoad( "30000" );
        
        // Select the contacts to be merged
        $this->select( "xpath=//div[@id='option51_length']/select", "value=25" );
        $this->waitForElementPresent( "xpath=//table[@class='pagerDisplay']/tbody//tr/td[1]/a[text()='$prefix $firstName $lastName']/../../td[2]/a[text()='$firstName $lastName']" );
        $this->click( "xpath=//table[@class='pagerDisplay']/tbody//tr/td[1]/a[text()='$prefix $firstName $lastName']/../../td[2]/a[text()='$firstName $lastName']/../../td[4]/a[text()='merge']" );
        $this->waitForElementPresent( '_qf_Merge_cancel-bottom' );

        $this->click( "css=div.crm-contact-merge-form-block div.action-link a" );
        $this->waitForPageToLoad( "30000" );
        $this->waitForElementPresent( '_qf_Merge_cancel-bottom' );
        
        // Move the activities, groups, etc to the main contact and merge using Merge and Goto Next Pair
        $this->check( 'move_individual_prefix' );
        $this->check( 'move_location_email_2' );
        $this->check( 'move_rel_table_activities' );
        $this->check( 'move_rel_table_groups' );
        $this->check( 'move_rel_table_tags' );
        $this->click( '_qf_Merge_next-bottom' );
        $this->waitForPageToLoad( "30000" );
        $this->waitForElementPresent( '_qf_Merge_cancel-bottom');
        $this->assertTrue( $this->isTextPresent( 'The contacts have been merged.' ), "Contacts have been merged text was not found after merge." );

        // Check that we are viewing the next Merge Pair (our 2nd contact, since the merge list is ordered by contact_id)
        $this->assertTrue( $this->isTextPresent( "{$fname2} {$lname2}" ), "Redirect for Goto Next Pair after merge did not work." );
        
        // Ensure that the duplicate contact has been deleted
        $this->open( $this->sboxPath . 'civicrm/contact/search/advanced?reset=1' );
        $this->waitForElementPresent( '_qf_Advanced_refresh' );
        $this->type( 'sort_name', $firstName );
        $this->check( 'deleted_contacts' );
        $this->click( '_qf_Advanced_refresh' );
        $this->waitForPageToLoad( "30000" );
        $this->assertTrue( $this->isTextPresent( '1 Contact' ), "Deletion of duplicate contact during merge was not successful. Dupe contact not found when searching trash." );

        // Search for the main contact
        $this->open( $this->sboxPath . 'civicrm/contact/search/advanced?reset=1' );
        $this->waitForElementPresent( '_qf_Advanced_refresh' );
        $this->type( 'sort_name', $firstName );
        $this->click( '_qf_Advanced_refresh' );
        $this->waitForElementPresent( "xpath=//form[@id='Advanced']/div[3]/div/div[2]/table/tbody/tr" );
        
        $this->click( "//form[@id='Advanced']/div[3]/div/div[2]/table/tbody/tr/td[11]/span[1]/a[text()='View']" );
        $this->waitForPageToLoad( "30000" );

        // Verify prefix merged
        // $this->verifyText( "xpath=//div[@class='left-corner']/h2", preg_quote( "$prefix $firstName $lastName" ) );

        // Verify billing email merged
        $this->verifyText( "xpath=//div[@class='contact_details']/div[1][@class='contact_panel']/div[1][@class='contactCardLeft']/table/tbody/tr[3]/td[2]/span/a", preg_quote( "$firstName.$lastName@billing.com" ) );

        // Verify activity merged
        $this->click( "css=li#tab_activity a" );
        $this->waitForElementPresent( "xpath=//table[@id='contact-activity-selector-activity']/tbody/tr" );
        $this->verifyText( "xpath=//table[@id='contact-activity-selector-activity']/tbody/tr/td[5]/a", 
                           preg_quote( "$lastName, $firstName" ) );
        
        // Verify group merged
        $this->click( "css=li#tab_group a" );
        $this->waitForElementPresent( "xpath=//div[@id='groupContact']//table/tbody/tr" );
        $this->verifyText( "xpath=//div[@id='groupContact']//table/tbody/tr/td/a", 
                           preg_quote( "$group" ) );

        // Verify tag merged
        $this->click( "css=li#tab_tag a" );
        $this->waitForElementPresent( 'check_5' );
        $this->assertChecked( "check_3" );
    }  

    function addActivity( $firstName, $lastName, $subject )
    {
        $withContact = substr(sha1(rand()), 0, 7);
        $this->webtestAddContact( $withContact, "Anderson", $withContact . "@anderson.name" );
        
        $this->click( "css=li#tab_activity a" );
        
        // waiting for the activity dropdown to show up
        $this->waitForElementPresent("other_activity");

        // Select the activity type from the activity dropdown
        $this->select("other_activity", "label=Meeting");
        
        // waitForPageToLoad is not always reliable. Below, we're waiting for the submit
        // button at the end of this page to show up, to make sure it's fully loaded.
        $this->waitForElementPresent("_qf_Activity_upload");
        
        // Let's start filling the form with values.
        
        // ...and verifying if the page contains properly formatted display name for chosen contact.
        $this->assertTrue($this->isTextPresent("Anderson, " . $withContact), "Contact not found in line " . __LINE__ );
        
        // Now we're filling the "Assigned To" field.
        // Typing contact's name into the field (using typeKeys(), not type()!)...
        $this->fireEvent( 'assignee_contact_id', 'focus' );
        $this->typeKeys("css=tr.crm-activity-form-block-assignee_contact_id input#token-input-assignee_contact_id", $firstName);
        
        // ...waiting for drop down with results to show up...
        $this->waitForElementPresent("css=div.token-input-dropdown-facebook");
        $this->waitForElementPresent("css=li.token-input-dropdown-item2-facebook");

        //..need to use mouseDownAt on first result (which is a li element), click does not work
        $this->mouseDownAt("css=li.token-input-dropdown-item2-facebook");

        // ...again, waiting for the box with contact name to show up...
        $this->waitForElementPresent("css=tr.crm-activity-form-block-assignee_contact_id td ul li span.token-input-delete-token-facebook");
        
        // ...and verifying if the page contains properly formatted display name for chosen contact.
        $this->assertTrue($this->isTextPresent("$lastName, " . $firstName), "Contact not found in line " . __LINE__ );
        
        // Since we're here, let's check if screen help is being displayed properly
        $this->assertTrue($this->isTextPresent("Assigned activities will appear in their Activities listing at CiviCRM Home"));
        
        // Putting the contents into subject field - assigning the text to variable, it'll come in handy later
        // For simple input fields we can use field id as selector
        $this->type("subject", $subject);
        $this->type("location", "Some location needs to be put in this field.");
        
        // Choosing the Date.
        // Please note that we don't want to put in fixed date, since
        // we want this test to work in the future and not fail because
        // of date being set in the past. Therefore, using helper webtestFillDateTime function.
        $this->webtestFillDateTime('activity_date_time','+1 month 11:10PM');
        
        // Setting duration.
        $this->type("duration", "30");
        
        // Putting in details.
        $this->type("details", "Really brief details information.");
        
        // Making sure that status is set to Scheduled (using value, not label).
        $this->select("status_id", "value=1");
        
        // Setting priority.
        $this->select("priority_id", "value=1");
        
        // Clicking save.
        $this->click("_qf_Activity_upload");
        $this->waitForPageToLoad("30000");
        
        // Is status message correct?
        $this->assertTrue($this->isTextPresent("Activity '$subject' has been saved."), "Status message didn't show up after saving!");
    }
}
