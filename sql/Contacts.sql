
/*******************************************************
*
* create_schema.sql
*
*    This script creates the schema for CRM module
*    (contact relationship management system).
*
*******************************************************/

/*******************************************************
*
* CREATE DATABASE XXX DEFAULT CHARACTER SET utf8 COLLATION utf8_bin
* the above params are important for mysql4.1 else we get errors
* with pma
* *******************************************************/

DROP DATABASE IF EXISTS crm;
CREATE DATABASE crm DEFAULT CHARACTER SET utf8 COLLATE utf8_bin;
use crm;

/*******************************************************
* Standard sizes for various VARCHAR.
* GOAL: 
* 255 is a waste of space and resource. use reasonable
* sizes
* Standardize on 10 sizes (approx)
* Implement size check constraints at the DAO level
* of the application. So the DAO knows the type and size
* if it is a VARCHAR or CHAR
*
* sizes being considered (anything of size less than 4 should be a CHAR!)
*
* SIZE_4   (state codes, country codes)
* SIZE_8   (prefixes, suffixes, sic_code)
* SIZE_12  (postal codes)
* SIZE_16  (phone numbers, UTM)
* SIZE_32  (legal identifier, usps_adc
* SIZE_64  (all names, email, job_title)
* SIZE_96  (street_address)
* SIZE_128 (url, display_name, custom_greeting, household name)
* SIZE_255 (descriptions, source)
*
*/

/*******************************************************
*
* CREATE TABLES
*
*******************************************************/

/*******************************************************
*
* crm_country
*
*******************************************************/
DROP TABLE IF EXISTS crm_country;
CREATE TABLE crm_country (

	id INT UNSIGNED NOT NULL COMMENT 'country id',

	name         VARCHAR(64),
	iso_code     CHAR(2),

	country_code VARCHAR(4) COMMENT 'national prefix to be used when dialing TO this country',
	idd_prefix   VARCHAR(4) COMMENT 'international direct dialing prefix from within the country TO another country',
	ndd_prefix   VARCHAR(4) COMMENT 'access prefix to call within a country to a different area',

    PRIMARY KEY(id)

) ENGINE=InnoDB DEFAULT CHARACTER SET utf8 COLLATE utf8_bin;


/*******************************************************
*
* crm_state_province
*
*******************************************************/
DROP TABLE IF EXISTS crm_state_province;
CREATE TABLE crm_state_province (

	id INT UNSIGNED NOT NULL COMMENT 'state_province id',

	name  VARCHAR(64),
	abbreviation  VARCHAR(4),
	country_id INT UNSIGNED NOT NULL,

	PRIMARY KEY(id),
	FOREIGN KEY(country_id) REFERENCES crm_country(id) ON DELETE CASCADE

) ENGINE=InnoDB DEFAULT CHARACTER SET utf8 COLLATE utf8_bin;

/*******************************************************
*
* crm_domain
*
* Top-level hierarchy (to support multi-org/domain installations.
*
*******************************************************/
DROP TABLE IF EXISTS crm_domain;
CREATE TABLE crm_domain (

	id INT UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'auto incremented id',

	name VARCHAR(64) COMMENT 'domain/org name',

	PRIMARY KEY (id)

) ENGINE=InnoDB DEFAULT CHARACTER SET utf8 COLLATE utf8_bin COMMENT='define domains for multi-org installs, else all contacts belong to domain 1';



/*******************************************************
*
* crm_location_type
*
*******************************************************/
DROP TABLE IF EXISTS crm_location_type;
CREATE TABLE crm_location_type (

	id INT UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'location_type id',

	domain_id  INT UNSIGNED NOT NULL COMMENT 'which organization/domain owns this location_type',

	name        VARCHAR(64) COMMENT 'location_type name (typically brief)',
	description VARCHAR(255) COMMENT 'location_type description (a more verbose description)',

    is_reserved BOOLEAN DEFAULT 0 COMMENT 'is this location type a system created location that cannot be deleted by the user',

	PRIMARY KEY (id),
	FOREIGN KEY (domain_id) REFERENCES crm_domain(id) ON DELETE CASCADE

) ENGINE=InnoDB DEFAULT CHARACTER SET utf8 COLLATE utf8_bin COMMENT='domain-level set of available location_types (e.g. Home, Work, Other...)';



/*******************************************************
*
* crm_contact
*
* Three types of contacts are defined: Individual,
* Organization and Household.
*
* Contact objects are defined by a crm_contact
* record plus a related crm_contact_<type>
* record. 
*
*******************************************************/
DROP TABLE IF EXISTS crm_contact;
CREATE TABLE crm_contact (

	id INT UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'unique contact id',

	domain_id  INT UNSIGNED NOT NULL COMMENT 'which organization/domain owns this contact',

	contact_type ENUM('Individual','Organization','Household') COMMENT 'type of contact',
	sort_name VARCHAR(64) COMMENT 'name being cached for sorting purposes',

    home_URL VARCHAR(128) COMMENT 'optional "home page" URL for this contact',
    image_URL VARCHAR(128) COMMENT 'optional URL for preferred image (photo, logo, etc.) to display for this contact',
    
	source VARCHAR(255) COMMENT 'where domain_id contact come from, e.g. import, donate module insert...',

    -- contact-level communication permissions and preferences
	preferred_communication_method ENUM('Phone', 'Email', 'Post') COMMENT 'what is the preferred mode of communication',
	do_not_phone     BOOLEAN DEFAULT 0,
	do_not_email     BOOLEAN DEFAULT 0,
	do_not_mail      BOOLEAN DEFAULT 0,

    -- the hash col gives us a unique random post/get param handle for the record that isn't easily reverse engineered
    -- since it is random. So we can use sequential ids (hence they can be guessed), but use the hash as a checksum to
    -- prevent the reverse engineering. the hash is generated during contact creation time
    hash INT UNSIGNED NOT NULL COMMENT 'key for validating requests related to this contact',

    --  Need to flesh out approach for linking module actions to contact. Commented out for now. dgg
    --	caid_latest INT UNSIGNED COMMENT 'latest contact action id',

    -- ? Is this needed?
    --	module VARCHAR(255) COMMENT 'which module is handling this type',

	PRIMARY KEY (id),
	FOREIGN KEY (domain_id) REFERENCES crm_domain(id) ON DELETE CASCADE,

	INDEX index_sort_name ( sort_name )

) ENGINE=InnoDB DEFAULT CHARACTER SET utf8 COLLATE utf8_bin COMMENT='primary record for contacts';



/*******************************************************
*
* crm_contact_individual
*
*******************************************************/
DROP TABLE IF EXISTS crm_contact_individual;
CREATE TABLE crm_contact_individual(
	id  INT UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'table record id',

	contact_id INT UNSIGNED NOT NULL COMMENT 'contact id FK',

	first_name VARCHAR(64) COMMENT 'first name',
	middle_name VARCHAR(64) COMMENT 'middle name',
	last_name VARCHAR(64) COMMENT 'last name',

	prefix VARCHAR(8) COMMENT 'prefix',
	suffix VARCHAR(8) COMMENT 'suffix',

    display_name VARCHAR(128) COMMENT 'formatted name representing preferred format for display/print/other output',

    -- greeting_type constants:
	-- Formal: Prefix + first_name + last_name
	-- Informal: first_name
	-- Honorific: prefix + ?? (not sure how this is supposed to work - check w/ Bob Schmitt)
	-- Custom: greeting is stored in custom_greeting column
	greeting_type ENUM('Formal', 'Informal', 'Honorific', 'Custom') COMMENT 'preferred greeting format',
	custom_greeting VARCHAR(128) COMMENT 'custom greeting message',

	job_title VARCHAR(64) COMMENT 'optional job title for contact',

	-- core demographics fields (additional demographics to be defined by other modules)
	gender ENUM('female','male','transgender'),
	birth_date DATE,
	is_deceased BOOLEAN NOT NULL DEFAULT 0,

	phone_to_household_id INT COMMENT 'OPTIONAL FK to crm_contact_household record. If NOT NULL, direct phone communications to household rather than individual location.',
	email_to_household_id INT COMMENT 'OPTIONAL FK to crm_contact_household record. If NOT NULL, direct email communications to household rather than individual location.',
	mail_to_household_id INT COMMENT 'OPTIONAL FK to crm_contact_household record. If NOT NULL, direct postal mail to household rather than individual location.',

	PRIMARY KEY (id),
    -- FULLTEXT (first_name, last_name ),

	FOREIGN KEY (contact_id) REFERENCES crm_contact(id) ON DELETE CASCADE

) ENGINE=InnoDB DEFAULT CHARACTER SET utf8 COLLATE utf8_bin COMMENT='extends contact for type=individual';


/*******************************************************
*
* crm_contact_organization
*
*******************************************************/
DROP TABLE IF EXISTS crm_contact_organization;
CREATE TABLE crm_contact_organization(

	id  INT UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'table record id',

	contact_id INT UNSIGNED NOT NULL COMMENT 'contact id FK',

	organization_name VARCHAR(64) NOT NULL,
	legal_name VARCHAR(64),
	nick_name VARCHAR(64),
    legal_identifier VARCHAR(32) COMMENT 'EIN or other applicable unique legal identifier for this organization',
	sic_code VARCHAR(8),

	primary_contact_id INT UNSIGNED COMMENT 'optional FK to primary contact for this org',

	PRIMARY KEY (id),
    -- FULLTEXT (organization_name, legal_name, nick_name),

	FOREIGN KEY (contact_id) REFERENCES crm_contact(id) ON DELETE CASCADE,
	FOREIGN KEY (primary_contact_id) REFERENCES crm_contact(id)

) ENGINE=InnoDB DEFAULT CHARACTER SET utf8 COLLATE utf8_bin COMMENT='extends contact for type=organization';


/*******************************************************
*
* crm_contact_household
*
*******************************************************/
DROP TABLE IF EXISTS crm_contact_household;
CREATE TABLE crm_contact_household(


	id  INT UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'table record id',

	contact_id INT UNSIGNED NOT NULL COMMENT 'contact id FK',

	household_name VARCHAR(128) NOT NULL COMMENT 'Formal display/identifying name for Household. May be actual family surname, or collection of surnames for non-family households',
	nick_name VARCHAR(128) COMMENT 'e.g. The Smiths',
    legal_identifier VARCHAR(32) COMMENT 'Census Household ID, or other applicable unique identifier for this household',

	primary_contact_id INT UNSIGNED COMMENT 'optional FK to primary contact for this household',

	PRIMARY KEY (id),
    -- FULLTEXT (household_name, nick_name),

	FOREIGN KEY (contact_id) REFERENCES crm_contact(id) ON DELETE CASCADE,
	FOREIGN KEY (primary_contact_id) REFERENCES crm_contact(id)

) ENGINE=InnoDB DEFAULT CHARACTER SET utf8 COLLATE utf8_bin COMMENT='extends contact for type=household';



