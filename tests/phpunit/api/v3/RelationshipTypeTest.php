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

require_once 'api/v3/Relationship.php';
require_once 'api/v3/RelationshipType.php';
require_once 'CiviTest/CiviUnitTestCase.php';

/**
 * Class contains api test cases for "civicrm_relationship_type"
 *
 */
class api_v3_RelationshipTypeTest extends CiviUnitTestCase 
{
    protected $_cId_a;
    protected $_cId_b;
    protected $_relTypeID;
    protected $_apiversion;
    
    function get_info( )
    {
        return array(
                     'name'        => 'RelationshipType Create',
                     'description' => 'Test all RelationshipType Create API methods.',
                     'group'       => 'CiviCRM API Tests',
                     );
    }
    
    function setUp( ) 
    { 

        parent::setUp();
        $this->_apiversion = 3;
        $this->_cId_a  = $this->individualCreate(null);
        $this->_cId_b  = $this->organizationCreate( null);
        
    }
    
    function tearDown( ) 
    {
        $this->contactDelete( $this->_cId_a );
        $this->contactDelete( $this->_cId_b);
    }

///////////////// civicrm_relationship_type_add methods
    
    /**
     * check with empty array
     */    
    function testRelationshipTypeCreateEmpty( )
    {
        $params = array( );        
        $result =& civicrm_api3_relationship_type_create( $params );
        
        $this->assertEquals( $result['is_error'], 1 );
        $this->assertEquals( $result['error_message'], 'Mandatory key(s) missing from params array: contact_type_a, contact_type_b, name_a_b, name_b_a, version' );
    }
    
    /**
     * check with No array
     */
    function testRelationshipTypeCreateParamsNotArray( )
    {
        $params = 'name_a_b = Employee of';   
        $result =& civicrm_api3_relationship_type_create( $params );                  
        
        $this->assertEquals( $result['is_error'], 1 );
        $this->assertEquals( $result['error_message'], 'Input variable `params` is not an array' );
    }
    
    /**
     * check with no name
     */
    function testRelationshipTypeCreateWithoutName( )
    {
        $relTypeParams = array(
                               'contact_type_a' => 'Individual',
                               'contact_type_b' => 'Organization',
                               'version'				=> $this->_apiversion,
                               );
        $result =& civicrm_api3_relationship_type_create( $relTypeParams );
        
        $this->assertEquals( $result['is_error'], 1 );
        $this->assertEquals( $result['error_message'], 
                             'Mandatory key(s) missing from params array: name_a_b, name_b_a' );
    }
    
    /**
     * check with no contact type
     */
    function testRelationshipTypeCreateWithoutContactType( )
    {
        $relTypeParams = array(
                               'name_a_b' => 'Relation 1 without contact type',
                               'name_b_a' => 'Relation 2 without contact type',
                               'version'  =>$this->_apiversion,
                               );
        $result = & civicrm_api3_relationship_type_create( $relTypeParams ); 
        
        $this->assertEquals( $result['is_error'], 1 );
        $this->assertEquals( $result['error_message'], 
                             'Mandatory key(s) missing from params array: contact_type_a, contact_type_b' );
    }
    
    /**
     * create relationship type
     */
    function testRelationshipTypeCreate( )
    {
        $params = array(
                               'name_a_b'       => 'Relation 1 for relationship type create',
                               'name_b_a'       => 'Relation 2 for relationship type create',
                               'contact_type_a' => 'Individual',
                               'contact_type_b' => 'Organization',
                               'is_reserved'    => 1,
                               'is_active'      => 1,
                               'version' 				=> $this->_apiversion,
                               'sequential' =>    1,
                               );
        $result = civicrm_api3_relationship_type_create( $params  );
        $this->documentMe($params,$result,__FUNCTION__,__FILE__); 
        $this->assertNotNull( $result['values'][0]['id'], 'in line ' . __LINE__ );   
        unset($params['version']);
        unset($params['sequential']);
        //assertDBState compares expected values in $result to actual values in the DB          
        $this->assertDBState( 'CRM_Contact_DAO_RelationshipType', $result['id'],  $params); 

    }
    /**
     *  Test  using example code
     */
  
    function testRelationshipTypeCreateExample( )
    {
      require_once 'api/v3/examples/RelationshipTypeCreate.php';
      $result = relationship_type_create_example();
      $expectedResult = relationship_type_create_expectedresult();
      $this->assertEquals($result,$expectedResult);
    }

///////////////// civicrm_relationship_type_delete methods
    
