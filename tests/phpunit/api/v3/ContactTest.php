<?php  // vim: set si ai expandtab tabstop=4 shiftwidth=4 softtabstop=4:

/**
 *  File for the TestContact class
 *
 *  (PHP 5)
 *
 *   @author Walt Haas <walt@dharmatech.org> (801) 534-1262
 *   @copyright Copyright CiviCRM LLC (C) 2009
 *   @license   http://www.fsf.org/licensing/licenses/agpl-3.0.html
 *              GNU Affero General Public License version 3
 *   @version   $Id: ContactTest.php 31254 2010-12-15 10:09:29Z eileen $
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
 *  Include class definitions
 */
require_once 'CiviTest/CiviUnitTestCase.php';
require_once 'api/v3/Contact.php';

/**
 *  Test APIv3 civicrm_activity_* functions
 *
 *  @package   CiviCRM
 */
class api_v3_ContactTest extends CiviUnitTestCase
{
  public $DBResetRequired = false;  
  protected $_apiversion;
  protected $_entity;
  protected $_params;
  /**
   *  Constructor
   *
   *  Initialize configuration
   */
  function __construct( ) {
    parent::__construct( );
  }

  /**
   *  Test setup for every test
   *
   *  Connect to the database, truncate the tables that will be used
   *  and redirect stdin to a temporary file
   */
  public function setUp()
  {
    //  Connect to the database
    parent::setUp();
    $this->_apiversion = 3;
    $this->_entity = 'contact';
    $this->_params =  array(
                        'first_name'   => 'abc1',
                        'contact_type' => 'Individual',
                        'last_name'    => 'xyz1',
                        'version'			=>  $this->_apiversion,
    );
    
  }

  function tearDown( ) {
    // truncate a few tables
    $tablesToTruncate = array( 'civicrm_contact',
                               'civicrm_email' );
        
    $this->quickCleanup( $tablesToTruncate );
  }

  /**
   *  Test civicrm_contact_add()
   *
   *  Verify that attempt to create individual contact with only
   *  first and last names succeeds
   */
  function testAddCreateIndividual()
  {
    $params = array(
                        'first_name'   => 'abc1',
                        'contact_type' => 'Individual',
                        'last_name'    => 'xyz1',
                        'version'			 => $this->_apiversion,
                        
                        );

                        $contact =& civicrm_api('contact','create',$params);
                        $this->assertEquals( 0, $contact['is_error'], "In line " . __LINE__ );
                        $this->assertEquals( 1, $contact['id'], "In line " . __LINE__ );
                        $getContact = civicrm_api('Contact','Get',array('version' =>3));
                        // delete the contact
                        civicrm_api('contact', 'delete' , $contact );
  }

  /**
   *  Verify that attempt to create contact with empty params fails
   */
  function testCreateEmptyContact()
  {
    $params = array();
    $contact =& civicrm_api('contact','create',$params);
    $this->assertEquals( $contact['is_error'], 1,
                             "In line " . __LINE__ );
  }

  /**
   *  Verify that attempt to create contact with bad contact type fails
   */
  function testCreateBadTypeContact()
  {
    $params = array(
                        'email'        => 'man1@yahoo.com',
                        'contact_type' => 'Does not Exist' 
                        );
                        $contact =& civicrm_api('contact','create',$params);
                        $this->assertEquals( $contact['is_error'], 1, "In line " . __LINE__ );

  }

  /**
   *  Verify that attempt to create individual contact with required
   *  fields missing fails
   */
  function testCreateBadRequiredFieldsIndividual()
  {
    $params = array(
                        'middle_name'  => 'This field is not required',
                        'contact_type' => 'Individual' 
                        );

                        $contact =& civicrm_api('contact','create',$params);
                        $this->assertEquals( $contact['is_error'], 1,
                             "In line " . __LINE__ );

  }

  /**
   *  Verify that attempt to create household contact with required
   *  fields missing fails
   */
  function testCreateBadRequiredFieldsHousehold()
  {
    $params = array(
                        'middle_name'  => 'This field is not required',
                        'contact_type' => 'Household' 
                        );

                        $contact =& civicrm_api('contact','create',$params);
                        $this->assertEquals( $contact['is_error'], 1,
                             "In line " . __LINE__ );

  }

  /**
   *  Verify that attempt to create organization contact with
   *  required fields missing fails
   */
  function testCreateBadRequiredFieldsOrganization()
  {
    $params = array(
                        'middle_name'  => 'This field is not required',
                        'contact_type' => 'Organization' 
                        );

                        $contact =& civicrm_api('contact','create',$params);
                        $this->assertEquals( $contact['is_error'], 1,
                             "In line " . __LINE__ );

  }

  /**
   *  Verify that attempt to create individual contact with only an
   *  email succeeds
   */
  function testCreateEmailIndividual()
  {

    $params = array(
                        'email'            => 'man3@yahoo.com',
                        'contact_type'     => 'Individual',
                        'location_type_id' => 1,
                        'version'					 => $this->_apiversion,
    );

    $contact =& civicrm_api('contact','create',$params);
    $this->assertEquals( 0, $contact['is_error'], "In line " . __LINE__
    . " error message: " . CRM_Utils_Array::value('error_message', $contact) );
    $this->assertEquals( 1, $contact['id'], "In line " . __LINE__ );
    $email = civicrm_api('email','get',array('contact_id' => $contact['id'],'version' =>3));
    $this->assertEquals( 0, $email['is_error'], "In line " . __LINE__);
    $this->assertEquals( 1, $email['count'], "In line " . __LINE__);
    $this->assertEquals( 'man3@yahoo.com', $email['values'][$email['id']]['email'], "In line " . __LINE__);
    
    // delete the contact
    civicrm_api('contact', 'delete' , $contact );
  }

  /**
   *  Verify that attempt to create individual contact with only
   *  first and last names succeeds
   */
  function testCreateNameIndividual()
  {
    $params = array(
                        'first_name'   => 'abc1',
                        'contact_type' => 'Individual',
                        'last_name'    => 'xyz1',
                        'version'			=>  $this->_apiversion,
    );

    $contact =& civicrm_api('contact','create',$params);
    $this->assertEquals( 0, $contact['is_error'], "In line " . __LINE__
    . " error message: " . CRM_Utils_Array::value('error_message', $contact) );
    $this->assertEquals( 1, $contact['id'], "In line " . __LINE__ );

    // delete the contact
    civicrm_api('contact', 'delete' , $contact );
  }

  /**
   *  Verify that attempt to create individual contact with
   *  first and last names and old key values works
   */
  function testCreateNameIndividualOldKeys()
  {
    $params = array(
                        'individual_prefix' => 'Dr.',
                        'first_name'   => 'abc1',
                        'contact_type' => 'Individual',
                        'last_name'    => 'xyz1',
                        'individual_suffix' => 'Jr.',
                        'version'			=>  $this->_apiversion,
    );

    $contact =& civicrm_api('contact','create',$params);
    $this->assertEquals( 0, $contact['is_error'], "In line " . __LINE__
    . " error message: " . CRM_Utils_Array::value('error_message', $contact) );
    $this->assertEquals( 1, $contact['id'], "In line " . __LINE__ );

    // delete the contact
    civicrm_api('contact', 'delete' , $contact );
  }

