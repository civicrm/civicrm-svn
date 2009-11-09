<?php

require_once 'CiviTest/CiviUnitTestCase.php';
require_once 'CRM/Contact/BAO/Relationship.php';
require_once 'CRM/Contact/BAO/RelationshipType.php';
require_once 'CiviTest/Contact.php';


class CRM_Contact_BAO_ContactType_RelationshipTest extends CiviUnitTestCase 
{
    
    function get_info( ) 
    {
        return array(
                     'name'        => 'Relationship Subtype',
                     'description' => 'Test Relattionship for subtype.',
                     'group'       => 'CiviCRM BAO Tests',
                     );
    }
    
    function setUp( ) 
    {        
        parent::setUp();

        $params = array( 'first_name'   => 'Anne',     
                         'last_name'    => 'Grant',
                         'contact_type' => 'Individual',
                         );
        $this->individual = Contact::create( $params );

        $params = array( 'first_name'   => 'Bill',     
                         'last_name'    => 'Adams',
                         'contact_type' => 'Individual',
                         'contact_sub_type' => 'Student'
                         );
        $this->indivi_student = Contact::create( $params );

        $params = array( 'first_name'   => 'Alen',     
                         'last_name'    => 'Adams',
                         'contact_type' => 'Individual',
                         'contact_sub_type' => 'Parent'
                         );
        $this->indivi_parent = Contact::create( $params );
        
        $params = array( 'organization_name' => 'Compumentor' ,     
                         'contact_type'      => 'Organization',
                         );
        $this->organization = Contact::create( $params );  
        
        $params = array( 'organization_name' => 'Conservation Corp' ,     
                         'contact_type'      => 'Organization',
                         'contact_sub_type'  => 'Sponsor'
                         );
        $this->organization_sponsor = Contact::create( $params );
        
        $this->household = Contact::createHousehold( );

    }

    /**
     * methods create relationshipType with valid data
     * success expected
     * 
     */
    function testRelationshipTypeAdd( )
    {
        //check Individual to Parent RelationshipType 
        $params = array( 'name_a_b'           => 'indivToparent',
                         'name_b_a'           => 'parentToindiv',
                         'contact_type_a'     => 'Individual',
                         'contact_type_b'     => 'Individual',
                         'contact_sub_type_b' => 'Parent',
                         );
        $result = CRM_Contact_BAO_RelationshipType::add( $params, $ids );
        $this->assertEquals( $result->name_a_b , 'indivToparent' );
        $this->assertEquals( $result->contact_type_a , 'Individual' );
        $this->assertEquals( $result->contact_type_b , 'Individual' );
        $this->assertEquals( $result->contact_sub_type_b , 'Parent' );
        CRM_Contact_BAO_RelationshipType::del( $result->id );
        
        //check Sponcer to Individual RelationshipType
        $params = array( 'name_a_b'           => 'SponsorToIndiv',
                         'name_b_a'           => 'IndivToSponsor',
                         'contact_type_a'     => 'Organization',
                         'contact_sub_type_a' => 'Sponsor',
                         'contact_type_b'     => 'Individual',
                         );
        $result = CRM_Contact_BAO_RelationshipType::add( $params, $ids );
        $this->assertEquals( $result->name_a_b , 'SponsorToIndiv' );
        $this->assertEquals( $result->contact_type_a , 'Organization' );
        $this->assertEquals( $result->contact_sub_type_a , 'Sponsor' );
        $this->assertEquals( $result->contact_type_b , 'Individual' );
        CRM_Contact_BAO_RelationshipType::del( $result->id );

        //check Student to Sponcer RelationshipType
        $params = array( 'name_a_b'           => 'StudentToSponser',
                         'name_b_a'           => 'SponsorToStudent',
                         'contact_type_a'     => 'Individual',
                         'contact_sub_type_a' => 'Student',
                         'contact_type_b'     => 'Organization',
                         'contact_sub_type_b' => 'Sponsor',
                         );
        $result = CRM_Contact_BAO_RelationshipType::add( $params, $ids );
        $this->assertEquals( $result->name_a_b , 'StudentToSponser' );
        $this->assertEquals( $result->contact_type_a , 'Individual' );
        $this->assertEquals( $result->contact_sub_type_a , 'Student' );
        $this->assertEquals( $result->contact_type_b , 'Organization' );
        $this->assertEquals( $result->contact_sub_type_b , 'Sponsor' );
        CRM_Contact_BAO_RelationshipType::del( $result->id );

        //check for Household to Sponcer RelationshipType
        $params = array( 'name_a_b'           => 'HouseholdToSponser',
                         'name_b_a'           => 'SponsorToHousehold',
                         'contact_type_a'     => 'Household',
                         'contact_type_b'     => 'Organization',
                         'contact_sub_type_b' => 'Sponsor',
                         );
        $result = CRM_Contact_BAO_RelationshipType::add( $params, $ids );
        $this->assertEquals( $result->name_a_b , 'HouseholdToSponser' );
        $this->assertEquals( $result->contact_type_a , 'Household' );
        $this->assertEquals( $result->contact_type_b , 'Organization' );
        $this->assertEquals( $result->contact_sub_type_b , 'Sponsor' );
        CRM_Contact_BAO_RelationshipType::del( $result->id );
    }

