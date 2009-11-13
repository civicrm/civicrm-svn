<?php

require_once 'CiviTest/CiviUnitTestCase.php';
require_once 'CiviTest/Contact.php';
require_once 'api/v2/Contact.php';
require_once 'CRM/Contact/BAO/ContactType.php';

class CRM_Contact_BAO_ContactType_ContactSearchTest extends CiviUnitTestCase 
{
    
    function get_info( ) 
    {
        return array(
                     'name'        => 'Contact Serach Subtype',
                     'description' => 'Test Contact for subtype.',
                     'group'       => 'CiviCRM BAO Tests',
                     );
    }
    
    function setUp( ) 
    {        
        parent::setUp();

        
        $params = array( 'label'    => 'indivi_student',
                         'name'      => 'indivi_student',
                         'parent_id' => 1,//Individual
                         'is_active' => 1
                         );
        $result  = CRM_Contact_BAO_ContactType::add( $params );
        $this->student = $params['name']; 
        
        $params = array( 'label'     => 'indivi_parent',
                         'name'      => 'indivi_parent',
                         'parent_id' => 1,//Individual
                         'is_active' => 1
                         );
        $result  = CRM_Contact_BAO_ContactType::add( $params );
        $this->parent = $params['name']; 


        $params = array( 'label'     => 'org_sponsor',
                         'name'      => 'org_sponsor',
                         'parent_id' => 3,//Organization
                         'is_active' => 1
                         );
        $result  = CRM_Contact_BAO_ContactType::add( $params );
        $this->sponsor =  $params['name'];


        $this->indiviParams = array( 'first_name'   => 'Anne',     
                                     'last_name'    => 'Grant',
                                     'contact_type' => 'Individual',
                                     );
        $this->individual = Contact::create( $this->indiviParams );
        
        $this->indiviStudentParams = array( 'first_name'       => 'Bill',     
                                            'last_name'        => 'Adams',
                                            'contact_type'     => 'Individual',
                                            'contact_sub_type' => $this->student
                                            );
        $this->indiviStudent = Contact::create( $this->indiviStudentParams );
        
        $this->indiviParentParams = array( 'first_name'       => 'Alen',     
                                           'last_name'        => 'Adams',
                                           'contact_type'     => 'Individual',
                                           'contact_sub_type' => $this->parent
                                           );
        $this->indiviParent = Contact::create(  $this->indiviParentParams );
        
        $this->organizationParams = array( 'organization_name' => 'Compumentor' ,     
                                           'contact_type'      => 'Organization',
                                           );
        $this->organization = Contact::create( $this->organizationParams );  
        
        $this->orgSponsorParams = array( 'organization_name' => 'Conservation Corp' ,     
                                         'contact_type'      => 'Organization',
                                         'contact_sub_type'  => $this->sponsor
                                         );
        $this->orgSponsor = Contact::create( $this->orgSponsorParams );
        
        $this->householdParams = array( 'household_name' => "John Doe's home",
                                        'contact_type'   => 'Household' );
        $this->household = Contact::create( $this->householdParams );
        
    }
    