    /**
     * check with empty array
     */
    function testRelationshipTypeDeleteEmpty( )
    {
        $params = array( );
        $result =& civicrm_api3_relationship_type_delete( $params );
        
        $this->assertEquals( $result['is_error'], 1 );
    }
    
    /**
     * check with No array
     */
    
    function testRelationshipTypeDeleteParamsNotArray( )
    {
        $params = 'name_a_b = Test1';                            
        $result =& civicrm_api3_relationship_type_delete( $params );
        
        $this->assertEquals( $result['is_error'], 1 );
    }
    
    /**
     * check if required fields are not passed
     */
    function testRelationshipTypeDeleteWithoutRequired( )
    {
        $params = array(
                        'name_b_a'       => 'Relation 2 delete without required',
                        'contact_type_b' => 'Individual',
                        'is_reserved'    => 0,
                        'is_active'      => 0
                        );
        
        $result =& civicrm_api3_relationship_type_delete( $params );
        
        $this->assertEquals( $result['is_error'], 1 );
        $this->assertEquals( $result['error_message'], 'Mandatory key(s) missing from params array: id, version' );
    }
    
    /**
     * check with incorrect required fields
     */
    function testRelationshipTypeDeleteWithIncorrectData( )
    {
        $params = array(
                        'id'             => 'abcd',
                        'name_b_a'       => 'Relation 2 delete with incorrect',
                        'description'    => 'Testing relationship type',
                        'contact_type_a' => 'Individual',
                        'contact_type_b' => 'Individual',
                        'is_reserved'    => 0,
                        'is_active'      => 0,
                        'version'				 => $this->_apiversion,
                        );
        
        $result =& civicrm_api3_relationship_type_delete( $params );
        
        $this->assertEquals( $result['is_error'], 1 );
        $this->assertEquals( $result['error_message'], 'Invalid value for relationship type ID' );
    }
    
    /**
     * check relationship type delete
     */
    function testRelationshipTypeDelete( )
    {
      $rel = $this->_relationshipTypeCreate();
       // create sample relationship type.
        $params = array('id' => $rel,
                                'version'  =>$this->_apiversion,  
            
          );
        $result =  civicrm_api3_relationship_type_delete( $params );
        $this->documentMe($params,$result,__FUNCTION__,__FILE__);        
        $this->assertEquals( $result['is_error'], 0 );
    }

///////////////// civicrm_relationship_type_update
    
    /**
     * check with empty array
     */    
    function testRelationshipTypeUpdateEmpty( )
    {
        $params = array( );        
        $result =& civicrm_api3_relationship_type_create( $params );
        
        $this->assertEquals( $result['is_error'], 1 );
        $this->assertEquals( $result['error_message'], 'Mandatory key(s) missing from params array: contact_type_a, contact_type_b, name_a_b, name_b_a, version' );
    }
    
    /**
     * check with No array
     */
    function testRelationshipTypeUpdateParamsNotArray( )
    {
        $params = 'name_a_b = Relation 1';                            
        $result =& civicrm_api3_relationship_type_create( $params );
        
        $this->assertEquals( $result['is_error'], 1 );
        $this->assertEquals( $result['error_message'], 'Input variable `params` is not an array' );
    }
    
    /**
     * check with no contact type
     */
    function testRelationshipTypeUpdateWithoutContactType( )
    {
        // create sample relationship type.
        $this->_relTypeID = $this->_relationshipTypeCreate(null);
        
        $relTypeParams = array(
                               'id'             => $this->_relTypeID,
                               'name_a_b'       => 'Test 1',
                               'name_b_a'       => 'Test 2',
                               'description'    => 'Testing relationship type',
                               'is_reserved'    => 1,
                               'is_active'      => 0,
                               'version'				=> $this->_apiversion,
                               );

        $result =  civicrm_api3_relationship_type_create( $relTypeParams );  

        $this->assertNotNull( $result['id'] );   
        unset($relTypeParams['version']);
        // assertDBState compares expected values in $result to actual values in the DB          
        $this->assertDBState( 'CRM_Contact_DAO_RelationshipType', $result['id'],  $relTypeParams ); 
    }
    
