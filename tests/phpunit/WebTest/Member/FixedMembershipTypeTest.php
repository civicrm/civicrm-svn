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


 
class WebTest_Member_FixedMembershipTypeTest extends CiviSeleniumTestCase {

    protected $captureScreenshotOnFailure = TRUE;
    protected $screenshotPath = '/var/www/api.dev.civicrm.org/public/sc';
    protected $screenshotUrl = 'http://api.dev.civicrm.org/sc/';
    
    protected function setUp()
    {
        parent::setUp();
    }
    
    function testMembershipTypeScenario1( ) 
    {
        // Scenario 1
        // Rollover Date < Start Date 
        // Join Date > Rollover Date and Join Date < Start Date
        

        // This is the path where our testing install resides. 
        // The rest of URL is defined in CiviSeleniumTestCase base class, in
        // class attributes.
        $this->open( $this->sboxPath );
        
        // Log in using webtestLogin() method
        $this->webtestLogin();

        $this->open( $this->sboxPath . "civicrm/contact/add?reset=1&ct=Organization" );
        $this->waitForElementPresent( '_qf_Contact_cancel' );
        
        $title = substr(sha1(rand()), 0, 7);
        $this->type( 'organization_name', "Organization $title" );
        $this->type( 'email_1_email', "$title@org.com" );
        $this->click( '_qf_Contact_upload_view' );
        $this->waitForPageToLoad("30000");
        
        $this->assertTrue( $this->isTextPresent( "Your Organization contact record has been saved." ) );
        
        // Go directly to the URL
        $this->open( $this->sboxPath . "civicrm/admin/member/membershipType?reset=1&action=browse" );
        $this->waitForPageToLoad("30000");

        $this->click( "link=Add Membership Type" );
        $this->waitForElementPresent( '_qf_MembershipType_cancel-bottom' );
        
        $this->type( 'name', "Membership Type $title" );
        $this->type( 'member_org', $title );
        $this->click( '_qf_MembershipType_refresh' );
        $this->waitForElementPresent( "xpath=//div[@id='membership_type_form']/fieldset/table[2]/tbody/tr[2]/td[2]" );
        
        $this->type( 'minimum_fee', '100' );
        
        $this->select( 'contribution_type_id', 'value=2' );
        
        $this->type( 'duration_interval', 1 );
        $this->select( 'duration_unit', "label=year" );
        
        $this->select( 'period_type', "label=fixed" );
        $this->waitForElementPresent( 'fixed_period_rollover_day[d]' );
        
        $this->select( 'fixed_period_start_day[M]', 'value=4' );
        $this->select( 'fixed_period_rollover_day[M]', 'value=1' );
        
        $this->click( 'relationship_type_id', 'value=4_b_a' );
        
        $this->click( '_qf_MembershipType_upload-bottom' );
        $this->waitForElementPresent( 'link=Add Membership Type' );
        $this->assertTrue( $this->isTextPresent( "The membership type 'Membership Type $title' has been saved." ) ); 
                
        // Go directly to the URL of the screen that you will be testing (New Individual).
        $this->open( $this->sboxPath . "civicrm/contact/add?reset=1&ct=Individual" );
        
        $firstName = "John_" . substr(sha1(rand()), 0, 7);
        
        //fill in first name
        $this->type( 'first_name', $firstName );
        
        //fill in last name
        $lastName = "Smith_" . substr(sha1(rand()), 0, 7);;
        $this->type( 'last_name', $lastName);
        
        //fill in email
        $email = substr(sha1(rand()), 0, 7) . "john@gmail.com";
        $this->type( 'email_1_email', $email );
        
        // Clicking save.
        $this->click( '_qf_Contact_upload_view' );
        $this->waitForPageToLoad("30000");
        
        $this->assertTrue( $this->isTextPresent( 'Your Individual contact record has been saved.' ) );
        
        // click through to the membership view screen
        $this->click( 'css=li#tab_member a' );

        $this->waitForElementPresent( 'link=Add Membership' );
        $this->click( 'link=Add Membership' );
        
        $this->waitForElementPresent( '_qf_Membership_cancel-bottom' );
        
        // fill in Membership Organization and Type
        $this->select( 'membership_type_id[0]', "label=Organization $title" );
        $this->select( 'membership_type_id[1]', "label=Membership Type $title");
        
        $sourceText = "Membership ContactAddTest with Fixed Membership Type";
        // fill in Source
        $this->type( 'source', $sourceText );

        //build the membership dates.
        require_once 'CRM/Core/Config.php';
        require_once 'CRM/Utils/Array.php';
        require_once 'CRM/Utils/Date.php';
        $currentYear  = date( 'Y' );
        $currentMonth = date( 'm' );
        $previousYear = $currentYear - 1;
        $nextYear     = $currentYear + 1;
        $joinDate     = date('Y-m-d', mktime( 0, 0, 0, 3, 25, $currentYear ) ); 
        $startDate    = date('Y-m-d', mktime( 0, 0, 0, 4, 1,  $previousYear  ) );
        $endDate      = date('Y-m-d', mktime( 0, 0, 0, 3, 31, $nextYear ) );
        $configVars   = new CRM_Core_Config_Variables( );        
        foreach ( array( 'joinDate', 'startDate', 'endDate' ) as $date ) {
            $$date = CRM_Utils_Date::customFormat( $$date, $configVars->dateformatFull ); 
        }

        $query = "
SELECT end_event_adjust_interval 
  FROM civicrm_membership_status 
 WHERE start_event = 'join_date'
   AND name = 'New'";
        $endInterval  = CRM_Core_DAO::singleValueQuery( $query ) + 3;
        
        $status = 'New';
        if ( $currentMonth >= $endInterval ) {
            $status = 'Current';
        }
        
        // fill in Join Date
        $this->webtestFillDate( 'join_date', $joinDate );
        
        // Clicking save.
        $this->click( '_qf_Membership_upload' );
        $this->waitForPageToLoad("30000");
        
        // page was loaded
        $this->waitForTextPresent( $sourceText );
        
        // Is status message correct?
        $this->assertTrue( $this->isTextPresent( "Membership Type $title membership for $firstName $lastName has been added." ),
                           "Status message didn't show up after saving!");
        
        // click through to the membership view screen
        $this->click( "xpath=//div[@id='memberships']//table//tbody/tr[1]/td[7]/span/a[text()='View']" );
        $this->waitForElementPresent("_qf_MembershipView_cancel-bottom");
        
        $this->webtestVerifyTabularData( 
                                        array( 'Membership Type' => "Membership Type $title",
                                               'Status'          => $status,
                                               'Source'          => $sourceText,
                                               'Member Since'    => $joinDate,
                                               'Start date'      => $startDate,
                                               'End date'        => $endDate
                                               )
                                         );
    }