    /*
     * search with only type
     * success expected.
     */
    function testSearchWithType( ) {

        /*
         * for type:Individual
         */
        $defaults = array( );
        $params   = array( 'contact_type' => 'Individual' );
        $result   =& civicrm_contact_search( $params, $defaults );
        
        $individual    = $result[$this->individual];
        $indiviStudent = $result[$this->indiviStudent];
        $indiviParent  = $result[$this->indiviParent];
        
        //asserts for type:Individual
        $this->assertEquals( $individual['contact_id'] , $this->individual, 'In line '. __LINE__ );
        $this->assertEquals( $individual['first_name'] , $this->indiviParams['first_name'], 'In line '. __LINE__ );
        $this->assertEquals( $individual['contact_type'], $this->indiviParams['contact_type'], 'In line '. __LINE__ );
        $this->assertNull( $individual['contact_sub_type'], 'In line '. __LINE__ );
        
        //asserts for type:Individual subtype:Student
        $this->assertEquals( $indiviStudent['contact_id'] , $this->indiviStudent, 'In line '. __LINE__ );
        $this->assertEquals( $indiviStudent['first_name'] , $this->indiviStudentParams['first_name'], 'In line '. __LINE__ );
        $this->assertEquals( $indiviStudent['contact_type'], $this->indiviStudentParams['contact_type'], 'In line '. __LINE__ );
        $this->assertEquals( $indiviStudent['contact_sub_type'], $this->indiviStudentParams['contact_sub_type'], 'In line '. __LINE__ );

        //asserts for type:Individual subtype:Parent
        $this->assertEquals( $indiviParent['contact_id'] , $this->indiviParent, 'In line '. __LINE__ );
        $this->assertEquals( $indiviParent['first_name'] , $this->indiviParentParams['first_name'], 'In line '. __LINE__ );
        $this->assertEquals( $indiviParent['contact_type'], $this->indiviParentParams['contact_type'], 'In line '. __LINE__ );
        $this->assertEquals( $indiviParent['contact_sub_type'], $this->indiviParentParams['contact_sub_type'], 'In line '. __LINE__ );

        /*
         * for type:Organization
         */
        $params   = array( 'contact_type' => 'Organization' );
        $result   =& civicrm_contact_search( $params, $defaults );
        
        $organization  = $result[$this->organization];
        $orgSponsor    = $result[$this->orgSponsor];
        
        //asserts for type:Organization
        $this->assertEquals( $organization['contact_id'] , $this->organization , 'In line '. __LINE__ );
        $this->assertEquals( $organization['organization_name'] , $this->organizationParams['organization_name'], 'In line '. __LINE__ );
        $this->assertEquals( $organization['contact_type'], $this->organizationParams['contact_type'], 'In line '. __LINE__ );
        $this->assertNull( $organization['contact_sub_type'], 'In line '. __LINE__ );
        
        //asserts for type:Organization subtype:Sponsor
        $this->assertEquals( $orgSponsor['contact_id'] , $this->orgSponsor, 'In line '. __LINE__ );
        $this->assertEquals( $orgSponsor['organization_name'] , $this->orgSponsorParams['organization_name'], 'In line '. __LINE__ );
        $this->assertEquals( $orgSponsor['contact_type'], $this->orgSponsorParams['contact_type'], 'In line '. __LINE__ );
        $this->assertEquals( $orgSponsor['contact_sub_type'], $this->orgSponsorParams['contact_sub_type'], 'In line '. __LINE__ );

        /*
         * for type:Household
         */
        $params   = array( 'contact_type' => 'Household' );
        $result   =& civicrm_contact_search( $params, $defaults );
        
        $household  = $result[$this->household];

        //asserts for type:Household
        $this->assertEquals( $household['contact_id'] , $this->household, 'In line '. __LINE__ );
        $this->assertEquals( $household['household_name'] , $this->householdParams['household_name'], 'In line '. __LINE__ );
        $this->assertEquals( $household['contact_type'], $this->householdParams['contact_type'], 'In line '. __LINE__ );
        $this->assertNull( $household['contact_sub_type'], 'In line '. __LINE__ );

    }

