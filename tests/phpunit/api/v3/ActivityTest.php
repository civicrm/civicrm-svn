<?php  // vim: set si ai expandtab tabstop=4 shiftwidth=4 softtabstop=4:

/**
 *  File for the TestActivity class
 *
 *  (PHP 5)
 *  
 *   @author Walt Haas <walt@dharmatech.org> (801) 534-1262
 *   @copyright Copyright CiviCRM LLC (C) 2009
 *   @license   http://www.fsf.org/licensing/licenses/agpl-3.0.html
 *              GNU Affero General Public License version 3
 *   @version   $Id: ActivityTest.php 31254 2010-12-15 10:09:29Z eileen $
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
require_once 'api/v3/Activity.php';
require_once 'CRM/Core/BAO/CustomGroup.php';
require_once 'Utils.php';

/**
 *  Test APIv3 civicrm_activity_* functions
 *
 *  @package   CiviCRM
 */
class api_v3_ActivityTest extends CiviUnitTestCase
{   protected $_params;
    protected $_entity;
    protected $_apiversion;
    /**
     *  Test setup for every test
     *
     *  Connect to the database, truncate the tables that will be used
     *  and redirect stdin to a temporary file
     */
    public function setUp()
    {
        $this->_apiversion =3;
        $this->_entity = 'activity';
        //  Connect to the database
        parent::setUp();
        $tablesToTruncate = array( 'civicrm_activity',
                                   'civicrm_contact',
                                  'civicrm_custom_group',
                                   'civicrm_custom_field',         );

        $this->quickCleanup( $tablesToTruncate );

        //  Truncate the tables
        $op = new PHPUnit_Extensions_Database_Operation_Truncate( );
        $op->execute( $this->_dbconn,
                      new PHPUnit_Extensions_Database_DataSet_FlatXMLDataSet(
                                                                             dirname(__FILE__) . '/../../CiviTest/truncate-option.xml') );
 
        //  Insert a row in civicrm_contact creating contact 17
        $op = new PHPUnit_Extensions_Database_Operation_Insert( );
        $op->execute( $this->_dbconn,
                      new PHPUnit_Extensions_Database_DataSet_XMLDataSet(
                                                                         dirname(__FILE__)
                                                                         . '/dataset/contact_17.xml') );
 
        //  Insert a row in civicrm_option_group creating option group
        //  activity_type 
        $op = new PHPUnit_Extensions_Database_Operation_Insert( );
        $op->execute( $this->_dbconn,
                      new PHPUnit_Extensions_Database_DataSet_FlatXMLDataSet(
                                                                             dirname(__FILE__)
                                                                             . '/dataset/option_group_activity.xml') );
 
        //  Insert a row in civicrm_option_value creating
        //  activity_type 5
        $op = new PHPUnit_Extensions_Database_Operation_Insert( );
        $op->execute( $this->_dbconn,
                      new PHPUnit_Extensions_Database_DataSet_XMLDataSet(
                                                                         dirname(__FILE__)
                                                                         . '/dataset/option_value_activity.xml') );
        $this->_params = array( 
                'source_contact_id' => 17,
                'activity_type_id' => 1,
                'subject' => 'test activity type id',
                'activity_date_time' => '2011-06-02 14:36:13',
                'status_id' => 2,
                'priority_id' => 1, 
                'version' => $this->_apiversion);
    }


    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     *
     * @access protected
     */
    function tearDown()
    {
        $tablesToTruncate = array( 'civicrm_contact', 
                                   'civicrm_activity',
                                   'civicrm_option_group',
                                   'civicrm_option_value'
                                   );
        $this->quickCleanup( $tablesToTruncate, true );
    }

    
    /**
     * check with empty array
     */
    function testActivityCreateEmpty( )
    {
        $params = array('version' => $this->_apiversion );
        $result = & civicrm_api3_activity_create($params);
        $this->assertEquals( $result['is_error'], 1,
                             "In line " . __LINE__ );
    }

    /**
     * check if required fields are not passed
     */
    function testActivityCreateWithoutRequired( )
    {
        $params = array(
                        'subject'             => 'this case should fail',
                        'scheduled_date_time' => date('Ymd'),
                        'version' => $this->_apiversion
                        );
        
        $result = & civicrm_api3_activity_create($params);
        $this->assertEquals( $result['is_error'], 1,
                             "In line " . __LINE__ );
    }

    /**
     *  Test civicrm_activity_create() with missing subject
     */
    function testActivityCreateMissingSubject( )
    {
        $params = array(
                        'source_contact_id'   => 17,
                        'activity_date_time'  => date('Ymd'),
                        'duration'            => 120,
                        'location'            => 'Pensulvania',
                        'details'             => 'a test activity',
                        'status_id'           => 1,
                        'activity_name'       => 'Test activity type',
                        'scheduled_date_time' => date('Ymd'),
                        'version' => $this->_apiversion
                        );
        
        $result = civicrm_api3_activity_create($params);
        $this->assertEquals( $result['is_error'], 1,
                             "In line " . __LINE__ );
    }

    /**
     *  Test civicrm_activity_create() with mismatched activity_type_id
     *  and activity_name
     */
    function testActivityCreateMismatchNameType( )
    {
        $params = array(
                        'source_contact_id'   => 17,
                        'subject'             => 'Test activity',
                        'activity_date_time'  => date('Ymd'),
                        'duration'            => 120,
                        'location'            => 'Pensulvania',
                        'details'             => 'a test activity',
                        'status_id'           => 1,
                        'activity_name'       => 'Fubar activity type',
                        'activity_type_id'    => 5,
                        'scheduled_date_time' => date('Ymd'),
                        'version' => $this->_apiversion
                        );

        $result = civicrm_api3_activity_create($params);
        $this->assertEquals( $result['is_error'], 1,
                             "In line " . __LINE__ );
    }

    /**
     *  Test civicrm_activity_id() with missing source_contact_id is put with the current user.
     *  note that there is no valid user in the test suite so this produces a fail - bit of a dud test really!
     *  
     *  !
     */
    function testActivityCreateWithMissingContactId( )
    {
        $params = array(
                        'subject'             => 'Make-it-Happen Meeting',
                        'activity_date_time'  => date('Ymd'),
                        'duration'            => 120,
                        'location'            => 'Pensulvania',
                        'details'             => 'a test activity',
                        'status_id'           => 1,
                        'activity_name'       => 'Test activity type',
                        'version'             => $this->_apiversion
                        );

        $result = & civicrm_api3_activity_create($params);
        
        $this->assertEquals( $result['is_error'], 1,
                             "In line " . __LINE__ );
    }