/*******************************************************
*
* crm_phone_mobile_provider
*
*******************************************************/
DROP TABLE IF EXISTS crm_phone_mobile_provider;
CREATE TABLE crm_phone_mobile_provider(

	id INT UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'mobile provider id',
	name VARCHAR(64) COMMENT 'name of mobile provider',
	PRIMARY KEY(id)

) ENGINE=InnoDB DEFAULT CHARACTER SET utf8 COLLATE utf8_bin COMMENT='list of mobile phone providers';



/*******************************************************
*
* crm_im_service
*
*******************************************************/
DROP TABLE IF EXISTS crm_im_service;
CREATE TABLE crm_im_service (

	id INT UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'Instant Messenger Svc id',
	name VARCHAR(64) COMMENT 'name of IM Service (e.g. AOL,Yahoo...',
	PRIMARY KEY(id)

) ENGINE=InnoDB DEFAULT CHARACTER SET utf8 COLLATE utf8_bin COMMENT='list of mobile phone providers';



/*******************************************************
*
* crm_location
* 
* Parent record for address, phone info, email, im for each contact
* by 'location_type' - e.g. Home, Work, etc. Contacts may
* have 1 -> n crm_location records, but may only have one
* record for each location type.
*
*******************************************************/
DROP TABLE IF EXISTS crm_location;
CREATE TABLE crm_location(

	id INT UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'record id',

	contact_id INT UNSIGNED NOT NULL COMMENT 'contact id',
	location_type_id INT UNSIGNED COMMENT 'fk to location_type (e.g. Home, Work...)',

	is_primary BOOLEAN NOT NULL DEFAULT 0 COMMENT 'primary contact location for this contact (allow 1 primary location per contact)',

	PRIMARY KEY (id),
    UNIQUE INDEX index_contact_location_type (contact_id,location_type_id),
	FOREIGN KEY (contact_id) REFERENCES crm_contact(id),
	FOREIGN KEY (location_type_id) REFERENCES crm_location_type(id)

) ENGINE=InnoDB DEFAULT CHARACTER SET utf8 COLLATE utf8_bin COMMENT='All contact information is keyed to location';


/*******************************************************
*
* crm_address
*
* stores the physical street / mailing address. This format
* should be capable of storing ALL international addresses.
*
* Only one crm_address is permitted per location.
*******************************************************/
DROP TABLE IF EXISTS crm_address;
CREATE TABLE crm_address(

	id INT UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'address id',

    location_id INT UNSIGNED NOT NULL COMMENT 'which location does this address belong to',

-- EITHER a concatenated street_address OR a set of street components (street_number, street_prefix, etc.) should be populated (depending on available source data).
	street_address VARCHAR(96) COMMENT 'Concatenation of all routable street address components (prefix, street number, street name, suffix, unit number OR P.O. Box. Apps should be able to determine physical location with this data (for mapping, mail delivery, etc.)',

-- Street address components
	street_number INT COMMENT 'Numeric portion of address number on the street, e.g. For 112A Main St, the street_number = 112',
	street_number_suffix VARCHAR(8) COMMENT 'Non-numeric portion of address number on the street, e.g. For 112A Main St, the street_number_suffix = A',
	street_predirectional VARCHAR(8) COMMENT 'Directional prefix, e.g. SE Main St, SE is the prefix',
	street_name VARCHAR(64) COMMENT 'Actual street name, excluding St, Dr, Rd, Ave, e.g. For 112 Main St, the street_name = Main',
	street_type VARCHAR(8) COMMENT 'St, Rd, Dr, etc.',
	street_postdirectional VARCHAR(8) COMMENT 'Directional suffix, e.g. Main St S, S is the suffix',
	street_unit VARCHAR(8) COMMENT 'Secondary unit designator, e.g. Apt 3 or Unit # 14, or Bldg 1200',
	street_unit_number INT COMMENT 'Numeric portion of secondary unit designator for sorting, e.g. for Apt 18C, value is 18',

	supplemental_address_1 VARCHAR(96) COMMENT 'Supplemental address info, e.g. c/o, organization name, department name, building name, etc.',
	supplemental_address_2 VARCHAR(96) COMMENT 'Supplemental address info, e.g. c/o, organization name, department name, building name, etc.',
	supplemental_address_3 VARCHAR(96) COMMENT 'Supplemental address info, e.g. c/o, organization name, department name, building name, etc.',

	city VARCHAR(64) COMMENT 'city, town, village...',

-- Should rename county to something more internationally used and make it a lookup.
	county VARCHAR(64),
	state_province_id INT UNSIGNED NOT NULL COMMENT 'FK to crm_state_province table',

	postal_code VARCHAR(12) COMMENT 'Store both US (zip5) AND international postal codes. App is responsible for country/region appropriate validation.',
    postal_code_suffix VARCHAR(12) COMMENT 'Store the suffix, like the +4 part in the USPS system.',
    -- US Postal Svc bulk mail address code (ADC = Area Distribution Center)
	usps_adc VARCHAR(32),

	country_id INT UNSIGNED COMMENT 'index to crm_country table',

    geo_type   ENUM('LATLONG', 'UTM') COMMENT 'system we using to encode the coordinates"
	geo_code_1 FLOAT COMMENT 'latitude or UTM (Universal Transverse Mercator Grid) Northing',
	geo_code_2 FLOAT COMMENT 'longitude or UTM (Universal Transverse Mercator Grid) Easting',
    geo_zone   VARCHAR(4) COMMENT 'UTM zone the codes are displayed in. This is typically a 2-3 character string: (1-36)(C-X)',
    geo_datum  VARCHAR(8) COMMENT 'DATUM is the underlying assumption of the shape of the earth being used, e.g. NAD27, WGS84'

	timezone VARCHAR(8) COMMENT 'timezone expressed as a UTC offset - e.g. United States CST would be written as "UTC-6"',
	address_note VARCHAR(255) COMMENT 'optional misc info (e.g. delivery instructions) for this address',

 	PRIMARY KEY (id),
    UNIQUE INDEX index_location (location_id),
    FOREIGN KEY (location_id)  REFERENCES crm_location(id),
	FOREIGN KEY (state_province_id)    REFERENCES crm_state_province(id),
	FOREIGN KEY (country_id)           REFERENCES crm_country(id)

) ENGINE=InnoDB DEFAULT CHARACTER SET utf8 COLLATE utf8_bin COMMENT='Address information for a specific location';

/*******************************************************
*
* crm_email
*
* stores the email for a specific location
*
*******************************************************/
DROP TABLE IF EXISTS crm_email;
CREATE TABLE crm_email(

	id INT UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'email id',

    location_id INT UNSIGNED NOT NULL COMMENT 'which location does this email belong to',

	email VARCHAR(64) COMMENT 'email address',

    is_primary   BOOLEAN DEFAULT 0 COMMENT 'is this the primary email for the contact / location',

	PRIMARY KEY (id),
    FOREIGN KEY (location_id)  REFERENCES crm_location(id)

) ENGINE=InnoDB DEFAULT CHARACTER SET utf8 COLLATE utf8_bin COMMENT='Email information for a specific location';



/*******************************************************
*
* crm_phone
*
* stores the phone for a specific location
*
*******************************************************/
DROP TABLE IF EXISTS crm_phone;
CREATE TABLE crm_phone(

	id INT UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'phone id',

    location_id INT UNSIGNED NOT NULL COMMENT 'which location does this phone number belong to',

	phone VARCHAR(16) COMMENT 'complete phone number',
	phone_type ENUM('Phone', 'Mobile', 'Fax', 'Pager') DEFAULT 'Phone' COMMENT 'what type of telecom device is this',
	mobile_provider_id INT UNSIGNED COMMENT 'optional mobile provider id. Denormalized-not worth another table for 1 byte col.',

    is_primary   BOOLEAN DEFAULT 0 COMMENT 'is this the primary phone number for the contact / location',

	PRIMARY KEY (id),
    FOREIGN KEY (location_id)  REFERENCES crm_location(id),
	FOREIGN KEY (mobile_provider_id) REFERENCES crm_phone_mobile_provider(id)

) ENGINE=InnoDB DEFAULT CHARACTER SET utf8 COLLATE utf8_bin COMMENT='Phone information for a specific location.';



/*******************************************************
*
* crm_im
*
* stores the instant messenger information for a specific location
*
*******************************************************/
DROP TABLE IF EXISTS crm_im;
CREATE TABLE crm_im(

	id INT UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'im id',

    location_id INT UNSIGNED NOT NULL COMMENT 'which location does this IM identifier belong to',

	im_screenname VARCHAR(64) COMMENT 'instant messenger screenname',
	im_service_id INT UNSIGNED COMMENT 'FK to crm_im_service - IM service id',

    is_primary   BOOLEAN DEFAULT 0 COMMENT 'is this the primary IM for the contact / location',

	PRIMARY KEY (id),
    FOREIGN KEY (location_id)  REFERENCES crm_location(id),
	FOREIGN KEY (im_service_id)        REFERENCES crm_im_service(id)

) ENGINE=InnoDB DEFAULT CHARACTER SET utf8 COLLATE utf8_bin COMMENT='IM information for a specific location';


/*******************************************************
*
* crm_relationship_type
*
* Several reserved types (e.g. parent/child, sibling,
* household member, employer/employee...) are included.
* 
* Relationship types s/b structured with contact_a as the
* 'subject/child' contact and contact_b as the 'object/parent'
* contact (e.g. Individual A is Employee of Org B).
*
* The name (or label) for the relationship between A and B
* (name_a_b_ is always defined. A name for the B to A
* relationship should also be defined if it has meaning and
* utility in identifying and searching for relationships.
* 
* Admins will be able to add types (for a domain).
* 
*******************************************************/
DROP TABLE IF EXISTS crm_relationship_type;
CREATE TABLE crm_relationship_type(

	id INT UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'contact relationship type id',

	domain_id INT UNSIGNED NOT NULL COMMENT 'which organization/domain owns this type',

	name_a_b VARCHAR(64) NOT NULL COMMENT 'name/label for relationship of contact_a to contact_b',
	name_b_a VARCHAR(64) COMMENT 'Optional name/label for relationship of contact_b to contact_a',

	description VARCHAR(255) COMMENT 'description of the relationship',

	contact_type_a ENUM('Individual','Organization','Household') COMMENT 'if defined, contact_a in a relationship of this type must be a specific contact_type',
	contact_type_b ENUM('Individual','Organization','Household') COMMENT 'if defined, contact_b in a relationship of this type must be a specific contact_type',

    is_reserved BOOLEAN DEFAULT 0 COMMENT 'TRUE = system created type that cannot be deleted',

	PRIMARY KEY(id),

	FOREIGN KEY (domain_id) REFERENCES crm_domain(id) ON DELETE CASCADE

) ENGINE=InnoDB DEFAULT CHARACTER SET utf8 COLLATE utf8_bin COMMENT='contact relationship types';



