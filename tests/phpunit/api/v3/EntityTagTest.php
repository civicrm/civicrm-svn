<?php
/*
 +--------------------------------------------------------------------+
 | CiviCRM version 3.3                                                |
 +--------------------------------------------------------------------+
 | Copyright CiviCRM LLC (c) 2004-2010                                |
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

require_once 'api/v3/EntityTag.php';
require_once 'CiviTest/CiviUnitTestCase.php';

class api_v3_EntityTagTest extends CiviUnitTestCase 
{

    protected $_individualID;
    protected $_householdID;
    protected $_organizationID;
    protected $_tagID;
    protected $_apiversion;
       
    function setUp( ) 
    {
        parent::setUp();
        $this->_apiversion =3;
        //  Truncate the tables
        $op = new PHPUnit_Extensions_Database_Operation_Truncate( );
        $op->execute( $this->_dbconn,
                      new PHPUnit_Extensions_Database_DataSet_FlatXMLDataSet(
                             dirname(__FILE__) . '/../../CiviTest/truncate-tag.xml') );

        $this->_individualID = $this->individualCreate(null,3 );
        $this->_tag = $this->tagCreate(null,3 );
        $this->_tagID = $this->_tag['id'];
        $this->_householdID = $this->houseHoldCreate(null,3 );
        $this->_organizationID = $this->organizationCreate(null,3 );
    }
    
    function tearDown( ) 
    {
    }

    ///////////////// civicrm_entity_tag_create methods
    
    function testAddWrongParamsType()    
    {
        $params = "some string";                             
        $individualEntity = civicrm_entity_tag_create( $params ); 
        $this->assertEquals( $individualEntity['is_error'], 1,
                             "In line " . __LINE__  ); 
        $this->assertEquals( $individualEntity['error_message'], 'contact_id is a required field' );
    }

    function testAddEmptyParams( ) 
    {
        $params = array( );                             
        $individualEntity = civicrm_entity_tag_create( $params ); 
        $this->assertEquals( $individualEntity['is_error'], 1 ); 
        $this->assertEquals( $individualEntity['error_message'], 'contact_id is a required field' );
    }
    
    function testAddWithoutTagID( )
    {
        $params = array( 'contact_id' => $this->_individualID );              
        $individualEntity = civicrm_entity_tag_create( $params ); 
        $this->assertEquals( $individualEntity['is_error'], 1 );
        $this->assertEquals( $individualEntity['error_message'], 'tag_id is a required field' );
    }

    function testAddWithoutContactID()
    {
        $params = array('tag_id' => $this->_tagID);              
        $individualEntity = civicrm_entity_tag_create( $params ); 
        $this->assertEquals( $individualEntity['is_error'], 1 );
        $this->assertEquals( $individualEntity['error_message'], 'contact_id is a required field' );
    }
    
    function testContactEntityTagCreate( ) 
    {
        $params = array(
                        'contact_id' => $this->_individualID,
                        'tag_id'     => $this->_tagID);
        
        $result = civicrm_entity_tag_create( $params ); 
        $this->documentMe($params,$result,__FUNCTION__,__FILE__);     
 
        $this->assertEquals( $result['is_error'], 0 );
        $this->assertEquals( $result['added'], 1 );
    }
    
    function testAddDouble( ) 
    {
        $individualId   = $this->_individualID;
        $organizationId = $this->_organizationID;
        $tagID = $this->_tagID;
        $params = array(
                        'contact_id' => $individualId,
                        'tag_id'     => $tagID
                        );
        
        $result = civicrm_entity_tag_create( $params );
        
        $this->assertEquals( $result['is_error'], 0 );
        $this->assertEquals( $result['added'],    1 );
                
        $params = array(
                        'contact_id_i' => $individualId,
                        'contact_id_o' => $organizationId,
                        'tag_id'       => $tagID
                        );
        
        $result = civicrm_entity_tag_create( $params );
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
        
        $individualEntity = civicrm_entity_tag_create( $params ); 
        $this->assertEquals( $individualEntity['is_error'], 0 );
        $this->assertEquals( $individualEntity['added'], 1 );
        
        $paramsEntity = "wrong params";
        $entity = civicrm_entity_tag_get( $paramsEntity );
        
        $this->assertEquals( $entity['is_error'], 1,
                             "In line " . __LINE__  );
        $this->assertEquals( $entity['error_message'], 'Input variable `params` is not an array' );
    }

    function testIndividualEntityTagGetWithoutContactID( )
    {
        $paramsEntity = array( 'version' => $this->_apiversion);
        $entity       =& civicrm_entity_tag_get( $paramsEntity ); 
        $this->assertEquals( $entity['is_error'], 1 );
        $this->assertNotNull( $entity['error_message'] );
        $this->assertEquals( $entity['error_message'], 'entity_id is a required field.' );
    }
    
    function testIndividualEntityTagGet()
    {
        $contactId = $this->_individualID;
        $tagID     = $this->_tagID;
        $params    = array(
                           'contact_id' =>  $contactId,
                           'tag_id'     =>  $tagID );
        
        $individualEntity = civicrm_entity_tag_create( $params ); 
        $this->assertEquals( $individualEntity['is_error'], 0 );
        $this->assertEquals( $individualEntity['added'], 1 );
        
        $paramsEntity = array('contact_id' => $contactId );
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
        
        $householdEntity = civicrm_entity_tag_create( $params ); 
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
        
        $organizationEntity = civicrm_entity_tag_create( $params ); 
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
                
        $result = civicrm_entity_tag_delete( $params );
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
                
        $result = civicrm_entity_tag_delete( $params );
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
        
        $result = civicrm_entity_tag_delete( $params );
        
        $this->assertEquals( $result['is_error'], 0 );
        $this->assertEquals( $result['removed'], 2 );
    }    
    
    function testEntityTagDeleteHH( )
    {
        $entityTagParams = array(
                                 'contact_id_i' => $this->_individualID,
                                 'contact_id_h' => $this->_householdID,
                                 'tag_id'       => $this->_tagID,
                                 'version' 			=> $this->_apiversion,
                                 );
        $this->entityTagAdd( $entityTagParams );
        
        $params = array(
                        'contact_id_h' => $this->_householdID,
                        'tag_id'       => $this->_tagID,
                        'version' 			=> $this->_apiversion,
                        );
                
        $result = civicrm_entity_tag_delete( $params );
        $this->documentMe($params,$result,__FUNCTION__,__FILE__); 
        $this->assertEquals( $result['removed'], 1 );
    }
    
    function testEntityTagRemoveHHORG( )
    {
        $entityTagParams = array(
                                 'contact_id_i' => $this->_individualID,
                                 'contact_id_h' => $this->_householdID,
                                 'tag_id'       => $this->_tagID,
                                 'version' 			=> $this->_apiversion,
                                 );
        $this->entityTagAdd( $entityTagParams );
        
        $params = array(
                        'contact_id_h' => $this->_householdID,
                        'contact_id_o' => $this->_organizationID,
                        'tag_id'       => $this->_tagID,
                        'version' 			=> $this->_apiversion,
                        );
                
        $result = civicrm_entity_tag_delete( $params );
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
        $this->assertEquals( $result['error_message'], 'entity_id is a required field.' );
    }

    function testEntityTagDisplayWithContactId( )
    {
        $entityTagParams = array(
                                 'contact_id' => $this->_individualID,
                                 'tag_id'     => $this->_tagID,
                                 'version'		=> $this->_apiversion,
                                 );
        $this->entityTagAdd( $entityTagParams );
        
        $params = array(
                        'contact_id' => $this->_individualID,
                        'version'		=> $this->_apiversion,
                        );
        
        $result = civicrm_entity_tag_display( $params );
        
        $this->assertEquals( $this->_tag['name'], $result );
    }

    ///////////////// civicrm_tag_entities_get methods

    function testGetEntitiesWithoutParams()
    {
        $params    = array(
                           'contact_id' =>  $this->_individualID,
                           'tag_id'     =>  $this->_tagID,
                           'version'		=> $this->_apiversion, );
        
        $individualEntity = civicrm_entity_tag_create( $params ); 
        
        $paramsEntity = array( );
        $entity = civicrm_tag_entities_get( $paramsEntity );
        $this->assertNotNull( $entity );
        $this->assertArrayHasKey( 0, $entity);
    }

    ///////////////// civicrm_entity_tag_common methods

    function testCommonAddWrongParamsType()    
    {
        $params = "some string";                             
        $individualEntity = civicrm_entity_tag_common( $params, 'add' ); 
        $this->assertEquals( $individualEntity['is_error'], 1,
                             "In line " . __LINE__  ); 
        $this->assertEquals( $individualEntity['error_message'], 'contact_id is a required field' );
    }

    function testCommonAddEmptyParams( ) 
    {
        $params = array( );                             
        $individualEntity = civicrm_entity_tag_common( $params, 'add' ); 
        $this->assertEquals( $individualEntity['is_error'], 1 ); 
        $this->assertEquals( $individualEntity['error_message'], 'contact_id is a required field' );
    }
    
    function testCommonAddWithoutTagID( )
    {
        $params = array('contact_id' => $this->_individualID );              
        $individualEntity = civicrm_entity_tag_common( $params, 'add' ); 
        $this->assertEquals( $individualEntity['is_error'], 1 );
        $this->assertEquals( $individualEntity['error_message'], 'tag_id is a required field' );
    }

    function testCommonAddWithoutContactID()
    {
        $params = array('tag_id' => $this->_tagID);              
        $individualEntity = civicrm_entity_tag_common( $params, 'add' ); 
        $this->assertEquals( $individualEntity['is_error'], 1 );
        $this->assertEquals( $individualEntity['error_message'], 'contact_id is a required field' );
    }
    
    function testCommonContactEntityTagAdd( ) 
    {
        $params = array(
                        'contact_id' =>  $this->_individualID,
                        'tag_id'     =>  $this->_tagID);
        
        $individualEntity = civicrm_entity_tag_common( $params, 'add' ); 
        $this->assertEquals( $individualEntity['is_error'], 0 );
        $this->assertEquals( $individualEntity['added'], 1 );
    }

    function testEntityTagCommonRemoveNoContactId( )
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
                
        $result = civicrm_entity_tag_common( $params, 'remove' );
        $this->assertEquals( $result['is_error'], 1 );
        $this->assertEquals( $result['error_message'], 'contact_id is a required field' );
    }
    
    function testEntityTagCommonRemoveNoTagId( )
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
                
        $result = civicrm_entity_tag_common( $params, 'remove' );
        $this->assertEquals( $result['is_error'], 1 );
        $this->assertEquals( $result['error_message'], 'tag_id is a required field' );
    }
    
    function testEntityTagCommonRemoveINDHH( )
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
        
        $result = civicrm_entity_tag_common( $params, 'remove' );
        
        $this->assertEquals( $result['is_error'], 0 );
        $this->assertEquals( $result['removed'], 2 );
    }    
    
    function testEntityTagCommonRemoveHH( )
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
                
        $result = civicrm_entity_tag_common( $params, 'remove' );
        $this->assertEquals( $result['removed'], 1 );
    }
    
    function testEntityTagCommonRemoveHHORG( )
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
                
        $result = civicrm_entity_tag_common( $params, 'remove' );
        $this->assertEquals( $result['removed'], 1 );
        $this->assertEquals( $result['not_removed'], 1 );
    }    
}



