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
 | License and the CiviCRM Licensing Exception along                  |
 | with this program; if not, contact CiviCRM LLC                     |
 | at info[AT]civicrm[DOT]org. If you have questions about the        |
 | GNU Affero General Public License or the licensing of CiviCRM,     |
 | see the CiviCRM license FAQ at http://civicrm.org/licensing        |
 +--------------------------------------------------------------------+
*/

require_once 'PHPUnit/Extensions/SeleniumTestCase.php';

/**
 *  Include configuration
 */
require_once 'tests/phpunit/CiviTest/civicrm.settings.php';

/**
 *  Base class for CiviCRM Selenium tests
 *
 *  Common functions for unit tests
 *  @package CiviCRM
 */
class CiviSeleniumTestCase extends PHPUnit_Extensions_SeleniumTestCase {

    //    protected $coverageScriptUrl = 'http://tests.dev.civicrm.org/drupal/phpunit_coverage.php';

    /**
     *  Constructor
     *
     *  Because we are overriding the parent class constructor, we
     *  need to show the same arguments as exist in the constructor of
     *  PHPUnit_Framework_TestCase, since
     *  PHPUnit_Framework_TestSuite::createTest() creates a
     *  ReflectionClass of the Test class and checks the constructor
     *  of that class to decide how to set up the test.
     *
     *  @param  string $name
     *  @param  array  $data
     *  @param  string $dataName
     */
    function __construct($name = NULL, array $data = array(), $dataName = '', array $browser = array() ) {
        parent::__construct($name, $data, $dataName, $browser);
        
        require_once 'CiviSeleniumSettings.php';
        $this->settings = new CiviSeleniumSettings();

        // also initialize a connection to the db 
        require_once 'CRM/Core/Config.php';
        $config = CRM_Core_Config::singleton( );
    }

    protected function setUp()
    {
        $this->setBrowser( $this->settings->browser );
        // Make sure that below strings have path separator at the end
        $this->setBrowserUrl( $this->settings->sandboxURL);
        $this->sboxPath = $this->settings->sandboxPATH;
    }

    protected function tearDown()
    {
        //        $this->open( $this->settings->sandboxPATH . "logout?reset=1");
    }

    /**
     */
    function webtestLogin( $admin = false ) {
		$this->open("{$this->sboxPath}user");
        $password = $admin ? $this->settings->adminPassword : $this->settings->password;
        $username = $admin ? $this->settings->adminUsername : $this->settings->username;
        // Make sure login form is available
        $this->waitForElementPresent('edit-submit');
        $this->type('edit-name', $username);
        $this->type('edit-pass', $password);
        $this->click('edit-submit');
        $this->waitForPageToLoad('30000');      
    }

    /**
     * Add a contact with the given first and last names and either a given email
     * (when specified), a random email (when true) or no email (when unspecified or null).
     *
     * @param string $fname contact’s first name
     * @param string $lname contact’s last name
     * @param mixed  $email contact’s email (when string) or random email (when true) or no email (when null)
     *
     * @return mixed either a string with the (either generated or provided) email or null (if no email)
     */

    
	function webtest_civicrm_api($entity,$action, $params){
		$CiviSeleniumSettings = new CiviSeleniumSettings;
		$url_params = array_merge(array('json'=>1, 'key' => $CiviSeleniumSettings->sandboxSITEKEY, 'api_key'=> $CiviSeleniumSettings->apikey, 'action' => $action, 'entity' => $entity), $params);
		$url_query = http_build_query($url_params);
		$settingsBaseURL = parse_url($CiviSeleniumSettings->sandboxURL);
		$url = $CiviSeleniumSettings->sandboxURL . '/sites/all/modules/civicrm/extern/rest.php?' . $url_query;    
		$result = json_decode(file_get_contents(($url)), TRUE);
		return $result;
	}

	function webtestGetFirstValueForOptionGroup($option_group_name){
      	$result=$this->webtest_civicrm_api("OptionValue", "getvalue", array('option_group_name'=>$option_group_name,'option.limit'=>1,'return'=>'value'));
		return $result['result'];
	}

	function webtestGetValidCountryID(){
		$config_backend=$this->webtestGetConfig('countryLimit');
		return current($config_backend);
	}

	function webtestGetValidEntityID($entity){
		//michaelmcandrew: would like to use getvalue but there is a bug for e.g. group where option.limit not working at the moment CRM-9110
      	$result=$this->webtest_civicrm_api($entity, "get", array('option.limit'=>1,'return'=>'id'));
		return current(array_keys($result['values']));
	}

	function webtestGetConfig($field){
      	$result=$this->webtest_civicrm_api("Domain", "getvalue", array('option.limit'=>1,'return'=>'config_backend'));
		$config_backend = unserialize($result['result']);
		return $config_backend[$field];
	}


	function webtestAddContact( $fname = 'Anthony', $lname = 'Anderson', $email = null ) {
        $this->open($this->sboxPath . 'civicrm/contact/add?reset=1&ct=Individual');
        $this->waitForElementPresent('_qf_Contact_upload_view-bottom');
        $this->type('first_name', $fname);
        $this->type('last_name', $lname);
        if ($email === true) $email = substr(sha1(rand()), 0, 7) . '@example.org';
        if ($email) $this->type('email_1_email', $email);
        $this->click('_qf_Contact_upload_view-bottom');
        $this->waitForPageToLoad('30000');        
        return $email;
    }

    function webtestAddHousehold( $householdName = "Smith's Home", $email = null ) {
        
        $this->open($this->sboxPath . 'civicrm/contact/add&reset=1&ct=Household');
        $this->click('household_name');
        $this->type('household_name', $householdName );

        if ($email === true) $email = substr(sha1(rand()), 0, 7) . '@example.org';
        if ($email) $this->type('email_1_email', $email);

        $this->click('_qf_Contact_upload_view');
        $this->waitForPageToLoad('30000');        
        return $email;
    }


