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

class ImportCiviSeleniumTestCase extends CiviSeleniumTestCase {
    
    /*
     * Function to test csv import for each component.
     *
     * @params string $component   component name ( Event, Contribution, Membership, Activity etc)
     * @params array  $headers     csv data headers
     * @params array  $rows        csv data rows
     * @params string $contactType contact type
     * @params string $mode        import mode
     * @params array  $fieldMapper select mapper fields while import
     * @params array  $other       other parameters
     *                             useMappingName     : to reuse mapping 
     *                             dateFormat         : date format of data
     *                             checkMapperHeaders : to override default check mapper headers
     *                             saveMapping        : save current mapping?
     *                             saveMappingName    : to override mapping name
     *
     */
    function importCSVComponent( $component, $headers, $rows, $contactType = 'Individual', $mode = 'Skip', $fieldMapper = array( ), $other = array( ) ) {
        
        // Go to contact import page.
        $this->open( $this->sboxPath . $this->_getImportComponentUrl($component) );

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
        
        // Date format, default: yyyy-mm-dd OR yyyymmdd
        if ( isset($other['dateFormat']) ) {
            $dateFormatMapper = array( 'yyyy-mm-dd OR yyyymmdd'   => 'CIVICRM_QFID_1_14', // default
                                       'mm/dd/yy OR mm-dd-yy'     => 'CIVICRM_QFID_2_16',
                                       'mm/dd/yyyy OR mm-dd-yyyy' => 'CIVICRM_QFID_4_18',
                                       'Month dd, yyyy'           => 'CIVICRM_QFID_8_20',       
                                       'dd-mon-yy OR dd/mm/yy'    => 'CIVICRM_QFID_16_22',
                                       'dd/mm/yyyy'               => 'CIVICRM_QFID_32_24'
                                       );
            
            $this->click( $dateFormatMapper[$other['dateFormat']] );
        }

        // Use already created mapping
        if ( isset($other['useMappingName']) ) {
            $this->select('savedMapping', "label=" . $other['useMappingName'] );
        }
        
        // Submit form.
        $this->click('_qf_UploadFile_upload');
        $this->waitForPageToLoad("30000");
        
        // Select matching field for cvs data.
        if ( !empty($fieldMapper) ) {
            foreach( $fieldMapper as $field => $value ) {
                $this->select($field, "value={$value}" );
            }          
        }
        
        // Check mapping data.
        $this->_checkImportMapperData($headers, $rows, isset($other['checkMapperHeaders']) ? $other['checkMapperHeaders'] : array( ));
        
        // Save mapping
        if ( isset($other['saveMapping']) ) {
            $mappingName = isset($other['saveMappingName']) ? $other['saveMappingName'] : "{$component}Import_" . substr(sha1(rand()), 0, 7);

            $this->click('saveMapping');
            $this->type('saveMappingName', $mappingName);
            $this->type('saveMappingDesc', "Mapping for {$contactType}" );
        }
            
        // Submit form.
        $this->click('_qf_MapField_next');
        $this->waitForPageToLoad("30000");
        
        // Check mapping data.
        $this->_checkImportMapperData($headers, $rows, isset($other['checkMapperHeaders']) ? $other['checkMapperHeaders'] : array( ));
      
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
    
    /*
     * Function to test contact import.
     *
     * @params array  $headers     csv data headers
     * @params array  $rows        csv data rows
     * @params string $contactType contact type
     * @params string $mode        import mode
     * @params array  $fieldMapper select mapper fields while import
     * @params array  $other       other parameters
     *                             useMappingName     : to reuse mapping
     *                             dateFormat         : date format of data
     *                             checkMapperHeaders : to override default check mapper headers
     *                             saveMapping        : save current mapping?
     *                             saveMappingName    : to override mapping name
     *                             createGroup        : create new group?
     *                             createGroupName    : to override new Group name
     *                             createTag          : create new tag?
     *                             createTagName      : to override new Tag name
     *
     * @params string $type        import type (csv/sql)
     *                             @todo:currently only supports csv, need to work on sql import 
     */
    function importContacts( $headers, $rows, $contactType = 'Individual', $mode = 'Skip', $fieldmapper = array( ), $other = array( ), $type = 'csv' ) {
      
      // Go to contact import page.
      $this->open($this->sboxPath . "civicrm/import/contact?reset=1");
      
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

      // Use already created mapping
      if ( isset($other['useMappingName']) ) {
          $this->select('savedMapping', "label=" . $other['useMappingName'] );
      }

      // Submit form.
      $this->click('_qf_DataSource_upload');
      $this->waitForPageToLoad("30000");
      
      if ( isset($other['checkMapperHeaders']) ) {
          $checkMapperHeaders = $other['checkMapperHeaders'];
      } else {
          $checkMapperHeaders = array( 1 => 'Column Names',
                                       2 => 'Import Data (row 1)',
                                       3 => 'Import Data (row 2)',
                                       4 => 'Matching CiviCRM Field' );
      }

      // Check mapping data.
      $this->_checkImportMapperData($headers, $rows, $checkMapperHeaders, 'td');

      // Date format, default: yyyy-mm-dd OR yyyymmdd
      if ( isset($other['dateFormat']) ) {
          $dateFormatMapper = array( 'yyyy-mm-dd OR yyyymmdd'   => 'CIVICRM_QFID_1_14', // default
                                     'mm/dd/yy OR mm-dd-yy'     => 'CIVICRM_QFID_2_16',
                                     'mm/dd/yyyy OR mm-dd-yyyy' => 'CIVICRM_QFID_4_18',
                                     'Month dd, yyyy'           => 'CIVICRM_QFID_8_20',       
                                     'dd-mon-yy OR dd/mm/yy'    => 'CIVICRM_QFID_16_22',
                                     'dd/mm/yyyy'               => 'CIVICRM_QFID_32_24'
                                     );
          
          $this->click( $dateFormatMapper[$other['dateFormat']] );
      }

      // Save mapping
      if ( isset($other['saveMapping']) ) {
            $mappingName = isset($other['saveMappingName']) ? $other['saveMappingName'] : 'ContactImport_' . substr(sha1(rand()), 0, 7);
            $this->click('saveMapping');
            $this->type('saveMappingName', $mappingName);
            $this->type('saveMappingDesc', "Mapping for {$contactType}" );
      }

      // Submit form.
      $this->click('_qf_MapField_next');
      $this->waitForPageToLoad("30000");
      
      // Check mapping data.
      $this->_checkImportMapperData($headers, $rows, $checkMapperHeaders, 'td');
      
      // Add imported contacts in new group.
      $groupName = null;
      if ( isset($other['createGroup']) ) {
          $groupName = isset($other['createGroupName']) ? $other['createGroupName'] :'ContactImport_' . substr(sha1(rand()), 0, 7);

          $this->click( "css=#new-group div.crm-accordion-header" );
          $this->type('newGroupName', $groupName);
          $this->type('newGroupDesc', "Group For {$contactType}" );
      }
      // @TODO: select existing group
      
      // Assign new tag to the imported contacts.
      $tagName = null;
      if ( isset($other['createTag']) ) {
          $tagName = isset($other['createTagName']) ? $other['createTagName'] : "{$contactType}_".substr(sha1(rand()), 0, 7);

          $this->click( "css=#new-tag div.crm-accordion-header" );
          $this->type('newTagName', $tagName);
          $this->type('newTagDesc', "Tag for {$contactType}" );
      }
      // @TODO: select existing tag

      // Submit form.
      $this->click('_qf_Preview_next');
      sleep(2);
      
      // Check confirmation alert.
      $this->assertTrue( (bool)preg_match("/^Are you sure you want to Import now[\s\S]$/", $this->getConfirmation()) );
      $this->chooseOkOnNextConfirmation( );

      $this->waitForElementPresent("id-processing");
      sleep(10);

      // Visit summary page.
      $this->waitForElementPresent("_qf_Summary_next");

      // Check success message.
      $this->assertTrue($this->isTextPresent("Import has completed successfully. The information below summarizes the results."));

      // Check summary Details.
      $importedContacts = count($rows);
      $importedContactsCount = ( $importedContacts == 1 ) ? 'One contact' : "$importedContacts contacts";
      $taggedContactsCount   = ( $importedContacts == 1 ) ? 'One contact is' : "$importedContacts contacts are";
      $checkSummary = array( 'Total Rows'               => $importedContacts,
                             'Total Contacts'           => $importedContacts
                             );
      
      if ( $groupName ) {
          $checkSummary['Import to Groups'] = "{$groupName}: {$importedContactsCount} added to this new group."; 
      }

      if ( $tagName ) {
          $checkSummary['Tagged Imported Contacts'] = "{$tagName}: {$taggedContactsCount} tagged with this tag.";
      }

      foreach( $checkSummary as $label => $value ) {
          $this->verifyText("xpath=//table[@id='summary-counts']/tbody/tr/td[text()='{$label}']/following-sibling::td", preg_quote($value));
      }
        
    }
    
    /*
     * Helper function to get the import url of the component.
     *
     * @params string $component component name
     *
     * @return string import url 
     */
    function _getImportComponentUrl( $component ) {

        $importComponentUrl = array( 'Event'        => 'civicrm/event/import?reset=1',
                                     'Contribution' => 'civicrm/contribute/import?reset=1',
                                     'Membership'   => 'civicrm/member/import?reset=1');

        return $importComponentUrl[$component];
    }
    
    /*
     * Helper function to check import mapping fields.
     *
     * @params array  $headers            field headers
     * @params array  $rows               field rows
     * @params array  $checkMapperHeaders override default mapper headers
     */
    function _checkImportMapperData( $headers, $rows, $checkMapperHeaders = array(), $headerSelector = 'th' ) {
        
        if ( empty($checkMapperHeaders) ) { 
            $checkMapperHeaders = array( 1 => 'Column Headers',
                                         2 => 'Import Data (row 2)',
                                         3 => 'Import Data (row 3)',
                                         4 => 'Matching CiviCRM Field' );
        }
        
        foreach ($checkMapperHeaders as $rownum => $value ) {
            $this->verifyText("xpath=//div[@id='map-field']//table[1]/tbody/tr[1]/{$headerSelector}[{$rownum}]", preg_quote($value));
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
    
    /*
     * Helper function to get imported contact ids.
     *
     * @params array  $rows        fields rows
     * @params string $contactType contact type 
     *
     * @return array  $contactIds  imported contact ids
     */
    function _getImportedContactIds($rows, $contactType = 'Individual') {
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
    
}
