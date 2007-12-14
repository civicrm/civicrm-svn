<?php

require_once 'api/v2/Contact.php';
require_once 'CRM/Utils/Array.php';

class testContactAdd extends CiviUnitTestCase 

{

    // class properties here
    // e.g protected $_participant;
    protected $_createdContacts   = array();

    // civicrm_contact
    protected $contactAllParams = array(
//                'id' => '',
                'nick_name' => 'This is nickname',
                'domain_id' => '1',
//                'contact_type' => '',
                'do_not_email' => '1',
                'do_not_phone' => '1',
                'do_not_mail' => '1',
                'contact_sub_type' => 'CertainSubType',
                'legal_identifier' => 'ABC23853ZZ2235',
                'external_identifier' => '1928837465',
//                'sort_name' => '',
//                'display_name' => '',
                'home_URL' => 'http://some.url.com',
                'image_URL' => 'http://some.url.com/image.jpg',
                'preferred_communication_method' => 'Mail',
                'preferred_mail_format' => 'HTML',
                'do_not_trade' => '1',
//                'hash' => '',
                'is_opt_out' => '1',
                'contact_source' => 'Just some source.'
                
                );

    // civicrm_individual
    protected $individualAllParams = array(
//                'id' => '',
//                'contact_id' => '',
                'first_name' => 'Johny',
                'middle_name' => 'Lorenzo',
                'last_name' => 'TestSubject',
                'prefix' => 'Mr',
                'suffix' => 'VII',
                'greeting_type' => 'Informal',
                'custom_greeting' => 'Dear Pal',
                'job_title' => 'President',
                'gender' => 'Male',
                'birth_date' => '1977-03-12',
                'is_deceased' => '1',
                'deceased_date' => '2499-12-12',
//                'phone_to_household_id' => '',
//                'email_to_household_id' => '',
//                'mail_to_household_id' => ''
                
                );
    
    // civicrm_organization
    protected $organizatonAllParams = array(
//                'id' => '',
//                'contact_id' => '',
                'organization_name' => '',
                'legal_name' => '',
                'sic_code' => '',
                'primary_contact_id' => '',                
                
                );

    // civicrm_household
    protected $householdAllParams = array(
//                'id' => '',
//                'contact_id' => '',
                'household_name' => '',
                'primary_contact_id' => '',
                
                );
    
    // end class properties
            
    /**
     * Prepares environment for given testcase.
     */
    function setUp() {
    }

    /**
     * Cleans up environment after given testcase.
     */    
    function tearDown() {
        // tearing down all the contacts created
        foreach ( $this->_createdContacts as $id ) {
            $params = array( 'contact_id' => $id );
            $result = civicrm_contact_delete( $params );
        }
    }


    // Put test functions below. Each function's name
    // needs to start with "test", e.g. testCreateEmptyContact



    /**
     * Create Individual with minimal set of fields - email as a base.
     * 
     * Well commented example for other tests.
     */    
    function testCreateIndividualMinimalWithEmail() {
        // FIXME: make sure this is minimal set of needed data
        $params = array('email'            => 'man23456@yahoo.com',
                        'contact_type'     => 'Individual',
                        );

        // We want to make sure that we will be comparing
        // created contact's attributes against parameters which
        // are exactly the same. civicrm_contact_add modifies $params
        // array through reference, so let's use a deep copy (totally
        // separate variable, without any references.
        $paramsCopy = CRM_Utils_Array::array_deep_copy( $params );

        // Performing contact creation
        $contact = &civicrm_contact_add( $paramsCopy );

        // This is a kind of overkill (further tests would throw exceptions), but
        // let's be paranoic. We have an assumption, that API methods return
        // arrays - so let's make sure this requirement is met.
        $this->assertIsA( $contact, 'Array' );

        // Let's check obvious results of civicrm_contact_add:
        // it should not be an error, and contact_id should be set.
        $this->assertEqual( $contact['is_error'], 0 );
        $this->assertNotNull( $contact['contact_id'] );

        // Another paranoic check - let's see if $contact and $paramsCopy (modified 
        // through reference by civicrm_contact_add) have the same contact_id
        $this->assertIdentical( $contact['contact_id'], $paramsCopy['contact_id'] );

        // Now we need to verify, whether created contact is correct. The best 
        // way to do it would be to check directly in the database, but this would
        // be pretty work intensive to do for every single test, so we'll do this 
        // kind of check (database) only in one special test further down the road.
        // For all other tests, we'll use civicrm_contact_get to retrieve created contact
        // and verify whether all the attributes match.
        // And of course, since we are paranoic, we're using copy again.

        $retrievedId = array( 'contact_id' => $contact['contact_id'] );
        $retrieved = &civicrm_contact_get( $retrievedId );

        // Now it's time to start comparing return from civicrm_contact_add with 
        // civicrm_contact_get result. Let's check if ids match first.
        // FIXME: Wanted to use assertIdentical, but $retrieved['contact_id']
        // is a string - is it intended?
        $this->assertEqual( $contact['contact_id'], $retrieved['contact_id'] );

        // We will be comparing each attribute against original params, which didn't 
        // get through any modifications through references.
        foreach( $params as $paramName => $paramValue ) {
            $this->assertEqual( $paramValue, $retrieved[$paramName] );
        }

        // Please note that many of the above assertions will be repeated many times
        // in further tests in this test case. After you saw how does it work line
        // by line, take a look at next method, which uses private functions to
        // save ourselves a lot of time and copy-pasting, but does basically the same
        // as above.

        // Now storing created contact's id for further deletion in tearDown()
        $this->_createdContacts[] = $contact['contact_id'];
    }


