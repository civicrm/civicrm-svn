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

class WebTest_Financial_FinancialAccountInitialPayment extends CiviSeleniumTestCase {

 function testContributeOfflineWithPriceSet()
  {
    // This is the path where our testing install resides. 
    // The rest of URL is defined in CiviSeleniumTestCase base class, in
    // class attributes.
    $this->open( $this->sboxPath );
      
    // Log in using webtestLogin() method
    $this->webtestLogin();

    //add financial type of account type expense
    $financialType= $this->_testAddFinancialType();

    $setTitle = 'Conference Fees - '.substr(sha1(rand()), 0, 7);
    $usedFor = 'Contribution';
    $setHelp = 'Select your conference options.';
    $this->_testAddSet( $setTitle, $usedFor, $setHelp );
      
    // Get the price set id ($sid) by retrieving and parsing the URL of the New Price Field form
    // which is where we are after adding Price Set.
    $elements = $this->parseURL( );
    $sid = $elements['queryString']['sid'];
    // $this->assertType( 'numeric', $sid );
      
    $validStrings = array( );
    $fields = array( 'Full Conference'        => 'Text',
                     'Meal Choice'            => 'Select',
                     'Pre-conference Meetup?' => 'Radio',
                     'Evening Sessions'       => 'CheckBox',
                     );
    $this->_testAddPriceFields( $fields, $validateStrings, $financialType  );
      
    // load the Price Set Preview and check for expected values
    $this->_testVerifyPriceSet( $validateStrings, $sid );  

       
    // Add new Financial Account
    $orgName = 'Alberta '.substr(sha1(rand()), 0, 7);
    $financialAccountTitle = 'Financial Account '.substr(sha1(rand()), 0, 4);
    $financialAccountDescription = "{$financialAccountTitle} Description";
    $accountingCode = 1033;
    $financialAccountType = 'Asset';
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
    
    //Add new Financial Type
    $financialType = array();
    $financialType['name'] = 'FinancialAsset '.substr(sha1(rand()), 0, 4);
    $financialType['is_deductible'] = true;
    $financialType['is_reserved'] = false;
    $this->addeditFinancialType( $financialType , 'new');
    
    $accountRelationship = "Is Asset Account of"; //Is Asset Account of - Income Account is
    $expected[] = array( 'financial_account'     => $financialAccountTitle, 
                         'account_relationship'  => $accountRelationship );
    
    $this->select( 'account_relationship', "label={$accountRelationship}" );
    sleep(2);
    $this->select( 'financial_account_id', "label={$financialAccountTitle}" );
    $this->click( '_qf_FinancialTypeAccount_next' );
    $this->waitForPageToLoad('30000');
    $text = 'The financial type Account has been saved.';
    $this->assertTrue( $this->isTextPresent($text), 'Missing text: ' . $text );
    
    
    $this->open($this->sboxPath . 'civicrm/contribute/add?reset=1&action=add&context=standalone');
    
    // As mentioned before, waitForPageToLoad is not always reliable. Below, we're waiting for the submit
    // button at the end of this page to show up, to make sure it's fully loaded.
    $this->waitForElementPresent('_qf_Contribution_upload');

    // Let's start filling the form with values.
      
    // create new contact using dialog
    $firstName = substr(sha1(rand()), 0, 7);
    $this->webtestNewDialogContact( $firstName, 'Contributor', $firstName . '@example.com' );
      
    // select financial type
    //$this->select('financial_type_id', 'value=1');
      
    // fill in Received Date
    $this->webtestFillDate('receive_date');
      
    //select recieved into
    $this->select("financial_type_id", "label={$financialType['name']}");
    // source
    $this->type('source', 'Mailer 1');
      
    // select price set items
    $this->select('price_set_id', "label=$setTitle");
    $this->type("xpath=//input[@class='form-text four required']", "1");
    $this->click("xpath=//input[@class='form-radio']");
    $this->click("xpath=//input[@class='form-checkbox']");
         
    $this->click('submitPayment_Information');
    $this->click('int_amount');
    $this->click('initial_amount');
    $this->type('initial_amount', 50.00);
    $this->click("xpath=//tr[@id='adjust-option-items']/td/label[1]");

    // select payment instrument type = Check and enter chk number
    $this->select('payment_instrument_id', 'value=4');
    $this->waitForElementPresent('check_number');
    $this->type('check_number', 'check #1041');
    $this->type('trxn_id', 'P20901X1' . rand(100, 10000));
    //Additional Detail section
    sleep(4);
    $this->click('AdditionalDetail');
    $this->waitForElementPresent('thankyou_date');

    $this->type('note', 'This is a test note.');
    $this->type('non_deductible_amount', '10');
    $this->type('fee_amount', '0');
    $this->type('net_amount', '0');
    $this->type('invoice_id', time());
    $this->webtestFillDate('thankyou_date');
     
    // Clicking save.
    $this->click('_qf_Contribution_upload');
    $this->waitForPageToLoad('30000');
      
    // Is status message correct?
    //$this->assertTrue($this->isTextPresent('The contribution record has been saved.'), "Status message didn't show up after saving!");

    $this->waitForElementPresent( "xpath=//div[@id='Contributions']//table//tbody/tr[1]/td[8]/span/a[text()='View']" );
      
    //click through to the Membership view screen
    $this->click( "xpath=//div[@id='Contributions']//table/tbody/tr[1]/td[8]/span/a[text()='View']" );
    $this->waitForElementPresent('_qf_ContributionView_cancel-bottom');
   
  }