    /**
     *  Test civicrm_activity_id() with non-numeric source_contact_id
     */
    function testActivityCreateWithNonNumericContactId( )
    {
        $params = array(
                        'source_contact_id'   => 'fubar',
                        'subject'             => 'Make-it-Happen Meeting',
                        'activity_date_time'  => date('Ymd'),
                        'duration'            => 120,
                        'location'            => 'Pensulvania',
                        'details'             => 'a test activity',
                        'status_id'           => 1,
                        'activity_name'       => 'Test activity type',
                        'version' => $this->_apiversion
                        );

        $result = & civicrm_api3_activity_create($params);
        
        $this->assertEquals( $result['is_error'], 1,
                             "In line " . __LINE__ );
    }

    /**
     *  Test civicrm_activity_id() with non-numeric duration
     *  @todo Come back to this in later stages
     */
    /// we don't offer single parameter correctness checking at the moment
    //function testActivityCreateWithNonNumericDuration( )
    //{
    //    $params = array(
    //                    'source_contact_id'   => 17,
    //                    'subject'             => 'Discussion on Apis for v3',
    //                    'activity_date_time'  => date('Ymd'),
    //                    'duration'            => 'fubar',
    //                    'location'            => 'Pensulvania',
    //                    'details'             => 'a test activity',
    //                    'status_id'           => 1,
    //                    'activity_name'       => 'Test activity type'
    //                    );
    //
    //    $result = civicrm_activity_create($params);
    //    
    //    $this->assertEquals( $result['is_error'], 1,
    //                         "In line " . __LINE__ );
    //}

    /**
     * check with incorrect required fields
     */
    function testActivityCreateWithNonNumericActivityTypeId( )
    {
        $params = array(
                        'source_contact_id'   => 17,
                        'subject'             => 'Make-it-Happen Meeting',
                        'activity_date_time'  => date('Ymd'),
                        'duration'            => 120,
                        'location'            => 'Pensulvania',
                        'details'             => 'a test activity',
                        'status_id'           => 1,
                        'activity_type_id'    => 'Test activity type',
                        'version' => $this->_apiversion
                        );

        $result = civicrm_api3_activity_create($params);

        $this->assertEquals( $result['is_error'], 1,
                             "In line " . __LINE__ );
    }

    /**
     * check with incorrect required fields
     */
    function testActivityCreateWithUnknownActivityTypeId( )
    {
        $params = array(
                        'source_contact_id'   => 17,
                        'subject'             => 'Make-it-Happen Meeting',
                        'activity_date_time'  => date('Ymd'),
                        'duration'            => 120,
                        'location'            => 'Pensulvania',
                        'details'             => 'a test activity',
                        'status_id'           => 1,
                        'activity_type_id'    => 6,
                        'version' => $this->_apiversion
                        );

        $result = & civicrm_api3_activity_create($params);

        $this->assertEquals( $result['is_error'], 1,
                             "In line " . __LINE__ );
    }



    /**
     *  Test civicrm_activity_create() with valid parameters
     */
    function testActivityCreate( )
    {
        $params = array(
                        'source_contact_id'   => 17,
                        'subject'             => 'Make-it-Happen Meeting',
                        'activity_date_time'  => '20110316',
                        'duration'            => 120,
                        'location'            => 'Pensulvania',
                        'details'             => 'a test activity',
                        'status_id'           => 1,
                        'activity_name'       => 'Test activity type',
                        'version'             => $this->_apiversion,
                        'priority_id'         => 1,
                        );
        
        $result = & civicrm_api3_activity_create( $params );
        $this->documentMe($params,$result,__FUNCTION__,__FILE__); 
        $this->assertEquals( $result['is_error'], 0,
                             "Error message: " . CRM_Utils_Array::value( 'error_message', $result ) .' in line ' . __LINE__ );
        $this->assertEquals( $result['values'][$result['id']]['source_contact_id'], 17,'in line ' . __LINE__);
        $this->assertEquals( $result['values'][$result['id']]['duration'], 120 ,'in line ' . __LINE__);
        $this->assertEquals( $result['values'][$result['id']]['subject'], 'Make-it-Happen Meeting','in line ' . __LINE__ );
        $this->assertEquals( $result['values'][$result['id']]['activity_date_time'], '20110316' . '000000'   ,'in line ' . __LINE__);
        $this->assertEquals( $result['values'][$result['id']]['location'], 'Pensulvania','in line ' . __LINE__ );
        $this->assertEquals( $result['values'][$result['id']]['details'], 'a test activity' ,'in line ' . __LINE__);
        $this->assertEquals( $result['values'][$result['id']]['status_id'], 1,'in line ' . __LINE__ );
    }
    /**
     *  Test civicrm_activity_create() with valid parameters - use type_id
     */
    function testActivityCreateCampaignTypeID( )
    {
        // force reload of config object
        $config = CRM_Core_Config::singleton( true, true );

        require_once 'CRM/Core/BAO/Setting.php';
        CRM_Core_BAO_Setting::enableComponent( 'CiviCampaign' );
      
        $defaults = array();

        $params = array(
                        'source_contact_id'   => 17,
                        'subject'             => 'Make-it-Happen Meeting',
                        'activity_date_time'  => '20110316',
                        'duration'            => 120,
                        'location'            => 'Pensulvania',
                        'details'             => 'a test activity',
                        'status_id'           => 1,
                        'activity_type_id'    => 29,
                        'version'             => $this->_apiversion,
                        'priority_id'         => 1,

                        );
      
        $result = & civicrm_api3_activity_create( $params );
        //todo test target & assignee are set
        $this->assertEquals( $result['is_error'], 0,
                             "Error message: " . CRM_Utils_Array::value( 'error_message', $result ) .' in line ' . __LINE__ );
 
        $this->assertEquals( $result['values'][$result['id']]['source_contact_id'], 17,'in line ' . __LINE__);
 
        $this->documentMe($params,$result,__FUNCTION__,__FILE__); 
        $result = & civicrm_api3_activity_get(array('id' => $result['id'], 'version' => $this->_apiversion) );
        $this->assertEquals( $result['values'][$result['id']]['source_contact_id'], 17,'in line ' . __LINE__);
 
        $this->assertEquals( $result['values'][$result['id']]['duration'], 120 ,'in line ' . __LINE__);
        $this->assertEquals( $result['values'][$result['id']]['subject'], 'Make-it-Happen Meeting','in line ' . __LINE__ );
        $this->assertEquals( $result['values'][$result['id']]['activity_date_time'], '2011-03-16 00:00:00'  ,'in line ' . __LINE__);
        $this->assertEquals( $result['values'][$result['id']]['location'], 'Pensulvania','in line ' . __LINE__ );
        $this->assertEquals( $result['values'][$result['id']]['details'], 'a test activity' ,'in line ' . __LINE__);
        $this->assertEquals( $result['values'][$result['id']]['status_id'], 1,'in line ' . __LINE__ );

    }
    function testActivityReturnTargetAssignee( )
    {

        $description = "Example demonstrates setting & retrieving the target & source";
        $subfile = "GetTargetandAssignee";
        $params = array(
                        'source_contact_id'   => 17,
                        'subject'             => 'Make-it-Happen Meeting',
                        'activity_date_time'  => '20110316',
                        'duration'            => 120,
                        'location'            => 'Pensulvania',
                        'details'             => 'a test activity',
                        'status_id'           => 1,
                        'activity_type_id'    => 1,
                        'version'             => $this->_apiversion,
                        'priority_id'         => 1,
                        'target_contact_id'   => 17,
                        'assignee_contact_id'  => 17
                        );
      
        $result = & civicrm_api3_activity_create( $params );
        //todo test target & assignee are set
        $this->assertEquals( $result['is_error'], 0,
                             "Error message: " . CRM_Utils_Array::value( 'error_message', $result ) .' in line ' . __LINE__ );
 
        $this->documentMe($params,$result,__FUNCTION__,__FILE__,$description,$subfile); 
        $result = & civicrm_api3_activity_get(array('id' => $result['id'], 'version' => $this->_apiversion, 'return.assignee_contact_id' => 1, 'return.target_contact_id' => 1 ) );

        $this->assertEquals( 17,$result['values'][$result['id']]['assignee_contact_id'][0], 'in line ' . __LINE__ );
        $this->assertEquals( 17,$result['values'][$result['id']]['target_contact_id'][0], 'in line ' . __LINE__ );
 
    }
    