/*******************************************************
*
* crm_contact_relationship
*
* Defines relationships between two contact entities
* Used to model both 'roles' (e.g. "board member") as
* well as 'relationships' (e.g. "parent/child")
*
*******************************************************/
DROP TABLE IF EXISTS crm_contact_relationship;
CREATE TABLE crm_contact_relationship(

	id INT UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'contact relationship id',

	contact_id_a INT UNSIGNED NOT NULL COMMENT 'contact id for source/meta contact (e.g. parent in child/parent or employer in employee/employer)',
	contact_id_b INT UNSIGNED NOT NULL COMMENT 'contact id for subject/child contact in relationship (e.g. child in child/parent, employee in employee/employer)',

	relationship_type_id INT UNSIGNED NOT NULL COMMENT 'contact relationship type id',

    start_date DATETIME COMMENT 'Optional start date for this relationship',
    end_date DATETIME COMMENT 'Optional end date for this relationship',
    is_active BOOLEAN DEFAULT 1 COMMENT 'set to false when relationship becomes inactive - manually or due to end_date expiration',

    PRIMARY KEY(id),

	FOREIGN KEY(relationship_type_id) REFERENCES crm_relationship_type(id) ON DELETE CASCADE,
	FOREIGN KEY(contact_id_a) REFERENCES crm_contact(id) ON DELETE CASCADE,
	FOREIGN KEY(contact_id_b) REFERENCES crm_contact(id) ON DELETE CASCADE

) ENGINE=InnoDB DEFAULT CHARACTER SET utf8 COLLATE utf8_bin COMMENT='contact relationships';



/*******************************************************
*
* crm_contact_action
*
* Summary / link records for past (and/or future?) actions
* of various types. They link the contact to the detailed
* record for various types of classes, including communication
* interactions (email, letter, phone call, meeting),
* donations, click-throughs, event registration and
* attendance, etc.
*
* The Contact module can display/sort these actions
* and provide links to sub-module screens which handle
* class-specific details.
*
*******************************************************/
DROP TABLE IF EXISTS crm_contact_action;
CREATE TABLE crm_contact_action(

	id INT UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'table record id',

	contact_id INT UNSIGNED NOT NULL COMMENT 'action is related to this contact id',

-- Module and callback may be modified to FKeys to proposed crm_external_modules/callbacks tables.
    module VARCHAR(64) COMMENT 'Display name of module which registered this action.',
    callback VARCHAR(64) COMMENT 'Function to call to get details for this action',
	action_id INT UNSIGNED NOT NULL COMMENT 'FK to details item - passed to callback',
	action_summary VARCHAR(255) COMMENT 'brief description of action for summary display - as populated by registering module',

-- Need to determine if best/expected practice is to update the action record inline as status changes
-- OR to insert a new record with the same action_id (FK to registering module)
    action_completed BOOLEAN DEFAULT 1 COMMENT 'allows us to separate OPEN from COMPLETED actions',

-- For simple cases the insert, start, and end dates will be the same
	insert_date DATETIME DEFAULT 0 COMMENT 'when was this action recorded',
	start_date DATETIME DEFAULT 0 COMMENT 'when was this action started',
	end_date DATETIME DEFAULT 0 COMMENT 'when was this action completed',

    relationship_id INT COMMENT 'OPTIONAL FK to crm_relationship.id. Which relationship (of this contact) potentially triggered this action, i.e. he donated because he was a Board Member of Org X / Employee of Org Y',
    group_id INT COMMENT 'OPTIONAL FK to crm_group.id. Was this part of a group communication that triggered this action?',

-- Other possible columns for this table:
    -- action_stage VARCHAR to record a 'stage/state', e.g. Pledged, Delivered, Acknowledged, Lost... )
    -- action_type ENUM('Human_Inbound','System') COMMENT 'Differentiates human-to-human actions/interactions from automatic/system actions
    -- action_direction ENUM('Inbound','Outbound') COMMENT 'Was action initiated by the contact (inbound), or by an org user (outbound)',
    -- Consider adding a quantity bucket to quick summarization across actions of the same category (esp. donations)
    -- Are future 'scheduled' actions are recorded in this table, or only things which have happened?
    -- other possible foreign key relationships (potentially null)
    -- action_key VARCHAR(16) COMMENT 'a keycode that actions can use to share data across multiple actions. An example usage could be correlating all actions to a specific campaign'
    -- acton_data VARCHAR(255) COMMENT 'data that accompanies the above key. The key could group like minded items together, the data could give more specifics / details for further subgrouping'
    -- Category concept now implemented via crm_entity_category table.
    -- category VARCHAR(255) COMMENT 'Module-provided display string for grouping/classifying actions, e.g. Email Sent, Letter Sent, Donation Pledge, etc',

	PRIMARY KEY(id),  
	FOREIGN KEY(contact_id) REFERENCES crm_contact(id) ON DELETE CASCADE

) ENGINE=InnoDB DEFAULT CHARACTER SET utf8 COLLATE utf8_bin COMMENT='tasks related to a contact';



/*******************************************************
*
* crm_task
*
* Tasks are assigned by a contact, to another contact
* (may be self-assignment) - and may be 'about' a
* target contact (e.g. call Dana Donor), or 'free-floating'
* (e.g. clean the bathroom).
*
* should it be part of CRM or as a seperate work-flow
* investigate phpflow, typo3 etc
*******************************************************/
DROP TABLE IF EXISTS crm_task;
CREATE TABLE crm_task(

	id INT UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'table record id',

	assigned_by_contact_id INT UNSIGNED NOT NULL COMMENT 'task assigned by which contact (may be same as assigned_to)',
	assigned_to_contact_id INT UNSIGNED NOT NULL COMMENT 'task assigned to which contact',
	target_contact_id INT UNSIGNED NOT NULL COMMENT 'optional target contact id for task',

	scheduled DATETIME DEFAULT 0 COMMENT 'when is task scheduled for',
	status ENUM('Open', 'Pending', 'Completed', 'Cancelled', 'Reassigned'),

	description VARCHAR(255) COMMENT 'description of task',

	PRIMARY KEY(id),
    -- FULLTEXT (description),
    
	FOREIGN KEY(assigned_by_contact_id) REFERENCES crm_contact(id) ON DELETE CASCADE,
	FOREIGN KEY(assigned_to_contact_id) REFERENCES crm_contact(id) ON DELETE CASCADE

) ENGINE=InnoDB DEFAULT CHARACTER SET utf8 COLLATE utf8_bin COMMENT='tasks related to a contact';



/*******************************************************
*
* crm_note
*
* Notes can be linked to any object in the application
* (using the table_name column)
*
*******************************************************/
DROP TABLE IF EXISTS crm_note;
CREATE TABLE crm_note(

	id INT UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'table record id',

    table_name VARCHAR(64)  NOT NULL DEFAULT 'crm_contact' COMMENT 'name of table where item being referenced is stored',
    table_id   INT UNSIGNED NOT NULL COMMENT 'foreign key to the referenced item',
-- this would need manual constraint checking during insert/update/delete.

	note TEXT COMMENT 'note or comment',

	PRIMARY KEY(id)
    -- FULLTEXT (description)

) ENGINE=InnoDB DEFAULT CHARACTER SET utf8 COLLATE utf8_bin COMMENT='multiple notes/comments related to a contact or other entity';



/*******************************************************
*
* crm_saved_search
*
* Saved searches are persistent queries which are
* used to aggregate contacts into 'dynamic' groups for
* a variety of purposes.
*
* NOTE: This draft associates saved searches w/ a domain
* (so they are shared across a user organization). We
* may want to also support linking saved searches to
* particular users or groups of users.
*  
* Should be able to turn off dynamic queries, cache dynamic queries
* have invalidate dates etc
*
*******************************************************/
DROP TABLE IF EXISTS crm_saved_search;
CREATE TABLE crm_saved_search (

	id INT UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'saved search id',

	domain_id  INT UNSIGNED NOT NULL COMMENT 'which organization/domain owns this saved_search',

	name        VARCHAR(64) NOT NULL COMMENT 'search name (brief)',
	description VARCHAR(255) COMMENT 'verbose description',
    query       TEXT NOT NULL COMMENT 'SQL query for this search',

	PRIMARY KEY (id),
	FOREIGN KEY (domain_id) REFERENCES crm_domain(id) ON DELETE CASCADE

) ENGINE=InnoDB DEFAULT CHARACTER SET utf8 COLLATE utf8_bin COMMENT='domain-level set of available saved searches';



/*******************************************************
*
* crm_group
* 
* [first draft of list/grouping approach]
* Two types of groups are supported:
*	- static : an arbitrary set of contacts, membership
*		in the list if not driven by any property of the
*		contact
*	- query : membership is controlled by a matches to
*		a saved search (query of contact properties)
*
* A static group may be created from the results of
* a saved search.
*
* We may determine that additional group attributes
* should be defined in this table and represented in
* the contact_group join table.
*
*	EX: A 'Board Member' group may need time-bound
*		attributes (e.g. start and end dates).
*******************************************************/
DROP TABLE IF EXISTS crm_group;
CREATE TABLE crm_group (

	id INT UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'role_type id',

	domain_id  INT UNSIGNED NOT NULL COMMENT 'which organization/domain owns this role_type',

	iname		VARCHAR(64) NOT NULL COMMENT 'internal group name (constructed from display/friendly name)',
	name		VARCHAR(64) COMMENT 'display name (user-defined friendly name)',
	description VARCHAR(255) COMMENT 'group description (verbose)',

	group_type	ENUM('static','query') NOT NULL COMMENT 'static group membership is defined via crm_contact_group',
	saved_search_id	INT UNSIGNED COMMENT 'FK to saved_searches table for type=query. We may also store the FK here for static groups created via saved search.',

	source		VARCHAR(64) COMMENT 'module or process which created this group',
    -- Category concept now implemented via crm_entity_category table.
	-- category	VARCHAR(255) COMMENT 'user-defined category, use comma-delimited list for multiple categories. This column may be used as a hook for permissioning queries.',

	PRIMARY KEY (id),
	FOREIGN KEY (domain_id) REFERENCES crm_domain(id) ON DELETE CASCADE,
	FOREIGN KEY (saved_search_id) REFERENCES crm_saved_search(id) ON DELETE CASCADE

) ENGINE=InnoDB DEFAULT CHARACTER SET utf8 COLLATE utf8_bin COMMENT='domain-level set of available role_types (e.g. Member, Board Member, Prospect etc.)';



