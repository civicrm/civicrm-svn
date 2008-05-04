<?php

require_once 'api/v2/EntityTag.php';

class TestOfEntityTagAdd extends CiviUnitTestCase 
{
    protected $_individualID;

    protected $_householdID;

    protected $_organizationID;

    protected $_tagID;
    
    function setup( ) 
    {
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

    function testIndividualEntityTagAddEmptyParams( ) 
    {
        $params = array( );                             
        $individualEntity = civicrm_entity_tag_add( $params ); 
        $this->assertEqual( $individualEntity['is_error'], 1 ); 
        $this->assertEqual( $individualEntity['error_message'], 'contact_id is a required field' );
       
    }
    
    function testIndividualEntityTagAddWithoutTagID( ) 
    {
        $ContactId =  $this->_individualID;
        $params = array('contact_id' =>  $ContactId);              
        $individualEntity = civicrm_entity_tag_add( $params ); 
        $this->assertEqual( $individualEntity['is_error'], 1 );
        $this->assertEqual( $individualEntity['error_message'], 'tag_id is a required field' );
    }
    
    function testIndividualEntityTagAdd( ) 
    {
        $ContactId = $this->_individualID; 
        $tagID = $this->_tagID ;  
        $params = array(
                        'contact_id' =>  $ContactId,
                        'tag_id'     =>  $tagID);
        
        $individualEntity = civicrm_entity_tag_add( $params ); 
        $this->assertEqual( $individualEntity['is_error'], 0 );
        $this->assertEqual( $individualEntity['added'], 1 );
    }
    
    function testHouseholdEntityTagAddEmptyParams( ) 
    {
        $params = array( );
        $householdEntity = civicrm_entity_tag_add( $params ); 
        $this->assertEqual( $householdEntity['is_error'], 1 );
        $this->assertEqual( $householdEntity['error_message'], 'contact_id is a required field' );
    }
    
    function testHouseholdEntityTagAddWithoutTagID( ) 
    {
        $ContactId = $_householdID;
        $params = array('contact_id' =>  $ContactId);
        $householdEntity = civicrm_entity_tag_add( $params ); 
        $this->assertEqual( $householdEntity['is_error'], 1 );
        $this->assertEqual( $householdEntity['error_message'], 'tag_id is a required field' );
        
    }
    
    function testHouseholdEntityTagAdd( ) 
    {
        $ContactId = $this->_householdID;
        $tagID = $this->_tagID;
        $params = array(
                        'contact_id' =>  $ContactId,
                        'tag_id'     =>  $tagID );
                               
        $householdEntity = civicrm_entity_tag_add( $params ); 
        $this->assertEqual( $householdEntity['is_error'], 0 );
        $this->assertEqual( $householdEntity['added'], 1 );
    }
    
    function testOrganizationEntityTagAddEmptyParams( ) 
    {
        $params = array( );
        $organizationEntity = civicrm_entity_tag_add( $params ); 
        $this->assertEqual( $organizationEntity['is_error'], 1 );
        $this->assertEqual( $organizationEntity['error_message'], 'contact_id is a required field' );
    }
    
    function testOrganizationEntityTagAddWithoutTagID( ) 
    {
        $ContactId = $this->_organizationID;
        $params = array('contact_id' =>  $ContactId);
        $organizationEntity = civicrm_entity_tag_add( $params ); 
        $this->assertEqual( $organizationEntity['is_error'], 1 );
        $this->assertEqual( $organizationEntity['error_message'], 'tag_id is a required field' );
    }
        
    function testOrganizationEntityTagAdd( ) 
    {
        $ContactId = $this->_organizationID;
        $tagID = $this->_tagID;
        $params = array(
                        'contact_id' =>  $ContactId,
                        'tag_id'     =>  $tagID );
        
        $organizationEntity = civicrm_entity_tag_add( $params ); 
        $this->assertEqual( $organizationEntity['is_error'], 0 );
        $this->assertEqual( $organizationEntity['added'], 1 );
    }
    
    function testEntityTagAddIndividualDouble( ) 
    {
        $individualId   = $this->_individualID;
        $organizationId = $this->_organizationID;
        $tagID = $this->_tagID;
        $params = array(
                        'contact_id' =>  $individualId,
                        'tag_id'     =>  $tagID
                        );
        
        $result = civicrm_entity_tag_add( $params );
        
        $this->assertEqual( $result['is_error'], 0 );
        $this->assertEqual( $result['added'],    1 );
                
        $params = array(
                        'contact_id_i' => $individualId,
                        'contact_id_o' => $organizationId,
                        'tag_id'       => $tagID
                        );
        
        $result = civicrm_entity_tag_add( $params );
        $this->assertEqual( $result['is_error'],  0 );
        $this->assertEqual( $result['added'],     1 );
        $this->assertEqual( $result['not_added'], 1 );
    }
    
}



