<?php

/*******************************************************
 * This class generates data for the schema located in Contact.sql
 *
 * each public method generates data for the concerned table.
 * so for example the addContactDomain method generates and adds
 * data to the contact_domain table
 *
 * Data generation is a bit tricky since the data generated
 * randomly in one table could be used as a FKEY in another
 * table.
 *
 * In order to ensure that a randomly generated FKEY matches
 * a field in the referened table, the field in the referenced
 * table is always generated linearly.
 *
 *
 *
 *
 * Some numbers
 *
 * Domain ID's - 1 to NUM_DOMAIN
 *
 * Context - 3/domain
 *
 * Contact - 1 to NUM_CONTACT
 *           75% - Individual
 *           15% - Household
 *           10% - Organization
 *
 *           Contact to Domain distribution should be equal.
 *
 *
 * Contact Individual = 1 to 0.75*NUM_CONTACT
 *
 * Contact Household = 0.75*NUM_CONTACT to 0.9*NUM_CONTACT
 *
 * Contact Organization = 0.9*NUM_CONTACT to NUM_CONTACT
 *
 * Contact Location = 15% for Households, 10% for Organizations, (75-(15*4))% for Individuals.
 *                     (Assumption is that each household contains 4 individuals)
 *
 *******************************************************/


/*******************************************************
 *
 * Note: implication of using of mt_srand(1) in constructor
 * The data generated will be done in a consistent manner
 * so as to give the same data during each run (but this
 * would involve populating the entire db at one go - since
 * mt_srand(1) is in the constructor, if one needs to be able
 * to get consistent random numbers then the mt_srand(1) shld
 * be in each function that adds data to each table.
 *
 *******************************************************/


require_once '../civicrm.config.php';

require_once 'CRM/Core/Config.php';
require_once 'CRM/Core/Error.php';
require_once 'CRM/Core/I18n.php';

require_once 'CRM/Core/DAO/Address.php';
require_once 'CRM/Core/DAO.php';
require_once 'CRM/Core/DAO/Phone.php';
require_once 'CRM/Core/DAO/Email.php';
require_once 'CRM/Core/DAO/EntityTag.php';
require_once 'CRM/Core/DAO/Note.php';
require_once 'CRM/Core/DAO/Domain.php';

require_once 'CRM/Contact/DAO/Group.php';
require_once 'CRM/Contact/DAO/GroupContact.php';
require_once 'CRM/Contact/DAO/SubscriptionHistory.php';
require_once 'CRM/Contact/DAO/Contact.php';
require_once 'CRM/Contact/DAO/Relationship.php';

class CRM_GCD {

    /*******************************************************
     * constants
     *******************************************************/
    const DATA_FILENAME="sample_data.xml";

    const NUM_DOMAIN = 1;
    const NUM_CONTACT = 100;

    const INDIVIDUAL_PERCENT = 75;
    const HOUSEHOLD_PERCENT = 15;
    const ORGANIZATION_PERCENT = 10;
    const NUM_INDIVIDUAL_PER_HOUSEHOLD = 4;

    const NUM_ACTIVITY = 150;

    // relationship types from the table crm_relationship_type
    const CHILD_OF            = 1;
    const SPOUSE_OF           = 2;
    const SIBLING_OF          = 3;
    const HEAD_OF_HOUSEHOLD   = 6;
    const MEMBER_OF_HOUSEHOLD = 7;


    // location types from the table crm_location_type
    const HOME            = 1;
    const WORK            = 2;
    const MAIN            = 3;
    const OTHER           = 4;
    
    const ADD_TO_DB=TRUE;
    //const ADD_TO_DB=FALSE;
    const DEBUG_LEVEL=1;

    
    /*********************************
     * private members
     *********************************/
    
    // enum's from database
    private $preferredCommunicationMethod = array('1', '2', '3','4','5');
    private $greetingType = array('Formal', 'Informal', 'Honorific', 'Custom', 'Other');
    private $contactType = array('Individual', 'Household', 'Organization');
    private $phoneType = array('Phone', 'Mobile', 'Fax', 'Pager');    

    // customizable enums (foreign keys)
    private $prefix = array(1 => 'Mrs', 2 => 'Ms', 3 => 'Mr', 4 => 'Dr');
    private $suffix = array(1 => 'Jr', 2 => 'Sr');
    private $gender = array(1 => 'Female', 2 =>'Male');    

    // store domain id's
    private $domain = array();

    // store contact id's
    private $contact = array();
    private $individual = array();
    private $household = array();
    private $organization = array();
    

    // store names, firstnames, street 1, street2
    private $firstName = array();
    private $lastName = array();
    private $streetName = array();
    private $supplementalAddress1 = array();
    private $city = array();
    private $state = array();
    private $country = array();
    private $addressDirection = array();
    private $streetType = array();
    private $emailDomain = array();
    private $emailTLD = array();
    private $organizationName = array();
    private $organizationField = array();
    private $organizationType = array();
    private $group = array();
    private $note = array();
    private $activity_type = array();
    private $module = array();
    private $callback = array();
    private $party_registration = array();
    private $degree = array();
    private $school = array();

    // stores the strict individual id and household id to individual id mapping
    private $strictIndividual = array();
    private $householdIndividual = array();
    
    // sample data in xml format
    private $sampleData = NULL;
    
    // private vars
    private $numIndividual = 0;
    private $numHousehold = 0;
    private $numOrganization = 0;
    private $numStrictIndividual = 0;

    private $CSC = array(
                         1228 => array( // united states
                                       1004 => array ('San Francisco', 'Los Angeles', 'Palo Alto'), // california
                                       1031 => array ('New York', 'Albany'), // new york
                                       ),
                         1101 => array( // india
                                       1113 => array ('Mumbai', 'Pune', 'Nasik'), // maharashtra
                                       1114 => array ('Bangalore', 'Mangalore', 'Udipi'), // karnataka
                                       ),
                         1172 => array( // poland
                                       1115 => array ('Warszawa', 'Płock'), // mazowieckie
                                       1116 => array ('Gdańsk', 'Gdynia'), // pomorskie 
                                       ),
                         );
    
    private $groupMembershipStatus = array('Added', 'Removed', 'Pending');
    private $subscriptionHistoryMethod = array('Admin', 'Email');


  /*********************************
   * private methods
   *********************************/

    // get a randomly generated string
    private function _getRandomString($size=32)
    {
        $string = "";

        // get an ascii code for each character
        for($i=0; $i<$size; $i++) {
            $random_int = mt_rand(65,122);
            if(($random_int<97) && ($random_int>90)) {
                // if ascii code between 90 and 97 substitute with space
                $random_int=32;
            }
            $random_char=chr($random_int);
            $string .= $random_char;
        }
        return $string;
    }

    private function _getRandomChar()
    {
        return chr(mt_rand(65, 90));
    }        

    private function getRandomBoolean()
    {
        return mt_rand(0,1);

    }

    private function _getRandomElement(&$array1)
    {
        return $array1[mt_rand(1, count($array1))-1];
    }
    
    private function _getRandomIndex(&$array1)
    {
        return mt_rand(1, count($array1));
    }
    
    
    // country state city combo
    private function _getRandomCSC()
    {
        $array1 = array();

        // $c = array_rand($this->CSC);
        $c = 1228;

        // the state array now
        $s = array_rand($this->CSC[$c]);

        // the city
        $ci = array_rand($this->CSC[$c][$s]);
        $city = $this->CSC[$c][$s][$ci];

        $array1[] = $c;
        $array1[] = $s;
        $array1[] = $city;

        return $array1;
    }



    /**
     * Generate a random date. 
     *
     *   If both $startDate and $endDate are defined generate
     *   date between them.
     *
     *   If only startDate is specified then date generated is
     *   between startDate + 1 year.
     *
     *   if only endDate is specified then date generated is
     *   between endDate - 1 year.
     *
     *   if none are specified - date is between today - 1year 
     *   and today
     *
     * @param  int $startDate Start Date in Unix timestamp
     * @param  int $endDate   End Date in Unix timestamp
     * @access private
     * @return string randomly generated date in the format "Ymd"
     *
     */
    private function _getRandomDate($startDate=0, $endDate=0)
    {
        
        // number of seconds per year
        $numSecond = 31536000;
        $dateFormat = "Ymdhis";
        $today = time();

        // both are defined
        if ($startDate && $endDate) {
            return date($dateFormat, mt_rand($startDate, $endDate));
        }

        // only startDate is defined
        if ($startDate) {
            // $nextYear = mktime(0, 0, 0, date("m", $startDate),   date("d", $startDate),   date("Y")+1);
            return date($dateFormat, mt_rand($startDate, $startDate+$numSecond));
        }

        // only endDate is defined
        if ($startDate) {
            return date($dateFormat, mt_rand($endDate-$numSecond, $endDate));
        }        
        
        // none are defined
        return date($dateFormat, mt_rand($today-$numSecond, $today));
    }


    // insert data into db's
    private function _insert(&$dao)
    {
        if (self::ADD_TO_DB) {
            if (!$dao->insert()) {
                echo mysql_error() . "\n";
                exit(1);
            }
        }
    }

    // update data into db's
    private function _update($dao)
    {
        if (self::ADD_TO_DB) {
            if (!$dao->update()) {
                echo mysql_error() . "\n";
                exit(1);
            }
        }
    }


    /**
     * Insert a note 
     *
     *   Helper function which randomly populates "note" and 
     *   "date_modified" and inserts it.
     *
     * @param  CRM_DAO_Note DAO object for Note
     * @access private
     * @return none
     *
     */
    private function _insertNote($note) {
        $note->note = $this->_getRandomElement($this->note);
        $note->modified_date = $this->_getRandomDate();                
        $this->_insert($note);        
    }


