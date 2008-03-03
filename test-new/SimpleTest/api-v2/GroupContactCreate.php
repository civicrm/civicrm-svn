<?php

require_once 'api/v2/GroupContact.php';

class TestOfGroupContactCreateAPIV2 extends CiviUnitTestCase 
{
    function setUp() 
    {
        $this->_contactId = $this->individualCreate();
        
    }
    
    function tearDown() 
    {
        $this->contactGroupDelete( $this->_contactId );
        $this->contactDelete($this->_contactId);
        if (  $this->_contactId1 ){
            $this->contactDelete($this->_contactId1);
            $this->contactGroupDelete( $this->_contactId1 );
        }
            
    }
    
    function testCreateGroupContactsWithEmptyParams( ) 
    {
        $params = array( );
        $groups = civicrm_group_contact_add( $params );
        
        $this->assertEqual( $groups['is_error'], 1 );
        $this->assertEqual( $groups['error_message'], 'contact_id is a required field' );
    }

    function testCreateGroupContactsWithoutGroupIdParams( ) 
    {
        $params = array(
                        'contact_id.1' => $contactId,
                        );
        
        $groups = civicrm_group_contact_add( $params );
        
        $this->assertEqual( $groups['is_error'], 1 );
        $this->assertEqual( $groups['error_message'], 'group_id is a required field' );
    }
    
    
    function testCreateGroupContacts( ) 
    {
        $cont = array( 'first_name'       => 'Amiteshwar',
                       'middle_name'      => 'L.',
                       'last_name'        => 'Prasad',
                       'prefix_id'        => 3,
                       'suffix_id'        => 3,
                       'email'            => 'amiteshwar.prasad@civicrm.org',
                       'contact_type'     => 'Individual');
        
        $this->_contactId1 = $this->individualCreate( $cont );
        $params = array(
                        'contact_id.1' => $this->_contactId,
                        'contact_id.2' => $this->_contactId1,
                        'group_id'     => 1 );
        
        $groups = civicrm_group_contact_add( $params );
        
        $this->assertEqual( $groups['is_error'], 0 );
        $this->assertEqual( $groups['not_added'], 0 );
        $this->assertEqual( $groups['added'], 2 );
        $this->assertEqual( $groups['total_count'], 2 );
        
    }
    
  
}

?>
