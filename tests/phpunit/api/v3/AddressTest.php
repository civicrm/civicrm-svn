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


require_once 'CiviTest/CiviUnitTestCase.php';

class api_v3_AddressTest extends CiviUnitTestCase 
{
    protected $_apiversion;
    protected $_contactID;
    protected $_locationType;
    protected $params;
    protected $params2;
    
    function setUp() 
    {
        $this->_apiversion = 3;
        parent::setUp();

        $this->_contactID    = $this->organizationCreate( );
        $this->_locationType = $this->locationTypeCreate( ); 
        CRM_Core_PseudoConstant::flush('locationType');

        $this->params = array( 'contact_id'       => $this->_contactID,
                               'location_type_id' => $this->_locationType->id,
        											 'street_name'		=>	'Ambachtstraat',
															 'street_number'		=>	'23',
															 'street_address'	=>	'Ambachtstraat 23',
															 'postal_code'		=>	'6971 BN',
															 'country_id'		=>	'1152',
															 'city'				=>	'Brummen',
                               'is_primary'       => 1,
                               'version'          => $this->_apiversion );
        $this->params2 = array( 'contact_id'       => $this->_contactID,
                               'location_type_id' => $this->_locationType->id,
        											 'street_name'		=>	'Big Street',
															 'street_number'		=>	'23',
															 'street_address'	=>	'Big 23',
															 'postal_code'		=>	'6971 BN',
															 'country_id'		=>	'1152',
															 'city'				=>	'ZebraCity',
                               'is_primary'       => 0,
                               'version'          => $this->_apiversion );
        
    }

    function tearDown() 
    {  
        $this->locationTypeDelete( $this->_locationType->id );
        $this->contactDelete( $this->_contactID );    
    }

    public function testCreateAddress() {
           
        $result = civicrm_api('address','create',$this->params);
        $this->documentMe($this->params,$result,__FUNCTION__,__FILE__); 
        $this->assertEquals( 0, $result['is_error'], 'In line ' . __LINE__ );
        $this->assertEquals( 1, $result['count'], 'In line ' . __LINE__ );
        $this->assertNotNull( $result['values'][$result['id']]['id'], 'In line ' . __LINE__ );
        $this->getAndCheck($this->params, $result['id'], 'address');

    }
    
    public function testDeleteAddress() {
 
         //check there are no addresss to start with
        $get = civicrm_api('address','get',array('version' => 3,
                                      'location_type_id' => $this->_locationType->id,));
        $this->assertEquals( 0, $get['is_error'], 'In line ' . __LINE__ );       
        $this->assertEquals( 0, $get['count'], 'Contact already exists ' . __LINE__ );        
         
        //create one
        $create = civicrm_api('address','create',$this->params);
       
        $this->assertEquals( 0, $create['is_error'], 'In line ' . __LINE__ );
        
        $result = civicrm_api('address','delete', array('id'=> $create['id'], 'version' => 3) );
        $this->documentMe($this->params,$result,__FUNCTION__,__FILE__); 
        $this->assertEquals( 0, $result['is_error'], 'In line ' . __LINE__ );
        $this->assertEquals( 1, $result['count'], 'In line ' . __LINE__ );
        $get = civicrm_api('address','get',array('version' => 3,
                                      'location_type_id' => $this->_locationType->id,));
        $this->assertEquals( 0, $get['is_error'], 'In line ' . __LINE__ );       
        $this->assertEquals( 0, $get['count'], 'Contact not successfully deleted In line ' . __LINE__ );   
    }


   
    /**
     * Test civicrm_address_get - success expected.
     */
    public function testGetAddress()
    {  
        $result = civicrm_api('address','create',$this->params);
        $this->assertEquals( 0, $result['is_error'], 'In line ' . __LINE__ );
       
        $params = array( 'contact_id' => $address['id'],
                         'address' => $address['values'][$address['id']]['address'],
                         'version' => $this->_apiversion  );
        $result = civicrm_api('Address', 'Get', ($params));
        $this->documentMe($params,$result,__FUNCTION__,__FILE__);
        $this->assertEquals( 0, $result['is_error'], 'In line ' . __LINE__ );
        $this->assertEquals( $address['values'][$address['id']]['location_type_id'], $result['values'][$tag['id']]['location_type_id'], 'In line ' . __LINE__ );
        $this->assertEquals( $address['values'][$address['id']]['address_type_id'], $result['values'][$tag['id']]['address_type_id'], 'In line ' . __LINE__ );
        $this->assertEquals( $address['values'][$address['id']]['is_primary'], $result['values'][$tag['id']]['is_primary'], 'In line ' . __LINE__ );
        $this->assertEquals( $address['values'][$address['id']]['address'], $result['values'][$tag['id']]['address'], 'In line ' . __LINE__ );
    } 
    
    /**
     * Test civicrm_address_get - success expected.
     */
    public function testGetAddressSort()
    {  
        civicrm_api('address','create',$this->params);
        $subfile = "AddressSort";
        $description = "Demonstrates Use of sort filter";
        $params = array( 'options' => array('sort' => 'street_address DESC'),
                         'version' => $this->_apiversion  ,
                         'sequential' => 1,
                          );
        $result = civicrm_api('Address', 'Get', ($params));
        $this->documentMe($params,$result,__FUNCTION__,__FILE__, $description,$subfile);
        $this->assertEquals( 0, $result['is_error'], 'In line ' . __LINE__ );
        $this->assertEquals( 2, $result['count'], 'In line ' . __LINE__ );
        $this->assertEquals( 'Ambachtstraat 23',$result['values'][0]['street_address'], 'In line ' . __LINE__ );
   } 
    
}