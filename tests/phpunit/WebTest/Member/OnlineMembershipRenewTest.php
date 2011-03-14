<?php

  /*
   +--------------------------------------------------------------------+
   | CiviCRM version 4.0                                                |
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

class WebTest_Member_OnlineMembershipRenewTest extends CiviSeleniumTestCase {
    
    protected function setUp()
    {
        parent::setUp();
    }
    
    function testOnlineMembershipRenew( ) 
    {
        // a random 7-char string and an even number to make this pass unique
        $hash = substr( sha1( rand( ) ), 0, 7 );
        $rand = 2 * rand( 2, 50 );
        
        // This is the path where our testing install resides. 
        // The rest of URL is defined in CiviSeleniumTestCase base class, in
        // class attributes.
        $this->open( $this->sboxPath );
        
        // Log in using webtestLogin() method
        $this->webtestLogin( );
        
        // We need a payment processor
        $processorName = "Webtest Dummy" . substr( sha1( rand( ) ), 0, 7 );
        $this->webtestAddPaymentProcessor( $processorName );
        
        $this->open( $this->sboxPath . "civicrm/admin/contribute/amount?reset=1&action=update&id=2" );
        
        //this contribution page for membership signup
        $this->waitForElementPresent( 'payment_processor_id' );
        $this->select( "payment_processor_id", "label=" . $processorName );
       
        // save
        $this->click( '_qf_Amount_next' );
        $this->waitForPageToLoad( );
        
        // go to Profiles
        $this->click( 'css=#tab_custom a' );
        
        // fill in Profiles
        $this->waitForElementPresent( 'custom_pre_id' );
        $this->select( 'custom_pre_id', 'value=1' );
        
        // save
        $this->click( '_qf_Custom_upload_done' );
        $this->waitForPageToLoad( );      
        
        $firstName = 'Ma'.substr( sha1( rand( ) ), 0, 4 );
        $lastName  = 'An'.substr( sha1( rand( ) ), 0, 7 );
        
        //Go to online membership signup page
        $this->open( $this->sboxPath . "civicrm/contribute/transact?reset=1&id=2" );
        $this->waitForElementPresent( "_qf_Main_upload-bottom" );
        
        //Type first name and last name
        $this->type( "first_name", $firstName );
        $this->type( "last_name",$lastName );
        
        //Credit Card Info
        $this->select( "credit_card_type", "value=Visa" );
        $this->select( "credit_card_type", "label=Visa" );
        $this->type( "credit_card_number", "4111111111111111" );
        $this->type( "cvv2", "000" );
        $this->select( "credit_card_exp_date[M]", "value=1" );
        $this->select( "credit_card_exp_date[Y]", "value=2020" );
        
        //Billing Info
        $this->type( "billing_first_name", $firstName."billing" );
        $this->type( "billing_last_name", $lastName."billing" );
        $this->type( "billing_street_address-5", "15 Main St." );
        $this->type( " billing_city-5", "San Jose" );
        $this->select( "billing_country_id-5", "value=1228" );
        $this->select( "billing_state_province_id-5", "value=1004" );
        $this->type( "billing_postal_code-5", "94129" ); 
        
        $this->click( "_qf_Main_upload-bottom" );
        
        $this->waitForPageToLoad( '30000' );
        $this->waitForElementPresent( "_qf_Confirm_next-bottom" );
        
        $this->click( "_qf_Confirm_next-bottom" );
        $this->waitForPageToLoad( '30000' );
        
        //Find Member
        $this->open( $this->sboxPath . "civicrm/member/search&reset=1" );
        $this->waitForElementPresent( "member_end_date_high" );
        
        $this->type( "sort_name", "$firstName $lastName" );
        $this->click( "_qf_Search_refresh" );
        $this->waitForPageToLoad( '30000' );
        
        $this->waitForElementPresent( 'css=#memberSearch table tbody tr td span a.action-item-first' );
        $this->click( 'css=#memberSearch table tbody tr td span a.action-item-first' );
        $this->waitForElementPresent( "_qf_MembershipView_cancel-bottom" );
        
        //View Membership Record
        $verifyMembershipData =  array(
                                       'Member'         => $firstName.' '.$lastName,
                                       'Membership Type'=> 'General',
                                       'Status'         => 'New',
                                       'Source'         => 'Online Contribution: Member Signup and Renewal',
                                       );
        foreach ( $verifyMembershipData as $label => $value ) {
            $this->verifyText( "xpath=//form[@id='MembershipView']//table/tbody/tr/td[text()='{$label}']/following-sibling::td", 
                               preg_quote( $value ) );   
        }
        $this->open( $this->sboxPath . "civicrm/contribute/transact?reset=1&id=2" );
        $this->waitForElementPresent( "_qf_Main_upload-bottom" );
        
        //Credit Card Info
        $this->select( "credit_card_type", "value=Visa" );
        $this->select( "credit_card_type", "label=Visa" );
        $this->type( "credit_card_number", "4111111111111111" );
        $this->type( "cvv2", "000" );
        $this->select( "credit_card_exp_date[M]", "value=1" );
        $this->select( "credit_card_exp_date[Y]", "value=2020" );
        $this->click( "_qf_Main_upload-bottom" );
        
        $this->waitForPageToLoad( '30000' );
        $this->waitForElementPresent( "_qf_Confirm_next-bottom" );
        $this->click( "_qf_Confirm_next-bottom" );
        $this->waitForPageToLoad( '30000' );
        
        //Find Member
        $this->open( $this->sboxPath . "civicrm/member/search&reset=1" );
        $this->waitForElementPresent( "member_end_date_high" );
        
        $this->type( "sort_name", "$firstName $lastName" );
        $this->click( "_qf_Search_refresh" );
        $this->waitForPageToLoad( '30000' );
        
        $this->waitForElementPresent( 'css=#memberSearch table tbody tr td span a.action-item-first' );
        $this->click( 'css=#memberSearch table tbody tr td span a.action-item-first' );
        $this->waitForElementPresent( "_qf_MembershipView_cancel-bottom" );
        
        //View Membership Record
        $verifyMembershipData =  array(
                                       'Member'         => $firstName.' '.$lastName,
                                       'Membership Type'=> 'General',
                                       'Status'         => 'New',
                                       'Source'         => 'Online Contribution: Member Signup and Renewal',
                                       );
        foreach ( $verifyMembershipData as $label => $value ) {
            $this->verifyText( "xpath=//form[@id='MembershipView']//table/tbody/tr/td[text()='{$label}']/following-sibling::td", 
                               preg_quote( $value ) );   
        }
    }
    
    function testOnlineMembershipRenewChangeType( ) {
        // a random 7-char string and an even number to make this pass unique
        $hash = substr( sha1( rand( ) ), 0, 7 );
        $rand = 2 * rand( 2, 50 );
        
        // This is the path where our testing install resides. 
        // The rest of URL is defined in CiviSeleniumTestCase base class, in
        // class attributes.
        $this->open( $this->sboxPath );
        
        // Log in using webtestLogin() method
        $this->webtestLogin( );
        
        // We need a payment processor
        $processorName = "Webtest Dummy" . substr( sha1( rand( ) ), 0, 7 );
        $this->webtestAddPaymentProcessor( $processorName );
        
        $this->open( $this->sboxPath . "civicrm/admin/contribute/amount?reset=1&action=update&id=2" );
        
        //this contribution page for membership signup
        $this->waitForElementPresent( 'payment_processor_id' );
        $this->select( "payment_processor_id", "label=" . $processorName );
        
        // save
        $this->click( '_qf_Amount_next' );
        $this->waitForPageToLoad( );
        
        // go to Profiles
        $this->click( 'css=#tab_custom a' );
        
        // fill in Profiles
        $this->waitForElementPresent( 'custom_pre_id' );
        $this->select( 'custom_pre_id', 'value=1' );
        
        // save
        $this->click( '_qf_Custom_upload_done' );
        $this->waitForPageToLoad( );      
        
        $firstName = 'Ma'.substr( sha1( rand( ) ), 0, 4 );
        $lastName  = 'An'.substr( sha1( rand( ) ), 0, 7 );
        
        //Go to online membership signup page
        $this->open( $this->sboxPath . "civicrm/contribute/transact?reset=1&id=2" );
        $this->waitForElementPresent( "_qf_Main_upload-bottom" );
        
        //Type first name and last name
        $this->type( "first_name", $firstName );
        $this->type( "last_name", $lastName );
        
        //Credit Card Info
        $this->select( "credit_card_type", "value=Visa" );
        $this->select( "credit_card_type", "label=Visa" );
        $this->type( "credit_card_number", "4111111111111111" );
        $this->type( "cvv2", "000" );
        $this->select( "credit_card_exp_date[M]", "value=1" );
        $this->select( "credit_card_exp_date[Y]", "value=2020" );
        
        //Billing Info
        $this->type( "billing_first_name", $firstName."billing" );
        $this->type( "billing_last_name", $lastName."billing" );
        $this->type( "billing_street_address-5", "15 Main St." );
        $this->type( " billing_city-5", "San Jose" );
        $this->select( "billing_country_id-5", "value=1228" );
        $this->select( "billing_state_province_id-5", "value=1004" );
        $this->type( "billing_postal_code-5", "94129" ); 
        
        $this->click( "_qf_Main_upload-bottom" );
        
        $this->waitForPageToLoad( '30000' );
        $this->waitForElementPresent( "_qf_Confirm_next-bottom" );
        
        $this->click( "_qf_Confirm_next-bottom" );
        $this->waitForPageToLoad( '30000' );
        
        //Find Member
        $this->open( $this->sboxPath . "civicrm/member/search&reset=1" );
        $this->waitForElementPresent( "member_end_date_high" );
        
        $this->type( "sort_name", "$firstName $lastName" );
        $this->click( "_qf_Search_refresh" );
        $this->waitForPageToLoad( '30000' );
        
        $this->waitForElementPresent( 'css=#memberSearch table tbody tr td span a.action-item-first' );
        $this->click( 'css=#memberSearch table tbody tr td span a.action-item-first' );
        $this->waitForElementPresent( "_qf_MembershipView_cancel-bottom" );
        
        $matches = array();
        preg_match('/id=([0-9]+)/', $this->getLocation(), $matches);
        $membershipCreatedId = $matches[1];
       
        $memberSince = date('F jS, Y');
        
        //View Membership Record
        $verifyMembershipData =  array(
                                       'Member'         => $firstName.' '.$lastName,
                                       'Membership Type'=> 'General',
                                       'Status'         => 'New',
                                       'Source'         => 'Online Contribution: Member Signup and Renewal',
                                       );
        foreach ( $verifyMembershipData as $label => $value ) {
            $this->verifyText( "xpath=//form[@id='MembershipView']//table/tbody/tr/td[text()='{$label}']/following-sibling::td", 
                               preg_quote( $value ) );   
        }
        $this->open( $this->sboxPath . "civicrm/contribute/transact?reset=1&id=2" );
        $this->waitForElementPresent( "_qf_Main_upload-bottom" );
        
        $this->click( "CIVICRM_QFID_2_4");
        
        //Credit Card Info
        $this->select( "credit_card_type", "value=Visa" );
        $this->select( "credit_card_type", "label=Visa" );
        $this->type( "credit_card_number", "4111111111111111" );
        $this->type( "cvv2", "000" );
        $this->select( "credit_card_exp_date[M]", "value=1" );
        $this->select( "credit_card_exp_date[Y]", "value=2020" );
        $this->click( "_qf_Main_upload-bottom" );
        
        $this->waitForPageToLoad( '30000' );
        $this->waitForElementPresent( "_qf_Confirm_next-bottom" );
        $this->click( "_qf_Confirm_next-bottom" );
        $this->waitForPageToLoad( '30000' );
        
        //Find Member
        $this->open( $this->sboxPath . "civicrm/member/search&reset=1" );
        $this->waitForElementPresent( "member_end_date_high" );
        
        $this->type( "sort_name", "$firstName $lastName" );
        $this->click( "_qf_Search_refresh" );
        $this->waitForPageToLoad( '30000' );
        
        $this->waitForElementPresent( 'css=#memberSearch table tbody tr td span a.action-item-first' );
        $this->click( 'css=#memberSearch table tbody tr td span a.action-item-first' );
        $this->waitForElementPresent( "_qf_MembershipView_cancel-bottom" );
        
        $matches = array( );
        preg_match( '/id=([0-9]+)/', $this->getLocation( ), $matches );
        $membershipRenewedId = $matches[1];
        
        //View Membership Record
        $verifyMembershipData =  array(
                                       'Member'         => $firstName.' '.$lastName,
                                       'Membership Type'=> 'Student',
                                       'Status'         => 'New',
                                       'Source'         => 'Online Contribution: Member Signup and Renewal',
                                       'Member Since'   => $memberSince
                                       );
        foreach ( $verifyMembershipData as $label => $value ) {
            $this->verifyText( "xpath=//form[@id='MembershipView']//table/tbody/tr/td[text()='{$label}']/following-sibling::td", 
                               preg_quote( $value ) );   
        }
        $this->assertEquals( $membershipCreatedId, $membershipRenewedId );
    }
}