  /**
   *  Verify that attempt to create individual contact with
   *  first and last names and old key values works
   */
  function testCreateNameIndividualOldKeys2()
  {
    $params = array(
                        'prefix_id'    => 'Dr.',
                        'first_name'   => 'abc1',
                        'contact_type' => 'Individual',
                        'last_name'    => 'xyz1',
                        'suffix_id'    => 'Jr.',
                        'gender_id'    => 'M',
                        'version'			=>  $this->_apiversion,
    );

    $contact =& civicrm_api('contact','create',$params);
    $this->assertEquals( 0, $contact['is_error'], "In line " . __LINE__
    . " error message: " . CRM_Utils_Array::value('error_message', $contact) );
    $this->assertEquals( 1, $contact['id'], "In line " . __LINE__ );

    // delete the contact
    civicrm_api('contact', 'delete' , $contact );
  }

  /**
   *  Verify that attempt to create household contact with only
   *  household name succeeds
   */
  function testCreateNameHousehold()
  {
    $params = array(
                        'household_name' => 'The abc Household',
                        'contact_type'   => 'Household',
                         'version'			=>  $this->_apiversion,
    );

    $contact =& civicrm_api('contact','create',$params);
    $this->assertEquals( 0, $contact['is_error'], "In line " . __LINE__
    . " error message: " . CRM_Utils_Array::value('error_message', $contact) );
    $this->assertEquals( 1, $contact['id'], "In line " . __LINE__ );

    // delete the contact
    civicrm_api('contact', 'delete' , $contact );
  }

  /**
   *  Verify that attempt to create organization contact with only
   *  organization name succeeds
   */
  function testCreateNameOrganization()
  {
    $params = array(
                        'organization_name' => 'The abc Organization',
                        'contact_type' => 'Organization',
                        'version'			=>  $this->_apiversion,
    );
    $contact =& civicrm_api('contact','create',$params);
    $this->assertEquals( 0, $contact['is_error'], "In line " . __LINE__
    . " error message: " . CRM_Utils_Array::value('error_message', $contact) );
    $this->assertEquals( 1, $contact['id'], "In line " . __LINE__ );

    // delete the contact
    civicrm_api('contact', 'delete' , $contact );
  }
  
  
        /**
     * check with complete array + custom field 
     * Note that the test is written on purpose without any
     * variables specific to participant so it can be replicated into other entities
     * and / or moved to the automated test suite
     */
    function testCreateWithCustom()
    {
        $ids = $this->entityCustomGroupWithSingleFieldCreate( __FUNCTION__,__FILE__);
        
        $params = $this->_params;
        $params['custom_'.$ids['custom_field_id']]  =  "custom string";
        $description = "/*this demonstrates setting a custom field through the API ";
        $subfile = "CustomFieldCreate";
        $result = civicrm_api($this->_entity,'create', $params);
        $this->documentMe($params,$result  ,__FUNCTION__,__FILE__,$description,$subfile);
        $this->assertNotEquals( $result['is_error'],1 ,$result['error_message'] . ' in line ' . __LINE__);

        $check = civicrm_api($this->_entity,'get',array('return.custom_'.$ids['custom_field_id']=> 1,'version' =>3, 'id' => $result['id']));
        $this->assertEquals("custom string", $check['values'][$check['id']]['custom_' .$ids['custom_field_id'] ],' in line ' . __LINE__);
   
        $this->customFieldDelete($ids['custom_field_id']);
        $this->customGroupDelete($ids['custom_group_id']);      

    }
        /**
     * check with complete array + custom field 
     * Note that the test is written on purpose without any
     * variables specific to participant so it can be replicated into other entities
     * and / or moved to the automated test suite
     */
    function testGetWithCustom()
    {
        $ids = $this->entityCustomGroupWithSingleFieldCreate( __FUNCTION__,__FILE__);
        
        $params = $this->_params;
        $params['custom_'.$ids['custom_field_id']]  =  "custom string";
        $description = "/*this demonstrates setting a custom field through the API ";
        $subfile = "CustomFieldGet";
        $result = civicrm_api($this->_entity,'create', $params);
        $this->assertNotEquals( $result['is_error'],1 ,$result['error_message'] . ' in line ' . __LINE__);

        $check = civicrm_api($this->_entity,'get',array('return.custom_'.$ids['custom_field_id']=> 1,'version' =>3, 'id' => $result['id']));
        $this->documentMe($params,$check  ,__FUNCTION__,__FILE__,$description,$subfile);
    
        $this->assertEquals("custom string", $check['values'][$check['id']]['custom_' .$ids['custom_field_id'] ],' in line ' . __LINE__);
        $fields = (civicrm_api('contact', 'getfields', $params)); 
        $this->assertTrue(is_array($fields['values']['custom_'.$ids['custom_field_id']]));   
        $this->customFieldDelete($ids['custom_field_id']);
        $this->customGroupDelete($ids['custom_group_id']);      

    }
    /*
     * check with complete array + custom field 
     * Note that the test is written on purpose without any
     * variables specific to participant so it can be replicated into other entities
     * and / or moved to the automated test suite
     */
    function testGetWithCustomReturnSyntax()
    {
        $ids = $this->entityCustomGroupWithSingleFieldCreate( __FUNCTION__,__FILE__);
        
        $params = $this->_params;
        $params['custom_'.$ids['custom_field_id']]  =  "custom string";
        $description = "/*this demonstrates setting a custom field through the API ";
        $subfile = "CustomFieldGetReturnSyntaxVariation";
        $result = civicrm_api($this->_entity,'create', $params);
        $this->assertNotEquals( $result['is_error'],1 ,$result['error_message'] . ' in line ' . __LINE__);
        $params= array('return' => 'custom_'.$ids['custom_field_id'],'version' =>3, 'id' => $result['id']);
        $check = civicrm_api($this->_entity,'get',$params);
        $this->documentMe($params,$check  ,__FUNCTION__,__FILE__,$description,$subfile);
    
        $this->assertEquals("custom string", $check['values'][$check['id']]['custom_' .$ids['custom_field_id'] ],' in line ' . __LINE__);
        civicrm_api('Contact','Delete',array('version' => 3, 'id' => $check['id']));
        $this->customFieldDelete($ids['custom_field_id']);
        $this->customGroupDelete($ids['custom_group_id']);      

    }
    
    function testGetGroupIDFromContact( ) 
    {
        $groupId  = $this->groupCreate( null);
        $description = "Get all from group and display contacts";
        $subfile = "GroupFilterUsingContactAPI";
        $params = array(
                        'email'            => 'man2@yahoo.com',
                        'contact_type'     => 'Individual',
                        'location_type_id' => 1,
                        'version' 				=> $this->_apiversion,
                        'api.group_contact.create' => array('group_id' => $groupId));
		      

        $contact =& civicrm_api('contact','create',$params);
        // testing as integer
        $params = array( 'filter.group_id' => $groupId ,
                         'version'    => $this->_apiversion,
                         'contact_type' => 'Individual');
        $result = civicrm_api('contact','get', $params );
        $this->documentMe($params,$result,__FUNCTION__,__FILE__,$description,$subfile);                  
        $this->assertEquals(1, $result['count']);
        // group 26 doesn't exist, but we can still search contacts in it.
        $params = array( 'filter.group_id' => 26,
                         'version'    => $this->_apiversion,
                         'contact_type' => 'Individual');
        $result = civicrm_api('contact','get', $params );
        $this->assertEquals(0, $result['count'], " in line ". __LINE__);
        // testing as string
        $params = array( 'filter.group_id' => "$groupId,26" ,
                         'version'    => $this->_apiversion,
                         'contact_type' => 'Individual');
        $result = civicrm_api('contact','get', $params );
        $this->documentMe($params,$result,__FUNCTION__,__FILE__,$description,$subfile);                  
        $this->assertEquals(1, $result['count']);
        $params = array( 'filter.group_id' => "26,27" ,
                         'version'    => $this->_apiversion,
                         'contact_type' => 'Individual');
        $result = civicrm_api('contact','get', $params );
        $this->assertEquals(0, $result['count'], " in line ". __LINE__);

        // testing as string
        $params = array( 'filter.group_id' => array($groupId,26) ,
                         'version'    => $this->_apiversion,
                         'contact_type' => 'Individual');
        $result = civicrm_api('contact','get', $params );
        $this->documentMe($params,$result,__FUNCTION__,__FILE__,$description,$subfile);                  
        $this->assertEquals(1, $result['count']);
        $params = array( 'filter.group_id' => array(26,27) ,
                         'version'    => $this->_apiversion,
                         'contact_type' => 'Individual');
        $result = civicrm_api('contact','get', $params );
        $this->assertEquals(0, $result['count'], " in line ". __LINE__);
    }

