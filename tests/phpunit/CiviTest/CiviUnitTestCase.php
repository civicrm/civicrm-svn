<?php  // vim: set si ai expandtab tabstop=4 shiftwidth=4 softtabstop=4:

/**
 *  File for the CiviUnitTestCase class
 *
 *  (PHP 5)
 *  
 *   @copyright Copyright CiviCRM LLC (C) 2009
 *   @license   http://www.fsf.org/licensing/licenses/agpl-3.0.html
 *              GNU Affero General Public License version 3
 *   @version   $Id$
 *   @package   CiviCRM
 *
 *   This file is part of CiviCRM
 *
 *   CiviCRM is free software; you can redistribute it and/or
 *   modify it under the terms of the GNU Affero General Public License
 *   as published by the Free Software Foundation; either version 3 of
 *   the License, or (at your option) any later version.
 *
 *   CiviCRM is distributed in the hope that it will be useful,
 *   but WITHOUT ANY WARRANTY; without even the implied warranty of
 *   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *   GNU Affero General Public License for more details.
 *
 *   You should have received a copy of the GNU Affero General Public
 *   License along with this program.  If not, see
 *   <http://www.gnu.org/licenses/>.
 */

/**
 *  Include configuration
 */
require_once 'tests/phpunit/CiviTest/civicrm.settings.php';

/**
 *  Include class definitions
 */
require_once 'PHPUnit/Extensions/Database/TestCase.php';
require_once 'PHPUnit/Extensions/Database/DataSet/FlatXmlDataSet.php';
require_once 'PHPUnit/Extensions/Database/DataSet/XmlDataSet.php';
require_once 'PHPUnit/Extensions/Database/DataSet/QueryDataSet.php';
require_once 'tests/phpunit/Utils.php';

/**
 *  Base class for CiviCRM unit tests
 *
 *  Common functions for unit tests
 *  @package CiviCRM
 */
class CiviUnitTestCase extends PHPUnit_Extensions_Database_TestCase {

    /**
     *  Database has been initialized
     *
     *  @var boolean
     */
    private static $dbInit = false;

    /**
     *  Database connection
     *
     *  @var PHPUnit_Extensions_Database_DB_IDatabaseConnection
     */
    protected $_dbconn;

    /**
     *  @var Don't reset database if set to true in TestCase
     */
    protected $noreset = false;

    /**
     *  @var Utils instance
     */
    public static $utils;

    public static $populateOnce = false;

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
    function __construct($name = NULL, array $data = array(), $dataName = '' ) {
        parent::__construct($name, $data, $dataName);

        //  create test database
        self::$utils = new Utils( $GLOBALS['mysql_host'],
                                $GLOBALS['mysql_user'],
                                $GLOBALS['mysql_pass'] );        
        
    }

    /**
     *  Create database connection for this instance
     *
     *  Initialize the test database if it hasn't been initialized
     *
     *  @return PHPUnit_Extensions_Database_DB_IDatabaseConnection connection
     */
    protected function getConnection()
    {
        if ( !self::$dbInit ) {

            //  install test database
            echo PHP_EOL
                . "Installing civicrm_tests_dev database"
                . PHP_EOL;

            $this->_populateDB();

            self::$dbInit = true;
        }
        return $this->createDefaultDBConnection(self::$utils->pdo,
                                             'civicrm_tests_dev');
    }

    /**
     *  Required implementation of abstract method
     */
    protected function getDataSet() { }