/*******************************************************
*
* crm_group_contact
*
* Join table sets membership for 'static' groups. Also
* used to store 'opt-out' entries for 'query' type groups
* (status = 'OUT').
*
*******************************************************/
DROP TABLE IF EXISTS crm_group_contact;
CREATE TABLE crm_group_contact(

	id INT UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'table record id',

	group_id INT UNSIGNED NOT NULL,
	contact_id INT UNSIGNED NOT NULL,
	
	status ENUM('Pending','In','Out') COMMENT 'status of contact relative to membership in group',
	pending_date DATETIME DEFAULT 0 COMMENT 'when was contact status for this group set to "Pending"',
	in_date DATETIME DEFAULT 0 COMMENT 'when was contact status for this group set to "In"',
	out_date DATETIME DEFAULT 0 COMMENT 'when was contact status for this group set to "Out"',
    
	PRIMARY KEY(id),

	FOREIGN KEY (group_id) REFERENCES crm_group(id) ON DELETE CASCADE,
	FOREIGN KEY (contact_id) REFERENCES crm_contact(id) ON DELETE CASCADE

) ENGINE=InnoDB DEFAULT CHARACTER SET utf8 COLLATE utf8_bin COMMENT='contact email';


/*******************************************************
*
* crm_category
* Provides support for flat or hierarchical classification
* of various types of entities (contacts, groups, actions...)
*
* Entities are assigned to one or more categories via
* crm_entity_category join table.
*
* NOTE: This implementation does not allow segmentation
* of categories as to which entities they may be used
* with. We will evaluate this capability and add if needed.
* 
*******************************************************/
DROP TABLE IF EXISTS crm_category;
CREATE TABLE crm_category(

	id INT UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'category id',

	domain_id INT UNSIGNED NOT NULL COMMENT 'which organization/domain owns this category',

	name VARCHAR(64) NOT NULL COMMENT 'name/label for the category',
	description VARCHAR(255) COMMENT 'Optional verbose description of the category',

	parent_category_id INT COMMENT 'OPTIONAL reference to crm_category.id of a parent category. NULL for top-level categories.',

	PRIMARY KEY(id),

	FOREIGN KEY (domain_id) REFERENCES crm_domain(id) ON DELETE CASCADE

) ENGINE=InnoDB DEFAULT CHARACTER SET utf8 COLLATE utf8_bin COMMENT='entity categories (for contacts, groups, actions)';


/*******************************************************
*
* crm_entity_category
* Assign entities (Contacts, Groups, Actions...) to 
* categories.
*
* entity_table is physical tablename for entity being
* assigned.
* 
*******************************************************/
DROP TABLE IF EXISTS crm_entity_category;
CREATE TABLE crm_entity_category(

	id INT UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'entity_category id',

	entity_table VARCHAR(64) NOT NULL COMMENT 'physical tablename for entity being assigned, e.g. crm_contact',
	entity_id INT UNSIGNED NOT NULL COMMENT 'FK to the entity in the specified entity_table - e.g. value of crm_contact.id, crm_group.id...',
	category_id INT UNSIGNED NOT NULL COMMENT 'FK to the category - crm_category.id',

	PRIMARY KEY(id),

	FOREIGN KEY (category_id) REFERENCES crm_category(id) ON DELETE CASCADE

) ENGINE=InnoDB DEFAULT CHARACTER SET utf8 COLLATE utf8_bin COMMENT='joins entities to categories';



/*******************************************************
*
* Extended Properties and Forms:
*	Extended properties are defined separately from the forms/form fields that
*	are used to edit/display them. This separation keeps presentation separate
*	from 'structure'. It also facilitates moving toward dynamic form rendering
*	for standard object properties in a future version.
*
*	contact_ext_property_group : logical sets of extended properties (e.g. voter info, ...)
*	contact_ext_property : defines the parent object, data type, and validation rule
*	contact_validation : defines built-in and customizable validation rules
*   contact_ext_data : stores the data for each instance of a property
*
*	Forms may contain form_fields, form_groups (visual groupings), or other forms.
*	Table structure (bottom-up):
*
*	contact_form_field : HTML form_field attributes. Linked to a contact_ext_property
*			(can link to built-in properties in future versions].
			May be standalone or part of a group.
*	contact_form_group : Identifies a set of related form_fields for grouped display
*	contact_form : Defines form title, prefix text, etc. Linked to a set of form_component
*     rows.
*	contact_form_builder : The 'elements' that make up a form. May be form_fields,
*		form_groups, or other forms. Rows are indexed to a specific contact_form.
*   contact_form_option : Option values and labels for any form_field which needs them
*		(e.g. select, radio, checkbox types)
*
*******************************************************/


/*******************************************************
*
* crm_validation
*	Stores core info about an extended (custom) property.
*	Input form-related info is kept separately (in contact_form_field),
*	so a property may be 'presented' in multiple form fields.
*
*******************************************************/
DROP TABLE IF EXISTS crm_validation;
CREATE TABLE crm_validation(

	id INT UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'validation rule id',

	domain_id  INT UNSIGNED NOT NULL COMMENT 'which organization/domain contains this entry',

	type	ENUM('Email','Money','URL','Phone Number','Money','Positive Number','Alpha-only',
				'Range','Comparison','RegEx-Match','RegEx-No Match')
			COMMENT 'list of rule built-in rule types. custom types may be added to ENUM via directory scan.',
	parameters VARCHAR(255)
			COMMENT 'optional value(s) passed to validation function, e.g.
			a regular expression, min and max for Range, operator + number for Comparison type, etc.',
	functionName VARCHAR(64) COMMENT 'custom validation function name',
    description VARCHAR(255) COMMENT 'rule description (verbose)',

	PRIMARY KEY (id),

	FOREIGN KEY (domain_id) REFERENCES crm_domain(id) ON DELETE CASCADE

) ENGINE=InnoDB DEFAULT CHARACTER SET utf8 COLLATE utf8_bin COMMENT='stores the data for extended properties';



/*******************************************************
*
* crm_ext_property_group
*	All extended (custom) properties are associated with a group.
*	These are logical sets of related data.
*
*******************************************************/
DROP TABLE IF EXISTS crm_ext_property_group;
CREATE TABLE crm_ext_property_group(

	id INT UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'extended property group id',

	domain_id  INT UNSIGNED NOT NULL COMMENT 'which organization/domain contains this ext property',

	iname VARCHAR(64) COMMENT 'variable name/programmatic handle for this group',
	name  VARCHAR(64) COMMENT 'friendly name',
    description VARCHAR(255) COMMENT 'group description (verbose)',

	extends ENUM('contact','contact_individual','contact_organization','contact_household') DEFAULT 'contact' COMMENT 'type of object this group extends (can add other options later e.g. contact_address, etc.)',
	
	PRIMARY KEY (id),

	FOREIGN KEY (domain_id) REFERENCES crm_domain(id) ON DELETE CASCADE

) ENGINE=InnoDB DEFAULT CHARACTER SET utf8 COLLATE utf8_bin COMMENT='extended property grouping';



/*******************************************************
*
* crm_ext_property
*	Stores core info about an extended (custom) property.
*
*	NOTE: Input form-related info is kept separately (in crm_form_field),
*	because a property may be 'presented' in multiple form fields. It
*   may also be populated via import or other method, and not be part
*	of any 'form'.
*
*******************************************************/
DROP TABLE IF EXISTS crm_ext_property;
CREATE TABLE crm_ext_property(

	id INT UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'extended property id',

	group_id  INT UNSIGNED NOT NULL COMMENT 'FK to crm_ext_property_group',

	iname VARCHAR(64) COMMENT 'variable name/programmatic handle for this property',
	name  VARCHAR(64) COMMENT 'friendly name',
    description VARCHAR(255) COMMENT 'property description (verbose)',

	data_type ENUM('string','int','float','money','text','date','boolean') COMMENT 'controls location of data storage in extended_data table',

	required BOOLEAN NULL DEFAULT 0 COMMENT 'is a value required for this property',
	validation_id INT UNSIGNED COMMENT 'FK to validation_rule table',

-- Assume all ext_property.max_instances will be 1 for now (until we figure out use cases that require multiple instances).
    max_instances INT UNSIGNED NOT NULL DEFAULT 1 COMMENT 'How many instances of this property may the parent object have.',
	
	PRIMARY KEY (id),

	FOREIGN KEY (group_id) REFERENCES crm_ext_property_group(id) ON DELETE CASCADE,
	FOREIGN KEY (validation_id) REFERENCES crm_validation(id)

) ENGINE=InnoDB DEFAULT CHARACTER SET utf8 COLLATE utf8_bin COMMENT='stores the data for extended properties';


/*******************************************************
*
* crm_ext_data
*
*******************************************************/
DROP TABLE IF EXISTS crm_ext_data;
CREATE TABLE crm_ext_data(

	id INT UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'table record id',

	contact_id INT UNSIGNED NOT NULL COMMENT 'data is for this contact id',

	ext_property_id INT UNSIGNED NOT NULL COMMENT 'FK to contact_ext_property',

    -- Data is stored in one of these 'buckets' depending on property type.
    -- ? Should we have separate storage bucket for BOOLEANS ? dgg
	int_data INT COMMENT 'stores data for ext property data_type = integer. This col supports signed integers.',
	float_data FLOAT COMMENT 'stores data for ext property data_type = float and money.',
	char_data VARCHAR(255) COMMENT 'data for ext property data_type = text',
	date_data DATETIME COMMENT 'data for ext property data_type = date',
	memo_data TEXT COMMENT 'data for ext property data_type = memo',

	PRIMARY KEY(id),

	FOREIGN KEY(contact_id) REFERENCES crm_contact(id) ON DELETE CASCADE,
	FOREIGN KEY(ext_property_id) REFERENCES crm_ext_property(id)

) ENGINE=InnoDB DEFAULT CHARACTER SET utf8 COLLATE utf8_bin COMMENT='stores the data for extended properties';



/*******************************************************
*
* crm_form
* Defines a form. Initially, pre-load with 1 'built-in' form - Contact
* which acts as 'parent' for the custom forms added by users.
*
*******************************************************/
DROP TABLE IF EXISTS crm_form;
CREATE TABLE crm_form(

	id INT UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'table record id',

	name		VARCHAR(64) NOT NULL COMMENT 'friendly name for form',
	title		VARCHAR(64) COMMENT 'display title for form',

    -- 'Inline' style tells module to append this form to the parent form as defined in contact_form_builder.
    -- 'Tab' style tells module to create a new tabbed page for this form.
	style		ENUM('tab','inline') COMMENT 'Visual relationship between this form and its parent',
 
	help_pre	TEXT default '' COMMENT 'Description and/or help text to display before fields in group',
	help_post	TEXT default '' COMMENT 'Description and/or help text to display after fields',

	PRIMARY KEY (id)

) ENGINE=InnoDB DEFAULT CHARACTER SET utf8 COLLATE utf8_bin COMMENT='Defines contact forms';

-- Consider adding INSERT statements in this script to add a domain, contact and default
-- built-in contact_form


