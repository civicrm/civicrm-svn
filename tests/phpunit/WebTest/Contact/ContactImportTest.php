<?php

/*
 +--------------------------------------------------------------------+
 | CiviCRM version 3.4                                                |
 +--------------------------------------------------------------------+
 | Copyright CiviCRM LLC (c) 2004-2011                                |
 +--------------------------------------------------------------------+
 | This file is a part of CiviCRM.                                    |
 |                                                                    |
 | CiviCRM is free software; you can copy, modify, and distribute it  |
 | under the terms of the GNU Affero General Public License           |
 | Version 3, 19 November 2007 and the CiviCRM Licensing Exception.   |
 |                                                                    |
 | CiviCRM is distributed in the hope that it will be useful, but     |
 | WITHOUT ANY WARRANTY; without even the implied warranty of         |
 | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.               |
 | See the GNU Affero General Public License for more details.        |
 |                                                                    |
 | You should have received a copy of the GNU Affero General Public   |
 | License along with this program; if not, contact CiviCRM LLC       |
 | at info[AT]civicrm[DOT]org. If you have questions about the        |
 | GNU Affero General Public License or the licensing of CiviCRM,     |
 | see the CiviCRM license FAQ at http://civicrm.org/licensing        |
 +--------------------------------------------------------------------+
*/

require_once 'CiviTest/CiviSeleniumTestCase.php';


 
class WebTest_Contact_ContactImportTest extends CiviSeleniumTestCase {

  protected $captureScreenshotOnFailure = TRUE;
  protected $screenshotPath = '/var/www/api.dev.civicrm.org/public/sc';
  protected $screenshotUrl = 'http://api.dev.civicrm.org/sc/';
    
  protected function setUp()
  {
      parent::setUp();
  }

  function testIndividualImport()
  {

      // This is the path where our testing install resides. 
      // The rest of URL is defined in CiviSeleniumTestCase base class, in
      // class attributes.
      $this->open( $this->sboxPath );

      // Logging in. Remember to wait for page to load. In most cases,
      // you can rely on 30000 as the value that allows your test to pass, however,
      // sometimes your test might fail because of this. In such cases, it's better to pick one element
      // somewhere at the end of page and use waitForElementPresent on it - this assures you, that whole
      // page contents loaded and you can continue your test execution.
      $this->webtestLogin();
      
      // Get sample import data.
      list($headers, $rows) = $this->individualCSVData( );
   
      // Import and check Individual contacts in Skip mode.
      $this->importCSVContacts($headers, $rows);
      
      // Get imported contact Ids
      $importedContactIds = $this->getImportedContactIds( $rows );
          
      // Build update mode import headers
      $updateHeaders = array( 'contact_id' => 'Internal Contact ID',
                              'first_name' => 'First Name',
                              'last_name'  => 'Last Name' );
      
      // Create update mode import rows
      $updateRows = array( );
      foreach ( $importedContactIds as $cid ) {
          $updateRows[$cid] = array( 'contact_id' => $cid,
                                     'first_name' => substr(sha1(rand()), 0, 7),
                                     'last_name'  => 'Anderson' . substr(sha1(rand()), 0, 7)  );
      }
      
      // Import and check Individual contacts in Update mode.
      $this->importCSVContacts( $updateHeaders, $updateRows, 'Individual', 'Update' ); 
      
      // Visit contacts to check updated data.
      foreach ( $updateRows as $updatedRow ) {
          $this->open($this->sboxPath . "civicrm/contact/view?reset=1&cid={$updatedRow['contact_id']}");
          $this->waitForPageToLoad("30000");

          $displayName = "{$updatedRow['first_name']} {$updatedRow['last_name']}"; 
          $this->assertTrue($this->isTextPresent("$displayName"), "Contact did not update!");
      }
      
      // Headers that should not updated.
      $fillHeaders = $updateHeaders;
      
      // Headers that should fill.
      $fillHeaders['gender'] = 'Gender';
      $fillHeaders['dob']    = 'Birth Date';
      
      $fillRows = array( );
      foreach ( $importedContactIds as $cid ) {
          $fillRows[$cid] = array( 'contact_id' => $cid,
                                   'first_name' => substr(sha1(rand()), 0, 7), // should not update
                                   'last_name'  => 'Anderson' . substr(sha1(rand()), 0, 7), // should not update
                                   'gender'     => 'Male',
                                   'dob'        => '1986-04-16'
                                   );
      }
      
      // Import and check Individual contacts in Update mode.
      $this->importCSVContacts( $fillHeaders, $fillRows, 'Individual', 'Fill' );

      // Visit contacts to check filled data.
      foreach ( $fillRows as $cid => $fillRow ) {
          $this->open($this->sboxPath . "civicrm/contact/view?reset=1&cid={$fillRow['contact_id']}");
          $this->waitForPageToLoad("30000");

          // Check old display name.
          $displayName = "{$updateRows[$cid]['first_name']} {$updateRows[$cid]['last_name']}"; 
          $this->assertTrue($this->isTextPresent("$displayName"), "Contact should not update in fill mode!");

          $this->verifyText("xpath=//div[@id='contact-summary']/div[2]/div[3]/div[2]/table/tbody/tr[1]/td[2]", preg_quote($fillRow['gender']));
      } 
      
  }
  
