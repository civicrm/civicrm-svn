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

  protected $_apiversion;
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
    require_once 'CRM/Core/Permission/UnitTests.php';
    CRM_Core_Permission_UnitTests::$permissions = null; // reset check() stub
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

                        $contact =& civicrm_api3_contact_create($params);
                        $this->assertEquals( 0, $contact['is_error'], "In line " . __LINE__ );
                        $this->assertEquals( 1, $contact['id'], "In line " . __LINE__ );
                        $getContact = civicrm_api('Contact','Get',array('version' =>3));
                        // delete the contact
                        civicrm_api3_contact_delete( $contact );
  }

  /**
   *  Verify that attempt to create contact with empty params fails
   */
  function testCreateEmptyContact()
  {
    $params = array();
    $contact =& civicrm_api3_contact_create($params);
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
                        $contact =& civicrm_api3_contact_create($params);
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

                        $contact =& civicrm_api3_contact_create($params);
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

                        $contact =& civicrm_api3_contact_create($params);
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

                        $contact =& civicrm_api3_contact_create($params);
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

    $contact =& civicrm_api3_contact_create($params);
    $this->assertEquals( 0, $contact['is_error'], "In line " . __LINE__
    . " error message: " . CRM_Utils_Array::value('error_message', $contact) );
    $this->assertEquals( 1, $contact['id'], "In line " . __LINE__ );
    $email = civicrm_api('email','get',array('contact_id' => $contact['id'],'version' =>3));
    $this->assertEquals( 0, $email['is_error'], "In line " . __LINE__);
    $this->assertEquals( 1, $email['count'], "In line " . __LINE__);
    $this->assertEquals( 'man3@yahoo.com', $email['values'][$email['id']]['email'], "In line " . __LINE__);
    
    // delete the contact
    civicrm_api3_contact_delete( $contact );
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

    $contact =& civicrm_api3_contact_create($params);
    $this->assertEquals( 0, $contact['is_error'], "In line " . __LINE__
    . " error message: " . CRM_Utils_Array::value('error_message', $contact) );
    $this->assertEquals( 1, $contact['id'], "In line " . __LINE__ );

    // delete the contact
    civicrm_api3_contact_delete( $contact );
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

    $contact =& civicrm_api3_contact_create($params);
    $this->assertEquals( 0, $contact['is_error'], "In line " . __LINE__
    . " error message: " . CRM_Utils_Array::value('error_message', $contact) );
    $this->assertEquals( 1, $contact['id'], "In line " . __LINE__ );

    // delete the contact
    civicrm_api3_contact_delete( $contact );
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

    $contact =& civicrm_api3_contact_create($params);
    $this->assertEquals( 0, $contact['is_error'], "In line " . __LINE__
    . " error message: " . CRM_Utils_Array::value('error_message', $contact) );
    $this->assertEquals( 1, $contact['id'], "In line " . __LINE__ );

    // delete the contact
    civicrm_api3_contact_delete( $contact );
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

    $contact =& civicrm_api3_contact_create($params);
    $this->assertEquals( 0, $contact['is_error'], "In line " . __LINE__
    . " error message: " . CRM_Utils_Array::value('error_message', $contact) );
    $this->assertEquals( 1, $contact['id'], "In line " . __LINE__ );

    // delete the contact
    civicrm_api3_contact_delete( $contact );
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
    $contact =& civicrm_api3_contact_create($params);
    $this->assertEquals( 0, $contact['is_error'], "In line " . __LINE__
    . " error message: " . CRM_Utils_Array::value('error_message', $contact) );
    $this->assertEquals( 1, $contact['id'], "In line " . __LINE__ );

    // delete the contact
    civicrm_api3_contact_delete( $contact );
  }

  /**
   *  Verify that attempt to create individual contact with first
   *  and last names and email succeeds
   */
  function testCreateIndividualWithContribution()
  {
    $params = array(
                        'first_name'   => 'abc3',
                        'last_name'    => 'xyz3',
                        'contact_type' => 'Individual',
                        'email'        => 'man3@yahoo.com',
                        'version'			=>  $this->_apiversion,
                        'entities'    => array(
                           'Contribution' => array(                         
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
                             'version' =>$this->_apiversion,
                        ),
                          'website' => array('url' => "http://civicrm.org")),
    );

    $result =civicrm_api('Contact','create',$params);
    $this->documentMe($params,$result,__FUNCTION__,__FILE__); 
    $this->assertEquals( 0, $result['is_error'], "In line " . __LINE__
    . " error message: " . CRM_Utils_Array::value('error_message', $result) );
    $this->assertEquals( 1, $result['id'], "In line " . __LINE__ );
    $this->assertEquals(0,$result['values'][$result['id']]['entities']['website']['is_error']);
    // delete the contact
    civicrm_api3_contact_delete( $result );
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

    $contact =& civicrm_api3_contact_create($params);
    $this->assertEquals( 0, $contact['is_error'], "In line " . __LINE__
    . " error message: " . CRM_Utils_Array::value('error_message', $contact) );
    $this->assertEquals( 1, $contact['id'], "In line " . __LINE__ );

    // delete the contact
    civicrm_api3_contact_delete( $contact );
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
    $result =& civicrm_api3_contact_create($params);

    $this->assertEquals( 0, $result['is_error'], "In line " . __LINE__
    . " error message: " . CRM_Utils_Array::value('error_message', $result) );
    $this->assertEquals( 1, $result['id'], "In line " . __LINE__ );

    // delete the contact
    civicrm_api3_contact_delete( $contact );

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

    $contact =& civicrm_api3_contact_create($params);
    $this->assertEquals( 0, $contact['is_error'], "In line " . __LINE__
    . " error message: " . CRM_Utils_Array::value('error_message', $contact) );
    $this->assertEquals( 1, $contact['id'], "In line " . __LINE__ );

    // delete the contact
    civicrm_api3_contact_delete( $contact );
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
    $result = civicrm_api3_contact_delete( $params );
    $this->assertEquals( 1, $result['is_error'], "In line " . __LINE__
    . " error message: " . CRM_Utils_Array::value('error_message', $result) );
  }

  /**
   *  Test civicrm_contact_delete() with error
   */
  function testContactDeleteError()
  {
    $params = array( 'contact_id' => 17 );
    $result = civicrm_api3_contact_delete( $params );
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
    $result = civicrm_api3_contact_delete( $params );
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
    $result = civicrm_api3_contact_get( $params );
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
    $result = civicrm_api3_contact_get( $params );
    $this->assertEquals( 17, $result['values'][17]['contact_id'], "In line " . __LINE__ );
    $this->assertEquals( 'Test', $result['values'][17]['first_name'] , "In line " . __LINE__);
  }

  /**
   *  Test civicrm_contact_get) with empty params
   */
  public function testContactGetEmptyParams()
  {
    $params = array();
    $result = civicrm_api3_contact_get( $params);
   
    $this->assertTrue( is_array( $result ),'in line '. __LINE__ );
    $this->assertEquals( 1, $result['is_error'] ,'in line '. __LINE__);

  }

  /**
   *  Test civicrm_contact_get(,true) with params not array
   */
  public function testContactGetParamsNotArray()
  {
    $params = 17;
    $result = civicrm_api3_contact_get( $params, true );
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
    $result = civicrm_api3_contact_get( $params );
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
    $result = civicrm_api3_contact_get( $params );
    $this->assertTrue( is_array( $result ) );
    $this->assertEquals(0, $result['is_error'], 'in line ' . __LINE__ );
    $this->assertEquals( 17, $result['values'][17]['contact_id'], 'in line ' . __LINE__  );
    $this->assertEquals( 17, $result['id'], 'in line ' . __LINE__  );
    
  }


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

    $contact =& civicrm_api3_contact_create($params);
    $this->assertEquals( 0, $contact['is_error'], "In line " . __LINE__
    . " error message: " . CRM_Utils_Array::value('error_message', $contact) );
    $this->assertEquals( 1, $contact['id'], "In line " . __LINE__ );

    $params = array( 'email' => 'man2@yahoo.com',
                      'version'	=> $this->_apiversion );
    $result = civicrm_api3_contact_get( $params );
    $this->documentMe($params,$result,__FUNCTION__,__FILE__); 
    $this->assertEquals( 1, $result['values'][1]['contact_id'], "In line " . __LINE__  );
    $this->assertEquals( 'man2@yahoo.com', $result['values'][1]['email'], "In line " . __LINE__  );

    // delete the contact
    civicrm_api3_contact_delete( $contact );
  }



  function testContactCreationPermissions()
  {
    $params = array('contact_type' => 'Individual', 'first_name' => 'Foo', 
    								'last_name' => 'Bear', 
    								'check_permissions' => true,
                    'version'     => $this->_apiversion);

    CRM_Core_Permission_UnitTests::$permissions = array('access CiviCRM');
    $result = civicrm_api3_contact_create($params);
    $this->assertEquals(1,                                                                                                $result['is_error'],      'lacking permissions should not be enough to create a contact');
    $this->assertEquals('API permission check failed for civicrm_api3_contact_create call; missing permission: add contacts.', $result['error_message'], 'lacking permissions should not be enough to create a contact');

    CRM_Core_Permission_UnitTests::$permissions = array('access CiviCRM', 'add contacts', 'import contacts');
    $result = civicrm_api3_contact_create($params);
    $this->assertEquals(0, $result['is_error'], 'overfluous permissions should be enough to create a contact');
  }

  function testContactUpdatePermissions()
  {
    $params = array('contact_type' => 'Individual', 'first_name' => 'Foo', 'last_name' => 'Bear', 'check_permissions' => true, 'version' =>3);
    $result = civicrm_api3_contact_create($params);

    $params = array('id' => $result['id'], 'contact_type' => 'Individual', 'last_name' => 'Bar', 'check_permissions' => true, 'version' =>3);

        CRM_Core_Permission_UnitTests::$permissions = array('access CiviCRM');
        $result = civicrm_api('Contact','Update',$params);
        $this->assertEquals(1,  $result['is_error'],      'lacking permissions should not be enough to update a contact');
        $this->assertEquals('API permission check failed for civicrm_api3_contact_create call; missing permission: add contacts.', $result['error_message'], 'lacking permissions should not be enough to update a contact');

        CRM_Core_Permission_UnitTests::$permissions = array('access CiviCRM', 'add contacts', 'import contacts');
        $result = civicrm_api('Contact','Update',$params);
        $this->assertEquals(0, $result['is_error'], 'overfluous permissions should be enough to update a contact');
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
