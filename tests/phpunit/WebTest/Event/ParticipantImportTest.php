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

class WebTest_Event_ParticipantImportTest extends CiviSeleniumTestCase {
    
    protected function setUp()
    {
        parent::setUp();
    }
    
    function testParticipantImportIndividual()
    {
        $this->open( $this->sboxPath );

        $this->webtestLogin();
        
        // Get sample import data.
        list($headers, $rows) = $this->participantIndividualCSVData( );
        
        $this->importCSVParticipants( $headers, $rows );
    }
   
    function testParticipantImportOrganizatio()
    {
        $this->open( $this->sboxPath );

        $this->webtestLogin();
        
        // Get sample import data.
        list($headers, $rows) = $this->participantOrganizationCSVData( );
        
        $this->importCSVParticipants( $headers, $rows, 'Organization' );
    }

    function testParticipantImportHousehold()
    {
        $this->open( $this->sboxPath );

        $this->webtestLogin();
        
        // Get sample import data.
        list($headers, $rows) = $this->participantHouseholdCSVData( );
        
        $this->importCSVParticipants( $headers, $rows, 'Household' );
    }

    function importCSVParticipants( $headers, $rows, $contactType = 'Individual', $mode = 'Skip' ) {
      
      // Go to contact import page.
      $this->open($this->sboxPath . "civicrm/event/import?reset=1");
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
      } else if ( $mode == 'No Duplicate Checking' ) {
          $this->click("CIVICRM_QFID_16_6");  
      }

      // select contact type, default is 'Individual'.
      if ( $contactType == 'Organization' ) {
          $this->click("CIVICRM_QFID_4_12");
      } else if ( $contactType == 'Household' ) {
          $this->click("CIVICRM_QFID_2_10");
      }

      // Submit form.
      $this->click('_qf_UploadFile_upload');
      $this->waitForPageToLoad("30000");
      
      if ( isset($headers['email']) ) {
          $this->select("mapper[0][0]", "value=email" );
      } else if ( isset($headers['household']) ) {
          $this->select("mapper[0][0]", "value=household_name" );
      } else if ( isset($headers['organization']) ) {
          $this->select("mapper[0][0]", "value=organization_name" );
      }
      
      // Check mapping data.
      $this->checkParticipantImportMapperData($headers, $rows);
      
      // Create new mapping
      $this->click('saveMapping');
      $mappingName = 'participantImport_'.substr(sha1(rand()), 0, 7);
      $this->type('saveMappingName', $mappingName);
      $this->type('saveMappingDesc', "Mapping for {$contactType}" );

      // Submit form.
      $this->click('_qf_MapField_next');
      $this->waitForPageToLoad("30000");

      // Check mapping data.
      $this->checkParticipantImportMapperData($headers, $rows);
      
      // Submit form.
      $this->click('_qf_Preview_next');

      sleep(10);

      // Visit summary page.
      $this->waitForElementPresent("_qf_Summary_next");
      
      // Check success message.
      $this->assertTrue($this->isTextPresent("Import has completed successfully. The information below summarizes the results."));
      
      // Check summary Details.
      $importedRecords = count($rows);
      $checkSummary = array( 'Total Rows'       => $importedRecords,
                             'Records Imported' => $importedRecords,
                             );
      
