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


require_once 'api/v3/Contact.php';
require_once 'api/v3/Location.php';
require_once 'CiviTest/CiviUnitTestCase.php';

class api_v3_LocationTest extends CiviUnitTestCase 
{
    protected $_contactID;
    protected $_apiversion;
    function get_info( )
    {
        return array(
                     'name'        => 'Location Add',
                     'description' => 'Test all Location Add API methods.',
                     'group'       => 'CiviCRM API Tests',
                     );
    }  

    function setUp() 
    {
      $this->markTestSkipped('location to be replaced with phone etc api');
        parent::setUp();
        $this->_apiversion = 3;    
        $this->_contactID    = $this->organizationCreate(null);
        $this->_locationType = $this->locationTypeCreate(null);        
    }
    
    function tearDown() 
    {
    }    

/*}


    function testAddWithoutContactid()
    {
        $params = array('location_type' => 'Home',
                        'is_primary'    => 1,
                        'name'          => 'Ashbury Terrace'
                        );
        $location = & civicrm_location_create($params);
 
        $this->assertEquals( $location['is_error'], 1 );       
        $this->assertEquals( $location['error_message'], 'Required fields not found for location contact_id' );
    }


    function testAddWithoutLocationid()
    {
        $params = array('contact_id'    => $this->_contactID,
                        'is_primary'    => 1,
                        'name'          => 'aaadadf'
                        );
        
        $location = & civicrm_location_create($params);

        $this->assertEquals( $location['is_error'], 1 );        
        $this->assertEquals( $location['error_message'], 'Required fields not found for location location_type' );
    }


    function testCreateOrganizationWithAddress()
    {
        $params = array('contact_id'             => $this->_contactID,
                        'location_type'          => 'New Location Type',
                        'is_primary'             => 1,
                        'name'                   => 'Saint Helier St',
                        'county'                 => 'Marin',
                        'country'                => 'India', 
                        'state_province'         => 'Michigan',
                        'street_address'         => 'B 103, Ram Maruti Road',
                        'supplemental_address_1' => 'Hallmark Ct', 
                        'supplemental_address_2' => 'Jersey Village',
                        'version' =>$this->_apiversion,
                        );
        
        $result = & civicrm_location_create($params);
        $this->documentMe($params,$result,__FUNCTION__,__FILE__);                 
        $match    = array( );
        $match['address'][0] = array( 'contact_id'             => $this->_contactID,
                                      'location_type_id'       => $this->_locationType->id,
                                      'country_id'             => 1101,
                                      'state_province_id'      => 1021,
                                      'street_address'         => 'B 103, Ram Maruti Road',
                                      'supplemental_address_1' => 'Hallmark Ct',
                                      'supplemental_address_2' => 'Jersey Village' );
        
        $this->_checkResult( $result['result'], $match );
    }

    function testAddWithoutStreetAddress()
    {
        $params = array('contact_id'             => $this->_contactID,
                        'location_type'          => 'New Location Type',
                        'is_primary'             => 1,
                        'name'                   => 'Saint Helier St',
                        'county'                 => 'Saginaw County',
                        'country'                => 'United States', 
                        'state_province'         => 'Michigan',
                        'supplemental_address_1' => 'Hallmark Ct', 
                        'supplemental_address_2' => 'Jersey Village',
                        'version'								 =>$this->_apiversion,
                        );
        
        $location = & civicrm_location_create($params);
        
        $match    = array( );
        $match['address'][0] = array( 'contact_id'             => $this->_contactID,
                                      'location_type_id'       => $this->_locationType->id,
                                      'is_primary'             => 1,
                                      'country_id'             => 1228,
                                      'state_province_id'      => 1021,
                                      'supplemental_address_1' => 'Hallmark Ct',
                                      'supplemental_address_2' => 'Jersey Village' );
        
        $this->_checkResult( $location['result'], $match );
    }
    
    function testAddWithAddressEmail()
    {
        $workPhone = array( 'phone'         => '91-20-276048',
                            'phone_type_id' => 1,
                            'is_primary'    => 1
                            );
        
        $workFax = array('phone'         => '91-20-234-657686',
                         'phone_type_id' => 3 );
        
        $phone     = array ($workPhone, $workFax);
        
        $workIMFirst = array('name'        => 'Hi',
                             'provider_id' => '1',
                             'is_primary'  => 0
                             );
        
        $workIMSecond = array('name'       => 'Hola',
                             'provider_id' => '3',
                             'is_primary'  => 0
                              );
        
        $workIMThird = array('name'        => 'Welcome',
                             'provider_id' => '5',
                             'is_primary'  => 1
                             );
        
        $im = array ($workIMFirst, $workIMSecond, $workIMThird );
        
        $workEmailFirst  = array( 'email'      => 'abc@def.com',
                                  'on_hold'    => 1);
        
        $workEmailSecond = array( 'email'       => 'yash@hotmail.com',
                                  'is_bulkmail' => 1);
        
        $workEmailThird  = array( 'email'      => 'yashi@yahoo.com');
        
        $email = array($workEmailFirst, $workEmailSecond, $workEmailThird);
        
        $params = array('contact_id'             => $this->_contactID,
                        'location_type'          => 'New Location Type',
                        'phone'                  => $phone,
                        'city'                   => 'San Francisco',
                        'state_province'         => 'California',
                        'country_id'             => 1228,
                        'street_address'         => '123, FC Road', 
                        'supplemental_address_1' => 'Near Wenna Lake',
                        'is_primary'             => 1,
                        'im'                     => $im,
                        'email'                  => $email,
                        'version'								 =>$this->_apiversion,
                        );
        
        $location = & civicrm_location_create($params); 
        
        $match    = array( );
        $match['address'][0] = array( 'contact_id'             => $this->_contactID,
                                      'location_type_id'       => $this->_locationType->id,
                                      'city'                   => 'San Francisco',
                                      'state_province_id'      => 1004,
                                      'country_id'             => 1228,
                                      'street_address'         => '123, FC Road', 
                                      'supplemental_address_1' => 'Near Wenna Lake',
                                      'is_primary'             => 1
                                      );
        
        $match['email'][0] = array( 'is_primary' => 1,
                                    'email'      => 'abc@def.com',
                                    'on_hold'    => 1
                                    );
        $match['email'][1] = array( 'is_primary' => 0,
                                    'email'      => 'yash@hotmail.com',
                                    'is_primary' => 0);
        $match['email'][2] = array( 'contact_id' => $this->_contactID,
                                    'is_primary' => 0,
                                    'email'      => 'yashi@yahoo.com' );
        
        $match['phone'][0] = array( 'is_primary'       => 1,
                                    'phone'            => '91-20-276048',
                                    'phone_type_id'    => 1,
                                    'location_type_id' => $this->_locationType->id,
                                    'contact_id'       => $this->_contactID );
        $match['phone'][1] = array( 'is_primary'       => 0,                               
                                    'phone_type_id'    => 3,
                                    'phone'            => '91-20-234-657686' );
        $match['im'][0] = array( 'name'             => 'Hi',
                                 'is_primary'       => 0,
                                 'provider_id'      => 1,
                                 'location_type_id' => $this->_locationType->id);
        $match['im'][1] = array( 'name'             => 'Hola',
                                 'is_primary'       => 0,
                                 'provider_id'      => 3);
        $match['im'][2] = array( 'name'             => 'Welcome',
                                 'is_primary'       => 1,
                                 'provider_id'      => 5,
                                 'contact_id'       => $this->_contactID );
        $this->_checkResult( $location['result'], $match );
    }
    
///////////////// civicrm_location_delete methods

    function testDeleteWrongParamsType()
    {
        $location = 1;
        
        $locationDelete =& civicrm_location_delete($location);
        $this->assertEquals( $locationDelete['is_error'], 1 );
        $this->assertEquals( $locationDelete['error_message'], 'Params need to be of type array!' );        
    }

    function testDeleteWithEmptyParams( )
    {
        $location = array( );
        $locationDelete =& civicrm_location_delete( $location );
        $this->assertEquals( $locationDelete['is_error'], 1 );
        $this->assertEquals( $locationDelete['error_message'], '$contact is not valid contact datatype' );
    }
    
    function testDeleteWithMissingContactId( )
    {
        $params = array( 'location_type' => 3 );
        $locationDelete =& civicrm_location_delete( $params );
        
        $this->assertEquals( $locationDelete['is_error'], 1 );
        $this->assertEquals( $locationDelete['error_message'], '$contact is not valid contact datatype' );        
    }
   
    function testDeleteWithMissingLocationTypeId( )
    {
        $params    = array( 'contact_id'    => $this->_contactID );
        $locationDelete =& civicrm_location_delete( $params );

        $this->assertEquals( $locationDelete['is_error'], 1 );
        $this->assertEquals( $locationDelete['error_message'], 'missing or invalid location' );
    }


    function testDeleteWithNoMatch( )
    {
        $params    = array( 'contact_id'    =>  $this->_contactID,
                            'location_type' => 10,
                            'version'								 =>$this->_apiversion, );
        $locationDelete =& civicrm_location_delete( $params );
        
        $this->assertEquals( $locationDelete['is_error'], 1 );
        $this->assertEquals( $locationDelete['error_message'], 'invalid location type' );                
    }
    

    function testDelete( )
    {
        $location  = $this->locationAdd(  $this->_contactID );
        
        $params = array( 'version'								 =>$this->_apiversion,
        								'contact_id'    => $this->_contactID,
                         'location_type' => $location['result']['location_type_id'] );
        $locationDelete =& civicrm_location_delete( $params );
        
        $this->assertNull( $locationDelete );
        $this->assertDBNull( 'CRM_Core_DAO_Address', $location['result']['address'][0],'contact_id','id', 'Check DB for deleted Location.');
    }

///////////////// civicrm_location_get methods

    function testGetWrongParamsType()
    {
        $params = 1;

        $result =& civicrm_location_get( $params );
        $this->assertEquals($result['is_error'], 1);
        $this->assertEquals( 'Params need to be of type array!', $result['error_message'] );
    }

    function testGetWithEmptyParams()
    {
        // empty params
        $params = array();

        $result =& civicrm_location_get( $params );
        $this->assertEquals($result['is_error'], 1);
    }

    function testGetWithoutContactId() {
        // no contact_id
        $params = array('location_type' => 'Main');
        
        $result =& civicrm_location_get( $params );
        $this->assertEquals($result['is_error'], 1);
    }

    function testGetWithEmptyLocationType() {
        // location_type an empty array
        $params = array('contact_id' => $this->_contactId, 
                         'version'								 =>$this->_apiversion,
                        'location_type' => array() );
        
        $result =& civicrm_location_get( $params );
        $this->assertEquals($result['is_error'], 1);
    }

    function testGet()
    {
        $location  = $this->locationAdd(  $this->_contactID ); 
        
        $proper = array(
            'country_id'             => 1228,
            'county_id'              => 3,
            'state_province_id'      => 1021,
            'supplemental_address_1' => 'Hallmark Ct',
            'supplemental_address_2' => 'Jersey Village',
            'version'								 =>$this->_apiversion,
        );
        $result = civicrm_location_get(array('contact_id' => $this->_contactId));
        foreach ($result as $location) {
            if ( CRM_Utils_Array::value( 'address', $location ) ) {
                foreach ($proper as $field => $value) {
                    $this->assertEquals($location['address'][$field], $value);
                }
            }
        }
    }

    
///////////////// civicrm_location_update methods


    function testUpdateWrongParamsType( )
    {
        $location = 1;
        
        $locationUpdate =& civicrm_location_update($location);
        $this->assertEquals( $locationUpdate['is_error'], 1 );
        $this->assertEquals( 'Params need to be of type array!', $locationUpdate['error_message'] );
        
    }

    function testLocationUpdateWithEmptyParams( ) 
    {
        $params = array( );

        $result = civicrm_location_update( $params );
        $this->assertEquals( $result['is_error'], 1 );
    }

    function testLocationUpdateWithMissingContactId( )
    {
        $params = array( 'location_type' => 3 );
        $locationUpdate =& civicrm_location_update( $params );
        
        $this->assertEquals( $locationUpdate['is_error'], 1 );
        $this->assertEquals( $locationUpdate['error_message'], '$contact is not valid contact datatype' );        
    }
   
    function testLocationUpdateWithMissingLocationTypeId( )
    {
        $params    = array( 'contact_id'    => $this->_contactID );
        $locationUpdate =& civicrm_location_update( $params );

        $this->assertEquals( $locationUpdate['is_error'], 1 );
        $this->assertEquals( $locationUpdate['error_message'], 'missing or invalid location_type_id' );        
    }

    function testLocationUpdate()
    {
        $location  = $this->locationAdd( $this->_contactID); 
       
        $workPhone =array('phone' => '02327276048',
                          'phone_type' => 'Phone');
        
        $phones = array ($workPhone);
        
        $workEmailFirst = array('email' => 'xyz@indiatimes.com');
        
        $workEmailSecond = array('email' => 'abcdef@hotmail.com');
        
        $emails = array($workEmailFirst,$workEmailSecond);
        
        $params = array(
                        'phone'                 => $phones,
                        'city'                  => 'Mumbai',
                        'email'                 => $emails,
                        'contact_id'            => $this->_contactID,
                        'location_type_id'      => $location['result']['location_type_id'],
                        'version'								 =>$this->_apiversion,
                                );
        
        $locationUpdate =& civicrm_location_update( $params );
        
        $this->assertEquals( $locationUpdate['is_error'], 0, 'In line ' . __LINE__ );
    }


///////////////// helper methods

    function _checkResult( &$result, &$match )
    {
        if ( CRM_Utils_Array::value( 'address', $match ) ) {
            $this->assertDBState( 'CRM_Core_DAO_Address', $result['address'][0], $match['address'][0] );
        }
        
        if ( CRM_Utils_Array::value( 'phone', $match ) ) {
            for( $i = 0; $i < count( $result['phone'] ); $i++){
                $this->assertDBState( 'CRM_Core_DAO_Phone', $result['phone'][$i], $match['phone'][$i] );
            }
        }
        
        if ( CRM_Utils_Array::value( 'email', $match ) ) {
            for( $i=0; $i < count( $result['email'] ); $i++){
                $this->assertDBState( 'CRM_Core_DAO_Email', $result['email'][$i], $match['email'][$i] );
            }
        }
        
        if ( CRM_Utils_Array::value( 'im', $match ) ) {
            for( $i=0; $i<count( $result['im'] ); $i++){
                $this->assertDBState( 'CRM_Core_DAO_IM', $result['im'][$i], $match['im'][$i] );
            }
        }
        
    }
 */   
}
 
