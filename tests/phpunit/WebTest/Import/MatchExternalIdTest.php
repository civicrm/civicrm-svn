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

require_once 'WebTest/Import/ImportCiviSeleniumTestCase.php';

class WebTest_Import_MatchExternalIdTest extends ImportCiviSeleniumTestCase {
    
    protected function setUp()
    {
        parent::setUp();
    }
    
    function testContributionImport()
    {
        $this->open( $this->sboxPath );
        
        $this->webtestLogin();
        
        // Get sample import data.
        list( $headers, $rows ) = $this->_contributionIndividualCSVData( );
        
        // Create and import csv from provided data and check imported data.
        $fieldMapper = array( 'mapper[0][0]' => 'external_identifier' );
        $this->importCSVComponent( 'Contribution', $headers, $rows, 'Individual', 'Insert new contributions', $fieldMapper );
    }
    
    function _contributionIndividualCSVData( ) 
    {
        $firstName1 = substr( sha1( rand( ) ), 0, 7 );
        $lastName1 = substr( sha1( rand( ) ), 0, 7 );
        $externalId1 = substr( sha1( rand( ) ), 0, 4 );
       
        $this->_addContact( $firstName1, $lastName1, $externalId1 );
        
        $firstName2 = substr( sha1( rand( ) ), 0, 7 );
        $lastName2 = substr( sha1( rand( ) ), 0, 7 );
        $externalId2 = substr( sha1( rand( ) ), 0, 4 );
        
        $this->_addContact( $firstName2, $lastName2, $externalId2 );
            
        $headers = array( 'external_identifier'    => 'External Identifier',
                          'fee_amount'             => 'Fee Amount',
                          'contribution_type'      => 'Contribution Type',
                          'contribution_status_id' => 'Contribution Status',
                          'total_amount'           => 'Total Amount'
                          );
        
        $rows = array( 
                      array( 'external_identifier'    => $externalId1, 
                             'fee_amount'             => '200',
                             'contribution_type'      => 'Donation',
                             'contribution_status_id' => 'Completed',
                             'total_amount'           => '200'
                             ),
                      
                      array( 'external_identifier'    => $externalId2,
                             'fee_amount'             => '400',
                             'contribution_type'      => 'Donation',
                             'contribution_status_id' => 'Completed',
                             'total_amount'           => '400'
                             )
                       );
         $fieldMapper = array( 'mapper[0][0]' => 'external_identifier' );
         return array( $headers, $rows, $fieldMapper );
    }
    
    function _addContact( $firstName, $lastName, $externalId ) 
    {
         $this->open($this->sboxPath . "civicrm/contact/add&reset=1&ct=Individual");
         
         //fill in first name
         $this->type( "first_name", $firstName );
         
         //fill in last name
         $this->type( "last_name", $lastName );
         
         //fill in external identifier
         $this->type( "external_identifier", $externalId );
         
         // Clicking save.
         $this->click("_qf_Contact_upload_view");
         $this->waitForPageToLoad("30000");
         $this->assertTrue($this->isTextPresent( "Your Individual contact record has been saved." ) );
         
         return $externalId;
    }
}