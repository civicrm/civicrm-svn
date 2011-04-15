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

class WebTest_Import_ActivityTest extends ImportCiviSeleniumTestCase {
    
    protected function setUp( )
    {
        parent::setUp( );
    }
    
    function testActivityImport( )
    {
        $this->open( $this->sboxPath );
        
        $this->webtestLogin( );
        
        // Get sample import data.
        list( $headers, $rows ) = $this->_activityCSVData( );
         $fieldMapper = array( 'mapper[0][0]' => 'email' );
        $this->importCSVComponent( 'Activity', $headers, $rows, null, null, $fieldMapper );
    }
    
    function _activityCSVData( ) {
        
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
}