    /*
     * search with only subtype 
     * success expected.
     */
    function testSearchWithSubype( ) {

        /*
         * for subtype:Student
         */
        $defaults = array( );
        $params   = array( 'contact_sub_type' => $this->student );
        $result   =& civicrm_contact_search( $params, $defaults );
        
        $indiviStudent = $result[$this->indiviStudent];
        
        //asserts for type:Individual subtype:Student
        $this->assertEquals( $indiviStudent['contact_id'] , $this->indiviStudent, 'In line '. __LINE__ );
        $this->assertEquals( $indiviStudent['first_name'] , $this->indiviStudentParams['first_name'], 'In line '. __LINE__ );
        $this->assertEquals( $indiviStudent['contact_type'], $this->indiviStudentParams['contact_type'], 'In line '. __LINE__ );
        $this->assertEquals( $indiviStudent['contact_sub_type'], $this->indiviStudentParams['contact_sub_type'], 'In line '. __LINE__ );

        //all other contact(rather than subtype:student) should not
        //exists
        $this->assertNull( $result[$this->individual]  , 'In line '. __LINE__ );
        $this->assertNull( $result[$this->indiviParent] , 'In line '. __LINE__ );
        $this->assertNull( $result[$this->organization] , 'In line '. __LINE__ );
        $this->assertNull( $result[$this->orgSponsor] , 'In line '. __LINE__ );
        $this->assertNull( $result[$this->household] , 'In line '. __LINE__ );

        /*
         * for subtype:Sponsor
         */
        $params   = array( 'contact_sub_type' => $this->sponsor );
        $result   =& civicrm_contact_search( $params, $defaults );
        
        $orgSponsor = $result[$this->orgSponsor];
         
        //asserts for type:Organization subtype:Sponsor
        $this->assertEquals( $orgSponsor['contact_id'] , $this->orgSponsor, 'In line '. __LINE__ );
        $this->assertEquals( $orgSponsor['organization_name'] , $this->orgSponsorParams['organization_name'], 'In line '. __LINE__ );
        $this->assertEquals( $orgSponsor['contact_type'], $this->orgSponsorParams['contact_type'], 'In line '. __LINE__ );
        $this->assertEquals( $orgSponsor['contact_sub_type'], $this->orgSponsorParams['contact_sub_type'], 'In line '. __LINE__ );

        //all other contact(rather than subtype:Sponsor) should not
        //exists
        $this->assertNull( $result[$this->individual]  , 'In line '. __LINE__ );
        $this->assertNull( $result[$this->indiviStudent]  , 'In line '. __LINE__ );
        $this->assertNull( $result[$this->indiviParent] , 'In line '. __LINE__ );
        $this->assertNull( $result[$this->organization] , 'In line '. __LINE__ );
        $this->assertNull( $result[$this->household] , 'In line '. __LINE__ );

    }

    /*
     * search with type as well as subtype 
     * success expected.
     */
    function testSearchWithTypeSubype( ) {

        /*
         * for type:individual subtype:Student
         */
        $defaults = array( );
        $params   = array('contact_type'     => 'Individual', 
                          'contact_sub_type' => $this->student );
        $result   =& civicrm_contact_search( $params, $defaults );
        
        $indiviStudent = $result[$this->indiviStudent];
        
        //asserts for type:Individual subtype:Student
        $this->assertEquals( $indiviStudent['contact_id'] , $this->indiviStudent, 'In line '. __LINE__ );
        $this->assertEquals( $indiviStudent['first_name'] , $this->indiviStudentParams['first_name'], 'In line '. __LINE__ );
        $this->assertEquals( $indiviStudent['contact_type'], $this->indiviStudentParams['contact_type'], 'In line '. __LINE__ );
        $this->assertEquals( $indiviStudent['contact_sub_type'], $this->indiviStudentParams['contact_sub_type'], 'In line '. __LINE__ );

        //all other contact(rather than subtype:student) should not
        //exists
        $this->assertNull( $result[$this->individual]  , 'In line '. __LINE__ );
        $this->assertNull( $result[$this->indiviParent] , 'In line '. __LINE__ );
        $this->assertNull( $result[$this->organization] , 'In line '. __LINE__ );
        $this->assertNull( $result[$this->orgSponsor] , 'In line '. __LINE__ );
        $this->assertNull( $result[$this->household] , 'In line '. __LINE__ );

        /*
         * for type:Organization subtype:Sponsor
         */
        $params   = array('contact_type'     => 'Organization', 
                          'contact_sub_type' => $this->sponsor );
        $result   =& civicrm_contact_search( $params, $defaults );
        
        $orgSponsor = $result[$this->orgSponsor];
         
        //asserts for type:Organization subtype:Sponsor
        $this->assertEquals( $orgSponsor['contact_id'] , $this->orgSponsor, 'In line '. __LINE__ );
        $this->assertEquals( $orgSponsor['organization_name'] , $this->orgSponsorParams['organization_name'], 'In line '. __LINE__ );
        $this->assertEquals( $orgSponsor['contact_type'], $this->orgSponsorParams['contact_type'], 'In line '. __LINE__ );
        $this->assertEquals( $orgSponsor['contact_sub_type'], $this->orgSponsorParams['contact_sub_type'], 'In line '. __LINE__ );

        //all other contact(rather than subtype:Sponsor) should not
        //exists
        $this->assertNull( $result[$this->individual]  , 'In line '. __LINE__ );
        $this->assertNull( $result[$this->indiviStudent]  , 'In line '. __LINE__ );
        $this->assertNull( $result[$this->indiviParent] , 'In line '. __LINE__ );
        $this->assertNull( $result[$this->organization] , 'In line '. __LINE__ );
        $this->assertNull( $result[$this->household] , 'In line '. __LINE__ );

    }

