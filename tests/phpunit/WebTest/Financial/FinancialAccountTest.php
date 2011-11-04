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

    function testFinancialAccount( ) 
    {
        // To Add Financial Account 
        // class attributes.
        $this->open( $this->sboxPath );
        
        // Log in using webtestLogin() method
        $this->webtestLogin();
        
        // Add new Financial Account
        $firstName = 'Alberta '.substr(sha1(rand()), 0, 7);
        $financialAccountTitle = 'Financial Account '.substr(sha1(rand()), 0, 4);
        $financialAccountDescription = "{$financialAccountTitle} Description";
        $accountingCode = 1033;
        $financialAccountType = 5;
        $parentFinancialAccount = 'Donation';
        $taxDeductible = FALSE;
        $isActive = TRUE;
        $headerAccount = TRUE;
        $isTax = TRUE;
        $taxRate = 10;
        $isDefault = FALSE;
        
        //Add new organisation
        if( $firstName )
            $this->webtestAddOrganization( $firstName );
        
        $this->_testAddFinancialAccount( $financialAccountTitle,
                                         $financialAccountDescription,
                                         $accountingCode,
                                         $firstName,
                                         $parentFinancialAccount,
                                         $financialAccountType,
                                         $taxDeductible,
                                         $isActive,
                                         $headerAccount,
                                         $isTax,
                                         $taxRate,
                                         $isDefault
                                         );
        
        $this->waitForElementPresent( "xpath=//table/tbody//tr/td[1][text()='{$financialAccountTitle}']/../td[7]/span/a[text()='Edit']" );
        
        $this->click( "xpath=//table/tbody//tr/td[1][text()='{$financialAccountTitle}']/../td[7]/span/a[text()='Edit']" );
        $this->waitForElementPresent( '_qf_FinancialAccount_cancel-botttom' );
        sleep(2);
        
        //Varify Data after Adding new Financial Account
        $verifyData = array( 'name' => $financialAccountTitle,
                             'description' => $financialAccountDescription,
                             'accounting_code' => $accountingCode,
                             'organisation_name' => $firstName,
                             'financial_account_type_id'   => $financialAccountType,
                             'parent_financial_account' => $parentFinancialAccount,
                             'tax_rate'   => $taxRate,
                             'is_tax' => 'on',
                             'is_deductible' => 'off',
                             'is_header_account' => 'on',
                             'is_default' => 'off');
      
        $this->_assertFinancialAccount( $verifyData );
        
        $this->click( '_qf_FinancialAccount_cancel-botttom' );
        
        //Edit Financial Account
        $editfinancialAccount = $financialAccountTitle;
        $financialAccountTitle .= ' Edited';
        $firstName = FALSE;
        $parentFinancialAccount = 'Member Dues';
        $financialAccountType = 3;
        // $financialAccountDescription = FALSE;
        // $accountingCode = FALSE;
        // $parentFinancialAccount = FALSE;
        // $financialAccountType = FALSE;
        // $taxDeductible = FALSE;
        // $isActive = TRUE;
        // $headerAccount = FALSE;
        // $isTax = FALSE;
        // $taxRate = FALSE;
        // $isDefault = FALSE;
        
        if ( $firstName ) {
            $firstName = 'NGO '.substr(sha1(rand()), 0, 7);
            $this->webtestAddOrganization( $firstName );
        }
        
        $this->_testEditFinancialAccount( $editfinancialAccount,
                                          $financialAccountTitle,
                                          $financialAccountDescription,
                                          $accountingCode,
                                          $firstName,
                                          $parentFinancialAccount,
                                          $financialAccountType,
                                          $taxDeductible,
                                          $isActive,
                                          $headerAccount,
                                          $isTax,
                                          $taxRate,
                                          $isDefault
                                          ); 

        $this->waitForElementPresent( "xpath=//table/tbody//tr/td[1][text()='{$financialAccountTitle}']/../td[7]/span/a[text()='Edit']" );
        $this->click( "xpath=//table/tbody//tr/td[1][text()='{$financialAccountTitle}']/../td[7]/span/a[text()='Edit']" );
        $this->waitForElementPresent( '_qf_FinancialAccount_cancel-botttom' );
        sleep(2);
       
        $verifyData = array( 'name' => $financialAccountTitle,
                             'description' => $financialAccountDescription,
                             'accounting_code' => $accountingCode,
                             'organisation_name' => $firstName,
                             'financial_account_type_id'   => $financialAccountType,
                             'parent_financial_account' => $parentFinancialAccount,
                             'tax_rate'   => $taxRate,
                             'is_tax' => 'on',
                             'is_deductible' => 'off',
                             'is_header_account' => 'on',
                             'is_default' => 'off');

        $this->click( '_qf_FinancialAccount_cancel-botttom' );
        $this->waitForElementPresent( "xpath=//table/tbody//tr/td[1][text()='{$financialAccountTitle}']/../td[7]/span/a[text()='Delete']" );
        
        //Delete Financial Account
        $this->_testDeleteFinancialAccount( $financialAccountTitle );
    }

    /**
     * Add new Financial Account
     */
    
    function _testAddFinancialAccount( $financialAccountTitle,
                                       $financialAccountDescription = FALSE,
                                       $accountingCode = FALSE,
                                       $firstName = FALSE,
                                       $parentFinancialAccount = FALSE,
                                       $financialAccountType = FALSE,
                                       $taxDeductible = FALSE,
                                       $isActive = FALSE,
                                       $headerAccount = FALSE,
                                       $isTax = FALSE,
                                       $taxRate = FALSE,
                                       $isDefault = FALSE
                                       ){
       
        // Go directly to the URL
        $this->open( $this->sboxPath . "civicrm/admin/financial/financialAccount?reset=1" );
        $this->waitForPageToLoad("30000");
        
        $this->click( "link=Add Contribution Type" );
        $this->waitForElementPresent( '_qf_FinancialAccount_cancel-botttom' );
        
        // Financial Account Name
        $this->type( 'name', $financialAccountTitle );
        
        // Financial Description
        if( $financialAccountDescription )
            $this->type( 'description', $financialAccountDescription );

        //Accounting Code
        if( $accountingCode )
            $this->type( 'accounting_code', $accountingCode );
        
        // Autofill Organization
        if( $firstName )
            $this->webtestOrganisationAutocomplete( $firstName );
         
        // Autofill Parent Financial Account Name
        if( $parentFinancialAccount ){
            $this->type("parent_financial_account", $parentFinancialAccount );
            $this->click("parent_financial_account");
            if( $firstName ){
                $this->waitForElementPresent("xpath=//body/div[8]/div/ul/li");
                $this->click("xpath=//body/div[8]/div/ul/li");
            }
            else{
                $this->waitForElementPresent("css=div.ac_results-inner li");
                $this->click("css=div.ac_results-inner li");
            }
                
        }
         
        // Financial Account Type     
        if( $financialAccountType )
            $this->select( 'financial_account_type_id', "value={$financialAccountType}" );
        
        // Is Tax Deductible
        if( $taxDeductible )
            $this->check( 'is_deductible' );
        else
            $this->uncheck( 'is_deductible' ); 
        // Is Active
        if( !$isActive )
            $this->check( 'is_active' );
        else
            $this->uncheck( 'is_active' );
        // Is Tax
        if( $isTax )
            $this->check( 'is_tax' );
        else
            $this->uncheck( 'is_tax' );

        // Tax Rate
        if( $taxRate )
            $this->type( 'tax_rate', $taxRate );
         
        // Is Header Account
        if( $headerAccount )
            $this->check( 'is_header_account' );
        else
            $this->uncheck( 'is_header_account' );

        // Set Default
        if( $isDefault )
            $this->check( 'is_default' );
        else
            $this->uncheck( 'is_default' );
        $this->click( '_qf_FinancialAccount_next-botttom' ); 
    }
    
    /**
     * Verify data after ADD and EDIT
     */
    function _assertFinancialAccount( $verifyData ){
        foreach( $verifyData as $key => $expectedvalue ) {
            $actualvalue = $this->getValue( $key );
            $this->assertEquals( $expectedvalue, $actualvalue );
        }
        
    }


    /**
     * Edit Financial Account
     */
    
    function _testEditFinancialAccount ( $editfinancialAccount,
                                         $financialAccountTitle = FALSE,
                                         $financialAccountDescription = FALSE,
                                         $accountingCode = FALSE,
                                         $firstName = FALSE,
                                         $parentFinancialAccount = FALSE,
                                         $financialAccountType = FALSE,
                                         $taxDeductible = FALSE,
                                         $isActive = TRUE,
                                         $headerAccount = FALSE,
                                         $isTax = FALSE,
                                         $taxRate = FALSE,
                                         $isDefault = FALSE
                                         ){
        if( $firstName ){
            $this->open( $this->sboxPath . "civicrm/admin/financial/financialAccount?reset=1" );
            $this->waitForPageToLoad("30000");
        }
            
        $this->waitForElementPresent( "xpath=//table/tbody//tr/td[1][text()='{$editfinancialAccount}']/../td[7]/span/a[text()='Edit']" );
        $this->click( "xpath=//table/tbody//tr/td[1][text()='{$editfinancialAccount}']/../td[7]/span/a[text()='Edit']" );

        $this->waitForElementPresent( '_qf_FinancialAccount_cancel-botttom' );
        
        // Change Financial Account Name
        if( $financialAccountTitle )
            $this->type( 'name', $financialAccountTitle );  

        // Financial Description
        if( $financialAccountDescription )
            $this->type( 'description', $financialAccountDescription );

        //Accounting Code
        if( $accountingCode )
            $this->type( 'accounting_code', $accountingCode );
        

        // Autofill Edit Organization
        if( $firstName )
            $this->webtestOrganisationAutocomplete( $firstName );
        
        // Autofill Edit Financial Account Name
        if( $parentFinancialAccount ){
            $this->type("parent_financial_account", $parentFinancialAccount );
            $this->click("parent_financial_account");
            if( $firstName ){
                $this->waitForElementPresent("xpath=//body/div[8]/div/ul/li");
                $this->click("xpath=//body/div[8]/div/ul/li"); 
            }
            else{
                $this->waitForElementPresent("css=div.ac_results-inner li");
                $this->click("css=div.ac_results-inner li");
            }
        }
        
        // Financial Account Type  
        if( $financialAccountType )
            $this->select( 'financial_account_type_id', "value={$financialAccountType}" );
        
        // Is Tax Deductible
        if( $taxDeductible )
            $this->check( 'is_deductible' );
        else
            $this->uncheck( 'is_deductible' );

        // Is Tax
        if( $isTax )
            $this->check( 'is_tax' );
        else
            $this->uncheck( 'is_tax' );
        
        // Tax Rate
        if( $taxRate )
            $this->type( 'tax_rate', $taxRate );
        
        // Is Header Account
        if( $headerAccount )
            $this->check( 'is_header_account' );
        else
            $this->uncheck( 'is_header_account' );

        // Set Default
        if( $isDefault )
            $this->check( 'is_default' );
        else
            $this->uncheck( 'is_default' );
        
        // Is Active
        if( $isActive )
            $this->check( 'is_active' );
        else
            $this->check( 'is_active' );
        $this->click( '_qf_FinancialAccount_next-botttom' );
              
    }
    
    /**
     * Delete Financial Account
     */
    function _testDeleteFinancialAccount( $financialAccountTitle ) 
    {     
        $this->click( "xpath=//table/tbody//tr/td[1][text()='{$financialAccountTitle}']/../td[7]/span/a[text()='Delete']" );
        $this->waitForElementPresent( '_qf_FinancialAccount_next-botttom' );
        $this->click( '_qf_FinancialAccount_next-botttom' );
        $this->waitForElementPresent( 'link=Add Contribution Type' );
        $this->assertTrue($this->isTextPresent("Selected contribution type has been deleted."));
    }
}