    function webtestAddOrganization( $organizationName = "Smith's Home", $email = null ) {
        
        $this->open($this->sboxPath . 'civicrm/contact/add?reset=1&ct=Organization');
        $this->click('organization_name');
        $this->type('organization_name', $organizationName );

        if ($email === true) $email = substr(sha1(rand()), 0, 7) . '@example.org';
        if ($email) $this->type('email_1_email', $email);

        $this->click('_qf_Contact_upload_view');
        $this->waitForPageToLoad('30000');        
        return $email;
    }

    /**
     */
    function webtestFillAutocomplete( $sortName ) {
        $this->typeKeys('contact_1', $sortName);
        $this->waitForElementPresent("css=div.ac_results-inner li");
        $this->click("css=div.ac_results-inner li");
        $this->assertContains($sortName, $this->getValue('contact_1'), "autocomplete expected $sortName but didn’t find it in " . $this->getValue('contact_1'));
    }
     /**
     */
    function webtestOrganisationAutocomplete( $sortName ) {
        $this->type('organisation_name', $sortName);
        $this->click('organisation_name');
        $this->waitForElementPresent("css=div.ac_results-inner li");
        $this->click("css=div.ac_results-inner li");
        //$this->assertContains($sortName, $this->getValue('contact_1'), "autocomplete expected $sortName but didn’t find it in " . $this->getValue('contact_1'));
    }
    

    /*
     * 1. By default, when no strtotime arg is specified, sets date to "now + 1 month"
     * 2. Does not set time. For setting both date and time use webtestFillDateTime() method.
     * 3. Examples of $strToTime arguments -
     *        webtestFillDate('start_date',"now")
     *        webtestFillDate('start_date',"10 September 2000")
     *        webtestFillDate('start_date',"+1 day")
     *        webtestFillDate('start_date',"+1 week")
     *        webtestFillDate('start_date',"+1 week 2 days 4 hours 2 seconds")
     *        webtestFillDate('start_date',"next Thursday")
     *        webtestFillDate('start_date',"last Monday")
     */
    function webtestFillDate( $dateElement, $strToTimeArgs = null ) {
        $timeStamp = strtotime($strToTimeArgs ? $strToTimeArgs : '+1 month');

        $year = date('Y', $timeStamp);
        $mon  = date('n', $timeStamp) - 1; // -1 ensures month number is inline with calender widget's month
        $day  = date('j', $timeStamp);

        $this->click ("{$dateElement}_display");
        $this->waitForElementPresent("css=div#ui-datepicker-div.ui-datepicker div.ui-datepicker-header div.ui-datepicker-title select.ui-datepicker-month");
        $this->select("css=div#ui-datepicker-div.ui-datepicker div.ui-datepicker-header div.ui-datepicker-title select.ui-datepicker-month", "value=$mon");
        $this->select("css=div#ui-datepicker-div div.ui-datepicker-header div.ui-datepicker-title select.ui-datepicker-year", "value=$year");
        $this->click ("link=$day");
    }
    // 1. set both date and time.
    function webtestFillDateTime( $dateElement, $strToTimeArgs = null ) {
        $this->webtestFillDate( $dateElement, $strToTimeArgs );

        $timeStamp = strtotime($strToTimeArgs ? $strToTimeArgs : '+1 month');
        $hour = date('h', $timeStamp);
        $min  = date('i', $timeStamp);
        $meri = date('A', $timeStamp);
       
        $this->type("{$dateElement}_time", "{$hour}:{$min}{$meri}");
    }

    /**
     * Verify that given label/value pairs are in *sibling* td cells somewhere on the page.
     *
     * @param array $expected       Array of key/value pairs (like Status/Registered) to be checked
     * @param string $xpathPrefix   Pass in an xpath locator to "get to" the desired table or tables. Will be prefixed to xpath
     *                              table path. Include leading forward slashes (e.g. "//div[@id='activity-content']").
     * @param string $tableId       Pass in the id attribute of a table to be verified if you want to only check a specific table
     *                              on the web page.
     */
    function webtestVerifyTabularData($expected,  $xpathPrefix = null, $tableId = null )
    {
        $tableLocator = "";
        if ( $tableId ) {
            $tableLocator = "[@id='$tableId']";
        }
        foreach ($expected as $label => $value) {
            if ( $xpathPrefix ) {
                $this->verifyText("xpath=//table{$tableLocator}/tbody/tr/td{$xpathPrefix}[text()='{$label}']/../following-sibling::td", preg_quote( $value ) );
            } else {
                $this->verifyText("xpath=//table{$tableLocator}/tbody/tr/td[text()='{$label}']/following-sibling::td", preg_quote($value));
            }
        }
    }

    /**
     * Types text into a ckEditor rich text field in a form
     *
     * @param string $fieldName form field name (as assigned by PHP buildForm class)
     * @param string $text      text to type into the field
     * @param string $editor    which text editor (valid values are 'CKEditor', 'TinyMCE')
     *
     * @return void
     */

    function fillRichTextField( $fieldName, $text = 'Typing this text into editor.', $editor = 'CKEditor' ) {
        if ( $editor == 'CKEditor') {
            $this->waitForElementPresent("css=td#cke_contents_{$fieldName} iframe");
            $this->selectFrame("css=td#cke_contents_{$fieldName} iframe");
        } else if ( $editor == 'TinyMCE') {
            $this->selectFrame("css=td.{$fieldName} iframe");
        } else {
            $this->fail( "Unknown editor value: $editor, failing (in CiviSeleniumTestCase::fillRichTextField ..." );
        }
        $this->type("//html/body", $text);
        $this->selectFrame('relative=top');
    }