    /**
     * methods create relationshipe with invalid Relationships
     * 
     */
    function testRelationshipCreateInvalidRelationships( ) {
        
        $relTypeIds = array( );

        //check for Individual to Parent
        $relTypeParams = array( 'name_a_b'           => 'indivToparent',
                                'name_b_a'           => 'parentToindiv',
                                'contact_type_a'     => 'Individual',
                                'contact_type_b'     => 'Individual',
                                'contact_sub_type_b' => 'Parent',
                                );
        $relType = CRM_Contact_BAO_RelationshipType::add( $relTypeParams, $relTypeIds );

        $params = array( 'relationship_type_id' => $relType->id.'_a_b',
                         'contact_check'        => array( $this->indivi_student => 1 )
                         );
        $ids = array('contact' => $this->individual );

        list( $valid, $invalid, $duplicate, $saved, $relationshipIds)  
            = CRM_Contact_BAO_Relationship::create( $params, $ids );
 
        $this->assertEquals( $invalid, 1, 'In line '. __LINE__ );
        $this->assertEquals( empty($relationshipIds), true , 'In line '. __LINE__ );
        CRM_Contact_BAO_RelationshipType::del( $relType->id );


        //check for Sponcer to Individual
        $relTypeParams = array( 'name_a_b'           => 'SponsorToIndiv',
                                'name_b_a'           => 'IndivToSponsor',
                                'contact_type_a'     => 'Organization',
                                'contact_sub_type_a' => 'Sponsor',
                                'contact_type_b'     => 'Individual',
                                );
        $relType = CRM_Contact_BAO_RelationshipType::add( $relTypeParams, $relTypeIds );

        $params = array( 'relationship_type_id' => $relType->id.'_a_b',
                         'contact_check'        => array( $this->individual => 1 )
                         );
        $ids = array('contact' => $this->indivi_parent );

        list( $valid, $invalid, $duplicate, $saved, $relationshipIds)  
            = CRM_Contact_BAO_Relationship::create( $params, $ids );

        $this->assertEquals( $invalid, 1, 'In line '. __LINE__ );
        $this->assertEquals( empty($relationshipIds), true , 'In line '. __LINE__ );
        CRM_Contact_BAO_RelationshipType::del( $relType->id );


        //check for Student to Sponcer
        $relTypeParams =  array( 'name_a_b'           => 'StudentToSponser',
                                 'name_b_a'           => 'SponsorToStudent',
                                 'contact_type_a'     => 'Individual',
                                 'contact_sub_type_a' => 'Student',
                                 'contact_type_b'     => 'Organization',
                                 'contact_sub_type_b' => 'Sponser',
                                 );
        $relType = CRM_Contact_BAO_RelationshipType::add( $relTypeParams, $relTypeIds );

        $params = array( 'relationship_type_id' => $relType->id.'_a_b',
                         'contact_check'        => array( $this->individual => 1 )
                         );
        $ids = array('contact' => $this->indivi_parent );

        list( $valid, $invalid, $duplicate, $saved, $relationshipIds)  
            = CRM_Contact_BAO_Relationship::create( $params, $ids );

        $this->assertEquals( $invalid, 1, 'In line '. __LINE__ );
        $this->assertEquals( empty($relationshipIds), true , 'In line '. __LINE__ );
        CRM_Contact_BAO_RelationshipType::del( $relType->id );

        
        //check for Household to Sponcer
        $relTypeParams =  array( 'name_a_b'           => 'HouseholdToSponser',
                                 'name_b_a'           => 'SponsorToHousehold',
                                 'contact_type_a'     => 'Individual',
                                 'contact_sub_type_a' => 'Student',
                                 'contact_type_b'     => 'Organization',
                                 'contact_sub_type_b' => 'Sponser',
                                 );
        $relType = CRM_Contact_BAO_RelationshipType::add( $relTypeParams, $relTypeIds );

        $params = array( 'relationship_type_id' => $relType->id.'_a_b',
                         'contact_check'        => array( $this->individual => 1 )
                         );
        $ids = array('contact' => $this->household );

        list( $valid, $invalid, $duplicate, $saved, $relationshipIds)  
            = CRM_Contact_BAO_Relationship::create( $params, $ids );

        $this->assertEquals( $invalid, 1, 'In line '. __LINE__ );
        $this->assertEquals( empty($relationshipIds), true , 'In line '. __LINE__ );
        CRM_Contact_BAO_RelationshipType::del( $relType->id );

    }
    