    function testMembershipTypeScenario2( ) 
    {
        // Scenario 2
        // Rollover Date < Start Date 
        // Join Date < Start Date and Join Date < Rollover Date


        // This is the path where our testing install resides. 
        // The rest of URL is defined in CiviSeleniumTestCase base class, in
        // class attributes.
        $this->open( $this->sboxPath );
        
        // Log in using webtestLogin() method
        $this->webtestLogin();

        $this->open( $this->sboxPath . "civicrm/contact/add?reset=1&ct=Organization" );
        $this->waitForElementPresent( '_qf_Contact_cancel' );
        
        $title = substr(sha1(rand()), 0, 7);
        $this->type( 'organization_name', "Organization $title" );
        $this->type( 'email_1_email', "$title@org.com" );
        $this->click( '_qf_Contact_upload_view' );
        $this->waitForPageToLoad("30000");
        
        $this->assertTrue( $this->isTextPresent( "Your Organization contact record has been saved." ) );
        
        // Go directly to the URL
        $this->open( $this->sboxPath . "civicrm/admin/member/membershipType?reset=1&action=browse" );
        $this->waitForPageToLoad("30000");

        $this->click( "link=Add Membership Type" );
        $this->waitForElementPresent( '_qf_MembershipType_cancel-bottom' );
        
        $this->type( 'name', "Membership Type $title" );
        $this->type( 'member_org', $title );
        $this->click( '_qf_MembershipType_refresh' );
        $this->waitForElementPresent( "xpath=//div[@id='membership_type_form']/fieldset/table[2]/tbody/tr[2]/td[2]" );
        
        $this->type( 'minimum_fee', '100' );
        
        $this->select( 'contribution_type_id', 'value=2' );
        
        $this->type( 'duration_interval', 2 );
        $this->select( 'duration_unit', "label=year" );
        
        $this->select( 'period_type', "label=fixed" );
        $this->waitForElementPresent( 'fixed_period_rollover_day[d]' );
        
        $this->select( 'fixed_period_start_day[M]', 'value=9' );
        $this->select( 'fixed_period_rollover_day[M]', 'value=6' );
        $this->select( 'fixed_period_rollover_day[d]', 'value=30' );
        
        $this->click( 'relationship_type_id', 'value=4_b_a' );
        
        $this->click( '_qf_MembershipType_upload-bottom' );
        $this->waitForElementPresent( 'link=Add Membership Type' );
        $this->assertTrue( $this->isTextPresent( "The membership type 'Membership Type $title' has been saved." ) ); 
                
        // Go directly to the URL of the screen that you will be testing (New Individual).
        $this->open( $this->sboxPath . "civicrm/contact/add?reset=1&ct=Individual" );
        
        $firstName = "John_" . substr(sha1(rand()), 0, 7);
        
        //fill in first name
        $this->type( 'first_name', $firstName );
        
        //fill in last name
        $lastName = "Smith_" . substr(sha1(rand()), 0, 7);;
        $this->type( 'last_name', $lastName);
        
        //fill in email
        $email = substr(sha1(rand()), 0, 7) . "john@gmail.com";
        $this->type( 'email_1_email', $email );
        
        // Clicking save.
        $this->click( '_qf_Contact_upload_view' );
        $this->waitForPageToLoad("30000");
        
        $this->assertTrue( $this->isTextPresent( 'Your Individual contact record has been saved.' ) );
        
        // click through to the membership add screen
        $this->click( 'css=li#tab_member a' );

        $this->waitForElementPresent( 'link=Add Membership' );
        $this->click( 'link=Add Membership' );
        
        $this->waitForElementPresent( '_qf_Membership_cancel-bottom' );
        
        // fill in Membership Organization and Type
        $this->select( 'membership_type_id[0]', "label=Organization {$title}" );
        // Wait for membership type select to reload
        $this->waitForTextPresent( "Membership Type {$title}" );
        $this->select( 'membership_type_id[1]', "label=Membership Type {$title}");
        
        $sourceText = "Membership ContactAddTest with Fixed Membership Type";
        // fill in Source
        $this->type( 'source', $sourceText );
        
        //build the membership dates.
        require_once 'CRM/Core/Config.php';
        require_once 'CRM/Utils/Array.php';
        require_once 'CRM/Utils/Date.php';
        $currentYear  = date( 'Y' );
        $currentMonth = date( 'm' );
        $previousYear = $currentYear - 1;
        $joinDate     = date('Y-m-d', mktime( 0, 0, 0, 1, 15, $currentYear ) ); 
        $startDate    = date('Y-m-d', mktime( 0, 0, 0, 9, 1,  $previousYear  ) );
        $endDate      = date('Y-m-d', mktime( 0, 0, 0, 8, 31, $currentYear + 1 ) );
        $configVars   = new CRM_Core_Config_Variables( );        
        foreach ( array( 'joinDate', 'startDate', 'endDate' ) as $date ) {
            $$date = CRM_Utils_Date::customFormat( $$date, $configVars->dateformatFull ); 
        }
        
        $query = "
SELECT end_event_adjust_interval 
  FROM civicrm_membership_status 
 WHERE start_event = 'join_date'
   AND name = 'New'";
        $endInterval  = CRM_Core_DAO::singleValueQuery( $query ) + 1;
        
        $status = 'New';
        if ( $currentMonth >= $endInterval ) {
            $status = 'Current';
        }
        
        // fill in Join Date
        $this->webtestFillDate( 'join_date', $joinDate );
        
        // Clicking save.
        $this->click( '_qf_Membership_upload' );
        $this->waitForPageToLoad("30000");
        
        // page was loaded
        $this->waitForTextPresent( $sourceText );
        
        // Is status message correct?
        $this->assertTrue( $this->isTextPresent( "Membership Type $title membership for $firstName $lastName has been added." ),
                           "Status message didn't show up after saving!");
        
        // click through to the membership view screen
        $this->click( "xpath=//div[@id='memberships']//table//tbody/tr[1]/td[7]/span/a[text()='View']" );
        $this->waitForElementPresent("_qf_MembershipView_cancel-bottom");
        
        $this->webtestVerifyTabularData( 
                                        array( 'Membership Type' => "Membership Type {$title}",
                                               'Status'          => $status,
                                               'Source'          => $sourceText,
                                               'Member Since'    => $joinDate,
                                               'Start date'      => $startDate,
                                               'End date'        => $endDate
                                               )
                                         );
    }