  function testOrganizationImport()
  {
      $this->open( $this->sboxPath );
      
      $this->webtestLogin();
      
      // Get sample import data.
      list($headers, $rows) = $this->organizationCSVData( );
   
      // Import and check Organization contacts
      $this->importCSVContacts($headers, $rows, 'Organization' );

      // Get imported contact Ids
      $importedContactIds = $this->getImportedContactIds( $rows, 'Organization' );
          
      // Build update mode import headers
      $updateHeaders = array( 'contact_id'        => 'Internal Contact ID',
                              'organization_name' => 'Organization Name',
                              );
      
      // Create update mode import rows
      $updateRows = array( );
      foreach ( $importedContactIds as $cid ) {
          $updateRows[$cid] = array( 'contact_id'        => $cid,
                                     'organization_name' => 'UpdatedOrg ' . substr(sha1(rand()), 0, 7) );
      }
      
      // Import and check Individual contacts in Update mode.
      $this->importCSVContacts( $updateHeaders, $updateRows, 'Organization', 'Update' ); 
      
      // Visit contacts to check updated data.
      foreach ( $updateRows as $updatedRow ) {
          $organizationName = $updatedRow['organization_name']; 
          $this->open($this->sboxPath . "civicrm/contact/view?reset=1&cid={$updatedRow['contact_id']}");
          $this->waitForPageToLoad("30000");

          $this->assertTrue($this->isTextPresent("$organizationName"), "Contact did not update!");
      }
     
      // Headers that should not updated.
      $fillHeaders = $updateHeaders;
      
      // Headers that should fill.
      $fillHeaders['legal_name'] = 'Legal Name';
      
      $fillRows = array( );
      foreach ( $importedContactIds as $cid ) {
          $fillRows[$cid] = array( 'contact_id'        => $cid,
                                   'organization_name' => 'UpdateOrg ' . substr(sha1(rand()), 0, 7), // should not update
                                   'legal_name'        => 'org '. substr(sha1(rand()), 0, 7)
                                   );
      }
      
      // Import and check Individual contacts in Update mode.
      $this->importCSVContacts( $fillHeaders, $fillRows, 'Organization', 'Fill' );

      // Visit contacts to check filled data.
      foreach ( $fillRows as $cid => $fillRow ) {
          $this->open($this->sboxPath . "civicrm/contact/view?reset=1&cid={$fillRow['contact_id']}");
          $this->waitForPageToLoad("30000");

          // Check old Organization name.
          $organizationName = $updateRows[$cid]['organization_name']; 
          $this->assertTrue($this->isTextPresent("$organizationName"), "Contact should not update in fill mode!");
          $this->verifyText("xpath=//div[@id='contactTopBar']/table/tbody/tr/td[4]", preg_quote($fillRow['legal_name']));
      }
      
  }