    /*******************************************************
     *
     * Start of public functions
     *
     *******************************************************/
    // constructor
    function __construct()
    {

        // initialize all the vars
        $this->numIndividual = self::INDIVIDUAL_PERCENT * self::NUM_CONTACT / 100;
        $this->numHousehold = self::HOUSEHOLD_PERCENT * self::NUM_CONTACT / 100;
        $this->numOrganization = self::ORGANIZATION_PERCENT * self::NUM_CONTACT / 100;
        $this->numStrictIndividual = $this->numIndividual - ($this->numHousehold * self::NUM_INDIVIDUAL_PER_HOUSEHOLD);


    }

    public function parseDataFile()
    {

        $sampleData = simplexml_load_file(self::DATA_FILENAME);

        // first names
        foreach ($sampleData->first_names->first_name as $first_name) {
            $this->firstName[] = trim($first_name);
        }

        // last names
        foreach ($sampleData->last_names->last_name as $last_name) {
            $this->lastName[] = trim($last_name);
        }

        //  street names
        foreach ($sampleData->street_names->street_name as $street_name) {
            $this->streetName[] = trim($street_name);
        }

        //  supplemental address 1
        foreach ($sampleData->supplemental_addresses_1->supplemental_address_1 as $supplemental_address_1) {
            $this->supplementalAddress1[] = trim($supplemental_address_1);
        }

        //  cities
        foreach ($sampleData->cities->city as $city) {
            $this->city[] = trim($city);
        }

        //  address directions
        foreach ($sampleData->address_directions->address_direction as $address_direction) {
            $this->addressDirection[] = trim($address_direction);
        }

        // street types
        foreach ($sampleData->street_types->street_type as $street_type) {
            $this->streetType[] = trim($street_type);
        }

        // email domains
        foreach ($sampleData->email_domains->email_domain as $email_domain) {
            $this->emailDomain[] = trim($email_domain);
        }

        // email top level domain
        foreach ($sampleData->email_tlds->email_tld as $email_tld) {
            $this->emailTLD[] = trim($email_tld);
        }

        // organization name
        foreach ($sampleData->organization_names->organization_name as $organization_name) {
            $this->organization_name[] = trim($organization_name);
        }

        // organization field
        foreach ($sampleData->organization_fields->organization_field as $organization_field) {
            $this->organizationField[] = trim($organization_field);
        }

        // organization type
        foreach ($sampleData->organization_types->organization_type as $organization_type) {
            $this->organizationType[] = trim($organization_type);
        }

        // group
        foreach ($sampleData->groups->group as $group) {
            $this->group[] = trim($group);
        }

        // notes
        foreach ($sampleData->notes->note as $note) {
            $this->note[] = trim($note);
        }

        // activity type
        foreach ($sampleData->activity_types->activity_type as $activity_type) {
            $this->activity_type[] = trim($activity_type);
        }


        // module
        foreach ($sampleData->modules->module as $module) {
            $this->module[] = trim($module);
        }

        // callback
        foreach ($sampleData->callbacks->callback as $callback) {
            $this->callback[] = trim($callback);
        }

        // custom data - party registration
        foreach ($sampleData->party_registrations->party_registration as $party_registration) {
            $this->party_registration[] = trim($party_registration); 
        }

        // custom data - degrees
        foreach ($sampleData->degrees->degree as $degree) {
            $this->degree[] = trim($degree); 
        }

        // custom data - schools
        foreach ($sampleData->schools->school as $school) {
            $this->school[] = trim($school); 
        }

        // custom data - issue
        foreach ($sampleData->issue->status as $status) {
            $this->issue[] = trim($status); 
        }

        // custom data - gotv
        require_once 'CRM/Core/BAO/CustomOption.php';
        foreach ($sampleData->gotv->status as $status) {
            $this->gotv[] = CRM_Core_BAO_CustomOption::VALUE_SEPERATOR.trim($status).CRM_Core_BAO_CustomOption::VALUE_SEPERATOR; 
        }

        // custom data - marital_status
        foreach ($sampleData->marital_status->status as $status) {
            $this->marital_status[] = trim($status); 
        }
    }

    public function getContactType($id)
    {
        if(in_array($id, $this->individual))
            return 'Individual';
        if(in_array($id, $this->household))
            return 'Household';
        if(in_array($id, $this->organization))
            return 'Organization';
    }


    public function initDB()
    {
        $config = CRM_Core_Config::singleton();
    }


    /*******************************************************
     *
     * this function creates arrays for the following
     *
     * domain id
     * contact id
     * contact_location id
     * contact_contact_location id
     * contact_email uuid
     * contact_phone_uuid
     * contact_instant_message uuid
     * contact_relationship uuid
     * contact_task uuid
     * contact_note uuid
     *
     *******************************************************/
    public function initID()
    {

        // may use this function in future if needed to get
        // a consistent pattern of random numbers.

        // get the domain and contact id arrays
        $this->domain = range(1, self::NUM_DOMAIN);
        shuffle($this->domain);
        $this->contact = range(2, self::NUM_CONTACT + 1);
        shuffle($this->contact);

        // get the individual, household  and organizaton contacts
        $offset = 0;
        $this->individual = array_slice($this->contact, $offset, $this->numIndividual);
        $offset += $this->numIndividual;
        $this->household = array_slice($this->contact, $offset, $this->numHousehold);
        $offset += $this->numHousehold;
        $this->organization = array_slice($this->contact, $offset, $this->numOrganization);

        // get the strict individual contacts (i.e individual contacts not belonging to any household)
        $this->strictIndividual = array_slice($this->individual, 0, $this->numStrictIndividual);
        
        // get the household to individual mapping array
        $this->householdIndividual = array_diff($this->individual, $this->strictIndividual);
        $this->householdIndividual = array_chunk($this->householdIndividual, self::NUM_INDIVIDUAL_PER_HOUSEHOLD);
        $this->householdIndividual = array_combine($this->household, $this->householdIndividual);
    }


    /*******************************************************
     *
     * addDomain()
     *
     * This method adds NUM_DOMAIN domains and then adds NUM_REVISION
     * revisions for each domain with the latest revision being the last one..
     *
     *******************************************************/
    public function addDomain()
    {

        /* Add a location for domain 1 */
        // FIXME FOR NEW LOCATION BLOCK STRUCTURE
        // $this->_addLocation(self::MAIN, 1, true);

        $domain =& new CRM_Core_DAO_Domain();
        for ($id=2; $id<=self::NUM_DOMAIN; $id++) {
            // domain name is pretty simple. it is "Domain $id"
            $domain->name = "Domain $id";
            $domain->description = "Description $id";
            $domain->contact_name = $this->randomName();
            $domain->email_domain = 
                $this->_getRandomElement($this->emailDomain) . ".fixme";

            // insert domain
            $this->_insert($domain);
            // FIXME FOR NEW LOCATION BLOCK STRUCTURE
            // $this->_addLocation(self::MAIN, $id, true);
        }
    }
    
    public function randomName() {
        $prefix = $this->_getRandomIndex($this->prefix);
        $first_name = ucfirst($this->_getRandomElement($this->firstName));
        $middle_name = ucfirst($this->_getRandomChar());
        $last_name = ucfirst($this->_getRandomElement($this->lastName));
        $suffix = $this->_getRandomIndex($this->suffix);

        return $this->prefix[$prefix] . " $first_name $middle_name $last_name " .  $this->suffix[$suffix];
    }
    /*******************************************************
     *
     * addContact()
     *
     * This method adds data to the contact table
     *
     * id - from $contact
     * contact_type 'Individual' 'Household' 'Organization'
     * preferred_communication (random 1 to 3)
     *
     *******************************************************/
    public function addContact()
    {

        // add contacts
        $contact =& new CRM_Contact_DAO_Contact();

        for ($id=1; $id<=self::NUM_CONTACT; $id++) {
            $contact->contact_type = $this->getContactType($id+1);
            $contact->do_not_phone = mt_rand(0, 1);
            $contact->do_not_email = mt_rand(0, 1);
            $contact->do_not_post  = mt_rand(0, 1);
            $contact->do_not_trade = mt_rand(0, 1);
            $contact->preferred_communication_method = $this->_getRandomElement($this->preferredCommunicationMethod);
            $this->_insert($contact);
        }
    }


    /*******************************************************
     *
     * addIndividual()
     *
     * This method adds data to the contact_individual table
     *
     * The following fields are generated and added.
     *
     * contact_uuid - individual
     * contact_rid - latest one
     * first_name 'First Name $contact_uuid'
     * middle_name 'Middle Name $contact_uuid'
     * last_name 'Last Name $contact_uuid'
     * job_title 'Job Title $contact_uuid'
     * greeting_type - randomly select from the enum values
     * custom_greeting - "custom greeting $contact_uuid'
     *
     *******************************************************/
    public function addIndividual()
    {

        $contact =& new CRM_Contact_DAO_Contact();

        for ($id=1; $id<=$this->numIndividual; $id++) {
            $contact->first_name = ucfirst($this->_getRandomElement($this->firstName));
            $contact->middle_name = ucfirst($this->_getRandomChar());
            $contact->last_name = ucfirst($this->_getRandomElement($this->lastName));
            $contact->prefix_id = $this->_getRandomIndex($this->prefix);
            $contact->suffix_id = $this->_getRandomIndex($this->suffix);
            $contact->greeting_type = $this->_getRandomElement($this->greetingType);
            $contact->gender_id = $this->_getRandomIndex($this->gender);
            $contact->birth_date = date("Ymd", mt_rand(0, time()));
            $contact->is_deceased = mt_rand(0, 1);

            $contact->id = $this->individual[($id-1)];

            // also update the sort name for the contact id.
            $contact->display_name = trim( $this->prefix[$contact->prefix_id] . " $contact->first_name $contact->middle_name $contact->last_name " . $this->suffix[$contact->suffix_id] );
            $contact->sort_name = $contact->last_name . ', ' . $contact->first_name;
            $contact->hash = crc32($contact->sort_name);
            $this->_update($contact);
        }
    }