    function testActivityCreateExample( )
    {
        /**
         *  Test civicrm_activity_create() using example code
         */
        require_once 'api/v3/examples/ActivityCreate.php';
        $result = activity_create_example();
        $expectedResult = activity_create_expectedresult();
        $this->assertEquals($result,$expectedResult);
    }
    /**
     *  Test civicrm_activity_create() with valid parameters for unique fields - 
     *  set up to see if unique fields work but activity_subject doesn't

     function testActivityCreateUniqueName( )
     {
     $this->markTestSkipped('test to see if api will take unique names but it doesn\'t yet');
     /*fields with unique names activity_id, 
     * activity_subject,activity_duration
     * activity_location, activity_status_id
     * activity_is_test
     * activity_medium_id
       
     $params = array(
     'source_contact_id'   => 17,
     'activity_subject'             => 'Make-it-Happen Meeting',
     'activity_date_time'  => date('Ymd'),
     'activity_duration'            => 120,
     'activity_location'            => 'Pensulvania',
     'details'             => 'a test activity',
     'activity_status_id'           => 1,
     'activity_name'       => 'Test activity type',
     'version'							=> $this->_apiversion,
     );
        
     $result =  civicrm_api3_activity_create( $params );
     $this->assertEquals( $result['is_error'], 0,
     "Error message: " . CRM_Utils_Array::value( 'error_message', $result ) );
 
     $this->assertEquals( $result['values'][$result['id']]['source_contact_id'], 17 );
     $this->assertEquals( $result['values'][$result['id']]['duration'], 120 );
     $this->assertEquals( $result['values'][$result['id']]['subject'], 'Make-it-Happen Meeting' ); //This field gets lost
     $this->assertEquals( $result['values'][$result['id']]['activity_date_time'], date('Ymd') . '000000' );
     $this->assertEquals( $result['values'][$result['id']]['location'], 'Pensulvania' );
     $this->assertEquals( $result['values'][$result['id']]['details'], 'a test activity' );
     $this->assertEquals( $result['values'][$result['id']]['status_id'], 1 );

     }
    */
    
    /**
     *  Test civicrm_activity_create() with valid parameters
     *  and some custom data
     */
    function testActivityCreateCustom( )
    {
        $ids = $this->entityCustomGroupWithSingleFieldCreate( __FUNCTION__,__FILE__);
        
        $params = $this->_params;
        $params['custom_'.$ids['custom_field_id']]  =  "custom string";
 
        $result = civicrm_api($this->_entity,'create', $params);
        $this->documentMe($params,$result  ,__FUNCTION__,__FILE__);
        $this->assertNotEquals( $result['is_error'],1 ,$result['error_message'] . ' in line ' . __LINE__);
        $result = civicrm_api($this->_entity,'get',array('return.custom_'.$ids['custom_field_id'] => 1,         'version' =>3, 'id' => $result['id']));
        $this->assertEquals("custom string", $result['values'][$result['id']]['custom_' .$ids['custom_field_id'] ],' in line ' . __LINE__);
  
        $this->customFieldDelete($ids['custom_field_id']);
        $this->customGroupDelete($ids['custom_group_id']);      
    }

