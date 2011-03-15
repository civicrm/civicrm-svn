<?php
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

require_once 'api/v3/Event.php';
require_once 'CiviTest/CiviUnitTestCase.php';

class api_v3_EventTest extends CiviUnitTestCase 
{
    protected $_params;
    protected $_apiversion;    
    function get_info( )
    {
        return array(
                     'name'        => 'Event Create',
                     'description' => 'Test all Event Create API methods.',
                     'group'       => 'CiviCRM API Tests',
                     );
    }  

    function setUp() 
    {
        parent::setUp();
        $this->_apiversion =3;   
        $this->_params = array(
            'title'                   => 'Annual CiviCRM meet',
            'summary'                 => 'If you have any CiviCRM realted issues or want to track where CiviCRM is heading, Sign up now',
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
            'version'				=>$this->_apiversion,        
        );

        $params = array(
                        'title'         => 'Annual CiviCRM meet',
                        'event_type_id' => 1,
                        'start_date'    => 20081021,
                        'version'				=>$this->_apiversion,
                        );

        $this->_event   = civicrm_api3_event_create($params);
        $this->_eventId = $this->_event['id'];
    }

    function tearDown() 
    {
        if ( $this->_eventId ) {
            $this->eventDelete( $this->_eventId );
        }        
        $this->eventDelete( $this->_event['id'] );	
    }

///////////////// civicrm_event_get methods

    function testGetWrongParamsType()
    {
        $params = 'Annual CiviCRM meet';
        $result = civicrm_api3_event_get( $params );

        $this->assertEquals( $result['is_error'], 1 );
        $this->assertEquals( $result['error_message'], 'Input variable `params` is not an array' );
    }


    function testGetEventEmptyParams( )
    {
        $params = array( );
        $result = civicrm_api3_event_get( $params );

        $this->assertEquals( $result['is_error'], 1 );
        $this->assertEquals( $result['error_message'], 'Mandatory key(s) missing from params array: version' );
    }
    
    function testGetEventById( )
    {
        $params = array( 'id' => $this->_event['id'],
                         'version'				=>$this->_apiversion, );
        $result = civicrm_api3_event_get( $params );
        $this->assertEquals( $result['values'][$this->_eventId]['event_title'], 'Annual CiviCRM meet' );
    }
    
    function testGetEventByEventTitle( )
    {
        $params = array( 'title' => 'Annual CiviCRM meet',
                         'version'=>$this->_apiversion,
        );
        
        $result = civicrm_api3_event_get( $params );
        $this->documentMe($params,$result,__FUNCTION__,__FILE__); 
        $this->assertEquals( $result['id'], $this->_eventId );
    }

///////////////// civicrm_event_create methods
    
    function testCreateEventParamsNotArray( )
    {
        $params = null;
        $result = civicrm_api3_event_create( $params );
        $this->assertEquals( 1, $result['is_error'] );
        $this->assertEquals( 'Input variable `params` is not an array', $result['error_message'], 'In line ' . __LINE__ );
    }    
    
    function testCreateEventEmptyParams( )
    {
        $params = array( );
        $result = civicrm_api3_event_create( $params );
        $this->assertEquals( $result['is_error'], 1 );
        $this->assertEquals( 'Mandatory key(s) missing from params array: start_date, event_type_id, title, version', $result['error_message'], 'In line ' . __LINE__ );
    }
    
    function testCreateEventParamsWithoutTitle( )
    {
        unset($this->_params['title']);
        $result = civicrm_api3_event_create( $this->_params );
        $this->assertEquals( $result['is_error'], 1 );
        $this->assertEquals( 'Mandatory key(s) missing from params array: title', $result['error_message'], 'In line ' . __LINE__ );
    }
    
    function testCreateEventParamsWithoutEventTypeId( )
    {
        unset($this->_params['event_type_id']);
        $result = civicrm_api3_event_create( $this->_params );
        $this->assertEquals( $result['is_error'], 1 );
        $this->assertEquals( 'Mandatory key(s) missing from params array: event_type_id', $result['error_message'], 'In line ' . __LINE__ );
    }
    
    function testCreateEventParamsWithoutStartDate( )
    {
        unset($this->_params['start_date']);
        $result = civicrm_api3_event_create( $this->_params );
        $this->assertEquals( $result['is_error'], 1 );
        $this->assertEquals( 'Mandatory key(s) missing from params array: start_date', $result['error_message'], 'In line ' . __LINE__ );
    }
    
    function testCreateEvent( )
    {
        $result = civicrm_api3_event_create( $this->_params );
        $this->documentMe($this->_params,$result,__FUNCTION__,__FILE__); 
        $this->assertEquals( $result['is_error'], 0 );
        $this->assertArrayHasKey( 'id', $result['values'][$result['id']], 'In line ' . __LINE__  );
    }

///////////////// civicrm_event_delete methods