 function _testAddFinancialType(){
      // Add new Financial Account
      $orgName = 'Alberta '.substr(sha1(rand()), 0, 7);
      $financialAccountTitle = 'Financial Account '.substr(sha1(rand()), 0, 4);
      $financialAccountDescription = "{$financialAccountTitle} Description";
      $accountingCode = 1033;
      $financialAccountType = 'Revenue'; //Asset Revenue
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
      
      //Add new Financial Type
      $financialType['name'] = 'FinancialType '.substr(sha1(rand()), 0, 4);
      $financialType['is_deductible'] = true;
      $financialType['is_reserved'] = false; 
      $this->addeditFinancialType( $financialType );

      $accountRelationship = "Income Account is"; //Is Asset Account - of Income Account is
      $expected[] = array( 'financial_account'     => $financialAccountTitle, 
                           'account_relationship'  => $accountRelationship );

        
      $this->select( 'account_relationship', "label={$accountRelationship}" );
      sleep(2);
      $this->select( 'financial_account_id', "label={$financialAccountTitle}" );
      $this->click( '_qf_FinancialTypeAccount_next' );
      $this->waitForPageToLoad('30000');
      $text = 'The financial type Account has been saved.';
      $this->assertTrue( $this->isTextPresent($text), 'Missing text: ' . $text );
      return $financialType['name'];
      
  }
  
function _testVerifyPriceSet( $validateStrings, $sid )
  {
      // verify Price Set at Preview page
      // start at Manage Price Sets listing
      $this->open($this->sboxPath . 'civicrm/admin/price?reset=1');
      $this->waitForPageToLoad('30000');
      
      // Use the price set id ($sid) to pick the correct row
      $this->click("css=tr#row_{$sid} a[title='View and Edit Price Fields']");
      
      $this->waitForPageToLoad('30000');
      // Look for Register button
      $this->waitForElementPresent('Link=Add Price Field');
      // Check for expected price set field strings
      $this->assertStringsPresent( $validateStrings );
  }
  
 function _testAddPriceFields( &$fields, &$validateString, $financialType, $dateSpecificFields = false )
  {
      $validateStrings [] = $financialType;
      foreach ( $fields as $label => $type ) {
          $validateStrings[] = $label;
          
          $this->type('label', $label);
          $this->select('html_type', "value={$type}");

          switch ($type) {
             case 'Text':
                $validateStrings[] = '525.00';
                $this->type('price', '525.00');
                if ( $dateSpecificFields == true ) {
                    $this->webtestFillDateTime('active_on', '+1 week');
                } else {
                    $this->check('is_required');
                }
                break;
             case 'Select':
                $options = array( 1 => array( 'label'  => 'Chicken',
                                              'amount' => '30.00' ),
                                  2 => array( 'label'  => 'Vegetarian', 
                                              'amount' => '25.00' ) );
                $this->addMultipleChoiceOptions( $options, $validateStrings );
                if ( $dateSpecificFields == true ) {
                    $this->webtestFillDateTime('expire_on', '-1 week');
                }
                break;
             case 'Radio':
                $options = array( 1 => array( 'label'  => 'Yes',
                                              'amount' => '50.00' ),
                                  2 => array( 'label'  => 'No', 
                                              'amount' => '0' ) );
                $this->addMultipleChoiceOptions( $options, $validateStrings );
                $this->check('is_required');
                if ( $dateSpecificFields == true ) {
                    $this->webtestFillDateTime('active_on', '-1 week');
                }
                break;
             case 'CheckBox':
                $options = array( 1 => array( 'label' => 'First Night',
                                              'amount' => '15.00' ),
                                  2 => array( 'label' => 'Second Night', 
                                              'amount' => '15.00' ) );
                $this->addMultipleChoiceOptions( $options, $validateStrings );
                if ( $dateSpecificFields == true ) {
                    $this->webtestFillDateTime('expire_on', '+1 week');
                }
                break;
             default:
                break;
          }
          $this->select( 'financial_type_id', "label={$financialType}" );
          $this->click('_qf_Field_next_new-bottom');
          $this->waitForPageToLoad('30000');
          $this->waitForElementPresent('_qf_Field_next-bottom');
      }
  }
  
 function _testAddPriceFieldsEvent( &$fields, &$validateString, $financialType, $dateSpecificFields = false )
  {
      $validateStrings [] = $financialType;
      foreach ( $fields as $label => $type ) {
          $validateStrings[] = $label;
          
          $this->type('label', $label);
          $this->select('html_type', "value={$type}");

          switch ($type) {
             case 'Text':
                $validateStrings[] = '525.00';
                $this->type('price', '525.00');
                if ( $dateSpecificFields == true ) {
                    $this->webtestFillDateTime('active_on', '+1 week');
                } else {
                    $this->check('is_required');
                }
                break;
             case 'Select':
                $options = array( 1 => array( 'label'  => 'Chicken',
                                              'amount' => '30.00' ,
                                              'financial_type_id' => $financialType ),
                                  2 => array( 'label'  => 'Vegetarian', 
                                              'amount' => '25.00' ,
                                              'financial_type_id' => $financialType ) );
                $this->addMultipleChoiceOptions( $options, $validateStrings );
                if ( $dateSpecificFields == true ) {
                    $this->webtestFillDateTime('expire_on', '-1 week');
                }
                break;
             case 'Radio':
                $options = array( 1 => array( 'label'  => 'Yes',
                                              'amount' => '50.00' ,
                                              'financial_type_id' => $financialType ),
                                  2 => array( 'label'  => 'No', 
                                              'amount' => '0' ,
                                              'financial_type_id' => $financialType ) );
                $this->addMultipleChoiceOptions( $options, $validateStrings );
                $this->check('is_required');
                if ( $dateSpecificFields == true ) {
                    $this->webtestFillDateTime('active_on', '-1 week');
                }
                break;
             case 'CheckBox':
                $options = array( 1 => array( 'label' => 'First Night',
                                              'amount' => '15.00' ,
                                              'financial_type_id' => $financialType ),
                                  2 => array( 'label' => 'Second Night', 
                                              'amount' => '15.00' ,
                                              'financial_type_id' => $financialType ) );
                $this->addMultipleChoiceOptions( $options, $validateStrings );
                if ( $dateSpecificFields == true ) {
                    $this->webtestFillDateTime('expire_on', '+1 week');
                }
                break;
             default:
                break;
          }
          //  $this->select( 'financial_type_id', "label={$financialType}" );
          $this->click('_qf_Field_next_new-bottom');
          $this->waitForPageToLoad('30000');
          $this->waitForElementPresent('_qf_Field_next-bottom');
      }
  }
  