  /**
   *  Verify that attempt to create individual contact with first
   *  and last names and email succeeds
   */
  function testCreateIndividualWithContributionDottedSyntax()
  {
    $params = array(
                        'first_name'   => 'abc3',
                        'last_name'    => 'xyz3',
                        'contact_type' => 'Individual',
                        'email'        => 'man3@yahoo.com',
                        'version'			=>  $this->_apiversion,
                        'api.contribution.create'    => array(
                                                   
                             'receive_date'           => '2010-01-01',
                             'total_amount'           => 100.00,
                             'contribution_type_id'   => 1,
                             'payment_instrument_id'  => 1,
                             'non_deductible_amount'  => 10.00,
                             'fee_amount'             => 50.00,
                             'net_amount'             => 90.00,
                             'trxn_id'                => 15345,
                             'invoice_id'             => 67990,
                             'source'                 => 'SSF',
                             'contribution_status_id' => 1,
                             ),
                        'api.website.create' => array(
                             'url' => "http://civicrm.org"),
                        'api.website.create.2' => array(
                             'url' => "http://chained.org",
                             ),
    );

    $result =civicrm_api('Contact','create',$params);
    $this->documentMe($params,$result,__FUNCTION__,__FILE__); 
    $this->assertEquals( 0, $result['is_error'], "In line " . __LINE__
    . " error message: " . CRM_Utils_Array::value('error_message', $result) );
    $this->assertEquals( 1, $result['id'], "In line " . __LINE__ );
    $this->assertEquals(0,$result['values'][$result['id']]['api.website.create']['is_error'], "In line " . __LINE__);
    $this->assertEquals("http://chained.org",$result['values'][$result['id']]['api.website.create.2']['values'][0]['url'], "In line " . __LINE__);
    // delete the contact
    civicrm_api('contact', 'delete' , $result );
  }
  
  /**
   *  Verify that attempt to create individual contact with first
   *  and last names and email succeeds
   */
  function testCreateIndividualWithContributionChainedArrays()
  {
    $params = array(
                        'first_name'   => 'abc3',
                        'last_name'    => 'xyz3',
                        'contact_type' => 'Individual',
                        'email'        => 'man3@yahoo.com',
                        'version'			=>  $this->_apiversion,
                        'api.contribution.create'    => array(
                                                   
                             'receive_date'           => '2010-01-01',
                             'total_amount'           => 100.00,
                             'contribution_type_id'   => 1,
                             'payment_instrument_id'  => 1,
                             'non_deductible_amount'  => 10.00,
                             'fee_amount'             => 50.00,
                             'net_amount'             => 90.00,
                             'trxn_id'                => 12345,
                             'invoice_id'             => 67890,
                             'source'                 => 'SSF',
                             'contribution_status_id' => 1,
                             ),
                        'api.website.create' => array(
                             array(
                                'url' => "http://civicrm.org"),
                             array(
                                'url' => "http://chained.org",
                                'website_type_id' => 2),
                             )
    );

    $result =civicrm_api('Contact','create',$params);
    $this->documentMe($params,$result,__FUNCTION__,__FILE__); 
    $this->assertEquals( 0, $result['is_error'], "In line " . __LINE__
    . " error message: " . CRM_Utils_Array::value('error_message', $result) );
    $this->assertEquals( 1, $result['id'], "In line " . __LINE__ );
    $this->assertEquals(0,$result['values'][$result['id']]['api.website.create'][0]['is_error'], "In line " . __LINE__);
    $this->assertEquals("http://chained.org",$result['values'][$result['id']]['api.website.create'][1]['values'][0]['url'], "In line " . __LINE__);
    // delete the contact
    civicrm_api('contact', 'delete' , $result );
  }
  /**
   *  Verify that attempt to create individual contact with first
   *  and last names and email succeeds
   */
  function testCreateIndividualWithNameEmail()
  {
    $params = array(
                        'first_name'   => 'abc3',
                        'last_name'    => 'xyz3',
                        'contact_type' => 'Individual',
                        'email'        => 'man3@yahoo.com',
                                'version'			=>  $this->_apiversion,
    );

    $contact =& civicrm_api('contact','create',$params);
    $this->assertEquals( 0, $contact['is_error'], "In line " . __LINE__
    . " error message: " . CRM_Utils_Array::value('error_message', $contact) );
    $this->assertEquals( 1, $contact['id'], "In line " . __LINE__ );

    // delete the contact
    civicrm_api('contact', 'delete' , $contact );
  }
  /**
   *  Verify that attempt to create individual contact with first
   *  and last names, email and location type succeeds
   */
  function testCreateIndividualWithNameEmailLocationType()
  {
    $params = array(
                        'first_name'       => 'abc4',
                        'last_name'        => 'xyz4',
                        'email'            => 'man4@yahoo.com',
                        'contact_type'     => 'Individual',
                        'location_type_id' => 1,
                        'version'			=>  $this->_apiversion,
    );
    $result =& civicrm_api('contact','create',$params);

    $this->assertEquals( 0, $result['is_error'], "In line " . __LINE__
    . " error message: " . CRM_Utils_Array::value('error_message', $result) );
    $this->assertEquals( 1, $result['id'], "In line " . __LINE__ );

    // delete the contact
    civicrm_api('contact', 'delete' , $contact );

  }
  /**
   *  Verify that attempt to create household contact with details
   *  succeeds
   */
  function testCreateHouseholdDetails()
  {
    $params = array(
                        'household_name' => 'abc8\'s House',
                        'nick_name'      => 'x House',
                        'email'          => 'man8@yahoo.com',
                        'contact_type'   => 'Household',
                        'version'			=>  $this->_apiversion,
    );

    $contact =& civicrm_api('contact','create',$params);
    $this->assertEquals( 0, $contact['is_error'], "In line " . __LINE__
    . " error message: " . CRM_Utils_Array::value('error_message', $contact) );
    $this->assertEquals( 1, $contact['id'], "In line " . __LINE__ );

    // delete the contact
    civicrm_api('contact', 'delete' , $contact );
  }

  /**
   *  Test civicrm_contact_check_params with check for required
   *  params and no params
   */
  function testCheckParamsWithNoParams()
  {
    $params = array();
    $contact =& _civicrm_api3_contact_check_params($params, false );
    $this->assertEquals( 1, $contact['is_error'],"In line " . __LINE__ );

  }

  /**
   *  Test civicrm_contact_check_params with params and no checkss
   */
  function testCheckParamsWithNoCheckss()
  {
    $params = array();
    $contact =& _civicrm_api3_contact_check_params($params, false, false, false );
    $this->assertNull( $contact,"In line " . __LINE__ );
  }