    function testDeleteWrongParamsType()
    {
        $params = 'Annual CiviCRM meet';
        $result =& civicrm_api3_event_delete($params);

        $this->assertEquals($result['is_error'], 1);        
        $this->assertEquals( $result['error_message'], 'Input variable `params` is not an array');
    }

    function testDeleteEmptyParams( )
    {
        $params = array( );
        $result =& civicrm_api3_event_delete($params);
        $this->assertEquals($result['is_error'], 1);        
    }
    
    function testDelete( )
    {
        $params = array('event_id' => $this->_eventId,
                        'version'				=>$this->_apiversion,);
        $result =& civicrm_api3_event_delete($params);
        $this->documentMe($params,$result,__FUNCTION__,__FILE__); 
        $this->assertNotEquals($result['is_error'], 1);
    }
    
    function testDeleteWithWrongEventId( )
    {
        $params = array('event_id' => $this->_eventId);
        $result =& civicrm_api3_event_delete($params);
        // try to delete again - there's no such event anymore
        $params = array('event_id' => $this->_eventId);
        $result =& civicrm_api3_event_delete($params);
        $this->assertEquals($result['is_error'], 1);
    }

///////////////// civicrm_event_search methods

    /**
     *  Test civicrm_event_search with wrong params type
     */
    function testSearchWrongParamsType()
    {
        $params = 'a string';
        $result =& civicrm_api3_event_get($params);

        $this->assertEquals( $result['is_error'], 1, 'In line ' . __LINE__ );
        $this->assertEquals( $result['error_message'], 'Input variable `params` is not an array', 'In line ' . __LINE__ );
    }

    /**
     *  Test civicrm_event_search with empty params
     */
     function testSearchEmptyParams()
     {
         $event  = civicrm_api3_event_create( $this->_params );

         $getparams = array('version' => $this->_apiversion,
                         'sequential' => 1, );
         $result =& civicrm_api3_event_get($getparams);
         $this->assertEquals($result['count'],2, 'In line ' . __LINE__);
         $res    = $result['values'][0];
         $this->assertArrayKeyExists('title', $res, 'In line ' . __LINE__ );
         $this->assertEquals( $res['event_type_id'], $this->_params['event_type_id'] , 'In line ' . __LINE__ );
        
     }

    /**
     *  Test civicrm_event_search. Success expected.
     */
     function testSearch()
     {
          $params = array(
                    'event_type_id'        => 1,
                    'return.title'         => 1,
                    'return.id'            => 1,
                    'return.start_date'    => 1,
                    'version'				=>$this->_apiversion,
                    );
          $result =& civicrm_api3_event_get($params);

          $this->assertEquals( $result['values'][$this->_eventId]['id'], $this->_eventId , 'In line ' . __LINE__ );
          $this->assertEquals( $result['values'][$this->_eventId]['title'], 'Annual CiviCRM meet' , 'In line ' . __LINE__ );
     }

    /**
     *  Test civicrm_event_search. Success expected.
     *  return.offset and return.max_results test (CRM-5266)
     */
     function testSearchWithOffsetAndMaxResults()
     {
         $maxEvents = 5;
         $events    = array( );
         while( $maxEvents > 0 ) {
             $params = array(
                             'title'         => 'Test Event'.$maxEvents,
                             'event_type_id' => 2,
                             'start_date'    => 20081021,
                             );
             
             $events[$maxEvents]  = civicrm_api3_event_create($params);
             $maxEvents--;
         }
         $params = array(
                         'event_type_id'      => 2,
                         'return.id'          => 1,
                         'return.title'       => 1,
                         'return.offset'      => 2,
                         'return.max_results' => 2
                         );
         $result =& civicrm_api3_event_get($params);
         $this->assertEquals( count($result), 2 , 'In line ' . __LINE__ ); 
     }

    function testEventCreationPermissions()
    {
        require_once 'CRM/Core/Permission/UnitTests.php';
        $params = array('event_type_id' => 1, 'start_date' => '2010-10-03', 'title' => 'le cake is a tie', 'check_permissions' => true,
                        'version' => $this->_apiversion);

        CRM_Core_Permission_UnitTests::$permissions = array('access CiviCRM');
        $result = civicrm_api3_event_create($params);
        $this->assertEquals(1,                                                                                                  $result['is_error'],      'lacking permissions should not be enough to create an event');
        $this->assertEquals('API permission check failed for civicrm_api3_event_create call; missing permission: access CiviEvent.', $result['error_message'], 'lacking permissions should not be enough to create an event');

        CRM_Core_Permission_UnitTests::$permissions = array('access CiviEvent', 'add contacts');
        $result = civicrm_api3_event_create($params);
        $this->assertEquals(0, $result['is_error'], 'overfluous permissions should be enough to create an event');

        CRM_Core_Permission_UnitTests::$permissions = null; // reset check() stub
    }
}