    /*******************************************************
     *
     * addHousehold()
     *
     * This method adds data to the contact_household table
     *
     * The following fields are generated and added.
     *
     * contact_uuid - household_individual
     * contact_rid - latest one
     * household_name 'household $contact_uuid primary contact $primary_contact_uuid'
     * nick_name 'nick $contact_uuid'
     * primary_contact_uuid = $household_individual[$contact_uuid][0];
     *
     *******************************************************/
    public function addHousehold()
    {

        $contact =& new CRM_Contact_DAO_Contact();
        for ($id=1; $id<=$this->numHousehold; $id++) {
            $cid = $this->household[($id-1)];
            $contact->primary_contact_id = $this->householdIndividual[$cid][0];

            // get the last name of the primary contact id
            $individual =& new CRM_Contact_DAO_Contact();
            $individual->id = $contact->primary_contact_id;
            $individual->find(true);
            $firstName = $individual->first_name;
            $lastName = $individual->last_name;

            // need to name the household and nick name appropriately
            $contact->household_name = "$firstName $lastName" . "'s home";
            $contact->nick_name = "$lastName" . "'s home";

            $contact->id = $this->household[($id-1)];
            // need to update the sort name for the main contact table
            $contact->display_name = $contact->sort_name = $contact->household_name;
            $contact->hash = crc32($contact->sort_name);
            $this->_update($contact);
        }
    }



    /*******************************************************
     *
     * addOrganization()
     *
     * This method adds data to the contact_organization table
     *
     * The following fields are generated and added.
     *
     * contact_uuid - organization
     * contact_rid - latest one
     * organization_name 'organization $contact_uuid'
     * legal_name 'legal  $contact_uuid'
     * nick_name 'nick $contact_uuid'
     * sic_code 'sic $contact_uuid'
     * primary_contact_id - random individual contact uuid
     *
     *******************************************************/
    public function addOrganization()
    {

        $contact =& new CRM_Contact_DAO_Contact();       

        for ($id=1; $id<=$this->numOrganization; $id++) {
            $contact->id = $this->organization[($id-1)];
            $name = $this->_getRandomElement($this->organization_name) . " " . $this->_getRandomElement($this->organization_field) . " " . $this->_getRandomElement($this->organization_type);
            $contact->organization_name = $name;
            $contact->primary_contact_id = $this->_getRandomElement($this->strict_individual);

            // need to update the sort name for the main contact table
            $contact->display_name = $contact->sort_name = $contact->organization_name;
            $contact->hash = crc32($contact->sort_name);
            $this->_update($contact);
        }
    }


    /*******************************************************
     *
     * addRelationship()
     *
     * This method adds data to the contact_relationship table
     *
     * it adds the following fields
     *
     *******************************************************/
    public function addRelationship()
    {

        $relationship =& new CRM_Contact_DAO_Relationship();

        $relationship->is_active = 1; // all active for now.

        foreach ($this->householdIndividual as $household_id => $household_member) {
            // add child_of relationship
            // 2 for each child
            $relationship->relationship_type_id = self::CHILD_OF;
            $relationship->contact_id_a = $household_member[2];
            $relationship->contact_id_b = $household_member[0];
            $this->_insert($relationship);
            $relationship->contact_id_a = $household_member[3];
            $relationship->contact_id_b = $household_member[0];
            $this->_insert($relationship);
            $relationship->contact_id_a = $household_member[2];
            $relationship->contact_id_b = $household_member[1];
            $this->_insert($relationship);
            $relationship->contact_id_a = $household_member[3];
            $relationship->contact_id_b = $household_member[1];
            $this->_insert($relationship);

            // add spouse_of relationship 1 for both the spouses
            $relationship->relationship_type_id = self::SPOUSE_OF;
            $relationship->contact_id_a = $household_member[1];
            $relationship->contact_id_b = $household_member[0];
            $this->_insert($relationship);

            // add sibling_of relationship 1 for both the siblings
            $relationship->relationship_type_id = self::SIBLING_OF;
            $relationship->contact_id_a = $household_member[3];
            $relationship->contact_id_b = $household_member[2];
            $this->_insert($relationship);

            // add head_of_household relationship 1 for head of house
            $relationship->relationship_type_id = self::HEAD_OF_HOUSEHOLD;
            $relationship->contact_id_a = $household_member[0];
            $relationship->contact_id_b = $household_id;
            $this->_insert($relationship);

            // add member_of_household relationship 3 for all other members
            $relationship->relationship_type_id = self::MEMBER_OF_HOUSEHOLD;
            $relationship->contact_id_a = $household_member[1];
            $this->_insert($relationship);
            $relationship->contact_id_a = $household_member[2];
            $this->_insert($relationship);
            $relationship->contact_id_a = $household_member[3];
            $this->_insert($relationship);
        }
    }


    /*******************************************************
     *
     * addLocation()
     *
     * This method adds data to the location table
     *
     *******************************************************/
    public function addLocation()
    {
        // strict individuals
        foreach ($this->strictIndividual as $contactId) {
            $this->_addLocation(self::HOME, $contactId);
        }
        
        //household
        foreach ($this->household as $contactId) {
            $this->_addLocation(self::HOME, $contactId);
        }
        
        //organization
        foreach ($this->organization as $contactId) {
            $this->_addLocation(self::MAIN, $contactId);
        }

        // some individuals.
        $someIndividual = array_diff($this->individual, $this->strictIndividual);
        $someIndividual = array_slice($someIndividual, 0, (int)(75*($this->numIndividual-$this->numStrictIndividual)/100));
        foreach ($someIndividual as $contactId) {
            $this->_addLocation(self::HOME, $contactId, false, true);
        }

    }

    private function _addLocation($locationTypeId, $contactId, $domain = false, $isPrimary = true)
    {
        $this->_addAddress( $locationTypeId, $contactId, $isPrimary );

        // add two phones for each location
        $this->_addPhone($locationTypeId, $contactId, 'Phone', $isPrimary);
        $this->_addPhone($locationTypeId, $contactId, 'Mobile', false);

        // need to get sort name to generate email id
        $contact =& new CRM_Contact_DAO_Contact();
        $contact->id = $contactId;
        $contact->find(true);
        // get the sort name of the contact
        $sortName  = $contact->sort_name;
        if ( ! empty( $sortName ) ) {
            // add 2 email for each location
            for ($emailId=1; $emailId<=2; $emailId++) {
                $this->_addEmail($locationTypeId, $contactId, $sortName, ($emailId == 1) && $isPrimary);
            }
        }
    }

    private function _addAddress($locationTypeId, $contactId, $isPrimary = false, $locationBlockID = null, $offset = 1)
    {
        $addressDAO =& new CRM_Core_DAO_Address();

        // add addresses now currently we are adding only 1 address for each location
        $addressDAO->location_type_id = $locationTypeId;
        $addressDAO->contact_id       = $contactId;
        $addressDAO->is_primary       = $isPrimary;

        $addressDAO->street_number = mt_rand(1, 1000);
        $addressDAO->street_number_suffix = ucfirst($this->_getRandomChar());
        $addressDAO->street_number_predirectional = $this->_getRandomElement($this->addressDirection);
        $addressDAO->street_name = ucwords($this->_getRandomElement($this->streetName));
        $addressDAO->street_type = $this->_getRandomElement($this->streetType);
        $addressDAO->street_number_postdirectional = $this->_getRandomElement($this->addressDirection);
        $addressDAO->street_address = $addressDAO->street_number_predirectional . " " . $addressDAO->street_number .  $addressDAO->street_number_suffix .  " " . $addressDAO->street_name .  " " . $addressDAO->street_type . " " . $addressDAO->street_number_postdirectional;
        $addressDAO->supplemental_address_1 = ucwords($this->_getRandomElement($this->supplementalAddress1));
        
        // some more random skips
        // hack add lat / long for US based addresses
        list( $addressDAO->country_id, $addressDAO->state_province_id, $addressDAO->city, 
              $addressDAO->postal_code, $addressDAO->geo_code_1, $addressDAO->geo_code_2 ) = 
            self::getZipCodeInfo( );

        $addressDAO->county_id = 1;
        
        $this->_insert($addressDAO);

    }

    private function _sortNameToEmail($sortName)
    {
        $email = preg_replace("([^a-zA-Z0-9_-]*)", "", $sortName);
        return $email;
    }

    private function _addPhone($locationTypeId, $contactId, $phoneType, $isPrimary=false, $locationBlockID = null, $offset = 1)
    {
        if ($contactId % 3) {
            $phone =& new CRM_Core_DAO_Phone();
            $phone->location_type_id = $locationTypeId;
            $phone->contact_id       = $contactId;
            $phone->is_primary = $isPrimary;
            $phone->phone = mt_rand(11111111, 99999999);
            $phone->phone_type = $phoneType;
            $this->_insert($phone);
        }
    }

    private function _addEmail($locationTypeId, $contactId, $sortName, $isPrimary=false, $locationBlockID = null, $offset = 1)
    {
        if ($contactId % 2) {
            $email =& new CRM_Core_DAO_Email();
            $email->location_type_id = $locationTypeId;
            $email->contact_id = $contactId;
            $email->is_primary = $isPrimary;
            
            $emailName = $this->_sortNameToEmail($sortName);
            $emailDomain = $this->_getRandomElement($this->emailDomain);
            $tld = $this->_getRandomElement($this->emailTLD);
            $email->email = strtolower( $emailName . "@" . $emailDomain . "." . $tld );
            $this->_insert($email);
        }

    }