    /**
     * Types option label and name into a table of multiple choice options
     * (for price set fields of type select, radio, or checkbox)
     * TODO: extend for custom field multiple choice table input
     *
     * @param array  $options           form field name (as assigned by PHP buildForm class)
     * @param array  $validateStrings   appends label and name strings to this array so they can be validated later
     *
     * @return void
     */
     
    function addMultipleChoiceOptions( $options, &$validateStrings ){
        foreach ( $options as $oIndex => $oValue ) {
            $validateStrings[] = $oValue['label'];
            $validateStrings[] = $oValue['amount'];
            if ( $oValue['membership_type_id'] ) {
                $this->select( "membership_type_id_{$oIndex}", "value={$oValue['membership_type_id']}" );
            }
            $this->type("option_label_{$oIndex}", $oValue['label'] ); 
            $this->type("option_amount_{$oIndex}" , $oValue['amount']  ); 
            $this->click('link=another choice');
        }         
    }

    /**
     */
    function webtestNewDialogContact( $fname = 'Anthony', $lname = 'Anderson', $email = 'anthony@anderson.biz', $type = 4 ) {
        // 4 - Individual profile
        // 5 - Organization profile
        // 6 - Household profile
        $this->select('profiles_1', "value={$type}");

        // create new contact using dialog
        $this->waitForElementPresent("css=div#contact-dialog-1");
        $this->waitForElementPresent('_qf_Edit_next');

        $this->type('first_name', $fname);
        $this->type('last_name',  $lname);
        $this->type('email-Primary', $email);
        $this->click('_qf_Edit_next');

        // Is new contact created?
        $this->assertTrue($this->isTextPresent('New contact has been created.'), "Status message didn't show up after saving!");
    }

    /** 
     * Generic function to check that strings are present in the page
     * 
     * @strings  array    array of strings or a single string
     *
     * @return   void
     */
    function assertStringsPresent( $strings ) {
        if ( is_array( $strings ) ) {
            // search for elements
            foreach ( $strings as $string ) {
                $this->assertTrue($this->isTextPresent($string), "Could not find $string on page");
            }
        } else {
            $this->assertTrue($this->isTextPresent($strings), "Could not find $strings on page");
        }
    }

    /** 
     * Generic function to parse a URL string into it's elements.extract a variable value from a string (url)
     * 
     * @url      string url to parse or retrieve current url if null
     *
     * @return   array  returns an associative array containing any of the various components 
     *                  of the URL that are present. Querystring elements are returned in sub-array (elements.queryString) 
     *                  http://php.net/manual/en/function.parse-url.php
     *
     */
    function parseURL( $url = null ) {
        if ( ! $url ) {
            $url = $this->getLocation( );
        }

        $elements = parse_url( $url );
        if ( ! empty( $elements['query'] ) ) {
            $elements['queryString'] = array( );
            parse_str( $elements['query'], $elements['queryString'] );
        }
        return $elements;
    }

    /**
     * Define a payment processor for use by a webtest. Default is to create Dummy processor
     * which is useful for testing online public forms (online contribution pages and event registration)
     *
     * @param string $processorName Name assigned to new processor
     * @param string $processorType Name for processor type (e.g. PayPal, Dummy, etc.)
     * @param array  $processorSettings Array of fieldname => value for required settings for the processor
     *
     * @return void
     */

    function webtestAddPaymentProcessor( $processorName, $processorType = 'Dummy', $processorSettings = null ) {
        if ( !$processorName ) {
            $this->fail("webTestAddPaymentProcessor requires $processorName.");      
        }
        if ( $processorType == 'Dummy' ) {
            $processorSettings = array( 'user_name'      => 'dummy',
                                        'url_site'       => 'http://dummy.com',
                                        'test_user_name' => 'dummytest',
                                        'test_url_site'  => 'http://dummytest.com',
                                        );
        } elseif ( $processorType == 'AuthNet' ) {
            // FIXME: we 'll need to make a new separate account for testing
            $processorSettings = array( 'test_user_name' => '5ULu56ex',
                                        'test_password'  => '7ARxW575w736eF5p',
                                        );
        } elseif ( $processorType == 'Google_Checkout' ) {
            // FIXME: we 'll need to make a new separate account for testing
            $processorSettings = array( 'test_user_name' => '559999327053114',
                                        'test_password'  => 'R2zv2g60-A7GXKJYl0nR0g',
                                        );
        } elseif ( empty( $processorSettings ) ) {
            $this->fail("webTestAddPaymentProcessor requires $processorSettings array if processorType is not Dummy.");
        }
        $this->open($this->sboxPath . 'civicrm/admin/paymentProcessor?action=add&reset=1&pp=' . $processorType);
        $this->type('name', $processorName);          
        foreach ( $processorSettings AS $f => $v ){
            $this->type($f, $v);          
        }
        $this->click('_qf_PaymentProcessor_next-bottom');
        $this->waitForPageToLoad('30000');
        // Is new processor created?
        $this->assertTrue($this->isTextPresent($processorName), 'Processor name not found in selector after adding payment processor (webTestAddPaymentProcessor).');
    }

    function webtestAddCreditCardDetails( ) {
        $this->select('credit_card_type', 'label=Visa');
        $this->type('credit_card_number', '4807731747657838');
        $this->type('cvv2', '123');
        $this->select('credit_card_exp_date[M]', 'label=Feb');
        $this->select('credit_card_exp_date[Y]', 'label=2019');
    }