      foreach( $checkSummary as $label => $value ) {
          $this->verifyText("xpath=//table[@id='summary-counts']/tbody/tr/td[text()='{$label}']/following-sibling::td", preg_quote($value));
      }
      
    }
    
    function checkParticipantImportMapperData( $headers, $rows ) {
        
        $checkMapperHeaders = array( 1 => 'Column Names',
                                     2 => 'Import Data (row 1)',
                                     3 => 'Import Data (row 2)',
                                     4 => 'Matching CiviCRM Field' );
        
        foreach ($checkMapperHeaders as $rownum => $value ) {
            $this->verifyText("xpath=//div[@id='map-field']//table[1]/tbody/tr[1]/th[{$rownum}]", preg_quote($value));
        }
        
        $rownum = 2;
        foreach ( $headers as $field => $header ) {
            $this->verifyText("xpath=//div[@id='map-field']//table[1]/tbody/tr[{$rownum}]/td[1]", preg_quote($header));
            $colnum = 2;
            foreach( $rows as $row ) {
                $this->verifyText("xpath=//div[@id='map-field']//table[1]/tbody/tr[{$rownum}]/td[{$colnum}]", preg_quote($row[$field]));  
                $colnum++;   
            }
            $rownum++;
        }
        
    }
    
    function participantIndividualCSVData( ) {
        $eventInfo = $this->addNewEvent( );
        
        $firstName1 = substr(sha1(rand()), 0, 7);
        $email1     = 'mail_' . substr(sha1(rand()), 0, 7) . '@example.com'; 
        $this->webtestAddContact( $firstName1, 'Anderson', $email1 );
        
        $firstName2 = substr(sha1(rand()), 0, 7);
        $email2     = 'mail_' . substr(sha1(rand()), 0, 7) . '@example.com'; 
        $this->webtestAddContact( $firstName2, 'Anderson', $email2 );
            
        $headers = array( 'email'         => 'Email',
                          'event_id'      => 'Event Id',
                          'fee_level'     => 'Fee Level',
                          'role'          => 'Participant Role',
                          'status'        => 'Participant Status',
                          'register_date' => 'Register date'
                          );
      
      $rows = 
          array( 
                array( 'email'         => $email1, 
                       'event_id'      => $eventInfo['event_id'],
                       'fee_level'     => 'Member',
                       'role'          => 1,
                       'status'        => 1,
                       'register_date' => '2011-03-30'
                        ),
                
                array( 'email'         => $email2,
                       'event_id'      => $eventInfo['event_id'],
                       'fee_level'     => 'Non-Member',
                       'role'          => 1,
                       'status'        => 1,
                       'register_date' => '2011-03-30' 
                        )
                 );

      return array($headers, $rows);
        
    }

    function participantHouseholdCSVData( ) {
        $eventInfo = $this->addNewEvent( );
        
        $household1 = substr(sha1(rand()), 0, 7) . ' home';
        $this->webtestAddHousehold( $household1, true );
        
        $household2 = substr(sha1(rand()), 0, 7) . ' home';
        $this->webtestAddHousehold( $household2, true );

        $headers = array( 'household'     => 'Household Name',
                          'event_id'      => 'Event Id',
                          'fee_level'     => 'Fee Level',
                          'role'          => 'Participant Role',
                          'status'        => 'Participant Status',
                          'register_date' => 'Register date'
                          );
      
      $rows = 
          array( 
                array( 'household'     => $household1, 
                       'event_id'      => $eventInfo['event_id'],
                       'fee_level'     => 'Member',
                       'role'          => 1,
                       'status'        => 1,
                       'register_date' => '2011-03-30'
                        ),
                
                array( 'household'     => $household2,
                       'event_id'      => $eventInfo['event_id'],
                       'fee_level'     => 'Non-Member',
                       'role'          => 1,
                       'status'        => 1,
                       'register_date' => '2011-03-30' 
                        )
                 );

      return array($headers, $rows);
        
    }

    function participantOrganizationCSVData( ) {
        $eventInfo = $this->addNewEvent( );
        
        $organization1 = substr(sha1(rand()), 0, 7) . ' org';
        $this->webtestAddOrganization( $organization1, true );
        
        $organization2 = substr(sha1(rand()), 0, 7) . ' org';
        $this->webtestAddOrganization( $organization2, true );

        $headers = array( 'organization'  => 'Organization Name',
                          'event_id'      => 'Event Id',
                          'fee_level'     => 'Fee Level',
                          'role'          => 'Participant Role',
                          'status'        => 'Participant Status',
                          'register_date' => 'Register date'
                          );
      
      $rows = 
          array( 
                array( 'organization'  => $organization1, 
                       'event_id'      => $eventInfo['event_id'],
                       'fee_level'     => 'Member',
                       'role'          => 1,
                       'status'        => 1,
                       'register_date' => '2011-03-30'
                        ),
                
                array( 'organization'  => $organization2,
                       'event_id'      => $eventInfo['event_id'],
                       'fee_level'     => 'Non-Member',
                       'role'          => 1,
                       'status'        => 1,
                       'register_date' => '2011-03-30' 
                        )
                 );

      return array($headers, $rows);
        
    }
    
    function addNewEvent( $forceCreate = false ) {
        
        // We need a payment processor
        $processorName = "Webtest Dummy" . substr(sha1(rand()), 0, 7);
        $this->webtestAddPaymentProcessor($processorName);
        
        // create an event
        $eventTitle = 'My Conference - '.substr(sha1(rand()), 0, 7);
        $params     = array( 'title'              => $eventTitle,
                             'template_id'        => 6,
                             'event_type_id'      => 4,
                             'payment_processor'  => $processorName,
                             'fee_level'          => array( 'Member'     => "250.00",
                                                            'Non-Member' => "325.00" )
                             );

        $this->open($this->sboxPath . "civicrm/event/add&reset=1&action=add");
        
        $this->waitForElementPresent("_qf_EventInfo_upload-bottom");
        
        // Let's start filling the form with values.
        $this->select("event_type_id", "value={$params['event_type_id']}");
        
        // Attendee role s/b selected now.
        $this->select("default_role_id", "value=1");
        
        // Enter Event Title, Summary and Description
        $this->type("title", $params['title'] );
        $this->type("summary", "This is a great conference. Sign up now!");
        $this->fillRichTextField( "description", "Here is a description for this event.", 'CKEditor' );
        
        // Choose Start and End dates.
        // Using helper webtestFillDate function.
        $this->webtestFillDateTime("start_date", "+1 week");
        $this->webtestFillDateTime("end_date", "+1 week 1 day 8 hours ");
        
        $this->type("max_participants", "50");
        $this->click("is_map");
        $this->click("_qf_EventInfo_upload-bottom");      
        
        // Wait for Location tab form to load
        $this->waitForPageToLoad("30000");
        
        // Go to Fees tab
        $this->click("link=Fees");
        $this->waitForElementPresent("_qf_Fee_upload-bottom");
        $this->click("CIVICRM_QFID_1_2");
        $this->select("payment_processor_id", "label=" . $params['payment_processor']);
        $this->select("contribution_type_id", "value=4");

        $counter = 1;
        foreach ( $params['fee_level'] as $label => $amount ) { 
            $this->type("label_{$counter}", $label );
            $this->type("value_{$counter}", $amount ); 
            $counter++;
        }      
        
        $this->click("_qf_Fee_upload-bottom");
        $this->waitForPageToLoad("30000");
        
        // Go to Online Registration tab
        $this->click("link=Online Registration");
        $this->waitForElementPresent("_qf_Registration_upload-bottom");
        
        $this->check("is_online_registration");
        $this->assertChecked("is_online_registration");
        
        $this->fillRichTextField("intro_text", "Fill in all the fields below and click Continue." );
        
        // enable confirmation email
        $this->click("CIVICRM_QFID_1_2");
        $this->type("confirm_from_name", "Jane Doe");
        $this->type("confirm_from_email", "jane.doe@example.org");
        
        $this->click("_qf_Registration_upload-bottom");
        $this->waitForPageToLoad("30000");
        $this->waitForTextPresent("'Registration' information has been saved.");
        
        // verify event input on info page
        // start at Manage Events listing
        $this->open($this->sboxPath . "civicrm/event/manage&reset=1");
        $this->click("link=". $params['title']);
        
        $this->waitForPageToLoad('30000');

        $matches = array();
        preg_match('/id=([0-9]+)/', $this->getLocation(), $matches);
        $params['event_id']  = $matches[1];
        
        return $params;
    }

}