    /*******************************************************
     *
     * addTagEntity()
     *
     * This method populates the crm_entity_tag table
     *
     *******************************************************/
    public function addEntityTag()
    {

        $entity_tag =& new CRM_Core_DAO_EntityTag();
        
        // add categories 1,2,3 for Organizations.
        for ($i=0; $i<$this->numOrganization; $i+=2) {
            $org_id = $this->organization[$i];
            // echo "org_id = $org_id\n";
            $entity_tag->contact_id = $this->organization[$i];
            $entity_tag->tag_id = mt_rand(1, 3);
            $this->_insert($entity_tag);
        }

        // add categories 4,5 for Individuals.        
        for ($i=0; $i<$this->numIndividual; $i+=2) {
            $entity_tag->contact_id = $this->individual[$i];
            if(($entity_tag->contact_id)%3) {
                $entity_tag->tag_id = mt_rand(4, 5);
                $this->_insert($entity_tag);
            } else {
                // some of the individuals are in both categories (4 and 5).
                $entity_tag->tag_id = 4;
                $this->_insert($entity_tag);                
                $entity_tag->tag_id = 5;
                $this->_insert($entity_tag);                
            }
        }
    }

    /*******************************************************
     *
     * addGroup()
     *
     * This method populates the crm_entity_tag table
     *
     *******************************************************/
    public function addGroup()
    {
        // add the 3 groups first
        $numGroup = count($this->group);
        require_once 'CRM/Contact/BAO/Group.php';
        for ($i=0; $i<$numGroup; $i++) {
            $group =& new CRM_Contact_BAO_Group();   
            $group->name       = $this->group[$i];
            $group->title      = $this->group[$i];
            $group->group_type = "12";
            $group->visibility = 'Public User Pages and Listings';
            $group->is_active  = 1;
            $group->save( );
            $group->buildClause( );
            $group->save( );
        }
        
        // 60 are for newsletter
        for ($i=0; $i<60; $i++) {
            $groupContact =& new CRM_Contact_DAO_GroupContact();
            $groupContact->group_id = 2;                                                     // newsletter subscribers
            $groupContact->contact_id = $this->individual[$i];
            $groupContact->status = $this->_getRandomElement($this->groupMembershipStatus);  // membership status


            $subscriptionHistory =& new CRM_Contact_DAO_SubscriptionHistory();
            $subscriptionHistory->contact_id = $groupContact->contact_id;

            $subscriptionHistory->group_id = $groupContact->group_id;
            $subscriptionHistory->status = $groupContact->status;
            $subscriptionHistory->method = $this->_getRandomElement($this->subscriptionHistoryMethod); // method
            $subscriptionHistory->date = $this->_getRandomDate();
            if ($groupContact->status != 'Pending') {
                $this->_insert($groupContact);
            }
            $this->_insert($subscriptionHistory);
        }

        // 15 volunteers
        for ($i=0; $i<15; $i++) {
            $groupContact =& new CRM_Contact_DAO_GroupContact();
            $groupContact->group_id = 3; // Volunteers
            $groupContact->contact_id = $this->individual[$i+60];
            $groupContact->status = $this->_getRandomElement($this->groupMembershipStatus);  // membership status

            $subscriptionHistory =& new CRM_Contact_DAO_SubscriptionHistory();
            $subscriptionHistory->contact_id = $groupContact->contact_id;
            $subscriptionHistory->group_id = $groupContact->group_id;
            $subscriptionHistory->status = $groupContact->status;
            $subscriptionHistory->method = $this->_getRandomElement($this->subscriptionHistoryMethod); // method
            $subscriptionHistory->date = $this->_getRandomDate();

            if ($groupContact->status != 'Pending') {
                $this->_insert($groupContact);
            }
            $this->_insert($subscriptionHistory);
        }

        // 8 advisory board group
        for ($i=0; $i<8; $i++) {
            $groupContact =& new CRM_Contact_DAO_GroupContact();
            $groupContact->group_id = 4; // advisory board group
            $groupContact->contact_id = $this->individual[$i*7];
            $groupContact->status = $this->_getRandomElement($this->groupMembershipStatus);  // membership status

            $subscriptionHistory =& new CRM_Contact_DAO_SubscriptionHistory();
            $subscriptionHistory->contact_id = $groupContact->contact_id;
            $subscriptionHistory->group_id = $groupContact->group_id;
            $subscriptionHistory->status = $groupContact->status;
            $subscriptionHistory->method = $this->_getRandomElement($this->subscriptionHistoryMethod); // method
            $subscriptionHistory->date = $this->_getRandomDate();

            if ($groupContact->status != 'Pending') {
                $this->_insert($groupContact);
            }
            $this->_insert($subscriptionHistory);
        }
    }



    
    /*******************************************************
     *
     * addNote()
     *
     * This method populates the crm_note table
     *
     *******************************************************/
    public function addNote()
    {

        $note =& new CRM_Core_DAO_Note();
        $note->entity_table = 'civicrm_contact';
        $note->contact_id   = 1;

        for ($i=0; $i<self::NUM_CONTACT; $i++) {
            $note->entity_id = $this->contact[$i];
            if ($this->contact[$i] % 5 || $this->contact[$i] % 3 || $this->contact[$i] % 2) {
                $this->_insertNote($note);
            }
        }
    }




    /*******************************************************
     *
     * addActivity()
     *
     * This method populates the crm_activity_history table
     *
     *******************************************************/
    public function addActivity( )
    {
        $contactDAO =& new CRM_Contact_DAO_Contact();
        $contactDAO->contact_type = 'Individual';
        $contactDAO->selectAdd();
        $contactDAO->selectAdd('id');
        $contactDAO->orderBy('sort_name');
        $contactDAO->find();
        
        $count = 0;
        
        while($contactDAO->fetch()) {
            if ($count++ > 2) {      
                break;
            }
            for ($i=0; $i<self::NUM_ACTIVITY; $i++) {
                require_once 'CRM/Activity/DAO/Activity.php';
                $activityDAO =& new CRM_Activity_DAO_Activity();
                $activityDAO->source_contact_id     = $contactDAO->id;
                $activityTypeID = mt_rand(1, 3);
                require_once 'CRM/Core/PseudoConstant.php';
                $activity = CRM_Core_PseudoConstant::activityType( ); 
                $activityDAO->activity_type_id = $activityTypeID;
                $activityDAO->subject = "Subject for $activity[$activityTypeID]";
                $activityDAO->activity_date_time = $this->_getRandomDate();
                $activityDAO->duration = mt_rand(1,6);
                $activityDAO->status_id = 2;
                $this->_insert($activityDAO);
                
                if ($activityTypeID < 4 ) { 
                    require_once 'CRM/Activity/DAO/ActivityTarget.php';
                    $activityTargetDAO =& new CRM_Activity_DAO_ActivityTarget();
                    $activityTargetDAO->activity_id = $activityDAO->id ;
                    $activityTargetDAO->target_contact_id = mt_rand(1,101);
                    $this->_insert($activityTargetDAO);
                }

                if ($activityTypeID <3 ) { 
                    require_once 'CRM/Activity/DAO/ActivityAssignment.php';
                    $activityAssignmentDAO =& new CRM_Activity_DAO_ActivityAssignment();
                    $activityAssignmentDAO->activity_id = $activityDAO->id ;
                    $activityAssignmentDAO->assignee_contact_id = mt_rand(1,101);
                    $this->_insert($activityAssignmentDAO);
                }
            }
        }
    }
    
    static function getZipCodeInfo( ) {
        static $stateMap;
        
        if ( ! isset( $stateMap ) ) {
            $query = 'SELECT id, abbreviation from civicrm_state_province where country_id = 1228';
            $dao =& new CRM_Core_DAO( );
            $dao->query( $query );
            $stateMap = array( );
            while ( $dao->fetch( ) ) {
                $stateMap[$dao->abbreviation] = $dao->id;
            }
        }

        $offset = mt_rand( 1, 43000 );
        $query = "SELECT city, state, zip, latitude, longitude FROM zipcodes LIMIT $offset, 1";
        $dao =& new CRM_Core_DAO( );
        $dao->query( $query );
        while ( $dao->fetch( ) ) {
            if ( $stateMap[$dao->state] ) {
                $stateID = $stateMap[$dao->state];
            } else {
                $stateID = 1004;
            }
            
            $zip = str_pad($dao->zip, 5, '0', STR_PAD_LEFT);
            return array( 1228, $stateID, $dao->city, $zip, $dao->latitude, $dao->longitude );
        }
    }

    static function getLatLong( $zipCode ) {
        $query     = "http://maps.google.com/maps?q=$zipCode&output=js";
        $userAgent = "Mozilla/5.0 (Macintosh; U; PPC Mac OS X Mach-O; en-US; rv:1.7.5) Gecko/20041107 Firefox/1.0";
        
        $ch        = curl_init( );
        curl_setopt( $ch, CURLOPT_URL, $query );
        curl_setopt( $ch, CURLOPT_HEADER, false);
        curl_setopt( $ch, CURLOPT_USERAGENT, $userAgent );
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        
        // grab URL and pass it to the browser
        $outstr = curl_exec($ch);
        
        // close CURL resource, and free up system resources
        curl_close($ch);
        
        $preg = "/'(<\?xml.+?)',/s";
        preg_match( $preg, $outstr, $matches );
        if ( $matches[1] ) {
            $xml = simplexml_load_string( $matches[1] );
            $attributes = $xml->center->attributes( );
            if ( !empty( $attributes ) ) {
                return array( (float ) $attributes['lat'], (float ) $attributes['lng'] );
            }
        }
        return array( null, null );
    }
    