 function _testAddPriceFieldsMem( &$fields, &$validateString, $dateSpecificFields = false, $title, $sid  )
  {
      $memTypeParams1 = $this->webtestAddMembershipType( );
      $memTypeTitle1  = $memTypeParams1['membership_type'];
      $memTypeId1     = explode( '&id=', $this->getAttribute( "xpath=//div[@id='membership_type']/div[2]/table/tbody//tr/td[text()='{$memTypeTitle1}']/../td[11]/span/a[1]@href" ) );
      $memTypeId1 = substr($memTypeId1[1],0,strpos($memTypeId1[1],'&'));

      $memTypeParams2 = $this->webtestAddMembershipType( );
      $memTypeTitle2  = $memTypeParams2['membership_type'];
      $memTypeId2     = explode( '&id=', $this->getAttribute( "xpath=//div[@id='membership_type']/div[2]/table/tbody//tr/td[text()='{$memTypeTitle2}']/../td[11]/span/a[1]@href" ) );
      $memTypeId2 = substr($memTypeId2[1],0,strpos($memTypeId2[1],'&'));
    
      $this->open( $this->sboxPath . "civicrm/admin/price/field?reset=1&action=add&sid={$sid}" );

      foreach ( $fields as $label => $type ) {
          $validateStrings[] = $label;
          
          $this->type('label', $label);
          $this->select('html_type', "value={$type}");
          
          switch ( $type ) {
          case 'Radio':
              $options = array( 1 => array( 'label'              => "$memTypeTitle1",
                                            'membership_type_id' => $memTypeId1,
                                            'amount'             => 100.00 ),
                                2 => array( 'label'              => "$memTypeTitle2", 
                                            'membership_type_id' => $memTypeId2,
                                            'amount'             => 50.00 ),
                                );
              $this->addMultipleChoiceOptions( $options, $validateStrings );
              break;

          case 'CheckBox':
              $options = array( 1 => array( 'label'              => "$memTypeTitle1",
                                            'membership_type_id' => $memTypeId1,
                                            'amount'             => 100.00 ),
                                2 => array( 'label'              => "$memTypeTitle2", 
                                            'membership_type_id' => $memTypeId2,
                                            'amount'             => 50.00 ),
                                );
              $this->addMultipleChoiceOptions( $options, $validateStrings );
              break;
              
          default:
              break;
          }
          $this->click( '_qf_Field_next_new-bottom' );
          $this->waitForPageToLoad( '30000' );
          $this->waitForElementPresent( '_qf_Field_next-bottom' );
          $this->assertTrue( $this->isTextPresent( "Price Field '{$label}' has been saved." ) );
      }
      return array( $memTypeTitle1, $memTypeTitle2 );
  }

 function _testAddSet( $setTitle, $usedFor, $setHelp, $financialType = null )
  {
      $this->open($this->sboxPath . 'civicrm/admin/price?reset=1&action=add');
      $this->waitForPageToLoad('30000');
      $this->waitForElementPresent('_qf_Set_next-bottom');

      // Enter Priceset fields (Title, Used For ...)
      $this->type('title', $setTitle);
      if ( $usedFor == 'Event' ){
          $this->check('extends[1]');
      } elseif ( $usedFor == 'Contribution') {
          $this->check('extends[2]');
      } elseif ( $usedFor == 'Membership') {
          $this->check('extends[3]');
          $this->waitForElementPresent( 'financial_type_id' );
          //select recieved into
          $this->select( "css=select.form-select", "label={$financialType}" );
          //  $this->select("financial_type_id", "label={$financialType}"); 
      }
      
      $this->type('help_pre', $setHelp);

      $this->assertChecked('is_active', 'Verify that Is Active checkbox is set.');
      $this->click('_qf_Set_next-bottom');      

      $this->waitForPageToLoad('30000');
      $this->waitForElementPresent('_qf_Field_next-bottom');
  }
  
 function testContributeOnlineWithPriceSet()
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
      $this->webtestLogin();
      
      //add financial type of account type expense
      $financialType= $this->_testAddFinancialType();

      $setTitle = 'Conference Fees - '.substr(sha1(rand()), 0, 7);
      $usedFor = 'Contribution';
      $setHelp = 'Select your conference options.';
      $this->_testAddSet( $setTitle, $usedFor, $setHelp );
      
      // Get the price set id ($sid) by retrieving and parsing the URL of the New Price Field form
      // which is where we are after adding Price Set.
      $elements = $this->parseURL( );
      $sid = $elements['queryString']['sid'];
      // $this->assertType( 'numeric', $sid );
      