    /**
     *  Test civicrm_activity_create() with an invalid text status_id
     */
    function testActivityCreateBadTextStatus( )
    {
        //  Truncate the tables
        $op = new PHPUnit_Extensions_Database_Operation_Truncate( );
        $op->execute( $this->_dbconn,
                      new PHPUnit_Extensions_Database_DataSet_FlatXMLDataSet(
                                                                             dirname(__FILE__) . '/../../CiviTest/truncate-option.xml') );
                             
        //  Insert a row in civicrm_option_group creating 
        //  an activity_status option group
        $op = new PHPUnit_Extensions_Database_Operation_Insert( );
        $op->execute( $this->_dbconn,
                      new PHPUnit_Extensions_Database_DataSet_FlatXMLDataSet(
                                                                             dirname(__FILE__)
                                                                             . '/dataset/option_group_activity.xml') );

        //  Insert rows in civicrm_option_value defining activity status
        //  values of 'Scheduled', 'Completed', 'Cancelled'
        $op = new PHPUnit_Extensions_Database_Operation_Insert( );
        $op->execute( $this->_dbconn,
                      new PHPUnit_Extensions_Database_DataSet_XMLDataSet(
                                                                         dirname(__FILE__)
                                                                         . '/dataset/option_value_activity.xml') );

        $params = array(
                        'source_contact_id'   => 17,
                        'subject'             => 'Discussion on Apis for v3',
                        'activity_date_time'  => date('Ymd'),
                        'duration'            => 120,
                        'location'            => 'Pensulvania',
                        'details'             => 'a test activity',
                        'status_id'           => 'Invalid',
                        'activity_name'       => 'Test activity type',
                        'version'						 => $this->_apiversion,
                        );
        
        $result = civicrm_api3_activity_create( $params );
        
        $this->assertEquals( $result['is_error'], 1,
                             "In line " . __LINE__ );
    }

    /**
     *  Test civicrm_activity_create() with valid parameters,
     *  using a text status_id
     */
    function testActivityCreateTextStatus( )
    {
        //  Truncate the tables
        $op = new PHPUnit_Extensions_Database_Operation_Truncate( );
        $op->execute( $this->_dbconn,
                      new PHPUnit_Extensions_Database_DataSet_FlatXMLDataSet(
                                                                             dirname(__FILE__) . '/../../CiviTest/truncate-option.xml') );
                             
        //  Insert a row in civicrm_option_group creating 
        //  an activity_status option group
        $op = new PHPUnit_Extensions_Database_Operation_Insert( );
        $op->execute( $this->_dbconn,
                      new PHPUnit_Extensions_Database_DataSet_FlatXMLDataSet(
                                                                             dirname(__FILE__)
                                                                             . '/dataset/option_group_activity.xml') );

        //  Insert rows in civicrm_option_value defining activity status
        //  values of 'Scheduled', 'Completed', 'Cancelled'
        $op = new PHPUnit_Extensions_Database_Operation_Insert( );
        $op->execute( $this->_dbconn,
                      new PHPUnit_Extensions_Database_DataSet_XMLDataSet(
                                                                         dirname(__FILE__)
                                                                         . '/dataset/option_value_activity.xml') );

        $params = array(
                        'source_contact_id'   => 17,
                        'subject'             => 'Make-it-Happen Meeting',
                        'activity_date_time'  => date('Ymd'),
                        'duration'            => 120,
                        'location'            => 'Pensulvania',
                        'details'             => 'a test activity',
                        'status_id'           => 'Scheduled',
                        'activity_name'       => 'Test activity type',
                        'version'							=> $this->_apiversion,
                        );
        
        $result = civicrm_api3_activity_create( $params );
        $this->assertEquals( $result['is_error'], 0,
                             "Error message: " . CRM_Utils_Array::value( 'error_message', $result )  .' in line ' . __LINE__ );
        $this->assertEquals( $result['values'][$result['id']]['source_contact_id'], 17 ,'in line ' . __LINE__);
        $this->assertEquals( $result['values'][$result['id']]['duration'], 120,'in line ' . __LINE__ );
        $this->assertEquals( $result['values'][$result['id']]['subject'], 'Make-it-Happen Meeting','in line ' . __LINE__ );
        $this->assertEquals( $result['values'][$result['id']]['activity_date_time'], date('Ymd')  . '000000' ,'in line ' . __LINE__ );
        $this->assertEquals( $result['values'][$result['id']]['location'], 'Pensulvania' ,'in line ' . __LINE__);
        $this->assertEquals( $result['values'][$result['id']]['details'], 'a test activity' ,'in line ' . __LINE__);
        $this->assertEquals( $result['values'][$result['id']]['status_id'], 'Scheduled' ,'in line ' . __LINE__);
    }

    /**
     *  Test civicrm_activity_get() with no params
     */
    function testActivityGetEmpty()
    {
        $params = array('version' => $this->_apiversion);
        $result = civicrm_api3_activity_get( $params );
        $this->assertEquals( 0, $result['is_error'], 'In line ' . __LINE__ );
    }

    /**
     *  Test civicrm_activity_get() with a good activity ID
     */
    function testActivityGetGoodID1()
    {
        //  Insert rows in civicrm_activity creating activities 4 and
        //  13
        $decription = "Function demonstrates getting asignee_contact_id & using it to get the contact";
        $subfile = 'ReturnAssigneeContact';
        $op = new PHPUnit_Extensions_Database_Operation_Insert( );
        $op->execute( $this->_dbconn,
                      new PHPUnit_Extensions_Database_DataSet_XMLDataSet(
                                                                         dirname(__FILE__)
                                                                         . '/dataset/activity_4_13.xml') );

        $contact = civicrm_api('Contact','Create',array('display_name' => "The Rock", 'contact_type' => 'Individual', 'version' => 3, 'api.activity.create' => array('id' => 13, 'assignee_contact_id' => '$value.id',)));                                                            
        $params = array( 'activity_id' => 13,
                         'version'			=> $this->_apiversion,
                         'sequential'  =>1,
                         'return.assignee_contact_id' => 1,
        								 'api.contact.get' => array('id' => '$value.source_contact_id', ));
     
        $result = civicrm_api( 'Activity','Get',$params );
            $this->documentMe($params,$result,__FUNCTION__,__FILE__,$description,$subfile);     
        
        $this->assertEquals( 0, $result['is_error'],
                             "Error message: " . CRM_Utils_Array::value( 'error_message', $result ) );
        $this->assertEquals( 13, $result['id'],  'In line ' . __LINE__ );
        $this->assertEquals( 17, $result['values'][0]['source_contact_id'], 'In line ' . __LINE__ );

        $this->assertEquals( $contact['id'], $result['values'][0]['assignee_contact_id'][0],  'In line ' . __LINE__ );
    
        $this->assertEquals( 17, $result['values'][0]['api.contact.get']['values'][0]['contact_id'],  'In line ' . __LINE__ );
        $this->assertEquals( 1, $result['values'][0]['activity_type_id'],'In line ' . __LINE__ );
        $this->assertEquals( "test activity type id",$result['values'][0]['subject'], 'In line ' . __LINE__ );
    }
    
