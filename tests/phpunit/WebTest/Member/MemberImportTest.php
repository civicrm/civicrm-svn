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

class WebTest_Member_MemberImportTest extends CiviSeleniumTestCase {
    
    protected function setUp()
    {
        parent::setUp();
    }
    
    function testMemberImportIndividual( )
    {
        $this->open( $this->sboxPath );

        $this->webtestLogin();
        // make sure default strict dedupe rules are in place
        $this->webtestStrictDedupeRuleDefault( "Individual" );
        
        // Get sample import data.
        list( $headers, $rows ) = $this->memberIndividualCSVData( );
        
        $this->importCSVMembers( $headers, $rows );
    }
    
    function testMemberImportHousehold()
    {
        $this->open( $this->sboxPath );
        
        $this->webtestLogin();
        
        // Get sample import data.
        list( $headers, $rows ) = $this->memberHouseholdCSVData( );
        
        $this->importCSVMembers( $headers, $rows, 'Household' );
    }
    
    function testMemberImportOrganization()
    {
        $this->open( $this->sboxPath );
        
        $this->webtestLogin();
        
        // Get sample import data.
        list( $headers, $rows ) = $this->memberOrganizationCSVData( );
        
        $this->importCSVMembers( $headers, $rows, 'Organization' );
    }
    
