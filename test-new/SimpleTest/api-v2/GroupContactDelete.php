<?php

require_once 'api/v2/GroupContact.php';

class TestOfGroupContactDeleteAPIV2 extends CiviUnitTestCase 
{
    function setUp() 
    {
        $this->_contactId = $this->individualCreate();
        $this->contactGroupCreate( $this->_contactId );
    }
    
    function tearDown() 
    { 
        $this->contactGroupDelete( $this->_contactId );
        $this->contactDelete($this->_contactId);
             
    }
    
    function testDeleteGroupContactsWithEmptyParams( ) 
    {
        $params = array( );
        $groups = civicrm_group_contact_remove( $params );
       
        $this->assertEqual( $groups['is_error'], 1 );
        $this->assertEqual( $groups['error_message'], 'contact_id is a required field' );
    }

    function testDeleteGroupContactsWithoutGroupIdParams( ) 
    {
        $params = array( );
        $params = array(
                        'contact_id.1' => $this->_contactId,
                        );
        
        $groups = civicrm_group_contact_remove( $params );
              
        $this->assertEqual( $groups['is_error'], 1 );
        $this->assertEqual( $groups['error_message'], 'group_id is a required field' );
    }
    
    
    function testDeleteGroupContacts( ) 
    {
        $params = array(
                        'contact_id.1' => $this->_contactId,
                        'group_id'     => 1 );
        
        
        $groups = civicrm_group_contact_remove( $params );
             
        $this->assertEqual( $groups['is_error'], 0 );
        $this->assertEqual( $groups['not_added'], 0 );
        $this->assertEqual( $groups['removed'], 1 );
        $this->assertEqual( $groups['total_count'], 1 );

    }

  
}


