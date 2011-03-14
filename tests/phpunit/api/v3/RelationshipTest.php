<?php
/*
 +--------------------------------------------------------------------+
 | CiviCRM version 4.0                                                |
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


require_once 'api/v3/Relationship.php';
require_once 'api/v3/CustomGroup.php';
require_once 'CiviTest/CiviUnitTestCase.php';
require_once ('api/api.php');

/**
 * Class contains api test cases for "civicrm_relationship"
 *
 */
class api_v3_RelationshipTest extends CiviUnitTestCase 
{
    protected $_apiversion;
    protected $_cId_a;
    protected $_cId_b;
    protected $_relTypeID;
    protected $_ids  = array( );
    protected $_customGroupId = null;
    protected $_customFieldId = null;
    
    function get_info( )
    {
        return array(
                     'name'        => 'Relationship Create',
                     'description' => 'Test all Relationship Create API methods.',
                     'group'       => 'CiviCRM API Tests',
                     );
    } 
    
    function setUp() 
    {
        parent::setUp();
        $this->_apiversion = 3;      
        $this->_cId_a  = $this->individualCreate(null);
        $this->_cId_b  = $this->organizationCreate(null );

        //Create a relationship type
        $relTypeParams = array(
                               'name_a_b'       => 'Relation 1 for delete',
                               'name_b_a'       => 'Relation 2 for delete',
                               'description'    => 'Testing relationship type',
                               'contact_type_a' => 'Individual',
                               'contact_type_b' => 'Organization',
                               'is_reserved'    => 1,
                               'is_active'      => 1,
                               'version'				=>$this->_apiversion,
                               );
        $this->_relTypeID = $this->relationshipTypeCreate($relTypeParams );        
    }

    function tearDown() 
    {
    }
    
///////////////// civicrm_relationship_create methods

    /**
     * check with empty array
     */
    function testRelationshipCreateEmpty( )
    {
        $params = array( 'version'  => $this->_apiversion);
        $result =& civicrm_api3_relationship_create( $params );
        $this->assertEquals( $result['is_error'], 1 );
        $this->assertEquals( $result['error_message'], 'Mandatory key(s) missing from params array: contact_id_a, contact_id_b, relationship_type_id, contact_id_a, contact_id_b, one of (relationship_type_id, relationship_type)' );
    }
    
    /**
     * check with No array
     */
    function testRelationshipCreateParamsNotArray( )
    {
        $params = 'relationship_type_id = 5';                            
        $result =& civicrm_api3_relationship_create( $params );
        $this->assertEquals( $result['is_error'], 1 );
        $this->assertEquals( $result['error_message'], 'Input variable `params` is not an array' );
    }
    
    /**
     * check if required fields are not passed
     */
    function testRelationshipCreateWithoutRequired( )
    {
        $params = array(
                        'start_date' => array('d'=>'10','M'=>'1','Y'=>'2008'),
                        'end_date'   => array('d'=>'10','M'=>'1','Y'=>'2009'),
                        'is_active'  => 1
                        );
        
        $result =& civicrm_api3_relationship_create($params);
        $this->assertEquals( $result['is_error'], 1 );
        $this->assertEquals( $result['error_message'], 'Mandatory key(s) missing from params array: contact_id_a, contact_id_b, relationship_type_id, contact_id_a, contact_id_b, one of (relationship_type_id, relationship_type), version' );
    }
    
    /**
     * check with incorrect required fields
     */
    function testRelationshipCreateWithIncorrectData( )
    {

        $params = array(
                        'contact_id_a'         => $this->_cId_a,
                        'contact_id_b'         => $this->_cId_b,
                        'relationship_type_id' => 'Breaking Relationship'
                        );

        $result =& civicrm_api3_relationship_create( $params );
        $this->assertEquals( $result['is_error'], 1 );

        //contact id is not an integer
        $params = array( 'contact_id_a'         => 'invalid',
                         'contact_id_b'         => $this->_cId_b,
                         'relationship_type_id' => $this->_relTypeID,
                         'start_date'           => array('d'=>'10','M'=>'1','Y'=>'2008'),
                         'is_active'            => 1
                         );
        $result =& civicrm_api3_relationship_create( $params );
        $this->assertEquals( $result['is_error'], 1 );

        //contact id does not exists
        $params['contact_id_a'] = 999;
        $result =& civicrm_api3_relationship_create( $params );
        $this->assertEquals( $result['is_error'], 1 );

        //invalid date
        $params['contact_id_a'] = $this->_cId_a;
        $params['start_date']   = array('d'=>'1','M'=>'1');
        $result =& civicrm_api3_relationship_create( $params );
        $this->assertEquals( $result['is_error'], 1 );
    }
   