/*******************************************************
*
* crm_form_group
* Defines presentation groups of form fields
*
*******************************************************/
DROP TABLE IF EXISTS crm_form_group;
CREATE TABLE crm_form_group(

	id INT UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'table record id',

	name		VARCHAR(64) NOT NULL COMMENT 'friendly name for group',
	title		VARCHAR(64) COMMENT 'display title for group (legend)',

	help_pre	TEXT default '' COMMENT 'Description and/or help text to display before fields in group',
	help_post	TEXT default '' COMMENT 'Description and/or help text to display after fields',

	is_active	BOOLEAN NOT NULL DEFAULT 1 COMMENT 'is this element in active use, or retained for legacy use ?',

	PRIMARY KEY (id)

) ENGINE=InnoDB DEFAULT CHARACTER SET utf8 COLLATE utf8_bin COMMENT='Defines form field groups';


/*******************************************************
*
* crm_form_field
* Defines form fields and their attributes
*
*******************************************************/
DROP TABLE IF EXISTS crm_form_field;
CREATE TABLE crm_form_field(

	id INT UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'table record id',
	property_type ENUM('built-in','extended'),
	property_id INT UNSIGNED NOT NULL COMMENT 'FK to contact_ext_property (when property_type = extended)',

	field_name	VARCHAR(64) NOT NULL default '' COMMENT 'variable name assigned to HTML form element',
	label		VARCHAR(64) default '' COMMENT 'field label for display',

	field_type	ENUM('text','textarea','select','radio','checkbox','select_date','select_state_province','select_country')
				COMMENT 'HTML types plus several built-in extended types', 

	field_mask	VARCHAR(64) COMMENT 'optional format instructions for specific field types.',
    --	EX: for 'select_date' -> "mmm yyyy" requests a pair of selects with format = <Jan> <2004> 

	html_attributes	VARCHAR(255) COMMENT 'store collection of type-appropriate attributes',
    --	EX: for 'textarea' ->  'rows="4" cols="80" class="myForms1"'

	script		VARCHAR(255) COMMENT 'store scripting attributes for field',
    --	EX: for 'select' -> "onChange=reloadPage();"

	default_value VARCHAR(255) COMMENT 'use form_options.is_default for field_types which use options',

	group_id	INT UNSIGNED COMMENT 'optional FK to contact_group, assigns field to visual/display grouping',
	help_pre	TEXT default '' COMMENT 'Description and/or help text to display before field control (and after label)',
	help_post	TEXT default '' COMMENT 'Description and/or help text to display after field control',

	is_active	BOOLEAN NOT NULL DEFAULT 1 COMMENT 'is this element in active use, or retained for legacy use ?',

	PRIMARY KEY (id),
	INDEX index_property (property_id),

    -- No explicit FK to for property_id to contact_ext_property because we will eventually reference
    -- built-in properties too..

	FOREIGN KEY (group_id) REFERENCES crm_form_group(id)

) ENGINE=InnoDB DEFAULT CHARACTER SET utf8 COLLATE utf8_bin COMMENT='Defines form fields and their attributes';


/*******************************************************
*
* crm_form_builder
* Defines components used to render a form (fields, groups, other forms)
*
*******************************************************/
DROP TABLE IF EXISTS crm_form_builder;
CREATE TABLE crm_form_builder(

	id INT UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'table record id',
	form_id INT UNSIGNED NOT NULL COMMENT 'FK to contact_form',

	component_type	ENUM('form_field','form_group','form') COMMENT 'type of item we are adding to form', 
    -- Should we consider allowing Drupal content types like blocks to be included here too?
	component_id	INT UNSIGNED NOT NULL COMMENT 'conditional FK to contact_form_field | contact_group | contact_form',

	weight		INT NOT NULL COMMENT 'sets sort order for components belonging to a form.',

	is_active	BOOLEAN NOT NULL DEFAULT 1 COMMENT 'is this element in active use, or retained for legacy use ?',

	PRIMARY KEY (id),
	INDEX index_form (form_id)

    -- No explicit FK to for component_id since it may key to form, field or group.

) ENGINE=InnoDB DEFAULT CHARACTER SET utf8 COLLATE utf8_bin COMMENT='Builds a form by defining its components';



/*******************************************************
*
* crm_form_option
* Option values and labels for form_fields (select, radio...)
*
*******************************************************/
DROP TABLE IF EXISTS crm_form_option;
CREATE TABLE crm_form_option(

	id				INT UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'table record id',

	field_id		INT UNSIGNED NOT NULL COMMENT 'FK to form_field table (who owns this option)',
	option_value	VARCHAR(64) COMMENT 'If NULL, use option_label as option value.',
	option_label	VARCHAR(64),
    --	EX: <option value=$option_value>$option_label</option>
    --  EX:	<input type=radio value=$option_value../>$option_label

	weight			INT NOT NULL COMMENT 'sets sort order for options in a set',
	is_default		BOOLEAN NOT NULL DEFAULT 0,

	is_active	BOOLEAN NOT NULL DEFAULT 1 COMMENT 'is this element in active use, or retained for legacy use ?',

	PRIMARY KEY (id),

	FOREIGN KEY (field_id) REFERENCES crm_form_field(id)

) ENGINE=InnoDB DEFAULT CHARACTER SET utf8 COLLATE utf8_bin COMMENT='Defines form field options';
		

/*******************************************************
*
* crm_user
*
* This table creates 1:1 relationship between a contact
* record and a user record. The intent is to generate a
* user and crm_user record for a contact at the point
* they interact with the site (e.g. respond to an event
* invite, sign a petition, etc.).
*
*******************************************************/
DROP TABLE IF EXISTS crm_user;
CREATE TABLE crm_user(

	id INT UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'table id',

	contact_id INT UNSIGNED NOT NULL COMMENT 'contact id',
	user_id INT UNSIGNED NOT NULL COMMENT 'implicit FK to Drupal users.uid',
    -- Given discrepancy in DB Engines, we cannot create explicit FK to Drupal users table

	PRIMARY KEY(id),
    UNIQUE INDEX index_contact_user (contact_id,user_id),

	FOREIGN KEY(contact_id) REFERENCES crm_contact(id) ON DELETE CASCADE

) ENGINE=InnoDB DEFAULT CHARACTER SET utf8 COLLATE utf8_bin COMMENT='1:1 link between contact and authenticated user';



/*******************************************************
*
* crm_county (!! TBD)
*
* Look at importing county tables (re: Advokit)
*
*******************************************************/



