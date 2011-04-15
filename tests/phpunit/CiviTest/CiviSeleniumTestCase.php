<?php  // vim: set si ai expandtab tabstop=4 shiftwidth=4 softtabstop=4:

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
 | License and the CiviCRM Licensing Exception along                  |
 | with this program; if not, contact CiviCRM LLC                     |
 | at info[AT]civicrm[DOT]org. If you have questions about the        |
 | GNU Affero General Public License or the licensing of CiviCRM,     |
 | see the CiviCRM license FAQ at http://civicrm.org/licensing        |
 +--------------------------------------------------------------------+
*/

require_once 'PHPUnit/Extensions/SeleniumTestCase.php';
//require_once '/var/www/tests.dev.civicrm.org/public/drupal/sites/default/civicrm.settings.php';

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
        $password = $admin ? $this->settings->adminPassword : $this->settings->password;
        $username = $admin ? $this->settings->adminUsername : $this->settings->username;
        $this->type("edit-name", $username);
        $this->type("edit-pass", $password);
        $this->click("edit-submit");
        $this->waitForPageToLoad("30000");      
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

    function webtestAddContact( $fname = 'Anthony', $lname = 'Anderson', $email = null ) {
        $this->open($this->sboxPath . "civicrm/dashboard?reset=1");
        $this->type("qa_first_name", $fname);
        $this->type("qa_last_name", $lname);
        if ($email === true) $email = substr(sha1(rand()), 0, 7) . '@example.org';
        if ($email) $this->type("qa_email", $email);
        $this->click("_qf_Contact_next");
        $this->waitForPageToLoad("30000");        
        return $email;
    }

    function webtestAddHousehold( $householdName = "Smith's Home", $email = null ) {
        
        $this->open($this->sboxPath . "civicrm/contact/add&reset=1&ct=Household");
        $this->click("household_name");
        $this->type("household_name", $householdName );

        if ($email === true) $email = substr(sha1(rand()), 0, 7) . '@example.org';
        if ($email) $this->type("email_1_email", $email);

        $this->click("_qf_Contact_upload_view");
        $this->waitForPageToLoad("30000");        
        return $email;
    }


    function webtestAddOrganization( $organizationName = "Smith's Home", $email = null ) {
        
        $this->open($this->sboxPath . "civicrm/contact/add&reset=1&ct=Organization");
        $this->click("organization_name");
        $this->type("organization_name", $organizationName );

        if ($email === true) $email = substr(sha1(rand()), 0, 7) . '@example.org';
        if ($email) $this->type("email_1_email", $email);

        $this->click("_qf_Contact_upload_view");
        $this->waitForPageToLoad("30000");        
        return $email;
    }

  /**
   */
   function webtestFillAutocomplete( $sortName ) {
      $this->typeKeys("contact_1", $sortName);
      $this->waitForElementPresent("css=div.ac_results-inner li");
      $this->click("css=div.ac_results-inner li");
      $this->assertContains($sortName, $this->getValue("contact_1"), "autocomplete expected $sortName but didn’t find it in " . $this->getValue("contact_1"));
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
       $timeStamp = strtotime($strToTimeArgs ? $strToTimeArgs : "+1 month");

       $year = date('Y', $timeStamp);
       $mon  = date('n', $timeStamp) - 1; // -1 ensures month number is inline with calender widget's month
       $day  = date('j', $timeStamp);

       $this->click ($dateElement);
       $this->select("css=div#ui-datepicker-div div.ui-datepicker-title select.ui-datepicker-month", "value=$mon");
       $this->select("css=div#ui-datepicker-div div.ui-datepicker-title select.ui-datepicker-year", "value=$year");
       $this->click ("link=$day");
   }

   // 1. set both date and time.
   function webtestFillDateTime( $dateElement, $strToTimeArgs = null ) {
       $this->webtestFillDate( $dateElement, $strToTimeArgs );

       $timeStamp = strtotime($strToTimeArgs ? $strToTimeArgs : "+1 month");
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
                $this->verifyText("xpath=//x:table{$tableLocator}/x:tbody/tr/td{$xpathPrefix}[text()='{$label}']/../following-sibling::td", preg_quote( $value ) );
            } else {
                $this->verifyText("xpath=//x:table{$tableLocator}/x:tbody/tr/td[text()='{$label}']/following-sibling::td", preg_quote($value));
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
            $this->selectFrame("{$fieldName}_text_ifr");
        } else {
            $this->fail( "Unknown editor value: $editor, failing (in CiviSeleniumTestCase::fillRichTextField ..." );
        }
        $this->type("//html/body", $text);
        $this->selectFrame("relative=top");
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
             $this->type("option_label_{$oIndex}", $oValue['label'] ); 
             $this->type("option_amount_{$oIndex}" , $oValue['amount']  ); 
             $this->click("link=another choice");
         }         
     }

   /**
    */
    function webtestNewDialogContact( $fname = 'Anthony', $lname = 'Anderson', $email = 'anthony@anderson.biz', $type = 4 ) {
        // 4 - Individual profile
        // 5 - Organization profile
        // 6 - Household profile
        $this->select("profiles_1", "value={$type}");

        // create new contact using dialog
        $this->waitForElementPresent("css=div#contact-dialog-1");
        $this->waitForElementPresent("_qf_Edit_next");

        $this->type("first_name", $fname);
        $this->type("last_name",  $lname);
        $this->type("email-Primary", $email);
        $this->click("_qf_Edit_next");

        // Is new contact created?
        $this->assertTrue($this->isTextPresent("New contact has been created."), "Status message didn't show up after saving!");
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
    function assertDBState( $daoName, $id, $match, $delete=false ) {
        if ( empty( $id ) ) {
            // adding this here since developers forget to check for an id
            // and hence we get the first value in the db
            $this->fail( 'ID not populated. Please fix your asserDBState usage!!!' );
        }
        
        require_once(str_replace('_', DIRECTORY_SEPARATOR, $daoName) . ".php");
        eval( '$object   =& new ' . $daoName . '( );' );
        $object->id =  $id;
        $verifiedCount = 0;
        
        // If we're asserting successful record deletion, make sure object is NOT found.
        if ( $delete ) {
            if ( $object->find( true ) ) {
                $this->fail("Object not deleted by delete operation: $daoName, $id");
            }
            return;
        }

        // Otherwise check matches of DAO field values against expected values in $match.
        if ( $object->find( true ) ) {
            $fields =& $object->fields( );
            foreach ( $fields as $name => $value ) {
                  $dbName = $value['name'];
                  if ( isset( $match[$name] ) ) {
                    $verifiedCount++;
                    $this->assertEquals( $object->$dbName, $match[$name] );
                  } 
                  else if ( isset( $match[$dbName] ) ) {
                    $verifiedCount++;
                    $this->assertEquals( $object->$dbName, $match[$dbName] );
                  }
            }
        } else {
            $this->fail("Could not retrieve object: $daoName, $id");
        }
        $object->free( );
        $matchSize = count( $match );
        if ( $verifiedCount != $matchSize ) {
            $this->fail("Did not verify all fields in match array: $daoName, $id. Verified count = $verifiedCount. Match array size = $matchSize");
        }
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
      } elseif ( empty( $processorSettings ) ) {
          $this->fail("webTestAddPaymentProcessor requires $processorSettings array if processorType is not Dummy.");
      }
      $this->open($this->sboxPath . "civicrm/admin/paymentProcessor?action=add&reset=1&pp=" . $processorType);
      $this->type('name', $processorName);          
      foreach ( $processorSettings AS $f => $v ){
          $this->type($f, $v);          
      }
      $this->click("_qf_PaymentProcessor_next-bottom");
      $this->waitForPageToLoad("30000");
      // Is new processor created?
      $this->assertTrue($this->isTextPresent($processorName), "Processor name not found in selector after adding payment processor (webTestAddPaymentProcessor).");
  }

  function webtestAddCreditCardDetails( ) {
      $this->select("credit_card_type", "label=Visa");
      $this->type("credit_card_number", "4807731747657838");
      $this->type("cvv2", "123");
      $this->select("credit_card_exp_date[M]", "label=Feb");
      $this->select("credit_card_exp_date[Y]", "label=2019");
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

      $this->type("billing_first_name", $firstName );
      $this->type("billing_middle_name", $middleName );
      $this->type("billing_last_name", $lastName );

      $this->type("billing_street_address-5", "234 Lincoln Ave");
      $this->type("billing_city-5", "San Bernadino");
      $this->select("billing_state_province_id-5", "label=California");
      $this->type("billing_postal_code-5", "93245");

      return array( $firstName, $middleName, $lastName );
  }

  function webtestAttachFile( $fieldLocator, $filePath = null ) {
      if ( !$filePath ) {
          $filePath = '/tmp/testfile_'.substr(sha1(rand()), 0, 7).".txt";
          $fp       = @fopen($filePath, 'w');
          fputs($fp, "Test file created by selenium test.");
          @fclose($fp);
      }

      $this->assertTrue( file_exists($filePath) , 'Not able to locate file: ' . $filePath );
      
      $this->attachFile( $fieldLocator, "file://{$filePath}"); 

      return $filePath;
  }
  
  function webtestCreateCSV( $headers, $rows , $filePath = null ) {
      if ( !$filePath ) {
          $filePath = '/tmp/testcsv_'.substr(sha1(rand()), 0, 7).".csv";
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
                                       $friend        = true, 
                                       $profilePreId  = 1, 
                                       $profilePostId = 7, 
                                       $premiums      = true,
                                       $widget        = true, 
                                       $pcp           = true ,
                                       $isAddPaymentProcessor = true,
                                       $isPcpApprovalNeeded = false
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
      $this->select('contribution_type_id', 'value=1');

      if ( $onBehalf ) {
          $this->click('is_organization');
          $this->type('for_organization', "On behalf $hash");
          $this->click('CIVICRM_QFID_2_4');          
      }

      $this->fillRichTextField( "intro_text", 'This is introductory message for ' . $pageTitle,'CKEditor' );
      $this->fillRichTextField( "footer_text", 'This is footer message for ' . $pageTitle,'CKEditor' );
      
      $this->type('goal_amount', 10 * $rand);

      // FIXME: handle Start/End Date/Time

      $this->click('honor_block_is_active');
      $this->type('honor_block_title', "Honoree Section Title $hash");
      $this->type('honor_block_text',  "Honoree Introductory Message $hash");

      // go to step 2
      $this->click('_qf_Settings_next');
      $this->waitForElementPresent("_qf_Amount_next-bottom"); 

      // fill in step 2 (Processor, Pay Later, Amounts)
      if ( $processorName ) {
          // select newly created processor if required
          $this->select("payment_processor_id",  "label={$processorName}");
      }

      if ( $amountSection ) {
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
              $this->click("is_recur");
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
          $this->click("amount_block_is_active");
      }

      $this->click('_qf_Amount_next');
      $this->waitForElementPresent("_qf_Amount_next-bottom"); 
      $this->waitForPageToLoad("30000");
      $text = "'Amount' information has been saved.";
      $this->assertTrue( $this->isTextPresent( $text ), 'Missing text: ' . $text );

      if ( $membershipTypes === true ) {
          $membershipTypes = array( array( 'id' => 2 ) );
      }
      if ( is_array($membershipTypes) && !empty($membershipTypes) ) {
          // go to step 3 (memberships)
          $this->click("link=Memberships");        
          $this->waitForElementPresent("_qf_MembershipBlock_next-bottom");            
          // fill in step 3 (Memberships)
          $this->click('is_active');
          $this->type('new_title',     "Title - New Membership $hash");
          $this->type('renewal_title', "Title - Renewals $hash");

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
          
          $this->click('_qf_MembershipBlock_next');
          $this->waitForPageToLoad("30000");
          $this->waitForElementPresent("_qf_MembershipBlock_next-bottom");
          $text = "'MembershipBlock' information has been saved.";
          $this->assertTrue( $this->isTextPresent( $text ), 'Missing text: ' . $text );
      }

      // go to step 4 (thank-you and receipting)
      $this->click("link=Receipt");
      $this->waitForElementPresent("_qf_ThankYou_next-bottom");

      // fill in step 4
      $this->type('thankyou_title',     "Thank-you Page Title $hash");
      // FIXME: handle Thank-you Message/Page Footer
      $this->type('receipt_from_name',  "Receipt From Name $hash");
      $this->type('receipt_from_email', "$hash@example.org");
      $this->type('receipt_text',       "Receipt Message $hash");
      $this->type('cc_receipt',         "$hash@example.net");
      $this->type('bcc_receipt',        "$hash@example.com");

      $this->click('_qf_ThankYou_next');
      $this->waitForElementPresent("_qf_ThankYou_next-bottom");
      $this->waitForPageToLoad("30000");
      $text = "'ThankYou' information has been saved.";
      $this->assertTrue( $this->isTextPresent( $text ), 'Missing text: ' . $text );

      if ( $friend ) {
          // fill in step 5 (Tell a Friend)
          $this->click("link=Tell a Friend");
          $this->waitForElementPresent("_qf_Contribute_next-bottom"); 
          $this->click('tf_is_active');
          $this->type('tf_title',          "TaF Title $hash");
          $this->type('intro',             "TaF Introduction $hash");
          $this->type('suggested_message', "TaF Suggested Message $hash");
          $this->type('general_link',      "TaF Info Page Link $hash");
          $this->type('thankyou_title',    "TaF Thank-you Title $hash");
          $this->type('thankyou_text',     "TaF Thank-you Message $hash");

          $this->click('_qf_Contribute_next');
          $this->waitForElementPresent("_qf_Contribute_next-bottom"); 
          $this->waitForPageToLoad("30000");
          $text = "'Friend' information has been saved.";
          $this->assertTrue( $this->isTextPresent( $text ), 'Missing text: ' . $text );
      }

      if ( $profilePreId || $profilePostId ) {
          // fill in step 6 (Include Profiles)
          $this->click("link=Profiles");
          $this->waitForElementPresent("_qf_Custom_next-bottom");
          
          if ( $profilePreId )
              $this->select('custom_pre_id',  "value=$profilePreId");

          if ( $profilePostId )
              $this->select('custom_post_id', "value=$profilePostId");

          $this->click('_qf_Custom_next');
          $this->waitForElementPresent("_qf_Custom_next-bottom");

          $this->waitForPageToLoad("30000");
          $text = "'Custom' information has been saved.";
          $this->assertTrue( $this->isTextPresent( $text ), 'Missing text: ' . $text );
      }

      if ( $premiums ) {
          // fill in step 7 (Premiums)
          $this->click("link=Premiums");
          $this->waitForElementPresent("_qf_Premium_next-bottom");
          $this->click('premiums_active');
          $this->type('premiums_intro_title',   "Prem Title $hash");
          $this->type('premiums_intro_text',    "Prem Introductory Message $hash");
          $this->type('premiums_contact_email', "$hash@example.info");
          $this->type('premiums_contact_phone', rand(100000000, 999999999));
          $this->click('premiums_display_min_contribution');

          $this->click('_qf_Premium_next');
          $this->waitForElementPresent("_qf_Premium_next-bottom");

          $this->waitForPageToLoad("30000");
          $text = "'Premium' information has been saved.";
          $this->assertTrue( $this->isTextPresent( $text ), 'Missing text: ' . $text );
      }


      if ( $widget ) {
          // fill in step 8 (Widget Settings)
          $this->click("link=Widgets");        
          $this->waitForElementPresent("_qf_Widget_next-bottom");

          $this->click('is_active');
          $this->type('url_logo',     "URL to Logo Image $hash");
          $this->type('button_title', "Button Title $hash");
          // Type About text in ckEditor (fieldname, text to type, editor)
          $this->fillRichTextField( "about", 'This is for ' . $pageTitle,'CKEditor' );

          $this->click('_qf_Widget_next');
          $this->waitForElementPresent("_qf_Widget_next-bottom");

          $this->waitForPageToLoad("30000");            
          $text = "'Widget' information has been saved.";
          $this->assertTrue( $this->isTextPresent( $text ), 'Missing text: ' . $text );
      }

      if ( $pcp ) {
          // fill in step 9 (Enable Personal Campaign Pages)
          $this->click("link=Personal Campaigns");
          $this->waitForElementPresent("_qf_PCP_next-bottom");

          $this->click('pcp_active');
          if( !$isPcpApprovalNeeded ) $this->click('is_approval_needed');
          $this->type('notify_email', "$hash@example.name");
          $this->select('supporter_profile_id', 'value=2');
          $this->type('tellfriend_limit', 7);
          $this->type('link_text', "'Create Personal Campaign Page' link text $hash");

          $this->click('_qf_PCP_next');
          $this->waitForElementPresent("_qf_PCP_next-bottom");
          $this->waitForPageToLoad("30000");
          $text = "'PCP' information has been saved.";
          $this->assertTrue( $this->isTextPresent( $text ), 'Missing text: ' . $text );
      }

      // parse URL to grab the contribution page id
      $elements = $this->parseURL( );
      $pageId = $elements['queryString']['id'];

      // pass $pageId back to any other tests that call this class
      return $pageId;      
  }  
}