    function addMembershipType()
    {
        $organizationDAO = new CRM_Contact_DAO_Contact();
        $organizationDAO->id = 5;
        $organizationDAO->find(true);
        $contact_id = $organizationDAO->contact_id;
        
        $membershipType = "INSERT INTO civicrm_membership_type
        (name, description, member_of_contact_id, contribution_type_id, minimum_fee, duration_unit, duration_interval, period_type, fixed_period_start_day, fixed_period_rollover_day, relationship_type_id, relationship_direction, visibility, weight, is_active)
        VALUES
        ('General', 'Regular annual membership.', ". $contact_id .", 3, 100, 'year', 1, 'rolling',null, null, 7, 'b_a', 'Public', 1, 1),
        ('Student', 'Discount membership for full-time students.', ". $contact_id .", 1, 50, 'year', 1, 'rolling', null, null, 7, 'b_a', 'Public', 2, 1),
        ('Lifetime', 'Lifetime membership.', ". $contact_id .", 2, 1200, 'lifetime', 1, 'rolling', null, null, 7, 'b_a', 'Admin', 3, 1);
        ";
        CRM_Core_DAO::executeQuery( $membershipType, CRM_Core_DAO::$_nullArray );      
    }
    
    function addMembership()
    {
        $contact = new CRM_Contact_DAO_Contact();
        $contact->query("SELECT id FROM civicrm_contact where contact_type = 'Individual'");
        while ( $contact->fetch() ) {
            $contacts[] = $contact->id;
        }
        shuffle($contacts);
        
        $randomContacts      = array_slice($contacts, 20, 30);
        
        $sources             = array( 'Payment', 'Donation', 'Check' );
        $membershipTypes     = array( 2, 1 );
        $membershipTypeNames = array( 'Student', 'General' );
        $statuses            = array( 3, 4 );
                
        $membership = " 
INSERT INTO civicrm_membership
        (contact_id, membership_type_id, join_date, start_date, end_date, source, status_id)
VALUES 
";
        $activity = "
INSERT INTO civicrm_activity
        (source_contact_id, source_record_id, activity_type_id, subject, activity_date_time, duration, location, phone_id, phone_number, details, priority_id,parent_id, is_test, status_id)
VALUES
";

        foreach( $randomContacts as $count => $dontCare ) {
            $source          = self::_getRandomElement($sources);
            $acititySourceId = $count +1;
            if (  ( ($count+1) % 11 == 0 ) ) {
                // lifetime membership, status can be anything
                $startDate = date( 'Y-m-d', mktime( 0, 0, 0, date('m'), ( date('d') - $count ), date('Y') ) );
                $membership .= "( {$randomContacts[$count]}, 3, '{$startDate}', '{$startDate}', null, '{$source}', 1)";
                $activity   .= "( {$randomContacts[$count]}, {$acititySourceId}, 7, 'Lifetime', '{$startDate} 00:00:00', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 2 )";
            } else if ( ($count+1) % 5 == 0 ) {
                // Grace or expired, memberhsip type is random of 1 & 2
                $randId         = array_rand( $membershipTypes );
                $membershipType = self::_getRandomElement($membershipTypes);
                $startDate      = date( 'Y-m-d', mktime( 0, 0, 0, 
                                                         date('m'), 
                                                         ( date('d') - ($count*($randId+1)*($randId+1)*($randId+1) ) ), 
                                                         ( date('Y') - ($randId+1) ) ) );
                $partOfDate     = explode( '-', $startDate );
                $endDate        = date( 'Y-m-d', mktime( 0, 0, 0, 
                                                         $partOfDate[1], 
                                                         ( $partOfDate[2] - 1 ),
                                                         ( $partOfDate[0] + ($randId + 1) ) )  );
                $membership .= "( {$randomContacts[$count]}, {$membershipType}, '{$startDate}', '{$startDate}', '{$endDate}', '{$source}', {$statuses[$randId]})";
                $activity   .= "( {$randomContacts[$count]}, {$acititySourceId}, 7, '{$membershipTypeNames[$randId]}', '{$startDate} 00:00:00', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 2 )";
            } else if ( ($count+1) % 2 == 0 ) {
                // membership type 2
                $startDate = date( 'Y-m-d', mktime( 0, 0, 0, date('m'), ( date('d') - $count ), date('Y') ) );
                $endDate   = date( 'Y-m-d', mktime( 0, 0, 0, date('m'), ( date('d') - $count ), ( date('Y') + 1) ) );
                $membership .= "( {$randomContacts[$count]}, 2, '{$startDate}', '{$startDate}', '{$endDate}', '{$source}', 1)";
                $activity   .= "( {$randomContacts[$count]}, {$acititySourceId}, 7, 'Student', '{$startDate} 00:00:00', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 2 )";
            } else {
                // membership type 1
                $startDate = date( 'Y-m-d', mktime( 0, 0, 0, date('m'), ( date('d') - $count ), date('Y') ) );
                $endDate   = date( 'Y-m-d', mktime( 0, 0, 0, date('m'), ( date('d') - $count ), ( date('Y') + 2) ) );
                $membership .= "( {$randomContacts[$count]}, 1, '{$startDate}', '{$startDate}', '{$endDate}', '{$source}', 1)";
                $activity   .= "( {$randomContacts[$count]}, {$acititySourceId}, 7, 'General', '{$startDate} 00:00:00', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 2 )";
            }
            
            if ( $count != 29 ) {
                $membership .= ",";
                $activity   .= ",";
            }
            
        }
            
        CRM_Core_DAO::executeQuery( $membership, CRM_Core_DAO::$_nullArray );

        CRM_Core_DAO::executeQuery( $activity,   CRM_Core_DAO::$_nullArray );
    }

    static function repairDate($date) {
        $dropArray = array('-' => '', ':' => '', ' ' => '');
        return strtr($date, $dropArray);
    }
    
    
    function addMembershipLog()
    {
        $membership = new CRM_Member_DAO_Membership();
        $membership->query("SELECT id FROM civicrm_membership");
        while ( $membership->fetch() ) {
            $ids[] = $membership->id;
        }
        require_once 'CRM/Member/DAO/MembershipLog.php';
        foreach ( $ids as $id) {
            $membership = new CRM_Member_DAO_Membership();
            $membership->id = $id;
            $membershipLog = new CRM_Member_DAO_MembershipLog();
            if ( $membership->find(true) ) {
                $membershipLog->membership_id = $membership->id;
                $membershipLog->status_id     = $membership->status_id;
                $membershipLog->start_date    = self::repairDate($membership->start_date);
                $membershipLog->end_date      = self::repairDate($membership->end_date);
                $membershipLog->modified_id   = $membership->contact_id;
                $membershipLog->modified_date = date("Ymd");
                $membershipLog->save();
            }
            $membershipLog = null;
        }
        
    }


    function addEventPage()
    {
        $event = "INSERT INTO civicrm_event_page
        ( event_id, intro_text, footer_text, confirm_title, confirm_text, confirm_footer_text, is_email_confirm, confirm_email_text, confirm_from_name, confirm_from_email, cc_confirm, bcc_confirm, default_fee_id, thankyou_title, thankyou_text, thankyou_footer_text, is_multiple_registrations)
      VALUES 
        ( 1, 'Fill in the information below to join as at this wonderful dinner event.', NULL, 'Confirm Your Registration Information', 'Review the information below carefully.', NULL, 1, 'Contact the Development Department if you need to make any changes to your registration.', 'Fundraising Dept.', 'development@example.org', NULL, NULL, 142, 'Thanks for Registering!', '<p>Thank you for your support. Your contribution will help us build even better tools.</p><p>Please tell your friends and colleagues about this wonderful event.</p>', '<p><a href=http://civicrm.org>Back to CiviCRM Home Page</a></p>', 0),
        ( 2, 'Complete the form below and click Continue to register online for the festival. Or you can register by calling us at 204 222-1000 ext 22.', '', 'Confirm Your Registration Information', '', '', 1, 'This email confirms your registration. If you have questions or need to change your registration - please do not hesitate to call us.', 'Event Dept.', 'events@example.org', '', NULL, 145, 'Thanks for Your Joining In!', '<p>Thank you for your support. Your participation will help build new parks.</p><p>Please tell your friends and colleagues about the concert.</p>', '<p><a href=http://civicrm.org>Back to CiviCRM Home Page</a></p>', 1),
        ( 3, 'Complete the form below to register your team for this year''s tournament.', '<em>A Soccer Youth Event</em>', 'Review and Confirm Your Registration Information', '', '<em>A Soccer Youth Event</em>', 1, 'Contact our Tournament Director for eligibility details.', 'Tournament Director', 'tournament@example.org', '', NULL, 148, 'Thanks for Your Support!', '<p>Thank you for your support. Your participation will help save thousands of acres of rainforest.</p>', '<p><a href=http://civicrm.org>Back to CiviCRM Home Page</a></p>', 0)
      ";
        CRM_Core_DAO::executeQuery( $event, CRM_Core_DAO::$_nullArray );      
    }