    function importCSVMembers( $headers, $rows, $contactType = 'Individual', $mode = 'Skip' ) 
    {
        // Go to contact import page.
        $this->open( $this->sboxPath . "civicrm/member/import?reset=1" );
        $this->waitForPageToLoad( '30000' );
        
        // check for upload field.
        $this->waitForElementPresent( 'uploadFile' );
        
        // Create csv file of sample data.
        $csvFile = $this->webtestCreateCSV( $headers, $rows );
        
        // Attach csv file.
        $this->webtestAttachFile( 'uploadFile', $csvFile );
        
        // First row is header.
        $this->click( 'skipColumnHeader' );
        
        // select mode, default is 'Skip'.
        if ( $mode == 'Update' ) {
            $this->click( "CIVICRM_QFID_4_4" );
        }

        // select contact type, default is 'Individual'.
        if ( $contactType == 'Organization' ) {
            $this->click( "CIVICRM_QFID_4_10" );
        } else if ( $contactType == 'Household' ) {
            $this->click( "CIVICRM_QFID_2_8" );
        }
        
        // Submit form.
        $this->click( '_qf_UploadFile_upload-bottom' );
        $this->waitForPageToLoad( "30000" );
        $this->waitForElementPresent( '_qf_MapField_next-bottom' );
        $this->waitForElementPresent( "mapper[0][0]" );

        $rowCount = 0;
        foreach ( $headers as $field => $header ) {
            if ( $rowCount == 0 ) {
                if ( isset( $headers['email'] ) ) {
                    $this->select( "mapper[{$rowCount}][0]", "value=email" );
                } else if ( isset( $headers['household_name'] ) ) {
                    $this->select( "mapper[{$rowCount}][0]", "value=household_name" );
                } else if ( isset( $headers['organization_name'] ) ) {
                    $this->select( "mapper[{$rowCount}][0]", "value=organization_name" );
                } 
            } else if ( $rowCount == 1 ) {
                if ( isset( $headers['membership_type_id'] ) ) {
                    $this->select( "mapper[{$rowCount}][0]", "value=membership_type_id" );
                } 
            } else {
                if ( isset( $headers['membership_start_date'] ) ) {
                    $this->select( "mapper[{$rowCount}][0]", "value=membership_start_date" );
                }
            }
            $rowCount++;
        }

        // Check mapping data.
        $this->checkMemberImportMapperData( $headers, $rows );
        
        // Create new mapping
        $this->click( 'saveMapping' );
        $mappingName = 'memberImport_' . substr( sha1(rand()), 0, 7 );
        $this->type( 'saveMappingName', $mappingName );
        $this->type( 'saveMappingDesc', "Mapping for {$contactType}" );
        
        // Submit form.
        $this->click( '_qf_MapField_next' );
        $this->waitForPageToLoad( "30000" );
        
        // Check mapping data.
        $this->checkMemberImportMapperData( $headers, $rows );
        
        // Submit form.
        $this->click( '_qf_Preview_next' );
        
        sleep(10);
        
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
            $this->verifyText( "xpath=//table[@id='summary-counts']/tbody/tr/td[text()='{$label}']/following-sibling::td",
                               preg_quote( $value ) );
        }
    }
    
    function checkMemberImportMapperData( $headers, $rows ) 
    {
        $checkMapperHeaders = array( 1 => 'Column Headers',
                                     2 => 'Import Data (row 2)',
                                     3 => 'Import Data (row 3)',
                                     4 => 'Matching CiviCRM Field' );
        
        foreach ( $checkMapperHeaders as $rownum => $value ) {
            $this->verifyText( "xpath=//div[@id='map-field']//table[1]/tbody/tr[1]/th[{$rownum}]", 
                               preg_quote( $value ) );
        }
        
        $rownum = 2;
        foreach ( $headers as $field => $header ) {
            $this->verifyText( "xpath=//div[@id='map-field']//table[1]/tbody/tr[{$rownum}]/td[1]", 
                               preg_quote( $header ) );
            $colnum = 2;
            foreach( $rows as $row ) {
                $this->verifyText( "xpath=//div[@id='map-field']//table[1]/tbody/tr[{$rownum}]/td[{$colnum}]", 
                                   preg_quote( $row[$field] ) );  
                $colnum++;   
            }
            $rownum++;
        }
    }
    
    function memberIndividualCSVData( ) 
    {
        $memTypeParams = $this->addMembershipType( );
                
        $firstName1 = substr(sha1(rand()), 0, 7);
        $email1     = 'mail_' . substr(sha1(rand()), 0, 7) . '@example.com'; 
        $this->webtestAddContact( $firstName1, 'Anderson', $email1 );
        $startDate1 = date( 'Y-m-d' );
        
        $firstName2 = substr(sha1(rand()), 0, 7);
        $email2     = 'mail_' . substr(sha1(rand()), 0, 7) . '@example.com'; 
        $this->webtestAddContact( $firstName2, 'Anderson', $email2 );
        $year  = date( 'Y' ) - 1;
        $startDate2 = date( 'Y-m-d', mktime( 0, 0, 0, 9, 10, $year ) );
        
        $headers = array( 'email'                 => 'Email',
                          'membership_type_id'    => 'Membership Type',
                          'membership_start_date' => 'Membership Start Date',
                          );
        
        $rows = 
            array( 
                  array( 'email'                 => $email1, 
                         'membership_type_id'    => $memTypeParams['membership_type'],
                         'membership_start_date' => $startDate1,
                         ),
                  
                  array( 'email'                 => $email2,
                         'membership_type_id'    => $memTypeParams['membership_type'],
                         'membership_start_date' => $startDate2,
                         )
                   );
        
        return array( $headers, $rows );
    }

    function memberHouseholdCSVData( ) 
    {
        $memTypeParams = $this->addMembershipType( );
        
        $household1 = substr(sha1(rand()), 0, 7) . ' home';
        $this->webtestAddHousehold( $household1, true );
        $startDate1 = date( 'Y-m-d' );
        
        $household2 = substr(sha1(rand()), 0, 7) . ' home';
        $this->webtestAddHousehold( $household2, true );
        $year  = date( 'Y' ) - 1;
        $startDate2 = date( 'Y-m-d', mktime( 0, 0, 0, 12, 31, $year ) );
        
        $headers = array( 'household_name'        => 'Household Name',
                          'membership_type_id'    => 'Membership Type',
                          'membership_start_date' => 'Membership Start Date',
                          );
        
        $rows = 
            array( 
                  array( 'household_name'        => $household1, 
                         'membership_type_id'    => $memTypeParams['membership_type'],
                         'membership_start_date' => $startDate1,
                         ),
                  
                  array( 'household_name'        => $household2,
                         'membership_type_id'    => $memTypeParams['membership_type'],
                         'membership_start_date' => $startDate2,
                         )
                   );
        
        return array( $headers, $rows );
    }

    function memberOrganizationCSVData( ) 
    {
        $memTypeParams = $this->addMembershipType( );
        
        $organization1 = substr(sha1(rand()), 0, 7) . ' org';
        $this->webtestAddOrganization( $organization1, true );
        $startDate1 = date( 'Y-m-d' );
        
        $organization2 = substr(sha1(rand()), 0, 7) . ' org';
        $this->webtestAddOrganization( $organization2, true );
        $year  = date( 'Y' ) - 1;
        $startDate2 = date( 'Y-m-d', mktime( 0, 0, 0, 12, 31, $year ) );
        
        $headers = array( 'organization_name'     => 'Household Name',
                          'membership_type_id'    => 'Membership Type',
                          'membership_start_date' => 'Membership Start Date',
                          );
        
        $rows = 
            array( 
                  array( 'organization_name'     => $organization1, 
                         'membership_type_id'    => $memTypeParams['membership_type'],
                         'membership_start_date' => $startDate1,
                         ),
                  
                  array( 'organization_name'     => $organization1,
                         'membership_type_id'    => $memTypeParams['membership_type'],
                         'membership_start_date' => $startDate2,
                         )
                   );
        
        return array( $headers, $rows );
    }

    function addMembershipType( )
    {
        $membershipTitle = substr(sha1(rand()), 0, 7);
        $membershipOrg   = $membershipTitle . ' memorg';
        $this->webtestAddOrganization( $membershipOrg, true );

        $title = "Membership Type " . substr(sha1(rand()), 0, 7);
        $memTypeParams = array( 'membership_type'   => $title,
                                'member_org'        => $membershipOrg,
                                'contribution_type' => 2,
                                'relationship_type' => '4_b_a' 
                                );
        
        $this->open( $this->sboxPath . "civicrm/admin/member/membershipType?reset=1&action=browse" );
        $this->waitForPageToLoad("30000");

        $this->click( "link=Add Membership Type" );
        $this->waitForElementPresent( '_qf_MembershipType_cancel-bottom' );
        
        $this->type( 'name', $memTypeParams['membership_type'] );
        $this->type( 'member_org', $membershipTitle );
        $this->click( '_qf_MembershipType_refresh' );
        $this->waitForElementPresent( "xpath=//div[@id='membership_type_form']/fieldset/table[2]/tbody/tr[2]/td[2]" );
        
        $this->type( 'minimum_fee', '100' );
        $this->select( 'contribution_type_id', "value={$memTypeParams['contribution_type']}" );
        
        $this->type( 'duration_interval', 1 );
        $this->select( 'duration_unit', "label=year" );
        
        $this->select( 'period_type', "label=rolling" );
        $this->click( 'relationship_type_id', "value={$memTypeParams['relationship_type']}" );
        
        $this->click( '_qf_MembershipType_upload-bottom' );
        $this->waitForElementPresent( 'link=Add Membership Type' );
        $this->assertTrue( $this->isTextPresent( "The membership type '$title' has been saved." ) );

        return $memTypeParams;
    }
}
    