    /**
     * check with all parameters
     */
    function testRelationshipTypeUpdate( )
    {
        // create sample relationship type.
        $this->_relTypeID = $this->_relationshipTypeCreate(null );
        
        $params = array(
                               'id'             => $this->_relTypeID,
                               'name_a_b'       => 'Test 1 for update',
                               'name_b_a'       => 'Test 2 for update',
                               'description'    => 'SUNIL PAWAR relationship type',
                               'contact_type_a' => 'Individual',
                               'contact_type_b' => 'Individual',
                               'is_reserved'    => 0,
                               'is_active'      => 0,
                               'version'  =>$this->_apiversion,
                               );
        
        $result = & civicrm_api3_relationship_type_create( $params);  
        $this->assertNotNull( $result['id'] );   
        unset($params['version']);
        // assertDBState compares expected values in $result to actual values in the DB          
        $this->assertDBState( 'CRM_Contact_DAO_RelationshipType', $result['id'],  $params ); 
    }

///////////////// civicrm_relationship_types_get methods
    
    /**
     * check with empty array
     */    
    function testRelationshipTypesGetEmptyParams( )
    {
        $firstRelTypeParams = array(
                                    'name_a_b'       => 'Relation 1 for create',
                                    'name_b_a'       => 'Relation 2 for create',
                                    'description'    => 'Testing relationship type',
                                    'contact_type_a' => 'Individual',
                                    'contact_type_b' => 'Organization',
                                    'is_reserved'    => 1,
                                    'is_active'      => 1,
                                    'version'				=> $this->_apiversion,
                                    );
        
        $secondRelTypeParams = array(
                                     'name_a_b'       => 'Relation 3 for create',
                                     'name_b_a'       => 'Relation 4 for create',
                                     'description'    => 'Testing relationship type second',
                                     'contact_type_a' => 'Individual',
                                     'contact_type_b' => 'Organization',
                                     'is_reserved'    => 0,
                                     'is_active'      => 1,
                                      'version'				=> $this->_apiversion,
                                
                  );

                                     
        $relTypeIds = array( );
        // create sample relationship types.
        foreach ( array( 'firstRelType', 'secondRelType' ) as $relType ) {
            $params = "{$relType}Params";
            $relTypeIds["{$relType}Id"] = $this->_relationshipTypeCreate( $$params );
        }
        
        //get relationship types from db.
        $params = array( 'version'				=> $this->_apiversion,
    );        
        $results =& civicrm_api3_relationship_type_get( $params );
        
        $retrievedRelTypes  = array( );
        if ( is_array( $results ) ) {
            foreach ( $results as $relTypeValues ) {
                if ( ( $relTypeId = CRM_Utils_Array::value( 'id', $relTypeValues ) ) 
                     && in_array( $relTypeId, $relTypeIds ) ) {
                    $retrievedRelTypes[$relTypeId] = $relTypeValues;
                }
            }
        }
        
        if ( count( $retrievedRelTypes ) < 2 ) {
            $this->fail( 'Failed to retrieve relationship types.' );  
        }
        
        foreach ( array( 'firstRelType', 'secondRelType' ) as $relType ) {
            $relTypeId     = $relTypeIds["{$relType}Id"];
            $relTypeparams = "{$relType}Params";
            foreach ( $$relTypeparams as $key => $val ) {
                $this->assertEquals( CRM_Utils_Array::value($key, $retrievedRelTypes[$relTypeId]), 
                                     $val, "Fail to retrieve {$key}" ); 
            }
        }        
    }
    
