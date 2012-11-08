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

class WebTest_Financial_FinancialContribution extends CiviSeleniumTestCase {

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

    $this->waitForElementPresent( "xpath=//div[@id='ltype']//table/tbody//tr/td[1][text()='{$financialAccountTitle}']/../td[8]/span/a[text()='Edit']" );
    
    //Add new Financial Type
    $financialType = array();
    $financialType['name'] = 'FinancialAsset '.substr(sha1(rand()), 0, 4);
    $financialType['is_deductible'] = true;
    $financialType['is_reserved'] = false;
    $this->addeditFinancialType( $financialType , 'new');
    
    $accountRelationship = "Asset Account of"; //Asset Account of - Income Account is
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
    $this->type("xpath=//input[@class='form-text four']", "1");
    $this->click("xpath=//input[@class='form-radio']");
    $this->click("xpath=//input[@class='form-checkbox']");
    $this->fireEvent("xpath=//input[@class='form-text four']", 'blur');  
         
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
   
    $this->waitForElementPresent( "xpath=//div[@id='Contributions']//table/tbody/tr/");
    $url = $this->getAttribute ( "xpath=//div[@id='Contributions']//table//tbody/tr[1]/td[8]/span/a[text()='Edit']@href" );  
    $url = explode('&',$url);
    $valueID = $url[2];
    $valueID = explode('=',$valueID);
    $contribId = $valueID[1]; 
    $lineItem = CRM_Price_BAO_LineItem::getLineItems($contribId, 'contribution', 1);
    $this->click( "xpath=//div[@id='Contributions']//table//tbody/tr[1]/td[8]/span/a[text()='Edit']" );   
    $this->waitForPageToLoad('30000');
    $this->_testLineItem( $lineItem);
  
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
      $this->waitForElementPresent( "xpath=//table/tbody//tr/td[1][text()='{$financialAccountTitle}']/../td[8]/span/a[text()='Edit']" );
      
      //Add new Financial Type
      $financialType['name'] = 'FinancialType '.substr(sha1(rand()), 0, 4);
      $financialType['is_deductible'] = true;
      $financialType['is_reserved'] = false; 
      $this->addeditFinancialType( $financialType );

      $accountRelationship = "Income Account is"; //Asset Account - of Income Account is
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
                  //  $this->check('is_required');
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
      
      $this->waitForElementPresent( "xpath=//table/tbody//tr/td[1][text()='{$financialAccountTitle}']/../td[8]/span/a[text()='Edit']" );
      
      //Add new Financial Type
      $financialType = array();
      $financialType['name'] = 'FinancialAsset '.substr(sha1(rand()), 0, 4);
      $financialType['is_deductible'] = true;
      $financialType['is_reserved'] = false;
      $this->addeditFinancialType( $financialType , 'new');
      
