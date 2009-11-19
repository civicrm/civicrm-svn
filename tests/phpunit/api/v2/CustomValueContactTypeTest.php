<?php

require_once 'CiviTest/CiviUnitTestCase.php';
require_once 'CiviTest/Contact.php';
require_once 'CiviTest/Custom.php';
require_once 'CRM/Core/BAO/CustomValueTable.php';
require_once 'api/v2/Contact.php';

class api_v2_CustomValueContactTypeTest  extends CiviUnitTestCase 
{
    function get_info( ) 
    {
        return array(
                     'name'        => 'Custom Data For Conatct Subtype',
                     'description' => 'Test Custom Data for Contact subtype.',
                     'group'       => 'CiviCRM API Tests',
                     );
    }
    
    function setUp( ) 
    {
        
        parent::setUp();
        
        //  Create Group For Individual  Contact Type
        $groupIndividual   = array(
                                   'title'       => 'TestGroup For Individual',
                                   'name'        => 'testGroupIndividual',
                                   'extends'     => 'individual',
                                   'style'       => 'Inline',
                                   'is_active'   => 1
                          );
        $this->CustomGroupIndividual = Custom::createGroup($groupIndividual );
        
        $params = array(
                         'custom_group_id' => $this->CustomGroupIndividual->id,
                         'label'           => 'Individual School Score',
                         'html_type'       => 'Text',
                         'data_type'       => 'String',
                         'weight'          => 4,
                         'is_required'     => 1,
                         'is_searchable'   => 0,
                         'is_active'       => 1
                        );
        
        $this->IndividualField = Custom::createField($params );
        
        //  Create Group For Individual-Student  Contact Sub  Type
        $groupIndiStudent   = array(
                                    'title'       => 'TestGroup For Individual - Student',
                                    'name'        => 'testGroupIndividualStudent',
                                    'extends'     => 'individual',
                                    'extends_entity_column_value' => CRM_Core_DAO::VALUE_SEPARATOR.'Student'.CRM_Core_DAO::VALUE_SEPARATOR,
                                    'style'       => 'Inline',
                                    'is_active'   => 1
                                    );
        $this->CustomGroupIndiStudent = Custom::createGroup($groupIndiStudent );
        
        $params = array(
                        'custom_group_id' => $this->CustomGroupIndiStudent->id,
                        'label'           => 'Individual-Student College',
                        'html_type'       => 'Text',
                        'data_type'       => 'String',
                        'weight'          => 4,
                        'is_required'     => 1,
                        'is_searchable'   => 0,
                        'is_active'       => 1
                        );
        
        $this->IndiStudentField = Custom::createField($params );
        
        $params = array( 'first_name'   => 'Mathev',     
                         'last_name'    => 'Adison',
                         'contact_type' => 'Individual',
                         );
        $this->individual = Contact::create( $params );
        
        $params = array( 'first_name'   => 'Steve',     
                         'last_name'    => 'Tosun',
                         'contact_type' => 'Individual',
                         'contact_sub_type' => 'Student'
                         );
        $this->individualStudent = Contact::create( $params );
        
        $params = array( 'first_name'   => 'Mark',     
                         'last_name'    => 'Dawson',
                         'contact_type' => 'Individual',
                         'contact_sub_type' => 'Parent'
                         );
        $this->individualParent = Contact::create( $params );
        
        $params = array( 'organization_name' => 'Wellspring' ,     
                         'contact_type'      => 'Organization',
                         );
        $this->organization = Contact::create( $params );
        
        $params = array( 'organization_name' => 'SubUrban' ,     
                         'contact_type'      => 'Organization',
                         'contact_sub_type'  => 'Sponsor'
                         );
        $this->organizationSponsor = Contact::create( $params );
    }
    
    /**
     * Add  Custom data of Contact Type : Individual to a Contact type: Organization 
     */ 
    function testAddIndividualCustomDataToOrganization() {
        
        $params = array(
                        'contact_id'           => $this->organization ,
                        'contact_type'      => 'Organization',
                        "custom_{$this->IndividualField->id}" => 'Test String',  
                        );
        
        $contact =& civicrm_contact_add( $params );
        $this->assertEquals( $contact['error_message'], 'Invalid Custom Field Contact Type: Organization' );
    }
    
    
    /**
     * Add valid  Empty params to a Contact Type : Individual
     */ 
    function testAddCustomDataEmptyToIndividual() {
        
        $params = array( );
        $contact =& civicrm_contact_add( $params );
        $this->assertEquals( $contact['is_error'], 1 );
        $this->assertEquals( $contact['error_message'], 'Input Parameters empty' );
    }

    
    /**
     * Add valid custom data to a Contact Type : Individual
     */ 
    function testAddValidCustomDataToIndividual() {
        
        $params = array(
                        'contact_id'           => $this->individual ,
                        'contact_type' => 'Individual',
                        "custom_{$this->IndividualField->id}" => 'Test String',  
                        );
        $contact =& civicrm_contact_add( $params );
        
        $this->assertNotNull( $contact['contact_id'] , 'In line '. __LINE__ );
        $entityValues =  CRM_Core_BAO_CustomValueTable::getEntityValues( $this->individual);
        $elements["custom_{$this->IndividualField->id}"] = $entityValues["{$this->IndividualField->id}"];
        
        // Check the Value in Database 
        $this->assertEquals( $elements["custom_{$this->IndividualField->id}"], 'Test String' );   
    }
    