INSERT INTO crm_country (id, name,iso_code) VALUES("1001", "Afghanistan", "AF");
INSERT INTO crm_country (id, name,iso_code) VALUES("1002", "Albania", "AL");
INSERT INTO crm_country (id, name,iso_code) VALUES("1003", "Algeria", "DZ");
INSERT INTO crm_country (id, name,iso_code) VALUES("1004", "American Samoa", "AS");
INSERT INTO crm_country (id, name,iso_code) VALUES("1005", "Andorra", "AD");
INSERT INTO crm_country (id, name,iso_code) VALUES("1006", "Angola", "AO");
INSERT INTO crm_country (id, name,iso_code) VALUES("1007", "Anguilla", "AI");
INSERT INTO crm_country (id, name,iso_code) VALUES("1008", "Antarctica", "AQ");
INSERT INTO crm_country (id, name,iso_code) VALUES("1009", "Antigua and Barbuda", "AG");
INSERT INTO crm_country (id, name,iso_code) VALUES("1010", "Argentina", "AR");
INSERT INTO crm_country (id, name,iso_code) VALUES("1011", "Armenia", "AM");
INSERT INTO crm_country (id, name,iso_code) VALUES("1012", "Aruba", "AW");
INSERT INTO crm_country (id, name,iso_code) VALUES("1013", "Australia", "AU");
INSERT INTO crm_country (id, name,iso_code) VALUES("1014", "Austria", "AT");
INSERT INTO crm_country (id, name,iso_code) VALUES("1015", "Azerbaijan", "AZ");
INSERT INTO crm_country (id, name,iso_code) VALUES("1016", "Bahrain", "BH");
INSERT INTO crm_country (id, name,iso_code) VALUES("1017", "Bangladesh", "BD");
INSERT INTO crm_country (id, name,iso_code) VALUES("1018", "Barbados", "BB");
INSERT INTO crm_country (id, name,iso_code) VALUES("1019", "Belarus", "BY");
INSERT INTO crm_country (id, name,iso_code) VALUES("1020", "Belgium", "BE");
INSERT INTO crm_country (id, name,iso_code) VALUES("1021", "Belize", "BZ");
INSERT INTO crm_country (id, name,iso_code) VALUES("1022", "Benin", "BJ");
INSERT INTO crm_country (id, name,iso_code) VALUES("1023", "Bermuda", "BM");
INSERT INTO crm_country (id, name,iso_code) VALUES("1024", "Bhutan", "BT");
INSERT INTO crm_country (id, name,iso_code) VALUES("1025", "Bolivia", "BO");
INSERT INTO crm_country (id, name,iso_code) VALUES("1026", "Bosnia and Herzegovina", "BA");
INSERT INTO crm_country (id, name,iso_code) VALUES("1027", "Botswana", "BW");
INSERT INTO crm_country (id, name,iso_code) VALUES("1028", "Bouvet Island", "BV");
INSERT INTO crm_country (id, name,iso_code) VALUES("1029", "Brazil", "BR");
INSERT INTO crm_country (id, name,iso_code) VALUES("1030", "British Indian Ocean Territory", "IO");
INSERT INTO crm_country (id, name,iso_code) VALUES("1031", "British Virgin Islands", "VG");
INSERT INTO crm_country (id, name,iso_code) VALUES("1032", "Brunei Darussalam", "BN");
INSERT INTO crm_country (id, name,iso_code) VALUES("1033", "Bulgaria", "BG");
INSERT INTO crm_country (id, name,iso_code) VALUES("1034", "Burkina Faso", "BF");
INSERT INTO crm_country (id, name,iso_code) VALUES("1035", "Burma", "MM");
INSERT INTO crm_country (id, name,iso_code) VALUES("1036", "Burundi", "BI");
INSERT INTO crm_country (id, name,iso_code) VALUES("1037", "Cambodia", "KH");
INSERT INTO crm_country (id, name,iso_code) VALUES("1038", "Cameroon", "CM");
INSERT INTO crm_country (id, name,iso_code) VALUES("1039", "Canada", "CA");
INSERT INTO crm_country (id, name,iso_code) VALUES("1040", "Cape Verde", "CV");
INSERT INTO crm_country (id, name,iso_code) VALUES("1041", "Cayman Islands", "KY");
INSERT INTO crm_country (id, name,iso_code) VALUES("1042", "Central African Republic", "CF");
INSERT INTO crm_country (id, name,iso_code) VALUES("1043", "Chad", "TD");
INSERT INTO crm_country (id, name,iso_code) VALUES("1044", "Chile", "CL");
INSERT INTO crm_country (id, name,iso_code) VALUES("1045", "China", "CN");
INSERT INTO crm_country (id, name,iso_code) VALUES("1046", "Christmas Island", "CX");
INSERT INTO crm_country (id, name,iso_code) VALUES("1047", "Cocos (Keeling) Islands", "CC");
INSERT INTO crm_country (id, name,iso_code) VALUES("1048", "Colombia", "CO");
INSERT INTO crm_country (id, name,iso_code) VALUES("1049", "Comoros", "KM");
INSERT INTO crm_country (id, name,iso_code) VALUES("1050", "Congo, Democratic Republic of the", "CG");
INSERT INTO crm_country (id, name,iso_code) VALUES("1051", "Congo, Republic of the", "CF");
INSERT INTO crm_country (id, name,iso_code) VALUES("1052", "Cook Islands", "CK");
INSERT INTO crm_country (id, name,iso_code) VALUES("1053", "Costa Rica", "CR");
INSERT INTO crm_country (id, name,iso_code) VALUES("1054", "Cote d\'Ivoire", "CI");
INSERT INTO crm_country (id, name,iso_code) VALUES("1055", "Croatia", "HR");
INSERT INTO crm_country (id, name,iso_code) VALUES("1056", "Cuba", "CU");
INSERT INTO crm_country (id, name,iso_code) VALUES("1057", "Cyprus", "CY");
INSERT INTO crm_country (id, name,iso_code) VALUES("1058", "Czech Republic", "CZ");
INSERT INTO crm_country (id, name,iso_code) VALUES("1059", "Denmark", "DK");
INSERT INTO crm_country (id, name,iso_code) VALUES("1060", "Djibouti", "DJ");
INSERT INTO crm_country (id, name,iso_code) VALUES("1061", "Dominica", "DM");
INSERT INTO crm_country (id, name,iso_code) VALUES("1062", "Dominican Republic", "DO");
INSERT INTO crm_country (id, name,iso_code) VALUES("1063", "East Timor", "TP");
INSERT INTO crm_country (id, name,iso_code) VALUES("1064", "Ecuador", "EC");
INSERT INTO crm_country (id, name,iso_code) VALUES("1065", "Egypt", "EG");
INSERT INTO crm_country (id, name,iso_code) VALUES("1066", "El Salvador", "SV");
INSERT INTO crm_country (id, name,iso_code) VALUES("1067", "Equatorial Guinea", "GQ");
INSERT INTO crm_country (id, name,iso_code) VALUES("1068", "Eritrea", "ER");
INSERT INTO crm_country (id, name,iso_code) VALUES("1069", "Estonia", "EE");
INSERT INTO crm_country (id, name,iso_code) VALUES("1070", "Ethiopia", "ET");
INSERT INTO crm_country (id, name,iso_code) VALUES("1071", "European Union", "EU");
INSERT INTO crm_country (id, name,iso_code) VALUES("1072", "Falkland Islands (Islas Malvinas)", NULL);
INSERT INTO crm_country (id, name,iso_code) VALUES("1073", "Faroe Islands", "FO");
INSERT INTO crm_country (id, name,iso_code) VALUES("1074", "Fiji", "FJ");
INSERT INTO crm_country (id, name,iso_code) VALUES("1075", "Finland", "FI");
INSERT INTO crm_country (id, name,iso_code) VALUES("1076", "France", "FR");
INSERT INTO crm_country (id, name,iso_code) VALUES("1077", "French Guiana", "GF");
INSERT INTO crm_country (id, name,iso_code) VALUES("1078", "French Polynesia", "PF");
INSERT INTO crm_country (id, name,iso_code) VALUES("1079", "French Southern and Antarctic Lands", "TF");
INSERT INTO crm_country (id, name,iso_code) VALUES("1080", "Gabon", "GA");
INSERT INTO crm_country (id, name,iso_code) VALUES("1081", "Georgia", "GE");
INSERT INTO crm_country (id, name,iso_code) VALUES("1082", "Germany", "DE");
INSERT INTO crm_country (id, name,iso_code) VALUES("1083", "Ghana", "GH");
INSERT INTO crm_country (id, name,iso_code) VALUES("1084", "Gibraltar", "GI");
INSERT INTO crm_country (id, name,iso_code) VALUES("1085", "Greece", "GR");
INSERT INTO crm_country (id, name,iso_code) VALUES("1086", "Greenland", "GL");
INSERT INTO crm_country (id, name,iso_code) VALUES("1087", "Grenada", "GD");
INSERT INTO crm_country (id, name,iso_code) VALUES("1088", "Guadeloupe", "GP");
INSERT INTO crm_country (id, name,iso_code) VALUES("1089", "Guam", "GU");
INSERT INTO crm_country (id, name,iso_code) VALUES("1090", "Guatemala", "GT");
INSERT INTO crm_country (id, name,iso_code) VALUES("1091", "Guinea", "GN");
INSERT INTO crm_country (id, name,iso_code) VALUES("1092", "Guinea-Bissau", "GW");
INSERT INTO crm_country (id, name,iso_code) VALUES("1093", "Guyana", "GY");
INSERT INTO crm_country (id, name,iso_code) VALUES("1094", "Haiti", "HT");
INSERT INTO crm_country (id, name,iso_code) VALUES("1095", "Heard Island and McDonald Islands", "HM");
INSERT INTO crm_country (id, name,iso_code) VALUES("1096", "Holy See (Vatican City)", "VA");
INSERT INTO crm_country (id, name,iso_code) VALUES("1097", "Honduras", "HN");
INSERT INTO crm_country (id, name,iso_code) VALUES("1098", "Hong Kong (SAR)", "HK");
INSERT INTO crm_country (id, name,iso_code) VALUES("1099", "Hungary", "HU");
INSERT INTO crm_country (id, name,iso_code) VALUES("1100", "Iceland", "IS");
INSERT INTO crm_country (id, name,iso_code) VALUES("1101", "India", "IN");
INSERT INTO crm_country (id, name,iso_code) VALUES("1102", "Indonesia", "ID");
INSERT INTO crm_country (id, name,iso_code) VALUES("1103", "Iran", "IR");
INSERT INTO crm_country (id, name,iso_code) VALUES("1104", "Iraq", "IQ");
INSERT INTO crm_country (id, name,iso_code) VALUES("1105", "Ireland", "IE");
INSERT INTO crm_country (id, name,iso_code) VALUES("1106", "Israel", "IL");
INSERT INTO crm_country (id, name,iso_code) VALUES("1107", "Italy", "IT");
INSERT INTO crm_country (id, name,iso_code) VALUES("1108", "Jamaica", "JM");
INSERT INTO crm_country (id, name,iso_code) VALUES("1109", "Japan", "JP");
INSERT INTO crm_country (id, name,iso_code) VALUES("1110", "Jordan", "JO");
INSERT INTO crm_country (id, name,iso_code) VALUES("1111", "Kazakhstan", "KZ");
INSERT INTO crm_country (id, name,iso_code) VALUES("1112", "Kenya", "KE");
INSERT INTO crm_country (id, name,iso_code) VALUES("1113", "Kiribati", "KI");
INSERT INTO crm_country (id, name,iso_code) VALUES("1114", "Korea, North", "KP");
INSERT INTO crm_country (id, name,iso_code) VALUES("1115", "Korea, South", "KR");
INSERT INTO crm_country (id, name,iso_code) VALUES("1116", "Kuwait", "KW");
INSERT INTO crm_country (id, name,iso_code) VALUES("1117", "Kyrgyzstan", "KG");
INSERT INTO crm_country (id, name,iso_code) VALUES("1118", "Laos", "LA");
INSERT INTO crm_country (id, name,iso_code) VALUES("1119", "Latvia", "LV");
INSERT INTO crm_country (id, name,iso_code) VALUES("1120", "Lebanon", "LB");
INSERT INTO crm_country (id, name,iso_code) VALUES("1121", "Lesotho", "LS");
INSERT INTO crm_country (id, name,iso_code) VALUES("1122", "Liberia", "LR");
INSERT INTO crm_country (id, name,iso_code) VALUES("1123", "Libya", "LY");
INSERT INTO crm_country (id, name,iso_code) VALUES("1124", "Liechtenstein", "LI");
INSERT INTO crm_country (id, name,iso_code) VALUES("1125", "Lithuania", "LT");
INSERT INTO crm_country (id, name,iso_code) VALUES("1126", "Luxembourg", "LU");
INSERT INTO crm_country (id, name,iso_code) VALUES("1127", "Macao", "MO");
INSERT INTO crm_country (id, name,iso_code) VALUES("1128", "Macedonia, The Former Yugoslav Republic of", "MK");
INSERT INTO crm_country (id, name,iso_code) VALUES("1129", "Madagascar", "MG");
INSERT INTO crm_country (id, name,iso_code) VALUES("1130", "Malawi", "MW");
INSERT INTO crm_country (id, name,iso_code) VALUES("1131", "Malaysia", "MY");
INSERT INTO crm_country (id, name,iso_code) VALUES("1132", "Maldives", "MV");
INSERT INTO crm_country (id, name,iso_code) VALUES("1133", "Mali", "ML");
INSERT INTO crm_country (id, name,iso_code) VALUES("1134", "Malta", "MT");
INSERT INTO crm_country (id, name,iso_code) VALUES("1135", "Marshall Islands", "MH");
INSERT INTO crm_country (id, name,iso_code) VALUES("1136", "Martinique", "MQ");
INSERT INTO crm_country (id, name,iso_code) VALUES("1137", "Mauritania", "MR");
INSERT INTO crm_country (id, name,iso_code) VALUES("1138", "Mauritius", "MU");
INSERT INTO crm_country (id, name,iso_code) VALUES("1139", "Mayotte", "YT");
INSERT INTO crm_country (id, name,iso_code) VALUES("1140", "Mexico", "MX");
INSERT INTO crm_country (id, name,iso_code) VALUES("1141", "Micronesia, Federated States of", "FM");
INSERT INTO crm_country (id, name,iso_code) VALUES("1142", "Moldova", "MD");
INSERT INTO crm_country (id, name,iso_code) VALUES("1143", "Monaco", "MC");
INSERT INTO crm_country (id, name,iso_code) VALUES("1144", "Mongolia", "MN");
INSERT INTO crm_country (id, name,iso_code) VALUES("1145", "Montserrat", "MS");
INSERT INTO crm_country (id, name,iso_code) VALUES("1146", "Morocco", "MA");
INSERT INTO crm_country (id, name,iso_code) VALUES("1147", "Mozambique", "MZ");
INSERT INTO crm_country (id, name,iso_code) VALUES("1148", "Namibia", "NA");
INSERT INTO crm_country (id, name,iso_code) VALUES("1149", "Nauru", "NR");
INSERT INTO crm_country (id, name,iso_code) VALUES("1150", "Nepal", "NP");
INSERT INTO crm_country (id, name,iso_code) VALUES("1151", "Netherlands Antilles", "AN");
INSERT INTO crm_country (id, name,iso_code) VALUES("1152", "Netherlands", "NL");
INSERT INTO crm_country (id, name,iso_code) VALUES("1153", "New Caledonia", "NC");
INSERT INTO crm_country (id, name,iso_code) VALUES("1154", "New Zealand", "NZ");
INSERT INTO crm_country (id, name,iso_code) VALUES("1155", "Nicaragua", "NI");
INSERT INTO crm_country (id, name,iso_code) VALUES("1156", "Niger", "NE");
INSERT INTO crm_country (id, name,iso_code) VALUES("1157", "Nigeria", "NG");
INSERT INTO crm_country (id, name,iso_code) VALUES("1158", "Niue", "NU");
INSERT INTO crm_country (id, name,iso_code) VALUES("1159", "Norfolk Island", "NF");
INSERT INTO crm_country (id, name,iso_code) VALUES("1160", "Northern Mariana Islands", "MP");
INSERT INTO crm_country (id, name,iso_code) VALUES("1161", "Norway", "NO");
INSERT INTO crm_country (id, name,iso_code) VALUES("1162", "Oman", "OM");
INSERT INTO crm_country (id, name,iso_code) VALUES("1163", "Pakistan", "PK");
INSERT INTO crm_country (id, name,iso_code) VALUES("1164", "Palau", "PW");
INSERT INTO crm_country (id, name,iso_code) VALUES("1165", "Palestinian Territory, Occupied", "PS");
INSERT INTO crm_country (id, name,iso_code) VALUES("1166", "Panama", "PA");
INSERT INTO crm_country (id, name,iso_code) VALUES("1167", "Papua New Guinea", "PG");
INSERT INTO crm_country (id, name,iso_code) VALUES("1168", "Paraguay", "PY");
INSERT INTO crm_country (id, name,iso_code) VALUES("1169", "Peru", "PE");
INSERT INTO crm_country (id, name,iso_code) VALUES("1170", "Philippines", "PH");
INSERT INTO crm_country (id, name,iso_code) VALUES("1171", "Pitcairn Islands", "PN");
INSERT INTO crm_country (id, name,iso_code) VALUES("1172", "Poland", "PL");
INSERT INTO crm_country (id, name,iso_code) VALUES("1173", "Portugal", "PT");
INSERT INTO crm_country (id, name,iso_code) VALUES("1174", "Puerto Rico", "PR");
INSERT INTO crm_country (id, name,iso_code) VALUES("1175", "Qatar", "QA");
INSERT INTO crm_country (id, name,iso_code) VALUES("1176", "Romania", "RO");
INSERT INTO crm_country (id, name,iso_code) VALUES("1177", "Russian Federation", "RU");
INSERT INTO crm_country (id, name,iso_code) VALUES("1178", "Rwanda", "RW");
INSERT INTO crm_country (id, name,iso_code) VALUES("1179", "Reunion", "RE");
INSERT INTO crm_country (id, name,iso_code) VALUES("1180", "Saint Helena", "SH");
INSERT INTO crm_country (id, name,iso_code) VALUES("1181", "Saint Kitts and Nevis", "KN");
INSERT INTO crm_country (id, name,iso_code) VALUES("1182", "Saint Lucia", "LC");
INSERT INTO crm_country (id, name,iso_code) VALUES("1183", "Saint Pierre and Miquelon", "PM");
INSERT INTO crm_country (id, name,iso_code) VALUES("1184", "Saint Vincent and the Grenadines", "VC");
INSERT INTO crm_country (id, name,iso_code) VALUES("1185", "Samoa", "WS");
INSERT INTO crm_country (id, name,iso_code) VALUES("1186", "San Marino", "SM");
INSERT INTO crm_country (id, name,iso_code) VALUES("1187", "Saudi Arabia", "SA");
INSERT INTO crm_country (id, name,iso_code) VALUES("1188", "Senegal", "SN");
INSERT INTO crm_country (id, name,iso_code) VALUES("1189", "Seychelles", "SC");
INSERT INTO crm_country (id, name,iso_code) VALUES("1190", "Sierra Leone", "SL");
INSERT INTO crm_country (id, name,iso_code) VALUES("1191", "Singapore", "SG");
INSERT INTO crm_country (id, name,iso_code) VALUES("1192", "Slovakia", NULL);
INSERT INTO crm_country (id, name,iso_code) VALUES("1193", "Slovenia", "SI");
INSERT INTO crm_country (id, name,iso_code) VALUES("1194", "Solomon Islands", "SB");
INSERT INTO crm_country (id, name,iso_code) VALUES("1195", "Somalia", "SO");
INSERT INTO crm_country (id, name,iso_code) VALUES("1196", "South Africa", "ZA");
INSERT INTO crm_country (id, name,iso_code) VALUES("1197", "South Georgia and the South Sandwich Islands", "GS");
INSERT INTO crm_country (id, name,iso_code) VALUES("1198", "Spain", "ES");
INSERT INTO crm_country (id, name,iso_code) VALUES("1199", "Sri Lanka", "LK");
INSERT INTO crm_country (id, name,iso_code) VALUES("1200", "Sudan", "SD");
INSERT INTO crm_country (id, name,iso_code) VALUES("1201", "Suriname", "SR");
INSERT INTO crm_country (id, name,iso_code) VALUES("1202", "Svalbard", NULL);
INSERT INTO crm_country (id, name,iso_code) VALUES("1203", "Swaziland", "SZ");
INSERT INTO crm_country (id, name,iso_code) VALUES("1204", "Sweden", "SE");
INSERT INTO crm_country (id, name,iso_code) VALUES("1205", "Switzerland", "CH");
INSERT INTO crm_country (id, name,iso_code) VALUES("1206", "Syria", "SY");
INSERT INTO crm_country (id, name,iso_code) VALUES("1207", "Sao Tome and Principe, Democratic Republic of", "ST");
INSERT INTO crm_country (id, name,iso_code) VALUES("1208", "Taiwan", "TW");
INSERT INTO crm_country (id, name,iso_code) VALUES("1209", "Tajikistan", "TJ");
INSERT INTO crm_country (id, name,iso_code) VALUES("1210", "Tanzania", "TZ");
INSERT INTO crm_country (id, name,iso_code) VALUES("1211", "Thailand", "TH");
INSERT INTO crm_country (id, name,iso_code) VALUES("1212", "Bahamas, The", "BS");
INSERT INTO crm_country (id, name,iso_code) VALUES("1213", "Gambia", "GM");
INSERT INTO crm_country (id, name,iso_code) VALUES("1214", "Togo", "TG");
INSERT INTO crm_country (id, name,iso_code) VALUES("1215", "Tokelau", "TK");
INSERT INTO crm_country (id, name,iso_code) VALUES("1216", "Tonga", "TO");
INSERT INTO crm_country (id, name,iso_code) VALUES("1217", "Trinidad and Tobago", "TT");
INSERT INTO crm_country (id, name,iso_code) VALUES("1218", "Tunisia", "TN");
INSERT INTO crm_country (id, name,iso_code) VALUES("1219", "Turkey", "TR");
INSERT INTO crm_country (id, name,iso_code) VALUES("1220", "Turkmenistan", "TM");
INSERT INTO crm_country (id, name,iso_code) VALUES("1221", "Turks and Caicos Islands", "TC");
INSERT INTO crm_country (id, name,iso_code) VALUES("1222", "Tuvalu", "TV");
INSERT INTO crm_country (id, name,iso_code) VALUES("1223", "Uganda", "UG");
INSERT INTO crm_country (id, name,iso_code) VALUES("1224", "Ukraine", "UA");
INSERT INTO crm_country (id, name,iso_code) VALUES("1225", "United Arab Emirates", "AE");
INSERT INTO crm_country (id, name,iso_code) VALUES("1226", "United Kingdom", "GB");
INSERT INTO crm_country (id, name,iso_code) VALUES("1227", "United States Minor Outlying Islands", "UM");
INSERT INTO crm_country (id, name,iso_code) VALUES("1228", "United States", "US");
INSERT INTO crm_country (id, name,iso_code) VALUES("1229", "Uruguay", "UY");
INSERT INTO crm_country (id, name,iso_code) VALUES("1230", "Uzbekistan", "UZ");
INSERT INTO crm_country (id, name,iso_code) VALUES("1231", "Vanuatu", "VU");
INSERT INTO crm_country (id, name,iso_code) VALUES("1232", "Venezuela", "VE");
INSERT INTO crm_country (id, name,iso_code) VALUES("1233", "Vietnam", "VN");
INSERT INTO crm_country (id, name,iso_code) VALUES("1234", "Virgin Islands, U.S.", "VI");
INSERT INTO crm_country (id, name,iso_code) VALUES("1235", "Wallis and Futuna", "WF");
INSERT INTO crm_country (id, name,iso_code) VALUES("1236", "Western Sahara", "EH");
INSERT INTO crm_country (id, name,iso_code) VALUES("1237", "Yemen", "YE");
INSERT INTO crm_country (id, name,iso_code) VALUES("1238", "Yugoslavia", "YU");
INSERT INTO crm_country (id, name,iso_code) VALUES("1239", "Zambia", "ZM");
INSERT INTO crm_country (id, name,iso_code) VALUES("1240", "Zimbabwe", "ZW");