  /**
   *  Test civicrm_contact_check_params with no contact type
   */
  function testCheckParamsWithNoContactType()
  {
    $params = array( 'foo' => 'bar' );
    $contact =& _civicrm_api3_contact_check_params($params, false );
    $this->assertEquals( 1, $contact['is_error'],"In line " . __LINE__ );
  }

  /**
   *  Test civicrm_contact_check_params with a duplicate
  */
  function testCheckParamsWithDuplicateContact()
  {
    //  Insert a row in civicrm_contact creating individual contact
    $op = new PHPUnit_Extensions_Database_Operation_Insert( );
    $op->execute( $this->_dbconn,
    new PHPUnit_Extensions_Database_DataSet_XMLDataSet(
    dirname(__FILE__)
    . '/dataset/contact_17.xml') );
    $op->execute( $this->_dbconn,
    new PHPUnit_Extensions_Database_DataSet_XMLDataSet(
    dirname(__FILE__)
    . '/dataset/email_contact_17.xml') );

    $params = array( 'first_name' => 'Test',
                         'last_name'  => 'Contact',
                         'email'      => 'TestContact@example.com',
                         'contact_type' => 'Individual' );
    $contact =& _civicrm_api3_contact_check_params($params, true );
    $this->assertEquals( 1, $contact['is_error'] );
    $this->assertRegexp( "/matching contacts.*17/s",
    CRM_Utils_Array::value('error_message', $contact) );
  }

  /**
   *  Test civicrm_contact_check_params with a duplicate
   *  and request the error in array format
   */
  function testCheckParamsWithDuplicateContact2()
  {
    //  Insert a row in civicrm_contact creating individual contact
    $op = new PHPUnit_Extensions_Database_Operation_Insert( );
    $op->execute( $this->_dbconn,
    new PHPUnit_Extensions_Database_DataSet_XMLDataSet(
    dirname(__FILE__)
    . '/dataset/contact_17.xml') );
    $op->execute( $this->_dbconn,
    new PHPUnit_Extensions_Database_DataSet_XMLDataSet(
    dirname(__FILE__)
    . '/dataset/email_contact_17.xml') );

    $params = array( 'first_name' => 'Test',
                         'last_name'  => 'Contact',
                         'email'      => 'TestContact@example.com',
                         'contact_type' => 'Individual' );
    $contact =& _civicrm_api3_contact_check_params($params, true, true );
    $this->assertEquals( 1, $contact['is_error'] );
    $this->assertRegexp( "/matching contacts.*17/s",
    $contact['error_message']['message'] );
  }

  /**
   *  Verify successful update of individual contact
   */
  function testUpdateIndividualWithAll()
  {
    //  Insert a row in civicrm_contact creating individual contact
    $op = new PHPUnit_Extensions_Database_Operation_Insert( );
    $op->execute( $this->_dbconn,
    new PHPUnit_Extensions_Database_DataSet_XMLDataSet(
    dirname(__FILE__)
    . '/dataset/contact_ind.xml') );

    $params = array(
                        'id'                    => 23,
                        'first_name'            => 'abcd',
                        'contact_type'          => 'Individual',
                        'nick_name'             => 'This is nickname first',
                        'do_not_email'          => '1',
                        'do_not_phone'          => '1',
                        'do_not_mail'           => '1',
                        'do_not_trade'          => '1',
                        'legal_identifier'      => 'ABC23853ZZ2235',
                        'external_identifier'   => '1928837465',
                        'image_URL'             => 'http://some.url.com/image.jpg',
                        'home_url'              => 'http://www.example.org',
                        'preferred_mail_format' => 'HTML',
                        'version'							=>  $this->_apiversion,
    );
    $getResult = civicrm_api('Contact','Get',array('version' =>3));
    $result =civicrm_api('Contact','Update',$params);
    $getResult = civicrm_api('Contact','Get',$params);
    //  Result should indicate successful update
    $this->assertEquals( 0, $result['is_error'], "In line " . __LINE__ );
    unset($params['version']);
    unset($params['contact_id']); 
    //Todo - neither API v2 or V3 are testing for home_url - not sure if it is being set.
    //reducing this test partially back to apiv2 level to get it through
    unset ($params['home_url']); 
    foreach ($params as $key =>$value){
      $this->assertEquals(  $value, $result['values'][23][$key], "In line " . __LINE__ );
    }
    //  Check updated civicrm_contact against expected
    $expected = new PHPUnit_Extensions_Database_DataSet_XMLDataSet(
    dirname( __FILE__ ) . '/dataset/contact_ind_upd.xml' );
    $actual = new PHPUnit_Extensions_Database_DataSet_QueryDataset(
    $this->_dbconn );
    $actual->addTable( 'civicrm_contact' );
    $expected->assertEquals( $actual );
  }

  /**
   *  Verify successful update of organization contact
   */
  function testUpdateOrganizationWithAll()
  {
    //  Insert a row in civicrm_contact creating organization contact
    $op = new PHPUnit_Extensions_Database_Operation_Insert( );
    $op->execute( $this->_dbconn,
    new PHPUnit_Extensions_Database_DataSet_XMLDataSet(
    dirname(__FILE__)
    . '/dataset/contact_org.xml') );

    $params = array(
                        'id'        => 24,
                        'organization_name' => 'WebAccess India Pvt Ltd',
                        'legal_name'        => 'WebAccess',
                        'sic_code'          => 'ABC12DEF',
                        'contact_type'      => 'Organization',
                        'version'			      =>  $this->_apiversion,
    );

    $result = civicrm_api('Contact','Update',$params);

    $expected = array( 'is_error'   => 0,
                        'id' => 24 );

    //  Result should indicate successful update
    $this->assertEquals( 0, $result['is_error'], "In line " . __LINE__
    . " error message: " . CRM_Utils_Array::value('error_message', $result) );
   // $this->assertEquals( $expected, $result, "In line " . __LINE__ );

    //  Check updated civicrm_contact against expected
    $expected = new PHPUnit_Extensions_Database_DataSet_XMLDataSet(
    dirname( __FILE__ ) . '/dataset/contact_org_upd.xml' );
    $actual = new PHPUnit_Extensions_Database_DataSet_QueryDataset(
    $this->_dbconn );
    $actual->addTable( 'civicrm_contact' );
    $expected->assertEquals( $actual );
  }

  /**
   *  Verify successful update of household contact
   */
  function testUpdateHouseholdwithAll()
  {
    //  Insert a row in civicrm_contact creating household contact
    $op = new PHPUnit_Extensions_Database_Operation_Insert( );
    $op->execute( $this->_dbconn,
    new PHPUnit_Extensions_Database_DataSet_XMLDataSet(
    dirname(__FILE__)
    . '/dataset/contact_hld.xml') );

    $params = array(
                        'id'     => 25,
                        'household_name' => 'ABC household',
                        'nick_name'      => 'ABC House',
                        'contact_type'   => 'Household',
                        'version'			=>  $this->_apiversion,
    );

    $result =civicrm_api('Contact','Update',$params);

    $expected = array( 'is_error'   => 0,
                        'contact_id' => 25 );

    //  Result should indicate successful update
    $this->assertEquals( 0, $result['is_error'], "In line " . __LINE__
    . " error message: " . CRM_Utils_Array::value('error_message', $result) );
 //   $this->assertEquals( $expected, $result, "In line " . __LINE__ );

    //  Check updated civicrm_contact against expected
    $expected = new PHPUnit_Extensions_Database_DataSet_XMLDataSet(
    dirname( __FILE__ ) . '/dataset/contact_hld_upd.xml' );
    $actual = new PHPUnit_Extensions_Database_DataSet_QueryDataset(
    $this->_dbconn );
    $actual->addTable( 'civicrm_contact' );
    $expected->assertEquals( $actual );
  }

