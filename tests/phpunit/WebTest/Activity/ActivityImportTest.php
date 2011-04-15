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

class WebTest_Activity_ActivityImportTest extends CiviSeleniumTestCase {
    
    protected function setUp( )
    {
        parent::setUp( );
    }
    
    function testActivityImport( )
    {
        $this->open( $this->sboxPath );
        
        $this->webtestLogin( );
        
        // Get sample import data.
        list( $headers, $rows ) = $this->activityCSVData( );
        
        $this->importCSVActivities( $headers, $rows );
    }
    
    function activityCSVData( ) {
        
        $firstName1 = substr( sha1( rand( ) ), 0, 7 );
        $email1     = 'mail_' . substr( sha1( rand( ) ), 0, 7 ) . '@example.com'; 
        $this->webtestAddContact( $firstName1, 'Anderson', $email1 );
        
        $firstName2 = substr( sha1( rand() ), 0, 7 );
        $email2     = 'mail_' . substr( sha1( rand( ) ), 0, 7 ) . '@example.com'; 
        $this->webtestAddContact( $firstName2, 'Anderson', $email2 );
            
        $headers = array( 'email'               => 'Email',
                          'activity_type_label' => 'Activity Type Label',
                          'subject'             => 'Subject',
                          'activity_date'       => 'Activity Date',
                          'activity_status_id'  => 'Activity Status Id',
                          'duration'            => 'Duration',
                          'location'            => 'Location'
                          );
        
        $rows = array( 
                      array( 'email'               => $email1, 
                             'activity_type_label' => 'Meeting',
                             'subject'             => 'Test Meeting',
                             'activity_date'       => '2009-10-01',
                             'activity_status_id'  => 'Completed',
                             'duration'            => '20',
                             'location'            => 'UK'
                             ),
                      
                      array( 'email'               => $email2,
                             'activity_type_label' => 'Phone Call',
                             'subject'             => 'Test Phone Call',
                             'activity_date'       => '2010-10-15',
                             'activity_status_id'  => 'Completed',
                             'duration'            => '20',
                             'location'            => 'USA'
                             )
                       );
        
        return array( $headers, $rows );
        
    }
    function importCSVActivities( $headers, $rows, $contactType = 'Individual', $mode = 'Skip' ) {
        
        // Go to contact import page.
        $this->open( $this->sboxPath . "civicrm/import/activity?reset=1" );
        $this->waitForPageToLoad( '30000' );
        
        // check for upload field.
        $this->waitForElementPresent( "uploadFile" );
        
        // Create csv file of sample data.
        $csvFile = $this->webtestCreateCSV( $headers, $rows );
        
        // Attach csv file.
        $this->webtestAttachFile( 'uploadFile', $csvFile );
        
        // First row is header.
        $this->click( 'skipColumnHeader' );
        
        // Submit form.
        $this->click( '_qf_UploadFile_upload' );
        $this->waitForPageToLoad( "30000" );
        
        if ( isset( $headers['email'] ) ) {
            $this->select( "mapper[0][0]", "value=email" );
        } 
        
        // Check mapping data.
        $this->checkActivityImportMapperData( $headers, $rows );
        
        // Create new mapping
        $this->click( 'saveMapping' );
        $mappingName = 'activityImport_'.substr( sha1( rand( ) ), 0, 7 );
        $this->type( 'saveMappingName', $mappingName );
        $this->type( 'saveMappingDesc', "Mapping for {$contactType}" );
        
        // Submit form.
        $this->click( '_qf_MapField_next' );
        $this->waitForPageToLoad( "30000" );
        
        // Check mapping data.
        $this->checkActivityImportMapperData( $headers, $rows );
        
        // Submit form.
        $this->click( '_qf_Preview_next' );
        
        sleep( 10 );
        
        // Visit summary page.
        $this->waitForElementPresent( "_qf_Summary_next" );
        
        // Check success message.
        $this->assertTrue( $this->isTextPresent( "Import has completed successfully. The information below summarizes the results." ) );
        
        // Check summary Details.
        $importedRecords = count( $rows );
        $checkSummary = array( 'Total Rows'       => $importedRecords,
                               'Records Imported' => $importedRecords,
                               );
        
        foreach( $checkSummary as $label => $value ) {
            $this->verifyText( "xpath=//table[@id='summary-counts']/tbody/tr/td[text()='{$label}']/following-sibling::td", preg_quote( $value ) );
        }
        
    }
    function checkActivityImportMapperData( $headers, $rows ) {
        
        $checkMapperHeaders = array( 1 => 'Column Headers',
                                     2 => 'Import Data (row 2)',
                                     3 => 'Import Data (row 3)',
                                     4 => 'Matching CiviCRM Field' );
        
        foreach ( $checkMapperHeaders as $rownum => $value ) {
            $this->verifyText( "xpath=//div[@id='map-field']//table[1]/tbody/tr[1]/th[{$rownum}]", preg_quote( $value ) );
        }
        
        $rownum = 2;
        foreach ( $headers as $field => $header ) {
            $this->verifyText( "xpath=//div[@id='map-field']//table[1]/tbody/tr[{$rownum}]/td[1]", preg_quote( $header ) );
            $colnum = 2;
            foreach( $rows as $row ) {
                $this->verifyText( "xpath=//div[@id='map-field']//table[1]/tbody/tr[{$rownum}]/td[{$colnum}]", preg_quote( $row[$field] ) );  
                $colnum++;   
            }
            $rownum++;
        }
    }
}