    /**
     * check relationship creation with invalid Relationship 
     */
    function testRelationshipCreatInvalidRelationship( )
    {
        // both the contact of type Individual
        $params = array( 'contact_id_a'         => $this->_cId_a,
                         'contact_id_b'         => $this->_cId_a,
                         'relationship_type_id' => $this->_relTypeID,
                         'start_date'           => array('d'=>'10','M'=>'1','Y'=>'2008'),
                         'is_active'            => 1
                         );
        
        $result = & civicrm_api3_relationship_create( $params );
        $this->assertEquals( $result['is_error'], 1 );
        
        // both the contact of type Organization
        $params = array( 'contact_id_a'         => $this->_cId_b,
                         'contact_id_b'         => $this->_cId_b,
                         'relationship_type_id' => $this->_relTypeID,
                         'start_date'           => array('d'=>'10','M'=>'1','Y'=>'2008'),
                         'is_active'            => 1
                         );
        
        $result = & civicrm_api3_relationship_create( $params );
        $this->assertEquals( $result['is_error'], 1 );

    } 
    
    /**
     * check relationship already exists
     */
    function testRelationshipCreateAlreadyExists( )
    {
        $params = array( 'contact_id_a'         => $this->_cId_a,
                         'contact_id_b'         => $this->_cId_b,
                         'relationship_type_id' => $this->_relTypeID,
                         'start_date'           => '2008-12-20',                         'end_date'             => null,
                         'is_active'            => 1,
                         'version'							=> $this->_apiversion,
                         );
        $relationship = & civicrm_api3_relationship_create( $params );
        
        $params = array( 'contact_id_a'         => $this->_cId_a,
                         'contact_id_b'         => $this->_cId_b,
                         'relationship_type_id' => $this->_relTypeID,
                         'start_date'           => '2008-12-20',
                         'is_active'            => 1,
                         'version'							=> $this->_apiversion,
                         );
        $result = & civicrm_api3_relationship_create( $params );

        $this->assertEquals( $result['is_error'], 1 );
        $this->assertEquals( $result['error_message'], 'Relationship already exists' ); 
        
        $params['id'] = $relationship['result']['id'] ; 
        $result = & civicrm_api3_relationship_delete( $params );
    } 

    /**
     * check relationship creation
     */
    function testRelationshipCreate( )
    {
        $params = array( 'contact_id_a'         => $this->_cId_a,
                         'contact_id_b'         => $this->_cId_b,
                         'relationship_type_id' => $this->_relTypeID,
                         'start_date'           => '2010-10-30',
                         'end_date'             => '2010-12-30',
                         'is_active'            => 1,
                         'note'                 => 'note',
                         'version'							=> $this->_apiversion,
                         );
        
        $result = & civicrm_api3_relationship_create( $params );
        $this->documentMe($params,$result,__FUNCTION__,__FILE__); 
        $this->assertNotNull( $result['id'],'in line ' . __LINE__ );   
        
        $relationParams = array(
                                'id' => CRM_Utils_Array::value('id', $result['values'])
                                );
        // assertDBState compares expected values in $result to actual values in the DB          
        $this->assertDBState( 'CRM_Contact_DAO_Relationship', $result['values']['id'], $relationParams ); 
        
        $params['id'] = $result['values']['id'] ; 
        $result = & civicrm_api3_relationship_delete( $params );
    }
    