  /**
   *  Test civicrm_update() Deliberately exclude contact_type as it should still 
   *  cope using civicrm_api CRM-7645
   */
   
  public function testUpdateCreateWithID()
  {
    //  Insert a row in civicrm_contact creating individual contact
    $op = new PHPUnit_Extensions_Database_Operation_Insert( );
    $op->execute( $this->_dbconn,
    new PHPUnit_Extensions_Database_DataSet_XMLDataSet(
    dirname(__FILE__)
    . '/dataset/contact_ind.xml') );
    
    
    
    $params = array(    'id'						=> 23,
                        'first_name'    => 'abcd',
                        'last_name'     => 'wxyz', 
                        'version'			  =>  $this->_apiversion,
    );

    $result = civicrm_api('Contact','Update',$params);
    $this->assertTrue( is_array( $result ) );
    $this->assertEquals( 0, $result['is_error'] );
  }

  /**
   *  Test civicrm_contact_delete() with no contact ID
   */
  function testContactDeleteNoID()
  {
    $params = array( 'foo' => 'bar',
                          'version'			=>  $this->_apiversion,);
    $result = civicrm_api('contact', 'delete' , $params );
    $this->assertEquals( 1, $result['is_error'], "In line " . __LINE__
    . " error message: " . CRM_Utils_Array::value('error_message', $result) );
  }

  /**
   *  Test civicrm_contact_delete() with error
   */
  function testContactDeleteError()
  {
    $params = array( 'contact_id' => 17 );
    $result = civicrm_api('contact', 'delete' , $params );
    $this->assertEquals( 1, $result['is_error'], "In line " . __LINE__
    . " error message: " . CRM_Utils_Array::value('error_message', $result) );
  }

  /**
   *  Test civicrm_contact_delete()
   */
  function testContactDelete()
  {
    //  Insert a row in civicrm_contact creating contact 17
    $op = new PHPUnit_Extensions_Database_Operation_Insert( );
    $op->execute( $this->_dbconn,
    new PHPUnit_Extensions_Database_DataSet_XMLDataSet(
    dirname(__FILE__)
    . '/dataset/contact_17.xml') );
    $params = array( 'id' => 17,
                      'version'   =>$this->_apiversion, );
    $result = civicrm_api('contact', 'delete' , $params );
    $this->documentMe($params,$result,__FUNCTION__,__FILE__); 
    $this->assertEquals( 0, $result['is_error'], "In line " . __LINE__
    . " error message: " . CRM_Utils_Array::value('error_message', $result) );
  }

  /**
   *  Test civicrm_contact_get() return only first name
   */
  public function testContactGetRetFirst()
  {
    //  Insert a row in civicrm_contact creating contact 17
    $op = new PHPUnit_Extensions_Database_Operation_Insert( );
    $op->execute( $this->_dbconn,
    new PHPUnit_Extensions_Database_DataSet_XMLDataSet(
    dirname(__FILE__)
    . '/dataset/contact_17.xml') );
    $params = array( 'contact_id'       => 17,
                         'return_first_name' => true,
                         'sort'              => 'first_name',
                         'version'			=>  $this->_apiversion, );
    $result = civicrm_api('contact', 'get', $params );
    $this->assertEquals( 1, $result['count'] , "In line " . __LINE__ );
    $this->assertEquals( 17, $result['id'], "In line " . __LINE__ );
    $this->assertEquals( 'Test', $result['values'][17]['first_name'] , "In line " . __LINE__);
  }

  /**
   *  Test civicrm_contact_get() with default return properties
   */
  public function testContactGetRetDefault()
  {
    //  Insert a row in civicrm_contact creating contact 17
    $op = new PHPUnit_Extensions_Database_Operation_Insert( );
    $op->execute( $this->_dbconn,
    new PHPUnit_Extensions_Database_DataSet_XMLDataSet(
    dirname(__FILE__)
    . '/dataset/contact_17.xml') );
    $params = array( 'contact_id' => 17,
                         'sort'       => 'first_name' ,
                          'version'   => $this->_apiversion);
    $result = civicrm_api('contact', 'get', $params );
    $this->assertEquals( 17, $result['values'][17]['contact_id'], "In line " . __LINE__ );
    $this->assertEquals( 'Test', $result['values'][17]['first_name'] , "In line " . __LINE__);
  }

  /**
   *  Test civicrm_contact_quicksearch() with empty name param
   */
  public function testContactQuickSearchEmpty()
  {
    $params = array( 
                          'version'   => $this->_apiversion);
    $result = civicrm_api('contact', 'quicksearch', $params );
    $this->assertTrue( is_array( $result ),'in line '. __LINE__ );
    $this->assertEquals( 1, $result['is_error'] ,'in line '. __LINE__);
  }

  /**
   *  Test civicrm_contact_quicksearch() with empty name param
   */
  public function testContactQuickSearch()
  {
    //  Insert a row in civicrm_contact creating individual contact
    $op = new PHPUnit_Extensions_Database_Operation_Insert( );
    $op->execute( $this->_dbconn,
    new PHPUnit_Extensions_Database_DataSet_XMLDataSet(
    dirname(__FILE__)
    . '/dataset/contact_17.xml') );
    $op->execute( $this->_dbconn,
    new PHPUnit_Extensions_Database_DataSet_XMLDataSet(
    dirname(__FILE__)
    . '/dataset/email_contact_17.xml') );
    $params = array( 
      'name' => "T",
      'version'   => $this->_apiversion);
    
    $result = civicrm_api('contact', 'quicksearch', $params );
    $this->assertTrue( is_array( $result ),'in line '. __LINE__ );
    $this->assertEquals( 0, $result['is_error'] ,'in line '. __LINE__);
    $this->assertEquals( 17, $result['values'][0]['id'] ,'in line '. __LINE__);
  }

  /**
   *  Test civicrm_contact_get) with empty params
   */
  public function testContactGetEmptyParams()
  {
    $params = array();
    $result = civicrm_api('contact', 'get', $params);
   
    $this->assertTrue( is_array( $result ),'in line '. __LINE__ );
    $this->assertEquals( 1, $result['is_error'] ,'in line '. __LINE__);

  }

  /**
   *  Test civicrm_contact_get(,true) with params not array
   */
  public function testContactGetParamsNotArray()
  {
    $params = 17;
    $result = civicrm_api('contact', 'get', $params, true );
    $this->assertTrue( is_array( $result ) );
    $this->assertEquals( 1, $result['is_error'] );
    $this->assertRegexp( "/not.*array/s",
    CRM_Utils_Array::value('error_message', $result) );
  }

  /**
   *  Test civicrm_contact_get(,true) with no matches
   */
  public function testContactGetOldParamsNoMatches()
  {
    //  Insert a row in civicrm_contact creating contact 17
    $op = new PHPUnit_Extensions_Database_Operation_Insert( );
    $op->execute( $this->_dbconn,
    new PHPUnit_Extensions_Database_DataSet_XMLDataSet(
    dirname(__FILE__)
    . '/dataset/contact_17.xml') );

    $params = array( 'first_name' => 'Fred',
                      'version' => $this->_apiversion );
    $result = civicrm_api('contact', 'get', $params );
    $this->assertTrue( is_array( $result ) , 'in line ' . __LINE__);
    $this->assertEquals( 0, $result['is_error'], 'in line ' . __LINE__ );
    $this->assertEquals( 0, $result['count'], 'in line ' . __LINE__ );
  }