    function addEventAndLocation()
    {
        $event = "INSERT INTO civicrm_address ( contact_id, location_type_id, is_primary, is_billing, street_address, street_number, street_number_suffix, street_number_predirectional, street_name, street_type, street_number_postdirectional, street_unit, supplemental_address_1, supplemental_address_2, supplemental_address_3, city, county_id, state_province_id, postal_code_suffix, postal_code, usps_adc, country_id, geo_code_1, geo_code_2, timezone)
      VALUES
      ( NULL, 1, 1, 1, 'S 14S El Camino Way E', 14, 'S', NULL, 'El Camino', 'Way', NULL, NULL, NULL, NULL, NULL, 'Collinsville', NULL, 1006, NULL, '6022', NULL, 1228, 41.8328, -72.9253, NULL),
      ( NULL, 1, 1, 1, 'E 11B Woodbridge Path SW', 11, 'B', NULL, 'Woodbridge', 'Path', NULL, NULL, NULL, NULL, NULL, 'Dayton', NULL, 1034, NULL, '45417', NULL, 1228, 39.7531, -84.2471, NULL),
      ( NULL, 1, 1, 1, 'E 581O Lincoln Dr SW', 581, 'O', NULL, 'Lincoln', 'Dr', NULL, NULL, NULL, NULL, NULL, 'Santa Fe', NULL, 1030, NULL, '87594', NULL, 1228, 35.5212, -105.982, NULL)
      ";
        CRM_Core_DAO::executeQuery( $event, CRM_Core_DAO::$_nullArray ); 
        
        $sql = "SELECT id from civicrm_address where street_address = 'S 14S El Camino Way E'";
        $eventAdd1 = CRM_Core_DAO::singleValueQuery( $sql, CRM_Core_DAO::$_nullArray ); 
        $sql = "SELECT id from civicrm_address where street_address = 'E 11B Woodbridge Path SW'";
        $eventAdd2 = CRM_Core_DAO::singleValueQuery( $sql, CRM_Core_DAO::$_nullArray ); 
        $sql = "SELECT id from civicrm_address where street_address = 'E 581O Lincoln Dr SW'";
        $eventAdd3 = CRM_Core_DAO::singleValueQuery( $sql, CRM_Core_DAO::$_nullArray ); 
        
        $event = "INSERT INTO civicrm_email (contact_id, location_type_id, email, is_primary, is_billing, on_hold, hold_date, reset_date)
       VALUES
       (NULL, 1, 'development@example.org', 0, 0, 0, NULL, NULL),
       (NULL, 1, 'tournaments@example.org', 0, 0, 0, NULL, NULL),
       (NULL, 1, 'celebration@example.org', 0, 0, 0, NULL, NULL)
       ";
        CRM_Core_DAO::executeQuery( $event, CRM_Core_DAO::$_nullArray ); 
        
        $sql = "SELECT id from civicrm_email where email = 'development@example.org'";
        $eventEmail1 = CRM_Core_DAO::singleValueQuery( $sql, CRM_Core_DAO::$_nullArray ); 
        $sql = "SELECT id from civicrm_email where email = 'tournaments@example.org'";
        $eventEmail2 = CRM_Core_DAO::singleValueQuery( $sql, CRM_Core_DAO::$_nullArray ); 
        $sql = "SELECT id from civicrm_email where email = 'celebration@example.org'";
        $eventEmail3 = CRM_Core_DAO::singleValueQuery( $sql, CRM_Core_DAO::$_nullArray ); 
        
        $event = "INSERT INTO civicrm_phone (contact_id, location_type_id, is_primary, is_billing, mobile_provider_id, phone, phone_type)
       VALUES
       (NULL, 1, 0, 0, NULL,'204 222-1000', 'Phone'),
       (NULL, 1, 0, 0, NULL,'204 223-1000', 'Phone'),
       (NULL, 1, 0, 0, NULL,'303 323-1000', 'Phone')
       ";
        CRM_Core_DAO::executeQuery( $event, CRM_Core_DAO::$_nullArray );      
        
        $sql = "SELECT id from civicrm_phone where phone = '204 222-1000'";
        $eventPhone1 = CRM_Core_DAO::singleValueQuery( $sql, CRM_Core_DAO::$_nullArray ); 
        $sql = "SELECT id from civicrm_phone where phone = '204 223-1000'";
        $eventPhone2 = CRM_Core_DAO::singleValueQuery( $sql, CRM_Core_DAO::$_nullArray ); 
        $sql = "SELECT id from civicrm_phone where phone = '303 323-1000'";
        $eventPhone3 = CRM_Core_DAO::singleValueQuery( $sql, CRM_Core_DAO::$_nullArray ); 
        
        $event = "INSERT INTO civicrm_loc_block ( address_id, email_id, phone_id, address_2_id, email_2_id, phone_2_id)
       VALUES
      ( $eventAdd1, $eventEmail1, $eventPhone1, NULL,NULL,NULL),
      ( $eventAdd2, $eventEmail2, $eventPhone2, NULL,NULL,NULL),
      ( $eventAdd3, $eventEmail3, $eventPhone3, NULL,NULL,NULL)
       ";

        CRM_Core_DAO::executeQuery( $event, CRM_Core_DAO::$_nullArray ); 
        
        $sql = "SELECT id from civicrm_loc_block where phone_id = $eventPhone1 AND email_id = $eventEmail1 AND address_id = $eventAdd1";
        $eventLok1 = CRM_Core_DAO::singleValueQuery( $sql, CRM_Core_DAO::$_nullArray ); 
        $sql = "SELECT id from civicrm_loc_block where phone_id = $eventPhone2 AND email_id = $eventEmail2 AND address_id = $eventAdd2";
        $eventLok2 = CRM_Core_DAO::singleValueQuery( $sql, CRM_Core_DAO::$_nullArray ); 
        $sql = "SELECT id from civicrm_loc_block where phone_id = $eventPhone3 AND email_id = $eventEmail3 AND address_id = $eventAdd3";
        $eventLok3 = CRM_Core_DAO::singleValueQuery( $sql, CRM_Core_DAO::$_nullArray ); 
        
        $event = "INSERT INTO civicrm_event
        ( title, summary, description, event_type_id, participant_listing_id, is_public, start_date, end_date, is_online_registration, registration_link_text, max_participants, event_full_text, is_monetary, contribution_type_id, is_map, is_active, fee_label, is_show_location, loc_block_id)
        VALUES
        ( 'Fall Fundraiser Dinner', 'Kick up your heels at our Fall Fundraiser Dinner/Dance at Glen Echo Park! Come by yourself or bring a partner, friend or the entire family!', 'This event benefits our teen programs. Admission includes a full 3 course meal and wine or soft drinks. Grab your dancing shoes, bring the kids and come join the party!', 3, 1, 1, '2008-09-21 17:00:00', '2008-09-21 23:00:00', 1, 'Register Now', 100, 'Sorry! The Fall Fundraiser Dinner is full. Please call Jane at 204 222-1000 ext 33 if you want to be added to the waiting list.', 1, 4, 1, 1, 'Dinner Contribution', 1 ,$eventLok1),
        ( 'Summer Solstice Festival Day Concert', 'Festival Day is coming! Join us and help support your parks.', 'We will gather at noon, learn a song all together,  and then join in a joyous procession to the pavilion. We will be one of many groups performing at this wonderful concert which benefits our city parks.', 5, 1, 1, '2007-11-17 12:00:00', '2008-11-17 17:00:00', 1, 'Register Now', 50, 'We have all the singers we can handle. Come to the pavilion anyway and join in from the audience.', 1, 2, NULL, 1, 'Festival Fee', 1, $eventLok2),
        ( 'Rain-forest Cup Youth Soccer Tournament', 'Sign up your team to participate in this fun tournament which benefits several Rain-forest protection groups in the Amazon basin.', 'This is a FYSA Sanctioned Tournament, which is open to all USSF/FIFA affiliated organizations for boys and girls in age groups: U9-U10 (6v6), U11-U12 (8v8), and U13-U17 (Full Sided).', 3, 1, 1, '2008-07-27 07:00:00', '2008-07-29 17:00:00', 1, 'Register Now', 500, 'Sorry! All available team slots for this tournament have been filled. Contact Jill Futbol for information about the waiting list and next years event.', 1, 4, NULL, 1, 'Tournament Fees',1, $eventLok3)
         ";
        CRM_Core_DAO::executeQuery( $event, CRM_Core_DAO::$_nullArray );      
     
    }
    
    function addEventFeeLabel()
    {
        $optionGroup = "INSERT INTO civicrm_option_group ( name, is_reserved, is_active)
      VALUES
      ( 'civicrm_event_page.amount.1', 0, 1),
      ( 'civicrm_event_page.amount.2', 0, 1),
      ( 'civicrm_event_page.amount.3', 0, 1)
";
        CRM_Core_DAO::executeQuery( $optionGroup, CRM_Core_DAO::$_nullArray );
        
        
        $sql = "SELECT max(id) from civicrm_option_group where name = 'civicrm_event_page.amount.1'";
        $page1 = CRM_Core_DAO::singleValueQuery( $sql, CRM_Core_DAO::$_nullArray ); 

        $sql = "SELECT max(id) from civicrm_option_group where name = 'civicrm_event_page.amount.2'";
        $page2 = CRM_Core_DAO::singleValueQuery( $sql, CRM_Core_DAO::$_nullArray ); 

        $sql = "SELECT max(id) from civicrm_option_group where name = 'civicrm_event_page.amount.3'";
        $page3 = CRM_Core_DAO::singleValueQuery( $sql, CRM_Core_DAO::$_nullArray ); 


        $optionValue = "INSERT INTO civicrm_option_value (option_group_id, label, value, is_default, weight, is_optgroup, is_reserved, is_active)
      VALUES
      ($page1, 'Single', '50', 0, 1, 0, 0, 1),
      ($page1, 'Couple', '100', 0, 2, 0, 0, 1),
      ($page1, 'Family', '200', 0, 3, 0, 0, 1),
      ($page2, 'Bass', '25', 0, 1, 0, 0, 1),
      ($page2, 'Tenor', '40', 0, 2, 0, 0, 1),
      ($page2, 'Soprano', '50', 0, 3, 0, 0, 1),
      ($page3, 'Tiny-tots (ages 5-8)', '800', 0, 1, 0, 0, 1),
      ($page3, 'Junior Stars (ages 9-12)', '1000', 0, 2, 0, 0, 1),
      ($page3, 'Super Stars (ages 13-18)', '1500', 0, 3, 0, 0, 1)";

        CRM_Core_DAO::executeQuery( $optionValue, CRM_Core_DAO::$_nullArray );
    }