      $validStrings = array( );
      $fields = array( 'Full Conference'        => 'Text',
                       'Meal Choice'            => 'Select',
                       'Pre-conference Meetup?' => 'Radio',
                       'Evening Sessions'       => 'CheckBox',
                       );
      //$this->_testAddPriceFields( $fields, $validateStrings, true );
      $this->_testAddPriceFields( $fields, $validateStrings, $financialType );
      
      // load the Price Set Preview and check for expected values
      $this->_testVerifyPriceSet( $validateStrings, $sid );      
      
      // Add new Financial Account
      $orgName = 'Alberta '.substr(sha1(rand()), 0, 7);
      $financialAccountTitle = 'Financial Account '.substr(sha1(rand()), 0, 4);
      $financialAccountDescription = "{$financialAccountTitle} Description";
      $accountingCode = 1033;
      $financialAccountType = 'Asset';
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
      
      //Add new Financial Type
      $financialType = array();
      $financialType['name'] = 'FinancialAsset '.substr(sha1(rand()), 0, 4);
      $financialType['is_deductible'] = true;
      $financialType['is_reserved'] = false;
      $this->addeditFinancialType( $financialType , 'new');
      
      $accountRelationship = "Is Asset Account of"; //Is Asset Account of - Income Account is
      $expected[] = array( 'financial_account'     => $financialAccountTitle, 
                           'account_relationship'  => $accountRelationship );
      
      $this->select( 'account_relationship', "label={$accountRelationship}" );
      sleep(2);
      $this->select( 'financial_account_id', "label={$financialAccountTitle}" );
      $this->click( '_qf_FinancialTypeAccount_next' );
      $this->waitForPageToLoad('30000');
      $text = 'The financial type Account has been saved.';
      $this->assertTrue( $this->isTextPresent($text), 'Missing text: ' . $text );
      
      // We need a payment processor
      $processorName = 'Webtest Dummy' . substr( sha1( rand( ) ), 0, 7 );
      $this->webtestAddPaymentProcessor( $processorName, '11', null, $financialType['name'] );
      
      $this->open( $this->sboxPath . 'civicrm/admin/contribute/add?reset=1&action=add' );
      $this->waitForPageToLoad( '30000' );
      $contributionTitle = substr( sha1( rand( ) ), 0, 7 );
      $rand = 2 * rand( 2, 50 );
        
      // fill in step 1 (Title and Settings)
      $contributionPageTitle = "Title $contributionTitle";
      $this->type( 'title', $contributionPageTitle );
      // $this->select( 'financial_type_id', 'value=1' );
      $this->fillRichTextField( 'intro_text','This is Test Introductory Message','CKEditor' );
      $this->fillRichTextField( 'footer_text','This is Test Footer Message','CKEditor' );
      
      // go to step 2
      $this->click( '_qf_Settings_next' );
      $this->waitForElementPresent( '_qf_Amount_next-bottom' );

      //this contribution page for online contribution 
      //$this->select( 'payment_processor_id', 'label=' . $processorName ); 
      $this->click( "xpath=//tr[@class='crm-contribution-contributionpage-amount-form-block-payment_processor']/td/label[text()='$processorName']" );
      $this->select( 'price_set_id', 'label=' . $setTitle );
      
      $this->click( 'is_pay_later' );
      $this->type( 'pay_later_receipt', 'I will send payment by check');
      sleep(2);
      $this->type('initial_amount_label' , 'Initial amount label');
      $this->type('initial_amount_help_text', 'Initial amount help text');
      $this->type('min_initial_amount', 10);
      
      $this->click( '_qf_Amount_next-bottom' );
      $this->waitForPageToLoad( '30000' );    

      //get Url for Live Contribution Page
      $registerUrl = $this->_testVerifyRegisterPage( $contributionPageTitle );
   
      //logout
      $this->open( $this->sboxPath . 'civicrm/logout?reset=1' );
      $this->waitForPageToLoad( '30000' );
      
      //Open Live Contribution Page
      $this->open( $this->sboxPath . $registerUrl );
      $this->waitForElementPresent( '_qf_Main_upload-bottom' );
      
      $firstName = 'Ma'.substr( sha1( rand( ) ), 0, 4 );
      $lastName  = 'An'.substr( sha1( rand( ) ), 0, 7 );
      $this->waitForElementPresent( '_qf_Main_upload-bottom' );
      $this->type( 'email-5', $firstName . '@example.com' );
      $this->type( 'billing_first_name', $firstName );
      $this->type( 'billing_last_name',$lastName );
      $this->type("xpath=//input[@class='form-text four required']", "1");
      $this->click("xpath=//input[@class='form-radio']");
      $this->click("xpath=//input[@class='form-checkbox']");
      $this->click("int_amount");
      $this->type("initial_amount",50);
      $streetAddress = '100 Main Street';
      $this->type( 'billing_street_address-5', $streetAddress );
      $this->type( 'billing_city-5', 'San Francisco' );
      $this->type( 'billing_postal_code-5', '94117' );
      $this->select( 'billing_country_id-5', 'value=1228' );
      $this->select( 'billing_state_province_id-5', 'value=1001' );
      
      //Credit Card Info
      $this->select( 'credit_card_type', 'value=Visa' );
      $this->type( 'credit_card_number', '4111111111111111' );
      $this->type( 'cvv2', '000' );
      $this->select( 'credit_card_exp_date[M]', 'value=1' );
      $this->select( 'credit_card_exp_date[Y]', 'value=2020' );
      
