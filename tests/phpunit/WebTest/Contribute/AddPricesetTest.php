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
 
class WebTest_Contribute_AddPricesetTest extends CiviSeleniumTestCase
{

  protected function setUp()
  {
      parent::setUp();
  }

  function testAddPriceSet()
  {
      // This is the path where our testing install resides. 
      // The rest of URL is defined in CiviSeleniumTestCase base class, in
      // class attributes.
      $this->open( $this->sboxPath );

      // Log in using webtestLogin() method
      $this->webtestLogin();

      $setTitle = 'Conference Fees - '.substr(sha1(rand()), 0, 7);
      $usedFor = 'Contribution';
      $setHelp = 'Select your conference options.';
      $this->_testAddSet( $setTitle, $usedFor, $setHelp );

      // Get the price set id ($sid) by retrieving and parsing the URL of the New Price Field form
      // which is where we are after adding Price Set.
      $elements = $this->parseURL( );
      $sid = $elements['queryString']['sid'];
      $this->assertType( 'numeric', $sid );

      $validStrings = array( );

      $fields = array( 'Full Conference'        => 'Text',
                       'Meal Choice'            => 'Select',
                       'Pre-conference Meetup?' => 'Radio',
                       'Evening Sessions'       => 'CheckBox',
                     );
      $this->_testAddPriceFields( $fields, $validateStrings );
      // var_dump($validateStrings);
      
      // load the Price Set Preview and check for expected values
      $this->_testVerifyPriceSet( $validateStrings, $sid );      
  }