    /*
     * test that get functioning does filtering
     */
    function testGetFilter(){
      $params = array(
                        'source_contact_id'   => 17,
                        'subject'             => 'Make-it-Happen Meeting',
                        'activity_date_time'  => '20110316',
                        'duration'            => 120,
                        'location'            => 'Pensulvania',
                        'details'             => 'a test activity',
                        'status_id'           => 1,
                        'activity_name'       => 'Test activity type',
                        'version'             => $this->_apiversion,
                        'priority_id'         => 1,
                        );
      civicrm_api('Activity','Create', $params    );     
      $result = civicrm_api('Activity','Get', array('version' => 3,'subject' => 'Make-it-Happen Meeting' ));
      $this->assertEquals(1, $result['count']);
      $this->assertEquals('Make-it-Happen Meeting', $result['values'][$result['id']]['subject']);
      civicrm_api('Activity','Delete',array('version' => 3, 'id' => $result['id']));
      
    }

    /**
     *  Test civicrm_activity_get() with a good activity ID which
     *  has associated custom data
     */
    function testActivityGetGoodIDCustom()
    {
       $ids = $this->entityCustomGroupWithSingleFieldCreate( __FUNCTION__,__FILE__);
  
        $params = $this->_params;
        $params['custom_'.$ids['custom_field_id']]  =  "custom string";
 
        $result = civicrm_api($this->_entity,'create', $params);
        //  Retrieve the test value
        $params = array( 
                         'activity_type_id' => 1,
                         'version' =>3, 
                         'sequential' =>1,
                         'return.custom_'.$ids['custom_field_id'] => 1);
        $result = civicrm_api3_activity_get( $params, true );
        $this->documentMe($params,$result,__FUNCTION__,__FILE__); 
        $this->assertEquals( 0, $result['is_error'], "Error message: " . CRM_Utils_Array::value( 'error_message', $result ) );
        $this->assertEquals("custom string", $result['values'][0]['custom_' .$ids['custom_field_id'] ],' in line ' . __LINE__);

        
        $this->assertEquals( 17, $result['values'][0]['source_contact_id'], 'In line ' . __LINE__ );
        $this->assertEquals( 1, $result['values'][0]['activity_type_id'], 'In line ' . __LINE__ );
        $this->assertEquals( 'test activity type id', $result['values'][0]['subject'],'In line ' . __LINE__ );
    }
    /**
     *  Test civicrm_activity_get() with a good activity ID which
     *  has associated custom data
     */
    function testActivityGetContact_idCustom()
    {
       $ids = $this->entityCustomGroupWithSingleFieldCreate( __FUNCTION__,__FILE__);
  
        $params = $this->_params;
        $params['custom_'.$ids['custom_field_id']]  =  "custom string";
 
        $result = civicrm_api($this->_entity,'create', $params);
        //  Retrieve the test value
        $params = array( 'contact_id' =>  $this->_params ['source_contact_id'],
                         'activity_type_id' => 1,
                         'version' =>3, 
                         'sequential' =>1,
                         'return.custom_'.$ids['custom_field_id'] => 1);
        $result = civicrm_api3_activity_get( $params, true );
        $this->documentMe($params,$result,__FUNCTION__,__FILE__); 
        $this->assertEquals( 0, $result['is_error'], "Error message: " . CRM_Utils_Array::value( 'error_message', $result ) );
        $this->assertEquals("custom string", $result['values'][0]['custom_' .$ids['custom_field_id'] ],' in line ' . __LINE__);

        
        $this->assertEquals( 17, $result['values'][0]['source_contact_id'], 'In line ' . __LINE__ );
        $this->assertEquals( 1, $result['values'][0]['activity_type_id'], 'In line ' . __LINE__ );
        $this->assertEquals( 'test activity type id', $result['values'][0]['subject'],'In line ' . __LINE__ );
    }
    
  
    /**
     * check activity deletion with empty params
     */
    function testDeleteActivityForEmptyParams( )
    {
        $params = array('version' => $this->_apiversion );
        $result =& civicrm_api3_activity_delete($params);
        $this->assertEquals( $result['is_error'], 1,
                             "In line " . __LINE__ );
    }

    /**
     * check activity deletion without activity id
     */
    function testDeleteActivityWithoutId( )
    {
        $params = array('activity_name' => 'Meeting',
                        'version' => $this->_apiversion);
        $result =& civicrm_api3_activity_delete($params);
        $this->assertEquals( $result['is_error'], 1,
                             "In line " . __LINE__ );
    }

    /**
     * check activity deletion without activity type
     */
    function testDeleteActivityWithoutActivityType( )
    {
        $params = array( 'id' => 1 );
        $result =& civicrm_api3_activity_delete( $params );
        $this->assertEquals( $result['is_error'], 1,
                             "In line " . __LINE__ );
    }
    
    /**
     * check activity deletion with incorrect data
     */
    function testDeleteActivityWithIncorrectActivityType( )
    {
        $params = array( 'id'            => 1,
                         'activity_name' => 'Test Activity'
                         );

        $result =& civicrm_api3_activity_delete( $params );
        $this->assertEquals( $result['is_error'], 1,
                             "In line " . __LINE__ );
    }

    /**
     * check activity deletion with correct data
     */
    function testDeleteActivity( )
    {
        //  Insert rows in civicrm_activity creating activities 4 and
        //  13 
        $op = new PHPUnit_Extensions_Database_Operation_Insert( );
        $op->execute( $this->_dbconn,
                      new PHPUnit_Extensions_Database_DataSet_XMLDataSet(
                                                                         dirname(__FILE__)
                                                                         . '/dataset/activity_4_13.xml') );
        $params = array( 'id' => 13,
                         'activity_type_id' => 1 ,
                         'version' => $this->_apiversion,);
        
        $result =& civicrm_api3_activity_delete($params);
        $this->documentMe($params,$result,__FUNCTION__,__FILE__); 
        $this->assertEquals( $result['is_error'], 0,
                             "Error message: " . CRM_Utils_Array::value( 'error_message', $result ) );
    }




     
    /**
     * check with empty array
     */
    function testActivityUpdateEmpty( )
    {
        $params = array( );
        $result =& civicrm_api3_activity_create($params);
        $this->assertEquals( $result['is_error'], 1,
                             "In line " . __LINE__ );
    }