#
# Insert data for table 'crm_state_province'
#

INSERT INTO crm_state_province (id, name, abbreviation, country_id) VALUES("1000", "Alabama", "AL", 1228);
INSERT INTO crm_state_province (id, name, abbreviation, country_id) VALUES("1001", "Alaska", "AK", 1228);
INSERT INTO crm_state_province (id, name, abbreviation, country_id) VALUES("1002", "Arizona", "AZ", 1228);
INSERT INTO crm_state_province (id, name, abbreviation, country_id) VALUES("1003", "Arkansas", "AR", 1228);
INSERT INTO crm_state_province (id, name, abbreviation, country_id) VALUES("1004", "California", "CA", 1228);
INSERT INTO crm_state_province (id, name, abbreviation, country_id) VALUES("1005", "Colorado", "CO", 1228);
INSERT INTO crm_state_province (id, name, abbreviation, country_id) VALUES("1006", "Connecticut", "CT", 1228);
INSERT INTO crm_state_province (id, name, abbreviation, country_id) VALUES("1007", "Delaware", "DE", 1228);
INSERT INTO crm_state_province (id, name, abbreviation, country_id) VALUES("1008", "Florida", "FL", 1228);
INSERT INTO crm_state_province (id, name, abbreviation, country_id) VALUES("1009", "Georgia", "GA", 1228);
INSERT INTO crm_state_province (id, name, abbreviation, country_id) VALUES("1010", "Hawaii", "HI", 1228);
INSERT INTO crm_state_province (id, name, abbreviation, country_id) VALUES("1011", "Idaho", "ID", 1228);
INSERT INTO crm_state_province (id, name, abbreviation, country_id) VALUES("1012", "Illinois", "IL", 1228);
INSERT INTO crm_state_province (id, name, abbreviation, country_id) VALUES("1013", "Indiana", "IN", 1228);
INSERT INTO crm_state_province (id, name, abbreviation, country_id) VALUES("1014", "Iowa", "IA", 1228);
INSERT INTO crm_state_province (id, name, abbreviation, country_id) VALUES("1015", "Kansas", "KS", 1228);
INSERT INTO crm_state_province (id, name, abbreviation, country_id) VALUES("1016", "Kentucky", "KY", 1228);
INSERT INTO crm_state_province (id, name, abbreviation, country_id) VALUES("1017", "Louisiana", "LA", 1228);
INSERT INTO crm_state_province (id, name, abbreviation, country_id) VALUES("1018", "Maine", "ME", 1228);
INSERT INTO crm_state_province (id, name, abbreviation, country_id) VALUES("1019", "Maryland", "MD", 1228);
INSERT INTO crm_state_province (id, name, abbreviation, country_id) VALUES("1020", "Massachusetts", "MA", 1228);
INSERT INTO crm_state_province (id, name, abbreviation, country_id) VALUES("1021", "Michigan", "MI", 1228);
INSERT INTO crm_state_province (id, name, abbreviation, country_id) VALUES("1022", "Minnesota", "MN", 1228);
INSERT INTO crm_state_province (id, name, abbreviation, country_id) VALUES("1023", "Mississippi", "MI", 1228);
INSERT INTO crm_state_province (id, name, abbreviation, country_id) VALUES("1024", "Missouri", "MO", 1228);
INSERT INTO crm_state_province (id, name, abbreviation, country_id) VALUES("1025", "Montana", "MT", 1228);
INSERT INTO crm_state_province (id, name, abbreviation, country_id) VALUES("1026", "Nebraska", "NE", 1228);
INSERT INTO crm_state_province (id, name, abbreviation, country_id) VALUES("1027", "Nevada", "NV", 1228);
INSERT INTO crm_state_province (id, name, abbreviation, country_id) VALUES("1028", "New Hampshire", "NV", 1228);
INSERT INTO crm_state_province (id, name, abbreviation, country_id) VALUES("1029", "New Jersey", "NJ", 1228);
INSERT INTO crm_state_province (id, name, abbreviation, country_id) VALUES("1030", "New Mexico", "NM", 1228);
INSERT INTO crm_state_province (id, name, abbreviation, country_id) VALUES("1031", "New York", "NY", 1228);
INSERT INTO crm_state_province (id, name, abbreviation, country_id) VALUES("1032", "North Carolina", "NC", 1228);
INSERT INTO crm_state_province (id, name, abbreviation, country_id) VALUES("1033", "North Dakota", "ND", 1228);
INSERT INTO crm_state_province (id, name, abbreviation, country_id) VALUES("1034", "Ohio", "OH", 1228);
INSERT INTO crm_state_province (id, name, abbreviation, country_id) VALUES("1035", "Oklahoma", "OK", 1228);
INSERT INTO crm_state_province (id, name, abbreviation, country_id) VALUES("1036", "Oregon", "OR", 1228);
INSERT INTO crm_state_province (id, name, abbreviation, country_id) VALUES("1037", "Pennsylvania", "PA", 1228);
INSERT INTO crm_state_province (id, name, abbreviation, country_id) VALUES("1038", "Rhode Island", "RI", 1228);
INSERT INTO crm_state_province (id, name, abbreviation, country_id) VALUES("1039", "South Carolina", "SC", 1228);
INSERT INTO crm_state_province (id, name, abbreviation, country_id) VALUES("1040", "South Dakota", "SD", 1228);
INSERT INTO crm_state_province (id, name, abbreviation, country_id) VALUES("1041", "Tennessee", "TN", 1228);
INSERT INTO crm_state_province (id, name, abbreviation, country_id) VALUES("1042", "Texas", "TX", 1228);
INSERT INTO crm_state_province (id, name, abbreviation, country_id) VALUES("1043", "Utah", "UT", 1228);
INSERT INTO crm_state_province (id, name, abbreviation, country_id) VALUES("1044", "Vermont", "VT", 1228);
INSERT INTO crm_state_province (id, name, abbreviation, country_id) VALUES("1045", "Virginia", "VA", 1228);
INSERT INTO crm_state_province (id, name, abbreviation, country_id) VALUES("1046", "Washington", "WA", 1228);
INSERT INTO crm_state_province (id, name, abbreviation, country_id) VALUES("1047", "West Virginia", "WV", 1228);
INSERT INTO crm_state_province (id, name, abbreviation, country_id) VALUES("1048", "Wisconsin", "WI", 1228);
INSERT INTO crm_state_province (id, name, abbreviation, country_id) VALUES("1049", "Wyoming", "WY", 1228);
INSERT INTO crm_state_province (id, name, abbreviation, country_id) VALUES("1050", "District of Columbia", "DC", 1228);