  function testHouseholdImport() 
  {
      $this->open( $this->sboxPath );
      
      $this->webtestLogin();
      
      // Get sample import data.
      list($headers, $rows) = $this->householdCSVData( );
   
      // Import and check Household contacts
      $this->importCSVContacts($headers, $rows, 'Household');

      // Get imported contact Ids
      $importedContactIds = $this->getImportedContactIds($rows, 'Household');
      
      // Build update mode import headers
      $updateHeaders = array( 'contact_id'     => 'Internal Contact ID',
                              'household_name' => 'Household Name'
                              );
      
      // Create update mode import rows
      $updateRows = array( );
      foreach ( $importedContactIds as $cid ) {
          $updateRows[$cid] = array( 'contact_id'     => $cid,
                                     'household_name' => 'UpdatedHousehold ' . substr(sha1(rand()), 0, 7) );
      }
      
      // Import and check Individual contacts in Update mode.
      $this->importCSVContacts( $updateHeaders, $updateRows, 'Household', 'Update'); 
      
      // Visit contacts to check updated data.
      foreach ( $updateRows as $updatedRow ) {
          $householdName = $updatedRow['household_name']; 
          $this->open($this->sboxPath . "civicrm/contact/view?reset=1&cid={$updatedRow['contact_id']}");
          $this->waitForPageToLoad("30000");

          $this->assertTrue($this->isTextPresent("$householdName"), "Contact did not update!");
      }   

     // Headers that should not updated.
      $fillHeaders = $updateHeaders;
      
      // Headers that should fill.
      $fillHeaders['nick_name'] = 'Nick Name';
      
      $fillRows = array( );
      foreach ( $importedContactIds as $cid ) {
          $fillRows[$cid] = array( 'contact_id'     => $cid,
                                   'household_name' => 'UpdatedHousehold ' . substr(sha1(rand()), 0, 7), // should not update
                                   'nick_name'      => 'Household '. substr(sha1(rand()), 0, 7)
                                   );
      }
      
      // Import and check Individual contacts in Update mode.
      $this->importCSVContacts( $fillHeaders, $fillRows, 'Household', 'Fill' );
      
      // Visit contacts to check filled data.
      foreach ( $fillRows as $cid => $fillRow ) {
          $this->open($this->sboxPath . "civicrm/contact/view?reset=1&cid={$fillRow['contact_id']}");
          $this->waitForPageToLoad("30000");

          // Check old Household name.
          $householdName = $updateRows[$cid]['household_name']; 
          $this->assertTrue($this->isTextPresent("$householdName"), "Contact should not update in fill mode!");
          $this->verifyText("xpath=//div[@id='contactTopBar']/table/tbody/tr/td[4]", preg_quote($fillRow['nick_name']));
      }
   
  }