    /**
     * Create Individual with minimal set of fields - name as a base.
     */
    function testCreateIndividualMinimalWithName() {
        $params = array('first_name' => 'abc1',
                        'last_name' => 'xyz1',
                        'contact_type'     => 'Individual'
                        );
        $this->_doCreateTest( $params );
    }

    /**
     * Create Household with minimal set of fields - name as a base.
     */    
    function testCreateHouseholdMinimalWithName() {
        // FIXME: We have an inconsistency in API here:
        // setting household_name, but not getting the same
        // back - instead we get display_name and sort_name
        $params = array( 'household_name' => 'The abc Household',
                         'contact_type' => 'Household',
                        );
        $this->_doCreateTest( $params );
    }

    /**
     * Create Organization with minimal set of fields - name as a base.
     */        
    function testCreateOrganizationMinimalWithName() {
        $params = array('organization_name' => 'The abc Organization',
                        'contact_type' => 'Organization',
                        );
        $this->_doCreateTest( $params );
    }

    /**
     * For each attribute defined in $this->*AllParams, set up a contact and
     * verify whether you get correct value.
     */
     private function _testCreateContactForAllAttributes( $typeSpecificAttributes ) {
         $allparams = array_merge( $this->contactAllParams, $typeSpecificAttributes );
         $c = 10000;
         foreach( $allparams as $key => $value ) {
             $params = array( 'email' => "johny$c@mail.com", 
                              'contact_type' => 'Individual',
                              $key    => $value
                            );
             $this->_doCreateTest( $params );
             $c = $c + 1;
         }
     }


     function testCreateIndividualForAllAttributes() {
         $this->_testCreateContactForAllAttributes( $this->individualAllParams );
     }
    
    function testCreateIndividualwithEmailLocationType() {
        $params = array('first_name'    => 'abc4',
                        'last_name'     => 'xyz4',
                        'email'         => 'man4@yahoo.com',
                        'contact_type'     => 'Individual',
                        'location_type_id' => 2,
                        );
        $contact =& civicrm_contact_add($params);
        $this->assertNotNull( $contact['contact_id'] );
        $this->_createdContacts[] = $contact['contact_id'];
    }

    
    function testCreateIndividualwithPhone() 
    {
        $params = array('first_name'    => 'abc5',
                        'last_name'     => 'xyz5',
                        'contact_type'     => 'Individual',
                        'location_type_id' => 2,
                        'phone'         => '11111',
                        'phone_type'    => 'Phone'
                        );
        $contact =& civicrm_contact_add($params);
        $this->assertNotNull( $contact['contact_id'] );
        $this->_createdContacts[] = $contact['contact_id'];
    }
    
    function testCreateIndividualwithAll() 
    {
        $params = array('first_name'    => 'abc7',
                        'last_name'     => 'xyz7', 
                        'contact_type'  => 'Individual',
                        'phone'         => '999999',
                        'phone_type'    => 'Phone',
                        'email'         => 'man7@yahoo.com',
                        'do_not_trade'  => 1,
                        'preferred_communication_method' => array(
                                                                  '2' => 1,
                                                                  '3' => 1,
                                                                  '4' => 1,
                                                                  )
                        );
        $contact =& civicrm_contact_add($params);
        $this->assertNotNull( $contact['contact_id'] );
        $this->_createdContacts[] = $contact['contact_id'];
    }
    
    function testCreateHouseholdDetails() 
    {
        $params = array('household_name' => 'abc8\'s House',
                        'nick_name'      => 'x House',
                        'email'          => 'man8@yahoo.com',
                        'contact_type'     => 'Household',
                        );
        $contact =& civicrm_contact_add($params);
        $this->assertNotNull( $contact['contact_id'] );
        $this->_createdContacts[] = $contact['contact_id'];
    }


    /**
     * Create contact without parameters.
     */
    function testCreateContactEmpty() {
        $params = array();
        $contact = &civicrm_contact_add( $params );
        $this->_verifyApiCallResult( $contact, $params, 'Input Parameters empty' );
    }

    /**
     * Create contact with bad contact type.
     */
    function testCreateContactBadType() {
        $params = array('email' => 'man1@yahoo.com',
                        'contact_type' => 'Does not Exist' );
        $contact = &civicrm_contact_add($params);
        $this->_verifyApiCallResult( $contact, $params, 'Invalid Contact Type: Does not Exist' );
    }