    /**
     * methods create relationshipe with valid data
     * success expected
     * 
     */
    function testRelationshipCreate( ) {

        $relTypeIds = array( );

        //check for Individual to Parent
        $relTypeParams = array( 'name_a_b'           => 'indivToparent',
                                'name_b_a'           => 'parentToindiv',
                                'contact_type_a'     => 'Individual',
                                'contact_type_b'     => 'Individual',
                                'contact_sub_type_b' => 'Parent',
                                );

        $relType = CRM_Contact_BAO_RelationshipType::add( $relTypeParams, $relTypeIds );
        $params = array( 'relationship_type_id' => $relType->id.'_a_b',
                         'contact_check'        => array( $this->indivi_parent => $this->indivi_parent )
                         );
        $ids = array('contact' => $this->individual );
        list( $valid, $invalid, $duplicate, $saved, $relationshipIds)  
            = CRM_Contact_BAO_Relationship::create( $params, $ids );

        $this->assertEquals( $valid, 1 , 'In line '. __LINE__ );
        $this->assertEquals( empty($relationshipIds), false , 'In line '. __LINE__ );
        CRM_Contact_BAO_RelationshipType::del( $relType->id );
        foreach( $relationshipIds as $id ) {
            CRM_Contact_BAO_Relationship::del( $id );
        }
        

        //check for Sponcer to Individual
        $relTypeParams = array( 'name_a_b'           => 'SponsorToIndiv',
                                'name_b_a'           => 'IndivToSponsor',
                                'contact_type_a'     => 'Organization',
                                'contact_sub_type_a' => 'Sponsor',
                                'contact_type_b'     => 'Individual',
                                );
        $relType = CRM_Contact_BAO_RelationshipType::add( $relTypeParams, $relTypeIds );
        $params = array( 'relationship_type_id' => $relType->id.'_a_b',
                         'contact_check'        => array( $this->indivi_student => 1 )
                         );
        $ids = array('contact' => $this->organization_sponsor );
        list( $valid, $invalid, $duplicate, $saved, $relationshipIds)  
            = CRM_Contact_BAO_Relationship::create( $params, $ids );
       
        $this->assertEquals( $valid, 1 , 'In line '. __LINE__ );
        $this->assertEquals( empty($relationshipIds), false , 'In line '. __LINE__ );
        CRM_Contact_BAO_RelationshipType::del( $relType->id );
        foreach( $relationshipIds as $id ) {
            CRM_Contact_BAO_Relationship::del( $id );
        }


        //check for Student to Sponcer
        $relTypeParams =  array( 'name_a_b'           => 'StudentToSponsor',
                                 'name_b_a'           => 'SponsorToStudent',
                                 'contact_type_a'     => 'Individual',
                                 'contact_sub_type_a' => 'Student',
                                 'contact_type_b'     => 'Organization',
                                 'contact_sub_type_b' => 'Sponsor',
                                 );
        $relType = CRM_Contact_BAO_RelationshipType::add( $relTypeParams, $relTypeIds );
        $params = array( 'relationship_type_id' => $relType->id.'_a_b',
                         'contact_check'        => array( $this->organization_sponsor => 1 )
                         );
        $ids = array('contact' => $this->indivi_student );
        list( $valid, $invalid, $duplicate, $saved, $relationshipIds)  
            = CRM_Contact_BAO_Relationship::create( $params, $ids );

        $this->assertEquals( $valid, 1 , 'In line '. __LINE__ );
        $this->assertEquals( empty($relationshipIds), false , 'In line '. __LINE__ );
        CRM_Contact_BAO_RelationshipType::del( $relType->id );
        foreach( $relationshipIds as $id ) {
            CRM_Contact_BAO_Relationship::del( $id );
        }

        
        //check for Household to Sponcer
        $relTypeParams =  array( 'name_a_b'           => 'HouseholdToSponser',
                                 'name_b_a'           => 'SponsorToHousehold',
                                 'contact_type_a'     => 'Household',
                                 'contact_type_b'     => 'Organization',
                                 'contact_sub_type_b' => 'Sponsor',
                                 );
        $relType = CRM_Contact_BAO_RelationshipType::add( $relTypeParams, $relTypeIds );
        $params = array( 'relationship_type_id' => $relType->id.'_a_b',
                         'contact_check'        => array( $this->organization_sponsor => 1 )
                         );
        $ids = array('contact' => $this->household );

        list( $valid, $invalid, $duplicate, $saved, $relationshipIds)  
            = CRM_Contact_BAO_Relationship::create( $params, $ids );
       
        $this->assertEquals( $valid, 1 , 'In line '. __LINE__ );
        $this->assertEquals( empty($relationshipIds), false , 'In line '. __LINE__ );
        CRM_Contact_BAO_RelationshipType::del( $relType->id );
        foreach( $relationshipIds as $id ) {
            CRM_Contact_BAO_Relationship::del( $id );
        }
    }
    
}

?>