    /**
     * Add  Custom data of Contact Type : Individual , SubType : Student to a Contact type: Organization  Subtype: Sponsor
     */ 
    function testAddIndividualStudentCustomDataToOrganizationSponsor() {
        
        $params = array(
                        'contact_id'           => $this->organizationSponsor ,
                        'contact_type'      => 'Organization',
                        "custom_{$this->IndiStudentField->id}" => 'Test String',  
                        );
        
        $contact =& civicrm_contact_add( $params );
        $this->assertEquals( $contact['error_message'], 'Invalid Custom Field Contact Type: Organization or Mismatched SubType: Sponsor.' );
    }
    
    /**
     * Add valid custom data to a Contact Type : Individual Subtype: Student
     */ 
    function testAddValidCustomDataToIndividualStudent() {
        
        $params = array(
                        'contact_id'           => $this->individualStudent ,
                        'contact_type' => 'Individual',
                        "custom_{$this->IndiStudentField->id}" => 'Test String',
                        );
        
        $contact =& civicrm_contact_add( $params );
        
        $this->assertNotNull( $contact['contact_id'] , 'In line '. __LINE__ );
        $entityValues =  CRM_Core_BAO_CustomValueTable::getEntityValues( $this->individualStudent);
        $elements["custom_{$this->IndiStudentField->id}"] = $entityValues["{$this->IndiStudentField->id}"];
        
        // Check the Value in Database 
        $this->assertEquals( $elements["custom_{$this->IndiStudentField->id}"], 'Test String' );
    }
    
    
    /**
     * Add custom data(of Individual Student)to a Contact Type : Individual  
     */ 
    function testAddIndividualStudentCustomDataToIndividual() {
        
        $params = array(
                        'contact_id'           => $this->individual ,
                        'contact_type' => 'Individual',
                        "custom_{$this->IndiStudentField->id}" => 'Test String',
                        );
        
        $contact =& civicrm_contact_add( $params );
        $this->assertEquals( $contact['error_message'], 'Invalid Custom Field Contact Type: Individual' );
    }
    
    /**
     * Add custom data of Individual Student to a Contact Type : Individual - parent   
     */ 
    function testAddIndividualStudentCustomDataToIndividualParent() {
        
        $params = array(
                        'contact_id'           => $this->individualParent ,
                        'contact_type' => 'Individual',
                        "custom_{$this->IndiStudentField->id}" => 'Test String',
                        );
        
        $contact =& civicrm_contact_add( $params );
        $this->assertEquals( $contact['error_message'], 'Invalid Custom Field Contact Type: Individual or Mismatched SubType: Parent.' );
    }
    
    
    
    // Retrieve Methods
    
    /**
     * Retrieve Valid custom Data added to  Individual Contact Type
     */
    function testRetrieveValidCustomDataToIndividual() {
        
        $params = array(
                        'contact_id'           => $this->individual ,
                        'contact_type' => 'Individual',
                        "custom_{$this->IndividualField->id}" => 'Test String',  
                        );
        $contact =& civicrm_contact_add( $params );
        $params = array( 
                        'contact_id'           => $this->individual ,
                        'contact_type' => 'Individual',
                        "return.custom_{$this->IndividualField->id}"  => 1
                         );
        $getContact = civicrm_contact_get( $params, false );
        $this->assertEquals( $getContact[$this->individual][ "custom_{$this->IndividualField->id}"], 'Test String' );
    }
    
    /**
     * Retrieve Valid custom Data added to  Individual Contact Type , Subtype : Student.
     */
    function testRetrieveValidCustomDataToIndividualStudent() {
        
        $params = array(
                        'contact_id'           => $this->individualStudent ,
                        'contact_type' => 'Individual',
                        'contact_sub_type'     => 'Student',
                        "custom_{$this->IndiStudentField->id}" => 'Test String',  
                        );
        $contact =& civicrm_contact_add( $params );
        $params = array(  
                        'contact_id'           => $this->individualStudent ,
                        'contact_type'         => 'Individual',
                        'contact_sub_type'     => 'Student',
                        "return.custom_{$this->IndiStudentField->id}"  => 1
                          ); 
        $getContact = civicrm_contact_get( $params, false );
        $this->assertEquals( $getContact[$this->individualStudent][ "custom_{$this->IndiStudentField->id}"], 'Test String' );         
    }
    
}

?> 