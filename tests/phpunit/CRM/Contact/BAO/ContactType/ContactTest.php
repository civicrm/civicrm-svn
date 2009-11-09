<?php

require_once 'CiviTest/CiviUnitTestCase.php';
require_once 'CRM/Contact/BAO/Contact.php';


class CRM_Contact_BAO_ContactType_ContactTest extends CiviUnitTestCase 
{
    
    function get_info( ) 
    {
        return array(
                     'name'        => 'Contact Subtype',
                     'description' => 'Test Contact for subtype.',
                     'group'       => 'CiviCRM BAO Tests',
                     );
    }

    /**
     * methods create Contact with valid data
     * success expected
     * 
     */
    function testCreateContact( ) {
        
        //check for Type:Individual
        $params = array( 'first_name'   => 'Anne',     
                         'last_name'    => 'Grant',
                         'contact_type' => 'Individual',
                         );
        $contact = CRM_Contact_BAO_Contact::add( $params );
        $this->assertEquals( $contact->first_name, 'Anne', 'In line '. __LINE__ );
        $this->assertEquals( $contact->contact_type, 'Individual', 'In line '. __LINE__ );
        CRM_Contact_BAO_Contact::deleteContact( $contact->id );

        //check for Type:Organization
        $params = array( 'organization_name' => 'Compumentor' ,     
                         'contact_type'      => 'Organization',
                         );
        $contact = CRM_Contact_BAO_Contact::add( $params );
        $this->assertEquals( $contact->organization_name, 'Compumentor', 'In line '. __LINE__ );
        $this->assertEquals( $contact->contact_type, 'Organization', 'In line '. __LINE__ );
        CRM_Contact_BAO_Contact::deleteContact( $contact->id );

        //check for Type:Household
        $params = array( 'household_name' => 'John Does home',
                         'contact_type'   => 'Household'
                         );
        $contact = CRM_Contact_BAO_Contact::add( $params );
        $this->assertEquals( $contact->household_name, 'John Does home', 'In line '. __LINE__ );
        $this->assertEquals( $contact->contact_type, 'Household', 'In line '. __LINE__ );
        CRM_Contact_BAO_Contact::deleteContact( $contact->id );

        //check for Type:Individual, Subtype:Student
        $params = array( 'first_name'       => 'Bill',     
                         'last_name'        => 'Adams',
                         'contact_type'     => 'Individual',
                         'contact_sub_type' => 'Student'
                         );
        $contact = CRM_Contact_BAO_Contact::add( $params );
        $this->assertEquals( $contact->first_name, 'Bill', 'In line '. __LINE__ );
        $this->assertEquals( $contact->contact_type, 'Individual', 'In line '. __LINE__ );
        $this->assertEquals( $contact->contact_sub_type, 'Student', 'In line '. __LINE__ );
        CRM_Contact_BAO_Contact::deleteContact( $contact->id );

        //check for Type:Organization, Subtype:Sponsor
        $params = array( 'organization_name' => 'Conservation Corp' ,     
                         'contact_type'      => 'Organization',
                         'contact_sub_type'  => 'Sponsor'
                         );
        $contact = CRM_Contact_BAO_Contact::add( $params );
        $this->assertEquals( $contact->organization_name, 'Conservation Corp', 'In line '. __LINE__ );
        $this->assertEquals( $contact->contact_type, 'Organization', 'In line '. __LINE__ );
        $this->assertEquals( $contact->contact_sub_type, 'Sponsor', 'In line '. __LINE__ );
        CRM_Contact_BAO_Contact::deleteContact( $contact->id );
        
    }
    
    /**
     * methods create Contacte with invalid Contact Subtype data
     * 
     */
    function testCreateContactInvalid( ) {
        
        //check for Type:Individual,Subtype:Sponsor
        $params = array( 'first_name'       => 'Anne',     
                         'last_name'        => 'Grant',
                         'contact_type'     => 'Individual',
                         'contact_sub_type' => 'Sponsor'
                         );
        $contact = CRM_Contact_BAO_Contact::add( $params );
        $this->assertNull( $contact , 'In line '. __LINE__ );
        
        //check for Type:null, Subtype:Student
        $params = array( 'first_name'       => 'Anne',     
                         'last_name'        => 'Grant',
                         'contact_sub_type' => 'Student'
                         ); 
        $contact = CRM_Contact_BAO_Contact::add( $params );
        $this->assertNull( $contact , 'In line '. __LINE__ );
        
        //check for Type:Individual, Subtype:Sponsor
        $params = array( 'organization_name' => 'Conservation Corp',
                         'contact_type'      => 'Individual',
                         'contact_sub_type'  => 'Student'
                         ); 
        $contact = CRM_Contact_BAO_Contact::add( $params );
        $this->assertNull( $contact , 'In line '. __LINE__ );

        //check for Type:Household, Subtype:Student
        $params = array( 'household_name'    => 'John Does home',
                         'contact_type'      => 'Household',
                         'contact_sub_type'  => 'Student'
                         );
        $contact = CRM_Contact_BAO_Contact::add( $params );
        $this->assertNull( $contact , 'In line '. __LINE__ );
    }