    function addParticipant()
    {
        $contact = new CRM_Contact_DAO_Contact();
        $contact->query("SELECT id FROM civicrm_contact");
        while ( $contact->fetch() ) {
            $contacts[] = $contact->id;
        }
        shuffle($contacts);
        $randomContacts = array_slice($contacts, 20, 50);
        
        $participant = "
INSERT INTO civicrm_participant
        (contact_id, event_id, status_id, role_id, register_date, source, fee_level, is_test, fee_amount)
VALUES
        ( ". $randomContacts[0]  .", 1, 1, 1, '2006-01-21', 'Check', 'Single', 0, 50),
        ( ". $randomContacts[1]  .", 2, 2, 2, '2005-05-07', 'Credit Card', 'Soprano', 0, 50),
        ( ". $randomContacts[2]  .", 3, 3, 3, '2005-05-05', 'Credit Card', 'Tiny-tots (ages 5-8)', 0, 800) ,
        ( ". $randomContacts[3]  .", 1, 4, 4, '2005-10-21', 'Direct Transfer', 'Single', 0, 50),
        ( ". $randomContacts[4]  .", 1, 1, 1, '2005-01-10', 'Check', 'Soprano', 0, 50),
        ( ". $randomContacts[5]  .", 2, 2, 2, '2005-03-05', 'Direct Transfer', 'Tiny-tots (ages 5-8)', 0, 800),
        ( ". $randomContacts[6]  .", 3, 3, 3, '2006-07-21', 'Direct Transfer', 'Single', 0, 50),
        ( ". $randomContacts[7]  .", 1, 4, 4, '2006-03-07', 'Credit Card', 'Soprano', 0, 50),
        ( ". $randomContacts[8]  .", 3, 1, 1, '2005-02-05', 'Direct Transfer', 'Tiny-tots (ages 5-8)', 0, 800),
        ( ". $randomContacts[9]  .", 1, 2, 2, '2005-02-01', 'Check', 'Single', 0, 50),
        ( ". $randomContacts[10]  .", 2, 3, 3, '2006-01-10', 'Direct Transfer', 'Soprano', 0, 50),
        ( ". $randomContacts[11]  .", 3, 4, 4, '2006-03-06', 'Credit Card', 'Tiny-tots (ages 5-8)', 0, 800),
        ( ". $randomContacts[12]  .", 1, 1, 2, '2005-06-04', 'Credit Card', 'Single', 0, 50),
        ( ". $randomContacts[13]  .", 2, 2, 3, '2004-01-10', 'Direct Transfer', 'Soprano', 0, 50),
        ( ". $randomContacts[14]  .", 2, 4, 1, '2005-07-04', 'Check', 'Tiny-tots (ages 5-8)', 0, 800),
        ( ". $randomContacts[15]  .", 1, 4, 2, '2006-01-21', 'Credit Card', 'Single', 0, 50),
        ( ". $randomContacts[16]  .", 2, 2, 3, '2005-01-10', 'Credit Card', 'Soprano', 0, 50),
        ( ". $randomContacts[17]  .", 3, 3, 1, '2006-03-05', 'Credit Card', 'Tiny-tots (ages 5-8)', 0, 800),
        ( ". $randomContacts[18]  .", 1, 2, 1, '2005-10-21', 'Direct Transfer', 'Single', 0, 50),
        ( ". $randomContacts[19]  .", 2, 4, 1, '2006-01-10', 'Credit Card', 'Soprano', 0, 50),
        ( ". $randomContacts[20]  .", 2, 1, 4, '2005-03-25', 'Check', 'Tiny-tots (ages 5-8)', 0, 800),
        ( ". $randomContacts[21]  .", 1, 2, 3, '2006-10-21', 'Direct Transfer', 'Single', 0, 50),
        ( ". $randomContacts[22]  .", 2, 4, 1, '2005-01-10', 'Direct Transfer', 'Soprano', 0, 50),
        ( ". $randomContacts[23]  .", 2, 3, 1, '2005-03-11', 'Credit Card', 'Tiny-tots (ages 5-8)', 0, 800),
        ( ". $randomContacts[24]  .", 3, 2, 2, '2005-04-05', 'Direct Transfer', 'Tiny-tots (ages 5-8)', 0, 800),
        ( ". $randomContacts[25]  .", 1, 1, 1, '2006-01-21', 'Check', 'Single', 0, 50),
        ( ". $randomContacts[26]  .", 2, 2, 2, '2007-05-07', 'Credit Card', 'Soprano', 0, 50),
        ( ". $randomContacts[27]  .", 3, 3, 3, '2007-05-05', 'Direct Transfer', 'Tiny-tots (ages 5-8)', 0, 800),
        ( ". $randomContacts[28]  .", 1, 4, 4, '2007-10-21', 'Credit Card', 'Single', 0, 50),
        ( ". $randomContacts[29]  .", 1, 1, 1, '2007-01-10', 'Direct Transfer', 'Soprano', 0, 50),
        ( ". $randomContacts[30]  .", 2, 2, 2, '2007-03-05', 'Credit Card', 'Tiny-tots (ages 5-8)', 0, 800),
        ( ". $randomContacts[31]  .", 3, 3, 3, '2006-07-21', 'Check', 'Single', 0, 50),
        ( ". $randomContacts[32]  .", 1, 4, 4, '2006-03-07', 'Direct Transfer', 'Soprano', 0, 50),
        ( ". $randomContacts[33]  .", 3, 1, 1, '2007-02-05', 'Credit Card', 'Tiny-tots (ages 5-8)', 0, 800),
        ( ". $randomContacts[34]  .", 1, 2, 2, '2007-02-01', 'Direct Transfer', 'Single', 0, 50),
        ( ". $randomContacts[35]  .", 2, 3, 3, '2006-01-10', 'Direct Transfer', 'Soprano', 0, 50),
        ( ". $randomContacts[36]  .", 3, 4, 4, '2006-03-06', 'Check', 'Tiny-tots (ages 5-8)', 0, 800),
        ( ". $randomContacts[37]  .", 1, 1, 2, '2007-06-04', 'Direct Transfer', 'Single', 0, 50),
        ( ". $randomContacts[38]  .", 2, 2, 3, '2004-01-10', 'Direct Transfer', 'Soprano', 0, 50),
        ( ". $randomContacts[39]  .", 2, 4, 1, '2007-07-04', 'Credit Card', 'Tiny-tots (ages 5-8)', 0, 800),
        ( ". $randomContacts[40]  .", 1, 4, 2, '2006-01-21', 'Credit Card', 'Single', 0, 50),
        ( ". $randomContacts[41]  .", 2, 2, 3, '2007-01-10', 'Credit Card', 'Soprano', 0, 50),
        ( ". $randomContacts[42]  .", 3, 3, 1, '2006-03-05', 'Credit Card', 'Tiny-tots (ages 5-8)', 0, 800),
        ( ". $randomContacts[43]  .", 1, 2, 1, '2007-10-21', 'Direct Transfer', 'Single', 0, 50),
        ( ". $randomContacts[44]  .", 2, 4, 1, '2006-01-10', 'Direct Transfer', 'Soprano', 0, 50),
        ( ". $randomContacts[45]  .", 2, 1, 4, '2007-03-25', 'Check', 'Tiny-tots (ages 5-8)', 0, 800),
        ( ". $randomContacts[46]  .", 1, 2, 3, '2006-10-21', 'Credit Card', 'Single', 0, 50),
        ( ". $randomContacts[47]  .", 2, 4, 1, '2007-01-10', 'Credit Card', 'Soprano', 0, 50),
        ( ". $randomContacts[48]  .", 2, 3, 1, '2007-03-11', 'Credit Card', 'Tiny-tots (ages 5-8)', 0, 800),
        ( ". $randomContacts[49]  .", 3, 2, 2, '2007-04-05', 'Check', 'Tiny-tots (ages 5-8)', 0, 800);
";
        CRM_Core_DAO::executeQuery( $participant, CRM_Core_DAO::$_nullArray );
   
        $query = "
INSERT INTO civicrm_activity
    (source_contact_id, source_record_id, activity_type_id, subject, activity_date_time, duration, location, phone_id, phone_number, details, priority_id,parent_id, is_test, status_id)
VALUES
    ($randomContacts[0], 01, 5, 'NULL', '2006-01-21 00:00:00', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 2 ),
    ($randomContacts[1], 02, 5, 'NULL', '2005-05-07 00:00:00', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 2 ),
    ($randomContacts[2], 03, 5, 'NULL', '2005-05-05 00:00:00', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 2 ),
    ($randomContacts[3], 04, 5, 'NULL', '2005-10-21 00:00:00', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 2 ),
    ($randomContacts[4], 05, 5, 'NULL', '2005-01-10 00:00:00', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 2 ),
    ($randomContacts[5], 06, 5, 'NULL', '2005-03-05 00:00:00', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 2 ),
    ($randomContacts[6], 07, 5, 'NULL', '2006-07-21 00:00:00', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 2 ),
    ($randomContacts[7], 08, 5, 'NULL', '2006-03-07 00:00:00', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 2 ),
    ($randomContacts[8], 09, 5, 'NULL', '2005-02-05 00:00:00', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 2 ),
    ($randomContacts[9], 10, 5, 'NULL', '2005-02-01 00:00:00', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 2 ),
    ($randomContacts[10], 11, 5, 'NULL', '2006-01-10 00:00:00', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 2 ),
    ($randomContacts[11], 12, 5, 'NULL', '2006-03-06 00:00:00', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 2 ),
    ($randomContacts[12], 13, 5, 'NULL', '2005-06-04 00:00:00', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 2 ),
    ($randomContacts[13], 14, 5, 'NULL', '2004-01-10 00:00:00', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 2 ),
    ($randomContacts[14], 15, 5, 'NULL', '2005-07-04 00:00:00', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 2 ),
    ($randomContacts[15], 16, 5, 'NULL', '2006-01-21 00:00:00', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 2 ),
    ($randomContacts[16], 17, 5, 'NULL', '2005-01-10 00:00:00', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 2 ),
    ($randomContacts[17], 18, 5, 'NULL', '2006-03-05 00:00:00', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 2 ),
    ($randomContacts[18], 19, 5, 'NULL', '2005-10-21 00:00:00', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 2 ),
    ($randomContacts[19], 20, 5, 'NULL', '2006-01-10 00:00:00', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 2 ),
    ($randomContacts[20], 21, 5, 'NULL', '2005-03-25 00:00:00', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 2 ),
    ($randomContacts[21], 22, 5, 'NULL', '2006-10-21 00:00:00', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 2 ),
    ($randomContacts[22], 23, 5, 'NULL', '2005-01-10 00:00:00', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 2 ),
    ($randomContacts[23], 24, 5, 'NULL', '2005-03-11 00:00:00', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 2 ),
    ($randomContacts[24], 25, 5, 'NULL', '2005-04-05 00:00:00', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 2 ),
    ($randomContacts[25], 26, 5, 'NULL', '2006-01-21 00:00:00', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 2 ),
    ($randomContacts[26], 27, 5, 'NULL', '2007-05-07 00:00:00', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 2 ),
    ($randomContacts[27], 28, 5, 'NULL', '2005-05-05 00:00:00', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 2 ),
    ($randomContacts[28], 29, 5, 'NULL', '2007-10-21 00:00:00', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 2 ),
    ($randomContacts[29], 30, 5, 'NULL', '2007-01-10 00:00:00', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 2 ),
    ($randomContacts[30], 31, 5, 'NULL', '2007-03-05 00:00:00', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 2 ),
    ($randomContacts[31], 32, 5, 'NULL', '2006-07-21 00:00:00', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 2 ),
    ($randomContacts[32], 33, 5, 'NULL', '2006-03-07 00:00:00', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 2 ),
    ($randomContacts[33], 34, 5, 'NULL', '2007-02-05 00:00:00', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 2 ),
    ($randomContacts[34], 35, 5, 'NULL', '2007-02-01 00:00:00', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 2 ),
    ($randomContacts[35], 36, 5, 'NULL', '2006-01-10 00:00:00', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 2 ),
    ($randomContacts[36], 37, 5, 'NULL', '2006-03-06 00:00:00', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 2 ),
    ($randomContacts[37], 38, 5, 'NULL', '2007-06-04 00:00:00', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 2 ),
    ($randomContacts[38], 39, 5, 'NULL', '2004-01-10 00:00:00', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 2 ),
    ($randomContacts[39], 40, 5, 'NULL', '2007-07-04 00:00:00', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 2 ),
    ($randomContacts[40], 41, 5, 'NULL', '2006-01-21 00:00:00', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 2 ),
    ($randomContacts[41], 42, 5, 'NULL', '2007-01-10 00:00:00', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 2 ),
    ($randomContacts[42], 43, 5, 'NULL', '2006-03-05 00:00:00', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 2 ),
    ($randomContacts[43], 44, 5, 'NULL', '2007-10-21 00:00:00', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 2 ),
    ($randomContacts[44], 45, 5, 'NULL', '2006-01-10 00:00:00', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 2 ),
    ($randomContacts[45], 46, 5, 'NULL', '2007-03-25 00:00:00', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 2 ),
    ($randomContacts[46], 47, 5, 'NULL', '2006-10-21 00:00:00', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 2 ),
    ($randomContacts[47], 48, 5, 'NULL', '2006-01-10 00:00:00', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 2 ),
    ($randomContacts[48], 49, 5, 'NULL', '2007-03-11 00:00:00', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 2 ),
    ($randomContacts[49], 50, 5, 'NULL', '2007-04-05 00:00:00', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 2 )
    ";
        CRM_Core_DAO::executeQuery( $query, CRM_Core_DAO::$_nullArray );
 
    }
    