    /**
     * Create Individual without required fields.
     */    
    function testCreateIndividualBadRequiredFields() {
        $params = array('middle_name' => 'This field is not required for Individual',
                        'contact_type' => 'Individual' );
        $contact = &civicrm_contact_add($params);
        $this->_verifyApiCallResult( $contact, $params, 'Required fields not found for Individual first_name' );
    }

    /**
     * Create Household without required fields.
     */
    function testCreateHouseholdBadRequiredFields() {
        $params = array('middle_name' => 'This field is not required for Household',
                        'contact_type' => 'Household' );
        $contact = &civicrm_contact_add($params);
        $this->_verifyApiCallResult( $contact, $params, 'Required fields not found for Household ' );
    }

    /**
     * Create Organization without required fields.
     */
    function testCreateOrganizationBadRequiredFields() {
        $params = array('middle_name' => 'This field is not required for Organization',
                        'contact_type' => 'Organization' );
        $contact = &civicrm_contact_add($params);
        $this->_verifyApiCallResult( $contact, $params, 'Required fields not found for Organization ' );
    }



    // Private helper functions relevant only to this UnitTestCase

    private function _doCreateTest( $params ) {
        $paramsCopy = CRM_Utils_Array::array_deep_copy( $params );
        $contact = &civicrm_contact_add( $paramsCopy );

        $this->_verifyApiCallResult( $contact, $paramsCopy );
        $this->_verifyContactAttributes( $paramsCopy['contact_id'], $params );

        $this->_createdContacts[] = $contact['contact_id'];
    }

    private function _verifyApiCallResult( $returned, $modifiedParams, $error_message = false ) {

        //CRM_Core_Error::debug( 'c', $returned );

        $this->assertIsA( $returned, 'Array' );
        if( array_key_exists( 'contact_id', $modifiedParams ) && 
            array_key_exists( 'contact_id', $modifiedParams ) ) {
            $this->assertIdentical( $returned['contact_id'], $modifiedParams['contact_id'] );
        }
        if( $error_message === false ) {
            $this->assertEqual( $returned['is_error'], 0 );
            $this->assertNotNull( $returned['contact_id'] );
        } else {
            $this->assertEqual( $returned['is_error'], 1 );
            $this->assertEqual( $returned['error_message'], $error_message );
        }
    }
    
    private function _verifyContactAttributes( $contactId, $params ) {
        $retrievedId = array( 'contact_id' => $contactId );
        
        $returnProperties = array();
        $retrievedParams  = array();
        
        foreach ( $params as $name => $value ) {
            if ( $name == 'prefix' ) {
                $returnProperties['return.individual_prefix'] = 1;
            } elseif ( $name == 'suffix' ) {
                $returnProperties['return.individual_suffix'] = 1;
            } elseif ( $name == 'gender' ) {
                $returnProperties['return.gender'] = 1;
            } else {
                $returnProperties['return.'."{$name}"] = 1;
            }
        }
        
        if ( empty( $returnProperties ) ) {
            $retrievedParams = $retrievedId;
        } else {
            $retrievedParams = array_merge( $retrievedId, $returnProperties );
        }
        
        $retrieved = &civicrm_contact_get( $retrievedParams );
        
        if ( isset( $params['prefix'] ) && isset( $retrieved['individual_prefix'] ) ) {
            if ( is_numeric( $params['prefix'] ) ) {
                $retrieved['prefix'] = $retrieved['individual_prefix_id'];
            } else {
                $retrieved['prefix'] = $retrieved['individual_prefix']; 
            }
            unset( $retrieved['individual_prefix'] );
        }
        
        if ( isset( $params['suffix'] ) && isset( $retrieved['individual_suffix'] ) ) {
            if ( is_numeric( $params['suffix'] ) ) {
                $retrieved['suffix'] = $retrieved['individual_suffix_id']; 
            } else {
                $retrieved['suffix'] = $retrieved['individual_suffix']; 
            }
            unset( $retrieved['individual_suffix'] );
        }
        
        if ( isset( $params['gender'] ) && isset( $retrieved['gender'] ) ) {
            if ( is_numeric( $params['gender'] ) ) {
                unset( $retrieved['gender'] );
                $retrieved['gender'] = $retrieved['gender_id']; 
            }
        }
        
        //CRM_Core_Error::debug( 'c', $retrieved );
        // FIXME: Wanted to use assertIdentical, but $retrieved['contact_id']
        // is a string - is it intended?
        $this->assertEqual( $contactId, $retrieved['contact_id'] );
        $this->_assertAttributesEqual( $params, $retrieved );
        
    }
    
    private function _assertAttributesEqual( $params, $target ) {

//            CRM_Core_Error::debug( 'p', $params );
//            CRM_Core_Error::debug( 't', $target );

        foreach( $params as $paramName => $paramValue ) {
            if( isset( $target[$paramName] ) ) {
                $this->assertEqual( $paramValue, $target[$paramName] );
            } else {
                $this->fail( "Attribute $paramName not available in results, but present in API call parameters."  );
            }
        }        
    }



}



?>