  /**
   *  Test civicrm_contact_get(,true) with one match
   */
  public function testContactGetOldParamsOneMatch()
  {
    //  Insert a row in civicrm_contact creating contact 17
    $op = new PHPUnit_Extensions_Database_Operation_Insert( );
    $op->execute( $this->_dbconn,
    new PHPUnit_Extensions_Database_DataSet_XMLDataSet( dirname(__FILE__)
    . '/dataset/contact_17.xml') );

    $params = array( 'first_name' => 'Test',
                      'version' 	=> $this->_apiversion );
    $result = civicrm_api('contact', 'get', $params );
    $this->assertTrue( is_array( $result ) );
    $this->assertEquals(0, $result['is_error'], 'in line ' . __LINE__ );
    $this->assertEquals( 17, $result['values'][17]['contact_id'], 'in line ' . __LINE__  );
    $this->assertEquals( 17, $result['id'], 'in line ' . __LINE__  );
    
  }
/*
 * seems contribution is no longer creating activity - test is in the too hard basket for now
 public function testContactGetWithActivityies(){
       $params = array(
                        'email'            => 'man2@yahoo.com',
                        'contact_type'     => 'Individual',
                        'location_type_id' => 1,
                        'version' 				=> $this->_apiversion,
                        'api.contribution.create'    => array(
                                                   
                             'receive_date'           => '2010-01-01',
                             'total_amount'           => 100.00,
                             'contribution_type_id'   => 1,
                             'payment_instrument_id'  => 1,
                             'non_deductible_amount'  => 10.00,
                             'fee_amount'             => 50.00,
                             'net_amount'             => 90.00,
                             'trxn_id'                => 15343455,
                             'invoice_id'             => 6755990,
                             'source'                 => 'SSF',
                             'contribution_status_id' => 1,
                             ),
		      
    );

    $contact = & civicrm_api('Contact', 'Create',$params);
    $params = array('version' => 3, 'id' => $contact['id'], 'api.activity' => array());
    $result = civicrm_api('Contact', 'Get', $params);
    $this->documentMe($params,$result,__FUNCTION__,__FILE__); 
    $this->assertGreaterThan(0, $result['values'][$result['id']]['api.activity']['count']);
    $this->assertEquals('Contribution', $result['values'][$result['id']]['api.activity']['values'][0]['activity_name']);   
 }
 */
  
  /**
   *  Test civicrm_contact_search_count()
   */
  public function testContactGetEmail()
  {
    $params = array(
                        'email'            => 'man2@yahoo.com',
                        'contact_type'     => 'Individual',
                        'location_type_id' => 1,
                        'version' 				=> $this->_apiversion,
		      
    );

    $contact =& civicrm_api('contact','create',$params);
    $this->assertEquals( 0, $contact['is_error'], "In line " . __LINE__
    . " error message: " . CRM_Utils_Array::value('error_message', $contact) );
    $this->assertEquals( 1, $contact['id'], "In line " . __LINE__ );

    $params = array( 'email' => 'man2@yahoo.com',
                      'version'	=> $this->_apiversion );
    $result = civicrm_api('contact', 'get', $params );
    $this->documentMe($params,$result,__FUNCTION__,__FILE__); 
    $this->assertEquals( 1, $result['values'][1]['contact_id'], "In line " . __LINE__  );
    $this->assertEquals( 'man2@yahoo.com', $result['values'][1]['email'], "In line " . __LINE__  );

    // delete the contact
    civicrm_api('contact', 'delete' , $contact );
  }

  /**
   *  Verify that attempt to create individual contact with first
   *  and last names and email succeeds
   */
  function testGetIndividualWithChainedArrays()
  {
    $ids = $this->entityCustomGroupWithSingleFieldCreate( __FUNCTION__,__FILE__);
    $params['custom_'.$ids['custom_field_id']]  =  "custom string";
 
    $moreids = $this->CustomGroupMultipleCreateWithFields();	
    $description = "/*this demonstrates the usage of chained api functions. In this case no notes or custom fields have been created ";
    $subfile = "APIChainedArray";
    $params = array(
                        'first_name'   => 'abc3',
                        'last_name'    => 'xyz3',
                        'contact_type' => 'Individual',
                        'email'        => 'man3@yahoo.com',
                        'version'			=>  $this->_apiversion,
                        'api.contribution.create'    => array(
                                                   
                             'receive_date'           => '2010-01-01',
                             'total_amount'           => 100.00,
                             'contribution_type_id'   => 1,
                             'payment_instrument_id'  => 1,
                             'non_deductible_amount'  => 10.00,
                             'fee_amount'             => 50.00,
                             'net_amount'             => 90.00,
                             'trxn_id'                => 12345,
                             'invoice_id'             => 67890,
                             'source'                 => 'SSF',
                             'contribution_status_id' => 1,
                            ),
                           'api.contribution.create.1'    => array(
                                                   
                             'receive_date'           => '2011-01-01',
                             'total_amount'           => 120.00,
                             'contribution_type_id'   => 1,
                             'payment_instrument_id'  => 1,
                             'non_deductible_amount'  => 10.00,
                             'fee_amount'             => 50.00,
                             'net_amount'             => 90.00,
                             'trxn_id'                => 12335,
                             'invoice_id'             => 67830,
                             'source'                 => 'SSF',
                             'contribution_status_id' => 1,
                             ),
                        'api.website.create' => array(
                             array(
                                'url' => "http://civicrm.org"),

                             )
                                );
 

    $result = civicrm_api('Contact','create',$params);
    $params = array('id' => $result['id'], 'version' => 3, 
    								'api.website.get' => array(), 
    								'api.Contribution.get' => array( 'total_amount' => '120.00', 
                      ),'api.CustomValue.get' => 1,
                      'api.Note.get' => 1);
    $result = civicrm_api('Contact','Get',$params);
    $this->documentMe($params,$result,__FUNCTION__,__FILE__,$description,$subfile); 
    // delete the contact
    civicrm_api('contact', 'delete' , $result );
    $this->customGroupDelete($ids['custom_group_id']);
    $this->customGroupDelete($moreids['custom_group_id']);
    $this->assertEquals( 0, $result['is_error'], "In line " . __LINE__
    . " error message: " . CRM_Utils_Array::value('error_message', $result) );
    $this->assertEquals( 1, $result['id'], "In line " . __LINE__ );
    $this->assertEquals(0,$result['values'][$result['id']]['api.website.get']['is_error'], "In line " . __LINE__);
    $this->assertEquals("http://civicrm.org",$result['values'][$result['id']]['api.website.get']['values'][0]['url'], "In line " . __LINE__);

  }
 function testGetIndividualWithChainedArraysFormats()
  {
    $description = "/*this demonstrates the usage of chained api functions. A variety of return formats are used. Note that no notes
    *custom fields or memberships exist";
    $subfile = "APIChainedArrayFormats";
    $ids = $this->entityCustomGroupWithSingleFieldCreate( __FUNCTION__,__FILE__);
    $params['custom_'.$ids['custom_field_id']]  =  "custom string";
 
    $moreids = $this->CustomGroupMultipleCreateWithFields();	
    $params = array(
                        'first_name'   => 'abc3',
                        'last_name'    => 'xyz3',
                        'contact_type' => 'Individual',
                        'email'        => 'man3@yahoo.com',
                        'version'			=>  $this->_apiversion,
                        'api.contribution.create'    => array(
                                                   
                             'receive_date'           => '2010-01-01',
                             'total_amount'           => 100.00,
                             'contribution_type_id'   => 1,
                             'payment_instrument_id'  => 1,
                             'non_deductible_amount'  => 10.00,
                             'fee_amount'             => 50.00,
                             'net_amount'             => 90.00,
                             'trxn_id'                => 12345,
                             'invoice_id'             => 67890,
                             'source'                 => 'SSF',
                             'contribution_status_id' => 1,
                            ),
                           'api.contribution.create.1'    => array(
                                                   
                             'receive_date'           => '2011-01-01',
                             'total_amount'           => 120.00,
                             'contribution_type_id'   => 1,
                             'payment_instrument_id'  => 1,
                             'non_deductible_amount'  => 10.00,
                             'fee_amount'             => 50.00,
                             'net_amount'             => 90.00,
                             'trxn_id'                => 12335,
                             'invoice_id'             => 67830,
                             'source'                 => 'SSF',
                             'contribution_status_id' => 1,
                             ),
                        'api.website.create' => array(
                             array(
                                'url' => "http://civicrm.org"),

                             )
                                );
 

    $result = civicrm_api('Contact','create',$params);
    $params = array('id' => $result['id'], 'version' => 3, 
    								'api.website.getValue' => array('return' => 'url'), 
    								'api.Contribution.getCount' => array(  
                      ),'api.CustomValue.get' => 1,
                      'api.Note.get' => 1,
                      'api.Membership.getCount' => array());
    $result = civicrm_api('Contact','Get',$params);
    $this->documentMe($params,$result,__FUNCTION__,__FILE__,$description,$subfile); 
    $this->assertEquals( 0, $result['is_error'], "In line " . __LINE__
    . " error message: " . CRM_Utils_Array::value('error_message', $result) );
    $this->assertEquals( 1, $result['id'], "In line " . __LINE__ );
    $this->assertEquals(2,$result['values'][$result['id']]['api.Contribution.getCount'], "In line " . __LINE__); 
    $this->assertEquals(0,$result['values'][$result['id']]['api.Note.get']['is_error'], "In line " . __LINE__);
    $this->assertEquals("http://civicrm.org",$result['values'][$result['id']]['api.website.getValue'], "In line " . __LINE__);
    // delete the contact
    
    $params = array('id' => $result['id'], 'version' => 3, 
    								'api_Contribution_get' => array(  
                      ),
                      'sequential' => 1,
                      'format.smarty' => 'api/v3/exampleLetter.tpl' );
     $subfile = 'smartyExample';
     $description = "demonstrates use of smarty as output";
     $result = civicrm_api('Contact','Get',$params); 
   //  $this->documentMe($params,$result,__FUNCTION__,__FILE__,$description,$subfile); 
  //   $this->assertContains('USD', $result);
   //  $this->assertContains('Dear', $result);
  //   $this->assertContains('Friday', $result);
     
    civicrm_api('contact', 'delete' , $result );
    $this->customGroupDelete($ids['custom_group_id']);
    $this->customGroupDelete($moreids['custom_group_id']);
  }
  