    function testMembershipTypeScenario3( )
    {
        // Scenario 3
        // Start Date < Rollover Date 
        // Join Date < Start Date and Join Date < Rollover Date


        // This is the path where our testing install resides. 
        // The rest of URL is defined in CiviSeleniumTestCase base class, in
        // class attributes.
        $this->open( $this->sboxPath );
        
        // Log in using webtestLogin() method
        $this->webtestLogin();

        $this->open( $this->sboxPath . "civicrm/contact/add?reset=1&ct=Organization" );
        $this->waitForElementPresent( '_qf_Contact_cancel' );
        
        $title = substr(sha1(rand()), 0, 7);
        $this->type( 'organization_name', "Organization $title" );
        $this->type( 'email_1_email', "$title@org.com" );
        $this->click( '_qf_Contact_upload_view' );
        $this->waitForPageToLoad("30000");
        
        $this->assertTrue( $this->isTextPresent( "Your Organization contact record has been saved." ) );
        
        // Go directly to the URL
        $this->open( $this->sboxPath . "civicrm/admin/member/membershipType?reset=1&action=browse" );
        $this->waitForPageToLoad("30000");

        $this->click( "link=Add Membership Type" );
        $this->waitForElementPresent( '_qf_MembershipType_cancel-bottom' );
        
        $this->type( 'name', "Membership Type $title" );
        $this->type( 'member_org', $title );
        $this->click( '_qf_MembershipType_refresh' );
        $this->waitForElementPresent( "xpath=//div[@id='membership_type_form']/fieldset/table[2]/tbody/tr[2]/td[2]" );
        
        $this->type( 'minimum_fee', '100' );
        
        $this->select( 'contribution_type_id', 'value=2' );
        
        $this->type( 'duration_interval', 3 );
        $this->select( 'duration_unit', "label=year" );
        
        $this->select( 'period_type', "label=fixed" );
        $this->waitForElementPresent( 'fixed_period_rollover_day[d]' );
        
        $this->select( 'fixed_period_start_day[M]', 'value=6' );
        $this->select( 'fixed_period_rollover_day[M]', 'value=8' );
        $this->select( 'fixed_period_rollover_day[d]', 'value=31' );
        
        $this->click( 'relationship_type_id', 'value=4_b_a' );
        
        $this->click( '_qf_MembershipType_upload-bottom' );
        $this->waitForElementPresent( 'link=Add Membership Type' );
        $this->assertTrue( $this->isTextPresent( "The membership type 'Membership Type $title' has been saved." ) ); 
                
        // Go directly to the URL of the screen that you will be testing (New Individual).
        $this->open( $this->sboxPath . "civicrm/contact/add?reset=1&ct=Individual" );
        
        $firstName = "John_" . substr(sha1(rand()), 0, 7);
        
        //fill in first name
        $this->type( 'first_name', $firstName );
        
        //fill in last name
        $lastName = "Smith_" . substr(sha1(rand()), 0, 7);;
        $this->type( 'last_name', $lastName);
        
        //fill in email
        $email = substr(sha1(rand()), 0, 7) . "john@gmail.com";
        $this->type( 'email_1_email', $email );
        
        // Clicking save.
        $this->click( '_qf_Contact_upload_view' );
        $this->waitForPageToLoad("30000");
        
        $this->assertTrue( $this->isTextPresent( 'Your Individual contact record has been saved.' ) );
        
        // click through to the membership view screen
        $this->click( 'css=li#tab_member a' );

        $this->waitForElementPresent( 'link=Add Membership' );
        $this->click( 'link=Add Membership' );
        
        $this->waitForElementPresent( '_qf_Membership_cancel-bottom' );
        
        // fill in Membership Organization and Type
        $this->select( 'membership_type_id[0]', "label=Organization {$title}" );
        // Wait for membership type select to reload
        $this->waitForTextPresent( "Membership Type {$title}" );
        $this->select( 'membership_type_id[1]', "label=Membership Type {$title}");

        
        $sourceText = "Membership ContactAddTest with Fixed Membership Type";
        // fill in Source
        $this->type( 'source', $sourceText );

        //build the membership dates.
        require_once 'CRM/Core/Config.php';
        require_once 'CRM/Utils/Array.php';
        require_once 'CRM/Utils/Date.php';
        $currentYear  = date( 'Y' );
        $currentMonth = date( 'm' );
        $previousYear = $currentYear - 1;
        $nextYear     = $currentYear + 2;
        $joinDate     = date('Y-m-d', mktime( 0, 0, 0, 1, 5,  $currentYear ) ); 
        $startDate    = date('Y-m-d', mktime( 0, 0, 0, 6, 1,  $previousYear  ) );
        $endDate      = date('Y-m-d', mktime( 0, 0, 0, 5, 31, $nextYear ) );
        $configVars   = new CRM_Core_Config_Variables( );        
        foreach ( array( 'joinDate', 'startDate', 'endDate' ) as $date ) {
            $$date = CRM_Utils_Date::customFormat( $$date, $configVars->dateformatFull ); 
        }

        $query = "
SELECT end_event_adjust_interval 
  FROM civicrm_membership_status 
 WHERE start_event = 'join_date'
   AND name = 'New'";
        $endInterval  = CRM_Core_DAO::singleValueQuery( $query ) + 1;
        
        $status = 'New';
        if ( $currentMonth >= $endInterval ) {
            $status = 'Current';
        }
        
        // fill in Join Date
        $this->webtestFillDate( 'join_date', $joinDate );
        
        // Clicking save.
        $this->click( '_qf_Membership_upload' );
        $this->waitForPageToLoad("30000");
        
        // page was loaded
        $this->waitForTextPresent( $sourceText );
        
        // Is status message correct?
        $this->assertTrue( $this->isTextPresent( "Membership Type $title membership for $firstName $lastName has been added." ),
                           "Status message didn't show up after saving!");
        
        // click through to the membership view screen
        $this->click( "xpath=//div[@id='memberships']//table//tbody/tr[1]/td[7]/span/a[text()='View']" );
        $this->waitForElementPresent("_qf_MembershipView_cancel-bottom");
        
        $this->webtestVerifyTabularData( 
                                        array( 'Membership Type' => "Membership Type {$title}",
                                               'Status'          => $status,
                                               'Source'          => $sourceText,
                                               'Member Since'    => $joinDate,
                                               'Start date'      => $startDate,
                                               'End date'        => $endDate
                                               )
                                         );
    }