  function _testAddSet( $setTitle, $usedFor, $setHelp )
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
      }

      $this->type('help_pre', $setHelp);

      $this->assertChecked('is_active', 'Verify that Is Active checkbox is set.');
      $this->click('_qf_Set_next-bottom');      

      $this->waitForPageToLoad('30000');
      $this->waitForElementPresent('_qf_Field_next-bottom');
  }
  
  function _testAddPriceFields( &$fields, &$validateString, $dateSpecificFields = false  )
  {
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
          $this->click('_qf_Field_next_new-bottom');
          $this->waitForPageToLoad('30000');
          $this->waitForElementPresent('_qf_Field_next-bottom');
      }
  }
  
  function _testVerifyPriceSet( $validateStrings, $sid )
  {
      // verify Price Set at Preview page
      // start at Manage Price Sets listing
      $this->open($this->sboxPath . 'civicrm/admin/price?reset=1');
      $this->waitForPageToLoad('30000');
      
      // Use the price set id ($sid) to pick the correct row
      $this->click("css=tr#row_{$sid} a[title='Preview Price Set']");
      
      $this->waitForPageToLoad('30000');
      // Look for Register button
      $this->waitForElementPresent('_qf_Preview_cancel-bottom');
      
      // Check for expected price set field strings
      $this->assertStringsPresent( $validateStrings );
  }
  
  function testContributeOfflineWithPriceSet()
  {
      // This is the path where our testing install resides. 
      // The rest of URL is defined in CiviSeleniumTestCase base class, in
      // class attributes.
      $this->open( $this->sboxPath );
      
      // Log in using webtestLogin() method
      $this->webtestLogin();

      $setTitle = 'Conference Fees - '.substr(sha1(rand()), 0, 7);
      $usedFor = 'Contribution';
      $setHelp = 'Select your conference options.';
      $this->_testAddSet( $setTitle, $usedFor, $setHelp );
      
      // Get the price set id ($sid) by retrieving and parsing the URL of the New Price Field form
      // which is where we are after adding Price Set.
      $elements = $this->parseURL( );
      $sid = $elements['queryString']['sid'];
      $this->assertType( 'numeric', $sid );
      
      $validStrings = array( );
      $fields = array( 'Full Conference'        => 'Text',
                       'Meal Choice'            => 'Select',
                       'Pre-conference Meetup?' => 'Radio',
                       'Evening Sessions'       => 'CheckBox',
                       );
      $this->_testAddPriceFields( $fields, $validateStrings );
      
      // load the Price Set Preview and check for expected values
      $this->_testVerifyPriceSet( $validateStrings, $sid );      
            
      $this->open($this->sboxPath . 'civicrm/contribute/add?reset=1&action=add&context=standalone');

      // As mentioned before, waitForPageToLoad is not always reliable. Below, we're waiting for the submit
      // button at the end of this page to show up, to make sure it's fully loaded.
      $this->waitForElementPresent('_qf_Contribution_upload');

      // Let's start filling the form with values.
      
      // create new contact using dialog
      $firstName = substr(sha1(rand()), 0, 7);
      $this->webtestNewDialogContact( $firstName, 'Contributor', $firstName . '@example.com' );
      
      // select contribution type
      $this->select('contribution_type_id', 'value=1');
      
      // fill in Received Date
      $this->webtestFillDate('receive_date');
     
      // source
      $this->type('source', 'Mailer 1');
      
      // select price set items
      $this->select('price_set_id', "label=$setTitle");
      $this->type("xpath=//input[@class='form-text four required']", "1");
      $this->click("xpath=//input[@class='form-radio']");
      $this->click("xpath=//input[@class='form-checkbox']");
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
     
      // Clicking save.
      $this->click('_qf_Contribution_upload');
      $this->waitForPageToLoad('30000');
      
      // Is status message correct?
      $this->assertTrue($this->isTextPresent('The contribution record has been saved.'), "Status message didn't show up after saving!");

      $this->waitForElementPresent( "xpath=//div[@id='Contributions']//table//tbody/tr[1]/td[8]/span/a[text()='View']" );
      
      //click through to the Membership view screen
      $this->click( "xpath=//div[@id='Contributions']//table/tbody/tr[1]/td[8]/span/a[text()='View']" );
      $this->waitForElementPresent('_qf_ContributionView_cancel-bottom');

      $expected = array(
                        2  => 'Donation', 
                        3  => '590.00',
                        8  => 'Completed',
                        9  => 'Check',
                        10 => 'check #1041' );
      foreach ( $expected as $label => $value ) {
          $this->verifyText("xpath=id('ContributionView')/div[2]/table[1]/tbody/tr[$label]/td[2]", preg_quote($value));
      }
      
      $exp = array ( 
                    2 => '$ 525.00',
                    3 => '$ 50.00',
                    4 => '$ 15.00'
                     );
      
      foreach ( $exp as $lab => $val ) {
          $this->verifyText( "xpath=id('ContributionView')/div[2]/table[1]/tbody/tr[3]/td[2]/table/tbody/tr[$lab]/td[3]", 
                             preg_quote($val) );
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
      
      $setTitle = 'Conference Fees - '.substr(sha1(rand()), 0, 7);
      $usedFor = 'Contribution';
      $setHelp = 'Select your conference options.';
      $this->_testAddSet( $setTitle, $usedFor, $setHelp );
      
      // Get the price set id ($sid) by retrieving and parsing the URL of the New Price Field form
      // which is where we are after adding Price Set.
      $elements = $this->parseURL( );
      $sid = $elements['queryString']['sid'];
      $this->assertType( 'numeric', $sid );
      
      $validStrings = array( );
      $fields = array( 'Full Conference'        => 'Text',
                       'Meal Choice'            => 'Select',
                       'Pre-conference Meetup?' => 'Radio',
                       'Evening Sessions'       => 'CheckBox',
                       );
      //$this->_testAddPriceFields( $fields, $validateStrings, true );
      $this->_testAddPriceFields( $fields, $validateStrings );
      
      // load the Price Set Preview and check for expected values
      $this->_testVerifyPriceSet( $validateStrings, $sid );      
    
      // We need a payment processor
      $processorName = 'Webtest Dummy' . substr( sha1( rand( ) ), 0, 7 );
      $this->webtestAddPaymentProcessor( $processorName );
      
      $this->open( $this->sboxPath . 'civicrm/admin/contribute/add?reset=1&action=add' );
      
      $contributionTitle = substr( sha1( rand( ) ), 0, 7 );
      $rand = 2 * rand( 2, 50 );
        
      // fill in step 1 (Title and Settings)
      $contributionPageTitle = "Title $contributionTitle";
      $this->type( 'title', $contributionPageTitle );
      $this->select( 'contribution_type_id', 'value=1' );
      $this->fillRichTextField( 'intro_text','This is Test Introductory Message','CKEditor' );
      $this->fillRichTextField( 'footer_text','This is Test Footer Message','CKEditor' );
      
      // go to step 2
      $this->click( '_qf_Settings_next' );
      $this->waitForElementPresent( '_qf_Amount_next-bottom' );

      //this contribution page for online contribution 
      $this->select( 'payment_processor_id', 'label=' . $processorName );
      $this->select( 'price_set_id', 'label=' . $setTitle );
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
      
      //View Contribution Record
      $expected = array( 3  => 'Donation',  
                         3  => '590.00', 
                         7  => 'Completed', 
                         ); 
      foreach ( $expected as  $value => $label ) { 
          $this->verifyText("xpath=id('ContributionView')/div[2]/table[1]/tbody/tr[$value]/td[2]", preg_quote($label)); 
      }
  }

  function testContributeWithDateSpecificPriceSet()
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
      
      $setTitle = 'Conference Fees - '.substr(sha1(rand()), 0, 7);
      $usedFor = 'Contribution';
      $setHelp = 'Select your conference options.';
      $this->_testAddSet( $setTitle, $usedFor, $setHelp );
      
      // Get the price set id ($sid) by retrieving and parsing the URL of the New Price Field form
      // which is where we are after adding Price Set.
      $elements = $this->parseURL( );
      $sid = $elements['queryString']['sid'];
      $this->assertType( 'numeric', $sid );
      
      $validStrings = array( );
      $fields = array( 'Full Conference'        => 'Text',
                       'Meal Choice'            => 'Select',
                       'Pre-conference Meetup?' => 'Radio',
                       'Evening Sessions'       => 'CheckBox',
                       );
      $this->_testAddPriceFields( $fields, $validateStrings, true );
      //$this->_testAddPriceFields( $fields, $validateStrings );
      
      // load the Price Set Preview and check for expected values
      $this->_testVerifyPriceSet( $validateStrings, $sid );      
    
      // We need a payment processor
      $processorName = 'Webtest Dummy' . substr( sha1( rand( ) ), 0, 7 );
      $this->webtestAddPaymentProcessor( $processorName );
      
      $this->open( $this->sboxPath . 'civicrm/admin/contribute/add?reset=1&action=add' );
      
      $contributionTitle = substr( sha1( rand( ) ), 0, 7 );
      $rand = 2 * rand( 2, 50 );
        
      // fill in step 1 (Title and Settings)
      $contributionPageTitle = "Title $contributionTitle";
      $this->type( 'title', $contributionPageTitle );
      $this->select( 'contribution_type_id', 'value=1' );
      $this->fillRichTextField( 'intro_text','This is Test Introductory Message','CKEditor' );
      $this->fillRichTextField( 'footer_text','This is Test Footer Message','CKEditor' );
      
      // go to step 2
      $this->click( '_qf_Settings_next' );
      $this->waitForElementPresent( '_qf_Amount_next-bottom' );

      //this contribution page for online contribution 
      $this->select( 'payment_processor_id', 'label=' . $processorName );
      $this->select( 'price_set_id', 'label=' . $setTitle );
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
      $this->click("xpath=//input[@class='form-radio']");
      $this->click("xpath=//input[@class='form-checkbox']");
      
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
      $this->waitForElementPresent( '_qf_ContributionView_cancel-bottom' );
      
      //View Contribution Record
      $expected = array( 3  => 'Donation',  
                         3  => '65.00', 
                         7  => 'Completed', 
                         ); 
      foreach ( $expected as  $value => $label ) { 
          $this->verifyText("xpath=id('ContributionView')/div[2]/table[1]/tbody/tr[$value]/td[2]", preg_quote($label)); 
      }
  }
}