    /*
     * search with invalid type or subtype
     */
    function testSearchWithInvalidData( ) {
        
        // for invalid type 
        $defaults = array( );
        $params   = array( 'contact_type' => 'Invalid' );
        $result   =& civicrm_contact_search( $params, $defaults );
        $this->assertEquals( empty($result), true, 'In line '. __LINE__ );
        
        
        // for invalid subtype 
        $params   = array( 'contact_sub_type' => 'Invalid' );
        $result   =& civicrm_contact_search( $params, $defaults );
        $this->assertEquals( empty($result), true, 'In line '. __LINE__ );

        
        // for invalid subtype as well as subtype
        $params   = array( 'contact_type'     => 'Invalid',
                           'contact_sub_type' => 'Invalid' );
        $result   =& civicrm_contact_search( $params, $defaults );
        $this->assertEquals( empty($result), true, 'In line '. __LINE__ );

        
        // for valid type and invalid subtype
        $params   = array( 'contact_type'     => 'Individual',
                           'contact_sub_type' => 'Invalid' );
        $result   =& civicrm_contact_search( $params, $defaults );
        $this->assertEquals( empty($result), true, 'In line '. __LINE__ ); 

        
        // for invalid type and valid subtype
        $params   = array( 'contact_type'     => 'Invalid',
                           'contact_sub_type' => $this->student );
        $result   =& civicrm_contact_search( $params, $defaults );
        $this->assertEquals( empty($result), true, 'In line '. __LINE__ ); 
    }
    
    /* search with wrong type or subtype
     *
     */
    function testSearchWithWrongdData( ) {
        
        // for type:Individual subtype:Sponsor 
        $defaults = array( );
        $params   = array( 'contact_type'     => 'Individual',
                           'contact_sub_type' => $this->sponsor );
        $result   =& civicrm_contact_search( $params, $defaults );
        $this->assertEquals( empty($result), true, 'In line '. __LINE__ );
        
        // for type:Orgaization subtype:Parent
        $params   = array( 'contact_type'     => 'Orgaization',
                           'contact_sub_type' => $this->parent );
        $result   =& civicrm_contact_search( $params, $defaults );
        $this->assertEquals( empty($result), true, 'In line '. __LINE__ );

        
        // for type:Household subtype:Sponsor
        $params   = array( 'contact_type'     => 'Household',
                           'contact_sub_type' => $this->sponsor );
        $result   =& civicrm_contact_search( $params, $defaults );
        $this->assertEquals( empty($result), true, 'In line '. __LINE__ );

        
        // for type:Household subtype:Student
        $params   = array( 'contact_type'     => 'Household',
                           'contact_sub_type' => $this->student );
        $result   =& civicrm_contact_search( $params, $defaults );
        $this->assertEquals( empty($result), true, 'In line '. __LINE__ ); 
        
    }
}

?>