    /**
     * check relationship creation with custom data
     */
    function testRelationshipCreateWithCustomData( )
    {         
        $customGroup = $this->createCustomGroup( );
        $this->_customGroupId = $customGroup['id'];
        $this->_ids  = $this->createCustomField( );     
        //few custom Values for comparing
        $custom_params = array("custom_{$this->_ids[0]}" => 'Hello! this is custom data for relationship',
                               "custom_{$this->_ids[1]}" => 'Y',
                               "custom_{$this->_ids[2]}" => '2009-07-11 00:00:00',
                               "custom_{$this->_ids[3]}" => 'http://example.com',
                               );
        
        $params = array( 'contact_id_a'         => $this->_cId_a,
                         'contact_id_b'         => $this->_cId_b,
                         'relationship_type_id' => $this->_relTypeID,
                         'start_date'           => '2008-12-20',
                         'is_active'            => 1,
                          'version'							=> $this->_apiversion,
                         );
        $params = array_merge( $params, $custom_params );
        $result = & civicrm_api3_relationship_create( $params );
        
        $this->assertNotNull( $result['values']['id'] );   
        $relationParams = array(
                                'id' => CRM_Utils_Array::value('id', $result['values'])
                                );
        // assertDBState compares expected values in $result to actual values in the DB          
        $this->assertDBState( 'CRM_Contact_DAO_Relationship', $result['values']['id'], $relationParams ); 
        
        $params['id'] = $result['values']['id'] ; 
        $result = & civicrm_api3_relationship_delete( $params );
        $this->relationshipTypeDelete( $this->_relTypeID ); 
    }

    function createCustomGroup( )
    {
        $params = array(
                        'title'            => 'Test Custom Group',
                        'extends'          => array ( 'Relationship' ),
                        'weight'           => 5,
                        'style'            => 'Inline',
                        'is_active'        => 1,
                        'max_multiple'     => 0,
                        'version'							=> $this->_apiversion,
                        );
        $customGroup =& civicrm_api3_custom_group_create($params);
        return null;
    }

    function createCustomField( )
    {
        $ids = array( );
        $params = array(
                        'custom_group_id' => $this->_customGroupId,
                        'label'           => 'Enter text about relationship',
                        'html_type'       => 'Text',
                        'data_type'       => 'String',
                        'default_value'   => 'xyz',
                        'weight'          => 1,
                        'is_required'     => 1,
                        'is_searchable'   => 0,
                        'is_active'       => 1,
                        'version' => $this->_apiversion,
                         );
        

        $result = civicrm_api('CustomField','create',$params );
  
        $customField = null;
        $ids[] = $customField['result']['customFieldId'];
        
        $optionValue[] = array (
                                'label'     => 'Red',
                                'value'     => 'R',
                                'weight'    => 1,
                                'is_active' => 1
                                );
        $optionValue[] = array (
                                'label'     => 'Yellow',
                                'value'     => 'Y',
                                'weight'    => 2,
                                'is_active' => 1
                                );
        $optionValue[] = array (
                                'label'     => 'Green',
                                'value'     => 'G',
                                'weight'    => 3,
                                'is_active' => 1
                                );
        
        $params = array(
                        'label'           => 'Pick Color',
                        'html_type'       => 'Select',
                        'data_type'       => 'String',
                        'weight'          => 2,
                        'is_required'     => 1,
                        'is_searchable'   => 0,
                        'is_active'       => 1,
                        'option_values'   => $optionValue,
                        'custom_group_id' => $this->_customGroupId,
                        );
        
        $customField  =& civicrm_api3_custom_field_create( $params );
        
        $ids[] = $customField['result']['customFieldId'];
        
        $params = array(
                        'custom_group_id' => $this->_customGroupId,
                        'name'            => 'test_date',
                        'label'           => 'test_date',
                        'html_type'       => 'Select Date',
                        'data_type'       => 'Date',
                        'default_value'   => '20090711',
                        'weight'          => 3,
                        'is_required'     => 1,
                        'is_searchable'   => 0,
                        'is_active'       => 1
                        );
        
        $customField  =& civicrm_api3_custom_field_create( $params );			
        
        $ids[] = $customField['result']['customFieldId'];
        $params = array(
                        'custom_group_id' => $this->_customGroupId,
                        'name'            => 'test_link',
                        'label'           => 'test_link',
                        'html_type'       => 'Link',
                        'data_type'       => 'Link',
                        'default_value'   => 'http://civicrm.org',
                        'weight'          => 4,
                        'is_required'     => 1,
                        'is_searchable'   => 0,
                        'is_active'       => 1
                        );
        
        $customField  =& civicrm_api3_custom_field_create( $params );
        $ids[] = $customField['result']['customFieldId'];
        return $ids;
    }

///////////////// civicrm_relationship_delete methods

