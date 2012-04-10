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


class WebTest_Contribute_OnBehalfOfOrganization extends CiviSeleniumTestCase {
    protected $captureScreenshotOnFailure = TRUE;
    protected $screenshotPath = '/var/www/api.dev.civicrm.org/public/sc';
    protected $screenshotUrl = 'http://api.dev.civicrm.org/sc/';
    protected $pageno = '';
    protected function setUp()
    {
        parent::setUp();
    }
    
    function testOnBehalfOfOrganization( ) {
        
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
        
        // create new individual
        $firstName = 'John_' . substr(sha1(rand()), 0, 7);
        $lastName  = 'Anderson_' . substr(sha1(rand()), 0, 7);
        $email     = "{$firstName}.{$lastName}@example.com";
        $contactParams = array( 'first_name' => $firstName,
                                'last_name'  => $lastName,
                                'email-5'    => $email );
        $streetAddress = "100 Main Street";
        
        //adding contact for membership sign up 
        $this->webtestAddContact( $firstName, $lastName, $email );
        $urlElements = $this->parseURL( );
        print_r($urlElements);
        $cid = $urlElements['queryString']['cid'];
        $this->assertType( 'numeric', $cid );
        
        // We need a payment processor
        $processorName = "Webtest Dummy" . substr( sha1( rand( ) ), 0, 7 );  
        $processorType = 'Dummy';
        $pageTitle = substr( sha1( rand( ) ), 0, 7 );
        $rand = 100;
        $hash = substr(sha1(rand()), 0, 7);
        $amountSection = true;
        $payLater =  true;
        $onBehalf = 'optional';
        $pledges = false;
        $recurring = false;
        $memberships = false;
        $memPriceSetId = null;
        $friend = true;
        $profilePreId  = null;
        $profilePostId = null;
        $premiums = false;
        $widget = false;
        $pcp = false;
        $honoreeSection = false; 
        $isAddPaymentProcessor = true;
        $isPcpApprovalNeeded = false;
        $isSeparatePayment = false;
        
        // create a new online contribution page
        // create contribution page with randomized title and default params
        $pageId = $this->webtestAddContributionPage( $hash, 
                                                     $rand, 
                                                     $pageTitle, 
                                                     array($processorName => $processorType),
                                                     $amountSection, 
                                                     $payLater, 
                                                     $onBehalf,
                                                     $pledges, 
                                                     $recurring, 
                                                     $memberships, 
                                                     $memPriceSetId,
                                                     $friend, 
                                                     $profilePreId,
                                                     $profilePostId,
                                                     $premiums, 
                                                     $widget, 
                                                     $pcp,
                                                     $isAddPaymentProcessor,
                                                     $isPcpApprovalNeeded,
                                                     $isSeparatePayment,
                                                     $honoreeSection );
        
        //logout
        $this->open( $this->sboxPath . "civicrm/logout?reset=1" );
        $this->waitForPageToLoad( '30000' );
        $this->_testAnomoyousOganization( $pageId, $cid, $pageTitle );
        $this->open( $this->sboxPath . "civicrm/logout?reset=1" );
        $this->waitForPageToLoad( '30000' );
        $this->_testUserWithOneRelationship( $pageId, $cid, $pageTitle );
        $this->open( $this->sboxPath . "civicrm/logout?reset=1" );
        $this->waitForPageToLoad( '30000' );
        $this->_testUserWithMoreThanOneRelationship( $pageId, $cid, $pageTitle );
    }
    