    function testMembershipTypeScenario4( )
    {
        // Scenario 4
        // Rollover Date < Start Date 
        // Join Date > Start Date and Join Date > Rollover Date

        
        // This is the path where our testing install resides. 
        // The rest of URL is defined in CiviSeleniumTestCase base class, in
        // class attributes.
        $this->open( $this->sboxPath );
        
        // Log in using webtestLogin() method
        $this->webtestLogin();

        $this->open( $this->sboxPath . "civicrm/contact/add?reset=1&ct=Organization" );
        $this->waitForElementPresent( '_qf_Contact_cancel' );
        
        $title = substr(sha1(rand()), 0, 7);
        $this->type( 'organization_name', "Organization $title" );
        $this->type( 'email_1_email', "$title@org.com" );
        $this->click( '_qf_Contact_upload_view' );
        $this->waitForPageToLoad("30000");
        
        $this->assertTrue( $this->isTextPresent( "Your Organization contact record has been saved." ) );
        
        // Go directly to the URL
        $this->open( $this->sboxPath . "civicrm/admin/member/membershipType?reset=1&action=browse" );
        $this->waitForPageToLoad("30000");

        $this->click( "link=Add Membership Type" );
        $this->waitForElementPresent( '_qf_MembershipType_cancel-bottom' );
        
        $this->type( 'name', "Membership Type $title" );
        $this->type( 'member_org', $title );
        $this->click( '_qf_MembershipType_refresh' );
        $this->waitForElementPresent( "xpath=//div[@id='membership_type_form']/fieldset/table[2]/tbody/tr[2]/td[2]" );
        
        $this->type( 'minimum_fee', '100' );
        
        $this->select( 'contribution_type_id', 'value=2' );
        
        $this->type( 'duration_interval', 1 );
        $this->select( 'duration_unit', "label=year" );
        
        $this->select( 'period_type', "label=fixed" );
        $this->waitForElementPresent( 'fixed_period_rollover_day[d]' );
        
        $this->select( 'fixed_period_start_day[M]', 'value=4' );
        $this->select( 'fixed_period_rollover_day[M]', 'value=1' );
        $this->select( 'fixed_period_rollover_day[d]', 'value=1' );
        
        $this->click( 'relationship_type_id', 'value=4_b_a' );
        
        $this->click( '_qf_MembershipType_upload-bottom' );
        $this->waitForElementPresent( 'link=Add Membership Type' );
        $this->assertTrue( $this->isTextPresent( "The membership type 'Membership Type $title' has been saved." ) ); 
                
        // Go directly to the URL of the screen that you will be testing (New Individual).
        $this->open( $this->sboxPath . "civicrm/contact/add?reset=1&ct=Individual" );
        
        $firstName = "John_" . substr(sha1(rand()), 0, 7);
        
        //fill in first name
        $this->type( 'first_name', $firstName );
        
        //fill in last name
        $lastName = "Smith_" . substr(sha1(rand()), 0, 7);;
        $this->type( 'last_name', $lastName);
        
        //fill in email
        $email = substr(sha1(rand()), 0, 7) . "john@gmail.com";
        $this->type( 'email_1_email', $email );
        
        // Clicking save.
        $this->click( '_qf_Contact_upload_view' );
        $this->waitForPageToLoad("30000");
        
        $this->assertTrue( $this->isTextPresent( 'Your Individual contact record has been saved.' ) );
        
        // click through to the membership view screen
        $this->click( 'css=li#tab_member a' );

        $this->waitForElementPresent( 'link=Add Membership' );
        $this->click( 'link=Add Membership' );
        
        $this->waitForElementPresent( '_qf_Membership_cancel-bottom' );
        
        // fill in Membership Organization and Type
        $this->select( 'membership_type_id[0]', "label=Organization $title" );
        $this->select( 'membership_type_id[1]', "label=Membership Type $title");
        
        $sourceText = "Membership ContactAddTest with Fixed Membership Type";
        // fill in Source
        $this->type( 'source', $sourceText );
        
        //build the membership dates.
        require_once 'CRM/Core/Config.php';
        require_once 'CRM/Utils/Array.php';
        require_once 'CRM/Utils/Date.php';
        $currentYear  = date( 'Y' );
        $currentMonth = date( 'm' );
        $nextYear     = $currentYear + 1;
        $joinDate     = date('Y-m-d', mktime( 0, 0, 0, 4, 5, $currentYear ) ); 
        $startDate    = date('Y-m-d', mktime( 0, 0, 0, 4, 1, $currentYear  ) );
        $endDate      = date('Y-m-d', mktime( 0, 0, 0, 3, 31, $nextYear ) );
        $configVars   = new CRM_Core_Config_Variables( );        
        foreach ( array( 'joinDate', 'startDate', 'endDate' ) as $date ) {
            $$date = CRM_Utils_Date::customFormat( $$date, $configVars->dateformatFull ); 
        }

        $query = "
SELECT end_event_adjust_interval 
  FROM civicrm_membership_status 
 WHERE start_event = 'join_date'
   AND name = 'New'";
        $endInterval  = CRM_Core_DAO::singleValueQuery( $query ) + 4;
        
        $status = 'New';
        if ( $currentMonth >= $endInterval ) {
            $status = 'Current';
        }
        
        // fill in Join Date
        $this->webtestFillDate( 'join_date', $joinDate );
        
        // Clicking save.
        $this->click( '_qf_Membership_upload' );
        $this->waitForPageToLoad("30000");
        
        // page was loaded
        $this->waitForTextPresent( $sourceText );
        
        // Is status message correct?
        $this->assertTrue( $this->isTextPresent( "Membership Type $title membership for $firstName $lastName has been added." ),
                           "Status message didn't show up after saving!");
        
        // click through to the membership view screen
        $this->click( "xpath=//div[@id='memberships']//table//tbody/tr[1]/td[7]/span/a[text()='View']" );
        $this->waitForElementPresent("_qf_MembershipView_cancel-bottom");
        
        $this->webtestVerifyTabularData( 
                                        array( 'Membership Type' => "Membership Type $title",
                                               'Status'          => $status,
                                               'Source'          => $sourceText,
                                               'Member Since'    => $joinDate,
                                               'Start date'      => $startDate,
                                               'End date'        => $endDate
                                               )
                                         );
    }
}