      $accountRelationship = "Asset Account of"; //Asset Account of - Income Account is
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
      $this->webtestAddPaymentProcessor( $processorName, 'Dummy', null, $financialType['name'] );
      
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
      $this->type("xpath=//input[@class='form-text four']", "1");
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
      $this->waitForElementPresent( "xpath=//div[@id='contributionSearch']//table//tbody/tr[1]/td[11]/span/a[text()='Edit']" );
      $url = $this->getAttribute("xpath=//div[@id='contributionSearch']//table//tbody/tr[1]/td[11]/span/a[text()='Edit']@href");
      $url = explode('&',$url);
      $valueID = $url[2];
      $valueID = explode('=',$valueID);
      $contribId = $valueID[1]; 
      $lineItem = CRM_Price_BAO_LineItem::getLineItems($contribId, 'contribution', 1);   
      $this->click( "xpath=//div[@id='contributionSearch']//table//tbody/tr[1]/td[11]/span/a[text()='Edit']" );  
      $this->_testLineItem( $lineItem);      
      $this->waitForElementPresent( "xpath=//div[@id='contributionSearch']//table//tbody/tr[1]/td[11]/span/a[text()='View']" );
      $this->click( "xpath=//div[@id='contributionSearch']//table//tbody/tr[1]/td[11]/span/a[text()='View']" );
      $this->waitForPageToLoad( '30000' );
      $this->waitForElementPresent( "_qf_ContributionView_cancel-bottom" );
      sleep(2);
      //View Contribution Record
      $expected = array(
                        'Contribution Amount' => '590.00',
                        'Contribution Status' => 'Pending : Incomplete Transaction',
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

  $this->waitForElementPresent( "xpath=//table/tbody//tr/td[1][text()='{$financialAccountTitle}']/../td[8]/span/a[text()='Edit']" );
    
  //Add new Financial Type
  $financialType = array();
  $financialType['name'] = 'FinancialAsset '.substr(sha1(rand()), 0, 4);
  $financialType['is_deductible'] = true;
  $financialType['is_reserved'] = false;
  $this->addeditFinancialType( $financialType , 'new');
    
  $accountRelationship = "Asset Account of"; //Asset Account of - Income Account is
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

  $fields = array( "National Membership $title" => 'Radio',
                   "Local Chapter $title"       => 'CheckBox' );

  list( $memTypeTitle1, $memTypeTitle2 ) = $this->_testAddPriceFieldsMem( $fields, $validateStrings, null, $title, $sid );
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

 function testContributionDistributeEvenly(){
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

    $this->waitForElementPresent( "xpath=//table/tbody//tr/td[1][text()='{$financialAccountTitle}']/../td[8]/span/a[text()='Edit']" );
    
    //Add new Financial Type
    $financialType = array();
    $financialType['name'] = 'FinancialAsset '.substr(sha1(rand()), 0, 4);
    $financialType['is_deductible'] = true;
    $financialType['is_reserved'] = false;
    $this->addeditFinancialType( $financialType , 'new');
    
    $accountRelationship = "Asset Account of"; //Asset Account of - Income Account is
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
      
    // fill in Received Date
    $this->webtestFillDate('receive_date');
      
    //select recieved into
    $this->select("financial_type_id", "label={$financialType['name']}");
    // source
    $this->type('source', 'Mailer 1');
      
    // select price set items
    $this->select('price_set_id', "label=$setTitle");
    $this->type("xpath=//input[@class='form-text four']", "1");
    $this->click("xpath=//input[@class='form-radio']");
    $this->click("xpath=//input[@class='form-checkbox']");
    $this->fireEvent("xpath=//input[@class='form-text four']", 'blur');  
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
    $this->click('CIVICRM_QFID_1_2');
    $this->fireEvent('initial_amount', 'blur');  
    $lineItem1 = $this->getAttribute("xpath= id('initialPayment')/table/tbody/tr[2]/td[2]/input[1]@value");
    $lineItem2 = $this->getAttribute("xpath= id('initialPayment')/table/tbody/tr[3]/td[2]/input[1]@value");
    $lineItem3 = $this->getAttribute("xpath= id('initialPayment')/table/tbody/tr[4]/td[2]/input[1]@value");
    $this->assertTrue(($lineItem1 == '44.49'),"LineItem amount incorrect");
    $this->assertTrue(($lineItem2 == '4.24'), "LineItem amount incorrect");
    $this->assertTrue(($lineItem3 == '1.27'), "LineItem amount incorrect");
  
    // Clicking save.
    $this->click('_qf_Contribution_upload');
    $this->waitForPageToLoad('30000');
   
    $this->waitForElementPresent( "xpath=//div[@id='Contributions']//table/tbody/tr/"); 
    $url = $this->getAttribute ( "xpath=//div[@id='Contributions']//table//tbody/tr[1]/td[8]/span/a[text()='Edit']@href" );  
    $url = explode('&',$url);
    $valueID = $url[2];
    $valueID = explode('=',$valueID);
    $contribId = $valueID[1]; 
    $lineItem = CRM_Price_BAO_LineItem::getLineItems($contribId, 'contribution', 1);
    $this->click( "xpath=//div[@id='Contributions']//table//tbody/tr[1]/td[8]/span/a[text()='Edit']" );   
    $this->waitForPageToLoad('30000');
    $this->_testLineItem( $lineItem);
 }

 function testContributionSingleAllocate(){
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

    $this->waitForElementPresent( "xpath=//table/tbody//tr/td[1][text()='{$financialAccountTitle}']/../td[8]/span/a[text()='Edit']" );
    
    //Add new Financial Type
    $financialType = array();
    $financialType['name'] = 'FinancialAsset '.substr(sha1(rand()), 0, 4);
    $financialType['is_deductible'] = true;
    $financialType['is_reserved'] = false;
    $this->addeditFinancialType( $financialType , 'new');
    
    $accountRelationship = "Asset Account of"; //Asset Account of - Income Account is
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
      
   
    // fill in Received Date
    $this->webtestFillDate('receive_date');
      
    //select recieved into
    $this->select("financial_type_id", "label={$financialType['name']}");
    // source
    $this->type('source', 'Mailer 1');
      
    // select price set items
    $this->select('price_set_id', "label=$setTitle");
    $this->type("xpath=//input[@class='form-text four']", "1");
    $this->click("xpath=//input[@class='form-radio']");
    $this->click("xpath=//input[@class='form-checkbox']");
    $this->fireEvent("xpath=//input[@class='form-text four']", 'blur');  
    $this->click('submitPayment_Information');
    $this->click('int_amount');
    $this->click('initial_amount');
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
    $this->type("xpath= id('initialPayment')/table/tbody/tr[2]/td[2]/input[1]",'100.00');
    // Clicking save.
    $this->click('_qf_Contribution_upload');
    $this->waitForPageToLoad('30000');
   
    $this->waitForElementPresent( "xpath=//div[@id='Contributions']//table/tbody/tr/");
    //$url = $this->getAttribute ( "xpath=//div[@id='Contributions']//table//tbody/tr[1]/td[8]/span/a[text()='Edit']@href" );  
    $url = $this->getAttribute ( "xpath=//div[@id='Contributions']//table//tbody/tr[1]/td[8]/span/a[text()='Edit']@href" );  
    $url = explode('&',$url);
    $valueID = $url[2];
    $valueID = explode('=',$valueID);
    $contribId = $valueID[1]; 
    $lineItem = CRM_Price_BAO_LineItem::getLineItems($contribId, 'contribution', 1);
    $this->click( "xpath=//div[@id='Contributions']//table//tbody/tr[1]/td[8]/span/a[text()='Edit']" );   
    $this->waitForPageToLoad('30000');
    $this->_testLineItem( $lineItem);
  
  }

 function testContributionDoubleAllocate(){
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

    $this->waitForElementPresent( "xpath=//table/tbody//tr/td[1][text()='{$financialAccountTitle}']/../td[8]/span/a[text()='Edit']" );
    
    //Add new Financial Type
    $financialType = array();
    $financialType['name'] = 'FinancialAsset '.substr(sha1(rand()), 0, 4);
    $financialType['is_deductible'] = true;
    $financialType['is_reserved'] = false;
    $this->addeditFinancialType( $financialType , 'new');
    
    $accountRelationship = "Asset Account of"; //Asset Account of - Income Account is
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
      
    // fill in Received Date
    $this->webtestFillDate('receive_date');
      
    //select recieved into
    $this->select("financial_type_id", "label={$financialType['name']}");
    // source
    $this->type('source', 'Mailer 1');
      
    // select price set items
    $this->select('price_set_id', "label=$setTitle");
    $this->type("xpath=//input[@class='form-text four']", "1");
    $this->click("xpath=//input[@class='form-radio']");
    $this->click("xpath=//input[@class='form-checkbox']");
    $this->fireEvent("xpath=//input[@class='form-text four']", 'blur');  
    $this->click('submitPayment_Information');
    $this->click('int_amount');
    $this->click('initial_amount');
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
    $this->type("xpath= id('initialPayment')/table/tbody/tr[2]/td[2]/input[1]",'100.00');
    $this->type("xpath= id('initialPayment')/table/tbody/tr[3]/td[2]/input[1]",'25.00');
    // Clicking save.
    $this->click('_qf_Contribution_upload');
    $this->waitForPageToLoad('30000');
   
    $this->waitForElementPresent( "xpath=//div[@id='Contributions']//table/tbody/tr/");
    $url = $this->getAttribute ( "xpath=//div[@id='Contributions']//table//tbody/tr[1]/td[8]/span/a[text()='Edit']@href" );  
    $url = explode('&',$url);
    $valueID = $url[2];
    $valueID = explode('=',$valueID);
    $contribId = $valueID[1]; 
    $lineItem = CRM_Price_BAO_LineItem::getLineItems($contribId, 'contribution', 1);
    $this->click( "xpath=//div[@id='Contributions']//table//tbody/tr[1]/td[8]/span/a[text()='Edit']" );   
    $this->waitForPageToLoad('30000');
    $this->_testLineItem( $lineItem);
  
  }

 function testContributionRemoveLineItem(){
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

    $this->waitForElementPresent( "xpath=//table/tbody//tr/td[1][text()='{$financialAccountTitle}']/../td[8]/span/a[text()='Edit']" );
    
    //Add new Financial Type
    $financialType = array();
    $financialType['name'] = 'FinancialAsset '.substr(sha1(rand()), 0, 4);
    $financialType['is_deductible'] = true;
    $financialType['is_reserved'] = false;
    $this->addeditFinancialType( $financialType , 'new');
    
    $accountRelationship = "Asset Account of"; //Asset Account of - Income Account is
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
      
    // fill in Received Date
    $this->webtestFillDate('receive_date');
      
    //select recieved into
    $this->select("financial_type_id", "label={$financialType['name']}");
    // source
    $this->type('source', 'Mailer 1');
      
    // select price set items
    $this->select('price_set_id', "label=$setTitle");
    $this->type("xpath=//input[@class='form-text four']", "1");
    $this->click("xpath=//input[@class='form-radio']");
    $this->fireEvent("xpath=//input[@class='form-text four']", 'blur');  
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
    $this->click('CIVICRM_QFID_1_2');
    $this->fireEvent('initial_amount', 'blur');  
    $lineItem1 = $this->getAttribute("xpath= id('initialPayment')/table/tbody/tr[2]/td[2]/input[1]@value");
    $lineItem2 = $this->getAttribute("xpath= id('initialPayment')/table/tbody/tr[3]/td[2]/input[1]@value");
    $this->assertTrue(($lineItem1 == '45.65'),"LineItem amount incorrect");
    $this->assertTrue(($lineItem2 == '4.35'), "LineItem amount incorrect");
    // Clicking save.
    $this->click('_qf_Contribution_upload');
    $this->waitForPageToLoad('30000');
   
    $this->waitForElementPresent( "xpath=//div[@id='Contributions']//table/tbody/tr/"); 
    $url = $this->getAttribute ( "xpath=//div[@id='Contributions']//table//tbody/tr[1]/td[8]/span/a[text()='Edit']@href" );  
    $url = explode('&',$url);
    $valueID = $url[2];
    $valueID = explode('=',$valueID);
    $contribId = $valueID[1]; 
    $lineItem = CRM_Price_BAO_LineItem::getLineItems($contribId, 'contribution', 1);
    $this->click( "xpath=//div[@id='Contributions']//table//tbody/tr[1]/td[8]/span/a[text()='Edit']" );   
    $this->waitForPageToLoad('30000');
    $this->_testLineItem( $lineItem);
  
  }

function testContributionOverPayment(){
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

    $this->waitForElementPresent( "xpath=//table/tbody//tr/td[1][text()='{$financialAccountTitle}']/../td[8]/span/a[text()='Edit']" );
    
    //Add new Financial Type
    $financialType = array();
    $financialType['name'] = 'FinancialAsset '.substr(sha1(rand()), 0, 4);
    $financialType['is_deductible'] = true;
    $financialType['is_reserved'] = false;
    $this->addeditFinancialType( $financialType , 'new');
    
    $accountRelationship = "Asset Account of"; //Asset Account of - Income Account is
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
    $this->type("xpath=//input[@class='form-text four']", "1");   
    $this->click("xpath=//input[@class='form-radio']");
    $this->click("xpath=//input[@class='form-checkbox']");
    $this->fireEvent("xpath=//input[@class='form-text four']", 'blur');  
    $this->click('submitPayment_Information');
    $this->click('int_amount');
    $this->click('initial_amount');
    $this->type('initial_amount', 500.00);
    $this->fireEvent('initial_amount', 'blur'); 
    $this->click("xpath=//tr[@id='adjust-option-items']/td/label[1]");
  
    // select payment instrument type = Check and enter chk number 
    $this->select('payment_instrument_id', 'value=4');
    $this->waitForElementPresent('check_number');
    $this->type('check_number', 'check #1041');
    $this->type('trxn_id', 'P20901X1' . rand(100, 10000));
    //Additional Detail section
    $this->click('AdditionalDetail');
    $this->waitForElementPresent('thankyou_date');

    $this->type('note', 'This is a test note.');
    $this->type('non_deductible_amount', '10');
    $this->type('fee_amount', '0');
    $this->type('net_amount', '0');
    $this->type('invoice_id', time());
    $this->webtestFillDate('thankyou_date');
    $this->fireEvent('CIVICRM_QFID_1_2', 'click');
    $this->type("xpath=//input[@class='form-text four valid']", ""); 
    $this->click("xpath=//div[@id='priceset']/div[3]/div[1]/label"); 
    $this->fireEvent("xpath=//select[@class='form-select']", 'focus'); 
    $this->fireEvent("xpath=//input[@class='form-text four valid']", 'blur');
    $this->click("xpath=//input[@class='form-text four valid']");
    sleep(2);  
  
    $lineItem1 = $this->getAttribute("xpath= id('initialPayment')/table/tbody/tr[2]/td[2]/input[1]@value");
    $lineItem2 = $this->getAttribute("xpath= id('initialPayment')/table/tbody/tr[3]/td[2]/input[1]@value");
    $lineItem3 = $this->getAttribute("xpath= id('initialPayment')/table/tbody/tr[4]/td[2]/input[1]@value");
    $this->assertTrue(($lineItem1 == 'NaN'),"LineItem amount incorrect");
    $this->assertTrue(($lineItem2 == '384.62'), "LineItem amount incorrect");
    $this->assertTrue(($lineItem3 == '115.38'), "LineItem amount incorrect");
    // Clicking save.
    $this->click('_qf_Contribution_upload');
    $this->waitForPageToLoad('30000');
    $this->assertTrue( $this->isTextPresent("Initial Amount is greater than base Amount."), "Validation message for overpayment did not showed up");
  }

function testContributionAddAndRemoveLineItem(){
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

    $this->waitForElementPresent( "xpath=//table/tbody//tr/td[1][text()='{$financialAccountTitle}']/../td[8]/span/a[text()='Edit']" );
    
    //Add new Financial Type
    $financialType = array();
    $financialType['name'] = 'FinancialAsset '.substr(sha1(rand()), 0, 4);
    $financialType['is_deductible'] = true;
    $financialType['is_reserved'] = false;
    $this->addeditFinancialType( $financialType , 'new');
    
    $accountRelationship = "Asset Account of"; //Asset Account of - Income Account is
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
      
    // fill in Received Date
    $this->webtestFillDate('receive_date');
      
    //select recieved into
    $this->select("financial_type_id", "label={$financialType['name']}");
    // source
    $this->type('source', 'Mailer 1');
      
    // select price set items
    $this->select('price_set_id', "label=$setTitle"); 
    $this->type("xpath=//input[@class='form-text four']", "1");   
    $this->click("xpath=//input[@class='form-radio']");
    $this->fireEvent("xpath=//input[@class='form-text four']", 'blur'); 
    $this->click('submitPayment_Information');
    $this->click('int_amount');
    $this->click('initial_amount');
    $this->type('initial_amount', 50.00);
    $this->fireEvent('initial_amount', 'blur'); 
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
    $this->fireEvent('CIVICRM_QFID_1_2', 'click');
    $this->type("xpath=//input[@class='form-text four valid']", ""); 
     $this->click("xpath=//input[@class='form-checkbox']");
    $this->click("xpath=//div[@id='priceset']/div[3]/div[1]/label"); 
    $this->fireEvent("xpath=//select[@class='form-select']", 'focus'); 
    $this->fireEvent("xpath=//input[@class='form-text four valid']", 'blur');
    $this->click("xpath=//input[@class='form-text four valid']");  
    $lineItem1 = $this->getAttribute("xpath= id('initialPayment')/table/tbody/tr[2]/td[2]/input[1]@value");
    $lineItem2 = $this->getAttribute("xpath= id('initialPayment')/table/tbody/tr[3]/td[2]/input[1]@value");
    $lineItem3 = $this->getAttribute("xpath= id('initialPayment')/table/tbody/tr[4]/td[2]/input[1]@value");
    $this->assertTrue(($lineItem1 == 'NaN'),"LineItem amount incorrect");
    $this->assertTrue(($lineItem2 == '38.46'), "LineItem amount incorrect");
    $this->assertTrue(($lineItem3 == '11.54'), "LineItem amount incorrect");
    
    // Clicking save.
    $this->click('_qf_Contribution_upload');
    $this->waitForPageToLoad('30000');
      
    // Is status message correct?

    $this->waitForElementPresent( "xpath=//div[@id='Contributions']//table/tbody/tr/"); 
    $url = $this->getAttribute ( "xpath=//div[@id='Contributions']//table//tbody/tr[1]/td[8]/span/a[text()='Edit']@href" );  
    $url = explode('&',$url);
    $valueID = $url[2];
    $valueID = explode('=',$valueID);
    $contribId = $valueID[1]; 
    $lineItem = CRM_Price_BAO_LineItem::getLineItems($contribId, 'contribution', 1);
    $this->click( "xpath=//div[@id='Contributions']//table//tbody/tr[1]/td[8]/span/a[text()='Edit']" ); 
    $this->waitForPageToLoad('30000');
    $this->_testLineItem( $lineItem);
  
  }

function testContributeRefundZero(){
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

    $this->waitForElementPresent( "xpath=//table/tbody//tr/td[1][text()='{$financialAccountTitle}']/../td[8]/span/a[text()='Edit']" );
    
    //Add new Financial Type
    $financialType = array();
    $financialType['name'] = 'FinancialAsset '.substr(sha1(rand()), 0, 4);
    $financialType['is_deductible'] = true;
    $financialType['is_reserved'] = false;
    $this->addeditFinancialType( $financialType , 'new');
    
    $accountRelationship = "Asset Account of"; //Asset Account of - Income Account is
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
      
    // fill in Received Date
    $this->webtestFillDate('receive_date');
      
    //select recieved into
    $this->select("financial_type_id", "label={$financialType['name']}");
    // source
    $this->type('source', 'Mailer 1');
      
    // select price set items
    $this->select('price_set_id', "label=$setTitle");  
     $this->click("xpath=//input[@class='form-checkbox']"); 
    $this->click("xpath=//input[@class='form-radio']");
    $this->fireEvent("xpath=//input[@class='form-text four']", 'blur'); 
    $this->click('submitPayment_Information');
    $this->click('int_amount');
    $this->click('initial_amount');
    $this->type('initial_amount', 500.00);
    $this->getExpression('initial_amount');
    $this->verifyAlert("Initial Amount is Greater") ; 
    $this->chooseOkOnNextConfirmation();
    $this->type("xpath=//input[@class='form-text four']", "1");
    $this->fireEvent("xpath=//input[@class='form-text four']", 'blur');
    $this->fireEvent('CIVICRM_QFID_1_2', 'click');
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
    $unallocated = $this->getText("xpath= id('unlocateAmount')/table/tbody/tr/td/strong"); 
    $this->assertTrue(($unallocated == '$ 0.00'), "Unallocated amount is not zero");

    // Clicking save.
    $this->click('_qf_Contribution_upload');
    $this->waitForPageToLoad('30000');
   
    $this->waitForElementPresent( "xpath=//div[@id='Contributions']//table/tbody/tr/"); 
    $url = $this->getAttribute ( "xpath=//div[@id='Contributions']//table//tbody/tr[1]/td[8]/span/a[text()='Edit']@href" );  
    $url = explode('&',$url);
    $valueID = $url[2];
    $valueID = explode('=',$valueID);
    $contribId = $valueID[1]; 
    $lineItem = CRM_Price_BAO_LineItem::getLineItems($contribId, 'contribution', 1);
    $this->click( "xpath=//div[@id='Contributions']//table//tbody/tr[1]/td[8]/span/a[text()='Edit']" );   
    $this->waitForPageToLoad('30000');
    $this->_testLineItem( $lineItem);
  }

function testContributeFullAmount(){
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

    $this->waitForElementPresent( "xpath=//table/tbody//tr/td[1][text()='{$financialAccountTitle}']/../td[8]/span/a[text()='Edit']" );
    
    //Add new Financial Type
    $financialType = array();
    $financialType['name'] = 'FinancialAsset '.substr(sha1(rand()), 0, 4);
    $financialType['is_deductible'] = true;
    $financialType['is_reserved'] = false;
    $this->addeditFinancialType( $financialType , 'new');
    
    $accountRelationship = "Asset Account of"; //Asset Account of - Income Account is
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
      
    // fill in Received Date
    $this->webtestFillDate('receive_date');
      
    //select recieved into
    $this->select("financial_type_id", "label={$financialType['name']}");
    // source
    $this->type('source', 'Mailer 1');
      
    // select price set items
    $this->select('price_set_id', "label=$setTitle");  
     $this->click("xpath=//input[@class='form-checkbox']"); 
    $this->click("xpath=//input[@class='form-radio']");
    $this->fireEvent("xpath=//input[@class='form-text four']", 'blur'); 
    $this->click('submitPayment_Information');
    $this->click('int_amount');
    $this->click('initial_amount');
    $this->type('initial_amount', 590.00);
    $this->getExpression('initial_amount');
    //$this->fireEvent('initial_amount', 'blur');
    $this->verifyAlert("Initial Amount is Greater") ; 
    $this->chooseOkOnNextConfirmation();
    $this->type("xpath=//input[@class='form-text four']", "1");
    $this->fireEvent("xpath=//input[@class='form-text four']", 'blur');
    $this->fireEvent('CIVICRM_QFID_1_2', 'click');
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
    $unallocated = $this->getText("xpath= id('unlocateAmount')/table/tbody/tr/td/strong"); 
    $this->assertTrue(($unallocated == '$ 0.00'), "Unallocated amount is not zero");

    // Clicking save.
    $this->click('_qf_Contribution_upload');
    $this->waitForPageToLoad('30000');
      
    // Is status message correct?
    //$this->assertTrue($this->isTextPresent('The contribution record has been saved.'), "Status message didn't show up after saving!");
   
    $this->waitForElementPresent( "xpath=//div[@id='Contributions']//table/tbody/tr/"); 
    $url = $this->getAttribute ( "xpath=//div[@id='Contributions']//table//tbody/tr[1]/td[8]/span/a[text()='Edit']@href" );  
    $url = explode('&',$url);
    $valueID = $url[2];
    $valueID = explode('=',$valueID);
    $contribId = $valueID[1]; 
    $lineItem = CRM_Price_BAO_LineItem::getLineItems($contribId, 'contribution', 1);
    $this->click( "xpath=//div[@id='Contributions']//table//tbody/tr[1]/td[8]/span/a[text()='Edit']" );   
    $this->waitForPageToLoad('30000');
    $this->_testLineItem( $lineItem);
  }

function testContributionPartialPayment(){
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

    $this->waitForElementPresent( "xpath=//table/tbody//tr/td[1][text()='{$financialAccountTitle}']/../td[8]/span/a[text()='Edit']" );
    
    //Add new Financial Type
    $financialType = array();
    $financialType['name'] = 'FinancialAsset '.substr(sha1(rand()), 0, 4);
    $financialType['is_deductible'] = true;
    $financialType['is_reserved'] = false;
    $this->addeditFinancialType( $financialType , 'new');
    
    $accountRelationship = "Asset Account of"; //Asset Account of - Income Account is
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
     $this->click("xpath=//input[@class='form-checkbox']"); 
    $this->click("xpath=//input[@class='form-radio']");
    $this->fireEvent("xpath=//input[@class='form-text four']", 'blur'); 
    $this->click('submitPayment_Information');
    $this->click('int_amount');
    $this->click('initial_amount');
    $this->type('initial_amount', 500.00);
    $this->getExpression('initial_amount');
    //$this->fireEvent('initial_amount', 'blur');
    $this->verifyAlert("Initial Amount is Greater") ; 
    $this->chooseOkOnNextConfirmation();
    $this->type("xpath=//input[@class='form-text four']", "1");
    $this->fireEvent("xpath=//input[@class='form-text four']", 'blur');
    $this->fireEvent('CIVICRM_QFID_1_2', 'click');
    $this->click("xpath=//tr[@id='adjust-option-items']/td/label[1]");sleep(20);
  
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
    /* $unallocated = $this->getText("xpath= id('unlocateAmount')/table/tbody/tr/td/strong");  */
    /* $this->assertTrue(($unallocated == '$ 0.00'), "Unallocated amount is not zero"); */
    $lineItem1 = $this->getAttribute("xpath= id('initialPayment')/table/tbody/tr[2]/td[2]/input[1]@value");
    $lineItem2 = $this->getAttribute("xpath= id('initialPayment')/table/tbody/tr[3]/td[2]/input[1]@value");
    $lineItem3 = $this->getAttribute("xpath= id('initialPayment')/table/tbody/tr[4]/td[2]/input[1]@value"); 
    $this->assertTrue(($lineItem1 == '444.92'),"LineItem amount incorrect");
    $this->assertTrue(($lineItem2 == '42.37'), "LineItem amount incorrect");
    $this->assertTrue(($lineItem3 == '12.71'), "LineItem amount incorrect");

    // Clicking save.
    $this->click('_qf_Contribution_upload');
    $this->waitForPageToLoad('30000');
      
    // Is status message correct?
    //$this->assertTrue($this->isTextPresent('The contribution record has been saved.'), "Status message didn't show up after saving!");
   
    $this->waitForElementPresent( "xpath=//div[@id='Contributions']//table/tbody/tr/"); 
    $url = $this->getAttribute ( "xpath=//div[@id='Contributions']//table//tbody/tr[1]/td[8]/span/a[text()='Edit']@href" );  
    $url = explode('&',$url);
    $valueID = $url[2];
    $valueID = explode('=',$valueID);
    $contribId = $valueID[1]; 
    $lineItem = CRM_Price_BAO_LineItem::getLineItems($contribId, 'contribution', 1);
    $this->click( "xpath=//div[@id='Contributions']//table//tbody/tr[1]/td[8]/span/a[text()='Edit']" );   
    $this->waitForPageToLoad('30000');
    $this->_testLineItem( $lineItem);
  }

function testContributionNoPayment(){
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

  $this->waitForElementPresent( "xpath=//table/tbody//tr/td[1][text()='{$financialAccountTitle}']/../td[8]/span/a[text()='Edit']" );
    
  //Add new Financial Type
  $financialType = array();
  $financialType['name'] = 'FinancialAsset '.substr(sha1(rand()), 0, 4);
  $financialType['is_deductible'] = true;
  $financialType['is_reserved'] = false;
  $this->addeditFinancialType( $financialType , 'new');
    
  $accountRelationship = "Asset Account of"; //Asset Account of - Income Account is
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
      
  // fill in Received Date
  $this->webtestFillDate('receive_date');
  //select recieved into
  $this->select("financial_type_id", "label={$financialType['name']}");
  // source
  $this->type('source', 'Mailer 1');
      
  // select price set items
  $this->select('price_set_id', "label=$setTitle");  
  $this->click("xpath=//input[@class='form-checkbox']"); 
  $this->click("xpath=//input[@class='form-radio']");
  $this->fireEvent("xpath=//input[@class='form-text four']", 'blur'); 
  $this->click('submitPayment_Information');
  $this->click('int_amount');
  $this->click('initial_amount');
  
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
  $lineItem1 = $this->getAttribute("xpath= id('initialPayment')/table/tbody/tr[2]/td[2]/input[1]@value");
  $lineItem2 = $this->getAttribute("xpath= id('initialPayment')/table/tbody/tr[3]/td[2]/input[1]@value");
  $lineItem3 = $this->getAttribute("xpath= id('initialPayment')/table/tbody/tr[4]/td[2]/input[1]@value");
  $this->assertTrue(($lineItem1 == 'NaN'),"LineItem amount incorrect");
  $this->assertTrue(($lineItem2 == 'NaN'), "LineItem amount incorrect");
  $this->assertTrue(($lineItem3 == 'NaN'), "LineItem amount incorrect");

  // Clicking save.
  $this->click('_qf_Contribution_upload');
  $this->waitForPageToLoad('30000');
   
  $this->waitForElementPresent( "xpath=//div[@id='Contributions']//table/tbody/tr/"); 
  $url = $this->getAttribute ( "xpath=//div[@id='Contributions']//table//tbody/tr[1]/td[8]/span/a[text()='Edit']@href" );  
  $url = explode('&',$url);
  $valueID = $url[2];
  $valueID = explode('=',$valueID);
  $contribId = $valueID[1]; 
  $lineItem = CRM_Price_BAO_LineItem::getLineItems($contribId, 'contribution', 1);
  $this->click( "xpath=//div[@id='Contributions']//table//tbody/tr[1]/td[8]/span/a[text()='Edit']" );   
  $this->waitForPageToLoad('30000');
  $this->_testLineItem( $lineItem);
}

function testSeparatePayment(){
  $this->open( $this->sboxPath );
  // Log in using webtestLogin() method
  $this->webtestLogin();  
  //add financial type of account type expense
  $financialType= $this->_testAddFinancialType();
  
  // We need a payment processor
  $pageTitle     = substr(sha1(rand()), 0, 7);
  $rand          = 2 * rand(10, 50);
  $hash          = substr(sha1(rand()), 0, 7);
  $amountSection = TRUE;
  $payLater      = TRUE;
  $onBehalf      = FALSE;
  $pledges       = FALSE;
  $recurring     = FALSE;
  $memberships   = TRUE;
  $friend        = FALSE;
  $profilePreId  = 1;
  $profilePostId = NULL;
  $premiums      = FALSE;
  $widget        = FALSE;
  $pcp           = FALSE;
  $memPriceSetId = NULL;

  $pageId = $this->webtestAddContributionPage($hash,
                                              $rand,
                                              $pageTitle,
                                              NULL,
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
                                              $financialType
                                              );
  
    //logout
    $this->open($this->sboxPath . "civicrm/logout?reset=1");
    $this->waitForPageToLoad('30000');

    //Open Live Contribution Page
    $this->open($this->sboxPath . "civicrm/contribute/transact?reset=1&id=" . $pageId);
    $this->waitForElementPresent("_qf_Main_upload-bottom");
    $firstName = substr(sha1(rand()), 0, 7);
    $lastname = 'Contributor';
    $email = $firstName.'.'.$lastname.'@exa.com';
    $this->type('first_name', $firstName);  
    $this->type('last_name', $lastname);  
    $this->type('email-5', $email); 
    $this->click('_qf_Main_upload-bottom');   
    $this->waitForElementPresent("_qf_Confirm_next-top");    
    $this->click('_qf_Confirm_next-top');  
    //login to check contribution
    $this->open( $this->sboxPath );      
    // Log in using webtestLogin() method
    $this->webtestLogin();
    $this->open($this->sboxPath . "civicrm/contribute/search?reset=1");  
    $this->waitForElementPresent("_qf_Search_refresh");    
    $this->type('sort_name', $email);     
    $this->click("_qf_Search_refresh");   
    $this->waitForElementPresent("xpath= id('contributionSearch')");
    $this->click("xpath= id('contributionSearch')/table/tbody/tr/td[11]/span/a[text()='Edit']");
    $this->waitForElementPresent('total_amount');
    $total_amount =  $this->getValue("total_amount");
    $paid = $this->getValue("paid");
    $owing = $this->getValue("owing");
    $this->assertTrue($total_amount == ($paid+$owing),"Incorrect Amount present");
    $this->click('_qf_Contribution_cancel');       
    $this->waitForElementPresent("xpath= id('contributionSearch')");
    $this->click("xpath= id('contributionSearch')/table/tbody/tr[2]/td[11]/span/a[text()='Edit']");
    $this->waitForElementPresent('total_amount');
    $total_amount = $this->getValue("total_amount");
    $paid = $this->getValue("paid");
    $owing = $this->getValue("owing");
    $this->assertTrue($total_amount == ($paid+$owing),"Incorrect Amount present");
}
function webtestAddContributionPage($hash = NULL,
    $rand = NULL,
    $pageTitle = NULL,
    $processor = array('Dummy Processor' => 'Dummy'),
    $amountSection = TRUE,
    $payLater = TRUE,
    $onBehalf = TRUE,
    $pledges = TRUE,
    $recurring = FALSE,
    $membershipTypes = TRUE,
    $memPriceSetId = NULL,
    $friend = FALSE,
    $profilePreId = NULL,
    $profilePostId = NULL,
    $premiums = TRUE,
    $widget = TRUE,
    $pcp = TRUE,
    $financialType,                                    
    $isAddPaymentProcessor = FALSE,
    $isPcpApprovalNeeded = FALSE,
    $isSeparatePayment = TRUE,
    $honoreeSection = FALSE,
    $allowOtherAmmount = TRUE,
    $isConfirmEnabled = TRUE
  )
{
    if (!$hash) {
      $hash = substr(sha1(rand()), 0, 7);
    }
    if (!$pageTitle) {
      $pageTitle = 'Donate Online ' . $hash;
    }
    if (!$rand) {
      $rand = 2 * rand(2, 50);
    }

    // Create a new payment processor if requested
    if ($isAddPaymentProcessor) {
      while (list($processorName, $processorType) = each($processor)) {
        $this->webtestAddPaymentProcessor($processorName, $processorType);
      }
    }

    // go to the New Contribution Page page
    $this->open($this->sboxPath . 'civicrm/admin/contribute?action=add&reset=1');
    $this->waitForPageToLoad();

    // fill in step 1 (Title and Settings)
    $this->type('title', $pageTitle);

    if ($onBehalf) {
      $this->click('is_organization');
      $this->select('onbehalf_profile_id', 'label=On Behalf Of Organization');
      $this->type('for_organization', "On behalf $hash");

      if ($onBehalf == 'required') {
        $this->click('CIVICRM_QFID_2_4');
      }
      elseif ($onBehalf == 'optional') {
        $this->click('CIVICRM_QFID_1_2');
      }
    }

    $this->fillRichTextField('intro_text', 'This is introductory message for ' . $pageTitle, 'CKEditor');
    $this->fillRichTextField('footer_text', 'This is footer message for ' . $pageTitle, 'CKEditor');

    $this->type('goal_amount', 10 * $rand);

    // FIXME: handle Start/End Date/Time
    if ($honoreeSection) {
      $this->click('honor_block_is_active');
      $this->type('honor_block_title', "Honoree Section Title $hash");
      $this->type('honor_block_text', "Honoree Introductory Message $hash");
    }

    // is confirm enabled? it starts out enabled, so uncheck it if false
    if (!$isConfirmEnabled) {
      $this->click("id=is_confirm_enabled");
    }

    // go to step 2
    $this->click('_qf_Settings_next');
    $this->waitForElementPresent('_qf_Amount_next-bottom');

    // fill in step 2 (Processor, Pay Later, Amounts)
    if (!empty($processor)) {
      reset($processor);
      while (list($processorName) = each($processor)) {
        // select newly created processor
        $xpath = "xpath=//label[text() = '{$processorName}']/preceding-sibling::input[1]";
        $this->assertTrue($this->isTextPresent($processorName));
        $this->check($xpath);
      }
    }

    if ($amountSection && !$memPriceSetId) {
      if ($payLater) {
        $this->click('is_pay_later');
        $this->type('pay_later_text', "Pay later label $hash");
        $this->type('pay_later_receipt', "Pay later instructions $hash");
      }
     
      $this->select('financial_type_id', "label={$financialType}");

      if ($pledges) {
        $this->click('is_pledge_active');
        $this->click('pledge_frequency_unit[week]');
        $this->click('is_pledge_interval');
        $this->type('initial_reminder_day', 3);
        $this->type('max_reminders', 2);
        $this->type('additional_reminder_day', 1);
      }
      elseif ($recurring) {
        $this->click('is_recur');
        // only monthly frequency unit enabled
        $this->click("recur_frequency_unit[day]");
        $this->click("recur_frequency_unit[week]");
        $this->click("recur_frequency_unit[year]");
      }
      if ($allowOtherAmmount) {

        $this->click('is_allow_other_amount');

        // there shouldn't be minimums and maximums on test contribution forms unless you specify it
        //$this->type('min_amount', $rand / 2);
        //$this->type('max_amount', $rand * 10);
      }
      $this->type('label_1', "Label $hash");
      $this->type('value_1', "$rand");
      $this->click('CIVICRM_QFID_1_2');
    }
    else {
      $this->click('amount_block_is_active');
    }

    $this->click('_qf_Amount_next');
    $this->waitForElementPresent('_qf_Amount_next-bottom');
    $this->waitForPageToLoad('30000');
    $text = "'Amount' information has been saved.";
    $this->assertTrue($this->isTextPresent($text), 'Missing text: ' . $text);

    if ($memPriceSetId || (($membershipTypes === TRUE) || (is_array($membershipTypes) && !empty($membershipTypes)))) {
      // go to step 3 (memberships)
      $this->click('link=Memberships');
      $this->waitForElementPresent('_qf_MembershipBlock_next-bottom');

      // fill in step 3 (Memberships)
      $this->click('member_is_active');
      $this->waitForElementPresent('displayFee');
      $this->type('new_title', "Title - New Membership $hash");
      $this->type('renewal_title', "Title - Renewals $hash");

      if ($memPriceSetId) {
        $this->click('member_price_set_id');
        $this->select('member_price_set_id', "value={$memPriceSetId}");
      }
      else {
        if ($membershipTypes === TRUE) {
          $membershipTypes = array(array('id' => 2));
        }

        // FIXME: handle Introductory Message - New Memberships/Renewals
        foreach ($membershipTypes as $mType) {
          $this->click("membership_type[{$mType['id']}]");
          if (array_key_exists('default', $mType)) {
            // FIXME:
          }
          if (array_key_exists('auto_renew', $mType)) {
            $this->select("auto_renew_{$mType['id']}", "label=Give option");
          }
        }

        $this->click('is_required');
        $this->waitForElementPresent('CIVICRM_QFID_2_4');
        $this->click('CIVICRM_QFID_2_4');
        if ($isSeparatePayment) {
          $this->click('is_separate_payment');
        }
      }
      $this->click('_qf_MembershipBlock_next');
      $this->waitForPageToLoad('30000');
      $this->waitForElementPresent('_qf_MembershipBlock_next-bottom');
      $text = "'MembershipBlock' information has been saved.";
      $this->assertTrue($this->isTextPresent($text), 'Missing text: ' . $text);
    }

    // go to step 4 (thank-you and receipting)
    $this->click('link=Receipt');
    $this->waitForElementPresent('_qf_ThankYou_next-bottom');

    // fill in step 4
    $this->type('thankyou_title', "Thank-you Page Title $hash");
    // FIXME: handle Thank-you Message/Page Footer
    $this->type('receipt_from_name', "Receipt From Name $hash");
    $this->type('receipt_from_email', "$hash@example.org");
    $this->type('receipt_text', "Receipt Message $hash");
    $this->type('cc_receipt', "$hash@example.net");
    $this->type('bcc_receipt', "$hash@example.com");

    $this->click('_qf_ThankYou_next');
    $this->waitForElementPresent('_qf_ThankYou_next-bottom');
    $this->waitForPageToLoad('30000');
    $text = "'ThankYou' information has been saved.";
    $this->assertTrue($this->isTextPresent($text), 'Missing text: ' . $text);

    if ($friend) {
      // fill in step 5 (Tell a Friend)
      $this->click('link=Tell a Friend');
      $this->waitForElementPresent('_qf_Contribute_next-bottom');
      $this->click('tf_is_active');
      $this->type('tf_title', "TaF Title $hash");
      $this->type('intro', "TaF Introduction $hash");
      $this->type('suggested_message', "TaF Suggested Message $hash");
      $this->type('general_link', "TaF Info Page Link $hash");
      $this->type('tf_thankyou_title', "TaF Thank-you Title $hash");
      $this->type('tf_thankyou_text', "TaF Thank-you Message $hash");

      //$this->click('_qf_Contribute_next');
      $this->click('_qf_Contribute_next-bottom');
      $this->waitForPageToLoad('30000');
      $text = "'Friend' information has been saved.";
      $this->assertTrue($this->isTextPresent($text), 'Missing text: ' . $text);
    }

    if ($profilePreId || $profilePostId) {
      // fill in step 6 (Include Profiles)
      $this->click("css=li#tab_custom a");
      $this->waitForElementPresent('_qf_Custom_next-bottom');

      if ($profilePreId) {
        $this->select('custom_pre_id', "value={$profilePreId}");
      }

      if ($profilePostId) {
        $this->select('custom_post_id', "value={$profilePostId}");
      }

      $this->click('_qf_Custom_next-bottom');
      //$this->waitForElementPresent('_qf_Custom_next-bottom');

      $this->waitForPageToLoad('30000');
      $text = "'Custom' information has been saved.";
      $this->assertTrue($this->isTextPresent($text), 'Missing text: ' . $text);
    }

    if ($premiums) {
      // fill in step 7 (Premiums)
      $this->click('link=Premiums');
      $this->waitForElementPresent('_qf_Premium_next-bottom');
      $this->click('premiums_active');
      $this->type('premiums_intro_title', "Prem Title $hash");
      $this->type('premiums_intro_text', "Prem Introductory Message $hash");
      $this->type('premiums_contact_email', "$hash@example.info");
      $this->type('premiums_contact_phone', rand(100000000, 999999999));
      $this->click('premiums_display_min_contribution');

      $this->click('_qf_Premium_next');
      $this->waitForElementPresent('_qf_Premium_next-bottom');

      $this->waitForPageToLoad('30000');
      $text = "'Premium' information has been saved.";
      $this->assertTrue($this->isTextPresent($text), 'Missing text: ' . $text);
    }


    if ($widget) {
      // fill in step 8 (Widget Settings)
      $this->click('link=Widgets');
      $this->waitForElementPresent('_qf_Widget_next-bottom');

      $this->click('is_active');
      $this->type('url_logo', "URL to Logo Image $hash");
      $this->type('button_title', "Button Title $hash");
      // Type About text in ckEditor (fieldname, text to type, editor)
      $this->fillRichTextField('about', 'This is for ' . $pageTitle, 'CKEditor');

      $this->click('_qf_Widget_next');
      $this->waitForElementPresent('_qf_Widget_next-bottom');

      $this->waitForPageToLoad('30000');
      $text = "'Widget' information has been saved.";
      $this->assertTrue($this->isTextPresent($text), 'Missing text: ' . $text);
    }
    if ($pcp) {
      // fill in step 9 (Enable Personal Campaign Pages)
      $this->click('link=Personal Campaigns');
      $this->waitForElementPresent('_qf_Contribute_next-bottom');
      $this->click('pcp_active');
      if (!$isPcpApprovalNeeded) {
        $this->click('is_approval_needed');
      }
      $this->type('notify_email', "$hash@example.name");
      $this->select('supporter_profile_id', 'value=2');
      $this->type('tellfriend_limit', 7);
      $this->type('link_text', "'Create Personal Campaign Page' link text $hash");

      $this->click('_qf_Contribute_next-bottom');
      //$this->waitForElementPresent('_qf_PCP_next-bottom');
      $this->waitForPageToLoad('30000');
      $text = "'Pcp' information has been saved.";
      $this->assertTrue($this->isTextPresent($text), 'Missing text: ' . $text);
    }

    // parse URL to grab the contribution page id
    $elements = $this->parseURL();
    $pageId = $elements['queryString']['id'];

    // pass $pageId back to any other tests that call this class
    return $pageId;
  }

}