    /**
     * check with empty array
     */
    function testRelationshipDeleteEmpty( )
    {
        $params = array( );
        $result =& civicrm_api3_relationship_delete( $params );
        $this->assertEquals( $result['is_error'], 1 );
        $this->assertEquals( $result['error_message'], 'Mandatory key(s) missing from params array: id, version' );
    }
    
    /**
     * check with No array
     */
    
    function testRelationshipDeleteParamsNotArray( )
    {
        $params = 'relationship_type_id = 5';                            
        $result =& civicrm_api3_relationship_delete( $params );
        $this->assertEquals( $result['is_error'], 1 );
        $this->assertEquals( $result['error_message'], 'Input variable `params` is not an array' );
    }
    
    /**
     * check if required fields are not passed
     */
    function testRelationshipDeleteWithoutRequired( )
    {
        $params = array(
                         'start_date'           => '2008-12-20',
                         'end_date'           => '2009-12-20',
                        'is_active'  => 1
                        );
        
        $result =& civicrm_api3_relationship_delete( $params ); 
        $this->assertEquals( $result['is_error'], 1 );
        $this->assertEquals( $result['error_message'], 'Mandatory key(s) missing from params array: id, version' );
    }
    
    /**
     * check with incorrect required fields
     */
    function testRelationshipDeleteWithIncorrectData( )
    {
        $params = array(
                        'contact_id_a'         => $this->_cId_a,
                        'contact_id_b'         => $this->_cId_b,
                        'relationship_type_id' => 'Breaking Relationship',
                        'version'							 => $this->_apiversion,
                        );
        
        $result =& civicrm_api3_relationship_delete( $params );
        $this->assertEquals( $result['is_error'], 1,'in line ' . __LINE__  );
        $this->assertEquals( $result['error_message'], 'Mandatory key(s) missing from params array: id','in line ' . __LINE__ );

        $params['id'] = "Invalid";
        $result =& civicrm_api3_relationship_delete( $params );
        $this->assertEquals( $result['is_error'], 1,'in line ' . __LINE__  );
        $this->assertEquals( $result['error_message'], 'Invalid value for relationship ID','in line ' . __LINE__  ); 
    }

    /**
     * check relationship creation
     */
    function testRelationshipDelete( )
    {
        $params = array( 'contact_id_a'         => $this->_cId_a,
                         'contact_id_b'         => $this->_cId_b,
                         'relationship_type_id' => $this->_relTypeID,
                         'start_date'           => '2008-12-20',
                         'is_active'            => 1,
                         'version'							=> $this->_apiversion,
                         );
        
        $result = & civicrm_api3_relationship_create( $params );
        $this->documentMe($params,$result,__FUNCTION__,__FILE__); 
        $this->assertNotNull( $result['values']['id'] );

        //Delete relationship
        $params = array();
        $params['id']= $result['values']['id'];
        
        $result = & civicrm_api3_relationship_delete( $params );
        $this->relationshipTypeDelete( $this->_relTypeID ); 
    }
    
///////////////// civicrm_relationship_update methods

    /**
     * check with empty array
     */
    function testRelationshipUpdateEmpty( )
    {
        $params = array( );
        $result =& civicrm_api3_relationship_update( $params );
        $this->assertEquals( $result['is_error'], 1 );
        $this->assertEquals( 'Mandatory key(s) missing from params array: contact_id_a, contact_id_b, relationship_type_id, relationship_id, version', $result['error_message'], 'In line ' . __LINE__ );
    }
    