      //Billing Info
      $this->type( 'billing_first_name', $firstName.'billing' );
      $this->type( 'billing_last_name', $lastName.'billing'  );
      $this->type( 'billing_street_address-5', '15 Main St.' );
      $this->type( ' billing_city-5', 'San Jose' );
      $this->select( 'billing_country_id-5', 'value=1228' );
      $this->select( 'billing_state_province_id-5', 'value=1004' );
      $this->type( 'billing_postal_code-5', '94129' );  
      $this->click( '_qf_Main_upload-bottom' );
      
      $this->waitForPageToLoad( '30000' );
      $this->waitForElementPresent( '_qf_Confirm_next-bottom' );
      
      $this->click( '_qf_Confirm_next-bottom' );
      $this->waitForPageToLoad( '30000' );

      //login to check contribution
      $this->open( $this->sboxPath );
      
      // Log in using webtestLogin() method
      $this->webtestLogin( );
      
      //Find Contribution
      $this->open( $this->sboxPath . 'civicrm/contribute/search?reset=1' );
      
      $this->waitForElementPresent( 'contribution_date_low' );
      
      $this->type( 'sort_name', "$firstName $lastName" );
      $this->click( '_qf_Search_refresh' );
        
      $this->waitForPageToLoad( '30000' );
      
      $this->waitForElementPresent( "xpath=//div[@id='contributionSearch']//table//tbody/tr[1]/td[11]/span/a[text()='View']" );
      $this->click( "xpath=//div[@id='contributionSearch']//table//tbody/tr[1]/td[11]/span/a[text()='View']" );
      $this->waitForPageToLoad( '30000' );
      $this->waitForElementPresent( "_qf_ContributionView_cancel-bottom" );
      sleep(2);
      //View Contribution Record
      $expected = array(
                        'Financial Type'   => 'Donation', 
                        'Contribution Amount' => '590.00',
                        'Contribution Status' => 'Completed',
                        );
      foreach ( $expected as $label => $value ) {
          $this->verifyText("xpath=id('ContributionView')/div[2]/table[1]/tbody//tr/td[1][text()='$label']/../td[2]", preg_quote($value));
      }
  }

function _testVerifyRegisterPage( $contributionPageTitle )
  {
      $this->open( $this->sboxPath . 'civicrm/admin/contribute?reset=1' );
      $this->waitForElementPresent( '_qf_SearchContribution_refresh' );
      $this->type( 'title', $contributionPageTitle );
      $this->click( '_qf_SearchContribution_refresh' );
      $this->waitForPageToLoad( '50000' );
      $id = $this->getAttribute("//div[@id='configure_contribution_page']//div[@class='dataTables_wrapper']/table/tbody/tr@id");
      $id = explode( '_', $id );
      $registerUrl = "civicrm/contribute/transact?reset=1&id=$id[1]";
      return $registerUrl;
  }
  function _testVerifyEventRegisterPage( $registerStrings ){
      // Go to Register page and check for intro text and fee levels
      $this->click("link=Register Now");
      $this->waitForElementPresent("_qf_Register_upload-bottom");
      //$this->assertStringsPresent( $registerStrings );
      return $this->getLocation();
  }

