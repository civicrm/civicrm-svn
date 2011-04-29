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


 
class WebTest_Member_InheritedMembershipTest extends CiviSeleniumTestCase {

    protected $captureScreenshotOnFailure = TRUE;
    protected $screenshotPath = '/var/www/api.dev.civicrm.org/public/sc';
    protected $screenshotUrl = 'http://api.dev.civicrm.org/sc/';
    
    protected function setUp()
    {
        parent::setUp();
    }
    
    function testInheritedMembership( ) 
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
        
        $this->select( 'period_type', "label=rolling" );    
        
        $this->removeSelection("relationship_type_id", "label=- select -");
        $this->addSelection("relationship_type_id", "label=Employer of");
         
        $this->click( '_qf_MembershipType_upload-bottom' );
        $this->waitForElementPresent( 'link=Add Membership Type' );
        $this->assertTrue( $this->isTextPresent( "The membership type 'Membership Type $title' has been saved." ) ); 
                
        $this->open( $this->sboxPath . "civicrm/contact/add?reset=1&ct=Organization" );
        $this->waitForElementPresent( '_qf_Contact_cancel' );
        
        // creating another Orgnization
        $title1 = substr(sha1(rand()), 0, 7);
        $this->type( 'organization_name', "Organization $title1" );
        $this->type( 'email_1_email', "$title1@org.com" );
        $this->click( '_qf_Contact_upload_view' );
        $this->waitForPageToLoad("30000");
        
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
        $previousYear = $currentYear - 1;
        $nextYear     = $currentYear + 1;
        $joinDate     = date('Y-m-d', mktime( 0, 0, 0, 4, 25, $currentYear ) ); 
        $startDate    = date('Y-m-d', mktime( 0, 0, 0, 4, 25,   $currentYear ) );
        $endDate      = date('Y-m-d', mktime( 0, 0, 0, 3, 31, $nextYear ) );
        $configVars   = new CRM_Core_Config_Variables( );        
        foreach ( array( 'joinDate', 'startDate', 'endDate' ) as $date ) {
            $$date = CRM_Utils_Date::customFormat( $$date, $configVars->dateformatFull ); 
        }
        
        // fill in Join Date
        //$this->webtestFillDate( 'join_date' );
        
        // Clicking save.
        $this->click( '_qf_Membership_upload' );
        $this->waitForPageToLoad("30000");
        
        // page was loaded
        $this->waitForTextPresent( $sourceText );
        
        // Is status message correct?
        $this->assertTrue( $this->isTextPresent( "Membership Type $title membership for Organization $title1 has been added." ),
                           "Status message didn't show up after saving!");

        // click through to the membership view screen
        $this->click( "xpath=//div[@id='memberships']//table//tbody/tr[1]/td[7]/span/a[text()='View']" );
        $this->waitForElementPresent("_qf_MembershipView_cancel-bottom");
        
        $this->webtestVerifyTabularData( 
                                        array( 'Membership Type' => "Membership Type $title",
                                               'Status'          => 'New',
                                               'Source'          => $sourceText,
                                               //  'Member Since'    => $joinDate,
                                               //'Start date'      => $startDate,
                                               //'End date'        => $endDate
                                               )
                                         );

        // Adding contact
        // We're using Quick Add block on the main page for this.
        $firstName = substr(sha1(rand()), 0, 7);
        $this->webtestAddContact( $firstName, "Anderson", "$firstName@anderson.name" );

        // visit relationship tab
        $this->click("css=li#tab_rel a");
        $this->waitForElementPresent("css=div.action-link");
        $this->click("//div[@id='crm-container-snippet']/div/div[1]/div[1]/a/span");
        $this->waitForPageToLoad("30000");
        $this->click("relationship_type_id");
        $this->select("relationship_type_id", "label=Employee of");
       
        $this->typeKeys( 'rel_contact', $title1);
        $this->fireEvent("rel_contact", "focus");
        $this->waitForElementPresent("css=div.ac_results-inner li");
        $this->click("css=div.ac_results-inner li");
        
        $this->waitForElementPresent("quick-save");
                
        $description = "Well here is some description !!!!";
        $this->type("description", $description );
        
        //save the relationship
        $this->click("quick-save");
        $this->waitForElementPresent("current-relationships");
        
        //check the status message
        $this->assertTrue($this->isTextPresent("1 new relationship record created."));
        
        $this->waitForElementPresent("xpath=//div[@id='current-relationships']//div//table/tbody//tr/td[9]/span/a[text()='View']");
      
        // click through to the membership view screen
        $this->click( 'css=li#tab_member a' );
        $this->waitForElementPresent("css=div#memberships");     
      
        // visit relationship tab
        $this->click("css=li#tab_rel a");
        $this->waitForElementPresent("css=div.action-link");
        $this->click("//li[@id='tab_rel']/a");
        $this->click("//div[@id='squeeze']/div/div");
        $this->waitForElementPresent("xpath=//div[@id='current-relationships']//div//table/tbody//tr/td[9]/span/a[text()='Edit']");
        $this->click("xpath=//div[@id='current-relationships']//div//table/tbody//tr/td[9]/span/a[text()='Edit']");
        $this->waitForElementPresent("is_active");
        $this->click("is_permission_a_b");
        $this->uncheck("is_active");
     
    }
    
}