  function importCSVContacts( $headers, $rows, $contactType = 'Individual', $mode = 'Skip' ) {
      
      // Go to contact import page.
      $this->open($this->sboxPath . "civicrm/import/contact?reset=1");
      $this->waitForPageToLoad( '30000' );
      
      // check for upload field.
      $this->waitForElementPresent("uploadFile");
      
      // Create csv file of sample data.
      $csvFile = $this->webtestCreateCSV($headers, $rows);

      // Attach csv file.
      $this->webtestAttachFile('uploadFile', $csvFile);
      
      // First row is header.
      $this->click('skipColumnHeader');
      
      // select mode, default is 'Skip'.
      if ( $mode == 'Update' ) {
          $this->click("CIVICRM_QFID_4_4");
      } else if ( $mode == 'Fill' ) {
          $this->click("CIVICRM_QFID_8_6");
      } else if ( $mode == 'No Duplicate Checking' ) {
          $this->click("CIVICRM_QFID_16_8");  
      }

      // select contact type, default is 'Individual'.
      if ( $contactType == 'Organization' ) {
          $this->click("CIVICRM_QFID_4_14");
      } else if ( $contactType == 'Household' ) {
          $this->click("CIVICRM_QFID_2_12");
      }

      // Submit form.
      $this->click('_qf_DataSource_upload');
      $this->waitForPageToLoad("30000");
      
      // Check mapping data.
      $this->checkImportMapperData($headers, $rows);
      
      // Create new mapping
      $this->click('saveMapping');
      $mappingName = 'contactimport_'.substr(sha1(rand()), 0, 7);
      $this->type('saveMappingName', $mappingName);
      $this->type('saveMappingDesc', "Mapping for {$contactType}" );

      // Submit form.
      $this->click('_qf_MapField_next');
      $this->waitForPageToLoad("30000");

      // Check mapping data.
      $this->checkImportMapperData($headers, $rows);
      
      // Add imported contacts in new group.
      $this->click( "css=#new-group div.crm-accordion-header" );
      $groupName = "{$contactType} Group " . substr(sha1(rand()), 0, 7);
      $this->type('newGroupName', $groupName);
      $this->type('newGroupDesc', "Group For {$contactType}" );

      // Assign new tag to the imported contacts.
      $this->click( "css=#new-tag div.crm-accordion-header" );
      $tagName = "{$contactType}_".substr(sha1(rand()), 0, 7);
      $this->type('newTagName', $tagName);
      $this->type('newTagDesc', "Tag for {$contactType}" );
      
      // Submit form.
      $this->click('_qf_Preview_next');
      sleep(2);
      
      // Check confirmation alert.
      $this->assertTrue( (bool)preg_match("/^Are you sure you want to Import now[\s\S]$/", $this->getConfirmation()) );
      $this->chooseOkOnNextConfirmation( );
      
      // Check import screen.
      $this->waitForElementPresent("id-processing");
      sleep(10);

      // Visit summary page.
      $this->waitForElementPresent("_qf_Summary_next");

      // Check success message.
      $this->assertTrue($this->isTextPresent("Import has completed successfully. The information below summarizes the results."));

      // Check summary Details.
      $importedContacts = count($rows);
      $checkSummary = array( 'Total Rows'               => $importedContacts,
                             'Total Contacts'           => $importedContacts,
                             'Import to Groups'         => "{$groupName}: {$importedContacts} contacts added to this new group.",
                             'Tagged Imported Contacts' => "{$tagName}: {$importedContacts} contacts are tagged with this tag."
                             );
      
      foreach( $checkSummary as $label => $value ) {
          $this->verifyText("xpath=//table[@id='summary-counts']/tbody/tr/td[text()='{$label}']/following-sibling::td", preg_quote($value));
      }
        
  }

  function checkImportMapperData( $headers, $rows ) {

      $checkMapperHeaders = array( 1 => 'Column Names',
                                   2 => 'Import Data (row 1)',
                                   3 => 'Import Data (row 2)',
                                   4 => 'Matching CiviCRM Field' );
      
      foreach ($checkMapperHeaders as $rownum => $value ) {
          $this->verifyText("xpath=//div[@id='map-field']//table[@class='selector']/tbody/tr[1]/td[{$rownum}]", preg_quote($value));
      }

      $rownum = 2;
      foreach ( $headers as $field => $header ) {
          $this->verifyText("xpath=//div[@id='map-field']//table[@class='selector']/tbody/tr[{$rownum}]/td[1]", preg_quote($header));
          $colnum = 2;
          foreach( $rows as $row ) {
              $this->verifyText("xpath=//div[@id='map-field']//table[@class='selector']/tbody/tr[{$rownum}]/td[{$colnum}]", preg_quote($row[$field]));  
              $colnum++;   
          }
          $rownum++;
      }

  }
  
  function getImportedContactIds($rows, $contactType = 'Individual') {
      $contactIds = array( );

      foreach ( $rows as $row ) {
          $searchName = '';
          
          // Build search name.
          if ( $contactType == 'Individual' ) {
              $searchName = "{$row['last_name']}, {$row['first_name']}";
          } else if ( $contactType == 'Organization' ) {
              $searchName = $row['organization_name'];
          } else if ( $contactType == 'Household' ) {
              $searchName = $row['household_name'];
          }
          
          $this->open($this->sboxPath . "civicrm/dashboard?reset=1");
          $this->waitForPageToLoad("30000");

          
          // Type search name in autocomplete.
          $this->typeKeys("css=input#sort_name_navigation", $searchName);
          $this->click("css=input#sort_name_navigation");
          
          // Wait for result list.
          $this->waitForElementPresent("css=div.ac_results-inner li");
          
          // Visit contact summary page.
          $this->click("css=div.ac_results-inner li");
          $this->waitForPageToLoad("30000");

          // Get contact id from url.
          $matches = array();
          preg_match('/cid=([0-9]+)/', $this->getLocation(), $matches);
          $contactIds[]  = $matches[1];
      }
      
      return $contactIds;
  }