    function webtestAddBillingDetails( $firstName = null, $middleName = null, $lastName = null ) {
        if ( ! $firstName ) {
            $firstName = 'John';
        }

        if ( ! $middleName ) {
            $middleName = 'Apple';
        }

        if ( ! $lastName ) {
            $lastName = 'Smith_' . substr(sha1(rand()), 0, 7);
        }

        $this->type('billing_first_name', $firstName );
        $this->type('billing_middle_name', $middleName );
        $this->type('billing_last_name', $lastName );

        $this->type('billing_street_address-5', '234 Lincoln Ave');
        $this->type('billing_city-5', 'San Bernadino');
        $this->click('billing_state_province_id-5');
        $this->select('billing_state_province_id-5', 'label=California');
        $this->type('billing_postal_code-5', '93245');

        return array( $firstName, $middleName, $lastName );
    }

    function webtestAttachFile( $fieldLocator, $filePath = null ) {
        if ( !$filePath ) {
            $filePath = '/tmp/testfile_'.substr(sha1(rand()), 0, 7).'.txt';
            $fp       = @fopen($filePath, 'w');
            fputs($fp, 'Test file created by selenium test.');
            @fclose($fp);
        }

        $this->assertTrue( file_exists($filePath) , 'Not able to locate file: ' . $filePath );
      
        $this->attachFile( $fieldLocator, "file://{$filePath}"); 

        return $filePath;
    }
  
    function webtestCreateCSV( $headers, $rows , $filePath = null ) {
        if ( !$filePath ) {
            $filePath = '/tmp/testcsv_'.substr(sha1(rand()), 0, 7).'.csv';
        }
      
        $data = '"' . implode('", "', $headers) . '"'. "\r\n";
      
        foreach ( $rows as $row ) {
            $temp = array( );
            foreach ( $headers as $field => $header ) {
                $temp[$field]  = isset($row[$field]) ? '"'. $row[$field] . '"' : '""';
            }
            $data .=  implode(', ', $temp) . "\r\n";
        }
      
        $fp = @fopen($filePath, 'w');
        @fwrite($fp, $data);
        @fclose($fp);

        $this->assertTrue( file_exists($filePath) , 'Not able to locate file: ' . $filePath );

        return $filePath;
    }

    /**
     * Create new relationship type w/ user specified params or default. 
     *
     * @param $params array of required params.
     *
     * @return an array of saved params values.
     */
    function webtestAddRelationshipType( $params = array( ) ) 
    {
        $this->open($this->sboxPath . 'civicrm/admin/reltype?reset=1&action=add');
      
        //build the params if not passed.
        if ( !is_array( $params ) || empty( $params ) ) {
            $params = array( 'label_a_b'       => 'Test Relationship Type A - B -'.rand( ),
                             'label_b_a'       => 'Test Relationship Type B - A -'.rand( ),
                             'contact_types_a' => 'Individual',
                             'contact_types_b' => 'Individual',
                             'description'     => 'Test Relationship Type Description' );
        }
        //make sure we have minimum required params.
        if ( !isset( $params['label_a_b'] ) || empty( $params['label_a_b'] ) ) {
            $params['label_a_b'] = 'Test Relationship Type A - B -'.rand( );
        }
      
        //start the form fill.
        $this->type('label_a_b', $params['label_a_b'] );
        $this->type('label_b_a', $params['label_b_a'] );
        $this->select('contact_types_a', "value={$params['contact_type_a']}");
        $this->select('contact_types_b', "value={$params['contact_type_b']}");
        $this->type('description', $params['description'] );
      
        //save the data.
        $this->click('_qf_RelationshipType_next-bottom');
        $this->waitForPageToLoad( '30000' );
      
        //does data saved.
        $this->assertTrue( $this->isTextPresent( 'The Relationship Type has been saved.' ), 
                           "Status message didn't show up after saving!" );
      
        $this->open($this->sboxPath . 'civicrm/admin/reltype?reset=1' );
        $this->waitForPageToLoad( '30000' );
      
        //validate data on selector.
        $data = $params;
        if ( isset( $data['description'] ) ) {
            unset( $data['description'] );
        }
        $this->assertStringsPresent( $data );
      
        return $params;
    }

