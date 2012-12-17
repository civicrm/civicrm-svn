<?php
  /*
    +--------------------------------------------------------------------+
    | CiviCRM version 4.2                                                |
    +--------------------------------------------------------------------+
    | Copyright CiviCRM LLC (c) 2004-2012                                |
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
class WebTest_Financial_FinancialMembershipTest extends CiviSeleniumTestCase {

  protected function setUp() {
    parent::setUp();
  }
  function testOfflineMembership()
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
    
    $accountRelationship = "Asset Account is"; //Asset Account is - Income Account is
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
    $this->_testAddSet( $setTitle, $usedFor, $setHelp,$financialType['name']);

    // Get the price set id ($sid) by retrieving and parsing the URL of the New Price Field form
    // which is where we are after adding Price Set.
    $elements = $this->parseURL( );
    $sid = $elements['queryString']['sid'];

    $fields = array(
                    "Membership Item $title" => 'Text',
                    "National Membership $title" => 'Radio',
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
    $cid = explode('cid=', $this->getLocation());
    $cid=$cid[1];
      
    $returnArray= array(
                  'sid' =>$sid,
                  'contactParams' => $contactParams,
                  'memTypeTitle1' => $memTypeTitle1,
                  'memTypeTitle2' => $memTypeTitle2,
);
    return $returnArray;
  }

  function testOnlineMembership()
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
    
    $accountRelationship = "Asset Account is"; //Asset Account is - Income Account is
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
    $this->_testAddSet( $setTitle, $usedFor, $setHelp,$financialType['name']);

    // Get the price set id ($sid) by retrieving and parsing the URL of the New Price Field form
    // which is where we are after adding Price Set.
    $elements = $this->parseURL( );
    $sid = $elements['queryString']['sid'];
    // $this->assertType( 'numeric', $sid );

    $fields = array(       
                    "Membership Item $title" => 'Text',
                    "National Membership $title" => 'Radio',
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

    $contributionPageTitle = "Contribution Page $title";
    $paymentProcessor = "Webtest Dummy $title";
    $this->webtestAddContributionPage(NULL, NULL, $contributionPageTitle, array($paymentProcessor => 'Dummy','financial_type'=>$financialType['name']),
                                      TRUE, FALSE, FALSE, FALSE, FALSE, TRUE, $sid, FALSE, 1, NULL
                                      );

    // Sign up for membership
    $registerUrl = $this->_testVerifyRegisterPage($contributionPageTitle);

    $firstName = 'John_' . substr(sha1(rand()), 0, 7);
    $lastName  = 'Anderson_' . substr(sha1(rand()), 0, 7);
    $email     = "{$firstName}.{$lastName}@example.com";

    $contactParams = array(
                           'first_name' => $firstName,
                           'last_name' => $lastName,
                           'email-5' => $email,
                           );
    $this->_testOnlineSignUpOrRenewMembership($registerUrl, $contactParams, $memTypeTitle1, $memTypeTitle2);
    // Renew this membership
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
      case 'Text':
         $this->type('price', '150.00');
        break;
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

  function _testVerifyRegisterPage($contributionPageTitle) {
    $this->open($this->sboxPath . 'civicrm/admin/contribute?reset=1');
    $this->waitForElementPresent('_qf_SearchContribution_refresh');
    $this->type('title', $contributionPageTitle);
    $this->click('_qf_SearchContribution_refresh');
    $this->waitForPageToLoad('50000');
    $id          = $this->getAttribute("//div[@id='configure_contribution_page']//div[@class='dataTables_wrapper']/table/tbody/tr@id");
    $id          = explode('_', $id);
    $registerUrl = "civicrm/contribute/transact?reset=1&id=$id[1]";
    return $registerUrl;
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

  function _testVerifyPriceSet($validateStrings, $sid) {
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
    $this->assertStringsPresent($validateStrings);
  }

  function testOfflineSignUpOrRenewMembership() {
    //build the membership dates.
    $params = $this->testOfflineMembership();
    $sid = $params['sid'];
   $contactParams = $params['contactParams'];
   $memTypeTitle1 = $params['memTypeTitle1'];
   $memTypeTitle2 = $params['memTypeTitle2'];
   $renew = FALSE;
    require_once 'CRM/Core/Config.php';
    require_once 'CRM/Utils/Array.php';
    require_once 'CRM/Utils/Date.php';
    $currentYear  = date('Y');
    $currentMonth = date('m');
    $previousDay  = date('d') - 1;
    $endYear      = ($renew) ? $currentYear + 2 : $currentYear + 1;
    $joinDate     = date('Y-m-d', mktime(0, 0, 0, $currentMonth, date('d'), $currentYear));
    $startDate    = date('Y-m-d', mktime(0, 0, 0, $currentMonth, date('d'), $currentYear));
    $endDate      = date('Y-m-d', mktime(0, 0, 0, $currentMonth, $previousDay, $endYear));
    $configVars   = new CRM_Core_Config_Variables();
    foreach (array(
                   'joinDate', 'startDate', 'endDate') as $date) {
      $$date = CRM_Utils_Date::customFormat($$date, $configVars->dateformatFull);
    }

    if (!$renew) {    
      
      // Go directly to the URL of the screen that you will be testing (Activity Tab).
      $this->click('css=li#tab_member a');
      $this->waitForElementPresent('link=Add Membership');

      $this->click('link=Add Membership');
      $this->waitForElementPresent('_qf_Membership_cancel-bottom');

      $this->select('price_set_id', "value={$sid}");
      $this->waitForElementPresent('pricesetTotal');
      $this->type("xpath=//input[@class='form-text four']", "1");  
      $this->click("xpath=//div[@id='priceset']/div[3]/div[2]/div/span/input");
      $this->click("xpath=//div[@id='priceset']/div[4]/div[2]/div[2]/span/input");    
      $this->fireEvent("xpath=//input[@class='form-text four']", 'blur');   
      $this->type('source', 'Offline membership Sign Up Test Text');
      $this->click('_qf_Membership_upload-bottom');
    }
    else {
      $this->click("xpath=//div[@id='memberships']/div/table/tbody//tr/td[text()='{$memTypeTitle1}']/../td[8]/span[2][text()='more']/ul/li/a[text()='Renew']");
      $this->waitForElementPresent('_qf_MembershipRenewal_cancel-bottom');
      $this->click('_qf_MembershipRenewal_upload-bottom');

      $this->waitForElementPresent("xpath=//div[@id='memberships']/div/table/tbody/tr");
      $this->click("xpath=//div[@id='memberships']/div/table/tbody//tr/td[text()='{$memTypeTitle2}']/../td[8]/span[2][text()='more']/ul/li/a[text()='Renew']");
      $this->waitForElementPresent('_qf_MembershipRenewal_cancel-bottom');
      $this->click('_qf_MembershipRenewal_upload-bottom');
    }

    $this->waitForElementPresent("xpath=//div[@id='memberships']/div/table/tbody/tr");
    $this->click("xpath=//div[@id='memberships']/div/table/tbody//tr/td[text()='{$memTypeTitle1}']/../td[8]/span/a[text()='View']");
    $this->waitForElementPresent("_qf_MembershipView_cancel-bottom");
    //View Membership Record
    $verifyData = array(
                        'Membership Type' => "{$memTypeTitle1}",
                        'Status' => 'New',
                        'Member Since' => $joinDate,
                        'Start date' => $startDate,
                        'End date' => $endDate,
                        );
    foreach ($verifyData as $label => $value) {
      $this->verifyText("xpath=//form[@id='MembershipView']//table/tbody/tr/td[text()='{$label}']/following-sibling::td",
                        preg_quote($value)
                        );
    }

    $this->click('_qf_MembershipView_cancel-bottom');
    $this->waitForElementPresent("xpath=//div[@id='memberships']/div/table/tbody/tr");
    $this->click("xpath=//div[@id='memberships']/div/table/tbody//tr/td[text()='{$memTypeTitle2}']/../td[8]/span/a[text()='View']");
    $this->waitForElementPresent("_qf_MembershipView_cancel-bottom");

    //View Membership Record
    $verifyData = array(
                        'Membership Type' => "{$memTypeTitle2}",
                        'Status' => 'New',
                        'Member Since' => $joinDate,
                        'Start date' => $startDate,
                        'End date' => $endDate,
                        );
    foreach ($verifyData as $label => $value) {
      $this->verifyText("xpath=//form[@id='MembershipView']//table/tbody/tr/td[text()='{$label}']/following-sibling::td",
                        preg_quote($value)
                        );
    }
    $this->click("_qf_MembershipView_cancel-bottom");
    $this->waitForElementPresent("xpath=//div[@id='memberships']/div/table/tbody/tr");
    $this->waitForElementPresent("xpath=//div[@id='memberships']/div/table/tbody//tr/td[text()='{$memTypeTitle1}']/../td[8]/span/a[text()='Edit']");
    $this->click("xpath=//div[@id='memberships']/div/table/tbody//tr/td[text()='{$memTypeTitle1}']/../td[8]/span/a[text()='Edit']"); 
    $this->waitForElementPresent("xpath=//form[@id='Membership']/div[2]/div[2]/fieldset/table/tbody//tr//td[8]/span/a[text()='Edit']");
    $url = $this->getAttribute("xpath=//form[@id='Membership']/div[2]/div[2]/fieldset/table/tbody//tr/td[8]/span/a[text()='Edit']@href");
    $url = explode('&',$url);
    foreach($url as $value){
      $valueID = explode('=',$value);
      if($valueID[0] == 'id'){  
        $contribId = $valueID[1];
      }
    }
    $lineItem = CRM_Price_BAO_LineItem::getLineItems($contribId, 'contribution', 1);
    $this->click("xpath=//form[@id='Membership']/div[2]/div[2]/fieldset/table/tbody//tr/td[8]/span/a[text()='Edit']");
    $this->_testLineItem( $lineItem );
  }


  function _testOnlineSignUpOrRenewMembership($registerUrl, $contactParams, $memTypeTitle1, $memTypeTitle2, $renew = FALSE) {
    $this->open($this->sboxPath . 'civicrm/logout?reset=1');
    $this->waitForPageToLoad('30000');

    $this->open($this->sboxPath . $registerUrl);
    $this->waitForElementPresent('_qf_Main_upload-bottom');

    //build the membership dates.
    require_once 'CRM/Core/Config.php';
    require_once 'CRM/Utils/Array.php';
    require_once 'CRM/Utils/Date.php';
    $currentYear  = date('Y');
    $currentMonth = date('m');
    $previousDay  = date('d') - 1;
    $endYear      = ($renew) ? $currentYear + 2 : $currentYear + 1;
    $joinDate     = date('Y-m-d', mktime(0, 0, 0, $currentMonth, date('d'), $currentYear));
    $startDate    = date('Y-m-d', mktime(0, 0, 0, $currentMonth, date('d'), $currentYear));
    $endDate      = date('Y-m-d', mktime(0, 0, 0, $currentMonth, $previousDay, $endYear));
    $configVars   = new CRM_Core_Config_Variables();
    foreach (array(
                   'joinDate', 'startDate', 'endDate') as $date) {
      $$date = CRM_Utils_Date::customFormat($$date, $configVars->dateformatFull);
    }

    $this->click("xpath=//div[@id='priceset']/div[3]/div[2]/div/span/input");
    $this->click("xpath=//div[@id='priceset']/div[4]/div[2]/div[2]/span/input");

    $this->type('email-5', $contactParams['email-5']);
    $this->type('first_name', $contactParams['first_name']);
    $this->type('last_name', $contactParams['last_name']);

    $streetAddress = "100 Main Street";
    $this->type("street_address-1", $streetAddress);
    $this->type("city-1", "San Francisco");
    $this->type("postal_code-1", "94117");
    $this->select("country-1", "value=1228");
    $this->select("state_province-1", "value=1001");

    //Credit Card Info
    $this->select("credit_card_type", "value=Visa");
    $this->type("credit_card_number", "4111111111111111");
    $this->type("cvv2", "000");
    $this->select("credit_card_exp_date[M]", "value=1");
    $this->select("credit_card_exp_date[Y]", "value=2020");

    //Billing Info
    $this->type("billing_first_name", $contactParams['first_name'] . "billing");
    $this->type("billing_last_name", $contactParams['last_name'] . "billing");
    $this->type("billing_street_address-5", "15 Main St.");
    $this->type(" billing_city-5", "San Jose");
    $this->select("billing_country_id-5", "value=1228");
    $this->select("billing_state_province_id-5", "value=1004");
    $this->type("billing_postal_code-5", "94129");
    $this->click("_qf_Main_upload-bottom");

    $this->waitForPageToLoad('30000');
    $this->waitForElementPresent("_qf_Confirm_next-bottom");

    $this->click("_qf_Confirm_next-bottom");
    $this->waitForPageToLoad('30000');

    //login to check membership
    $this->open($this->sboxPath);

    // Log in using webtestLogin() method
    $this->webtestLogin();

    $this->open($this->sboxPath . "civicrm/member/search?reset=1");
    $this->waitForElementPresent("member_end_date_high");

    $this->type("sort_name", "{$contactParams['first_name']} {$contactParams['last_name']}");
    $this->click("_qf_Search_refresh");

    $this->waitForPageToLoad('30000');
    $this->assertTrue($this->isTextPresent("2 Results "));

    $this->waitForElementPresent("xpath=//div[@id='memberSearch']/table/tbody/tr");
    $this->click("xpath=//div[@id='memberSearch']/table/tbody//tr/td[4][text()='{$memTypeTitle1}']/../td[11]/span/a[text()='View']");
    $this->waitForElementPresent("_qf_MembershipView_cancel-bottom");

    //View Membership Record
    $verifyData = array(
                        'Membership Type' => "$memTypeTitle1",
                        'Status' => 'New',
                        'Member Since' => $joinDate,
                        'Start date' => $startDate,
                        'End date' => $endDate,
                        );
    foreach ($verifyData as $label => $value) {
      $this->verifyText("xpath=//form[@id='MembershipView']//table/tbody/tr/td[text()='{$label}']/following-sibling::td",
                        preg_quote($value)
                        );
    }

    $this->click('_qf_MembershipView_cancel-bottom');
    $this->waitForElementPresent("xpath=//div[@id='memberSearch']/table/tbody/tr[2]");
    $this->click("xpath=//div[@id='memberSearch']/table/tbody//tr/td[4][text()='{$memTypeTitle2}']/../td[11]/span/a[text()='View']");
    $this->waitForElementPresent("_qf_MembershipView_cancel-bottom");    
    
    //View Membership Record
    $verifyData = array(
                        'Membership Type' => "$memTypeTitle2",
                        'Status' => 'New',
                        'Member Since' => $joinDate,
                        'Start date' => $startDate,
                        'End date' => $endDate,
                        );
    foreach ($verifyData as $label => $value) {
      $this->verifyText("xpath=//form[@id='MembershipView']//table/tbody/tr/td[text()='{$label}']/following-sibling::td",
                        preg_quote($value)
                        );
    }
    
    $this->click("_qf_MembershipView_cancel-bottom");
    $this->waitForElementPresent("xpath=//div[@id='memberSearch']/table/tbody/");
    $this->waitForElementPresent("xpath=//div[@id='memberSearch']/table/tbody//tr/td[text()='{$memTypeTitle1}']/../td[11]/span/a[text()='Edit']");
    $this->click("xpath=//div[@id='memberSearch']/table/tbody//tr/td[text()='{$memTypeTitle1}']/../td[11]/span/a[text()='Edit']"); 
    $this->waitForElementPresent("xpath=//form[@id='Membership']/div[2]/div[2]/fieldset/table/tbody//tr//td[8]/span/a[text()='Edit']");
    $url = $this->getAttribute("xpath=//form[@id='Membership']/div[2]/div[2]/fieldset/table/tbody//tr/td[8]/span/a[text()='Edit']@href");
    $url = explode('&',$url);
    foreach($url as $value){
      $valueID = explode('=',$value);
      if($valueID[0] == 'id'){  
        $contribId = $valueID[1];
      }
    }
    $lineItem = CRM_Price_BAO_LineItem::getLineItems($contribId, 'contribution', 1);
    $this->click("xpath=//form[@id='Membership']/div[2]/div[2]/fieldset/table/tbody//tr/td[8]/span/a[text()='Edit']");
    $this->_testLineItem( $lineItem );

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
                                      $friend = TRUE,
                                      $profilePreId = 1,
                                      $profilePostId = 7,
                                      $premiums = TRUE,
                                      $widget = TRUE,
                                      $pcp = TRUE,
                                      $isAddPaymentProcessor = TRUE,
                                      $isPcpApprovalNeeded = FALSE,
                                      $isSeparatePayment = FALSE,
                                      $honoreeSection = TRUE,
                                      $allowOtherAmmount = TRUE,
                                      $isConfirmEnabled = TRUE
                                      ) {
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
      if( list($processorName, $processorType) = each($processor)) { 
        $this->webtestAddPaymentProcessor( $processorName, 'Dummy', null, $processor['financial_type'] );
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
    $this->select('financial_type_id', 'label='.$processor['financial_type']);
    // fill in step 2 (Processor, Pay Later, Amounts)
    unset($processor['financial_type']);
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

  function testMembershipDistributeEvenly() {
    //build the membership dates.
    $params = $this->testOfflineMembership();
   $sid = $params['sid'];
   $contactParams = $params['contactParams'];
   $memTypeTitle1 = $params['memTypeTitle1'];
   $memTypeTitle2 = $params['memTypeTitle2'];
   $renew = FALSE;
    require_once 'CRM/Core/Config.php';
    require_once 'CRM/Utils/Array.php';
    require_once 'CRM/Utils/Date.php';
    $currentYear  = date('Y');
    $currentMonth = date('m');
    $previousDay  = date('d') - 1;
    $endYear      = ($renew) ? $currentYear + 2 : $currentYear + 1;
    $joinDate     = date('Y-m-d', mktime(0, 0, 0, $currentMonth, date('d'), $currentYear));
    $startDate    = date('Y-m-d', mktime(0, 0, 0, $currentMonth, date('d'), $currentYear));
    $endDate      = date('Y-m-d', mktime(0, 0, 0, $currentMonth, $previousDay, $endYear));
    $configVars   = new CRM_Core_Config_Variables();
    foreach (array(
                   'joinDate', 'startDate', 'endDate') as $date) {
      $$date = CRM_Utils_Date::customFormat($$date, $configVars->dateformatFull);
    }

    if (!$renew) {
      // Go directly to the URL of the screen that you will be testing (Activity Tab).id('initialPayment')/x:table/x:tbody/x:tr[2]/x:td[2]
      $this->click('css=li#tab_member a');
      $this->waitForElementPresent('link=Add Membership');

      $this->click('link=Add Membership');
      $this->waitForElementPresent('_qf_Membership_cancel-bottom');

      $this->select('price_set_id', "value={$sid}");
      $this->waitForElementPresent('pricesetTotal');
      $this->type("xpath=//input[@class='form-text four']", "1");  
      $this->click("xpath=//div[@id='priceset']/div[3]/div[2]/div/span/input");
      $this->click("xpath=//div[@id='priceset']/div[4]/div[2]/div[2]/span/input");    
      $this->fireEvent("xpath=//input[@class='form-text four']", 'blur');         
      $this->click('int_amount'); 
      $this->type('initial_amount', '50.00');
      $this->click("CIVICRM_QFID_1_2");
      $this->fireEvent("initial_amount", 'blur');  
      $lineItem1 = $this->getAttribute("xpath= id('initialPayment')/table/tbody/tr[2]/td[2]/input[1]@value");
      $lineItem2 = $this->getAttribute("xpath= id('initialPayment')/table/tbody/tr[3]/td[2]/input[1]@value");
      $lineItem3 = $this->getAttribute("xpath= id('initialPayment')/table/tbody/tr[4]/td[2]/input[1]@value");
      $this->assertTrue(($lineItem1 == '25.00'), "LineItem amount incorrect");
      $this->assertTrue(($lineItem2 == '16.67'), "LineItem amount incorrect");
      $this->assertTrue(($lineItem3 == '8.33'), "LineItem amount incorrect");
      $this->type('source', 'Offline membership Sign Up Test Text');
      $this->click('_qf_Membership_upload-bottom');
    }
    else {
      $this->click("xpath=//div[@id='memberships']/div/table/tbody//tr/td[text()='{$memTypeTitle1}']/../td[8]/span[2][text()='more']/ul/li/a[text()='Renew']");
      $this->waitForElementPresent('_qf_MembershipRenewal_cancel-bottom');
      $this->click('_qf_MembershipRenewal_upload-bottom');

      $this->waitForElementPresent("xpath=//div[@id='memberships']/div/table/tbody/tr");
      $this->click("xpath=//div[@id='memberships']/div/table/tbody//tr/td[text()='{$memTypeTitle2}']/../td[8]/span[2][text()='more']/ul/li/a[text()='Renew']");
      $this->waitForElementPresent('_qf_MembershipRenewal_cancel-bottom');
      $this->click('_qf_MembershipRenewal_upload-bottom');
    }

    $this->waitForElementPresent("xpath=//div[@id='memberships']/div/table/tbody/tr");
    $this->click("xpath=//div[@id='memberships']/div/table/tbody//tr/td[text()='{$memTypeTitle1}']/../td[8]/span/a[text()='View']");
    $this->waitForElementPresent("_qf_MembershipView_cancel-bottom");
    //View Membership Record
    $verifyData = array(
                        'Membership Type' => "{$memTypeTitle1}",
                        'Status' => 'New',
                        'Member Since' => $joinDate,
                        'Start date' => $startDate,
                        'End date' => $endDate,
                        );
    foreach ($verifyData as $label => $value) {
      $this->verifyText("xpath=//form[@id='MembershipView']//table/tbody/tr/td[text()='{$label}']/following-sibling::td",
                        preg_quote($value)
                        );
    }

    $this->click('_qf_MembershipView_cancel-bottom');
    $this->waitForElementPresent("xpath=//div[@id='memberships']/div/table/tbody/tr");
    $this->click("xpath=//div[@id='memberships']/div/table/tbody//tr/td[text()='{$memTypeTitle2}']/../td[8]/span/a[text()='View']");
    $this->waitForElementPresent("_qf_MembershipView_cancel-bottom");

    //View Membership Record
    $verifyData = array(
                        'Membership Type' => "{$memTypeTitle2}",
                        'Status' => 'New',
                        'Member Since' => $joinDate,
                        'Start date' => $startDate,
                        'End date' => $endDate,
                        );
    foreach ($verifyData as $label => $value) {
      $this->verifyText("xpath=//form[@id='MembershipView']//table/tbody/tr/td[text()='{$label}']/following-sibling::td",
                        preg_quote($value)
                        );
    }
    $this->click("_qf_MembershipView_cancel-bottom");
    $this->waitForElementPresent("xpath=//div[@id='memberships']/div/table/tbody/tr");
    $this->waitForElementPresent("xpath=//div[@id='memberships']/div/table/tbody//tr/td[text()='{$memTypeTitle1}']/../td[8]/span/a[text()='Edit']");
    $this->click("xpath=//div[@id='memberships']/div/table/tbody//tr/td[text()='{$memTypeTitle1}']/../td[8]/span/a[text()='Edit']"); 
    $this->waitForElementPresent("xpath=//form[@id='Membership']/div[2]/div[2]/fieldset/table/tbody//tr//td[8]/span/a[text()='Edit']");
    $url = $this->getAttribute("xpath=//form[@id='Membership']/div[2]/div[2]/fieldset/table/tbody//tr/td[8]/span/a[text()='Edit']@href");
    $url = explode('&',$url);
    foreach($url as $value){
      $valueID = explode('=',$value);
      if($valueID[0] == 'id'){  
        $contribId = $valueID[1];
      }
    }
    $lineItem = CRM_Price_BAO_LineItem::getLineItems($contribId, 'contribution', 1);
    $this->click("xpath=//form[@id='Membership']/div[2]/div[2]/fieldset/table/tbody//tr/td[8]/span/a[text()='Edit']");
    $this->_testLineItem( $lineItem );
  }

  function testMembershipSingleAllocate() {
    //build the membership dates.
    $params = $this->testOfflineMembership();
    $sid = $params['sid'];
    $contactParams = $params['contactParams'];
    $memTypeTitle1 = $params['memTypeTitle1'];
    $memTypeTitle2 = $params['memTypeTitle2'];
    $renew = FALSE;
    require_once 'CRM/Core/Config.php';
    require_once 'CRM/Utils/Array.php';
    require_once 'CRM/Utils/Date.php';
    $currentYear  = date('Y');
    $currentMonth = date('m');
    $previousDay  = date('d') - 1;
    $endYear      = ($renew) ? $currentYear + 2 : $currentYear + 1;
    $joinDate     = date('Y-m-d', mktime(0, 0, 0, $currentMonth, date('d'), $currentYear));
    $startDate    = date('Y-m-d', mktime(0, 0, 0, $currentMonth, date('d'), $currentYear));
    $endDate      = date('Y-m-d', mktime(0, 0, 0, $currentMonth, $previousDay, $endYear));
    $configVars   = new CRM_Core_Config_Variables();
    foreach (array(
                   'joinDate', 'startDate', 'endDate') as $date) {
      $$date = CRM_Utils_Date::customFormat($$date, $configVars->dateformatFull);
    }
      // Go directly to the URL of the screen that you will be testing (Activity Tab).id('initialPayment')/x:table/x:tbody/x:tr[2]/x:td[2]
      $this->click('css=li#tab_member a');
      $this->waitForElementPresent('link=Add Membership');

      $this->click('link=Add Membership');
      $this->waitForElementPresent('_qf_Membership_cancel-bottom');

      $this->select('price_set_id', "value={$sid}");
      $this->waitForElementPresent('pricesetTotal');
      $this->type("xpath=//input[@class='form-text four']", "1");  
      $this->click("xpath=//div[@id='priceset']/div[3]/div[2]/div/span/input");
      $this->click("xpath=//div[@id='priceset']/div[4]/div[2]/div[2]/span/input");    
      $this->fireEvent("xpath=//input[@class='form-text four']", 'blur');         
      $this->click('int_amount'); 
      $this->type("xpath= id('initialPayment')/table/tbody/tr[3]/td[2]/input[1]",'25.00');
      $this->type('source', 'Offline membership Sign Up Test Text');
      $this->click('_qf_Membership_upload-bottom');
      
      $this->waitForElementPresent("xpath=//div[@id='memberships']/div/table/tbody/tr");
      $this->click("xpath=//div[@id='memberships']/div/table/tbody//tr/td[text()='{$memTypeTitle1}']/../td[8]/span/a[text()='View']");
      $this->waitForElementPresent("_qf_MembershipView_cancel-bottom");
      //View Membership Record
      $verifyData = array(
                          'Membership Type' => "{$memTypeTitle1}",
                        'Status' => 'New',
                        'Member Since' => $joinDate,
                        'Start date' => $startDate,
                        'End date' => $endDate,
                        );
    foreach ($verifyData as $label => $value) {
      $this->verifyText("xpath=//form[@id='MembershipView']//table/tbody/tr/td[text()='{$label}']/following-sibling::td",
                        preg_quote($value)
                        );
    }

    $this->click('_qf_MembershipView_cancel-bottom');
    $this->waitForElementPresent("xpath=//div[@id='memberships']/div/table/tbody/tr");
    $this->click("xpath=//div[@id='memberships']/div/table/tbody//tr/td[text()='{$memTypeTitle2}']/../td[8]/span/a[text()='View']");
    $this->waitForElementPresent("_qf_MembershipView_cancel-bottom");

    //View Membership Record
    $verifyData = array(
                        'Membership Type' => "{$memTypeTitle2}",
                        'Status' => 'New',
                        'Member Since' => $joinDate,
                        'Start date' => $startDate,
                        'End date' => $endDate,
                        );
    foreach ($verifyData as $label => $value) {
      $this->verifyText("xpath=//form[@id='MembershipView']//table/tbody/tr/td[text()='{$label}']/following-sibling::td",
                        preg_quote($value)
                        );
    }
    $this->click("_qf_MembershipView_cancel-bottom");
    $this->waitForElementPresent("xpath=//div[@id='memberships']/div/table/tbody/tr");
    $this->waitForElementPresent("xpath=//div[@id='memberships']/div/table/tbody//tr/td[text()='{$memTypeTitle1}']/../td[8]/span/a[text()='Edit']");
    $this->click("xpath=//div[@id='memberships']/div/table/tbody//tr/td[text()='{$memTypeTitle1}']/../td[8]/span/a[text()='Edit']"); 
    $this->waitForElementPresent("xpath=//form[@id='Membership']/div[2]/div[2]/fieldset/table/tbody//tr//td[8]/span/a[text()='Edit']");
    $url = $this->getAttribute("xpath=//form[@id='Membership']/div[2]/div[2]/fieldset/table/tbody//tr/td[8]/span/a[text()='Edit']@href");
    $url = explode('&',$url);
    foreach($url as $value){
      $valueID = explode('=',$value);
      if($valueID[0] == 'id'){  
        $contribId = $valueID[1];
      }
    }
    $lineItem = CRM_Price_BAO_LineItem::getLineItems($contribId, 'contribution', 1);
    $this->click("xpath=//form[@id='Membership']/div[2]/div[2]/fieldset/table/tbody//tr/td[8]/span/a[text()='Edit']");    
    $this->_testLineItem( $lineItem );
  }

 function testMembershipDoubleAllocate() {
    //build the membership dates.
   $params = $this->testOfflineMembership();
   $sid = $params['sid'];
   $contactParams = $params['contactParams'];
   $memTypeTitle1 = $params['memTypeTitle1'];
   $memTypeTitle2 = $params['memTypeTitle2'];
   $renew = FALSE;
    require_once 'CRM/Core/Config.php';
    require_once 'CRM/Utils/Array.php';
    require_once 'CRM/Utils/Date.php';
    $currentYear  = date('Y');
    $currentMonth = date('m');
    $previousDay  = date('d') - 1;
    $endYear      = ($renew) ? $currentYear + 2 : $currentYear + 1;
    $joinDate     = date('Y-m-d', mktime(0, 0, 0, $currentMonth, date('d'), $currentYear));
    $startDate    = date('Y-m-d', mktime(0, 0, 0, $currentMonth, date('d'), $currentYear));
    $endDate      = date('Y-m-d', mktime(0, 0, 0, $currentMonth, $previousDay, $endYear));
    $configVars   = new CRM_Core_Config_Variables();
    foreach (array(
                   'joinDate', 'startDate', 'endDate') as $date) {
      $$date = CRM_Utils_Date::customFormat($$date, $configVars->dateformatFull);
    }

    if (!$renew) {
      // Go directly to the URL of the screen that you will be testing (Activity Tab).id('initialPayment')/x:table/x:tbody/x:tr[2]/x:td[2]
      $this->click('css=li#tab_member a');
      $this->waitForElementPresent('link=Add Membership');

      $this->click('link=Add Membership');
      $this->waitForElementPresent('_qf_Membership_cancel-bottom');

      $this->select('price_set_id', "value={$sid}");
      $this->waitForElementPresent('pricesetTotal');
      $this->type("xpath=//input[@class='form-text four']", "1");  
      $this->click("xpath=//div[@id='priceset']/div[3]/div[2]/div/span/input");
      $this->click("xpath=//div[@id='priceset']/div[4]/div[2]/div[2]/span/input");    
      $this->fireEvent("xpath=//input[@class='form-text four']", 'blur');         
      $this->click('int_amount'); 
      $this->type("xpath= id('initialPayment')/table/tbody/tr[3]/td[2]/input[1]",'25.00');
      $this->type("xpath= id('initialPayment')/table/tbody/tr[4]/td[2]/input[1]",'25.00');
      $this->type('source', 'Offline membership Sign Up Test Text');
      $this->click('_qf_Membership_upload-bottom');
    }
    else {
      $this->click("xpath=//div[@id='memberships']/div/table/tbody//tr/td[text()='{$memTypeTitle1}']/../td[8]/span[2][text()='more']/ul/li/a[text()='Renew']");
      $this->waitForElementPresent('_qf_MembershipRenewal_cancel-bottom');
      $this->click('_qf_MembershipRenewal_upload-bottom');
      $this->waitForElementPresent("xpath=//div[@id='memberships']/div/table/tbody/tr");
      $this->click("xpath=//div[@id='memberships']/div/table/tbody//tr/td[text()='{$memTypeTitle2}']/../td[8]/span[2][text()='more']/ul/li/a[text()='Renew']");
      $this->waitForElementPresent('_qf_MembershipRenewal_cancel-bottom');
      $this->click('_qf_MembershipRenewal_upload-bottom');
    }

      $this->waitForElementPresent("xpath=//div[@id='memberships']/div/table/tbody/tr");
    $this->click("xpath=//div[@id='memberships']/div/table/tbody//tr/td[text()='{$memTypeTitle1}']/../td[8]/span/a[text()='View']");
    $this->waitForElementPresent("_qf_MembershipView_cancel-bottom");
    //View Membership Record
    $verifyData = array(
                        'Membership Type' => "{$memTypeTitle1}",
                        'Status' => 'New',
                        'Member Since' => $joinDate,
                        'Start date' => $startDate,
                        'End date' => $endDate,
                        );
    foreach ($verifyData as $label => $value) {
      $this->verifyText("xpath=//form[@id='MembershipView']//table/tbody/tr/td[text()='{$label}']/following-sibling::td",
                        preg_quote($value)
                        );
    }

    $this->click('_qf_MembershipView_cancel-bottom');
    $this->waitForElementPresent("xpath=//div[@id='memberships']/div/table/tbody/tr");
    $this->click("xpath=//div[@id='memberships']/div/table/tbody//tr/td[text()='{$memTypeTitle2}']/../td[8]/span/a[text()='View']");
    $this->waitForElementPresent("_qf_MembershipView_cancel-bottom");

    //View Membership Record
    $verifyData = array(
                        'Membership Type' => "{$memTypeTitle2}",
                        'Status' => 'New',
                        'Member Since' => $joinDate,
                        'Start date' => $startDate,
                        'End date' => $endDate,
                        );
    foreach ($verifyData as $label => $value) {
      $this->verifyText("xpath=//form[@id='MembershipView']//table/tbody/tr/td[text()='{$label}']/following-sibling::td",
                        preg_quote($value)
                        );
    }
    $this->click("_qf_MembershipView_cancel-bottom");
    $this->waitForElementPresent("xpath=//div[@id='memberships']/div/table/tbody/tr");
    $this->waitForElementPresent("xpath=//div[@id='memberships']/div/table/tbody//tr/td[text()='{$memTypeTitle1}']/../td[8]/span/a[text()='Edit']");
    $this->click("xpath=//div[@id='memberships']/div/table/tbody//tr/td[text()='{$memTypeTitle1}']/../td[8]/span/a[text()='Edit']"); 
    $this->waitForElementPresent("xpath=//form[@id='Membership']/div[2]/div[2]/fieldset/table/tbody//tr//td[8]/span/a[text()='Edit']");
    $url = $this->getAttribute("xpath=//form[@id='Membership']/div[2]/div[2]/fieldset/table/tbody//tr/td[8]/span/a[text()='Edit']@href");
    $url = explode('&',$url);
    foreach($url as $value){
      $valueID = explode('=',$value);
      if($valueID[0] == 'id'){  
        $contribId = $valueID[1];
      }
    }
    $lineItem = CRM_Price_BAO_LineItem::getLineItems($contribId, 'contribution', 1);
    $this->click("xpath=//form[@id='Membership']/div[2]/div[2]/fieldset/table/tbody//tr/td[8]/span/a[text()='Edit']");    
    $this->_testLineItem( $lineItem );
  }

  function testMembershipRemoveLineItem () {
    //build the membership dates.
    $params = $this->testOfflineMembership();
    $sid = $params['sid'];
    $contactParams = $params['contactParams'];
    $memTypeTitle1 = $params['memTypeTitle1'];
    $memTypeTitle2 = $params['memTypeTitle2'];
    $renew = FALSE;
    require_once 'CRM/Core/Config.php';
    require_once 'CRM/Utils/Array.php';
    require_once 'CRM/Utils/Date.php';
    $currentYear  = date('Y');
    $currentMonth = date('m');
    $previousDay  = date('d') - 1;
    $endYear      = ($renew) ? $currentYear + 2 : $currentYear + 1;
    $joinDate     = date('Y-m-d', mktime(0, 0, 0, $currentMonth, date('d'), $currentYear));
    $startDate    = date('Y-m-d', mktime(0, 0, 0, $currentMonth, date('d'), $currentYear));
    $endDate      = date('Y-m-d', mktime(0, 0, 0, $currentMonth, $previousDay, $endYear));
    $configVars   = new CRM_Core_Config_Variables();
    foreach (array(
                   'joinDate', 'startDate', 'endDate') as $date) {
      $$date = CRM_Utils_Date::customFormat($$date, $configVars->dateformatFull);
    }

    if (!$renew) {
      // Go directly to the URL of the screen that you will be testing (Activity Tab).id('initialPayment')/x:table/x:tbody/x:tr[2]/x:td[2]
      $this->click('css=li#tab_member a');
      $this->waitForElementPresent('link=Add Membership');

      $this->click('link=Add Membership');
      $this->waitForElementPresent('_qf_Membership_cancel-bottom');

      $this->select('price_set_id', "value={$sid}");
      $this->waitForElementPresent('pricesetTotal');  
      //$this->click("xpath=//input[@class='form-radio']");
      $this->type("xpath=//input[@class='form-text four']", '1');   
         $this->click("xpath=//input[@class='form-checkbox']");      
      $this->fireEvent("xpath=//input[@class='form-text four']", 'blur');   
      //$this->click("xpath=//div[@id='priceset']/div[2]/div[2]/div/span/input");
      //$this->click("xpath=//div[@id='priceset']/div[3]/div[2]/div[2]/span/input");    
      $this->click('int_amount'); 
      $this->type('initial_amount', '50.00');
      $this->click("CIVICRM_QFID_1_2");
      $this->fireEvent("initial_amount", 'blur');  
      $lineItem1 = $this->getAttribute("xpath= id('initialPayment')/table/tbody/tr[2]/td[2]/input[1]@value");
      $lineItem2 = $this->getAttribute("xpath= id('initialPayment')/table/tbody/tr[3]/td[2]/input[1]@value");
      $this->assertTrue(($lineItem1 == '30.00'), "LineItem amount incorrect"); 
      $this->assertTrue(($lineItem2 == '20.00'), "LineItem amount incorrect");
      $this->type('source', 'Offline membership Sign Up Test Text');
      $this->click('_qf_Membership_upload-bottom');
    }
    else {
      $this->click("xpath=//div[@id='memberships']/div/table/tbody//tr/td[text()='{$memTypeTitle1}']/../td[8]/span[2][text()='more']/ul/li/a[text()='Renew']");
      $this->waitForElementPresent('_qf_MembershipRenewal_cancel-bottom');
      $this->click('_qf_MembershipRenewal_upload-bottom');

      $this->waitForElementPresent("xpath=//div[@id='memberships']/div/table/tbody/tr");
      $this->click("xpath=//div[@id='memberships']/div/table/tbody//tr/td[text()='{$memTypeTitle2}']/../td[8]/span[2][text()='more']/ul/li/a[text()='Renew']");
      $this->waitForElementPresent('_qf_MembershipRenewal_cancel-bottom');
      $this->click('_qf_MembershipRenewal_upload-bottom');
    }

    $this->waitForElementPresent("xpath=//div[@id='memberships']/div/table/tbody/tr");
    /* CRM_Core_Error::debug( '$memTypeTitle2', $memTypeTitle2 ); */
    /* CRM_Core_Error::debug( '$memTypeTitle2', $memTypeTitle1 ); */
    $this->waitForElementPresent("xpath=//div[@id='memberships']/div/table/tbody//tr/td[text()='{$memTypeTitle1}']");
    $this->click("xpath=//div[@id='memberships']/div/table/tbody//tr/td[text()='{$memTypeTitle1}']/../td[8]/span/a[text()='View']");
    $this->waitForElementPresent("_qf_MembershipView_cancel-bottom");
    //View Membership Record
    $verifyData = array(
                        'Membership Type' => "{$memTypeTitle1}",
                        'Status' => 'New',
                        'Member Since' => $joinDate,
                        'Start date' => $startDate,
                        'End date' => $endDate,
                        );
    foreach ($verifyData as $label => $value) {
      $this->verifyText("xpath=//form[@id='MembershipView']//table/tbody/tr/td[text()='{$label}']/following-sibling::td",
                        preg_quote($value)
                        );
    }

    $this->click('_qf_MembershipView_cancel-bottom');
    $this->waitForElementPresent("xpath=//div[@id='memberships']/div/table/tbody/tr");
    $this->waitForElementPresent("xpath=//div[@id='memberships']/div/table/tbody//tr/td[text()='{$memTypeTitle1}']/../td[8]/span/a[text()='Edit']");
    $this->click("xpath=//div[@id='memberships']/div/table/tbody//tr/td[text()='{$memTypeTitle1}']/../td[8]/span/a[text()='Edit']"); 
    $this->waitForElementPresent("xpath=//form[@id='Membership']/div[2]/div[2]/fieldset/table/tbody//tr//td[8]/span/a[text()='Edit']");
    $url = $this->getAttribute("xpath=//form[@id='Membership']/div[2]/div[2]/fieldset/table/tbody//tr/td[8]/span/a[text()='Edit']@href");
    $url = explode('&',$url);
    foreach($url as $value){
      $valueID = explode('=',$value);
      if($valueID[0] == 'id'){  
        $contribId = $valueID[1];
      }
    }
    $lineItem = CRM_Price_BAO_LineItem::getLineItems($contribId, 'contribution', 1);
    $this->click("xpath=//form[@id='Membership']/div[2]/div[2]/fieldset/table/tbody//tr/td[8]/span/a[text()='Edit']");
    $this->_testLineItem( $lineItem );
  }

  function testMembershipOverPayment() {
    //build the membership dates.
    $params = $this->testOfflineMembership();
   $sid = $params['sid'];
   $contactParams = $params['contactParams'];
   $memTypeTitle1 = $params['memTypeTitle1'];
   $memTypeTitle2 = $params['memTypeTitle2'];
   $renew = FALSE;
    require_once 'CRM/Core/Config.php';
    require_once 'CRM/Utils/Array.php';
    require_once 'CRM/Utils/Date.php';
    $currentYear  = date('Y');
    $currentMonth = date('m');
    $previousDay  = date('d') - 1;
    $endYear      = ($renew) ? $currentYear + 2 : $currentYear + 1;
    $joinDate     = date('Y-m-d', mktime(0, 0, 0, $currentMonth, date('d'), $currentYear));
    $startDate    = date('Y-m-d', mktime(0, 0, 0, $currentMonth, date('d'), $currentYear));
    $endDate      = date('Y-m-d', mktime(0, 0, 0, $currentMonth, $previousDay, $endYear));
    $configVars   = new CRM_Core_Config_Variables();
    foreach (array(
                   'joinDate', 'startDate', 'endDate') as $date) {
      $$date = CRM_Utils_Date::customFormat($$date, $configVars->dateformatFull);
    }

      // Go directly to the URL of the screen that you will be testing (Activity Tab).id('initialPayment')/x:table/x:tbody/x:tr[2]/x:td[2]
      $this->click('css=li#tab_member a');
      $this->waitForElementPresent('link=Add Membership');

      $this->click('link=Add Membership');
      $this->waitForElementPresent('_qf_Membership_cancel-bottom'); 
      $this->select('price_set_id', "value={$sid}");
      $this->waitForElementPresent('pricesetTotal');  
      $this->type("xpath=//input[@class='form-text four']", "1");       
      $this->click("xpath=//div[@id='priceset']/div[3]/div[2]/div/span/input");
      $this->click("xpath=//div[@id='priceset']/div[4]/div[2]/div[2]/span/input");      
      $this->fireEvent("xpath=//input[@class='form-text four']", 'blur');    
      $this->click('int_amount'); 
      $this->type('initial_amount', '300.00');
      $this->click("CIVICRM_QFID_1_2");
      $this->type("xpath=//input[@class='form-text four valid']", ""); 
      $this->click("xpath=//div[@id='priceset']/div[3]/div[1]/label"); 
      $this->fireEvent("xpath=//select[@class='form-select']", 'focus'); 
      $this->fireEvent("xpath=//input[@class='form-text four valid']", 'blur');
      $this->click("xpath=//input[@class='form-text four valid']");
      $lineItem1 = $this->getAttribute("xpath= id('initialPayment')/table/tbody/tr[3]/td[2]/input[1]@value");
      $lineItem2 = $this->getAttribute("xpath= id('initialPayment')/table/tbody/tr[4]/td[2]/input[1]@value");
      $this->assertTrue(($lineItem1 == '200.00'), "LineItem amount incorrect"); 
      $this->assertTrue(($lineItem2 == '100.00'), "LineItem amount incorrect");
      $this->type('source', 'Offline membership Sign Up Test Text');
      $this->click('_qf_Membership_upload-bottom');
      $this->waitForPageToLoad('30000');
      $this->assertTrue( $this->isTextPresent("Initial Amount is greater than base Amount."), "Validation message for overpayment did not showed up");
  }


 function testMembershipAddAndRemoveLineItem() {
    //build the membership dates.
   $params = $this->testOfflineMembership();
   $sid = $params['sid'];
   $contactParams = $params['contactParams'];
   $memTypeTitle1 = $params['memTypeTitle1'];
   $memTypeTitle2 = $params['memTypeTitle2'];
   $renew = FALSE;
    require_once 'CRM/Core/Config.php';
    require_once 'CRM/Utils/Array.php';
    require_once 'CRM/Utils/Date.php';
    $currentYear  = date('Y');
    $currentMonth = date('m');
    $previousDay  = date('d') - 1;
    $endYear      = ($renew) ? $currentYear + 2 : $currentYear + 1;
    $joinDate     = date('Y-m-d', mktime(0, 0, 0, $currentMonth, date('d'), $currentYear));
    $startDate    = date('Y-m-d', mktime(0, 0, 0, $currentMonth, date('d'), $currentYear));
    $endDate      = date('Y-m-d', mktime(0, 0, 0, $currentMonth, $previousDay, $endYear));
    $configVars   = new CRM_Core_Config_Variables();
    foreach (array(
                   'joinDate', 'startDate', 'endDate') as $date) {
      $$date = CRM_Utils_Date::customFormat($$date, $configVars->dateformatFull);
    }

    if (!$renew) {
      // Go directly to the URL of the screen that you will be testing (Activity Tab).id('initialPayment')/x:table/x:tbody/x:tr[2]/x:td[2]
      $this->click('css=li#tab_member a');
      $this->waitForElementPresent('link=Add Membership');
      $this->click('link=Add Membership');
      $this->waitForElementPresent('_qf_Membership_cancel-bottom'); 
      $this->select('price_set_id', "value={$sid}");
      $this->waitForElementPresent('pricesetTotal');  
      $this->type("xpath=//input[@class='form-text four']", "1");
      $this->click("xpath=//div[@id='priceset']/div[4]/div[2]/div[2]/span/input");      
      $this->fireEvent("xpath=//input[@class='form-text four']", 'blur');    
      $this->click('int_amount'); 
      $this->type('initial_amount', '100.00');
      $this->click("CIVICRM_QFID_1_2");
      $this->type("xpath=//input[@class='form-text four valid']", "");        
      $this->click("xpath=//div[@id='priceset']/div[3]/div[2]/div/span/input");
      $this->click("xpath=//div[@id='priceset']/div[3]/div[1]/label"); 
      $this->fireEvent("xpath=//select[@class='form-select']", 'focus'); 
      $this->fireEvent("xpath=//input[@class='form-text four valid']", 'blur');
      $this->click("xpath=//input[@class='form-text four valid']");
      $lineItem1 = $this->getAttribute("xpath= id('initialPayment')/table/tbody/tr[3]/td[2]/input[1]@value");
      $lineItem2 = $this->getAttribute("xpath= id('initialPayment')/table/tbody/tr[4]/td[2]/input[1]@value");
      $this->assertTrue(($lineItem1 == '66.67'), "LineItem amount incorrect"); 
      $this->assertTrue(($lineItem2 == '33.33'), "LineItem amount incorrect");
      $this->type('source', 'Offline membership Sign Up Test Text');
      $this->click('_qf_Membership_upload-bottom');
    }
    else {
      $this->click("xpath=//div[@id='memberships']/div/table/tbody//tr/td[text()='{$memTypeTitle1}']/../td[8]/span[2][text()='more']/ul/li/a[text()='Renew']");
      $this->waitForElementPresent('_qf_MembershipRenewal_cancel-bottom');
      $this->click('_qf_MembershipRenewal_upload-bottom');
      
      $this->waitForElementPresent("xpath=//div[@id='memberships']/div/table/tbody/tr");
      $this->click("xpath=//div[@id='memberships']/div/table/tbody//tr/td[text()='{$memTypeTitle2}']/../td[8]/span[2][text()='more']/ul/li/a[text()='Renew']");
      $this->waitForElementPresent('_qf_MembershipRenewal_cancel-bottom');
      $this->click('_qf_MembershipRenewal_upload-bottom');
    }
    $this->waitForElementPresent("xpath=//div[@id='memberships']/div/table/tbody/tr");
    $this->click("xpath=//div[@id='memberships']/div/table/tbody//tr/td[text()='{$memTypeTitle1}']/../td[8]/span/a[text()='View']");
    $this->waitForElementPresent("_qf_MembershipView_cancel-bottom");
    //View Membership Record
    $verifyData = array(
                        'Membership Type' => "{$memTypeTitle1}",
                        'Status' => 'New',
                        'Member Since' => $joinDate,
                        'Start date' => $startDate,
                        'End date' => $endDate,
                        );
    foreach ($verifyData as $label => $value) {
      $this->verifyText("xpath=//form[@id='MembershipView']//table/tbody/tr/td[text()='{$label}']/following-sibling::td",
                        preg_quote($value)
                        );
    }

    $this->click('_qf_MembershipView_cancel-bottom');
    $this->waitForElementPresent("xpath=//div[@id='memberships']/div/table/tbody/tr");
    $this->click("xpath=//div[@id='memberships']/div/table/tbody//tr/td[text()='{$memTypeTitle2}']/../td[8]/span/a[text()='View']");
    $this->waitForElementPresent("_qf_MembershipView_cancel-bottom");

    //View Membership Record
    $verifyData = array(
                        'Membership Type' => "{$memTypeTitle2}",
                        'Status' => 'New',
                        'Member Since' => $joinDate,
                        'Start date' => $startDate,
                        'End date' => $endDate,
                        );
    foreach ($verifyData as $label => $value) {
      $this->verifyText("xpath=//form[@id='MembershipView']//table/tbody/tr/td[text()='{$label}']/following-sibling::td",
                        preg_quote($value)
                        );
    }
    $this->click("_qf_MembershipView_cancel-bottom");
    $this->waitForElementPresent("xpath=//div[@id='memberships']/div/table/tbody/tr");
    $this->waitForElementPresent("xpath=//div[@id='memberships']/div/table/tbody//tr/td[text()='{$memTypeTitle1}']/../td[8]/span/a[text()='Edit']");
    $this->click("xpath=//div[@id='memberships']/div/table/tbody//tr/td[text()='{$memTypeTitle1}']/../td[8]/span/a[text()='Edit']"); 
    $this->waitForElementPresent("xpath=//form[@id='Membership']/div[2]/div[2]/fieldset/table/tbody//tr//td[8]/span/a[text()='Edit']");
    $url = $this->getAttribute("xpath=//form[@id='Membership']/div[2]/div[2]/fieldset/table/tbody//tr/td[8]/span/a[text()='Edit']@href");
    $url = explode('&',$url);
    foreach($url as $value){
      $valueID = explode('=',$value);
      if($valueID[0] == 'id'){  
        $contribId = $valueID[1];
      }
    }
    $lineItem = CRM_Price_BAO_LineItem::getLineItems($contribId, 'contribution', 1);
    $this->click("xpath=//form[@id='Membership']/div[2]/div[2]/fieldset/table/tbody//tr/td[8]/span/a[text()='Edit']");
    $this->_testLineItem( $lineItem );
  }

}