    /**
     * check with params Not Array.
     */
    function testRelationshipTypesGetParamsNotArray( )
    {
        $firstRelTypeParams = array(
                                    'name_a_b'       => 'Relation 1 for create',
                                    'name_b_a'       => 'Relation 2 for create',
                                    'description'    => 'Testing relationship type',
                                    'contact_type_a' => 'Individual',
                                    'contact_type_b' => 'Organization',
                                    'is_reserved'    => 1,
                                    'is_active'      => 1,
                                    'version'				=> $this->_apiversion,
   
                                    );
        
        $secondRelTypeParams = array(
                                     'name_a_b'       => 'Relation 3 for create',
                                     'name_b_a'       => 'Relation 4 for create',
                                     'description'    => 'Testing relationship type second',
                                     'contact_type_a' => 'Individual',
                                     'contact_type_b' => 'Organization',
                                     'is_reserved'    => 0,
                                     'is_active'      => 1,
                                     'version'				=> $this->_apiversion,
   
                                     );
        $relTypeIds = array( );
        // create sample relationship types.
        foreach ( array( 'firstRelType', 'secondRelType' ) as $relType ) {
            $params = "{$relType}Params";
            $relTypeIds["{$relType}Id"] = $this->_relationshipTypeCreate( $$params );
        }
        
        //get relationship types from db.
        $params = array('name_a_b' => 'Employee of',
                        'version'				=> $this->_apiversion,
   );        
        $results =civicrm_api3_relationship_type_get( $params );
        
        $retrievedRelTypes  = array( );
        if ( is_array( $results ) ) {
            foreach ( $results as $relTypeValues ) {
                if ( ( $relTypeId = CRM_Utils_Array::value( 'id', $relTypeValues ) ) 
                     && in_array( $relTypeId, $relTypeIds ) ) {
                    $retrievedRelTypes[$relTypeId] = $relTypeValues;
                }
            }
        }
        
        if ( count( $retrievedRelTypes ) < 2 ) {
            $this->fail( 'Fail to retrieve relationship types.' );  
        }
        
        foreach ( array( 'firstRelType', 'secondRelType' ) as $relType ) {
            $relTypeId     = $relTypeIds["{$relType}Id"];
            $relTypeparams = "{$relType}Params";
            foreach ( $$relTypeparams as $key => $val ) {
                $this->assertEquals( CRM_Utils_Array::value($key, $retrievedRelTypes[$relTypeId]), 
                                     $val, "Fail to retrieve {$key}" ); 
            }
        }        
    }
    
    /**
     * check with valid params array.
     */
    function testRelationshipTypesGet( )
    {
        $firstRelTypeParams = array(
                                    'name_a_b'       => 'Relation 1 for create',
                                    'name_b_a'       => 'Relation 2 for create',
                                    'description'    => 'Testing relationship type',
                                    'contact_type_a' => 'Individual',
                                    'contact_type_b' => 'Organization',
                                    'is_reserved'    => 1,
                                    'is_active'      => 1,
                                    'version'				=> $this->_apiversion,
   
                                    );
        
        $secondRelTypeParams = array(
                                     'name_a_b'       => 'Relation 3 for create',
                                     'name_b_a'       => 'Relation 4 for create',
                                     'description'    => 'Testing relationship type second',
                                     'contact_type_a' => 'Individual',
                                     'contact_type_b' => 'Organization',
                                     'is_reserved'    => 0,
                                     'is_active'      => 1,
                                     'version'				=> $this->_apiversion,
   
                                     );
        $relTypeIds = array( );
        // create sample relationship types.
        foreach ( array( 'firstRelType', 'secondRelType' ) as $relType ) {
            $params = "{$relType}Params";
            $relTypeIds["{$relType}Id"] = $this->_relationshipTypeCreate( $$params );
        }
        
        //get relationship types from db.
        $params = array( 'name_a_b' => 'Relation 3 for create', 
                         'name_b_a' => 'Relation 4 for create',
                         'description'    => 'Testing relationship type second'    ,     
                         'version'				=> $this->_apiversion,);
   
        $results =& civicrm_api3_relationship_type_get( $params );
        
        $retrievedRelTypes  = array( );
        if ( is_array( $results ) ) {
            foreach ( $results as $relTypeValues ) {
                if ( ( $relTypeId = CRM_Utils_Array::value( 'id', $relTypeValues ) ) 
                     && in_array( $relTypeId, $relTypeIds ) ) {
                    $retrievedRelTypes[$relTypeId] = $relTypeValues;
                }
            }
        }
        
        if ( count( $retrievedRelTypes ) != 1 ) {
            $this->fail( 'Fail to retrieve target relationship type.' );  
        }
        
        foreach ( $secondRelTypeParams as $key => $val ) {
            $this->assertEquals( CRM_Utils_Array::value( $key, $retrievedRelTypes[$relTypeIds['secondRelTypeId']]), 
                                 $val, "Fail to retrieve {$key}" ); 
        }
    }
    
    /**
     * create relationship type.
     */
    function _relationshipTypeCreate( $params = null )
    {
        if ( !is_array( $params ) || empty( $params ) ) {
            $params = array(
                            'name_a_b'       => 'Relation 1 for create',
                            'name_b_a'       => 'Relation 2 for create',
                            'description'    => 'Testing relationship type',
                            'contact_type_a' => 'Individual',
                            'contact_type_b' => 'Organization',
                            'is_reserved'    => 1,
                            'is_active'      => 1,
                            'version'				 => API_LATEST_VERSION,
                            );
        }

        return $this->relationshipTypeCreate( $params );
    }
}
 
?> 