    private function _populateDB() {

        if ( self::$populateOnce ) {
            return;
        }

        self::$populateOnce = null;

            $queries = array( "DROP DATABASE IF EXISTS civicrm_tests_dev;", 
                              "CREATE DATABASE civicrm_tests_dev DEFAULT" . 
                              " CHARACTER SET utf8 COLLATE utf8_unicode_ci;", 
                              "USE civicrm_tests_dev;", 
                              "SET SQL_MODE='STRICT_ALL_TABLES';", 
                              "SET foreign_key_checks = 0" );
            foreach( $queries as $query ) {
                if ( self::$utils->do_query($query) === false ) {

                    //  failed to create test database
                    exit;
                }
            }

            //  initialize test database
            $sql_file1 = dirname( dirname( dirname( dirname( __FILE__ ) ) ) )
                . "/sql/civicrm.mysql";
            $sql_file2 = dirname( dirname( dirname( dirname( __FILE__ ) ) ) )
                . "/sql/civicrm_data.mysql";
            $sql_file3 = dirname( dirname( dirname( dirname( __FILE__ ) ) ) )
                . "/sql/test_data.mysql";
            $query1 = file_get_contents( $sql_file1 );
            $query2 = file_get_contents( $sql_file2 );
            $query3 = file_get_contents( $sql_file3 );
            if ( self::$utils->do_query($query1) === false ) {
                echo "Loading schema in setUp crapped out. Aborting.";
                exit;
            }
            if ( self::$utils->do_query($query2) === false ) {
                echo "Cannot load civicrm_data.mysql. Aborting.";
                exit;
            }
            if ( self::$utils->do_query($query3) === false ) {
                echo "Cannot load test_data.mysql. Aborting.";
                exit;
            }
            
            unset( $query, $query1, $query2);
    }

    /**
     *  Common setup functions for all unit tests
     */
    protected function setUp() {

        // "initialize" CiviCRM to avoid problems when running single tests
        // FIXME: look at it closer in second stage
        if (isset( $config ) ) {
            unset( $config );
        }
        require_once 'CRM/Core/Config.php';
        $config =& CRM_Core_Config::singleton();

        // when running unit tests, use mockup user framework
        $config->setUserFramework( 'UnitTests' );
        // enable backtrace to get meaningful errors
        $config->backtrace = 1;
        
        //  Use a temporary file for STDIN
        $GLOBALS['stdin'] = tmpfile( );
        if ( $GLOBALS['stdin'] === false ) {
            echo "Couldn't open temporary file\n";
            exit(1);
        }

        //  Get and save a connection to the database
        $this->_dbconn = $this->getConnection();

        // reload database before each test
        $this->_populateDB();
    }


    public function cleanDB() {
        self::$populateOnce = null;

        $this->_dbconn = $this->getConnection();
        $this->_populateDB();
    }

    /**
     *  Common teardown functions for all unit tests
     */
    protected function tearDown() { }


    /**
     *  FIXME: Maybe a better way to do it
     */
    function foreignKeyChecksOff() {

        self::$utils = new Utils( $GLOBALS['mysql_host'],
                                  $GLOBALS['mysql_user'],
                                  $GLOBALS['mysql_pass'] );        
    
        $query = "USE civicrm_tests_dev;"
               . "SET foreign_key_checks = 1";
        if ( self::$utils->do_query($query) === false ) {
            // fail happens
            echo 'Cannot set foreign_key_checks = 0';
            exit(1);
        }
        return true;
    }
    