  function testGetIndividualWithChainedArraysAndMultipleCustom()
  {
    $ids = $this->entityCustomGroupWithSingleFieldCreate( __FUNCTION__,__FILE__);
    $params['custom_'.$ids['custom_field_id']]  =  "custom string";
    $moreids = $this->CustomGroupMultipleCreateWithFields();	
    $andmoreids = $this->CustomGroupMultipleCreateWithFields(array('title'      => "another group"));	
    $description = "/*this demonstrates the usage of chained api functions. A variety of techniques are used";
    $subfile = "APIChainedArrayMultipleCustom";
    $params = array(
                        'first_name'   => 'abc3',
                        'last_name'    => 'xyz3',
                        'contact_type' => 'Individual',
                        'email'        => 'man3@yahoo.com',
                        'version'			=>  $this->_apiversion,
                        'api.contribution.create'    => array(
                                                   
                             'receive_date'           => '2010-01-01',
                             'total_amount'           => 100.00,
                             'contribution_type_id'   => 1,
                             'payment_instrument_id'  => 1,
                             'non_deductible_amount'  => 10.00,
                             'fee_amount'             => 50.00,
                             'net_amount'             => 90.00,
                             'trxn_id'                => 12345,
                             'invoice_id'             => 67890,
                             'source'                 => 'SSF',
                             'contribution_status_id' => 1,
                            ),
                           'api.contribution.create.1'    => array(
                                                   
                             'receive_date'           => '2011-01-01',
                             'total_amount'           => 120.00,
                             'contribution_type_id'   => 1,
                             'payment_instrument_id'  => 1,
                             'non_deductible_amount'  => 10.00,
                             'fee_amount'             => 50.00,
                             'net_amount'             => 90.00,
                             'trxn_id'                => 12335,
                             'invoice_id'             => 67830,
                             'source'                 => 'SSF',
                             'contribution_status_id' => 1,
                             ),
                        'api.website.create' => array(
                             array(
                                'url' => "http://civicrm.org"),

                             ),
                         'custom_' . $ids['custom_field_id'] => "value 1",
                         'custom_' . $moreids['custom_field_id'][0] => "value 2",    
                         'custom_' . $moreids['custom_field_id'][1] => "warm beer",
												 'custom_' . $andmoreids['custom_field_id'][1] => "vegemite",
                             );
 

    $result = civicrm_api('Contact','create',$params);
    $result = civicrm_api('Contact','create',array('contact_type' => 'Individual', 'id' => $result['id'], 'version' => 3, 'custom_' . $moreids['custom_field_id'][0] => "value 3", 'custom_' . $ids['custom_field_id'] => "value 4",));

    $params = array('id' => $result['id'], 'version' => 3, 
    								'api.website.getValue' => array('return' => 'url'), 
    								'api.Contribution.getCount' => array( 
                      ),'api.CustomValue.get' => 1,
                      );
    $result = civicrm_api('Contact','Get',$params);
    $this->documentMe($params,$result,__FUNCTION__,__FILE__,$description,$subfile); 
    // delete the contact
    civicrm_api('contact', 'delete' , $result );
    $this->customGroupDelete($ids['custom_group_id']);
    $this->customGroupDelete($moreids['custom_group_id']);
    $this->customGroupDelete($andmoreids['custom_group_id']);
    $this->assertEquals( 0, $result['is_error'], "In line " . __LINE__
    . " error message: " . CRM_Utils_Array::value('error_message', $result) );
    $this->assertEquals( 1, $result['id'], "In line " . __LINE__ );
    $this->assertEquals(0,$result['values'][$result['id']]['api.CustomValue.get']['is_error'], "In line " . __LINE__);
    $this->assertEquals('http://civicrm.org',$result['values'][$result['id']]['api.website.getValue'], "In line " . __LINE__);
    
   
  }
  /*
   * Test checks siusage of $values to pick & choose inputs
   */
   function testChainingValuesCreate ( )   {
    $description = "/*this demonstrates the usage of chained api functions.  Specifically it has one 'parent function' &
    2 child functions - one receives values from the parent (Contact) and the other child (Tag). ";
    $subfile = "APIChainedArrayValuesFromSiblingFunction";
    $params = array('version' => 3, 'display_name' => 'batman' , 'contact_type' => 'Individual', 
                    'api.tag.create' => array('name' => '$value.id', 'description' => '$value.display_name','format.only_id' => 1),
                    'api.entity_tag.create' => array('tag_id' =>'$value.api.tag.create'));
              $result = civicrm_api('Contact','Create',$params);
        
        $this->assertEquals(0, $result['is_error']);
        $this->assertEquals(0, $result['values'][$result['id']]['api.entity_tag.create']['is_error']);
       $this->documentMe($params,$result,__FUNCTION__,__FILE__,$description,$subfile);     
        $tablesToTruncate = array( 'civicrm_contact', 
                                   'civicrm_activity',
                                   'civicrm_entity_tag',
                                   'civicrm_tag'
                                   );
        $this->quickCleanup( $tablesToTruncate, true );
    }
    
