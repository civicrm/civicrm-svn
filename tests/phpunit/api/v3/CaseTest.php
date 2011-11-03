<?php  // vim: set si ai expandtab tabstop=4 shiftwidth=4 softtabstop=4:

/**
 *  File for the TestCase class
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
require_once 'api/v3/Case.php';
require_once 'CRM/Core/BAO/CustomGroup.php';
require_once 'Utils.php';

/**
 *  Test APIv3 civicrm_case_* functions
 *
 *  @package   CiviCRM
 */
class api_v3_CaseTest extends CiviUnitTestCase
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
        $this->_entity = 'case';
        //  Connect to the database
        parent::setUp();
        $tablesToTruncate = array( 'civicrm_activity',
                                   'civicrm_contact',
                                   'civicrm_custom_group',
                                   'civicrm_custom_field',
                                   'civicrm_case',
                                   'civicrm_case_contact',
                                   'civicrm_case_activity',
                                   'civicrm_activity_target',
                                   'civicrm_activity_assignment',
                                   'civicrm_relationship',
                                   'civicrm_relationship_type',
                                   );

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

        //  Insert a row in civicrm_option_value creating option_group
        //  case_type
        $op = new PHPUnit_Extensions_Database_Operation_Insert( );
        $op->execute( $this->_dbconn,
                      new PHPUnit_Extensions_Database_DataSet_FlatXMLDataSet(
                                                                         dirname(__FILE__)
                                                                         . '/dataset/option_group_case.xml') );

        //  Insert a row in civicrm_option_value creating
        //  case_types
        $op = new PHPUnit_Extensions_Database_Operation_Insert( );
        $op->execute( $this->_dbconn,
                      new PHPUnit_Extensions_Database_DataSet_XMLDataSet(
                                                                         dirname(__FILE__)
                                                                         . '/dataset/option_value_case.xml') );

        //  Insert a row in civicrm_option_value creating
        //  case-specific activity_types
        $op = new PHPUnit_Extensions_Database_Operation_Insert( );
        $op->execute( $this->_dbconn,
                      new PHPUnit_Extensions_Database_DataSet_XMLDataSet(
                                                                         dirname(__FILE__)
                                                                         . '/dataset/option_value_case_activity.xml') );

        //Create relationship types
        $relTypeParams = array(
                               'name_a_b'       => 'Case Coordinator is',
                               'label_a_b'      => 'Case Coordinator is',
                               'name_b_a'       => 'Case Coordinator',
                               'label_b_a'      => 'Case Coordinator',
                               'description'    => 'Case Coordinator',
                               'contact_type_a' => 'Individual',
                               'contact_type_b' => 'Individual',
                               'is_reserved'    => 0,
                               'is_active'      => 1,
                               'version'				=>$this->_apiversion,
                               );
        $this->relationshipTypeCreate( $relTypeParams );  

        $relTypeParams = array(
                               'name_a_b'       => 'Homeless Services Coordinator is',
                               'label_a_b'      => 'Homeless Services Coordinator is',
                               'name_b_a'       => 'Homeless Services Coordinator',
                               'label_b_a'      => 'Homeless Services Coordinator',
                               'description'    => 'Homeless Services Coordinator',
                               'contact_type_a' => 'Individual',
                               'contact_type_b' => 'Individual',
                               'is_reserved'    => 0,
                               'is_active'      => 1,
                               'version'				=>$this->_apiversion,
                               );
        $this->relationshipTypeCreate( $relTypeParams );  

        $relTypeParams = array(
                               'name_a_b'       => 'Health Services Coordinator is',
                               'label_a_b'      => 'Health Services Coordinator is',
                               'name_b_a'       => 'Health Services Coordinator',
                               'label_b_a'      => 'Health Services Coordinator',
                               'description'    => 'Health Services Coordinator',
                               'contact_type_a' => 'Individual',
                               'contact_type_b' => 'Individual',
                               'is_reserved'    => 0,
                               'is_active'      => 1,
                               'version'				=>$this->_apiversion,
                               );
        $this->relationshipTypeCreate( $relTypeParams );  

        $relTypeParams = array(
                               'name_a_b'       => 'Senior Services Coordinator is',
                               'label_a_b'      => 'Senior Services Coordinator is',
                               'name_b_a'       => 'Senior Services Coordinator',
                               'label_b_a'      => 'Senior Services Coordinator',
                               'description'    => 'Senior Services Coordinator',
                               'contact_type_a' => 'Individual',
                               'contact_type_b' => 'Individual',
                               'is_reserved'    => 0,
                               'is_active'      => 1,
                               'version'				=>$this->_apiversion,
                               );
        $this->relationshipTypeCreate( $relTypeParams );  

        $relTypeParams = array(
                               'name_a_b'       => 'Benefits Specialist is',
                               'label_a_b'      => 'Benefits Specialist is',
                               'name_b_a'       => 'Benefits Specialist',
                               'label_b_a'      => 'Benefits Specialist',
                               'description'    => 'Benefits Specialist',
                               'contact_type_a' => 'Individual',
                               'contact_type_b' => 'Individual',
                               'is_reserved'    => 0,
                               'is_active'      => 1,
                               'version'				=>$this->_apiversion,
                               );
        $this->relationshipTypeCreate( $relTypeParams );  
 
        // enable the default custom templates for the case type xml files
        $this->customDirectories( array( 'template_path' => TRUE ) );

        // case is not enabled by default
        require_once 'CRM/Core/BAO/ConfigSetting.php';
        $enableResult = CRM_Core_BAO_ConfigSetting::enableComponent( 'CiviCase' );
        $this->assertTrue( $enableResult, 'Cannot enable CiviCase in line ' . __LINE__);

        $this->_params = array( 
                'case_type_id' => 1,
                'subject' => 'Test case',
                'contact_id' => 17,
                'version' => $this->_apiversion);

        // create a logged in USER since the code references it for source_contact_id
        $this->createLoggedInUser( );
        $session = CRM_Core_Session::singleton( );
        $this->_loggedInUser = $session->get( 'userID' );
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
                                   'civicrm_case',
                                   'civicrm_case_contact',
                                   'civicrm_case_activity',
                                   'civicrm_activity_target',
                                   'civicrm_activity_assignment',
                                   'civicrm_relationship',
                                   'civicrm_relationship_type',
                                   );
        $this->quickCleanup( $tablesToTruncate, true );

        $this->customDirectories( array( 'template_path' => FALSE ) );
    }

    
    /**
     * check with empty array
     */
    function testCaseCreateEmpty( )
    {
        $params = array('version' => $this->_apiversion );
        $result = & civicrm_api('case','create',$params);
        $this->assertEquals( $result['is_error'], 1,
                             "In line " . __LINE__ );
    }

    /**
     * check if required fields are not passed
     */
    function testCaseCreateWithoutRequired( )
    {
        $params = array(
                        'subject'             => 'this case should fail',
                        'case_type_id' => 1,
                        'version' => $this->_apiversion
                        );
        
        $result = & civicrm_api('case','create',$params);
        $this->assertEquals( $result['is_error'], 1,
                             "In line " . __LINE__ );
    }


    /**
     *  Test civicrm_case_create() with valid parameters
     */
    function testCaseCreate( )
    {
        
        $params = $this->_params;
        $result =& civicrm_api('case','create', $params );

        $this->assertEquals( $result['is_error'], 0,
                             "Error message: " . CRM_Utils_Array::value( 'error_message', $result ) .' in line ' . __LINE__ );


        $result =& civicrm_api('case','get', $params );
// TODO: There's more things we could check
        $this->assertEquals( $result['values'][$result['id']]['id'], 1,'in line ' . __LINE__);
        $this->assertEquals( $result['values'][$result['id']]['case_type_id'], $params['case_type_id'],'in line ' . __LINE__);
    }

    /**
     *  Test activity api create for case activities
     */
    function testCaseActivityCreate( )
    {
        // Create a case first
        $params = $this->_params;
        $result =& civicrm_api('case','create', $params );
        
        $params = array( 'case_id' => 1,
                         'activity_type_id' => 14, // follow up
                         'subject' => 'Test followup',
                         'source_contact_id' => $this->_loggedInUser,
                         'target_contact_id' => $this->_params['contact_id'],
                         'version' => $this->_apiversion,
                       );
        $result =& civicrm_api('activity','create', $params );

        $this->assertEquals( $result['is_error'], 0,
                             "Error message: " . CRM_Utils_Array::value( 'error_message', $result ) .' in line ' . __LINE__ );
        $this->assertEquals( $result['values'][$result['id']]['activity_type_id'], $params['activity_type_id'],'in line ' . __LINE__);

        // might need this for other tests that piggyback on this one
        $this->_caseActivityId = $result['values'][$result['id']]['id'];

        // Check other DB tables populated properly - is there a better way to do this? assertDBState() requires that we know the id already.
        require_once 'CRM/Case/DAO/CaseActivity.php';
        $dao = new CRM_Case_DAO_CaseActivity( );
        $dao->case_id = 1;
        $dao->activity_id = $this->_caseActivityId;
        $this->assertEquals( $dao->find( ), 1, 'case_activity table not populated correctly in line ' . __LINE__) ;
        $dao->free();

        require_once 'CRM/Activity/DAO/ActivityTarget.php';
        $dao = new CRM_Activity_DAO_ActivityTarget( );
        $dao->activity_id = $this->_caseActivityId;
        $dao->target_contact_id = $this->_params['contact_id'];
        $this->assertEquals( $dao->find( ), 1, 'activity_target table not populated correctly in line ' . __LINE__) ;
        $dao->free();

// TODO: There's more things we could check

    }

    /**
     *  Test activity api update for case activities
     */
    function testCaseActivityUpdate( )
    {
        // Need to create the case and activity before we can update it
        $this->testCaseActivityCreate( );

        $params = array( 'activity_id' => $this->_caseActivityId,
                         'case_id' => 1,
                         'activity_type_id' => 14,
                         'source_contact_id' => $this->_loggedInUser,
                         'subject' => 'New subject',
                         'version' => $this->_apiversion,
                       );
        $result =& civicrm_api('activity','create', $params );

        $this->assertEquals( $result['is_error'], 0,
                             "Error message: " . CRM_Utils_Array::value( 'error_message', $result ) .' in line ' . __LINE__ );
        $this->assertEquals( $result['values'][$result['id']]['subject'], $params['subject'],'in line ' . __LINE__);
        
        // id should be one greater, since this is a new revision
        $this->assertEquals( $result['values'][$result['id']]['id'],
                             $this->_caseActivityId + 1,
                             'in line ' . __LINE__);
        $this->assertEquals( $result['values'][$result['id']]['original_id'],
                             $this->_caseActivityId,
                             'in line ' . __LINE__);

        // Check revision is as expected
        $revParams = array( 'activity_id' => $this->_caseActivityId,
                            'version' => $this->_apiversion,
                          );
        $revActivity =& civicrm_api( 'activity', 'get', $revParams );
        $this->assertEquals( $revActivity['values'][$this->_caseActivityId]['is_current_revision'],
                             0, 
                             'in line ' . __LINE__);
        $this->assertEquals( $revActivity['values'][$this->_caseActivityId]['is_deleted'],
                             0, 
                             'in line ' . __LINE__);
        
//TODO: check some more things
    }
    
    function testCaseActivityUpdateCustom() {
        // Create a case first
        $params = $this->_params;
        $result =& civicrm_api('case','create', $params );

        // Create custom field group
        // Note the second parameter is Activity on purpose, not Case.
        $custom_ids = $this->entityCustomGroupWithSingleFieldCreate( __FUNCTION__, 'ActivityTest.php');
        
        // create activity
        $params = array( 'case_id' => 1,
                         'activity_type_id' => 14, // follow up
                         'subject' => 'Test followup',
                         'source_contact_id' => $this->_loggedInUser,
                         'target_contact_id' => $this->_params['contact_id'],
                         'custom_'.$custom_ids['custom_field_id'] => "custom string",
                         'version' => $this->_apiversion,
                       );
        $result =& civicrm_api('activity','create', $params );  

        $this->assertEquals( $result['is_error'], 0,
                             "Error message: " . CRM_Utils_Array::value( 'error_message', $result ) .' in line ' . __LINE__ );

        $aid = $result['values'][$result['id']]['id'];
        
        // Update activity
        $params = array( 'activity_id' => $aid,
                         'case_id' => 1,
                         'activity_type_id' => 14,
                         'source_contact_id' => $this->_loggedInUser,
                         'subject' => 'New subject',
                         'version' => $this->_apiversion,
                       );
        $revAct =& civicrm_api('activity','create', $params );

        $this->assertEquals( $revAct['is_error'], 0,
                             "Error message: " . CRM_Utils_Array::value( 'error_message', $revAct ) .' in line ' . __LINE__ );

        // Retrieve revision and check custom fields got copied
        $revParams = array( 'activity_id' => $aid + 1,
                            'version' => $this->_apiversion,
                            'return.custom_'.$custom_ids['custom_field_id'] => 1,
                          );
        $revAct =& civicrm_api( 'activity', 'get', $revParams );

        $this->assertEquals( $revAct['is_error'], 0,
                             "Error message: " . CRM_Utils_Array::value( 'error_message', $revAct ) .' in line ' . __LINE__ );
        $this->assertEquals( $revAct['values'][$aid + 1]['custom_'.$custom_ids['custom_field_id']], "custom string",
                             "Error message: " . CRM_Utils_Array::value( 'error_message', $revAct ) .' in line ' . __LINE__ );

        $this->customFieldDelete($custom_ids['custom_field_id']);
        $this->customGroupDelete($custom_ids['custom_group_id']);  
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
