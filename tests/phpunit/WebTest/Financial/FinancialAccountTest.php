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

class WebTest_Financial_FinancialAccountTest extends CiviSeleniumTestCase {

    function testAddFinancialAccount( ) 
    {
        
        // To Add Financial Account 
        // class attributes.
        $this->open( $this->sboxPath );
        
        // Log in using webtestLogin() method
        $this->webtestLogin();
        $firstName = substr(sha1(rand()), 0, 7);
        $this->webtestAddOrganization( $firstName );
        // Go directly to the URL
        $this->open( $this->sboxPath . "civicrm/admin/financial/financialAccount?reset=1" );
        $this->waitForPageToLoad("30000");
        $this->click( "link=Add Contribution Type" );
        $this->waitForElementPresent( '_qf_FinancialAccount_cancel-botttom' );
        
        // Financial Account Name
        $this->type( 'name', "Financial Account $title" );
        //Accounting Code
        $this->type( 'accounting_code', '1033' );
        // Autofill Organization
        $this->webtestOrganisationAutocomplete( $firstName );
         // Autofill Parent Financial Account Name
        $this->type("parent_financial_account", 'Donation');
        $this->click("parent_financial_account");
        $this->waitForElementPresent("xpath=//body/div[8]/div/ul/li");
        $this->click("xpath=//body/div[8]/div/ul/li");
        // Financial Account Type     
        $this->select( 'financial_account_type_id', 'value=4' );
        // Is Tax
        $this->click( 'is_tax', 'value=1' );
        // Tax Rate
        $this->type( 'tax_rate', '10' );
        // Is Header Account
        $this->click( 'is_header_account', 'value=1' );
        // Set Default
        $this->click( 'is_default', 'value=1' );
        $this->click( '_qf_FinancialAccount_next-botttom' );
        $this->waitForElementPresent( 'link=Add Contribution Type' );
       
    }
    
    
    function testEditFinancialAccount( ) 
    {
        // To Edit Financial Account 
        // class attributes.
        $this->open( $this->sboxPath );
         
        // Log in using webtestLogin() method
        $this->webtestLogin();
         
        // Go directly to the URL
        $this->open( $this->sboxPath . "civicrm/admin/financial/financialAccount?reset=1" );
        $this->waitForPageToLoad("30000");
        $this->waitForElementPresent( "xpath=//table//tbody/tr[5]/td[7]/span/a[text()='Edit']" );
        $this->click( "xpath=//table//tbody/tr[5]/td[7]/span/a[text()='Edit']" );

        $this->waitForElementPresent( '_qf_FinancialAccount_cancel-botttom' );
        // Change Financial Account Name
        $this->type( 'name', "Financial Account Edited" );
        // Autofill Edit Organization
        $this->webtestOrganisationAutocomplete( 'com' );
        // Autofill Edit Financial Account Name
        $this->type("parent_financial_account", 'Mem');
        $this->click("parent_financial_account");
        $this->waitForElementPresent("xpath=//body/div[8]/div/ul/li");
        $this->click("xpath=//body/div[8]/div/ul/li");   
        // Financial Account Type  
        $this->select( 'financial_account_type_id', 'value=3' );
        // Is Tax
        $this->click( 'is_tax', 'value=1' );
        // Tax Rate
        $this->type( 'tax_rate', '' );
        $this->click( '_qf_FinancialAccount_next-botttom' );
        $this->waitForElementPresent( 'link=Add Contribution Type' );
      
    }

    function testDeleteFinancialAccount( ) 
    {
       
        // To Delete Financial Account 
        // class attributes.
        $this->open( $this->sboxPath );
        
        // Log in using webtestLogin() method
        $this->webtestLogin();
        
        // Go directly to the URL
        $this->open( $this->sboxPath . "civicrm/admin/financial/financialAccount?reset=1" );
        $this->waitForPageToLoad("30000");

        $this->waitForElementPresent( "xpath=//table//tbody/tr[5]/td[7]/span/a[text()='Delete']" );
        $this->click( "xpath=//table//tbody/tr[5]/td[7]/span/a[text()='Delete']" );
        $this->waitForElementPresent( '_qf_FinancialAccount_cancel-botttom' );

        $this->click( '_qf_FinancialAccount_next-botttom' );
        $this->waitForElementPresent( 'link=Add Contribution Type' );
      
    }
}
