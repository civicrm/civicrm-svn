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


class WebTest_Contribute_OnBehalfOfOrganization extends CiviSeleniumTestCase {
    protected $captureScreenshotOnFailure = TRUE;
    protected $screenshotPath = '/var/www/api.dev.civicrm.org/public/sc';
    protected $screenshotUrl = 'http://api.dev.civicrm.org/sc/';
    protected $pageno = '';
    protected function setUp()
    {
        parent::setUp();
    }
    
    function testAnomoyousOganization()
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
        
        // We need a payment processor
        $processorName = "Webtest Dummy" . substr( sha1( rand( ) ), 0, 7 );  
        $processorType = 'Dummy';
        $pageTitle = substr( sha1( rand( ) ), 0, 7 );
        $rand = 2 * rand( 2, 50 );
        $hash = substr(sha1(rand()), 0, 7);
        $amountSection = true;
        $payLater =  true;
        $onBehalf = 'optional';
        $pledges = false;
        $recurring = false;
        $memberships = false;
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
                                                     $processorType, 
                                                     $processorName, 
                                                     $amountSection, 
                                                     $payLater, 
                                                     $onBehalf,
                                                     $pledges, 
                                                     $recurring, 
                                                     $memberships, 
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
        $this->open( $this->sboxPath . "logout" );
        $this->waitForPageToLoad( '30000' );
        
        //Open Live Contribution Page
        $this->open( $this->sboxPath . "civicrm/contribute/transact?reset=1&id=" . $pageId );
        
        $this->waitForElementPresent( "_qf_Main_upload-bottom" );
        
        $this->click( 'CIVICRM_QFID_amount_other_radio_4' );
        $this->type( 'amount_other', 100 );
        
        $firstName = 'Ma' . substr( sha1( rand( ) ), 0, 4 );
        $lastName  = 'An' . substr( sha1( rand( ) ), 0, 7 );
        $orgName = 'org_11_' . substr( sha1( rand( ) ), 0, 7 );
        $this->type( "email-5", $firstName . "@example.com" );
        
        // enable onbehalforganization block
        $this->click("is_for_organization");
        $this->waitForElementPresent( "onbehalf_state_province-3" );
        
        // onbehalforganization info
        $this->type( "onbehalf_organization_name", $orgName  );
        $this->type( "onbehalf_phone-3", substr( sha1( rand( ) ), 0, 10 ) );
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
        $this->open( $this->sboxPath . "civicrm/contribute/search&reset=1" );
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
    
    
    function testLoginUserOrganization()
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
        $this->waitForPageToLoad( '30000' );

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
        $this->assertTrue($this->isTextPresent("Your custom field set '$groupTitle' has been added. You can add custom fields now."), "Status message didn't show up after saving custom field set!");
        
        // add a custom field to the custom group
        $fieldTitle = "Custom Field " . substr(sha1(rand()), 0, 7);
        $this->type("label", $fieldTitle );
        
        $this->select("data_type[1]", "value=Text");
        $this->click( '_qf_Field_next-bottom' );

        $this->waitForPageToLoad( '30000' );
        $this->assertTrue($this->isTextPresent("Your custom field '$fieldTitle' has been saved."), "Status message didn't show up after saving custom field set!");
        $url = explode( '&id=', $this->getAttribute( "xpath=//div[@id='field_page']/div[2]/table/tbody//tr/td[1][text()='$fieldTitle']/../td[7]/span/a@href" ) );
        $fieldId = $url[1];
        
        // get cid for login user
        $this->open( $this->sboxPath . "civicrm/dashboard?reset=1" );
        $userId = explode( '&cid=', $this->getAttribute( "xpath=//div[@id='recently-viewed']/ul/li/a@href" ) );
        $userId = $userId[1];

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
        $this->open("{$this->sboxPath}admin/user/permissions");
        $this->waitForElementPresent('edit-submit');
        $this->check('edit-2-administer-CiviCampaign');
        $this->click('edit-submit');
        $this->waitForPageToLoad();
        $this->assertTrue($this->isTextPresent('The changes have been saved.'));
        
        // Go directly to the URL of the screen that you will be add campaign
        $this->open($this->sboxPath . "civicrm/campaign/add&reset=1");
        
        // As mentioned before, waitForPageToLoad is not always reliable. Below, we're waiting for the submit
        // button at the end of this page to show up, to make sure it's fully loaded.
        $this->waitForElementPresent("_qf_Campaign_next-bottom");

        // Let's start filling the form with values.
        $title = 'Campaign ' . substr(sha1(rand()), 0, 7);
        $this->type( "title", $title );
        
        // select the campaign type
        $this->select("campaign_type_id", "value=2");
        
        // fill in the description
        $this->type("description", "This is a test campaign");
        
        // include groups for the campaign
        $this->addSelection("includeGroups-f", "label=Advisory Board");
        $this->click("//option[@value=4]");
        $this->click("add");
        
