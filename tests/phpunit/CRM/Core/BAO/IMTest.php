<?php

require_once 'CiviTestCase.php';
require_once 'Contact.php';

class BAO_Core_IM extends CiviTestCase 
{
    function get_info( ) 
    {
        return array(
                     'name'        => 'IM BAOs',
                     'description' => 'Test all Core_BAO_IM methods.',
                     'group'       => 'CiviCRM BAO Tests',
                     );
    }
    
    function setUp( ) 
    {
        parent::setUp();
    }
    
    /**
     * add() method (create and update modes)
     */
    function testAdd( )
    {
        $contactId = Contact::createIndividual( );

        $params = array( );
        $params = array( 'name'             => 'jane.doe',
                         'provider_id'	    => 1,
                         'is_primary'       => 1,
                         'location_type_id' => 1,
                         'contact_id'       => $contactId );
        
        require_once 'CRM/Core/BAO/IM.php';
        CRM_Core_BAO_IM::add( $params );
        
        $imId = $this->assertDBNotNull( 'CRM_Core_DAO_IM', 'jane.doe' , 'id', 'name',
                                           'Database check for created IM name.' );
	
        // Now call add() to modify an existing IM

        $params = array( );
        $params = array( 'id'           => $imId,
                         'contact_id'   => $contactId,
                         'provider_id'	=> 3,
                         'name'	        => 'doe.jane' );
        
        CRM_Core_BAO_IM::add( $params );
        
        $isEditIM = $this->assertDBNotNull( 'CRM_Core_DAO_IM',$imId,'provider_id','id','Database check on updated IM provider_name record.' );
        $this->assertEqual( $isEditIM, 3, 'Verify IM provider_id value is 3.');
        $isEditIM = $this->assertDBNotNull( 'CRM_Core_DAO_IM',$imId,'name','id','Database check on updated IM name record.' );
        $this->assertEqual( $isEditIM, 'doe.jane', 'Verify IM provider_id value is doe.jane.');

        Contact::delete( $contactId );
    }

     /**
     * AllIMs() method - get all IMs for our contact, with primary IM first
     */
    function testAllIMs( )
    {
        $contactParams = array ( 'first_name'      => 'Alan',
                                 'last_name'       => 'Smith',
                                 'im-1'            => 'alan1.smith1',
                                 'im-2'            => 'alan2.smith2',
                                 'im-3'            => 'alan3.smith3',
                                 'im-1-provider_id'=> 1,
                                 'im-2-provider_id'=> 2,
                                 'im-3-provider_id'=> 3
                                 );

        $contactId = Contact::createIndividual( $contactParams );
       
        require_once 'CRM/Core/BAO/IM.php';
        $IMs = CRM_Core_BAO_IM::allIMs( $contactId );

        $this->assertEqual( count( $IMs ) , 3, 'Checking number of returned IMs.' );
        
        $firstIMValue = array_slice( $IMs, 0, 1 );
                
        $this->assertEqual( 'alan1.smith1',  $firstIMValue[0]['name'], 'Confirm primary IM value.' ); 
        $this->assertEqual( 1,  $firstIMValue[0]['is_primary'], 'Confirm first IM is primary.' ); 
        
        Contact::delete( $contactId );
    }
 
}
?>