  function individualCSVData( ) {
      $headers = array( 'first_name'  => 'First Name',
                        'middle_name' => 'Middle Name',
                        'last_name'   => 'Last Name',
                        'email'       => 'Email',
                        'phone'       => 'Phone',  
                        'address_1'   => 'Additional Address 1',
                        'address_2'   => 'Additional Address 2',
                        'city'        => 'City',
                        'state'       => 'State',
                        'country'     => 'Country'
                        );
      
      $rows = 
          array( 
                array(  'first_name'  => substr(sha1(rand()), 0, 7),
                        'middle_name' => substr(sha1(rand()), 0, 7) ,
                        'last_name'   => 'Anderson',
                        'email'       => substr(sha1(rand()), 0, 7).'@example.com',
                        'phone'       => '6949912154',  
                        'address_1'   => 'Add 1',
                        'address_2'   => 'Add 2',
                        'city'        => 'Watson',
                        'state'       => 'NY',
                        'country'     => 'United States'
                        ),
                
                array(  'first_name'  => substr(sha1(rand()), 0, 7),
                        'middle_name' => substr(sha1(rand()), 0, 7) ,
                        'last_name'   => 'Summerson',
                        'email'       => substr(sha1(rand()), 0, 7).'@example.com',
                        'phone'       => '6944412154',  
                        'address_1'   => 'Add 1',
                        'address_2'   => 'Add 2',
                        'city'        => 'Watson',
                        'state'       => 'NY',
                        'country'     => 'United States'
                        )
                 );

      return array($headers, $rows);
  }

  function organizationCSVData( ) {
      $headers = array( 'organization_name' => 'Organization Name',
                        'email'             => 'Email',
                        'phone'             => 'Phone',  
                        'address_1'         => 'Additional Address 1',
                        'address_2'         => 'Additional Address 2',
                        'city'              => 'City',
                        'state'             => 'State',
                        'country'           => 'Country'
                        );
      
      $rows = 
          array( 
                array(  'organization_name' => 'org_' . substr(sha1(rand()), 0, 7),
                        'email'             => substr(sha1(rand()), 0, 7).'@example.org',
                        'phone'             => '9949912154',  
                        'address_1'         => 'Add 1',
                        'address_2'         => 'Add 2',
                        'city'              => 'Watson',
                        'state'             => 'NY',
                        'country'           => 'United States'
                        ),
                
                array(  'organization_name' => 'org_' . substr(sha1(rand()), 0, 7),
                        'email'             => substr(sha1(rand()), 0, 7).'@example.org',
                        'phone'             => '6949412154',  
                        'address_1'         => 'Add 1',
                        'address_2'         => 'Add 2',
                        'city'              => 'Watson',
                        'state'             => 'NY',
                        'country'           => 'United States'
                        )
                 );

      return array($headers, $rows);
  }

  function householdCSVData( ) {
      $headers = array( 'household_name' => 'Household Name',
                        'email'          => 'Email',
                        'phone'          => 'Phone',  
                        'address_1'      => 'Additional Address 1',
                        'address_2'      => 'Additional Address 2',
                        'city'           => 'City',
                        'state'          => 'State',
                        'country'        => 'Country'
                        );
      
      $rows = 
          array( 
                array(  'household_name' => 'household_' . substr(sha1(rand()), 0, 7),
                        'email'          => substr(sha1(rand()), 0, 7).'@example.org',
                        'phone'          => '3949912154',  
                        'address_1'      => 'Add 1',
                        'address_2'      => 'Add 2',
                        'city'           => 'Watson',
                        'state'          => 'NY',
                        'country'        => 'United States'
                        ),
                
                array(  'household_name' => 'household_' . substr(sha1(rand()), 0, 7),
                        'email'          => substr(sha1(rand()), 0, 7).'@example.org',
                        'phone'          => '5949412154',  
                        'address_1'      => 'Add 1',
                        'address_2'      => 'Add 2',
                        'city'           => 'Watson',
                        'state'          => 'NY',
                        'country'        => 'United States'
                        )
                 );

      return array($headers, $rows);
  }
}