    function addContribution( ) 
    {
        $query = "
INSERT INTO civicrm_contribution
    (contact_id, contribution_type_id, payment_instrument_id, receive_date, non_deductible_amount, total_amount, trxn_id, currency, cancel_date, cancel_reason, receipt_date, thankyou_date, source)
VALUES
    (2, 1, 4, '2007-04-11 00:00:00', 0.00, 125.00, 'check #1041', 'USD', NULL, NULL, NULL, NULL, 'Apr 2007 Mailer 1'),
    (4, 1, 1, '2007-03-21 00:00:00', 0.00, 50.00, 'P20901X1', 'USD', NULL, NULL, NULL, NULL, 'Online: Save the Penguins'),
    (6, 1, 4, '2007-04-29 00:00:00', 0.00, 25.00, 'check #2095', 'USD', NULL, NULL, NULL, NULL, 'Apr 2007 Mailer 1'),
    (8, 1, 4, '2007-04-11 00:00:00', 0.00, 50.00, 'check #10552', 'USD', NULL, NULL, NULL, NULL, 'Apr 2007 Mailer 1'),
    (16, 1, 4, '2007-04-15 00:00:00', 0.00, 500.00, 'check #509', 'USD', NULL, NULL, NULL, NULL, 'Apr 2007 Mailer 1'),
    (19, 1, 4, '2007-04-11 00:00:00', 0.00, 175.00, 'check #102', 'USD', NULL, NULL, NULL, NULL, 'Apr 2007 Mailer 1'),
    (82, 1, 1, '2007-03-27 00:00:00', 0.00, 50.00, 'P20193L2', 'USD', NULL, NULL, NULL, NULL, 'Online: Save the Penguins'),
    (92, 1, 1, '2007-03-08 00:00:00', 0.00, 10.00, 'P40232Y3', 'USD', NULL, NULL, NULL, NULL, 'Online: Save the Penguins'),
    (34, 1, 1, '2007-04-22 00:00:00', 0.00, 250.00, 'P20193L6', 'USD', NULL, NULL, NULL, NULL, 'Online: Save the Penguins');
";
        CRM_Core_DAO::executeQuery( $query, CRM_Core_DAO::$_nullArray );
        
        $query = "
INSERT INTO civicrm_activity
    (source_contact_id, source_record_id, activity_type_id, subject, activity_date_time, duration, location, phone_id, phone_number, details, priority_id,parent_id, is_test, status_id)
VALUES
    (2, 1, 6, '$ 125.00-Apr 2007 Mailer 1', '2007-04-11 00:00:00', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 2 ),
    (4, 2, 6, '$ 50.00-Online: Save the Penguins', '2007-03-21 00:00:00', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 2 ),
    (6, 3, 6, '$ 25.00-Apr 2007 Mailer 1', '2007-04-29 00:00:00', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 2 ),
    (8, 4, 6, '$ 50.00-Apr 2007 Mailer 1', '2007-04-11 00:00:00', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 2 ),
    (16, 5, 6, '$ 500.00-Apr 2007 Mailer 1', '2007-04-15 00:00:00', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 2 ),
    (19, 6, 6, '$ 175.00-Apr 2007 Mailer 1', '2007-04-11 00:00:00', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 2 ),
    (82, 7, 6, '$ 50.00-Online: Save the Penguins', '2007-03-27 00:00:00', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 2 ),
    (92, 8, 6, '$ 10.00-Online: Save the Penguins', '2007-03-08 00:00:00', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 2 ),
    (34, 9, 6, '$ 250.00-Online: Save the Penguins', '2007-04-22 00:00:00', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 2 );
    ";
        CRM_Core_DAO::executeQuery( $query, CRM_Core_DAO::$_nullArray );
    }

    function addPledge( )
    {
        $pledge = "INSERT INTO civicrm_pb_pledge
        ( creator_name, creator_pledge_desc, signers_limit, signer_description_text, signer_pledge_desc, deadline, url_reference, description, creator_id, creator_description, created_date, loc_block_id, is_active )
      VALUES 
        ( 'I', 'donate $200 towards defending WikiLeaks in their first amendment fight', 10, 'other good people', 'give $20 to $200 dollars and encourage others to do the same!', '2009-06-30 00:00:00', NULL, NULL, 76, NULL, '2008-06-23 17:57:28', NULL, 1 ),
        ( 'My organisation', 'give at least 20 pounds to the Ebbsfleet United playing budget for 2008-09', 1000, 'other people',  'do the same', '2009-06-23 00:00:00', NULL, NULL, 90, NULL, '2008-06-23 18:08:32', NULL, 1 ),
        ( 'I',  'donate my old laptop to a charity', 10, 'other people', 'do the same', '2009-06-30 00:00:00', NULL, NULL, 40, NULL, '2008-06-23 18:12:38', NULL, 1 );
      ";
        CRM_Core_DAO::executeQuery( $pledge, CRM_Core_DAO::$_nullArray );      
    }
    
    function addPledgeSigner( )
    {
        $pledgeSigner = "INSERT INTO civicrm_pb_signer
        ( contact_id,  pledge_id, is_anonymous, email_id, is_done, signing_date, is_test)
      VALUES
        ( 19, 1, 1, 42, 1, '2008-06-23 18:18:58', 0 ),
        (  9, 2, 0,  8, 1, '2008-06-23 00:00:00', 0 ),
        ( 73, 3, 0,  4, 0, '2008-06-23 18:20:16', 0 );
      ";
        CRM_Core_DAO::executeQuery( $pledgeSigner, CRM_Core_DAO::$_nullArray );      
    }
    
}

function user_access( $str = null ) {
    return true;
}

function module_list( ) {
    return array( );
}


echo("Starting data generation on " . date("F dS h:i:s A") . "\n");
$obj1 =& new CRM_GCD();
$obj1->initID();
$obj1->parseDataFile();
$obj1->initDB();
$obj1->addDomain();
$obj1->addContact();
$obj1->addIndividual();
$obj1->addHousehold();
$obj1->addOrganization();
$obj1->addRelationship();
$obj1->addLocation();
$obj1->addEntityTag();
$obj1->addGroup();
$obj1->addNote();
$obj1->addActivity();
$obj1->addMembership();
$obj1->addMembershipLog();
$obj1->addEventFeeLabel();
$obj1->addEventAndLocation();
$obj1->addEventPage();
$obj1->addParticipant();
$obj1->addContribution();
$obj1->addPledge();
$obj1->addPledgeSigner();
echo("Ending data generation on " . date("F dS h:i:s A") . "\n");


