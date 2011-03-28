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

      // Go to contact import page.
      $this->open($this->sboxPath . "civicrm/import/contact?reset=1");
      $this->waitForPageToLoad( '30000' );
      
      // check for upload field.
      $this->waitForElementPresent("uploadFile");
      
      // Get sample import data.
      list($headers, $rows) = $this->individualCSVData( );

      // Create csv file of sample data.
      $csvFile = $this->webtestCreateCSV($headers, $rows);

      // Attach csv file.
      $this->webtestAttachFile('uploadFile', $csvFile);
      
      // First row is header.
      $this->click('skipColumnHeader');

      // Submit form.
      $this->click('_qf_DataSource_upload');
      $this->waitForPageToLoad("30000");
      
      // Check mapping data.
      $this->checkImportMapperData($headers, $rows);
      
      // Create new mapping
      $this->click('saveMapping');
      $mappingName = 'contactimport_'.substr(sha1(rand()), 0, 7);
      $this->type('saveMappingName', $mappingName);
      $this->type('saveMappingDesc', 'Mapping for Individuals' );

      // Submit form.
      $this->click('_qf_MapField_next');
      $this->waitForPageToLoad("30000");

      // Check mapping data.
      $this->checkMapperData($headers, $rows);
      
      // Add imported contacts in new group.
      $this->click( "css=#new-group div.crm-accordion-header" );
      $groupName = 'Individual Group '.substr(sha1(rand()), 0, 7);
      $this->type('newGroupName', $groupName);
      $this->type('newGroupDesc', 'Group For Individuals' );

      // Assign new tag to the imported contacts.
      $this->click( "css=#new-tag div.crm-accordion-header" );
      $tagName = 'indivi_'.substr(sha1(rand()), 0, 7);
      $this->type('newTagName', $tagName);
      $this->type('newTagDesc', 'Tag for Individuals' );
      
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
  
  function individualCSVData( ) {
      $headers = array( 'first_name'           => 'First Name',
                        'middle_name'          => 'Middle Name',
                        'last_name'            => 'Last Name',
                        'email'                => 'Email',
                        'phone'                => 'Phone',  
                        'address_1'            => 'Additional Address 1',
                        'address_2'            => 'Additional Address 2',
                        'city'                 => 'City',
                        'state'                => 'State',
                        'country'              => 'Country'
                        );
      
      $rows = 
          array( 
                array(  'first_name'           => substr(sha1(rand()), 0, 7),
                        'middle_name'          => substr(sha1(rand()), 0, 7) ,
                        'last_name'            => 'Anderson',
                        'email'                => substr(sha1(rand()), 0, 7).'@example.com',
                        'phone'                => '6949912154',  
                        'address_1'            => 'Add 1',
                        'address_2'            => 'Add 2',
                        'city'                 => 'Watson',
                        'state'                => 'NY',
                        'country'              => 'United States'
                        ),
                
                array(  'first_name'           => substr(sha1(rand()), 0, 7),
                        'middle_name'          => substr(sha1(rand()), 0, 7) ,
                        'last_name'            => 'Summerson',
                        'email'                => substr(sha1(rand()), 0, 7).'@example.com',
                        'phone'                => '6944412154',  
                        'address_1'            => 'Add 1',
                        'address_2'            => 'Add 2',
                        'city'                 => 'Watson',
                        'state'                => 'NY',
                        'country'              => 'United States'
                        )
                 );

      return array($headers, $rows);
  }
}