    /**
     * Create new online contribution page w/ user specified params or defaults. 
     *
     * @param User can define pageTitle, hash and rand values for later data verification
     *
     * @return $pageId of newly created online contribution page.
     */
    function webtestAddContributionPage( $hash, 
                                         $rand, 
                                         $pageTitle, 
                                         $processorType = 'Dummy', 
                                         $processorName = null,
                                         $amountSection = true,
                                         $payLater      = true, 
                                         $onBehalf      = true,
                                         $pledges       = true, 
                                         $recurring     = false, 
                                         $membershipTypes = true, 
                                         $memPriceSetId = null,
                                         $friend        = true, 
                                         $profilePreId  = 1, 
                                         $profilePostId = 7, 
                                         $premiums      = true,
                                         $widget        = true, 
                                         $pcp           = true ,
                                         $isAddPaymentProcessor = true,
                                         $isPcpApprovalNeeded = false,
                                         $isSeparatePayment = false,
                                         $honoreeSection = true
                                         ) 
    {
        if ( !$pageTitle ) {
            $pageTitle = 'Donate Online ' . $hash;
        }
        if ( !$hash ) {
            $hash = substr(sha1(rand()), 0, 7);
        }
        if ( !$rand ) {
            $rand = 2 * rand(2, 50);
        }

        // Create a new payment processor if requested
        if ( $processorName  && $isAddPaymentProcessor ) {
            $this->webtestAddPaymentProcessor( $processorName, $processorType );                  
        }

        // go to the New Contribution Page page
        $this->open($this->sboxPath . 'civicrm/admin/contribute?action=add&reset=1');        
        $this->waitForPageToLoad();

        // fill in step 1 (Title and Settings)
        $this->type('title', $pageTitle );
        $this->select('financial_account_id', 'value=1');

        if ( $onBehalf ) {
            $this->click('is_organization');
            $this->select('onbehalf_profile_id', 'label=On Behalf Of Organization');
            $this->type('for_organization', "On behalf $hash");
            
            if ( $onBehalf == 'required' ) {
                $this->click('CIVICRM_QFID_2_4');          
            } else if ( $onBehalf == 'optional' ) {
                $this->click('CIVICRM_QFID_1_2');  
            }
        }

        $this->fillRichTextField( 'intro_text', 'This is introductory message for ' . $pageTitle,'CKEditor' );
        $this->fillRichTextField( 'footer_text', 'This is footer message for ' . $pageTitle,'CKEditor' );
      
        $this->type('goal_amount', 10 * $rand);

        // FIXME: handle Start/End Date/Time
        if ( $honoreeSection ) {
            $this->click('honor_block_is_active');
            $this->type('honor_block_title', "Honoree Section Title $hash");
            $this->type('honor_block_text',  "Honoree Introductory Message $hash");
        }
        // go to step 2
        $this->click('_qf_Settings_next');
        $this->waitForElementPresent('_qf_Amount_next-bottom'); 

        // fill in step 2 (Processor, Pay Later, Amounts)
        if ( $processorName ) {
            // select newly created processor if required
            $this->select('payment_processor_id',  "label={$processorName}");
        }

        if ( $amountSection && !$memPriceSetId ) {
            if ( $payLater ) {
                $this->click('is_pay_later');
                $this->type('pay_later_text',    "Pay later label $hash");
                $this->type('pay_later_receipt', "Pay later instructions $hash");            
            }

            if ( $pledges ) {
                $this->click('is_pledge_active');
                $this->click('pledge_frequency_unit[week]');
                $this->click('is_pledge_interval');
                $this->type('initial_reminder_day',    3);
                $this->type('max_reminders',           2);
                $this->type('additional_reminder_day', 1);            
            } else if ( $recurring ) {
                $this->click('is_recur');
                // only monthly frequency unit enabled
                $this->click("recur_frequency_unit[day]");
                $this->click("recur_frequency_unit[week]");
                $this->click("recur_frequency_unit[year]");
            }

            $this->click('is_allow_other_amount');
            $this->type('min_amount', $rand / 2);
            $this->type('max_amount', $rand * 10);

            $this->type('label_1', "Label $hash");
            $this->type('value_1', "$rand");
      
        } else {
            $this->click('amount_block_is_active');
        }

        $this->click('_qf_Amount_next');
        $this->waitForElementPresent('_qf_Amount_next-bottom'); 
        $this->waitForPageToLoad('30000');
        $text = "'Amount' information has been saved.";
        $this->assertTrue( $this->isTextPresent( $text ), 'Missing text: ' . $text );

        if ( ( $membershipTypes === true ) || ( is_array( $membershipTypes ) && !empty( $membershipTypes ) ) ) {
            // go to step 3 (memberships)
            $this->click('link=Memberships');        
            $this->waitForElementPresent('_qf_MembershipBlock_next-bottom');   
            
            // fill in step 3 (Memberships)
            $this->click('member_is_active');
            $this->waitForElementPresent( 'displayFee' );
            $this->type('new_title',     "Title - New Membership $hash");
            $this->type('renewal_title', "Title - Renewals $hash");

            if ( $memPriceSetId ) {
                $this->click( 'member_price_set_id' );
                $this->select( 'member_price_set_id', "value={$memPriceSetId}" );
            } else {
                //$membershipTypes = array( array( 'id' => 2 ) );
                if ( $membershipTypes === true ) {
                    $membershipTypes = array( array( 'id' => 2 ) );
                }
                               
                // FIXME: handle Introductory Message - New Memberships/Renewals
                foreach ( $membershipTypes as $mType ) {
                    $this->click("membership_type[{$mType['id']}]");
                    if ( array_key_exists('default', $mType) ) {
                        // FIXME:
                    }
                    if ( array_key_exists('auto_renew', $mType) ) {
                        $this->select("auto_renew_{$mType['id']}", "label=Give option");
                    }
                }
                
                $this->click('is_required');
                
                if( $isSeparatePayment ){
                    $this->click('is_separate_payment');
                }
            }
            $this->click('_qf_MembershipBlock_next');
            $this->waitForPageToLoad('30000');
            $this->waitForElementPresent('_qf_MembershipBlock_next-bottom');
            $text = "'MembershipBlock' information has been saved.";
            $this->assertTrue( $this->isTextPresent( $text ), 'Missing text: ' . $text );
        }
        
        // go to step 4 (thank-you and receipting)
        $this->click('link=Receipt');
        $this->waitForElementPresent('_qf_ThankYou_next-bottom');

        // fill in step 4
        $this->type('thankyou_title',     "Thank-you Page Title $hash");
        // FIXME: handle Thank-you Message/Page Footer
        $this->type('receipt_from_name',  "Receipt From Name $hash");
        $this->type('receipt_from_email', "$hash@example.org");
        $this->type('receipt_text',       "Receipt Message $hash");
        $this->type('cc_receipt',         "$hash@example.net");
        $this->type('bcc_receipt',        "$hash@example.com");

        $this->click('_qf_ThankYou_next');
        $this->waitForElementPresent('_qf_ThankYou_next-bottom');
        $this->waitForPageToLoad('30000');
        $text = "'ThankYou' information has been saved.";
        $this->assertTrue( $this->isTextPresent( $text ), 'Missing text: ' . $text );

        if ( $friend ) {
            // fill in step 5 (Tell a Friend)
            $this->click('link=Tell a Friend');
            $this->waitForElementPresent('_qf_Contribute_next-bottom'); 
            $this->click('tf_is_active');
            $this->type('tf_title',          "TaF Title $hash");
            $this->type('intro',             "TaF Introduction $hash");
            $this->type('suggested_message', "TaF Suggested Message $hash");
            $this->type('general_link',      "TaF Info Page Link $hash");
            $this->type('thankyou_title',    "TaF Thank-you Title $hash");
            $this->type('thankyou_text',     "TaF Thank-you Message $hash");

            $this->click('_qf_Contribute_next');
            $this->waitForElementPresent('_qf_Contribute_next-bottom'); 
            $this->waitForPageToLoad('30000');
            $text = "'Friend' information has been saved.";
            $this->assertTrue( $this->isTextPresent( $text ), 'Missing text: ' . $text );
        }

        if ( $profilePreId || $profilePostId ) {
            // fill in step 6 (Include Profiles)
            $this->click('link=Profiles');
            $this->waitForElementPresent('_qf_Custom_next-bottom');
          
            if ( $profilePreId )
                $this->select('custom_pre_id',  "value={$profilePreId}");

            if ( $profilePostId )
                $this->select('custom_post_id', "value={$profilePostId}");

            $this->click('_qf_Custom_next-bottom');
            //$this->waitForElementPresent('_qf_Custom_next-bottom');

            $this->waitForPageToLoad('30000');
            $text = "'Custom' information has been saved.";
            $this->assertTrue( $this->isTextPresent( $text ), 'Missing text: ' . $text );
        }

        if ( $premiums ) {
            // fill in step 7 (Premiums)
            $this->click('link=Premiums');
            $this->waitForElementPresent('_qf_Premium_next-bottom');
            $this->click('premiums_active');
            $this->type('premiums_intro_title',   "Prem Title $hash");
            $this->type('premiums_intro_text',    "Prem Introductory Message $hash");
            $this->type('premiums_contact_email', "$hash@example.info");
            $this->type('premiums_contact_phone', rand(100000000, 999999999));
            $this->click('premiums_display_min_contribution');

            $this->click('_qf_Premium_next');
            $this->waitForElementPresent('_qf_Premium_next-bottom');

            $this->waitForPageToLoad('30000');
            $text = "'Premium' information has been saved.";
            $this->assertTrue( $this->isTextPresent( $text ), 'Missing text: ' . $text );
        }


        if ( $widget ) {
            // fill in step 8 (Widget Settings)
            $this->click('link=Widgets');        
            $this->waitForElementPresent('_qf_Widget_next-bottom');

            $this->click('is_active');
            $this->type('url_logo',     "URL to Logo Image $hash");
            $this->type('button_title', "Button Title $hash");
            // Type About text in ckEditor (fieldname, text to type, editor)
            $this->fillRichTextField( 'about', 'This is for ' . $pageTitle,'CKEditor' );

            $this->click('_qf_Widget_next');
            $this->waitForElementPresent('_qf_Widget_next-bottom');

            $this->waitForPageToLoad('30000');            
            $text = "'Widget' information has been saved.";
            $this->assertTrue( $this->isTextPresent( $text ), 'Missing text: ' . $text );
        }

        if ( $pcp ) {
            // fill in step 9 (Enable Personal Campaign Pages)
            $this->click('link=Personal Campaigns');
            $this->waitForElementPresent('_qf_PCP_next-bottom');

            $this->click('pcp_active');
            if( !$isPcpApprovalNeeded ) $this->click('is_approval_needed');
            $this->type('notify_email', "$hash@example.name");
            $this->select('supporter_profile_id', 'value=2');
            $this->type('tellfriend_limit', 7);
            $this->type('link_text', "'Create Personal Campaign Page' link text $hash");

            $this->click('_qf_PCP_next');
            $this->waitForElementPresent('_qf_PCP_next-bottom');
            $this->waitForPageToLoad('30000');
            $text = "'PCP' information has been saved.";
            $this->assertTrue( $this->isTextPresent( $text ), 'Missing text: ' . $text );
        }

        // parse URL to grab the contribution page id
        $elements = $this->parseURL( );
        $pageId = $elements['queryString']['id'];

        // pass $pageId back to any other tests that call this class
        return $pageId;      
    }  
    
