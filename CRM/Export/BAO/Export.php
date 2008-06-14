<?php

/*
 +--------------------------------------------------------------------+
 | CiviCRM version 2.1                                                |
 +--------------------------------------------------------------------+
 | Copyright CiviCRM LLC (c) 2004-2008                                |
 +--------------------------------------------------------------------+
 | This file is a part of CiviCRM.                                    |
 |                                                                    |
 | CiviCRM is free software; you can copy, modify, and distribute it  |
 | under the terms of the GNU Affero General Public License           |
 | Version 3, 19 November 2007.                                       |
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

/**
 *
 * @package CRM
 * @copyright CiviCRM LLC (c) 2004-2007
 * $Id$
 *
 */


/**
 * This class contains the funtions for Component export
 *
 */
class CRM_Export_BAO_Export
{
    /**
     * Function to get the list the export fields
     *
     * @param int    $selectAll user preference while export
     * @param array  $ids  contact ids
     * @param array  $params associated array of fields
     * @param string $order order by clause
     * @param array  $associated array of fields
     * @param array  $moreReturnProperties additional return fields
     * @param int    $exportMode export mode
     * @param string $componentClause component clause
     *
     * @static
     * @access public
     */
    static function exportComponents( $selectAll, $ids, $params, $order = null, 
                                      $fields = null, $moreReturnProperties = null, 
                                      $exportMode = CRM_Export_Form_Select::CONTACT_EXPORT,
                                      $componentClause = null ) 
    {
        $headerRows       = array();
        $primary          = false;
        $returnProperties = array( );
        $origFields       = $fields;
        
        //used to check if user map current employer field to be exported.
        $currentEmployer  = false;
        //used to check if user wants only current employer field to be exported.
        //in that case contact_id should be unset from csv row.
        $unsetContactID   = false;
        
        if ( $fields ) {
            //construct return properties 
            $locationTypes =& CRM_Core_PseudoConstant::locationType();
            
            foreach ( $fields as $key => $value) {
                $fieldName   = CRM_Utils_Array::value( 1, $value );
                
                if ( ! $fieldName ) {
                    continue;
                } else if ( $fieldName == 'current_employer' ) {
                    //export current employer field.
                    $currentEmployer = true;
                    continue;
                }
                
                $contactType = CRM_Utils_Array::value( 0, $value );
                $locTypeId   = CRM_Utils_Array::value( 2, $value );
                $phoneTypeId = CRM_Utils_Array::value( 3, $value );
                
                if ( is_numeric($locTypeId) ) {
                    if ($phoneTypeId) {
                        $returnProperties['location'][$locationTypes[$locTypeId]]['phone-' .$phoneTypeId] = 1;
                    } else {
                        $returnProperties['location'][$locationTypes[$locTypeId]][$fieldName] = 1;
                    }
                } else {
                    //hack to fix component fields
                    if ( $fieldName == 'event_id' ) {
                        $returnProperties['event_title'] = 1;
                    } else {
                        $returnProperties[$fieldName] = 1;
                    }
                }
            }
            
            // hack to add default returnproperty based on export mode
            if ( $exportMode == CRM_Export_Form_Select::CONTRIBUTE_EXPORT ) {
                $returnProperties['contribution_id'] = 1;
            } else if ( $exportMode == CRM_Export_Form_Select::EVENT_EXPORT ) {
                $returnProperties['participant_id'] = 1;
            } else if ( $exportMode == CRM_Export_Form_Select::MEMBER_EXPORT ) {
                $returnProperties['membership_id'] = 1;
            }

            //check if user map current employer field,
            //and did not map Internal Contact ID.
            $returnContactID = CRM_Utils_Array::value( 'id' , $returnProperties );
            if ( $currentEmployer && empty( $returnContactID ) ) {
                $unsetContactID = true;
            }
        } else {
            $primary = true;
            $fields = CRM_Contact_BAO_Contact::exportableFields( 'All', true, true );
            foreach ($fields as $key => $var) { 
                if ( $key &&
                     ( substr($key,0, 6) !=  'custom' ) ) { //for CRM=952
                    $returnProperties[$key] = 1;
                }
            }
            
            $paymentFields = false;
            $queryMode = CRM_Contact_BAO_Query::MODE_CONTACTS;

            switch ( $exportMode )  {
            case CRM_Export_Form_Select::CONTRIBUTE_EXPORT :
                $queryMode = CRM_Contact_BAO_Query::MODE_CONTRIBUTE;
                break;
            case CRM_Export_Form_Select::EVENT_EXPORT :
                $queryMode = CRM_Contact_BAO_Query::MODE_EVENT;
                $paymentFields  = true;
                $paymentTableId = "participant_id";
                break;
            case CRM_Export_Form_Select::MEMBER_EXPORT :
                $queryMode = CRM_Contact_BAO_Query::MODE_MEMBER;
                $paymentFields  = true;
                $paymentTableId = "membership_id";
                break;
            }

            if ( $queryMode != CRM_Contact_BAO_Query::MODE_CONTACTS ) {
                $componentReturnProperties =& CRM_Contact_BAO_Query::defaultReturnProperties( $queryMode );
                $returnProperties          = array_merge( $returnProperties, $componentReturnProperties );
                
                // unset groups, tags, notes for components
                foreach ( array( 'groups', 'tags', 'notes' ) as $value ) {
                    unset( $returnProperties[$value] );
                }
            }
        }

        if ( $primary ) {
            $returnProperties['location_type'] = 1;
            $returnProperties['im_provider'  ] = 1;
            $returnProperties['phone_type'   ] = 1;
        }

        if ( $moreReturnProperties ) {
            $returnProperties = array_merge( $returnProperties, $moreReturnProperties );
        }
        
        $query =& new CRM_Contact_BAO_Query( 0, $returnProperties, null, false, false, $queryMode ); 

        list( $select, $from, $where ) = $query->query( );

        // make sure the groups stuff is included only if specifically specified
        // by the fields param (CRM-1969), else we limit the contacts outputted to only
        // ones that are part of a group
        if ( $origFields &&
             CRM_Utils_Array::value( 'groups', $returnProperties ) ) {
            $groupClause = " ( civicrm_group_contact.status = 'Added' OR civicrm_group_contact.status is NULL ) ";
            if ( empty( $where ) ) {
                $where = "WHERE $groupClause";
            } else {
                $where .= " AND $groupClause";
            }
        }

        if ( $componentClause ) {
            if ( empty( $where ) ) {
                $where = "WHERE $componentClause";
            } else {
                $where .= " AND $componentClause";
            }
        }
        
        $queryString = "$select $from $where";

        if ( CRM_Utils_Array::value( 'tags'  , $returnProperties ) || 
             CRM_Utils_Array::value( 'groups', $returnProperties ) ||
             CRM_Utils_Array::value( 'notes' , $returnProperties ) ) { 
            $queryString .= " GROUP BY contact_a.id";
        }
        
        if ( $order ) {
            list( $field, $dir ) = explode( ' ', $order, 2 );
            $field = trim( $field );
            if ( CRM_Utils_Array::value( $field, $returnProperties ) ) {
                $queryString .= " ORDER BY $order";
            }
        }

        //hack for student data
        require_once 'CRM/Core/OptionGroup.php';
        $multipleSelectFields = array( 'preferred_communication_method' => 1 );

        if ( CRM_Core_Permission::access( 'Quest' ) ) { 
            require_once 'CRM/Quest/BAO/Student.php';
            $studentFields = array();
            $studentFields = CRM_Quest_BAO_Student::$multipleSelectFields;
            $multipleSelectFields = array_merge( $multipleSelectFields, $studentFields );
        }
        //crm_core_error::debug('$queryString', $queryString ); exit();
        $dao =& CRM_Core_DAO::executeQuery( $queryString, CRM_Core_DAO::$_nullArray );
        $header = false;
        
        if ( $paymentFields ) {
            $addPaymentHeader = true;
            //special return properties for event and members
            $paymentHeaders = array( 'Total Amount', 'Contribution Status', 'Received Date',
                                     'Payment Instrument', 'Transaction ID');

            // get payment related in for event and members
            $paymentDetails = CRM_Contribute_BAO_Contribution::getContributionDetails( $exportMode, $ids );
        }

        $contactDetails = array( );
        while ($dao->fetch()) {
            $row = array( );
            $validRow = false;
            foreach ($dao as $key => $varValue) {
                $flag = false;
                foreach ($returnProperties as $propKey => $props) {
                    if (is_array($props)) {
                        foreach($props as $propKey1=>$prop) {
                            foreach($prop as $propkey2=>$prop1) {
                                $locationfield = str_replace( ' ', '_', $propKey1."-".$propkey2 );
                                if( $locationfield == $key) {
                                    $flag = true;
                                }
                            }
                        }
                    }
                } 
                
                if (array_key_exists($key, $returnProperties)) {
                    $flag = true;
                }
                //for exporting both Current Employer and 
                //Internal contact ID, 'contact_id' should be present in $row.
                if ( ( $key == 'contact_id' && array_key_exists( 'id' , $returnProperties ) ) ||
                     ( $key == 'contact_id' && $currentEmployer ) ) {
                    $flag = true;
                }
                
                if ( $flag ) {
                    if ( isset( $varValue ) && $varValue != '' ) {
                        if ( $cfID = CRM_Core_BAO_CustomField::getKeyID($key) ) {
                            $row[$key] = CRM_Core_BAO_CustomField::getDisplayValue( $varValue, $cfID, $query->_options );
                        } else if ( array_key_exists($key ,$multipleSelectFields ) ){
                            $paramsNew = array($key => $varValue );
                            if ( $key == 'test_tutoring') {
                                $name = array( $key => array('newName' => $key ,'groupName' => 'test' ));
                            } else if (substr( $key, 0, 4) == 'cmr_') { //for  readers group
                                $name = array( $key => array('newName' => $key, 'groupName' => substr($key, 0, -3) ));
                            } else {
                                $name = array( $key => array('newName' => $key ,'groupName' => $key ));
                            }
                            CRM_Core_OptionGroup::lookupValues( $paramsNew, $name, false );
                            $row[$key] = $paramsNew[$key];
                            
                        } else {
                            $row[$key] = $varValue;
                        }
                        $validRow  = true;
                    } else {
                        $row[$key] = '';
                    }
                   
                    if ( ! $header ) {
                        if (isset($query->_fields[$key]['title'])) {
                            $headerRows[] = $query->_fields[$key]['title'];
                        } else if ($key == 'phone_type'){
                            $headerRows[] = 'Phone Type';
                        } else if ($key == 'contact_id'){
                            $headerRows[] = $query->_fields['id']['title'];
                        } else {
                            $keyArray = explode('-', $key);
                            
                            $hdr      = $keyArray[0];

                            if ( CRM_Utils_Array::value( 1, $keyArray ) ) {
                                $hdr .= "-" . $query->_fields[$keyArray[1]]['title'];
                            }

                            if ( CRM_Utils_Array::value( 2, $keyArray ) ) {
                                $hdr .= " " . $keyArray[2];
                            }
                            $headerRows[] = $hdr;
                        }
                    }
                }
            }
            
            if ( $paymentFields && $addPaymentHeader && isset( $paymentDetails[ $row[$paymentTableId] ] ) ) {
                $headerRows = array_merge(  $headerRows, $paymentHeaders );
                $addPaymentHeader = false;
            }

            if ( $validRow ) {
                //get the current employer name, CRM-2968.
                if ( ( $currentEmployer || $primary ) && $exportMode == CRM_Export_Form_Select::CONTACT_EXPORT ) {
                    require_once 'CRM/Contact/BAO/Relationship.php';
                    $relationships = CRM_Contact_BAO_Relationship::getRelationship( $row['contact_id'] );
                    krsort( $relationships );
                    foreach ( $relationships as $relationshipID => $value ) {
                        if ( $value['relation'] == 'Employee of' && $value['is_active'] == 1 ) {
                            $row['current_employer'] = $value['name'];
                            break;
                        }
                    }
                    //unset contact_id if Internal Contact ID is not map;
                    if ( $unsetContactID ) {
                        unset( $row['contact_id'] );
                    }
                }

                if ( $paymentFields && isset( $paymentDetails[ $row[$paymentTableId] ] ) ) {
                    $row = array_merge( $row, $paymentDetails[ $row[$paymentTableId] ] );
                }
                
                $contactDetails[] = $row;
            }
            $header = true;
        }

        if ( ($currentEmployer || $primary ) && $exportMode == CRM_Export_Form_Select::CONTACT_EXPORT ) {
            $headerRows[] = 'Current Employer';
        }
        //unset contact id from header when Internal Contact ID is not map;
        if ( $unsetContactID ) {
            $unsetKey = CRM_Utils_Array::key('Internal Contact ID', $headerRows );
            unset( $headerRows[$unsetKey] );
        }
        
        require_once 'CRM/Core/Report/Excel.php';
        CRM_Core_Report_Excel::writeCSVFile( self::getExportFileName( 'csv', $exportMode ), $headerRows, $contactDetails );
        
        exit();
    }