-- American Territories
INSERT INTO crm_state_province (id, name, abbreviation, country_id) VALUES("1051", "APO", "XX", 1228);
INSERT INTO crm_state_province (id, name, abbreviation, country_id) VALUES("1052", "American Samoa", "AS", 1228);
INSERT INTO crm_state_province (id, name, abbreviation, country_id) VALUES("1053", "Guam", "GU", 1228);
INSERT INTO crm_state_province (id, name, abbreviation, country_id) VALUES("1054", "Marshall Islands", "MH", 1228);
INSERT INTO crm_state_province (id, name, abbreviation, country_id) VALUES("1055", "Northern Mariana Islands", "MP", 1228);
INSERT INTO crm_state_province (id, name, abbreviation, country_id) VALUES("1056", "Puerto Rico", "PR", 1228);
INSERT INTO crm_state_province (id, name, abbreviation, country_id) VALUES("1057", "Virgin Islands", "VI", 1228);

-- Canadian Provinces
INSERT INTO crm_state_province (id, name, abbreviation, country_id) VALUES("1100", "Alberta", "AB", 1039);
INSERT INTO crm_state_province (id, name, abbreviation, country_id) VALUES("1101", "British Columbia", "BC", 1039);
INSERT INTO crm_state_province (id, name, abbreviation, country_id) VALUES("1102", "Manitoba", "MB", 1039);
INSERT INTO crm_state_province (id, name, abbreviation, country_id) VALUES("1103", "New Brunswick", "NB", 1039);
INSERT INTO crm_state_province (id, name, abbreviation, country_id) VALUES("1104", "Newfoundland", "NL", 1039);
INSERT INTO crm_state_province (id, name, abbreviation, country_id) VALUES("1105", "Northwest Territories", "NT", 1039);
INSERT INTO crm_state_province (id, name, abbreviation, country_id) VALUES("1106", "Nova Scotia", "NS", 1039);
INSERT INTO crm_state_province (id, name, abbreviation, country_id) VALUES("1107", "Nunavut", "NU", 1039);
INSERT INTO crm_state_province (id, name, abbreviation, country_id) VALUES("1108", "Ontario", "ON", 1039);
INSERT INTO crm_state_province (id, name, abbreviation, country_id) VALUES("1109", "Prince Edward Island", "PE", 1039);
INSERT INTO crm_state_province (id, name, abbreviation, country_id) VALUES("1110", "Quebec", "QC", 1039);
INSERT INTO crm_state_province (id, name, abbreviation, country_id) VALUES("1111", "Saskatchewan", "SK", 1039);
INSERT INTO crm_state_province (id, name, abbreviation, country_id) VALUES("1112", "Yukon Territory", "YT", 1039);


#
# insert some data for domain, reserved location_types, and reserved relationship_types
#
INSERT INTO crm_domain( name ) VALUES ( 'CRM Test Domain' );

INSERT INTO crm_location_type( domain_id, name, description, is_reserved ) VALUES( 1, 'Home', 'Place of residence', 1 );
INSERT INTO crm_location_type( domain_id, name, description, is_reserved ) VALUES( 1, 'Work', 'Work location', 1 );
INSERT INTO crm_location_type( domain_id, name, description, is_reserved ) VALUES( 1, 'Main', 'Main office location', 0 );
INSERT INTO crm_location_type( domain_id, name, description, is_reserved ) VALUES( 1, 'Other', 'Another location', 0 );

INSERT INTO crm_relationship_type( domain_id, name_a_b, name_b_a, description, contact_type_a, contact_type_b, is_reserved )
    VALUES( 1, 'Child', 'Parent', 'Parent/child relationship.', 'Individual', 'Individual', 1 );
INSERT INTO crm_relationship_type( domain_id, name_a_b, name_b_a, description, contact_type_a, contact_type_b, is_reserved )
    VALUES( 1, 'Spouse', 'Spouse', 'Spousal relationship.', 'Individual', 'Individual', 1 );
INSERT INTO crm_relationship_type( domain_id, name_a_b, name_b_a, description, contact_type_a, contact_type_b, is_reserved )
    VALUES( 1, 'Sibling','Sibling', 'Sibling relationship.','Individual','Individual', 1 );
INSERT INTO crm_relationship_type( domain_id, name_a_b, name_b_a, description, contact_type_a, contact_type_b, is_reserved )
    VALUES( 1, 'Employee', 'Employer', 'Employment relationship.','Individual','Organization', 1 );
INSERT INTO crm_relationship_type( domain_id, name_a_b, name_b_a, description, contact_type_a, contact_type_b, is_reserved )
    VALUES( 1, 'Volunteer', 'Volunteers', 'Volunteer relationship.','Individual','Organization', 1 );
INSERT INTO crm_relationship_type( domain_id, name_a_b, name_b_a, description, contact_type_a, contact_type_b, is_reserved )
    VALUES( 1, 'Head of Household', 'Head of Household', 'Head of household.','Individual','Household', 1 );
INSERT INTO crm_relationship_type( domain_id, name_a_b, name_b_a, description, contact_type_a, contact_type_b, is_reserved )
    VALUES( 1, 'Household Member', 'Household Members', 'Household membership.','Individual','Household', 1 );

INSERT INTO crm_category( domain_id, name, description, parent_category_id )
    VALUES( 1, 'Non-profit', 'Any not-for-profit organization.', NULL );
INSERT INTO crm_category( domain_id, name, description, parent_category_id )
    VALUES( 1, 'Company', 'For-profit organization.', NULL );
INSERT INTO crm_category( domain_id, name, description, parent_category_id )
    VALUES( 1, 'Government Entity', 'Any governmental entity.', NULL );