    /**
     * Function to update default strict rule. 
     *
     * @params  string   $contactType  Contact type
     * @param   array    $fields       Fields to be set for strict rule
     * @param   Integer  $threshold    Rule's threshold value
     */
    function webtestStrictDedupeRuleDefault( $contactType = 'Individual', $fields = array( ), $threshold = 10 ) {
        // set default strict rule.
        $strictRuleId = 4;
        if ( $contactType == 'Organization' ) {
            $strictRuleId = 5;
        } else if ( $contactType == 'Household' ) {
            $strictRuleId = 6;
        }
        
        // Default dedupe fields for each Contact type.
        if ( empty($fields) ) {
            $fields = array( 'civicrm_email.email' => 10 );
            if ( $contactType == 'Organization' ) {
                $fields = array( 'civicrm_contact.organization_name' => 10,
                                 'civicrm_email.email'               => 10 );
            } else if ( $contactType == 'Household' ) {
                $fields = array( 'civicrm_contact.household_name' => 10,
                                 'civicrm_email.email'            => 10 );
            }
        } 
        
        $this->open( $this->sboxPath . 'civicrm/contact/deduperules?action=update&id=' . $strictRuleId );
        $this->waitForPageToLoad('30000');
        $this->waitForElementPresent( '_qf_DedupeRules_next-bottom' );
        
        $count = 0;
        foreach ( $fields as $field => $weight ) {
            $this->select( "where_{$count}","value={$field}" );
            $this->type( "length_{$count}", '' );
            $this->type( "weight_{$count}", $weight );
            $count++;
        }
        
        if ( $count > 4 ) {
            $this->type( 'threshold', $threshold );
            // click save 
            $this->click( '_qf_DedupeRules_next-bottom' );
            $this->waitForPageToLoad( '30000' );
            return;
        }
        
        for ( $i = $count; $i <= 4; $i++ ) { 
            $this->select( "where_{$i}", 'label=- none -' );
            $this->type( "length_{$i}", '' );
            $this->type( "weight_{$i}", '' );
        }
        
        $this->type( 'threshold', $threshold );
        
        // click save 
        $this->click( '_qf_DedupeRules_next-bottom' );
        $this->waitForPageToLoad( '30000' );
    }
    
