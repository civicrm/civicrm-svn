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

class WebTest_Financial_FinancialAccountTypeTest extends CiviSeleniumTestCase {

    function testFinancialAccount( ) 
    {
        // To Add Financial Account 
        // class attributes.
        $this->open( $this->sboxPath );
        
        // Log in using webtestLogin() method
        $this->webtestLogin();
        
        // Add new Financial Account
        $orgName = 'Alberta '.substr(sha1(rand()), 0, 7);
        $financialAccountTitle = 'Financial Account '.substr(sha1(rand()), 0, 4);
        $financialAccountDescription = "{$financialAccountTitle} Description";
        $accountingCode = 1033;
        $financialAccountType = 'Expenses';
        $parentFinancialAccount = 'Donation';
        $taxDeductible = FALSE;
        $isActive = FALSE;
        $headerAccount = TRUE;
        $isTax = TRUE;
        $taxRate = 10;
        $isDefault = FALSE;
        
        //Add new organisation
        if( $orgName )
            $this->webtestAddOrganization( $orgName );
        
        $this->_testAddFinancialAccount( $financialAccountTitle,
                                         $financialAccountDescription,
                                         $accountingCode,
                                         $orgName,
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
                             'organisation_name' => $orgName,
                             'parent_financial_account' => $parentFinancialAccount,
                             'tax_rate'   => $taxRate,
                             'is_tax' => 'on',
                             'is_deductible' => 'off',
                             'is_header_account' => 'on',
                             'is_default' => 'off');
      
        $this->_assertFinancialAccount( $verifyData );
        $verifySelectFieldData = array( 'financial_account_type_id'   => $financialAccountType,
                                        );
        $this->_assertSelectVerify( $verifySelectFieldData );
        $this->click( '_qf_FinancialAccount_cancel-botttom' );
        $this->waitForPageToLoad('30000');

        //Add new Financial Type
        $financialType['name'] = 'FinancialType '.substr(sha1(rand()), 0, 4);
        $financialType['is_deductible'] = true;
        $financialType['is_reserved'] = false;
        $this->addFinancialType( $financialType );
        $this->waitForElementPresent( '_qf_FinancialTypeAccount_next_new' );
        $text = "The financial type '{$financialType['name']}' has been added. You can add Financial Accounts to this Financial Type now.";
        $this->assertTrue( $this->isTextPresent($text), 'Missing text: ' . $text );
        $accountRelationship = "Income Account is";
        $expected[] = array( 'financial_account'     => $financialAccountTitle, 
                             'account_relationship'  => $accountRelationship );

        
        $this->select( 'account_relationship', "label={$accountRelationship}" );
        $this->select( 'financial_account_id', "label={$financialAccountTitle}" );
        $this->click( '_qf_FinancialTypeAccount_next_new' );
        $this->waitForPageToLoad('30000');
        $text = 'The financial type Account has been saved. You can add another Financial Account Type.';
        $this->assertTrue( $this->isTextPresent($text), 'Missing text: ' . $text );

        $expected[] = array( 'financial_account'     => 'Member Dues', 
                             'account_relationship'  => $accountRelationship );

        $this->select( 'account_relationship', "label={$accountRelationship}" );
        $this->select( 'financial_account_id', "label=Member Dues" );
        $this->click( '_qf_FinancialTypeAccount_next' );
        $this->waitForElementPresent( 'newfinancialTypeAccount' );
        $text = 'The financial type Account has been saved.';
        $this->assertTrue( $this->isTextPresent($text), 'Missing text: ' . $text );
        
        foreach ( $expected as  $value => $label ) {
            $this->verifyText("xpath=id('ltype')/div/table/tbody/tr/td[1][text()='$label[financial_account]']/../td[2]", preg_quote($label['account_relationship']));
        }
        $this->open($this->sboxPath . 'civicrm/admin/financial/financialType?reset=1');
        $this->waitForElementPresent( 'newFinancialType' );
        $this->verifyText("xpath=id('ltype')/div/table/tbody/tr/td[1][text()='$financialType[name]']/../td[3]", $financialAccountTitle.',Member Dues' );
        $this->click("xpath=id('ltype')/div/table/tbody/tr/td[1][text()='$financialType[name]']/../td[7]/span/a[text()='Accounts']" );
        $this->waitForElementPresent( 'newfinancialTypeAccount' );
        $this->click("xpath=id('ltype')/div/table/tbody/tr/td[1][text()='Member Dues']/../td[7]/span/a[text()='Edit']");
        $this->waitForElementPresent( '_qf_FinancialTypeAccount_next' );
        $this->select( 'account_relationship', "label=AR Account is" );
        $this->select( 'financial_account_id', "label=Event Fee" );
        $this->click( '_qf_FinancialTypeAccount_next' );
        $this->waitForElementPresent("xpath=id('ltype')/div/table/tbody/tr/td[1][text()='Event Fee']/../td[7]/span/a[text()='Edit']");
        $this->verifyText("xpath=id('ltype')/div/table/tbody/tr/td[1][text()='Event Fee']/../td[2]", preg_quote('AR Account is'));
        $this->click("xpath=id('ltype')/div/table/tbody/tr/td[1][text()='Event Fee']/../td[7]/span/a[text()='Delete']"); 
        $this->waitForElementPresent( '_qf_FinancialTypeAccount_next-botttom' );
        $this->click( '_qf_FinancialTypeAccount_next-botttom' );
         
        $this->waitForPageToLoad('30000');
        $this->assertTrue( $this->isTextPresent('Selected financial type account has been deleted.'), 'Missing text: ' . 'Selected financial type account has been deleted.' );
    }



}