    /**
     * update the contact with no subtype to a valid subtype 
     * success expected
     */
    function testUpdateContactNosubtypeToValid( ) {
     
        $params     =  array( 'first_name'   => 'Anne',     
                              'last_name'    => 'Grant',
                              'contact_type' => 'Individual'
                              );
        $contact = CRM_Contact_BAO_Contact::add( $params );
        
        $updateParams = array( 'contact_sub_type'  => 'Student',
                               'contact_type'      => 'Individual',
                               'contact_id'        => $contact->id
                               );
        $updatedContact = CRM_Contact_BAO_Contact::add( $updateParams );

        $this->assertEquals( $updatedContact->id, $contact->id, 'In line '. __LINE__ );
        $this->assertEquals( $updatedContact ->contact_type, 'Individual', 'In line '. __LINE__ );
        $this->assertEquals( $updatedContact->contact_sub_type, 'Student', 'In line '. __LINE__ );
        CRM_Contact_BAO_Contact::deleteContact( $contact->id );
       

        $params = array( 'organization_name' => 'Compumentor' ,     
                         'contact_type'      => 'Organization' 
                         );
        $contact = CRM_Contact_BAO_Contact::add( $params );

        $updateParams = array( 'contact_sub_type'  => 'Sponsor',
                               'contact_type'      => 'Organization',
                               'contact_id'        => $contact->id
                               );
        $updatedContact = CRM_Contact_BAO_Contact::add( $updateParams );

        $this->assertEquals( $updatedContact->id, $contact->id, 'In line '. __LINE__ );
        $this->assertEquals( $updatedContact ->contact_type, 'Organization', 'In line '. __LINE__ );
        $this->assertEquals( $updatedContact->contact_sub_type, 'Sponsor', 'In line '. __LINE__ );
        CRM_Contact_BAO_Contact::deleteContact( $contact->id );
    }

    /**
     * update the contact with no subtype to a invalid subtype 
     */
    function testUpdateContactNosubtypeToInvalid( ) {
     
        $params  =  array( 'first_name'   => 'Anne',     
                           'last_name'    => 'Grant',
                           'contact_type' => 'Individual' 
                           );
        $contact = CRM_Contact_BAO_Contact::add( $params );
        
        $updateParams = array( 'contact_sub_type'  => 'Sponsor',
                               'contact_type'      => 'Individual',
                               'contact_id'        => $contact->id );
        $updatedContact = CRM_Contact_BAO_Contact::add( $updateParams );
 
        $this->assertNull( $updatedContact , 'In line '. __LINE__ );
        CRM_Contact_BAO_Contact::deleteContact( $contact->id );
       

        $params = array( 'organization_name' => 'Compumentor' ,     
                         'contact_type'      => 'Organization' 
                         );
        $contact = CRM_Contact_BAO_Contact::add( $params );

        $updateParams = array( 'contact_sub_type'  => 'Student',
                               'contact_type'      => 'Organization',
                               'contact_id'        => $contact->id );
        $updatedContact = CRM_Contact_BAO_Contact::add( $updateParams );

        $this->assertNull( $updatedContact , 'In line '. __LINE__ );
        CRM_Contact_BAO_Contact::deleteContact( $contact->id );

        $params = array( 'household_name' => 'John Does home',
                         'contact_type'   => 'Household'
                         );
        $contact = CRM_Contact_BAO_Contact::add( $params );
        $updateParams = array( 'contact_sub_type'  => 'Student',
                               'contact_type'      => 'Household',
                               'contact_id'        => $contact->id );
        $updatedContact = CRM_Contact_BAO_Contact::add( $updateParams );

        $this->assertNull( $updatedContact , 'In line '. __LINE__ );
        CRM_Contact_BAO_Contact::deleteContact( $contact->id );
    }
    