    /**
     * check if required fields are not passed
     */
    function testActivityUpdateWithoutRequired( )
    {
        $params = array(
                        'subject'             => 'this case should fail',
                        'scheduled_date_time' => date('Ymd')
                        );
        
        $result =& civicrm_api3_activity_create($params);
        $this->assertEquals( $result['is_error'], 1,
                             "In line " . __LINE__ );
    }

    /**
     * check with incorrect required fields
     */
    function testActivityUpdateWithIncorrectData( )
    {
        $params = array(
                        'activity_name'       => 'Meeting',
                        'subject'             => 'this case should fail',
                        'scheduled_date_time' => date('Ymd')
                        );

        $result =& civicrm_api3_activity_create($params);
        $this->assertEquals( $result['is_error'], 1,
                             "In line " . __LINE__ );
    }

    /**
     * Test civicrm_activity_update() with non-numeric id
     */
    function testActivityUpdateWithNonNumericId( )
    {
        $params = array( 'id'                  => 'lets break it',
                         'activity_name'       => 'Meeting',
                         'subject'             => 'this case should fail',
                         'scheduled_date_time' => date('Ymd')
                         );

        $result =& civicrm_api3_activity_create($params);
        $this->assertEquals( $result['is_error'], 1,
                             "In line " . __LINE__ );
    }

    /**
     * check with incorrect required fields
     */
    function testActivityUpdateWithIncorrectContactActivityType( )
    {
        $params = array(
                        'id'                  => 1,
                        'activity_name'       => 'Test Activity',
                        'subject'             => 'this case should fail',
                        'scheduled_date_time' => date('Ymd'),
                        'version'							=> $this->_apiversion,
                        'source_contact_id'   => 17,
                        );

        $result =& civicrm_api3_activity_create($params);
        $this->assertEquals( $result['is_error'], 1, "In line " . __LINE__ );
        $this->assertEquals( $result['error_message'], 'Invalid Activity Id' ,"In line " . __LINE__ );
    }
    
    /**
     *  Test civicrm_activity_update() to update an existing activity
     */
    function testActivityUpdate( )
    {
        //  Insert rows in civicrm_activity creating activities 4 and 13
        $op = new PHPUnit_Extensions_Database_Operation_Insert( );
        $op->execute( $this->_dbconn,
                      new PHPUnit_Extensions_Database_DataSet_XMLDataSet(
                                                                         dirname(__FILE__)
                                                                         . '/dataset/activity_4_13.xml') );

        $params = array(
                        'id'                  => 4,
                        'subject'             => 'Make-it-Happen Meeting',
                        'activity_date_time'  => '20091011123456',
                        'duration'            => 120,
                        'location'            => '21, Park Avenue',
                        'details'             => 'Lets update Meeting',
                        'status_id'           => 1,
                        'activity_name'       => 'Test activity type',
                        'source_contact_id'   => 17,
                        'priority_id'         => 1,
                        'version'							=>$this->_apiversion,
                        );

        $result = civicrm_api3_activity_create( $params );

        //  civicrm_activity should show new values
        $expected = new PHPUnit_Extensions_Database_DataSet_XMLDataSet(
                                                                       dirname( __FILE__ )
                                                                       . '/dataset/activity_4_13_updated.xml' );
        $actual = new PHPUnit_Extensions_Database_DataSet_QueryDataset(
                                                                       $this->_dbconn );
        $actual->addTable( 'civicrm_activity' );
        $expected->assertEquals( $actual );
    }
    
    /**
     *  Test civicrm_activity_update() with valid parameters
     *  and some custom data
     */
    function testActivityUpdateCustom( )
    {
        $ids = $this->entityCustomGroupWithSingleFieldCreate( __FUNCTION__,__FILE__);
        
        $params = $this->_params;
   
        //  Create an activity with custom data
        //this has been updated from the previous 'old format' function - need to make it work
        $params = array(
                        'source_contact_id'   => 17,
                        'subject'             => 'Make-it-Happen Meeting',
                        'activity_date_time'  => '2009-10-18',
                        'duration'            => 120,
                        'location'            => 'Pensulvania',
                        'details'             => 'a test activity to check the update api',
                        'status_id'           => 1,
                        'activity_name'       => 'Test activity type',
                        'version'		          => $this->_apiversion,
                        'custom_'.$ids['custom_field_id']           => 'custom string', 
                        );
        $result = civicrm_api3_activity_create( $params );
        $activityId = $result['id'];
        $this->assertEquals( 0, $result['is_error'],
                             "Error message: " . CRM_Utils_Array::value( 'error_message', $result ) );
        $result = civicrm_api($this->_entity,'get',array('return.custom_'.$ids['custom_field_id'] => 1,         'version' =>3, 'id' => $result['id']));
        $this->assertEquals("custom string", $result['values'][$result['id']]['custom_' .$ids['custom_field_id'] ],' in line ' . __LINE__);
        $this->assertEquals("2009-10-18 00:00:00", $result['values'][$result['id']]['activity_date_time' ],' in line ' . __LINE__);
        
        
        //  Update the activity with custom data
        $params = array(
                        'id'                  => $activityId,
                        'source_contact_id'   => 17,
                        'subject'             => 'Make-it-Happen Meeting',
                        'status_id'           => 1,
                        'activity_name'       => 'Test activity type',
                        'activity_date_time'  => date('Ymd'), // add this since dates are messed up
                        'custom_'.$ids['custom_field_id']           => 'Updated my test data',
                        'version'		       => $this->_apiversion,
                        );
        $result = civicrm_api('Activity','Create', $params );
        $this->assertEquals( 0, $result['is_error'],
                             "Error message: " . CRM_Utils_Array::value( 'error_message', $result ) );

        $this->documentMe($params,$result,__FUNCTION__,__FILE__); 

        $result = civicrm_api($this->_entity,'get',array('return.custom_'.$ids['custom_field_id'] => 1,         'version' =>3, 'id' => $result['id']));
        $this->assertEquals("Updated my test data", $result['values'][$result['id']]['custom_' .$ids['custom_field_id'] ],' in line ' . __LINE__);
        
    }

