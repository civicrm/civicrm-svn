<?php

require_once 'api/v2/EntityTag.php';

class TestOfEntityTagRemoveAPIV2 extends CiviUnitTestCase 
{
    public $individualID;
    public $householdID;
    public $tagID;
        
    function setUp( )
    {
        $this->individualID    = $this->individualCreate( );
        $this->householdID     = $this->householdCreate( );
        $this->organizationID  = $this->organizationCreate( );
        
        $this->tagID           = $this->tagCreate( );
    }
    
    function testEntityTagRemoveNoContactId( )
    {
        $entityTagParams = array(
                                 'contact_id_i' => $this->individualID,
                                 'contact_id_h' => $this->householdID,
                                 'tag_id'       => $this->tagID
                                 );
        $this->entityTagAdd( $entityTagParams );
        
        $params = array(
                        'tag_id' => $this->tagID
                        );
                
        $result = civicrm_entity_tag_remove( $params );
        $this->assertEqual( $result['is_error'], 1 );
        $this->assertEqual( $result['error_message'], 'contact_id is a required field' );
    }
    
    function testEntityTagRemoveNoTagId( )
    {
        $entityTagParams = array(
                                 'contact_id_i' => $this->individualID,
                                 'contact_id_h' => $this->householdID,
                                 'tag_id'       => $this->tagID
                                 );
        $this->entityTagAdd( $entityTagParams );
        
        $params = array(
                        'contact_id_i' => $this->individualID,
                        'contact_id_h' => $this->householdID,
                        );
                
        $result = civicrm_entity_tag_remove( $params );
        $this->assertEqual( $result['is_error'], 1 );
        $this->assertEqual( $result['error_message'], 'tag_id is a required field' );
    }
    
    function testEntityTagRemoveINDHH( )
    {
        $entityTagParams = array(
                                 'contact_id_i' => $this->individualID,
                                 'contact_id_h' => $this->householdID,
                                 'tag_id'       => $this->tagID
                                 );
        $this->entityTagAdd( $entityTagParams );
        
        $params = array(
                        'contact_id_i' => $this->individualID,
                        'contact_id_h' => $this->householdID,
                        'tag_id'       => $this->tagID
                        );
                
        $result = civicrm_entity_tag_remove( $params );
        $this->assertEqual( $result['is_error'], 0 );
        $this->assertEqual( $result['removed'], 2 );
    }    
    
    function testEntityTagRemoveHH( )
    {
        $entityTagParams = array(
                                 'contact_id_i' => $this->individualID,
                                 'contact_id_h' => $this->householdID,
                                 'tag_id'       => $this->tagID
                                 );
        $this->entityTagAdd( $entityTagParams );
        
        $params = array(
                        'contact_id_h' => $this->householdID,
                        'tag_id'       => $this->tagID
                        );
                
        $result = civicrm_entity_tag_remove( $params );
        $this->assertEqual( $result['removed'], 1 );
    }
    
    function testEntityTagRemoveHHORG( )
    {
        $entityTagParams = array(
                                 'contact_id_i' => $this->individualID,
                                 'contact_id_h' => $this->householdID,
                                 'tag_id'       => $this->tagID
                                 );
        $this->entityTagAdd( $entityTagParams );
        
        $params = array(
                        'contact_id_h' => $this->householdID,
                        'contact_id_o' => $this->organizationID,
                        'tag_id'       => $this->tagID
                        );
                
        $result = civicrm_entity_tag_remove( $params );
        $this->assertEqual( $result['removed'], 1 );
        $this->assertEqual( $result['not_removed'], 1 );
    }
    
    function tearDown( )
    {
        $this->contactDelete( $this->individualID );
        $this->contactDelete( $this->householdID  );
        $this->tagDelete(     $this->tagID        );
    }
}
?>