    function foreignKeyChecksOn() {
      // FIXME: might not be needed if previous fixme implemented
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

    // Request a record from the DB by seachColumn+searchValue. Success if a record is found. 
    function assertDBNotNull(  $daoName, $searchValue, $returnColumn, $searchColumn, $message  ) 
    {
        $value = CRM_Core_DAO::getFieldValue( $daoName, $searchValue, $returnColumn, $searchColumn );
        $this->assertNotNull( $value, $message );
        
        return $value;
    }

    // Request a record from the DB by seachColumn+searchValue. Success if returnColumn value is NULL. 
    function assertDBNull(  $daoName, $searchValue, $returnColumn, $searchColumn, $message  ) 
    {
        $value = CRM_Core_DAO::getFieldValue( $daoName, $searchValue, $returnColumn, $searchColumn );
        $this->assertNull(  $value, $message );
    }

    // Request a record from the DB by id. Success if row not found. 
    function assertDBRowNotExist(  $daoName, $id, $message  ) 
    {
        $value = CRM_Core_DAO::getFieldValue( $daoName, $id, 'id', 'id' );
        $this->assertNull(  $value, $message );
    }

    // Compare a single column value in a retrieved DB record to an expected value
    function assertDBCompareValue(  $daoName, $searchValue, $returnColumn, $searchColumn,
                                    $expectedValue, $message  ) 
    {
        $value = CRM_Core_DAO::getFieldValue( $daoName, $searchValue, $returnColumn, $searchColumn );
        $this->assertEquals(  $value, $expectedValue, $message );
    }

    // Compare all values in a single retrieved DB record to an array of expected values
    function assertDBCompareValues( $daoName, $searchParams, $expectedValues )  
    {
        //get the values from db 
        $dbValues = array( );
        CRM_Core_DAO::commonRetrieve( $daoName, $searchParams, $dbValues );
        

        // compare db values with expected values
        self::assertAttributesEquals( $expectedValues, $dbValues);
    }


    function assertAttributesEquals( &$expectedValues, &$actualValues ) 
    {
        foreach( $expectedValues as $paramName => $paramValue ) {
            if ( isset( $actualValues[$paramName] ) ) {
                $this->assertEquals( $paramValue, $actualValues[$paramName] );
            } else {
                $this->fail( "Attribute '$paramName' not present in actual array." );
            }
        }        
    }
    
    function assertArrayKeyExists( $key, &$list ) {
        $result = isset( $list[$key] ) ? true : false;
        $this->assertTrue( $result, ts( "%1 element exists?",
                                        array( 1 => $key ) ) );
    }

    function assertArrayValueNotNull( $key, &$list ) {
        $this->assertArrayKeyExists( $key, $list );

        $value = isset( $list[$key] ) ? $list[$key] : null;
        $this->assertTrue( $value,
                           ts( "%1 element not null?",
                               array( 1 => $key ) ) );
    }
    
    /** 
     * Generic function to create Organisation, to be used in test cases
     * 
     * @param array   parameters for civicrm_contact_add api function call
     * @return int    id of Organisation created
     */
    function organizationCreate( $params = null ) {
        if ( $params === null ) {
            $params = array( 'organization_name' => 'Unit Test Organization',
                             'contact_type'      => 'Organization' );
        }
        return $this->_contactCreate( $params );
    }
    
    /** 
     * Generic function to create Individual, to be used in test cases
     * 
     * @param array   parameters for civicrm_contact_add api function call
     * @return int    id of Individual created
     */
    function individualCreate( $params = null ) {
        if ( $params === null ) {
            $params = array( 'first_name'       => 'Anthony',
                             'middle_name'      => 'J.',
                             'last_name'        => 'Anderson',
                             'prefix_id'        => 3,
                             'suffix_id'        => 3,
                             'email'            => 'anthony_anderson@civicrm.org',
                             'contact_type'     => 'Individual');
        }
        return $this->_contactCreate( $params );
    }
    
    /** 
     * Generic function to create Household, to be used in test cases
     * 
     * @param array   parameters for civicrm_contact_add api function call
     * @return int    id of Household created
     */
    function householdCreate( $params = null ) {
        if ( $params === null ) {    
            $params = array( 'household_name' => 'Unit Test household',
                             'contact_type'      => 'Household' );
        }
        return $this->_contactCreate( $params );
    }
    
    /** 
     * Private helper function for calling civicrm_contact_add
     * 
     * @param array   parameters for civicrm_contact_add api function call
     * @return int    id of Household created
     */
    private function _contactCreate( $params ) {
        require_once 'api/v2/Contact.php';
        $result = civicrm_contact_add( $params );
        if ( CRM_Utils_Array::value( 'is_error', $result ) ||
             ! CRM_Utils_Array::value( 'contact_id', $result ) ) {
            throw new Exception( 'Could not create test contact.' );
        }
        return $result['contact_id'];
    }
    
    function contactDelete( $contactID ) 
    {
        require_once 'api/v2/Contact.php';
        $params['contact_id'] = $contactID;
        $result = civicrm_contact_delete( $params );
        if ( CRM_Utils_Array::value( 'is_error', $result ) ) {
            throw new Exception( 'Could not delete contact: ' . $result['error_message'] );
        }
        return;
    }
    
    function membershipTypeCreate( $contactID, $contributionTypeID = 1 ) 
    {
        $params = array( 'name'                 => 'General',
                         'duration_unit'        => 'year',
                         'duration_interval'    => 1,
                         'period_type'          => 'rolling',
                         'member_of_contact_id' => $contactID,
                         'domain_id'		=> 1,
                         // FIXME: I know it's 1, cause it was loaded directly to the db.
                         // FIXME: when we load all the data, we'll need to address this to
                         // FIXME: avoid hunting numbers around.
                         'contribution_type_id' => 1 );
        
        $result = civicrm_membership_type_create( $params );
        
        if ( CRM_Utils_Array::value( 'is_error', $result ) ||
             ! CRM_Utils_Array::value( 'id', $result) ) {
            throw new Exception( 'Could not create membership type' );
        }
        
        return $result['id'];
    }

   

    
    function contactMembershipCreate( $params ) 
    {
        $pre = array('join_date'   => '2007-01-21',
                     'start_date'  => '2007-01-21',
                     'end_date'    => '2007-12-21',
                     'source'      => 'Payment'  );
        foreach ( $pre as $key => $val ) {
            if ( ! isset( $params[$key] ) ) {
                $params[$key] = $val;
            }
        }
        
        $result = civicrm_contact_membership_create( $params );
        
        if ( CRM_Utils_Array::value( 'is_error', $result ) ||
             ! CRM_Utils_Array::value( 'id', $result) ) {
            if ( CRM_Utils_Array::value( 'error_message', $result ) ) {
                throw new Exception( $result['error_message'] );
            } else {
                throw new Exception( 'Could not create membership' . ' - in line: ' . __LINE__ );
            }
        }

        return $result['id'];
    }
    
    /**
     * Function to delete Membership Type
     * 
     * @param int $membershipTypeID
     */
    function membershipTypeDelete( $membershipTypeID )
    {
        $params['id'] = $membershipTypeID;
        $result = civicrm_membership_type_delete( $params );
        if ( CRM_Utils_Array::value( 'is_error', $result ) ) {
            throw new Exception( 'Could not delete membership type' );
        }
        return;
    }
    
    function membershipDelete( $membershipID )
    {
        $result = civicrm_membership_delete( $membershipID );
        if ( CRM_Utils_Array::value( 'is_error', $result ) ) {
            throw new Exception( 'Could not delete membership' );
        }
        return;
    }
    
    function membershipStatusCreate( $name = 'test member status' ) 
    {
        $params['name'] = $name;
        $params['start_event'] = 'start_date';
        $params['end_event'] = 'end_date';
        $params['is_current_member'] = 1;
        $params['is_active'] = 1;
        $result = civicrm_membership_status_create( $params );
        if ( CRM_Utils_Array::value( 'is_error', $result ) ) {
            throw new Exception( 'Could not create membership status' );
        }
        return $result['id'];
    }
    
    function membershipStatusDelete( $membershipStatusID ) 
    {
        $params['id'] = $membershipStatusID;
        $result = civicrm_membership_status_delete( $params );
        if ( CRM_Utils_Array::value( 'is_error', $result ) ) {
            throw new Exception( 'Could not delete membership status' );
        }
        return;
    }
    

    function relationshipTypeCreate( &$params ) 
    {  
        require_once 'api/v2/RelationshipType.php';
        $result= civicrm_relationship_type_add($params);
        
        if ( civicrm_error( $params ) ) {
            throw new Exception( 'Could not create relationship type' );
        }
        return $result['id'];
    }
    
   /**
     * Function to delete Relatinship Type
     * 
     * @param int $relationshipTypeID
     */
    function relationshipTypeDelete( $relationshipTypeID )
    {
        $params['id'] = $relationshipTypeID;
        $result = civicrm_relationship_type_delete( $params );
        
        if (civicrm_error( $params ) ) {
            throw new Exception( 'Could not delete relationship type' );
        }
        return;
    }



    /** 
     * Function to create Participant 
     *
     * @param array $params  array of contact id and event id values
     *
     * @return int $id of participant created
     */    
    function participantCreate( $params ) 
    { 
        $params = array(
                        'contact_id'    => $params['contactID'],
                        'event_id'      => $params['eventID'],
                        'status_id'     => 2,
                        'role_id'       => 1,
                        'register_date' => 20070219,
                        'source'        => 'Wimbeldon',
                        'event_level'   => 'Payment'
                        );
        
        $result = civicrm_participant_create( $params );
        if ( CRM_Utils_Array::value( 'is_error', $result ) ) {
            throw new Exception( 'Could not create participant' );
        }
        return $result['result'];
    }
    
    /** 
     * Function to create Contribution Type
     * 
     * @return int $id of contribution type created
     */    
    function contributionTypeCreate() 
    {
        $op = new PHPUnit_Extensions_Database_Operation_Insert( );
        $op->execute( $this->_dbconn,
                      new PHPUnit_Extensions_Database_DataSet_XMLDataSet(
                             dirname(__FILE__)
                             . '/../api/v2/dataset/contribution_types.xml') );
                             
        // FIXME: CHEATING LIKE HELL HERE, TO BE FIXED
        return 1;
    }
    
    /**
     * Function to delete contribution Types 
     *      * @param int $contributionTypeId
     */
    function contributionTypeDelete($contributionTypeID) 
    {
        require_once 'CRM/Contribute/BAO/ContributionType.php';
        $del= CRM_Contribute_BAO_ContributionType::del($contributionTypeID);
    }
    
    /** 
     * Function to create Tag
     * 
     * @return int tag_id of created tag
     */    
    function tagCreate( $params = null )
    {
        if ( $params === null ) {
            $params = array(
                            'name'        => 'New Tag3',
                            'description' => 'This is description for New Tag 03',
                            'domain_id'   => '1'
                            );
        }
        
        require_once 'api/v2/Tag.php';
        $tag =& civicrm_tag_create($params);
        
        return $tag['tag_id'];
    }
    
    /** 
     * Function to delete Tag
     * 
     * @param  int $tagId   id of the tag to be deleted
     */    
    function tagDelete( $tagId )
    {
        require_once 'api/v2/Tag.php';
        $params['tag_id'] = $tagId;
        $result = civicrm_tag_delete( $params );
        if ( CRM_Utils_Array::value( 'is_error', $result ) ) {
            throw new Exception( 'Could not delete tag' );
        }
        return;
    }
    
    /** 
     * Add entity(s) to the tag
     * 
     * @param  array  $params 
     *
     */
    function entityTagAdd( $params )
    {
        $result = civicrm_entity_tag_add( $params );
        
        if ( CRM_Utils_Array::value( 'is_error', $result ) ) {
            throw new Exception( 'Error while creating entity tag' );
        }
        return ;
    }
    
    /**
     * Function to create contribution  
     * 
     * @param int $cID      contact_id
     * @param int $cTypeID  id of contribution type
     *
     * @return int id of created contribution
     */
    function contributionCreate($cID,$cTypeID)
    {
        require_once 'api/v2/Contribute.php';
        $params = array(
                        'domain_id'              => 1,
                        'contact_id'             => $cID,
                        'receive_date'           => date('Ymd'),
                        'total_amount'           => 100.00,
                        'contribution_type_id'   => $cTypeID,
                        'payment_instrument_id'  => 1,
                        'non_deductible_amount'  => 10.00,
                        'fee_amount'             => 50.00,
                        'net_amount'             => 90.00,
                        'trxn_id'                => 12345,
                        'invoice_id'             => 67890,
                        'source'                 => 'SSF',
                        'contribution_status_id' => 1,
                     // 'note'                   => 'Donating for Nobel Cause', *Fixme
                        );
        
        $contribution =& civicrm_contribution_add($params);

        return $contribution['id'];
        
    }
    
    /**
     * Function to delete contribution  
     * 
     * @param int $contributionId
     */
    function contributionDelete($contributionId)
    {
        require_once 'api/v2/Contribute.php';
        $params = array( 'contribution_id' => $contributionId );
        $val =& civicrm_contribution_delete( $params );
    }
    
    /**
     * Function to create an Event  
     * 
     * @param array $params  name-value pair for an event
     *
     * @return array $event
     */
    function eventCreate( $params = null )
    {
        if ( $params === null ) {
            $params = array(
                            'title'                   => 'Annual CiviCRM meet',
                            'summary'                 => 'If you have any CiviCRM related issues or want to track where CiviCRM is heading, Sign up now',
                            'description'             => 'This event is intended to give brief idea about progess of CiviCRM and giving solutions to common user issues',
                            'event_type_id'           => 1,
                            'is_public'               => 1,
                            'start_date'              => 20081021,
                            'end_date'                => 20081023,
                            'is_online_registration'  => 1,
                            'registration_start_date' => 20080601,
                            'registration_end_date'   => 20081015,
                            'max_participants'        => 100,
                            'event_full_text'         => 'Sorry! We are already full',
                            'is_monetory'             => 0, 
                            'is_active'               => 1,
                            'is_show_location'        => 0,
                            );
        }
        require_once 'api/v2/Event.php';
        $event =& civicrm_event_create( $params );
        
        return $event;
    }
    
    /**
     * Function to delete event  
     * 
     * @param int $id  ID of the event
     */
    function eventDelete( $id )
    {
        $params = array( 'event_id' => $id );
        civicrm_event_delete( $params );
    }
    
    /**
     * Function to delete participant 
     * 
     * @param int $participantID
     */
    
    function participantDelete( $participantID ) 
    {
        require_once 'api/v2/Participant.php';
        $params = array( 'id' => $participantID );
        $result = & civicrm_participant_delete( $params );
        if ( CRM_Utils_Array::value( 'is_error', $result ) ) {
            throw new Exception( 'Could not delete participant' );
        }
        return;
        
    }
    
    /**
     * Function to create participant payment
     *
     * @return int $id of created payment
     */
    
    function participantPaymentCreate( $participantID, $contributionID ) 
    {
        require_once 'api/v2/Participant.php';
        //Create Participant Payment record With Values
        $params = array(
                        'participant_id'       => $participantID,
                        'contribution_id'      => $contributionID
                        );
        
        $participantPayment = & civicrm_participant_payment_create( $params );
        
        if ( CRM_Utils_Array::value( 'is_error', $participantPayment ) ||
             ! CRM_Utils_Array::value( 'id', $participantPayment ) ) {
            throw new Exception( 'Could not create participant payment' );
        }
        
        return $participantPayment['id'];
    }
    
    /**
     * Function to delete participant payment
     * 
     * @param int $paymentID
     */
    
    function participantPaymentDelete( $paymentID ) 
    {
        require_once 'api/v2/Participant.php';
        $params = array( 'id' => $paymentID );        
        $result = & civicrm_participant_payment_delete( $params );
        if ( CRM_Utils_Array::value( 'is_error', $result ) ) {
            throw new Exception( 'Could not delete participant payment' );
        }
        
        return;
    }
    
    /** 
     * Function to add a Location
     * 
     * @return int location id of created location
     */    
    function locationAdd( $contactID ) 
    {
        $params = array('contact_id'             => $contactID,
                        'location_type'          => 'New Location Type',
                        'is_primary'             => 1,
                        'name'                   => 'Saint Helier St',
                        'county'                 => 'Marin',
                        'country'                => 'United States', 
                        'state_province'         => 'Michigan',
                        'supplemental_address_1' => 'Hallmark Ct', 
                        'supplemental_address_2' => 'Jersey Village'
                        );
        
        require_once 'api/v2/Location.php';
        $result = civicrm_location_add( $params );
        if ( civicrm_error( $result ) ) {
            throw new Exception( 'Could not create location', $result );
        }
        
        return $result;
    }
    
    /** 
     * Function to add a Location Type
     * 
     * @return int location id of created location
     */    
    function locationTypeCreate( ) 
    {
        $params = array('name'             => 'New Location Type',
                        'vcard_name'       => 'New Location Type',
                        'description'      => 'Location Type for Delete',
                        'is_active'        => 1,
                        );

        require_once 'CRM/Core/DAO/LocationType.php';
        $locationType =& new CRM_Core_DAO_LocationType( );
        $locationType->copyValues( $params );
        $locationType->save();
        return $locationType;
    }

    /** 
     * Function to add a Group
     *
     *@params array to add group
     *
     *@return int groupId of created group
     * 
     */ 
    function groupCreate( $params = null )
    {
        if ( $params === null ) { 
            $params = array(
                            'name'        => 'Test Group 1',
                            'domain_id'   => 1,
                            'title'       => 'New Test Group Created',
                            'description' => 'New Test Group Created',
                            'is_active'   => 1,
                            'visibility'  => 'Public Pages',
                            'group_type'  => array( '1' => 1,
                                                    '2' => 1 ), 
                            );
        }
        require_once 'api/v2/Group.php';
        $result = &civicrm_group_add( $params );
        
        return $result['result'];
    }    
    /** 
     * Function to delete a Group
     *
     * @param int $id 
     */ 
    function groupDelete( $gid )
    {
        $params['id'] = $gid;
        require_once 'api/v2/Group.php';
        $result = &civicrm_group_delete( $params );
    }

    /** 
     * Function to add a UF Join Entry
     *
     * @return int $id of created UF Join
     */ 
    function ufjoinCreate( $params = null ) {
        if ( $params === null ) { 
            $params = array(
                            'is_active'    => 1,
                            'module'       => 'CiviEvent',
                            'entity_table' => 'civicrm_event',
                            'entity_id'    => 3,
                            'weight'       => 1,
                            'uf_group_id'  => 1,
                            );
        }
        
        $result = crm_add_uf_join( $params );
        
        return $result;
    }    
    
    /** 
     * Function to delete a UF Join Entry
     *
     * @param array with missing uf_group_id   
     */ 
    function ufjoinDelete( $params = null ) {
        if ( $params === null ) { 
            $params = array(
                            'is_active'    => 1,
                            'module'       => 'CiviEvent',
                            'entity_table' => 'civicrm_event',
                            'entity_id'    => 3,
                            'weight'       => 1,
                            'uf_group_id'  => '',
                            );
        }
        
        $result = crm_add_uf_join( $params );
        
    }    
    
    /**
     * Function to create Group for a contact
     * 
     * @param int $contactId
     */
    function contactGroupCreate( $contactId )
    {
        $params = array(
                        'contact_id.1' => $contactId,
                        'group_id'     => 1 );
        
        civicrm_group_contact_add( $params );
    }
    
    /**
     * Function to delete Group for a contact
     * 
     * @param array $params
     */
    function contactGroupDelete( $contactId )
    {
        $params = array(
                        'contact_id.1' => $contactId,
                        'group_id'     => 1 );
        civicrm_group_contact_remove( $params );
    }
    
    /**
     * Function to create Activity 
     * 
     * @param int $contactId
     */
    function activityCreate( $params = null )
    {
        if ( $params === null ) { 
            $individualSourceID    = $this->individualCreate( );

            $contactParams = array( 'first_name'       => 'Julia',
                                    'Last_name'        => 'Anderson',
                                    'prefix'           => 'Ms',
                                    'email'            => 'julia_anderson@civicrm.org',
                                    'contact_type'     => 'Individual');

            $individualTargetID    = $this->individualCreate( $contactParams );

            $params = array(
                            'source_contact_id'   => $individualSourceID,
                            'target_contact_id'   => array( $individualTargetID ),
                            'assignee_contact_id' => array( $individualTargetID ),
                            'subject'             => 'Discussion on Apis for v2',
                            'activity_date_time'  => date('Ymd'),
                            'duration_hours'      => 30,
                            'duration_minutes'    => 20,
                            'location'            => 'Baker Street',
                            'details'             => 'Lets schedule a meeting',
                            'status_id'           => 1,
                            'activity_name'       => 'Meeting',
                            );
        }

        require_once 'api/v2/Activity.php';
        $result =& civicrm_activity_create($params, true);
        $result['target_contact_id']   = $individualTargetID;
        $result['assignee_contact_id']   = $individualTargetID;
        return $result;
    }
    
    /**
     * Function to create custom group
     * 
     * @param string $className
     * @param string $title  name of custom group
     */
    function customGroupCreate( $className,$title ) 
    {
        require_once 'api/v2/CustomGroup.php';
        $params = array(
                        'title'      => $title,
                        'class_name' => $className,
                        'domain_id'  => 1,                       
                        'style'      => 'Inline',
                        'is_active'  => 1
                        );
      
        $result =& civicrm_custom_group_create($params);      
        if ( CRM_Utils_Array::value( 'is_error', $result ) ||
             ! CRM_Utils_Array::value( 'id', $result) ) {
            throw new Exception( 'Could not create Custom Group' );
        }
        return $result;    
    }
    
    /**
     * Function to delete custom group
     * 
     * @param int    $customGroupID
     */
    function customGroupDelete( $customGroupID ) 
    { 
        $params['id'] = $customGroupID;
        $result = & civicrm_custom_group_delete($params);
        if ( CRM_Utils_Array::value( 'is_error', $result ) ) {
            throw new Exception( 'Could not delete custom group' );
        }
        return;
    }
    
    /**
     * Function to create custom field
     * 
     * @param int    $customGroupID
     * @param string $name  name of custom field
     */
    
    function customFieldCreate( $customGroupID, $name ) 
    {
        require_once 'api/v2/CustomGroup.php';
        $fieldParams = array(
                             'label'           => $name,
                             'name'            => $name,
                             'custom_group_id' => $customGroupID,
                             'data_type'       => 'String',
                             'html_type'       => 'Text',
                             'is_searchable'   =>  1, 
                             'is_active'        => 1,
                             );
        
        $result =& civicrm_custom_field_create($fieldParams);
        
        if ( civicrm_error( $result ) 
             || !( CRM_Utils_Array::value( 'customFieldId' , $result['result'] ) ) ) {
            throw new Exception( 'Could not create Custom Field' );
        }
        return $result;    
    }
    
    /**
     * Function to delete custom field
     * 
     * @param int $customFieldID
     */
    function customFieldDelete( $customFieldID ) 
    {
        //$this->fail( 'civicrm_custom_field_delete seems to be broken!');
        $params['result']['customFieldId'] = $customFieldID;
        $result = & civicrm_custom_field_delete($params);
        if ( civicrm_error( $result ) ) {
            throw new Exception( 'Could not delete custom field' );
        }
        return;
    }
    
    /**
     * Function to create note
     * 
     * @params array $params  name-value pair for an event
     * 
     * @return array $note
     */
    function noteCreate( $cId )
    {
        require_once 'api/v2/Note.php';
        $params = array(
                        'entity_table'  => 'civicrm_contact',
                        'entity_id'     => $cId,
                        'note'          => 'hello I am testing Note',
                        'contact_id'    => $cId,
                        'modified_date' => date('Ymd'),
                        'subject'       =>'Test Note', 
                        );
        $note =& civicrm_note_create( $params );
        return $note;
    }
    
    /**
     * Function to delete note
     * 
     * @params int $noteID
     * 
     */
    function noteDelete( $params )
    {

        require_once 'api/v2/Note.php';
        $result = & civicrm_note_delete( $params );

        if ( CRM_Utils_Array::value( 'is_error', $result ) ) {
            throw new Exception( 'Could not delete note' );
        }
    
        return;
    }    
     
    /**
     * Function to create custom field with Option Values
     * 
     * @param array    $customGroup
     * @param string $name  name of custom field
     */
    function customFieldOptionValueCreate( $customGroup, $name ) 
    {
        require_once 'api/v2/CustomGroup.php';
        
        $fieldParams = array ('custom_group_id' => $customGroup['id'],
                              'name'            => 'test_custom_group',
                              'label'           => 'Country',
                              'html_type'       => 'Select',
                              'data_type'       => 'String',
                              'weight'          => 4,
                              'is_required'     => 1,
                              'is_searchable'   => 0,
                              'is_active'       => 1
                              );
        
        $optionGroup = array('domain_id' => 1,
                             'name'      => 'option_group1',
                             'label'     => 'option_group_label1'
                             );
        
        $optionValue = array ('option_label'     => array('Label1', 'Label2'),
                              'option_value'     => array( 'value1', 'value2'),
                              'option_name'      => array( $name.'_1', $name.'_2'),
                              'option_weight'    => array(1, 2),
                              );
        
        $params = array_merge( $fieldParams, $optionGroup, $optionValue );
                
        $result =& civicrm_custom_field_create($params);
        if ( civicrm_error( $result ) 
             || !( CRM_Utils_Array::value( 'customFieldId', $result['result'] ) ) ) {
            throw new Exception( 'Could not create Custom Field' );
        }
        return $result;    
    }      
}

// -- set Emacs parameters --
// Local variables:
// mode: php;
// tab-width: 4
// c-basic-offset: 4
// c-hanging-comment-ender-p: nil
// indent-tabs-mode: nil
// End: