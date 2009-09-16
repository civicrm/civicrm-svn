<?php
/*
 +--------------------------------------------------------------------+
 | CiviCRM version 3.0                                                |
 +--------------------------------------------------------------------+
 | Copyright CiviCRM LLC (c) 2004-2009                                |
 +--------------------------------------------------------------------+
 | This file is a part of CiviCRM.                                    |
 |                                                                    |
 | CiviCRM is free software; you can copy, modify, and distribute it  |
 | under the terms of the GNU Affero General Public License           |
 | Version 3, 19 November 2007.                                       |
 |                                                                    |
 | CiviCRM is distributed in the hope that it will be useful, but     |
 | WITHOUT ANY WARRANTY; without even the implied warranty of         |
 | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.               |
 | See the GNU Affero General Public License for more details.        |
 |                                                                    |
 | You should have received a copy of the GNU Affero General Public   |
 | License along with this program; if not, contact CiviCRM LLC       |
 | at info[AT]civicrm[DOT]org. If you have questions about the        |
 | GNU Affero General Public License or the licensing of CiviCRM,     |
 | see the CiviCRM license FAQ at http://civicrm.org/licensing        |
 +--------------------------------------------------------------------+
*/

require_once 'api/v2/EntityTag.php';
require_once 'CiviTest/CiviUnitTestCase.php';

class api_v2_EntityTagTest extends CiviUnitTestCase 
{

    protected $_individualID;
    protected $_householdID;
    protected $_organizationID;
    protected $_tagID;
    
    function get_info( )
    {
        return array(
                     'name'        => 'EntityTag Add',
                     'description' => 'Test all EntityTag Add API methods.',
                     'group'       => 'CiviCRM API Tests',
                     );
    }

    function setUp( ) 
    {
        parent::setUp();

        $this->_individualID = $this->individualCreate( );
        $this->_tagID = $this->tagCreate( ); 
        $this->_householdID = $this->houseHoldCreate( );
        $this->_organizationID = $this->organizationCreate( );
    }
    
    function tearDown( ) 
    {
        $this->contactDelete( $this->_individualID );
        $this->contactDelete( $this->_householdID );
        $this->contactDelete( $this->_organizationID );
        $this->tagDelete( $this->_tagID );
    }

///////////////// civicrm_entity_tag_add methods
    
    function testAddWrongParamsType()    
    {
        $params = "some string";                             
        $individualEntity = civicrm_entity_tag_add( $params ); 
        $this->assertEquals( $individualEntity['is_error'], 1,
                             "In line " . __LINE__  ); 
        $this->assertEquals( $individualEntity['error_message'], 'contact_id is a required field' );
    }

    function testAddEmptyParams( ) 
    {
        $params = array( );                             
        $individualEntity = civicrm_entity_tag_add( $params ); 
        $this->assertEquals( $individualEntity['is_error'], 1 ); 
        $this->assertEquals( $individualEntity['error_message'], 'contact_id is a required field' );
    }
    
    function testAddWithoutTagID( )
    {
        $ContactId =  $this->_individualID;
        $params = array('contact_id' =>  $ContactId);              
        $individualEntity = civicrm_entity_tag_add( $params ); 
        $this->assertEquals( $individualEntity['is_error'], 1 );
        $this->assertEquals( $individualEntity['error_message'], 'tag_id is a required field' );
    }

    function testAddWithoutContactID()
    {
        $params = array('tag_id' => $this->_tagID);              
        $individualEntity = civicrm_entity_tag_add( $params ); 
        $this->assertEquals( $individualEntity['is_error'], 1 );
        $this->assertEquals( $individualEntity['error_message'], 'contact_id is a required field' );
    }
    
    function testContactEntityTagAdd( ) 
    {
        $ContactId = $this->_individualID; 
        $tagID = $this->_tagID ;  
        $params = array(
                        'contact_id' =>  $ContactId,
                        'tag_id'     =>  $tagID);
        
        $individualEntity = civicrm_entity_tag_add( $params ); 
        $this->assertEquals( $individualEntity['is_error'], 0 );
        $this->assertEquals( $individualEntity['added'], 1 );
    }
    
    function testAddDouble( ) 
    {
        $individualId   = $this->_individualID;
        $organizationId = $this->_organizationID;
        $tagID = $this->_tagID;
        $params = array(
                        'contact_id' =>  $individualId,
                        'tag_id'     =>  $tagID
                        );
        
        $result = civicrm_entity_tag_add( $params );
        
        $this->assertEquals( $result['is_error'], 0 );
        $this->assertEquals( $result['added'],    1 );
                
        $params = array(
                        'contact_id_i' => $individualId,
                        'contact_id_o' => $organizationId,
                        'tag_id'       => $tagID
                        );
        
        $result = civicrm_entity_tag_add( $params );
        $this->assertEquals( $result['is_error'],  0 );
        $this->assertEquals( $result['added'],     1 );
        $this->assertEquals( $result['not_added'], 1 );
    }


///////////////// civicrm_entity_tag_get methods

    function testGetWrongParamsType()
    {
        $ContactId = $this->_individualID;
        $tagID     = $this->_tagID;
        $params    = array(
                           'contact_id' =>  $ContactId,
                           'tag_id'     =>  $tagID );
        
        $individualEntity = civicrm_entity_tag_add( $params ); 
        $this->assertEquals( $individualEntity['is_error'], 0 );
        $this->assertEquals( $individualEntity['added'], 1 );
        
        $paramsEntity = "wrong params";
        $entity = civicrm_entity_tag_get( $paramsEntity );
        
        $this->assertEquals( $entity['is_error'], 1,
                             "In line " . __LINE__  );
        $this->assertEquals( $entity['error_message'], 'contact_id is a required field' );
    }

    function testIndividualEntityTagGetWithoutContactID( )
    {
        $paramsEntity = array( );
        $entity       =& civicrm_entity_tag_get( $paramsEntity ); 
        $this->assertEquals( $entity['is_error'], 1 );
        $this->assertNotNull( $entity['error_message'] );
        $this->assertEquals( $entity['error_message'], 'contact_id is a required field' );
    }
    
    function testIndividualEntityTagGet()
    {
        $ContactId = $this->_individualID;
        $tagID     = $this->_tagID;
        $params    = array(
                           'contact_id' =>  $ContactId,
                           'tag_id'     =>  $tagID );
        
        $individualEntity = civicrm_entity_tag_add( $params ); 
        $this->assertEquals( $individualEntity['is_error'], 0 );
        $this->assertEquals( $individualEntity['added'], 1 );
        
        $paramsEntity = array('contact_id' =>  $ContactId );
        $entity =& civicrm_entity_tag_get( $paramsEntity );
    }
    
    function testHouseholdEntityGetWithoutContactID( )
    {
        $paramsEntity = array( );
        $entity       =& civicrm_entity_tag_get( $paramsEntity );
        $this->assertEquals( $entity['is_error'], 1 );
        $this->assertNotNull( $entity['error_message'] );
    }

    function testHouseholdEntityGet( )
    {
        $ContactId = $this->_householdID;
        $tagID     = $this->_tagID;
        $params    = array(
                           'contact_id' =>  $ContactId,
                           'tag_id'     =>  $tagID );
        
        $householdEntity = civicrm_entity_tag_add( $params ); 
        $this->assertEquals( $householdEntity['is_error'], 0 );
        $this->assertEquals( $householdEntity['added'], 1 );
        
        $paramsEntity = array('contact_id' => $ContactId ); 
        $entity =& civicrm_entity_tag_get( $paramsEntity );
    }
    
    function testOrganizationEntityGetWithoutContactID()
    {
        $paramsEntity = array( );
        $entity =& civicrm_entity_tag_get( $paramsEntity ); 
        $this->assertEquals( $entity['is_error'], 1 );
        $this->assertNotNull( $entity['error_message'] );
    }

    function testOrganizationEntityGet( )
    {
        $ContactId = $this->_organizationID;
        $tagID     = $this->_tagID;
        $params    = array(
                           'contact_id' =>  $ContactId,
                           'tag_id'     =>  $tagID );
        
        $organizationEntity = civicrm_entity_tag_add( $params ); 
        $this->assertEquals( $organizationEntity['is_error'], 0 );
        $this->assertEquals( $organizationEntity['added'], 1 );
        
        $paramsEntity = array('contact_id' => $ContactId );
        $entity =& civicrm_entity_tag_get( $paramsEntity ); 
    }


///////////////// civicrm_entity_tag_remove methods

    function testEntityTagRemoveNoContactId( )
    {
        $entityTagParams = array(
                                 'contact_id_i' => $this->_individualID,
                                 'contact_id_h' => $this->_householdID,
                                 'tag_id'       => $this->_tagID
                                 );
        $this->entityTagAdd( $entityTagParams );
        
        $params = array(
                        'tag_id' => $this->_tagID
                        );
                
        $result = civicrm_entity_tag_remove( $params );
        $this->assertEquals( $result['is_error'], 1 );
        $this->assertEquals( $result['error_message'], 'contact_id is a required field' );
    }
    
    function testEntityTagRemoveNoTagId( )
    {
        $entityTagParams = array(
                                 'contact_id_i' => $this->_individualID,
                                 'contact_id_h' => $this->_householdID,
                                 'tag_id'       => $this->_tagID
                                 );
        $this->entityTagAdd( $entityTagParams );
        
        $params = array(
                        'contact_id_i' => $this->_individualID,
                        'contact_id_h' => $this->_householdID,
                        );
                
        $result = civicrm_entity_tag_remove( $params );
        $this->assertEquals( $result['is_error'], 1 );
        $this->assertEquals( $result['error_message'], 'tag_id is a required field' );
    }
    
    function testEntityTagRemoveINDHH( )
    {
        $entityTagParams = array(
                                 'contact_id_i' => $this->_individualID,
                                 'contact_id_h' => $this->_householdID,
                                 'tag_id'       => $this->_tagID
                                 );
        $this->entityTagAdd( $entityTagParams );
        
        $params = array(
                        'contact_id_i' => $this->_individualID,
                        'contact_id_h' => $this->_householdID,
                        'tag_id'       => $this->_tagID
                        );
        
        $result = civicrm_entity_tag_remove( $params );
        
        $this->assertEquals( $result['is_error'], 0 );
        $this->assertEquals( $result['removed'], 2 );
    }    
    
    function testEntityTagRemoveHH( )
    {
        $entityTagParams = array(
                                 'contact_id_i' => $this->_individualID,
                                 'contact_id_h' => $this->_householdID,
                                 'tag_id'       => $this->_tagID
                                 );
        $this->entityTagAdd( $entityTagParams );
        
        $params = array(
                        'contact_id_h' => $this->_householdID,
                        'tag_id'       => $this->_tagID
                        );
                
        $result = civicrm_entity_tag_remove( $params );
        $this->assertEquals( $result['removed'], 1 );
    }
    
    function testEntityTagRemoveHHORG( )
    {
        $entityTagParams = array(
                                 'contact_id_i' => $this->_individualID,
                                 'contact_id_h' => $this->_householdID,
                                 'tag_id'       => $this->_tagID
                                 );
        $this->entityTagAdd( $entityTagParams );
        
        $params = array(
                        'contact_id_h' => $this->_householdID,
                        'contact_id_o' => $this->_organizationID,
                        'tag_id'       => $this->_tagID
                        );
                
        $result = civicrm_entity_tag_remove( $params );
        $this->assertEquals( $result['removed'], 1 );
        $this->assertEquals( $result['not_removed'], 1 );
    }


///////////////// civicrm_entity_tag_display methods

    function testEntityTagDisplayNoContactId( )
    {
        $entityTagParams = array(
                                 'contact_id' => $this->_individualID,
                                 'tag_id'     => $this->_tagID
                                 );
        $this->entityTagAdd( $entityTagParams );
        
        $params = array(
                        'tag_id' => $this->_tagID
                        );
        
        $result = civicrm_entity_tag_display( $params );
        $this->assertEquals( $result['is_error'], 1 );
        $this->assertEquals( $result['error_message'], 'contact_id is a required field' );
    }

    function testEntityTagDisplayWithContactId( )
    {
        $entityTagParams = array(
                                 'contact_id' => $this->_individualID,
                                 'tag_id'     => $this->_tagID
                                 );
        $this->entityTagAdd( $entityTagParams );
        
        $params = array(
                        'contact_id' => $this->_individualID
                        );
        
        $result = civicrm_entity_tag_display( $params );
    }
///////////////// civicrm_tag_entities_get methods

///////////////// civicrm_entity_tag_common methods
    
}