    function _testAnomoyousOganization( $pageId, $cid, $pageTitle )
    {
        //Open Live Contribution Page
        $this->open( $this->sboxPath . "civicrm/contribute/transact?reset=1&id=" . $pageId );
        
        $this->waitForElementPresent( "_qf_Main_upload-bottom" );
        
        $this->click( 'CIVICRM_QFID_amount_other_radio_4' );
        $this->type( 'amount_other', 60 );
        
        $firstName = 'Ma' . substr( sha1( rand( ) ), 0, 4 );
        $lastName  = 'An' . substr( sha1( rand( ) ), 0, 7 );
        $orgName = 'org_11_' . substr( sha1( rand( ) ), 0, 7 );
        $this->type( "email-5", $firstName . "@example.com" );
        
        // enable onbehalforganization block
        $this->click("is_for_organization");
        $this->waitForElementPresent( "onbehalf_state_province-3" );
        
        // onbehalforganization info
        $this->type( "onbehalf_organization_name", $orgName  );
        $this->type( "onbehalf_phone-3-1", 9999999999 );
        $this->type( "onbehalf_email-3", "{$orgName}@example.com");
        $this->type( "onbehalf_street_address-3", "Test Street Address");
        $this->type( "onbehalf_city-3", "Test City");
        $this->type( "onbehalf_postal_code-3", substr( sha1( rand( ) ), 0, 6 ) );
        $this->click( "onbehalf_country-3");
        $this->select( "onbehalf_country-3", "label=United States" );
        $this->click( "onbehalf_state_province-3" );
        $this->select( "onbehalf_state_province-3", "label=Alabama" );
        
        
        // Credit Card Info
        $this->select( "credit_card_type", "value=Visa" );
        $this->type( "credit_card_number", "4111111111111111" );
        $this->type( "cvv2", "000" );
        $this->select( "credit_card_exp_date[M]", "value=1" );
        $this->select( "credit_card_exp_date[Y]", "value=2020" );
        
        //Billing Info
        $this->type( "billing_first_name", $firstName . 'billing' );
        $this->type( "billing_last_name", $lastName . 'billing'  );
        $this->type( "billing_street_address-5", "0121 Mount Highschool." );
        $this->type( " billing_city-5", "Shangai" );
        $this->select( "billing_country_id-5", "value=1228" );
        $this->select( "billing_state_province_id-5", "value=1004" );
        $this->type( "billing_postal_code-5", "94129" );  
        $this->click( "_qf_Main_upload-bottom" );
        
        $this->waitForPageToLoad( '30000' );
        $this->waitForElementPresent( "_qf_Confirm_next-bottom" );
        
        $this->click( "_qf_Confirm_next-bottom" );
        $this->waitForPageToLoad( '30000' );
        
        //login to check contribution
        $this->open( $this->sboxPath );
        
        // Log in using webtestLogin() method
        $this->webtestLogin( );
        
        //Find Contribution
        $this->open( $this->sboxPath . "civicrm/contribute/search?reset=1" );
        $this->type( "sort_name", $orgName );
        $this->click( "_qf_Search_refresh" );
        
        $this->waitForPageToLoad( '30000' );
        
        $this->waitForElementPresent( "xpath=//div[@id='contributionSearch']//table//tbody/tr[1]/td[11]/span/a[text()='View']" );
        $this->click( "xpath=//div[@id='contributionSearch']//table//tbody/tr[1]/td[11]/span/a[text()='View']" );
        $this->waitForPageToLoad( '30000' );
        $this->waitForElementPresent( "_qf_ContributionView_cancel-bottom" );
        
        // verify contrb created
        $expected = array( 1   => $orgName,  
                           2   => 'Donation', 
                           10  => $pageTitle 
                           ); 
        foreach ( $expected as  $value => $label ) { 
            $this->verifyText( "xpath=id( 'ContributionView' )/div[2]/table[1]/tbody/tr[$value]/td[2]", preg_quote( $label ) ); 
        }
        
    }
    
    
    function _testUserWithOneRelationship( $pageId, $cid, $pageTitle )
    {
        // Logging in. Remember to wait for page to load. In most cases,
        // you can rely on 30000 as the value that allows your test to pass, however,
        // sometimes your test might fail because of this. In such cases, it's better to pick one element
        // somewhere at the end of page and use waitForElementPresent on it - this assures you, that whole
        // page contents loaded and you can continue your test execution.
        $this->webtestLogin( );
        $this->waitForPageToLoad( '30000' );
        
        // Create new group
        $groupName = $this->WebtestAddGroup( );
        $this->open( $this->sboxPath . "civicrm/group?reset=1" );
        $this->waitForPageToLoad( '30000' );
        
        $groupId = $this->getText( "xpath=//div[@id='group']/div[3]/table/tbody//tr/td[text()='{$groupName}']/../td[2]" );
        
        $this->open( $this->sboxPath . "civicrm/contact/view?reset=1&cid={$cid}" );
        $this->waitForPageToLoad("30000");
        
        $this->click( 'link=Edit' );
        $this->waitForElementPresent( '_qf_Contact_cancel-bottom' );
        $this->click( 'addressBlock' );
        $this->waitForElementPresent( 'link=Another Address' );
        
        //Billing Info
        $this->select( 'address_1_location_type_id', 'label=Billing' );
        $this->type( 'address_1_street_address', '0121 Mount Highschool.' );
        $this->type( 'address_1_city', "Shangai" );
        $this->type( 'address_1_postal_code', "94129" );
        $this->select( 'address_1_country_id', "value=1228" );
        $this->select( 'address_1_state_province_id', "value=1004" );
        $this->click( '_qf_Contact_upload_view-bottom' );
        $this->waitForPageToLoad("30000");
        
        // add contact to group
        // visit group tab
        $this->click("css=li#tab_group a");
        $this->waitForElementPresent("group_id");
        
        // add to group
        $this->select("group_id", "label={$groupName}");
        $this->click("_qf_GroupContact_next");
        $this->waitForPageToLoad("30000");
        
        $this->open($this->sboxPath . "civicrm/admin/custom/group?action=add&reset=1");
        $this->waitForElementPresent("_qf_Group_next-bottom");
        
        // fill in a unique title for the custom group
        $groupTitle = "Custom Group" . substr(sha1(rand()), 0, 7);
        $this->type("title", $groupTitle );
        
        // select the group this custom data set extends
        $this->select("extends[0]", "value=Contribution");
        $this->waitForElementPresent("extends[1]");
        
        // save the custom group
        $this->click("_qf_Group_next-bottom");
        $this->waitForElementPresent("_qf_Field_next_new-bottom");
        $this->assertTrue($this->isTextPresent("Your custom field set '$groupTitle' has been added. You can add custom fields now.") );
        
        // add a custom field to the custom group
        $fieldTitle = "Custom Field " . substr(sha1(rand()), 0, 7);
        $this->type("label", $fieldTitle );
        
        $this->select("data_type[1]", "value=Text");
        $this->click( '_qf_Field_next-bottom' );
        
        $this->waitForPageToLoad( '30000' );
        $this->assertTrue($this->isTextPresent("Your custom field '$fieldTitle' has been saved.") );
        $url = explode( '&id=', $this->getAttribute( "xpath=//div[@id='field_page']/div[2]/table/tbody//tr/td[1][text()='$fieldTitle']/../td[8]/span/a@href" ) );
        $fieldId = $url[1];
        
        // Enable CiviCampaign module if necessary
        $this->open( $this->sboxPath . "civicrm/admin/setting/component?reset=1" );
        $this->waitForPageToLoad( '30000' );
        $this->waitForElementPresent( '_qf_Component_next-bottom' );
        $enabledComponents = $this->getSelectOptions( 'enableComponents-t' );
        if ( !in_array( "CiviCampaign", $enabledComponents ) ) {
            $this->addSelection( 'enableComponents-f', "label=CiviCampaign");
            $this->click( "//option[@value='CiviCampaign']" );
            $this->click( 'add' );
            $this->click( '_qf_Component_next-bottom' );
            $this->waitForPageToLoad( "30000" );          
            $this->assertTrue( $this->isTextPresent( 'Your changes have been saved.' ) );    
        }
        
        // add the required Drupal permission
        $permission = array('edit-2-administer-civicampaign');
        $this->changePermissions( $permission );
        
        // Go directly to the URL of the screen that you will be add campaign
        $this->open($this->sboxPath . "civicrm/campaign/add?reset=1");
        
        // As mentioned before, waitForPageToLoad is not always reliable. Below, we're waiting for the submit
        // button at the end of this page to show up, to make sure it's fully loaded.
        $this->waitForElementPresent("_qf_Campaign_upload-bottom");
        
        // Let's start filling the form with values.
        $title = 'Campaign ' . substr(sha1(rand()), 0, 7);
        $this->type( "title", $title );
        
        // select the campaign type
        $this->select("campaign_type_id", "value=2");
        
        // fill in the description
        $this->type("description", "This is a test campaign");
        
        // include groups for the campaign
        $this->addSelection("includeGroups-f", "label={$groupName}");
        $this->click("//option[@value={$groupId}]");
        $this->click("add");
        
        // fill the end date for campaign
        $this->webtestFillDate("end_date", "+1 year");
        
        // select campaign status
        $this->select("status_id", "value=2");
        
        // click save
        $this->click("_qf_Campaign_upload-bottom");
        $this->waitForElementPresent( "xpath=//div[@id='campaigns_wrapper']//table[@id='campaigns']/tbody//tr/td[3][text()='{$title}']" );
        $this->assertTrue($this->isTextPresent("Campaign {$title} has been saved."), "Status message didn't show up after saving!");
        
        $this->open( $this->sboxPath . "civicrm/admin/uf/group?reset=1" );
        $this->waitForPageToLoad("30000");
        $this->click( "link=Reserved Profiles" );
        
        $this->click( "xpath=//div[@id='reserved-profiles']/div/div/table/tbody//tr/td[1][text()='On Behalf Of Organization']/../td[5]/span/a[text()='Fields']" );
        $this->waitForPageToLoad("30000");
        
        $this->click( "link=Add Field" );
        $this->waitForElementPresent( '_qf_Field_next-bottom' );
        $this->select( 'field_name[0]', 'value=Contribution' );
        $this->select( 'field_name[1]', 'label=Campaign' );
        $this->click( 'field_name[1]' );
        $this->click( '_qf_Field_next_new-bottom' );
        $this->waitForPageToLoad("30000");
        
        $this->select( 'field_name[0]', 'value=Contribution' );
        $this->select( 'field_name[1]', "label=$fieldTitle :: $groupTitle" );
        $this->click( 'field_name[1]' );
        $this->click( '_qf_Field_next-bottom' );
        $this->waitForPageToLoad("30000");
        $this->assertTrue( $this->isTextPresent( "Your CiviCRM Profile Field '{$fieldTitle}' has been saved to 'On Behalf Of Organization'." ) );
        
        // Open Page to create Organization
        $this->open( $this->sboxPath . "civicrm/contact/add?reset=1&ct=Organization" );
        $this->waitForElementPresent( "_qf_Contact_upload_view-bottom" );
        $orgName1 = 'org1_' . substr( sha1( rand( ) ), 0, 7 );
        
        // Type Organization name
        $this->type( "organization_name", $orgName1 );
        
        // Type Organizatio email for main
        $this->type( "email_1_email", "{$orgName1}@example.com" );
        $this->select("email_1_location_type_id", "value=3");
        
        // type phone no for main
        $this->type( "phone_1_phone", 9999999999 );
        $this->select("phone_1_location_type_id", "value=3");
        
        //address section    
        $this->click("addressBlock");
        $this->waitForElementPresent("address_1_street_address");
        
        //fill in address 1 for main
        $this->select( "address_1_location_type_id", "value=3" );
        $this->type( "address_1_street_address", "{$orgName1} street address" );
        $this->type( "address_1_city", "{$orgName1} city" );
        $this->type( "address_1_postal_code", substr( sha1( rand( ) ), 0, 4 ) );
        $this->assertTrue( $this->isTextPresent( "- select - United States" ) );
        $this->select( "address_1_state_province_id", "value=1019" );
        $this->type( "address_1_geo_code_1", "1234" );
        $this->type( "address_1_geo_code_2", "5678" );
        
        // Save the Organization
        $this->click( "_qf_Contact_upload_view-bottom" );
        $this->waitForPageToLoad( '30000' );
        
        // open contact
        $this->open( $this->sboxPath . "civicrm/contact/view/rel?cid={$cid}&action=add&reset=1" );
        $this->waitForPageToLoad( '30000' );
        
        // select relationship type
        $this->click( "relationship_type_id" );
        $this->select( "relationship_type_id", "value=4_a_b" );
        
        // search organization
        $this->type( 'contact_1',$orgName1 );
        $this->click( "contact_1" );
        $this->waitForElementPresent( "css=div.ac_results-inner li" );
        $this->click( "css=div.ac_results-inner li" );
        $this->assertContains( $orgName1, $this->getValue( 'contact_1' ), "autocomplete expected $orgName1 but didn’t find it in " . $this->getValue('contact_1' ) );
        
        // give permission
        $this->click( "is_permission_a_b" );
        $this->click( "is_permission_b_a" );
        
        // save relationship
        $this->click( "details-save" );
        
        //Open Live Contribution Page
        $this->open( $this->sboxPath . "civicrm/contribute/transact?reset=1&id=" . $pageId . "&cid=" . $cid);
        $this->waitForElementPresent( "onbehalf_state_province-3" );
        $this->click( 'CIVICRM_QFID_amount_other_radio_4' );
        $this->type( 'amount_other', 60 );
        $this->click( 'onbehalf_contribution_campaign_id' );
        $this->select( 'onbehalf_contribution_campaign_id', "label={$title}" );
        $this->type( "onbehalf_custom_{$fieldId}", 'Test Subject' );
        
        // Credit Card Info
        $this->select( "credit_card_type", "value=Visa" );
        $this->type( "credit_card_number", "4111111111111111" );
        $this->type( "cvv2", "000" );
        $this->select( "credit_card_exp_date[M]", "value=1" );
        $this->select( "credit_card_exp_date[Y]", "value=2020" );
        
        //Billing Info
        $this->type( "billing_street_address-5", "0121 Mount Highschool." );
        $this->type( " billing_city-5", "Shangai" );
        $this->select( "billing_country_id-5", "value=1228" );
        $this->select( "billing_state_province_id-5", "value=1004" );
        $this->type( "billing_postal_code-5", "94129" );  
        
        $this->click( "_qf_Main_upload-bottom" );
        $this->waitForPageToLoad( '30000' );
        $this->waitForElementPresent( "_qf_Confirm_next-bottom" );
        $this->click( "_qf_Confirm_next-bottom" );
        $this->waitForPageToLoad( '30000' );
        
        //Find Contribution
        $this->open( $this->sboxPath . "civicrm/contribute/search?reset=1" );
        $this->type( "sort_name", $orgName1 );
        $this->click( "_qf_Search_refresh" );
        $this->waitForPageToLoad( '30000' );
        $this->waitForElementPresent( "xpath=//div[@id='contributionSearch']//table//tbody/tr[1]/td[11]/span/a[text()='View']" );
        $this->click( "xpath=//div[@id='contributionSearch']//table//tbody/tr[1]/td[11]/span/a[text()='View']" );
        $this->waitForPageToLoad( '30000' );
        $this->waitForElementPresent( "_qf_ContributionView_cancel-bottom" );
        
        // verify contrb created
        $expected = array( 1   => $orgName1,  
                           2   => 'Donation', 
                           10  => $title,
                           11  => $pageTitle 
                           ); 
        foreach ( $expected as  $value => $label ) { 
            $this->verifyText( "xpath=id( 'ContributionView' )/div[2]/table[1]/tbody/tr[$value]/td[2]", preg_quote( $label ) ); 
        }
        
        
        $this->open( $this->sboxPath . "civicrm/admin/uf/group?reset=1" );
        $this->waitForPageToLoad("30000");
        $this->click( "link=Reserved Profiles" );
        
        $this->click( "xpath=//div[@id='reserved-profiles']/div/div/table/tbody//tr/td[1][text()='On Behalf Of Organization']/../td[5]/span/a[text()='Fields']" );
        $this->waitForPageToLoad("30000");
        
        $this->click( "xpath=//div[@id='field_page']/div[3]/table/tbody//tr/td[1][text()='Campaign']/../td[9]/span[2][text()='more ']/ul/li[2]/a[text()='Delete']" );
        $this->waitForElementPresent( '_qf_Field_next-bottom' );
        
        $this->click( '_qf_Field_next-bottom' );
        $this->waitForPageToLoad("30000");
        $this->assertTrue( $this->isTextPresent( 'Selected Profile Field has been deleted.' ), "Status message didn't show up after saving!" );
        
        $this->click( "xpath=//div[@id='field_page']/div[3]/table/tbody//tr/td[1][text()='{$fieldTitle}']/../td[9]/span[2][text()='more ']/ul/li[2]/a[text()='Delete']" );
        $this->waitForElementPresent( '_qf_Field_next-bottom' );
        
        $this->click( '_qf_Field_next-bottom' );
        $this->waitForPageToLoad("30000");
        $this->assertTrue( $this->isTextPresent( 'Selected Profile Field has been deleted.' ), "Status message didn't show up after saving!" );
    }
    
    
    function _testUserWithMoreThanOneRelationship( $pageId, $cid, $pageTitle )
    { 
        // Logging in. Remember to wait for page to load. In most cases,
        // you can rely on 30000 as the value that allows your test to pass, however,
        // sometimes your test might fail because of this. In such cases, it's better to pick one element
        // somewhere at the end of page and use waitForElementPresent on it - this assures you, that whole
        // page contents loaded and you can continue your test execution.
        $this->webtestLogin( );
        $this->waitForPageToLoad( '30000' );
        
        // Create new group
        $groupName = $this->WebtestAddGroup( );
        $this->open( $this->sboxPath . "civicrm/group?reset=1" );
        $this->waitForPageToLoad( '30000' );
        
        $groupId = $this->getText( "xpath=//div[@id='group']/div[3]/table/tbody//tr/td[text()='{$groupName}']/../td[2]" );
        
        $this->open( $this->sboxPath . "civicrm/contact/view?reset=1&cid={$cid}" );
        $this->waitForPageToLoad("30000");
        
        $this->click( 'link=Edit' );
        $this->waitForElementPresent( '_qf_Contact_cancel-bottom' );
        $this->click( 'addressBlock' );
        $this->waitForElementPresent( 'link=Another Address' );
        
        //Billing Info
        $this->select( 'address_1_location_type_id', 'label=Billing' );
        $this->type( 'address_1_street_address', '0121 Mount Highschool.' );
        $this->type( 'address_1_city', "Shangai" );
        $this->type( 'address_1_postal_code', "94129" );
        $this->select( 'address_1_country_id', "value=1228" );
        $this->select( 'address_1_state_province_id', "value=1004" );
        $this->click( '_qf_Contact_upload_view-bottom' );
        $this->waitForPageToLoad("30000");
        
        // add contact to group
        // visit group tab
        $this->click("css=li#tab_group a");
        $this->waitForElementPresent("group_id");
        
        // add to group
        $this->select("group_id", "label=$groupName");
        $this->click("_qf_GroupContact_next");
        $this->waitForPageToLoad("30000");
        
        $this->open($this->sboxPath . "civicrm/admin/custom/group?action=add&reset=1");
        $this->waitForElementPresent( "_qf_Group_next-bottom" );
        
        // fill in a unique title for the c$groupIdustom group
        $groupTitle = "Members Custom Group" . substr(sha1(rand()), 0, 7);
        $this->type( "title", $groupTitle );
        
        // select the group this custom data set extends
        $this->select( "extends[0]", "value=Membership" );
        $this->waitForElementPresent( "extends[1]" );
        
        // save the custom group
        $this->click( "_qf_Group_next-bottom" );
        
        $this->waitForElementPresent( "_qf_Field_next_new-bottom" );
        $this->assertTrue($this->isTextPresent( "Your custom field set '$groupTitle' has been added. You can add custom fields now." ) );
        
        // add a custom field to the custom group
        $fieldTitle = "Member Custom Field " . substr(sha1(rand()), 0, 7 );
        $this->type( "label", $fieldTitle );
        
        $this->select( "data_type[1]", "value=Text" );
        $this->click( '_qf_Field_next-bottom' );
        
        $this->waitForPageToLoad( '30000' );
        $this->assertTrue($this->isTextPresent( "Your custom field '$fieldTitle' has been saved.") );
        $url = explode( '&id=', $this->getAttribute( "xpath=//div[@id='field_page']/div[2]/table/tbody//tr/td[1][text()='$fieldTitle']/../td[8]/span/a@href" ) );
        $fieldId = $url[1];
        
        // Enable CiviCampaign module if necessary
        $this->open( $this->sboxPath . "civicrm/admin/setting/component?reset=1" );
        $this->waitForPageToLoad( '30000' );
        $this->waitForElementPresent( '_qf_Component_next-bottom' );
        $enabledComponents = $this->getSelectOptions( 'enableComponents-t' );
        if ( !in_array( "CiviCampaign", $enabledComponents ) ) {
            $this->addSelection( 'enableComponents-f', "label=CiviCampaign" );
            $this->click( "//option[@value='CiviCampaign']" );
            $this->click( 'add' );
            $this->click( '_qf_Component_next-bottom' );
            $this->waitForPageToLoad( "30000" );          
            $this->assertTrue( $this->isTextPresent( 'Your changes have been saved.' ) );    
        }
        
        // add the required Drupal permission
        $permission = array('edit-2-administer-civicampaign');
        $this->changePermissions( $permission );
        
        // Go directly to the URL of the screen that you will be add campaign
        $this->open( $this->sboxPath . "civicrm/campaign/add?reset=1" );
        
        // As mentioned before, waitForPageToLoad is not always reliable. Below, we're waiting for the submit
        // button at the end of this page to show up, to make sure it's fully loaded.
        $this->waitForElementPresent( "_qf_Campaign_upload-bottom" );
        
        // Let's start filling the form with values.
        $title = 'Campaign ' . substr(sha1(rand()), 0, 7);
        $this->type( "title", $title );
        
        // select the campaign type
        $this->select( "campaign_type_id", "value=2" );
        
        // fill in the description
        $this->type( "description", "This is a test campaign" );
        
        // include groups for the campaign
        $this->addSelection( "includeGroups-f", "label={$groupName}" );
        $this->click( "//option[@value={$groupId}]" );
        $this->click( "add" );
        
        // fill the end date for campaign
        $this->webtestFillDate( "end_date", "+1 year" );
        
        // select campaign status
        $this->select( "status_id", "value=2" );
        
        // click save
        $this->click( "_qf_Campaign_upload-bottom" );
        $this->waitForPageToLoad("30000");
        
        $this->assertTrue($this->isTextPresent( "Campaign {$title} has been saved."), "Status message didn't show up after saving!" );
        
        $this->open( $this->sboxPath . "civicrm/admin/uf/group?reset=1" );
        $this->waitForPageToLoad("30000");
        $this->click( "link=Reserved Profiles" );
        $this->click( "xpath=//div[@id='reserved-profiles']/div/div/table/tbody//tr/td[1][text()='On Behalf Of Organization']/../td[5]/span/a[text()='Fields']" );
        $this->waitForPageToLoad("30000");
        
        $this->click( "link=Add Field" );
        $this->waitForElementPresent( '_qf_Field_next-bottom' );
        $this->select( 'field_name[0]', 'value=Membership' );
        $this->select( 'field_name[1]', 'label=Campaign' );
        $this->click( 'field_name[1]' );
        $this->click( '_qf_Field_next_new-bottom' );
        $this->waitForPageToLoad("30000");
        $this->waitForElementPresent( '_qf_Field_cancel-bottom' );
        
        $this->select( 'field_name[0]', 'value=Membership' );
        $this->select( 'field_name[1]', "label=$fieldTitle :: $groupTitle" );
        $this->click( 'field_name[1]' );
        $this->click( '_qf_Field_next-bottom' );
        $this->waitForPageToLoad("30000");
        $this->assertTrue($this->isTextPresent( "Your CiviCRM Profile Field '{$fieldTitle}' has been saved to 'On Behalf Of Organization'." ),
                          "Status message didn't show up after saving!");
        
        // Open Page to create Organization 1
        $this->open( $this->sboxPath . "civicrm/contact/add?reset=1&ct=Organization" );
        $this->waitForElementPresent( "_qf_Contact_upload_view-bottom" );
        $orgName1 = 'org1_' . substr( sha1( rand( ) ), 0, 7 );
        
        // Type Organization name
        $this->type( "organization_name", $orgName1 );
        
        // Type Organizatio email for main
        $this->type( "email_1_email", "{$orgName1}@example.com" );
        $this->select( "email_1_location_type_id", "value=3" );
        
        // type phone no for main
        $this->type( "phone_1_phone", substr( sha1( rand( ) ), 0, 4 ) );
        $this->select( "phone_1_location_type_id", "value=3" );
        
        //address section    
        $this->click( "addressBlock" );
        $this->waitForElementPresent( "address_1_street_address" );
        
        //fill in address 1 for main
        $this->select( "address_1_location_type_id", "value=3" );
        $this->type( "address_1_street_address", "{$orgName1} street address" );
        $this->type( "address_1_city", "{$orgName1} city" );
        $this->type( "address_1_postal_code", "9999999999" );
        $this->assertTrue( $this->isTextPresent( "- select - United States" ) );
        $this->select( "address_1_state_province_id", "value=1019" );
        $this->type( "address_1_geo_code_1", "1234" );
        $this->type( "address_1_geo_code_2", "5678" );
        
        // Save the Organization
        $this->click( "_qf_Contact_upload_view-bottom" );
        $this->waitForPageToLoad( '30000' );
        
        // create second orzanization
        $this->open( $this->sboxPath . "civicrm/contact/add?reset=1&ct=Organization" );
        $this->waitForElementPresent( "_qf_Contact_upload_view-bottom" );
        $orgName2 = 'org2_' . substr( sha1( rand( ) ), 0, 7 );
        
        // Type Organization name
        $this->type( "organization_name", $orgName2 );
        
        // Type Organizatio email for main
        $this->type( "email_1_email", "{$orgName2}@example.com" );
        $this->select("email_1_location_type_id", "value=3");
        
        // type phone no for main
        $this->type( "phone_1_phone", substr( sha1( rand( ) ), 0, 4 ) );
        $this->select("phone_1_location_type_id", "value=3");
        
        //address section    
        $this->click("addressBlock");
        $this->waitForElementPresent("address_1_street_address");
        
        //fill in address 1 for main
        $this->select( "address_1_location_type_id", "value=3" );
        $this->type( "address_1_street_address", "{$orgName2} street address" );
        $this->type( "address_1_city", "{$orgName2} city" );
        $this->type( "address_1_postal_code", "7777777777" );
        $this->assertTrue( $this->isTextPresent( "- select - United States" ) );
        $this->select( "address_1_state_province_id", "value=1019" );
        $this->type( "address_1_geo_code_1", "1224" );
        $this->type( "address_1_geo_code_2", "5628" );
        
        // Save the Organization
        $this->click( "_qf_Contact_upload_view-bottom" );
        $this->waitForPageToLoad( '30000' );
        
        
        // create Membership type 
        $title1 = "Membership Type" . substr(sha1(rand()), 0, 7);
        $this->open( $this->sboxPath . "civicrm/admin/member/membershipType?reset=1&action=browse" );
        $this->waitForPageToLoad("30000");
        
        $this->click( "link=Add Membership Type" );
        $this->waitForElementPresent( '_qf_MembershipType_cancel-bottom' );
        
        $this->type( 'name', $title1 );
        $this->type( 'member_org', $orgName1 );
        $this->click( '_qf_MembershipType_refresh' );
        $this->waitForElementPresent( "xpath=//div[@id='membership_type_form']/fieldset/table[2]/tbody/tr[2]/td[2]" );
        
        $this->type( 'minimum_fee', '50' );
        
        $this->select( 'contribution_type_id', 'value=2' );
        
        $this->type( 'duration_interval', 1 );
        $this->select( 'duration_unit', "label=year" );
        
        $this->select( 'period_type', "label=fixed" );
        $this->waitForElementPresent( 'fixed_period_rollover_day[d]' );
        
        $this->select( 'fixed_period_start_day[M]', 'value=4' );
        $this->select( 'fixed_period_rollover_day[M]', 'value=1' );
        
        $this->select( 'relationship_type_id', 'value=4_b_a' );
        
        $this->click( '_qf_MembershipType_upload-bottom' );
        $this->waitForElementPresent( 'link=Add Membership Type' );
        $this->assertTrue( $this->isTextPresent( "The membership type '$title1' has been saved." ) );
        $typeUrl = explode( '&id=', $this->getAttribute( "xpath=//div[@id='membership_type']/div[2]/table/tbody//tr/td[1][text()='{$title1}']/../td[10]/span/a[3]@href" ) );
        $typeId = $typeUrl[1];
        
        // open contact
        $this->open( $this->sboxPath . "civicrm/contact/view/rel?cid={$cid}&action=add&reset=1" );
        $this->waitForPageToLoad( '30000' );
        
        // select relationship type
        $this->click( "relationship_type_id" );
        $this->select( "relationship_type_id", "value=4_a_b" );
        
        // search organization
        $this->type( 'contact_1',$orgName1 );
        $this->click( "contact_1" );
        $this->waitForElementPresent( "css=div.ac_results-inner li" );
        $this->click( "css=div.ac_results-inner li" );
        $this->assertContains( $orgName1, $this->getValue( 'contact_1' ), "autocomplete expected $orgName1 but didn’t find it in " . $this->getValue('contact_1' ) );
        
        // give permission
        $this->click( "is_permission_a_b" );
        $this->click( "is_permission_b_a" );
        
        // save relationship
        $this->click( "details-save" );
        
        // open contact
        $this->open( $this->sboxPath . "civicrm/contact/view/rel?cid={$cid}&action=add&reset=1" );
        $this->waitForPageToLoad( '30000' );
        
        // select relationship type
        $this->click( "relationship_type_id" );
        $this->select( "relationship_type_id", "value=4_a_b" );
        
        // search organization
        $this->type( 'contact_1',$orgName2 );
        $this->click( "contact_1" );
        $this->waitForElementPresent( "css=div.ac_results-inner li" );
        $this->click( "css=div.ac_results-inner li" );
        $this->assertContains( $orgName2, $this->getValue( 'contact_1' ), "autocomplete expected $orgName2 but didn’t find it in " . $this->getValue('contact_1' ) );
        
        // give permission
        $this->click( "is_permission_a_b" );
        $this->click( "is_permission_b_a" );
        
        // save relationship
        $this->click( "details-save" );
        
        // set membership type
        $this->open( $this->sboxPath . "civicrm/admin/contribute/membership?reset=1&action=update&id=" . $pageId );
        $this->waitForElementPresent( "_qf_MembershipBlock_upload_done-bottom" );
        $this->click( "member_is_active" );
        $this->click( "membership_type[{$typeId}]" );
        $this->click( "xpath=//div[@id='memberFields']//table[@class='report']/tbody//tr/td[1]/label[text()='{$title1}']/../../td[2]/input" );
        $this->click( '_qf_MembershipBlock_upload_done-bottom' );
        $this->waitForPageToLoad( '30000' );
        
        //Open Live Membership Page
        $this->open( $this->sboxPath . "civicrm/contribute/transact?reset=1&id=" . $pageId. "&cid=" . $cid );
        $this->waitForElementPresent( "_qf_Main_upload-bottom" );
        $this->click( 'CIVICRM_QFID_amount_other_radio_4' );
        $this->type( 'amount_other', 60 );
        $this->typeKeys( 'onbehalf_organization_name', $orgName1 );
        $this->click( "onbehalf_organization_name" );
        $this->waitForElementPresent( "css=div.ac_results-inner li" );
        $this->click( "css=div.ac_results-inner li" );
        sleep(5);
        $this->click( 'onbehalf_member_campaign_id' );
        $this->select( 'onbehalf_member_campaign_id', "label={$title}" );
        $this->type( "onbehalf_custom_{$fieldId}", 'Test Subject' );
        
        $this->assertContains( $orgName1, $this->getValue( 'onbehalf_organization_name' ), "autocomplete expected $orgName1 but didn’t find it in " . $this->getValue('onbehalf_organization_name' ) );
        
        // Credit Card Info
        $this->select( "credit_card_type", "value=Visa" );
        $this->type( "credit_card_number", "4111111111111111" );
        $this->type( "cvv2", "000" );
        $this->select( "credit_card_exp_date[M]", "value=1" );
        $this->select( "credit_card_exp_date[Y]", "value=2020" );
        
        $this->click( "_qf_Main_upload-bottom" );
        $this->waitForPageToLoad( '30000' );
        $this->waitForElementPresent( "_qf_Confirm_next-bottom" );
        $this->click( "_qf_Confirm_next-bottom" );
        $this->waitForPageToLoad( '30000' );
        
        //Find Membership for organization
        $this->open( $this->sboxPath . "civicrm/member/search?reset=1" );
        $this->type( "sort_name", $orgName1 );
        $this->click( "_qf_Search_refresh" );
        $this->waitForPageToLoad( '30000' );
        $this->waitForElementPresent( "xpath=//div[@id='memberSearch']//table//tbody/tr[1]/td[11]/span/a[text()='View']" );
        $this->click( "xpath=//div[@id='memberSearch']//table//tbody/tr[1]/td[11]/span/a[text()='View']" );
        $this->waitForPageToLoad( '30000' );
        $this->waitForElementPresent( "_qf_MembershipView_cancel-bottom" );
        
        //verify contrb created
        $expected = array( 1   => $orgName1,  
                           2   => $title1, 
                           3   => 'New'
                           ); 
        foreach ( $expected as  $value => $label ) { 
            $this->verifyText( "xpath=//form[@id='MembershipView']/div[2]/div/table/tbody/tr[$value]/td[2]", preg_quote( $label ) ); 
        }
        
        // find membership for contact in relationship
        $this->open( $this->sboxPath ."civicrm/contact/view?reset=1&force=1&cid={$cid}" );
        $this->waitForPageToLoad( '30000' );
        $this->click( "css=li#tab_member a" );
        $this->waitForElementPresent( "xpath=//div[@id='memberships']/div/table//tbody//tr/td[1][text()='{$title1}']" );
        $this->click( "xpath=//div[@id='memberships']/div/table//tbody//tr/td[1][text()='{$title1}']/../td[7]/span/a[text()='View']" );
        $this->waitForPageToLoad( '30000' );
        
        //verify contrb created
        $expected = array(
                          3   => $title1, 
                          4   => 'New'
                          ); 
        foreach ( $expected as  $value => $label ) { 
            $this->verifyText( "xpath=//form[@id='MembershipView']/div[2]/div/table/tbody/tr[$value]/td[2]", preg_quote( $label ) ); 
        }
        
        $this->open( $this->sboxPath . "civicrm/admin/uf/group?reset=1" );
        $this->waitForPageToLoad("30000");
        $this->click( "link=Reserved Profiles" );        
        $this->click( "xpath=//div[@id='reserved-profiles']/div/div/table/tbody//tr/td[1][text()='On Behalf Of Organization']/../td[5]/span/a[text()='Fields']" );
        $this->waitForPageToLoad("30000");
        
        $this->click( "xpath=//div[@id='field_page']/div[3]/table/tbody//tr/td[1][text()='Campaign']/../td[9]/span[2][text()='more ']/ul/li[2]/a[text()='Delete']" );
        $this->waitForElementPresent( '_qf_Field_next-bottom' );
        
        $this->click( '_qf_Field_next-bottom' );
        $this->waitForPageToLoad("30000");
        $this->assertTrue( $this->isTextPresent( 'Selected Profile Field has been deleted.' ), 
                           "Status message didn't show up after saving!" );
        
        $this->click( "xpath=//div[@id='field_page']/div[3]/table/tbody//tr/td[1][text()='{$fieldTitle}']/../td[9]/span[2][text()='more ']/ul/li[2]/a[text()='Delete']" );
        $this->waitForElementPresent( '_qf_Field_next-bottom' );
        
        $this->click( '_qf_Field_next-bottom' );
        $this->waitForPageToLoad("30000");
        $this->assertTrue( $this->isTextPresent( 'Selected Profile Field has been deleted.' ),
                           "Status message didn't show up after saving!" );
        
        $this->open( $this->sboxPath . "civicrm/contact/view?reset=1&cid={$cid}" );
        $this->waitForPageToLoad("30000");
        $this->click( "css=li#tab_rel a" );
        
        $this->waitForElementPresent( "xpath=//div[@id='current-relationships']/div/table/tbody//tr/td[2]/a[text()='{$orgName1}']" );
        $this->click( "xpath=//div[@id='current-relationships']/div/table/tbody//tr/td[2]/a[text()='{$orgName1}']/../../td[9]/span[2][text()='more ']/ul/li[2]/a[text()='Delete']" );
        
        // Check confirmation alert.
        $this->assertTrue( (bool)preg_match("/^Are you sure you want to delete this relationship?/", 
                                            $this->getConfirmation()) );
        $this->chooseOkOnNextConfirmation( );
        $this->waitForPageToLoad("30000");
        $this->assertTrue( $this->isTextPresent( 'Selected relationship has been deleted successfully.' ),
                           "Status message didn't show up after saving!" );
    }
    
}