    /**
     *  Test civicrm_activity_update() for core activity fields
     *  and some custom data
     */
    function testActivityUpdateCheckCoreFields( )
    {        
        $params = $this->_params;
   
        $contact1Params = array('first_name'       => 'John',
                                'middle_name'      => 'J.',
                                'last_name'        => 'Anderson',
                                'prefix_id'        => 3,
                                'suffix_id'        => 3,
                                'email'            => 'john_anderson@civicrm.org',
                                'contact_type'     => 'Individual' );
        
        $contact1 = $this->individualCreate( $contact1Params );

        $contact2Params = array('first_name'       => 'Michal',
                                'middle_name'      => 'J.',
                                'last_name'        => 'Anderson',
                                'prefix_id'        => 3,
                                'suffix_id'        => 3,
                                'email'            => 'michal_anderson@civicrm.org',
                                'contact_type'     => 'Individual' );
        
        $contact2 = $this->individualCreate( $contact2Params );

        $params['assignee_contact_id'] = array( $contact1 => $contact1 );
        $params['target_contact_id']   = array( $contact2 => $contact2 );
        $result = civicrm_api('Activity','Create', $params );

        $result = civicrm_api3_activity_create( $params );
        $activityId = $result['id'];
        $this->assertEquals( 0, $result['is_error'],
                             "Error message: " . CRM_Utils_Array::value( 'error_message', $result ) );
        $result = civicrm_api($this->_entity,'get',array('return.assignee_contact_id' => 1, 'return.target_contact_id' => 1, 'version' =>3, 'id' => $result['id']));
        
        $assignee = $result['values'][$result['id']]['assignee_contact_id'];
        $target   = $result['values'][$result['id']]['target_contact_id'];

        $this->assertEquals( 1, count($assignee), ' in line ' . __LINE__);
        $this->assertEquals( 1, count($target), ' in line ' . __LINE__);
        $this->assertEquals( true, in_array($contact1, $assignee), ' in line ' . __LINE__);
        $this->assertEquals( true, in_array($contact2, $target), ' in line ' . __LINE__);
        
        $contact3Params = array('first_name'       => 'Jijo',
                                'middle_name'      => 'J.',
                                'last_name'        => 'Anderson',
                                'prefix_id'        => 3,
                                'suffix_id'        => 3,
                                'email'            => 'jijo_anderson@civicrm.org',
                                'contact_type'     => 'Individual' );

        $contact4Params = array('first_name'       => 'Grant',
                                'middle_name'      => 'J.',
                                'last_name'        => 'Anderson',
                                'prefix_id'        => 3,
                                'suffix_id'        => 3,
                                'email'            => 'grant_anderson@civicrm.org',
                                'contact_type'     => 'Individual' );
        
        $contact3 = $this->individualCreate( $contact3Params );
        $contact4 = $this->individualCreate( $contact4Params );

        $params = array( );
        $params['id']                  = $activityId;
        $params['version']             = $this->_apiversion;
        $params['assignee_contact_id'] = array( $contact3 => $contact3 );
        $params['target_contact_id']   = array( $contact4 => $contact4 );

        $result = civicrm_api3_activity_create( $params );
        $this->assertEquals( 0, $result['is_error'],
                             "Error message: " . CRM_Utils_Array::value( 'error_message', $result ) );
        
        $this->assertEquals( $activityId, $result['id'], ' in line ' . __LINE__);

        $result = civicrm_api($this->_entity,'get',array('return.assignee_contact_id' => 1, 'return.target_contact_id' => 1, 'version' =>3, 'id' => $result['id']));

        $assignee = $result['values'][$result['id']]['assignee_contact_id'];
        $target   = $result['values'][$result['id']]['target_contact_id'];
        
        $this->assertEquals( 1, count($assignee), ' in line ' . __LINE__);
        $this->assertEquals( 1, count($target), ' in line ' . __LINE__);
        $this->assertEquals( true, in_array($contact3, $assignee), ' in line ' . __LINE__);
        $this->assertEquals( true, in_array($contact4, $target), ' in line ' . __LINE__);
        
        foreach ( $this->_params as $fld => $val ) {
            if ( $fld == 'version' ) {
                continue;
            }
            $this->assertEquals($val, $result['values'][$result['id']][$fld],' in line ' . __LINE__);
        }
    }


    /**
     *  Test civicrm_activity_update() where the DB has a date_time
     *  value and there is none in the update params.
     */
    function testActivityUpdateNotDate( )
    {
        //  Insert rows in civicrm_activity creating activities 4 and 13
        $op = new PHPUnit_Extensions_Database_Operation_Insert( );
        $op->execute( $this->_dbconn,
                      new PHPUnit_Extensions_Database_DataSet_XMLDataSet(
                                                                         dirname(__FILE__)
                                                                         . '/dataset/activity_4_13.xml') );
        //  
        $params = array(
                        'id'                  => 4,
                        'subject'             => 'Updated Make-it-Happen Meeting',
                        'duration'            => 120,
                        'location'            => '21, Park Avenue',
                        'details'             => 'Lets update Meeting',
                        'status_id'           => 1,
                        'activity_name'       => 'Test activity type',
                        'source_contact_id'   => 17,
                        'priority_id'         => 1,
                        'version'							=>$this->_apiversion,
                        );

        $result = civicrm_api3_activity_create( $params );

        //  civicrm_activity should show new values except date
        $expected = new PHPUnit_Extensions_Database_DataSet_XMLDataSet(
                                                                       dirname( __FILE__ )
                                                                       . '/dataset/activity_4_13_updated_not_date.xml' );
        $actual = new PHPUnit_Extensions_Database_DataSet_QueryDataset(
                                                                       $this->_dbconn );
        $actual->addTable( 'civicrm_activity' );
        $expected->assertEquals( $actual );
    }
    
