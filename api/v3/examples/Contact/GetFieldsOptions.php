<?php

/*
 Demonstrate retrieving custom field options
 */
function contact_getfields_example(){
$params = array( 
  'options' => array( 
      'get_options' => 'custom_1',
    ),
  'version' => 3,
  'action' => 'create',
);

  $result = civicrm_api( 'contact','GetFields',$params );

  return $result;
}

/*
 * Function returns array of result expected from previous function
 */
function contact_getfields_expectedresult(){

  $expectedResult = array( 
  'is_error' => 0,
  'version' => 3,
  'count' => 52,
  'values' => array( 
      'id' => array( 
          'name' => 'id',
          'type' => 1,
          'title' => 'Internal Contact ID',
          'required' => true,
          'import' => true,
          'where' => 'civicrm_contact.id',
          'headerPattern' => '/internal|contact?|id$/i',
          'export' => true,
          'api.aliases' => array( 
              '0' => 'contact_id',
            ),
        ),
      'contact_type' => array( 
          'name' => 'contact_type',
          'type' => 2,
          'title' => 'Contact Type',
          'maxlength' => 64,
          'size' => 30,
          'export' => true,
          'where' => 'civicrm_contact.contact_type',
          'pseudoconstant' => array( 
              'name' => 'contactType',
              'table' => 'civicrm_location_type',
              'keyColumn' => 'name',
              'labelColumn' => 'label',
            ),
          'api.required' => 1,
          'options' => array( 
              'Individual' => 'Individual',
              'Household' => 'Household',
              'Organization' => 'Organization',
            ),
        ),
      'contact_sub_type' => array( 
          'name' => 'contact_sub_type',
          'type' => 2,
          'title' => 'Contact Subtype',
          'maxlength' => 255,
          'size' => 45,
          'import' => true,
          'where' => 'civicrm_contact.contact_sub_type',
          'headerPattern' => '/C(ontact )?(subtype|sub-type|sub type)/i',
          'export' => true,
        ),
      'do_not_email' => array( 
          'name' => 'do_not_email',
          'type' => 16,
          'title' => 'Do Not Email',
          'import' => true,
          'where' => 'civicrm_contact.do_not_email',
          'headerPattern' => '/d(o )?(not )?(email)/i',
          'dataPattern' => '/^\d{1,}$/',
          'export' => true,
        ),
      'do_not_phone' => array( 
          'name' => 'do_not_phone',
          'type' => 16,
          'title' => 'Do Not Phone',
          'import' => true,
          'where' => 'civicrm_contact.do_not_phone',
          'headerPattern' => '/d(o )?(not )?(call|phone)/i',
          'dataPattern' => '/^\d{1,}$/',
          'export' => true,
        ),
      'do_not_mail' => array( 
          'name' => 'do_not_mail',
          'type' => 16,
          'title' => 'Do Not Mail',
          'import' => true,
          'where' => 'civicrm_contact.do_not_mail',
          'headerPattern' => '/^(d(o\s)?n(ot\s)?mail)|(\w*)?bulk\s?(\w*)$/i',
          'dataPattern' => '/^\d{1,}$/',
          'export' => true,
        ),
      'do_not_sms' => array( 
          'name' => 'do_not_sms',
          'type' => 16,
          'title' => 'Do Not Sms',
          'import' => true,
          'where' => 'civicrm_contact.do_not_sms',
          'headerPattern' => '/d(o )?(not )?(sms)/i',
          'dataPattern' => '/^\d{1,}$/',
          'export' => true,
        ),
      'do_not_trade' => array( 
          'name' => 'do_not_trade',
          'type' => 16,
          'title' => 'Do Not Trade',
          'import' => true,
          'where' => 'civicrm_contact.do_not_trade',
          'headerPattern' => '/d(o )?(not )?(trade)/i',
          'dataPattern' => '/^\d{1,}$/',
          'export' => true,
        ),
      'is_opt_out' => array( 
          'name' => 'is_opt_out',
          'type' => 16,
          'title' => 'No Bulk Emails (User Opt Out)',
          'required' => true,
          'import' => true,
          'where' => 'civicrm_contact.is_opt_out',
          'export' => true,
        ),
      'legal_identifier' => array( 
          'name' => 'legal_identifier',
          'type' => 2,
          'title' => 'Legal Identifier',
          'maxlength' => 32,
          'size' => 20,
          'import' => true,
          'where' => 'civicrm_contact.legal_identifier',
          'headerPattern' => '/legal\s?id/i',
          'dataPattern' => '/\w+?\d{5,}/',
          'export' => true,
        ),
      'external_identifier' => array( 
          'name' => 'external_identifier',
          'type' => 2,
          'title' => 'External Identifier',
          'maxlength' => 32,
          'size' => 8,
          'import' => true,
          'where' => 'civicrm_contact.external_identifier',
          'headerPattern' => '/external\s?id/i',
          'dataPattern' => '/^\d{11,}$/',
          'export' => true,
        ),
      'sort_name' => array( 
          'name' => 'sort_name',
          'type' => 2,
          'title' => 'Sort Name',
          'maxlength' => 128,
          'size' => 30,
          'export' => true,
          'where' => 'civicrm_contact.sort_name',
        ),
      'display_name' => array( 
          'name' => 'display_name',
          'type' => 2,
          'title' => 'Display Name',
          'maxlength' => 128,
          'size' => 30,
          'export' => true,
          'where' => 'civicrm_contact.display_name',
        ),
      'nick_name' => array( 
          'name' => 'nick_name',
          'type' => 2,
          'title' => 'Nick Name',
          'maxlength' => 128,
          'size' => 30,
          'import' => true,
          'where' => 'civicrm_contact.nick_name',
          'headerPattern' => '/n(ick\s)name|nick$/i',
          'dataPattern' => '/^\w+$/',
          'export' => true,
        ),
      'legal_name' => array( 
          'name' => 'legal_name',
          'type' => 2,
          'title' => 'Legal Name',
          'maxlength' => 128,
          'size' => 30,
          'import' => true,
          'where' => 'civicrm_contact.legal_name',
          'headerPattern' => '/^legal|(l(egal\s)?name)$/i',
          'export' => true,
        ),
      'image_URL' => array( 
          'name' => 'image_URL',
          'type' => 2,
          'title' => 'Image Url',
          'maxlength' => 255,
          'size' => 45,
          'import' => true,
          'where' => 'civicrm_contact.image_URL',
          'export' => true,
        ),
      'preferred_communication_method' => array( 
          'name' => 'preferred_communication_method',
          'type' => 2,
          'title' => 'Preferred Communication Method',
          'maxlength' => 255,
          'size' => 45,
          'import' => true,
          'where' => 'civicrm_contact.preferred_communication_method',
          'headerPattern' => '/^p(ref\w*\s)?c(omm\w*)|( meth\w*)$/i',
          'dataPattern' => '/^\w+$/',
          'export' => true,
        ),
      'preferred_language' => array( 
          'name' => 'preferred_language',
          'type' => 2,
          'title' => 'Preferred Language',
          'maxlength' => 5,
          'size' => 6,
          'import' => true,
          'where' => 'civicrm_contact.preferred_language',
          'headerPattern' => '/^lang/i',
          'export' => true,
        ),
      'preferred_mail_format' => array( 
          'name' => 'preferred_mail_format',
          'type' => 2,
          'title' => 'Preferred Mail Format',
          'import' => true,
          'where' => 'civicrm_contact.preferred_mail_format',
          'headerPattern' => '/^p(ref\w*\s)?m(ail\s)?f(orm\w*)$/i',
          'export' => true,
          'default' => 'Both',
          'enumValues' => 'Text, HTML, Both',
          'options' => array( 
              '0' => 'Text',
              '1' => 'HTML',
              '2' => 'Both',
            ),
        ),
      'hash' => array( 
          'name' => 'hash',
          'type' => 2,
          'title' => 'Contact Hash',
          'maxlength' => 32,
          'size' => 20,
          'export' => true,
          'where' => 'civicrm_contact.hash',
        ),
      'api_key' => array( 
          'name' => 'api_key',
          'type' => 2,
          'title' => 'Api Key',
          'maxlength' => 32,
          'size' => 20,
        ),
      'first_name' => array( 
          'name' => 'first_name',
          'type' => 2,
          'title' => 'First Name',
          'maxlength' => 64,
          'size' => 30,
          'import' => true,
          'where' => 'civicrm_contact.first_name',
          'headerPattern' => '/^first|(f(irst\s)?name)$/i',
          'dataPattern' => '/^\w+$/',
          'export' => true,
        ),
      'middle_name' => array( 
          'name' => 'middle_name',
          'type' => 2,
          'title' => 'Middle Name',
          'maxlength' => 64,
          'size' => 20,
          'import' => true,
          'where' => 'civicrm_contact.middle_name',
          'headerPattern' => '/^middle|(m(iddle\s)?name)$/i',
          'dataPattern' => '/^\w+$/',
          'export' => true,
        ),
      'last_name' => array( 
          'name' => 'last_name',
          'type' => 2,
          'title' => 'Last Name',
          'maxlength' => 64,
          'size' => 30,
          'import' => true,
          'where' => 'civicrm_contact.last_name',
          'headerPattern' => '/^last|(l(ast\s)?name)$/i',
          'dataPattern' => '/^\w+(\s\w+)?+$/',
          'export' => true,
        ),
      'prefix_id' => array( 
          'name' => 'prefix_id',
          'type' => 1,
          'title' => 'Individual Prefix',
          'pseudoconstant' => array( 
              'name' => 'individualPrefix',
              'optionGroupName' => 'individualPrefix',
            ),
          'api.aliases' => array( 
              '0' => 'prefix',
            ),
          'options' => array( 
              '1' => 'Mrs.',
              '2' => 'Ms.',
              '3' => 'Mr.',
              '4' => 'Dr.',
            ),
        ),
      'suffix_id' => array( 
          'name' => 'suffix_id',
          'type' => 1,
          'title' => 'Individual Suffix',
          'pseudoconstant' => array( 
              'name' => 'individualSuffix',
              'optionGroupName' => 'individualSuffix',
            ),
          'api.aliases' => array( 
              '0' => 'suffix',
            ),
          'options' => array( 
              '1' => 'Jr.',
              '2' => 'Sr.',
              '3' => 'II',
              '4' => 'III',
              '5' => 'IV',
              '6' => 'V',
              '7' => 'VI',
              '8' => 'VII',
            ),
        ),
      'email_greeting_id' => array( 
          'name' => 'email_greeting_id',
          'type' => 1,
          'title' => 'Email Greeting ID',
        ),
      'email_greeting_custom' => array( 
          'name' => 'email_greeting_custom',
          'type' => 2,
          'title' => 'Email Greeting Custom',
          'maxlength' => 128,
          'size' => 45,
          'import' => true,
          'where' => 'civicrm_contact.email_greeting_custom',
        ),
      'email_greeting_display' => array( 
          'name' => 'email_greeting_display',
          'type' => 2,
          'title' => 'Email Greeting',
          'maxlength' => 255,
          'size' => 45,
        ),
      'postal_greeting_id' => array( 
          'name' => 'postal_greeting_id',
          'type' => 1,
          'title' => 'Postal Greeting ID',
        ),
      'postal_greeting_custom' => array( 
          'name' => 'postal_greeting_custom',
          'type' => 2,
          'title' => 'Postal Greeting Custom',
          'maxlength' => 128,
          'size' => 45,
          'import' => true,
          'where' => 'civicrm_contact.postal_greeting_custom',
        ),
      'postal_greeting_display' => array( 
          'name' => 'postal_greeting_display',
          'type' => 2,
          'title' => 'Postal Greeting',
          'maxlength' => 255,
          'size' => 45,
        ),
      'addressee_id' => array( 
          'name' => 'addressee_id',
          'type' => 1,
          'title' => 'Addressee ID',
        ),
      'addressee_custom' => array( 
          'name' => 'addressee_custom',
          'type' => 2,
          'title' => 'Addressee Custom',
          'maxlength' => 128,
          'size' => 45,
          'import' => true,
          'where' => 'civicrm_contact.addressee_custom',
        ),
      'addressee_display' => array( 
          'name' => 'addressee_display',
          'type' => 2,
          'title' => 'Addressee',
          'maxlength' => 255,
          'size' => 45,
        ),
      'job_title' => array( 
          'name' => 'job_title',
          'type' => 2,
          'title' => 'Job Title',
          'maxlength' => 255,
          'size' => 20,
          'import' => true,
          'where' => 'civicrm_contact.job_title',
          'headerPattern' => '/^job|(j(ob\s)?title)$/i',
          'dataPattern' => '//',
          'export' => true,
        ),
      'gender_id' => array( 
          'name' => 'gender_id',
          'type' => 1,
          'title' => 'Gender',
        ),
      'birth_date' => array( 
          'name' => 'birth_date',
          'type' => 4,
          'title' => 'Birth Date',
          'import' => true,
          'where' => 'civicrm_contact.birth_date',
          'headerPattern' => '/^birth|(b(irth\s)?date)|D(\W*)O(\W*)B(\W*)$/i',
          'dataPattern' => '/\d{4}-?\d{2}-?\d{2}/',
          'export' => true,
        ),
      'is_deceased' => array( 
          'name' => 'is_deceased',
          'type' => 16,
          'title' => 'Is Deceased',
          'import' => true,
          'where' => 'civicrm_contact.is_deceased',
          'headerPattern' => '/i(s\s)?d(eceased)$/i',
          'export' => true,
        ),
      'deceased_date' => array( 
          'name' => 'deceased_date',
          'type' => 4,
          'title' => 'Deceased Date',
          'import' => true,
          'where' => 'civicrm_contact.deceased_date',
          'headerPattern' => '/^deceased|(d(eceased\s)?date)$/i',
          'export' => true,
        ),
      'household_name' => array( 
          'name' => 'household_name',
          'type' => 2,
          'title' => 'Household Name',
          'maxlength' => 128,
          'size' => 30,
          'import' => true,
          'where' => 'civicrm_contact.household_name',
          'headerPattern' => '/^household|(h(ousehold\s)?name)$/i',
          'dataPattern' => '/^\w+$/',
          'export' => true,
        ),
      'primary_contact_id' => array( 
          'name' => 'primary_contact_id',
          'type' => 1,
          'title' => 'Household Primary Contact ID',
          'FKClassName' => 'CRM_Contact_DAO_Contact',
        ),
      'organization_name' => array( 
          'name' => 'organization_name',
          'type' => 2,
          'title' => 'Organization Name',
          'maxlength' => 128,
          'size' => 30,
          'import' => true,
          'where' => 'civicrm_contact.organization_name',
          'headerPattern' => '/^organization|(o(rganization\s)?name)$/i',
          'dataPattern' => '/^\w+$/',
          'export' => true,
        ),
      'sic_code' => array( 
          'name' => 'sic_code',
          'type' => 2,
          'title' => 'Sic Code',
          'maxlength' => 8,
          'size' => 8,
          'import' => true,
          'where' => 'civicrm_contact.sic_code',
          'headerPattern' => '/^sic|(s(ic\s)?code)$/i',
          'export' => true,
        ),
      'user_unique_id' => array( 
          'name' => 'user_unique_id',
          'type' => 2,
          'title' => 'Unique ID (OpenID)',
          'maxlength' => 255,
          'size' => 45,
          'import' => true,
          'where' => 'civicrm_contact.user_unique_id',
          'headerPattern' => '/^Open\s?ID|u(niq\w*)?\s?ID/i',
          'dataPattern' => '/^[\w\/\:\.]+$/',
          'export' => true,
          'rule' => 'url',
        ),
      'created_date' => array( 
          'name' => 'created_date',
          'type' => 256,
          'title' => 'Created Date',
          'required' => '',
          'default' => 'UL',
        ),
      'modified_date' => array( 
          'name' => 'modified_date',
          'type' => 256,
          'title' => 'Modified Date',
          'required' => '',
          'default' => 'URRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAM',
        ),
      'source' => array( 
          'name' => 'source',
          'type' => 2,
          'title' => 'Source of Contact Data',
          'maxlength' => 255,
          'size' => 30,
          'import' => true,
          'where' => 'civicrm_contact.source',
          'headerPattern' => '/(S(ource\s)?o(f\s)?C(ontact\s)?Data)$/i',
          'export' => true,
          'uniqueName' => 'contact_source',
        ),
      'employer_id' => array( 
          'name' => 'employer_id',
          'type' => 1,
          'title' => 'Current Employer ID',
          'export' => true,
          'where' => 'civicrm_contact.employer_id',
          'FKClassName' => 'CRM_Contact_DAO_Contact',
          'uniqueName' => 'current_employer_id',
        ),
      'is_deleted' => array( 
          'name' => 'is_deleted',
          'type' => 16,
          'title' => 'Contact is in Trash',
          'required' => true,
          'export' => true,
          'where' => 'civicrm_contact.is_deleted',
          'uniqueName' => 'contact_is_deleted',
        ),
      'custom_1' => array( 
          'label' => 'Country',
          'groupTitle' => 'select_test_group',
          'data_type' => 'String',
          'html_type' => 'Select',
          'text_length' => '',
          'options_per_line' => '',
          'extends' => 'Contact',
          'is_search_range' => 0,
          'extends_entity_column_value' => '',
          'extends_entity_column_id' => '',
          'is_view' => 0,
          'is_multiple' => 0,
          'option_group_id' => '86',
          'date_format' => '',
          'time_format' => '',
          'options' => array( 
              '1' => 'Label1',
              '2' => 'Label2',
            ),
        ),
      'current_employer' => array( 
          'title' => 'Current Employer',
          'description' => 'Name of Current Employer',
        ),
    ),
);

  return $expectedResult  ;
}


/*
* This example has been generated from the API test suite. The test that created it is called
*
* testCustomFieldCreateWithOptionValues and can be found in
* http://svn.civicrm.org/civicrm/trunk/tests/phpunit/CiviTest/api/v3/ContactTest.php
*
* You can see the outcome of the API tests at
* http://tests.dev.civicrm.org/trunk/results-api_v3
*
* To Learn about the API read
* http://book.civicrm.org/developer/current/techniques/api/
*
* and review the wiki at
* http://wiki.civicrm.org/confluence/display/CRMDOC/CiviCRM+Public+APIs
*
* Read more about testing here
* http://wiki.civicrm.org/confluence/display/CRM/Testing
*
* API Standards documentation:
* http://wiki.civicrm.org/confluence/display/CRM/API+Architecture+Standards
*/