    /**
     * check with No array
     */
    function testRelationshipUpdateParamsNotArray( )
    {
        $params = 'relationship_type_id = 5';                            
        $result =& civicrm_api3_relationship_update( $params );
        $this->assertEquals( $result['is_error'], 1 );
        $this->assertEquals( 'Input variable `params` is not an array', $result['error_message'], 'In line ' . __LINE__ );
    }

    /**
     * check if required fields are not passed
     */
    function testRelationshipUpdateWithoutRequired( )
    {
        $params = array(
                        'contact_id_b'         => $this->_cId_b,
                        'relationship_type_id' => $this->_relTypeID,
                        'start_date' => array('d'=>'10','M'=>'1','Y'=>'2008'),
                        'end_date'   => array('d'=>'10','M'=>'1','Y'=>'2009'),
                        'is_active'  => 1,
                        'version'							=> $this->_apiversion,
                        );
        
        $result =& civicrm_api3_relationship_update( $params );
        $this->assertEquals( $result['is_error'], 1 );
        $this->assertEquals( 'Mandatory key(s) missing from params array: contact_id_a, relationship_id', $result['error_message'], 'In line ' . __LINE__ );
    }  
   
    /**
     * check relationship update
     */
    function testRelationshipUpdate( )
    {
        $relParams     = array(
                               'contact_id_a'         => $this->_cId_a,
                               'contact_id_b'         => $this->_cId_b,
                               'relationship_type_id' => $this->_relTypeID,
                               'start_date'           => '20081214',
                               'end_date'             => '20091214',
                               'is_active'            => 1,
                               'version'							=> $this->_apiversion,
                               );

        $result = & civicrm_api3_relationship_create( $relParams );

        $this->assertNotNull( $result['id'], 'In line ' . __LINE__ );  
        $this->_relationID = $result['id'];

        $params = array(
                        'relationship_id'      => $this->_relationID,
                        'contact_id_a'         => $this->_cId_a,
                        'contact_id_b'         => $this->_cId_b,
                        'relationship_type_id' => $this->_relTypeID,
                        'start_date'           => '20081214',
                        'end_date'             => '20091214',                       'is_active'            => 0,
                        'version'							 => $this->_apiversion,
                        );
        
        $result = & civicrm_api3_relationship_create( $params );
        
        $this->assertEquals( $result['is_error'], 1, 'In line ' . __LINE__  );
        $this->assertEquals( $result['error_message'], 'Relationship already exists', 'In line ' . __LINE__  );
        //delete created relationship
        $params = array('id'=>$this->_relationID,
                        'version'  => $this->_apiversion);
        
        $result = & civicrm_api3_relationship_delete( $params );
        $this->assertEquals( $result['is_error'], 0 ,'in line ' .__LINE__);
        
        //delete created relationship type        
        $this->relationshipTypeDelete( $this->_relTypeID ); 
    }


    ///////////////// civicrm_relationship_get methods
    
    /**
     * check with empty array
     */    
    function testRelationshipGetEmptyParams( )
    {
        //get relationship
        $params = array( );
        $result =& civicrm_api3_relationship_get( $params );
        $this->assertEquals( $result['is_error'], 1 );
        $this->assertEquals( $result['error_message'], 'Mandatory key(s) missing from params array: version' );
    }
    
    /**
     * check with params Not Array.
     */
    function testRelationshipGetParamsNotArray( )
    {
        $params = 'relationship';                            
        
        $result =& civicrm_api3_relationship_get( $params );
        $this->assertEquals( $result['is_error'], 1 );
        $this->assertEquals( $result['error_message'], 'Input variable `params` is not an array' );
    }
    
    /**
     * check with valid params array.
     */
    function testRelationshipsGet( )
    {
        $relParams = array(
                           'contact_id_a'         => $this->_cId_a,
                           'contact_id_b'         => $this->_cId_b,
                           'relationship_type_id' => $this->_relTypeID,
                           'start_date'           => array('d'=>'10','M'=>'1','Y'=>'2008'),
                           'end_date'             => array('d'=>'10','M'=>'1','Y'=>'2009'),
                           'is_active'            => 1,
                           'version'							=> $this->_apiversion,
                           );

        civicrm_api3_relationship_create( $relParams );
        
        //get relationship
        $params = array( 'contact_id' => $this->_cId_b ,
                          'version'   => $this->_apiversion);
        $result =& civicrm_api3_relationship_get( $params );
        $this->documentMe($params,$result,__FUNCTION__,__FILE__); 
        $this->assertEquals( $result['is_error'], 0,'in line ' .__LINE__ );
    }
    