    /**
     * name of the export file based on mode
     *
     * @param string $output type of output
     * @param int    $mode export mode
     * @return string name of the file
     */
    function getExportFileName( $output = 'csv', $mode = CRM_Export_Form_Select::CONTACT_EXPORT ) 
    {
        switch ( $mode ) {
        case CRM_Export_Form_Select::CONTACT_EXPORT : 
            return ts('CiviCRM Contact Search');
            
        case CRM_Export_Form_Select::CONTRIBUTE_EXPORT : 
            return ts('CiviCRM Contribution Search');
            
        case CRM_Export_Form_Select::MEMBER_EXPORT : 
            return ts('CiviCRM Member Search');
            
        case CRM_Export_Form_Select::EVENT_EXPORT : 
            return ts('CiviCRM Participant Search');
        }
    }


    /**
     * handle the export case. this is a hack, so please fix soon
     *
     * @param $args array this array contains the arguments of the url
     *
     * @static
     * @access public
     */
    static function invoke( $args ) 
    {
        // FIXME:  2005-06-22 15:17:33 by Brian McFee <brmcfee@gmail.com>
        // This function is a dirty, dirty hack.  It should live in its own
        // file.
        $session =& CRM_Core_Session::singleton();
        $type = $_GET['type'];
        
        if ($type == 1) {
            $varName = 'errors';
            $saveFileName = 'Import_Errors.csv';
        } else if ($type == 2) {
            $varName = 'conflicts';
            $saveFileName = 'Import_Conflicts.csv';
        } else if ($type == 3) {
            $varName = 'duplicates';
            $saveFileName = 'Import_Duplicates.csv';
        } else if ($type == 4) {
            $varName = 'mismatch';
            $saveFileName = 'Import_Mismatch.csv';
        }else {
            /* FIXME we should have an error here */
            return;
        }
        
        // FIXME: a hack until we have common import
        // mechanisms for contacts and contributions
        $realm = CRM_Utils_Array::value('realm',$_GET);
        if ($realm == 'contribution') {
            $controller = 'CRM_Contribute_Import_Controller';
        } else if ( $realm == 'membership' ) {
            $controller = 'CRM_Member_Import_Controller';
        } else if ( $realm == 'event' ) {
            $controller = 'CRM_Event_Import_Controller';
        } else if ( $realm == 'activity' ) {
            $controller = 'CRM_Activity_Import_Controller';
        } else {
            $controller = 'CRM_Import_Controller';
        }
        
        require_once 'CRM/Core/Key.php';
        $qfKey = CRM_Core_Key::get( $controller );
        
        $fileName = $session->get($varName . 'FileName', "{$controller}_{$qfKey}");
        
        $config =& CRM_Core_Config::singleton( ); 
        
        header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
        header('Content-Description: File Transfer');
        header('Content-Type: text/csv');
        header('Content-Length: ' . filesize($fileName) );
        header('Content-Disposition: attachment; filename=' . $saveFileName);
        
        readfile($fileName);
        
        exit();
    }

}


