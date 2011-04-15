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

class WebTest_Contribute_ContributionImportTest extends CiviSeleniumTestCase {
    
    protected function setUp()
    {
        parent::setUp();
    }
    
    function testContributionImportIndividual()
    {
        $this->open( $this->sboxPath );
        
        $this->webtestLogin();
        
        // Get sample import data.
        list($headers, $rows) = $this->contributionIndividualCSVData( );
        
        $this->importCSVContributions( $headers, $rows );
    }
    
    function testContributionImportOrganization()
    {
        $this->open( $this->sboxPath );
        
        $this->webtestLogin();
        
        // Get sample import data.
        list($headers, $rows) = $this->contributionOrganizationCSVData( );
        
        $this->importCSVContributions( $headers, $rows, 'Organization' );
    }
    
    function testContributionImportHousehold()
    {
        $this->open( $this->sboxPath );
        
        $this->webtestLogin();
        
        // Get sample import data.
        list($headers, $rows) = $this->contributionHouseholdCSVData( );
        
        $this->importCSVContributions( $headers, $rows, 'Household' );
    }
    
    function importCSVContributions( $headers, $rows, $contactType = 'Individual', $mode = 'Insert new contributions' ) 
    {
        // Go to contact import page.
        $this->open($this->sboxPath . "civicrm/contribute/import?reset=1");
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
        if ( $mode == 'Insert new contributions' ) {
            $this->click("CIVICRM_QFID_1_2");
        } else if ( $mode == 'Update existing contributions' ) {
            $this->click("CIVICRM_QFID_4_4");  
        }
        
        // select contact type, default is 'Individual'.
        if ( $contactType == 'Organization' ) {
            $this->click("CIVICRM_QFID_4_10");
        } else if ( $contactType == 'Household' ) {
            $this->click("CIVICRM_QFID_2_8");
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
        $this->checkContributionImportMapperData($headers, $rows);
        
        // Create new mapping
        $this->click('saveMapping');
        $mappingName = 'contributionImport_'.substr(sha1(rand()), 0, 7);
        $this->type('saveMappingName', $mappingName);
        $this->type('saveMappingDesc', "Mapping for {$contactType}" );
        
        // Submit form.
        $this->click('_qf_MapField_next');
        $this->waitForPageToLoad("30000");
        
        // Check mapping data.
        $this->checkContributionImportMapperData($headers, $rows);
       
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
    
    function checkContributionImportMapperData( $headers, $rows ) 
    {
        $checkMapperHeaders = array( 1 => 'Column Headers',
                                     2 => 'Import Data (row 2)',
                                     3 => 'Import Data (row 3)',
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
    
    function contributionIndividualCSVData( ) 
    {
        $firstName1 = substr(sha1(rand()), 0, 7);
        $email1     = 'mail_' . substr(sha1(rand()), 0, 7) . '@example.com'; 
        $this->webtestAddContact( $firstName1, 'Anderson', $email1 );
        
        $firstName2 = substr(sha1(rand()), 0, 7);
        $email2     = 'mail_' . substr(sha1(rand()), 0, 7) . '@example.com'; 
        $this->webtestAddContact( $firstName2, 'Anderson', $email2 );
            
        $headers = array( 'email'                  => 'Email',
                          'fee_amount'             => 'Fee Amount',
                          'contribution_type'      => 'Contribution Type',
                          'contribution_status_id' => 'Contribution Status',
                          'total_amount'           => 'Total Amount'
                          );
        
        $rows = 
            array( 
                  array( 'email'                  => $email1, 
                         'fee_amount'             => '200',
                         'contribution_type'      => 'Donation',
                         'contribution_status_id' => 'Completed',
                         'total_amount'           => '200'
                         ),
                  
                  array( 'email'                  => $email2,
                         'fee_amount'             => '400',
                         'contribution_type'      => 'Donation',
                         'contribution_status_id' => 'Completed',
                         'total_amount'           => '400'
                         )
                 );
        
        return array($headers, $rows);
    }
    
    function contributionHouseholdCSVData( ) 
    {
        $household1 = substr(sha1(rand()), 0, 7) . ' home';
        $this->webtestAddHousehold( $household1, true );
        
        $household2 = substr(sha1(rand()), 0, 7) . ' home';
        $this->webtestAddHousehold( $household2, true );
        
        $headers = array( 'household'     => 'Household Name',
                          'fee_amount'             => 'Fee Amount',
                          'contribution_type'      => 'Contribution Type',
                          'contribution_status_id' => 'Contribution Status',
                          'total_amount'           => 'Total Amount'
                          );
        
        $rows = 
            array( 
                  array( 'household'     => $household1, 
                         'fee_amount'             => '200',
                         'contribution_type'      => 'Donation',
                         'contribution_status_id' => 'Completed',
                         'total_amount'           => '200'
                         ),
                  
                  array( 'household'     => $household2,
                         'fee_amount'             => '400',
                         'contribution_type'      => 'Donation',
                         'contribution_status_id' => 'Completed',
                         'total_amount'           => '400'
                         )
                   );
        
        return array($headers, $rows);
        
    } 
    
    function contributionOrganizationCSVData( ) 
    {
        $organization1 = substr(sha1(rand()), 0, 7) . ' org';
        $this->webtestAddOrganization( $organization1, true );
         
        $organization2 = substr(sha1(rand()), 0, 7) . ' org';
        $this->webtestAddOrganization( $organization2, true );
        
        $headers = array( 'organization'  => 'Organization Name',
                          'fee_amount'             => 'Fee Amount',
                          'contribution_type'      => 'Contribution Type',
                          'contribution_status_id' => 'Contribution Status',
                          'total_amount'           => 'Total Amount'
                          );
        
        $rows = 
            array( 
                  array( 'organization'  => $organization1, 
                         'fee_amount'             => '200',
                         'contribution_type'      => 'Donation',
                         'contribution_status_id' => 'Completed',
                         'total_amount'           => '200'
                         ),
                  
                  array( 'organization'  => $organization2,
                         'fee_amount'             => '400',
                         'contribution_type'      => 'Donation',
                         'contribution_status_id' => 'Completed',
                         'total_amount'           => '400' 
                         )
                   );
        
        return array($headers, $rows);   
    }
}