    /**
     * check activity update with status
     */
    function testActivityUpdateWithStatus( )
    {
        //  Truncate the tables
        $op = new PHPUnit_Extensions_Database_Operation_Truncate( );
        $op->execute( $this->_dbconn,
                      new PHPUnit_Extensions_Database_DataSet_FlatXMLDataSet(
                                                                             dirname(__FILE__) . '/../../CiviTest/truncate-option.xml') );

        //  Insert a row in civicrm_option_group creating 
        //  an activity_status option group
        $op = new PHPUnit_Extensions_Database_Operation_Insert( );
        $op->execute( $this->_dbconn,
                      new PHPUnit_Extensions_Database_DataSet_FlatXMLDataSet(
                                                                             dirname(__FILE__)
                                                                             . '/dataset/option_group_activity.xml') );

        //  Insert rows in civicrm_option_value defining activity status
        //  values of 'Scheduled', 'Completed', 'Cancelled'
        $op = new PHPUnit_Extensions_Database_Operation_Insert( );
        $op->execute( $this->_dbconn,
                      new PHPUnit_Extensions_Database_DataSet_XMLDataSet(
                                                                         dirname(__FILE__)
                                                                         . '/dataset/option_value_activity.xml') );  
                                                         
        //  Insert a row in civicrm_activity creating activity 1
        $op = new PHPUnit_Extensions_Database_Operation_Insert( );
        $op->execute( $this->_dbconn,
                      new PHPUnit_Extensions_Database_DataSet_XMLDataSet(
                                                                         dirname(__FILE__)
                                                                         . '/dataset/activity_type_5.xml') );
        $params = array(
                        'id'                  => 4,
                        'source_contact_id'   => 17,
                        'subject'             => 'Hurry update works', 
                        'status_id'           => 1,
                        'activity_name'       => 'Test activity type',
                        'version'					 =>$this->_apiversion,
                        );

        $result = civicrm_api3_activity_create( $params );
        $this->assertNotContains( 'is_error', $result );
        $this->assertEquals( $result['id'] , 4, "In line " . __LINE__ );
        $this->assertEquals( $result['values'][4]['source_contact_id'] , 17,
                             "In line " . __LINE__ );
        $this->assertEquals( $result['values'][4]['subject'], 'Hurry update works',
                             "In line " . __LINE__ );
        $this->assertEquals( $result['values'][4]['status_id'], 1,
                             "In line " . __LINE__ );
    }




    /**
     *  Test civicrm_activities_contact_get()
     */
    function testActivitiesContactGet()
    {
        //  Insert rows in civicrm_activity creating activities 4 and 13
        $op = new PHPUnit_Extensions_Database_Operation_Insert( );
        $op->execute( $this->_dbconn,
                      new PHPUnit_Extensions_Database_DataSet_XMLDataSet(
                                                                         dirname(__FILE__)
                                                                         . '/dataset/activity_4_13.xml') );
        //  Get activities associated with contact 17
        $params = array( 'contact_id' => 17 ,
                         'version'    => $this->_apiversion);
        $result = civicrm_api3_activity_get( $params );

        $this->assertEquals( 0, $result['is_error'], "Error message: " . CRM_Utils_Array::value( 'error_message', $result ) );
        $this->assertEquals( 2, $result['count'],'In line ' . __LINE__ );
        $this->assertEquals( 1, $result['values'][4]['activity_type_id'] , 'In line ' . __LINE__ );
        $this->assertEquals( 4, $result['values'][4]['id'] , 'In line ' . __LINE__ );
        $this->assertEquals( 'Test activity type', $result['values'][4]['activity_name'],'In line ' . __LINE__ );
        $this->assertEquals( 'Test activity type', $result['values'][13]['activity_name'],'In line ' . __LINE__ );
    }
/*
 * test chained Activity format
 */
    function testchainedActivityGet(){

      civicrm_api('Contact','Create',array('version' => $this->_apiversion, 
      									'display_name' => "bob brown", 
      									'contact_type' => 'Individual', 
      									'api.activity_type.create' => array(
                        'weight'=> '2',
                        'label' => 'send out letters',
                        'filter' => 0,
                        'is_active' =>1,
        								'is_optgroup' =>1,
                        'is_default' => 0, 
                        ),'api.activity.create' => array('activity_type_id' => '$values.api.activity_type.create.')));
      $result = civicrm_api('Activity','Get',array(
											'version' => 3,  
											'id' => $activityID, 
											'return.assignee_contact_id' => 1,
											'api.contact.get' => array('api.pledge.get' => 1)));
    }
    /**
     * check civicrm_activities_contact_get() with empty array
     */
    function testActivityContactGetEmpty( )
    {
        $params = array( );
        $result = civicrm_api3_activity_get( $params );
        $this->assertEquals( $result['is_error'], 1,"In line " . __LINE__ );
    }
   
    /**
     *  Test  civicrm_activity_contact_get() with missing source_contact_id
     */
    function testActivitiesContactGetWithInvalidParameter( )
    {
        $params = null;
        $result = civicrm_api3_activity_get( $params );
        $this->assertEquals( $result['is_error'], 1,
                             "In line " . __LINE__ );
    }

    /**
     *  Test civicrm_activity_contact_get() with invalid Contact Id
     */
    function testActivitiesContactGetWithInvalidContactId( )
    {
        $params = array( 'contact_id' => null );
        $result = civicrm_api3_activity_get( $params );
        $this->assertEquals( $result['is_error'], 1,
                             "In line " . __LINE__ );

        $params = array( 'contact_id' => 'contact' );
        $result = civicrm_api3_activity_get( $params );
        $this->assertEquals( $result['is_error'], 1,
                             "In line " . __LINE__ );
        
        $params = array( 'contact_id' => 2.4 );
        $result = civicrm_api3_activity_get( $params );
        $this->assertEquals( $result['is_error'], 1,
                             "In line " . __LINE__ );
    }

    /**
     *  Test civicrm_activity_contact_get() with contact having no Activity
     */
    function testActivitiesContactGetHavingNoActivity( )
    {
        $params = array(
                        'first_name'    => 'dan',
                        'last_name'     => 'conberg',
                        'email'         => 'dan.conberg@w.co.in',
                        'contact_type'  => 'Individual',
                        'version'				=> $this->_apiversion,
                        );
   
        $contact = civicrm_api('contact', 'create', $params );
        $params  = array( 'contact_id' => $contact['id'] ,
                          'version'				=> $this->_apiversion,        );
        $result  = civicrm_api3_activity_get( $params );
        $this->assertEquals( $result['is_error'], 0,'in line ' . __LINE__ );
        $this->assertEquals( $result['count'], 0,'in line ' . __LINE__ );
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