        // fill the end date for campaign
        $this->webtestFillDate("end_date", "+1 year");
        
        // select campaign status
        $this->select("status_id", "value=2");
        
        // click save
        $this->click("_qf_Campaign_next-bottom");
        $this->waitForPageToLoad("30000");
        
        $this->assertTrue($this->isTextPresent("Campaign {$title} has been saved."), "Status message didn't show up after saving!");
                
        $this->open( $this->sboxPath . "civicrm/admin/uf/group?reset=1" );
        $this->waitForPageToLoad("30000");

        $this->click( "xpath=//div[@id='uf_profile']/div[2]/table/tbody//tr/td[1][text()='On Behalf Of Organization']/../td[7]/span/a[text()='Fields']" );
        $this->waitForPageToLoad("30000");

        $this->click( "link=Add Field" );
        $this->waitForElementPresent( '_qf_Field_next-bottom' );
        $this->select( 'field_name[0]', 'value=Contribution' );
        $this->select( 'field_name[1]', 'label=Campaign' );
        $this->click( 'field_name[1]' );
        $this->click( '_qf_Field_next_new-bottom' );
        $this->waitForPageToLoad("30000");
        $this->waitForElementPresent( '_qf_Field_cancel-bottom' );
        
        $this->select( 'field_name[0]', 'value=Contribution' );
        $this->select( 'field_name[1]', "label=$fieldTitle :: $groupTitle" );
        $this->click( 'field_name[1]' );
        $this->click( '_qf_Field_next-bottom' );
        $this->waitForPageToLoad("30000");
        $this->assertTrue($this->isTextPresent( "Your CiviCRM Profile Field '{$fieldTitle}' has been saved to 'On Behalf Of Organization'." ),
                          "Status message didn't show up after saving!");
        
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
        $this->type( "phone_1_phone", substr( sha1( rand( ) ), 0, 4 ) );
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
        $this->open( $this->sboxPath . "civicrm/contact/view/rel?cid={$userId}&action=add&reset=1" );
        $this->waitForPageToLoad( '30000' );
        
        // select relationship type
        $this->click( "relationship_type_id" );
        $this->select( "relationship_type_id", "value=4_a_b" );
        
        // search organization
        $this->type( 'rel_contact',$orgName1 );
        $this->click( "rel_contact" );
        $this->waitForElementPresent( "css=div.ac_results-inner li" );
        $this->click( "css=div.ac_results-inner li" );
        $this->assertContains( $orgName1, $this->getValue( 'rel_contact' ), "autocomplete expected $orgName1 but didn’t find it in " . $this->getValue('rel_contact' ) );
        
        // give permission
        $this->click( "is_permission_a_b" );
        $this->click( "is_permission_b_a" );
        
        // save relationship
        $this->click( "details-save" );
        
        // create contribution page
        $processorName = "Webtest Dummy" . substr( sha1( rand( ) ), 0, 7 );  
        $processorType = 'Dummy';
        $pageTitle = substr( sha1( rand( ) ), 0, 7 );
        $rand = 100;
        $hash = substr(sha1(rand()), 0, 7);
        $amountSection = true;
        $payLater =  true;
        $onBehalf = 'required';
        $pledges = false;
        $recurring = false;
        $memberships = false;
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
        
        // create contribution page with randomized title and default params
        $pageId = $this->webtestAddContributionPage( $hash, 
                                                     $rand, 
                                                     $pageTitle, 
                                                     $processorType, 
                                                     $processorName, 
                                                     $amountSection, 
                                                     $payLater, 
                                                     $onBehalf,
                                                     $pledges, 
                                                     $recurring, 
                                                     $memberships, 
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
        
        
        //Open Live Contribution Page
        $this->open( $this->sboxPath . "civicrm/contribute/transact?reset=1&id=" . $pageId );
        $this->waitForElementPresent( "_qf_Main_upload-bottom" );
        $this->click( 'CIVICRM_QFID_amount_other_radio_4' );
        $this->type( 'amount_other', 60 );

        $this->select( 'onbehalf_contribution_campaign_id', "label={$title}" );
        $this->type( "onbehalf_custom_{$fieldId}", 'Test Subject' );
        
        // Credit Card Info
        $this->select( "credit_card_type", "value=Visa" );
        $this->type( "credit_card_number", "4111111111111111" );
        $this->type( "cvv2", "000" );
        $this->select( "credit_card_exp_date[M]", "value=1" );
        $this->select( "credit_card_exp_date[Y]", "value=2020" );

        //Billing Info
        $this->type( "billing_first_name", 'Admin First Name', 'billing' );
        $this->type( "billing_last_name", 'Admin Last Name' . 'billing'  );
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
        $this->open( $this->sboxPath . "civicrm/contribute/search&reset=1" );
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
    }
}