function testAddPriceSet()
{
  // This is the path where our testing install resides. 
  // The rest of URL is defined in CiviSeleniumTestCase base class, in
  // class attributes.
  $this->open( $this->sboxPath );

  // Log in using webtestLogin() method
  $this->webtestLogin();


  // Add new Financial Account
  $orgName = 'Alberta '.substr(sha1(rand()), 0, 7);
  $financialAccountTitle = 'Financial Account '.substr(sha1(rand()), 0, 4);
  $financialAccountDescription = "{$financialAccountTitle} Description";
  $accountingCode = 1033;
  $financialAccountType = 'Asset';
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
    
  //Add new Financial Type
  $financialType = array();
  $financialType['name'] = 'FinancialAsset '.substr(sha1(rand()), 0, 4);
  $financialType['is_deductible'] = true;
  $financialType['is_reserved'] = false;
  $this->addeditFinancialType( $financialType , 'new');
    
  $accountRelationship = "Is Asset Account of"; //Is Asset Account of - Income Account is
  $expected[] = array( 'financial_account'     => $financialAccountTitle, 
                       'account_relationship'  => $accountRelationship );
    
  $this->select( 'account_relationship', "label={$accountRelationship}" );
  sleep(2);
  $this->select( 'financial_account_id', "label={$financialAccountTitle}" );
  $this->click( '_qf_FinancialTypeAccount_next' );
  $this->waitForPageToLoad('30000');
  $text = 'The financial type Account has been saved.';
  $this->assertTrue( $this->isTextPresent($text), 'Missing text: ' . $text );
    



  $title            = substr(sha1(rand()), 0, 7);
  $setTitle         = "Membership Fees - $title";
  $usedFor          = 'Membership';
  $contributionType = 'Donation';
  $setHelp          = 'Select your membership options.';
  $this->_testAddSet( $setTitle, $usedFor, $setHelp, $financialType['name']);

  // Get the price set id ($sid) by retrieving and parsing the URL of the New Price Field form
  // which is where we are after adding Price Set.
  $elements = $this->parseURL( );
  $sid = $elements['queryString']['sid'];
  // $this->assertType( 'numeric', $sid );

  $fields = array( "National Membership $title" => 'Radio',
                   "Local Chapter $title"       => 'CheckBox' );

  list( $memTypeTitle1, $memTypeTitle2 ) = $this->_testAddPriceFieldsMem( $fields, $validateStrings, null, $title, $sid );
  //var_dump($validateStrings);
  // load the Price Set Preview and check for expected values
  $this->_testVerifyPriceSet( $validateStrings, $sid );

  // Sign up for membership
  $firstName     = 'John_' . substr(sha1(rand()), 0, 7);
  $lastName      = 'Anderson_' . substr(sha1(rand()), 0, 7);
  $email         = "{$firstName}.{$lastName}@example.com";
  $contactParams = array( 'first_name' => $firstName,
                          'last_name'  => $lastName,
                          'email-5'    => $email );

  // Add a contact from the quick add block
  $this->webtestAddContact( $firstName, $lastName, $email );

  $this->_testSignUpOrRenewMembership( $sid, $contactParams, $memTypeTitle1, $memTypeTitle2 );
      
}
   
  function _testSignUpOrRenewMembership( $sid, $contactParams, $memTypeTitle1, $memTypeTitle2, $renew = false )
  {
      //build the membership dates.
      require_once 'CRM/Core/Config.php';
      require_once 'CRM/Utils/Array.php';
      require_once 'CRM/Utils/Date.php';
      $currentYear  = date( 'Y' );
      $currentMonth = date( 'm' );
      $previousDay  = date( 'd' ) - 1;
      $endYear      = ( $renew ) ? $currentYear + 2 : $currentYear + 1;
      $joinDate     = date('Y-m-d', mktime( 0, 0, 0, $currentMonth, date( 'd' ), $currentYear  ) );
      $startDate    = date('Y-m-d', mktime( 0, 0, 0, $currentMonth, date( 'd' ), $currentYear  ) );
      $endDate      = date('Y-m-d', mktime( 0, 0, 0, $currentMonth, $previousDay, $endYear ) );
      $configVars   = new CRM_Core_Config_Variables( );        
      foreach ( array( 'joinDate', 'startDate', 'endDate' ) as $date ) {
          $$date = CRM_Utils_Date::customFormat( $$date, $configVars->dateformatFull ); 
      }
      
      if ( !$renew ) {
          // Go directly to the URL of the screen that you will be testing (Activity Tab).
          $this->click( 'css=li#tab_member a' );
          $this->waitForElementPresent( 'link=Add Membership' );
          
          $this->click( 'link=Add Membership' );
          $this->waitForElementPresent( '_qf_Membership_cancel-bottom' );
          
          $this->select( 'price_set_id', "value={$sid}" );
          $this->waitForElementPresent( 'pricesetTotal' );
          $this->click( "xpath=//div[@id='priceset']/div[2]/div[2]/div/span/input" );
          $this->click( "xpath=//div[@id='priceset']/div[3]/div[2]/div[2]/span/input" );
         
          $this->click('int_amount');
          $this->click('initial_amount');
          $this->type('initial_amount', 50.00);
          $this->click("xpath=//tr[@id='adjust-option-items']/td/label[1]");
          
          $this->type( 'source', 'Offline membership Sign Up Test Text' );
          $this->click( '_qf_Membership_upload-bottom' );
      } else {
          $this->click( "xpath=//div[@id='memberships']/div/table/tbody//tr/td[text()='{$memTypeTitle1}']/../td[8]/span[2][text()='more ']/ul/li/a[text()='Renew']" );
          $this->waitForElementPresent( '_qf_MembershipRenewal_cancel-bottom' );
          $this->click( '_qf_MembershipRenewal_upload-bottom' );

          $this->waitForElementPresent( "xpath=//div[@id='memberships']/div/table/tbody/tr");
          $this->click( "xpath=//div[@id='memberships']/div/table/tbody//tr/td[text()='{$memTypeTitle2}']/../td[8]/span[2][text()='more ']/ul/li/a[text()='Renew']" );
          $this->waitForElementPresent( '_qf_MembershipRenewal_cancel-bottom' );
          $this->click( '_qf_MembershipRenewal_upload-bottom' );
      }
      $this->waitForElementPresent( "xpath=//div[@id='memberships']/div/table/tbody/tr");
      $this->click( "xpath=//div[@id='memberships']/div/table/tbody//tr/td[text()='{$memTypeTitle1}']/../td[8]/span/a[text()='View']" );
      $this->waitForElementPresent( "_qf_MembershipView_cancel-bottom" );
      //View Membership Record
      $verifyData = array( 'Membership Type' => "{$memTypeTitle1}",
                           'Status'          => 'New',
                           'Member Since'    => $joinDate,
                           'Start date'      => $startDate,
                           'End date'        => $endDate
                          );
      foreach ( $verifyData as $label => $value ) {
          $this->verifyText( "xpath=//form[@id='MembershipView']//table/tbody/tr/td[text()='{$label}']/following-sibling::td", 
                             preg_quote( $value ) );
      }

      $this->click( '_qf_MembershipView_cancel-bottom' );
      $this->waitForElementPresent( "xpath=//div[@id='memberships']/div/table/tbody/tr");
      $this->click( "xpath=//div[@id='memberships']/div/table/tbody//tr/td[text()='{$memTypeTitle2}']/../td[8]/span/a[text()='View']" );
      $this->waitForElementPresent( "_qf_MembershipView_cancel-bottom" );

      //View Membership Record
      $verifyData = array( 'Membership Type' => "{$memTypeTitle2}",
                           'Status'          => 'New',
                           'Member Since'    => $joinDate,
                           'Start date'      => $startDate,
                           'End date'        => $endDate
                          );
      foreach ( $verifyData as $label => $value ) {
          $this->verifyText( "xpath=//form[@id='MembershipView']//table/tbody/tr/td[text()='{$label}']/following-sibling::td", 
                             preg_quote( $value ) );
      }
      $this->click( "_qf_MembershipView_cancel-bottom" );
      $this->waitForElementPresent( "xpath=//div[@id='memberships']/div/table/tbody/tr");
  }

 function testOnlineEvent()
 {
   // This is the path where our testing install resides. 
   // The rest of URL is defined in CiviSeleniumTestCase base class, in
   // class attributes.
   $this->open( $this->sboxPath );

   // Log in using webtestLogin() method
   $this->webtestLogin();

   // Adding contact with randomized first name (so we can then select that contact when creating event registration)
   // We're using Quick Add block on the main page for this.
  
   //add financial type of account type expense
   $financialType= $this->_testAddFinancialType();

   $setTitle = 'Event Fees - '.substr(sha1(rand()), 0, 7);
   $usedFor = 'Event';
   $setHelp = 'Select your conference options.';
   $this->_testAddSet( $setTitle, $usedFor, $setHelp );
      
   // Get the price set id ($sid) by retrieving and parsing the URL of the New Price Field form
   // which is where we are after adding Price Set.
   $elements = $this->parseURL( );
   $sid = $elements['queryString']['sid'];
   // $this->assertType( 'numeric', $sid );
      
   $validStrings = array( );
   $fields = array( 'Full Conference'        => 'Text',
                    'Meal Choice'            => 'Select',
                    'Pre-conference Meetup?' => 'Radio',
                    'Evening Sessions'       => 'CheckBox',
                    );
   $this->_testAddPriceFieldsEvent( $fields, $validateStrings, $financialType  );
      
   // load the Price Set Preview and check for expected values
   $this->_testVerifyPriceSet( $validateStrings, $sid ); 
   
   // Add new Financial Account
   $orgName = 'Alberta '.substr(sha1(rand()), 0, 7);
   $financialAccountTitle = 'Financial Account '.substr(sha1(rand()), 0, 4);
   $financialAccountDescription = "{$financialAccountTitle} Description";
   $accountingCode = 1033;
   $financialAccountType = 'Asset';
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
      
   //Add new Financial Type
   $financialType = array();
   $financialType['name'] = 'FinancialAsset '.substr(sha1(rand()), 0, 4);
   $financialType['is_deductible'] = true;
   $financialType['is_reserved'] = false;
   $this->addeditFinancialType( $financialType , 'new');
      
   $accountRelationship = "Is Asset Account of"; //Is Asset Account of - Income Account is
   $expected[] = array( 'financial_account'     => $financialAccountTitle, 
                        'account_relationship'  => $accountRelationship );
      
   $this->select( 'account_relationship', "label={$accountRelationship}" );
   sleep(2);
   $this->select( 'financial_account_id', "label={$financialAccountTitle}" );
   $this->click( '_qf_FinancialTypeAccount_next' );
   $this->waitForPageToLoad('30000');
   $text = 'The financial type Account has been saved.';
   $this->assertTrue( $this->isTextPresent($text), 'Missing text: ' . $text );

   // We need a payment processor
   $processorName = "Webtest Dummy" . substr(sha1(rand()), 0, 7);
   $this->webtestAddPaymentProcessor($processorName, '11', null, $financialType['name'] );

   // Go directly to the URL of the screen that you will be testing (New Event).
   $this->open($this->sboxPath . "civicrm/event/add?reset=1&action=add");

   $eventTitle = 'My Conference - '.substr(sha1(rand()), 0, 7);
   $eventDescription = "Here is a description for this conference.";
   $this->_testAddEventInfo( $eventTitle, $eventDescription );

   $streetAddress = "100 Main Street";
   $this->_testAddLocation( $streetAddress );
      
   //  $this->_testAddReminder( $eventTitle );

   $this->_testAddFees( false, $setTitle, $processorName );
      
   // intro text for registration page
   $registerIntro = "Fill in all the fields below and click Continue.";
   $multipleRegistrations = true;
   $this->_testAddOnlineRegistration( $registerIntro, $multipleRegistrations );

   $eventInfoStrings = array( $eventTitle, $eventDescription, $streetAddress );
   $this->_testVerifyEventInfo( $eventTitle, $eventInfoStrings );
     
   $registerStrings = array("250.00","Member", "325.00", "Non-member", $registerIntro );
   $registerUrl = $this->_testVerifyEventRegisterPage( $registerStrings );
   
   $numberRegistrations = 0;
   $anonymous = true;
   $this->_testOnlineRegistration( $registerUrl, $numberRegistrations, $anonymous );

 }

 function _testOnlineRegistration( $registerUrl, $numberRegistrations=1, $anonymous=true ){
      if ( $anonymous ){
          $this->open($this->sboxPath . "civicrm/logout?reset=1");
          $this->waitForPageToLoad('30000');          
      }
      $this->open($registerUrl);
      $this->waitForPageToLoad('30000');
      $this->select("additional_participants", "value=");

      // $this->select( 'price_set_id', "value={$sid}" );
      $this->waitForElementPresent( 'pricesetTotal' );
           $this->type("xpath=//input[@class='form-text four required']", "1");
      $this->click("xpath=//input[@class='form-radio']");
      $this->click("xpath=//input[@class='form-checkbox']");



      $this->click('int_amount');
      $this->type('initial_amount', 50.00); 
      $this->type("email-Primary", "smith" . substr(sha1(rand()), 0, 7) . "@example.org" );

      $this->select("credit_card_type", "value=Visa");
      $this->type("credit_card_number", "4111111111111111");
      $this->type("cvv2", "000");
      $this->select("credit_card_exp_date[M]", "value=1");
      $this->select("credit_card_exp_date[Y]", "value=2020");
      $this->type("billing_first_name", "Jane");
      $this->type("billing_last_name", "Smith" . substr(sha1(rand()), 0, 7));
      $this->type("billing_street_address-5", "15 Main St.");
      $this->type(" billing_city-5", "San Jose");
      $this->select("billing_country_id-5", "value=1228");
      $this->select("billing_state_province_id-5", "value=1004");
      $this->type("billing_postal_code-5", "94129");
      
      $this->click("_qf_Register_upload-bottom");
      
      if ( $numberRegistrations > 1 ){
          for ($i = 1; $i <= $numberRegistrations; $i++){
              $this->waitForPageToLoad('30000');
              // Look for Skip button
              $this->waitForElementPresent("_qf_Participant_{$i}_next_skip-Array");
              $this->type("email-Primary", "smith" . substr(sha1(rand()), 0, 7) . "@example.org" );
              $this->click("_qf_Participant_{$i}_next");
          }
      }

      $this->waitForPageToLoad('30000');
      $this->waitForElementPresent("_qf_Confirm_next-bottom");
      $confirmStrings = array("Event Fee(s)", "Billing Name and Address", "Credit Card Information");
      $this->assertStringsPresent( $confirmStrings );
      $this->click("_qf_Confirm_next-bottom");
      $this->waitForPageToLoad('30000');
      $thankStrings = array("Thank You for Registering", "Event Total", "Transaction Date");
      $this->assertStringsPresent( $thankStrings );
  }

  function _testAddEventInfo( $eventTitle, $eventDescription ) {
      // As mentioned before, waitForPageToLoad is not always reliable. Below, we're waiting for the submit
      // button at the end of this page to show up, to make sure it's fully loaded.
      $this->waitForElementPresent("_qf_EventInfo_upload-bottom");

      // Let's start filling the form with values.
      $this->select("event_type_id", "value=1");
      
      // Attendee role s/b selected now.
      $this->select("default_role_id", "value=1");
      
      // Enter Event Title, Summary and Description
      $this->type("title", $eventTitle);
      $this->type("summary", "This is a great conference. Sign up now!");

      // Type description in ckEditor (fieldname, text to type, editor)
      $this->fillRichTextField( "description", $eventDescription,'CKEditor' );

      // Choose Start and End dates.
      // Using helper webtestFillDate function.
      $this->webtestFillDateTime("start_date", "+1 week");
      $this->webtestFillDateTime("end_date", "+1 week 1 day 8 hours ");

      $this->type("max_participants", "50");
      $this->click("is_map");
      $this->click("_qf_EventInfo_upload-bottom");      
  }


 function _testAddLocation( $streetAddress ) {
      // Wait for Location tab form to load
      $this->waitForPageToLoad("30000");
      $this->waitForElementPresent("_qf_Location_upload-bottom");

      // Fill in address fields
      $streetAddress = "100 Main Street";
      $this->type("address_1_street_address", $streetAddress);
      $this->type("address_1_city", "San Francisco");
      $this->type("address_1_postal_code", "94117");
      $this->select("address_1_state_province_id", "value=1004");
      $this->type("email_1_email", "info@civicrm.org");

      $this->click("_qf_Location_upload-bottom");      

      // Wait for "saved" status msg
      $this->waitForPageToLoad('30000');
      $this->waitForTextPresent("'Location' information has been saved.");
      
  }
  
  function _testAddFees( $discount=false, $priceSet=false, $processorName = "PP Pro" ){
      // Go to Fees tab
      $this->click("link=Fees");
      $this->waitForElementPresent("_qf_Fee_upload-bottom");
      $this->click("CIVICRM_QFID_1_2");
      $this->click( "xpath=//tr[@class='crm-event-manage-fee-form-block-payment_processor']/td[2]/label[text()='$processorName']" );
      //  $this->select("financial_type_id", "value=4");
      if ( $priceSet) {
          // get one - TBD
        $this->select("price_set_id", "label={$priceSet}");
      } else {
          $this->type("label_1", "Member");
          $this->type("value_1", "250.00");
          $this->type("label_2", "Non-member");
          $this->type("value_2", "325.00");          
      }

      if ( $discount ) {
          // enter early bird discounts TBD
      }
      
      $this->click("_qf_Fee_upload-bottom");      

      // Wait for "saved" status msg
      $this->waitForPageToLoad('30000');
      $this->waitForTextPresent("'Fee' information has been saved.");      
  }
  
 function _testAddOnlineRegistration($registerIntro, $multipleRegistrations = false){
      // Go to Online Registration tab
      $this->click("link=Online Registration");
      $this->waitForElementPresent("_qf_Registration_upload-bottom");

      $this->check("is_online_registration");
      $this->assertChecked("is_online_registration");
      if ( $multipleRegistrations ){
          $this->check("is_multiple_registrations");
          $this->assertChecked("is_multiple_registrations");
      }
      
      //$this->fillRichTextField("intro_text", $registerIntro);
      
      // enable confirmation email
      $this->click("CIVICRM_QFID_1_2");
      $this->type("confirm_from_name", "Jane Doe");
      $this->type("confirm_from_email", "jane.doe@example.org");

      $this->click("_qf_Registration_upload-bottom");
      $this->waitForPageToLoad("30000");
      $this->waitForTextPresent("'Registration' information has been saved.");
  }
  
 function _testVerifyEventInfo( $eventTitle, $eventInfoStrings ){
      // verify event input on info page
      // start at Manage Events listing
      $this->open($this->sboxPath . "civicrm/event/manage?reset=1");
      $this->click("link=$eventTitle");
      
      $this->waitForPageToLoad('30000');
      // Look for Register button
      $this->waitForElementPresent("link=Register Now");
      
      // Check for correct event info strings
      $this->assertStringsPresent( $eventInfoStrings );
  }



}