    /**
     * update the contact with subtype to another valid subtype 
     * success expected
     */
    function testUpdateContactSubtype( ) {
     
        $params  =  array( 'first_name'       => 'Anne',     
                           'last_name'        => 'Grant',
                           'contact_type'     => 'Individual',
                           'contact_sub_type' => 'Student'
                           );
        $contact = CRM_Contact_BAO_Contact::add( $params );
        
        $updateParams = array( 'contact_sub_type'  => 'Parent',
                               'contact_type'      => 'Individual',
                               'contact_id'        => $contact->id 
                               );
        $updatedContact = CRM_Contact_BAO_Contact::add( $updateParams );

        $this->assertEquals( $updatedContact->id, $contact->id, 'In line '. __LINE__ );
        $this->assertEquals( $updatedContact ->contact_type, 'Individual', 'In line '. __LINE__ );
        $this->assertEquals( $updatedContact->contact_sub_type, 'Parent', 'In line '. __LINE__ );
        CRM_Contact_BAO_Contact::deleteContact( $contact->id );
       

        $params = array( 'organization_name' => 'Compumentor' ,     
                         'contact_type'      => 'Organization',
                         'contact_sub_type'  => 'Sponsor'
                         );
        $contact = CRM_Contact_BAO_Contact::add( $params );

        $updateParams = array( 'contact_sub_type'  => 'Team',
                               'contact_type'      => 'Organization',
                               'contact_id'        => $contact->id 
                               );
        $updatedContact = CRM_Contact_BAO_Contact::add( $updateParams );

        $this->assertEquals( $updatedContact->id, $contact->id, 'In line '. __LINE__ );
        $this->assertEquals( $updatedContact ->contact_type, 'Organization', 'In line '. __LINE__ );
        $this->assertEquals( $updatedContact->contact_sub_type, 'Team', 'In line '. __LINE__ );
        CRM_Contact_BAO_Contact::deleteContact( $contact->id );


        $params  =  array( 'first_name'       => 'Anne',     
                           'last_name'        => 'Grant',
                           'contact_type'     => 'Individual',
                           'contact_sub_type' => 'Student'
                           );
        $contact = CRM_Contact_BAO_Contact::add( $params );
        
        $updateParams = array( 'contact_sub_type'  => null,
                               'contact_type'      => 'Individual',
                               'contact_id'        => $contact->id 
                               );
        $updatedContact = CRM_Contact_BAO_Contact::add( $updateParams );

        $this->assertEquals( $updatedContact->id, $contact->id, 'In line '. __LINE__ );
        $this->assertEquals( $updatedContact ->contact_type, 'Individual', 'In line '. __LINE__ );
        $this->assertEquals( $updatedContact->contact_sub_type, null, 'In line '. __LINE__ );
        CRM_Contact_BAO_Contact::deleteContact( $contact->id );

    }
    
    /**
     * update the contact with subtype to a invalid subtype 
     */
    function testUpdateContactSubtypeInvalid( ) {
     
        $params  =  array( 'first_name'       => 'Anne',     
                           'last_name'        => 'Grant',
                           'contact_type'     => 'Individual',
                           'contact_sub_type' => 'Student'
                           );
        $contact = CRM_Contact_BAO_Contact::add( $params );
        
        $updateParams = array( 'contact_sub_type'  => 'Sponsor',
                               'contact_type'      => 'Individual',
                               'contact_id'        => $contact->id 
                               );
        $updatedContact = CRM_Contact_BAO_Contact::add( $updateParams );
 
        $this->assertNull( $updatedContact , 'In line '. __LINE__ );
        CRM_Contact_BAO_Contact::deleteContact( $contact->id );
       

        $params = array( 'organization_name' => 'Compumentor' ,     
                         'contact_type'      => 'Organization',
                         'contact_sub_type'  => 'Sponsor'
                         );
        $contact = CRM_Contact_BAO_Contact::add( $params );

        $updateParams = array( 'contact_sub_type'  => 'Student',
                               'contact_type'      => 'Organization',
                               'contact_id'        => $contact->id 
                               );
        $updatedContact = CRM_Contact_BAO_Contact::add( $updateParams );

        $this->assertNull( $updatedContact , 'In line '. __LINE__ );
        CRM_Contact_BAO_Contact::deleteContact( $contact->id );

    }
}

?>