   ///////////////// civicrm_relationship_type_add methods
    
   /**
    * check with invalid relationshipType Id
    */
    function testRelationshipTypeAddInvalidId( )
    {
        $relTypeParams = array(
                               'id'             => 'invalid',
                               'name_a_b'       => 'Relation 1 for delete',
                               'name_b_a'       => 'Relation 2 for delete',
                               'contact_type_a' => 'Individual',
                               'contact_type_b' => 'Organization',
                               'version'				=>$this->_apiversion,
                               );
        $result =& civicrm_api3_relationship_type_create( $relTypeParams );
        $this->assertEquals( $result['is_error'], 1 ,'in line ' .__LINE__);
        $this->assertEquals( $result['error_message'], 'Invalid value for relationship type ID', 'in line ' .__LINE__);
    } 

    ///////////////// civicrm_get_relationships
    
    /**
    * check with invalid data
    */
    function testGetRelationshipInvalidData( )
    {
        $contact_a = array( 'contact_id' => $this->_cId_a );
        $contact_b = array( 'contact_id' => $this->_cId_b );
        
        //no relationship has been created
        $result =& civicrm_api3_relationship_get( $contact_a, $contact_b, null , 'asc' );
        $this->assertEquals( $result['is_error'], 1 );
        $this->assertEquals( $result['error_message'], 'Invalid Data' );
    } 
    
    
    /**
     * check with valid data with contact_b
     */
    function testGetRelationshipWithContactB( )
    {
        $relParams = array(
                           'contact_id_a'         => $this->_cId_a,
                           'contact_id_b'         => $this->_cId_b,
                           'relationship_type_id' => $this->_relTypeID,
                           'start_date'           => array('d'=>'10','M'=>'1','Y'=>'2008'),
                           'end_date'             => array('d'=>'10','M'=>'1','Y'=>'2009'),
                           'is_active'            => 1,
                           'version'							=> $this->_apiversion,
                           );

        $relationship = & civicrm_api3_relationship_create( $relParams );
        
        $contacts = array( 'contact_id_a' => $this->_cId_a ,
      											'contact_id_b' => $this->_cId_b,
                            'version'			=> $this->_apiversion );

        $result =& civicrm_api3_relationship_get( $contacts );
        $this->assertEquals( $result['is_error'], 0 ,'in line ' .__LINE__);

        $params = array('id' => $relationship['id'] ,
                        'version' => $this->_apiversion,);
        $result = & civicrm_api3_relationship_delete( $params );
        $this->relationshipTypeDelete( $relTypeID );
    }

    /**
    * check with valid data with relationshipTypes
    */
    function testGetRelationshipWithRelTypes( )
    {
        $relParams = array(
                           'contact_id_a'         => $this->_cId_a,
                           'contact_id_b'         => $this->_cId_b,
                           'relationship_type_id' => $this->_relTypeID,
                           'start_date'           => array('d'=>'10','M'=>'1','Y'=>'2008'),
                           'end_date'             => array('d'=>'10','M'=>'1','Y'=>'2009'),
                           'is_active'            => 1,
                           'version'							=> $this->_apiversion,
                           );

        $relationship = & civicrm_api3_relationship_create( $relParams );
        
        $contact_a = array( 'contact_id_a' => $this->_cId_a,
                            'relationship_type_id' => $this->_relTypeID,
                            'version'      => $this->_apiversion, );

        $result =& civicrm_api3_relationship_get( $contact_a, null, $relationshipTypes, 'desc' );

        $this->assertEquals( $result['is_error'], 0,'in line ' .__LINE__ );

        $params = array('id' => $relationship['result']['id'],
                        'version'		=>$this->_apiversion,) ;
        $result = & civicrm_api3_relationship_delete( $params );
        $this->relationshipTypeDelete( $relTypeID );
    } 

}
 
?> 