    function webtestAddMembershipType( $period_type = 'rolling', $duration_interval = 1, $duration_unit = 'year', $auto_renew = 'no' ) {
        $membershipTitle = substr(sha1(rand()), 0, 7);
        $membershipOrg   = $membershipTitle . ' memorg';
        $this->webtestAddOrganization( $membershipOrg, true );

        $title = 'Membership Type ' . substr(sha1(rand()), 0, 7);
        $memTypeParams = array( 'membership_type'   => $title,
                                'member_org'        => $membershipOrg,
                                'financial_account' => 2,
                                'period_type'       => $period_type,
                                );
      
        $this->open( $this->sboxPath . 'civicrm/admin/member/membershipType?reset=1&action=browse' );
        $this->waitForPageToLoad('30000');

        $this->click( 'link=Add Membership Type' );
        $this->waitForElementPresent( '_qf_MembershipType_cancel-bottom' );
      
        $this->type( 'name', $memTypeParams['membership_type'] );
      
        // if auto_renew optional or required - a valid payment processor must be created first (e.g Auth.net)
        // select the radio first since the element id changes after membership org search results are loaded
        switch ($auto_renew) {
        case 'optional':
            $this->click('CIVICRM_QFID_1_10');
            break;
        case 'required':
            $this->click('CIVICRM_QFID_2_12');
            break;
        default:
            break;
        }      
      
        $this->type( 'member_org', $membershipTitle );
        $this->click( '_qf_MembershipType_refresh' );
        $this->waitForElementPresent( "xpath=//div[@id='membership_type_form']/fieldset/table[2]/tbody/tr[2]/td[2]" );
      
        $this->type( 'minimum_fee', '100' );
        $this->select( 'financial_account_id', "value={$memTypeParams['financial_account']}" );
      
        $this->type( 'duration_interval', $duration_interval );
        $this->select( 'duration_unit', "label={$duration_unit}" );
      
        $this->select( 'period_type', "label={$period_type}" );
      
        $this->click( '_qf_MembershipType_upload-bottom' );
        $this->waitForElementPresent( 'link=Add Membership Type' );
        $this->assertTrue( $this->isTextPresent( "The membership type '$title' has been saved." ) );

        return $memTypeParams;
    }

    function WebtestAddGroup( ) 
    {
        $this->open($this->sboxPath . 'civicrm/group/add?reset=1');
      
        // As mentioned before, waitForPageToLoad is not always reliable. Below, we're waiting for the submit
        // button at the end of this page to show up, to make sure it's fully loaded.
        $this->waitForElementPresent('_qf_Edit_upload-bottom');
      
        // Create new group
        $title = substr(sha1(rand()), 0, 7);
        $groupName = "group_$title";
      
        // fill group name
        $this->type('title', $groupName);
      
        // fill description
        $this->type('description', 'Adding new group.');
      
        // check Access Control
        $this->click('group_type[1]');
      
        // check Mailing List
        $this->click('group_type[2]');

        // select Visibility as Public Pages
        $this->select('visibility', 'value=Public Pages');
      
        // Clicking save.
        $this->click('_qf_Edit_upload-bottom');
        $this->waitForPageToLoad('30000');
      
        // Is status message correct?
        $this->assertTrue($this->isTextPresent("The Group '$groupName' has been saved."));
        return $groupName;
    }
    