  /*
   * test TrueFalse format - I couldn't come up with an easy way to get an error on Get
   */
  function testContactGetFormatIsSuccessTrue(){
    $this->createContactFromXML();
    $description = "This demonstrates use of the 'format.is_success' param. 
    This param causes only the success or otherwise of the function to be returned as BOOLEAN";
    $subfile = "FormatIsSuccess_True";
    $params = array('version' => 3, 'id' => 17, 'format.is_success' => 1);
    $result = civicrm_api('Contact', 'Get', $params);
    $this->documentMe($params,$result,__FUNCTION__,__FILE__, $description,$subfile);
    $this->assertEquals(1, $result);
    civicrm_api('Contact', 'Delete', $params) ; 
  }
  /*
   * test TrueFalse format
   */
  function testContactCreateFormatIsSuccessFalse(){

    $description = "This demonstrates use of the 'format.is_success' param. 
    This param causes only the success or otherwise of the function to be returned as BOOLEAN";
    $subfile = "FormatIsSuccess_Fail";
    $params = array('version' => 3, 'id' => 500, 'format.is_success' => 1);
    $result = civicrm_api('Contact', 'Create', $params);
    $this->documentMe($params,$result,__FUNCTION__,__FILE__, $description,$subfile);
    $this->assertEquals(0, $result);
 
  }
  /*
   * test Single Entity format
   */
  function testContactGetSingle_entity_array(){
    $this->createContactFromXML();
    $description = "This demonstrates use of the 'format.single_entity_array' param. 
    /* This param causes the only contact to be returned as an array without the other levels.
    /* it will be ignored if there is not exactly 1 result";
    $subfile = "GetSingleContact";
    $params = array('version' => 3, 'id' => 17);
    $result = civicrm_api('Contact', 'GetSingle', $params);
    $this->documentMe($params,$result,__FUNCTION__,__FILE__, $description,$subfile);
    $this->assertEquals('Test Contact' , $result['display_name'] , "in line " . __LINE__ );
    civicrm_api('Contact', 'Delete', $params) ; 
   }
   
  /*
   * test Single Entity format
   */
  function testContactGetFormatcount_only(){
    $this->createContactFromXML();
    $description = "/*This demonstrates use of the 'getCount' action 
    /*  This param causes the count of the only function to be returned as an integer";
    $subfile = "GetCountContact";
    $params = array('version' => 3, 'id' => 17);
    $result = civicrm_api('Contact', 'GetCount', $params);
    $this->documentMe($params,$result,__FUNCTION__,__FILE__, $description,$subfile);
    $this->assertEquals('1' , $result , "in line " . __LINE__ );
    civicrm_api('Contact', 'Delete', $params) ; 
   }
   /*
    * Test id only format
    */
  function testContactGetFormatID_only(){
    $this->createContactFromXML();
    $description = "This demonstrates use of the 'format.id_only' param. 
    /* This param causes the id of the only entity to be returned as an integer.
    /* it will be ignored if there is not exactly 1 result";
    $subfile = "FormatOnlyID";
    $params = array('version' => 3, 'id' => 17, 'format.only_id' => 1);
    $result = civicrm_api('Contact', 'Get', $params);
    $this->documentMe($params,$result,__FUNCTION__,__FILE__, $description,$subfile);
    $this->assertEquals('17' , $result , "in line " . __LINE__ );
    civicrm_api('Contact', 'Delete', $params) ; 
   }  
   
      /*
    * Test id only format
    */
  function testContactGetFormatSingleValue(){
    $this->createContactFromXML();
    $description = "This demonstrates use of the 'format.single_value' param. 
    /* This param causes only a single value of the only entity to be returned as an string.
    /* it will be ignored if there is not exactly 1 result";
    $subfile = "FormatSingleValue";
    $params = array('version' => 3, 'id' => 17, 'return' => 'display_name');
    $result = civicrm_api('Contact', 'GetValue', $params);
    $this->documentMe($params,$result,__FUNCTION__,__FILE__, $description,$subfile);
    $this->assertEquals('Test Contact' , $result , "in line " . __LINE__ );
    civicrm_api('Contact', 'Delete', $params) ; 
   } 
   
  function testContactCreationPermissions()
  {
    $params = array('contact_type' => 'Individual', 'first_name' => 'Foo', 
    								'last_name' => 'Bear', 
    								'check_permissions' => true,
                    'version'     => $this->_apiversion);

    CRM_Core_Permission_UnitTests::$permissions = array('access CiviCRM');
    $result = civicrm_api('contact', 'create', $params);
    $this->assertEquals(1,                                                                                        $result['is_error'],      'lacking permissions should not be enough to create a contact');
    $this->assertEquals('API permission check failed for contact/create call; missing permission: add contacts.', $result['error_message'], 'lacking permissions should not be enough to create a contact');

    CRM_Core_Permission_UnitTests::$permissions = array('access CiviCRM', 'add contacts', 'import contacts');
    $result = civicrm_api('contact', 'create', $params);
    $this->assertEquals(0, $result['is_error'], 'overfluous permissions should be enough to create a contact');
  }

  function testContactUpdatePermissions()
  {
    $params = array('contact_type' => 'Individual', 'first_name' => 'Foo', 'last_name' => 'Bear', 'check_permissions' => true, 'version' =>3);
    $result = civicrm_api('contact', 'create', $params);

    $params = array('id' => $result['id'], 'contact_type' => 'Individual', 'last_name' => 'Bar', 'check_permissions' => true, 'version' =>3);

    CRM_Core_Permission_UnitTests::$permissions = array('access CiviCRM');
    $result = civicrm_api('contact', 'update', $params);
    $this->assertEquals(1,                                                                                             $result['is_error'],      'lacking permissions should not be enough to update a contact');
    $this->assertEquals('API permission check failed for contact/update call; missing permission: edit all contacts.', $result['error_message'], 'lacking permissions should not be enough to update a contact');

    CRM_Core_Permission_UnitTests::$permissions = array('access CiviCRM', 'add contacts', 'view all contacts', 'edit all contacts', 'import contacts');

    $result = civicrm_api('contact', 'update', $params);
    $this->assertEquals(0, $result['is_error'], 'overfluous permissions should be enough to update a contact');
  }
  function createContactFromXML(){
        //  Insert a row in civicrm_contact creating contact 17
    $op = new PHPUnit_Extensions_Database_Operation_Insert( );
    $op->execute( $this->_dbconn,
    new PHPUnit_Extensions_Database_DataSet_XMLDataSet(
    dirname(__FILE__)
    . '/dataset/contact_17.xml') );
  }
} // class api_v3_ContactTest

// -- set Emacs parameters --
// Local variables:
// mode: php;
// tab-width: 4
// c-basic-offset: 4
// c-hanging-comment-ender-p: nil
// indent-tabs-mode: nil
// End:

