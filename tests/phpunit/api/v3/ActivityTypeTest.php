<?php

/**
 *  File for the TestActivityType class
 *
 *  (PHP 5)
 *  
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

require_once 'CiviTest/CiviUnitTestCase.php';
require_once 'api/v3/ActivityType.php';


/**
 *  Test APIv3 civicrm_activity_* functions
 *
 *  @package   CiviCRM
 */


class api_v3_ActivityTypeTest extends CiviUnitTestCase 
{
     protected $_apiversion;
    function get_info( ) 
    {
        return array(
                     'name'        => 'Activity Type',
                     'description' => 'Test all ActivityType Get/Create/Delete methods.',
                     'group'       => 'CiviCRM API Tests',
                     );
    }
    
    function setUp( ) 
    {  
        $this->_apiversion = 3;
        parent::setUp();
    }
    
    /**
     *  Test civicrm_activity_type_get()
     */
    function testActivityTypeGetValues()
    {
        $params = array('version' => $this->_apiversion);
        $result = & civicrm_activity_type_get($params);
         $this->documentMe($params,$result,__FUNCTION__,__FILE__); 
        $this->assertEquals($result['1'],'Meeting', 'In line ' . __LINE__ );
        $this->assertEquals($result['13'],'Open Case', 'In line ' . __LINE__ );
        
    }
    
    /**
     *  Test civicrm_activity_type_create()
     */
    function testActivityTypeCreate( ) {
        
        $params = array(
                        'weight'=> '2',
                        'label' => 'send out letters',
                        'version' => $this->_apiversion,
                        'filter' => 0,
                        'is_active' =>1,
        								'is_optgroup' =>1,
                        'is_default' => 0, 
                        );
        $result = & civicrm_activity_type_create($params);
        $this->documentMe($params,$result,__FUNCTION__,__FILE__); 
        $this->assertEquals( $result['is_error'], 0);
       
    }
            /**
     *  Test  using example code
     */
    function testActivityTypeCreateExample( )
    {
      require_once 'api/v3/examples/ActivityTypeCreate.php';
      $result = activity_type_create_example();
      $expectedResult = activity_type_create_expectedresult();
      $this->assertEquals($result,$expectedResult);
    }   
    /**
     *  Test civicrm_activity_type_create - check id
     */
    function testActivityTypecreatecheckId( ) {
        
        $params = array(
                        'label' => 'type_create',
                        'weight'=> '2',
                        'version'=> $this->_apiversion,
                        );
        $activitycreate = & civicrm_activity_type_create($params);
        $activityID = $activitycreate['id'];
        $this->assertNotContains( 'is_error', $activitycreate );
        $this->assertArrayHasKey( 'id', $activitycreate );
        $this->assertArrayHasKey( 'option_group_id', $activitycreate );
    }
    
    /**
     *  Test civicrm_activity_type_delete()
     */
    function testActivityTypeDelete( ) {
        
        $params = array(
                        'label' => 'type_create_delete',
                        'weight'=> '2',
                        'version'=> $this->_apiversion,
                        );
        $activitycreate = & civicrm_activity_type_create($params);      
        $params = array( 'activity_type_id' => $activitycreate['id'],
                          'version'=>  $this->_apiversion );
        $result = & civicrm_activity_type_delete($params);
        $this->documentMe($params,$result,__FUNCTION__,__FILE__); 
        $this->assertEquals($result , 1 , 'In line ' . __LINE__);
    }
}