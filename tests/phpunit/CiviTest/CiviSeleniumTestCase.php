<?php  // vim: set si ai expandtab tabstop=4 shiftwidth=4 softtabstop=4:

/*
 +--------------------------------------------------------------------+
 | CiviCRM version 3.1                                                |
 +--------------------------------------------------------------------+
 | Copyright CiviCRM LLC (c) 2004-2010                                |
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

//    protected $captureScreenshotOnFailure = TRUE;
//    protected $screenshotPath = '/var/www/api.dev.civicrm.org/public/sc';
//    protected $screenshotUrl = 'http://api.dev.civicrm.org/sc/';

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
    function webtestLogin( ) {
        $this->type("edit-name", $this->settings->username);
        $this->type("edit-pass", $this->settings->password);
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

  /**
   */
   function webtestFillAutocomplete( $sortName ) {
      $this->typeKeys("contact", $sortName);
      $this->waitForElementPresent("css=div.ac_results-inner li");
      $this->click("css=div.ac_results-inner li");
      $this->assertContains($sortName, $this->getValue("contact"), "autocomplete expected $sortName but didn’t find it in " . $this->getValue("contact"));
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
     * @param array $expected array of key/value pairs (like Status/Registered) to be checked
     */
    function webtestVerifyTabularData($expected)
    {
        foreach ($expected as $label => $value) {
            $this->verifyText("xpath=//table//tr/td[text()=\"$label\"]/../td[2]", preg_quote($value));
        }
    }

   /**
    */
    function webtestNewDialogContact( $fname = 'Anthony', $lname = 'Anderson', $email = 'anthony@anderson.biz', $type = 4 ) {
        // 4 - Individual profile
        // 5 - Organization profile
        // 6 - Household profile
        $this->select("profiles", "value={$type}");

        // create new contact using dialog
        $this->waitForElementPresent("css=div#contact-dialog");
        $this->waitForElementPresent("_qf_Edit_next");

        $this->type("first_name", $fname);
        $this->type("last_name",  $lname);
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
  function _checkStrings( $strings ) {
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
   * Generic function to extract a variable value from a string (url)
   * 
   * @url      string url to parse or retrieve current url if null
   *
   * @return   array  returns an associative array containing any of the various components 
   *                  of the URL that are present.
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

  

}