    function WebtestAddActivity ( $activityType = "Meeting")
    {
        // Adding Adding contact with randomized first name for test testContactContextActivityAdd
        // We're using Quick Add block on the main page for this.
        $firstName1 = substr(sha1(rand()), 0, 7);
        $this->webtestAddContact( $firstName1, "Summerson", $firstName1 . "@summerson.name" );
        $firstName2 = substr(sha1(rand()), 0, 7);
        $this->webtestAddContact( $firstName2, "Anderson", $firstName2 . "@anderson.name" ); 

        // Go directly to the URL of the screen that you will be testing (Activity Tab).
        $this->click("css=li#tab_activity a");

        // waiting for the activity dropdown to show up
        $this->waitForElementPresent("other_activity");

        // Select the activity type from the activity dropdown
        $this->select("other_activity", "label=Meeting");
        
        $this->waitForElementPresent("_qf_Activity_upload");

        $this->assertTrue($this->isTextPresent("Anderson, " . $firstName2), "Contact not found in line " . __LINE__ );

        // Typing contact's name into the field (using typeKeys(), not type()!)...
        $this->typeKeys("css=tr.crm-activity-form-block-assignee_contact_id input#token-input-assignee_contact_id", $firstName1);

        // ...waiting for drop down with results to show up...
        $this->waitForElementPresent("css=div.token-input-dropdown-facebook");
        $this->waitForElementPresent("css=li.token-input-dropdown-item2-facebook");

        //.need to use mouseDownAt on first result (which is a li element), click does not work
        $this->mouseDownAt("css=li.token-input-dropdown-item2-facebook");

        // ...again, waiting for the box with contact name to show up...
        $this->waitForElementPresent("css=tr.crm-activity-form-block-assignee_contact_id td ul li span.token-input-delete-token-facebook");

        // ...and verifying if the page contains properly formatted display name for chosen contact.
        $this->assertTrue($this->isTextPresent("Summerson, " . $firstName1), "Contact not found in line " . __LINE__ );

        // Since we're here, let's check if screen help is being displayed properly
        $this->assertTrue($this->isTextPresent("A copy of this activity will be emailed to each Assignee"));

        // Putting the contents into subject field - assigning the text to variable, it'll come in handy later
        $subject = "This is subject of test activity being added through activity tab of contact summary screen.";
        // For simple input fields we can use field id as selector
        $this->type("subject", $subject);
        $this->type("location", "Some location needs to be put in this field.");

        $this->webtestFillDateTime('activity_date_time','+1 month 11:10PM');

        // Setting duration.
        $this->type("duration", "30");

        // Putting in details.
        $this->type("details", "Really brief details information.");

        // Making sure that status is set to Scheduled (using value, not label).
        $this->select("status_id", "value=1");

        // Setting priority.
        $this->select("priority_id", "value=1");   

        // Scheduling follow-up.
        $this->click( "css=.crm-activity-form-block-schedule_followup div.crm-accordion-header" );
        $this->select( "followup_activity_type_id", "value=1" );
        $this->type( "interval", "1" );
        $this->select( "interval_unit","value=day" ); 
        $this->type( "followup_activity_subject","This is subject of schedule follow-up activity" );

        // Clicking save.
        $this->click("_qf_Activity_upload");
        $this->waitForPageToLoad("30000");

        // Is status message correct?
        $this->assertTrue($this->isTextPresent("Activity '$subject' has been saved."), "Status message didn't show up after saving!");

        $this->waitForElementPresent("xpath=//div[@id='Activities']//table/tbody/tr[2]/td[8]/span/a[text()='View']");

        // click through to the Activity view screen
        $this->click("xpath=//div[@id='Activities']//table/tbody/tr[2]/td[8]/span/a[text()='View']");
        $this->waitForElementPresent('_qf_Activity_cancel-bottom');
        $elements = $this->parseURL( );
        $activityID = $elements['queryString']['id'];
        return $activityID;
    }

    static function checkDoLocalDBTest( ) {
        if ( defined( 'CIVICRM_WEBTEST_LOCAL_DB' ) && 
             CIVICRM_WEBTEST_LOCAL_DB ) {
            require_once 'tests/phpunit/CiviTest/CiviDBAssert.php';
            return true;
        }
        return false;
    }
                                            
    /** 
     * Generic function to compare expected values after an api call to retrieved
     * DB values.
     * 
     * @daoName  string   DAO Name of object we're evaluating.
     * @id       int      Id of object
     * @match    array    Associative array of field name => expected value. Empty if asserting 
     *                      that a DELETE occurred
     * @delete   boolean  True if we're checking that a DELETE action occurred.
     */
    function assertDBState( $daoName, $id, $match, $delete = false ) {
        if ( ! self::checkDoLocalDBTest( ) ) {
            return;
        }

        return CiviDBAssert::assertDBState( $this, $daoName, $id, $match, $delete );
    }

    // Request a record from the DB by seachColumn+searchValue. Success if a record is found. 
    function assertDBNotNull(  $daoName, $searchValue, $returnColumn, $searchColumn, $message  ) 
    {
        if ( ! self::checkDoLocalDBTest( ) ) {
            return;
        }

        return CiviDBAssert::assertDBNotNull( $this, $daoName, $searchValue, $returnColumn, $searchColumn, $message  );
    }

    // Request a record from the DB by seachColumn+searchValue. Success if returnColumn value is NULL. 
    function assertDBNull(  $daoName, $searchValue, $returnColumn, $searchColumn, $message  ) 
    {
        if ( ! self::checkDoLocalDBTest( ) ) {
            return;
        }

        return CiviDBAssert::assertDBNull( $this, $daoName, $searchValue, $returnColumn, $searchColumn, $message  );
    }

    // Request a record from the DB by id. Success if row not found. 
    function assertDBRowNotExist(  $daoName, $id, $message  ) 
    {
        if ( ! self::checkDoLocalDBTest( ) ) {
            return;
        }

        return CiviDBAssert::assertDBRowNotExist( $this, $daoName, $id, $message );
    }

    // Compare a single column value in a retrieved DB record to an expected value
    function assertDBCompareValue(  $daoName, $searchValue, $returnColumn, $searchColumn,
                                    $expectedValue, $message  ) 
    {
        if ( ! self::checkDoLocalDBTest( ) ) {
            return;
        }

        return CiviDBAssert::assertDBCompareValue( $daoName, $searchValue, $returnColumn, $searchColumn,
                                                   $expectedValue, $message );
    }

    // Compare all values in a single retrieved DB record to an array of expected values
    function assertDBCompareValues( $daoName, $searchParams, $expectedValues )  
    {
        if ( ! self::checkDoLocalDBTest( ) ) {
            return;
        }

        return CiviDBAssert::assertDBCompareValues( $daoName, $searchParams, $expectedValues );
    }


    function assertAttributesEquals( &$expectedValues, &$actualValues ) 
    {
        if ( ! self::checkDoLocalDBTest( ) ) {
            return;
        }

        return CiviDBAssert::assertAttributesEquals( $expectedValues, $actualValues );
    }
    
    function changeAdminLinks( ){
        $version = 7;
        if( $version == 7 ) {
            $this->open("{$this->sboxPath}admin/people/permissions");
        } else {
            $this->open("{$this->sboxPath}admin/user/permissions"); 
        }   
    } 
}
