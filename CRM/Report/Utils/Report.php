<?php

/*
 +--------------------------------------------------------------------+
 | CiviCRM version 2.3                                                |
 +--------------------------------------------------------------------+
 | Copyright CiviCRM LLC (c) 2004-2009                                |
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
 * @copyright CiviCRM LLC (c) 2004-2009
 * $Id$
 *
 */


class CRM_Report_Utils_Report {

    static function getValueFromUrl( $instanceID = null ) {
        if ( $instanceID ) {
            $optionVal = CRM_Core_DAO::getFieldValue( 'CRM_Report_DAO_Instance',
                                                      $instanceID,
                                                      'report_id' );
        } else {
            $config =& CRM_Core_Config::singleton( );
            $args   = explode( '/', $_GET[$config->userFrameworkURLVar] );

            // remove 'civicrm/report' from args
            array_shift($args);
            array_shift($args);

            // put rest of arguement back in the form of url, which is how value 
            // is stored in option value table
            $optionVal = implode( '/', $args );
        }
        return $optionVal;
    }

    static function getValueIDFromUrl( $instanceID = null ) {
        $optionVal = self::getValueFromUrl( $instanceID );

        if ( $optionVal ) {
            require_once 'CRM/Core/OptionGroup.php';
            $templateInfo = CRM_Core_OptionGroup::getRowValues( 'report_template', "{$optionVal}", 'value' );
            return array( $templateInfo['id'], $optionVal );
        }

        return false;
    }

    static function getInstanceIDForValue( $optionVal ) {
        static $valId = array();

        if ( ! array_key_exists($optionVal, $valId) ) {
            $sql = "
SELECT MAX(id) FROM civicrm_report_instance
WHERE  report_id = %1";
            
            $params = array( 1 => array( $optionVal, 'String' ) );
            $valId[$optionVal] = CRM_Core_DAO::singleValueQuery( $sql, $params );
        }
        return $valId[$optionVal];
    }

    static function getNextUrl( $urlValue, $query = 'reset=1', $absolute = false, $instanceID = null ) {
        if ( $instanceID ) {
            $instanceID = self::getInstanceIDForValue( $urlValue );
                
            if ( $instanceID ) {
                return CRM_Utils_System::url( "civicrm/report/instance/{$instanceID}", 
                                              "{$query}", $absolute );
            } else {
                return false;
            }
        } else {
            return CRM_Utils_System::url( "civicrm/report/" . trim($urlValue, '/') , 
                                          $query, $absolute );
        }
    }

    // get instance count for a template 
    static function getInstanceCount( $optionVal ) {
        $sql = "
SELECT count(inst.id)
FROM   civicrm_report_instance inst
WHERE  inst.report_id = %1";

        $params = array( 1 => array( $optionVal, 'String' ) );
        $count  = CRM_Core_DAO::singleValueQuery( $sql, $params );
        return $count;
    }

    static function mailReport( $fileContent, $instanceID = null, $outputMode = 'html' ) {
        if ( ! $instanceID ) {
            return false;
        }

        $url = CRM_Utils_System::url("civicrm/report/instance", 
                                     "reset=1&id={$instanceID}", true);
        $url = "Report Url: {$url} ";
        $fileContent = $url . $fileContent;

        require_once 'CRM/Core/BAO/Domain.php';
        list( $domainEmailName, 
              $domainEmailAddress ) = CRM_Core_BAO_Domain::getNameAndEmail( );

        $params       = array( 'id' => $instanceID );
        $instanceInfo = array( );
        CRM_Core_DAO::commonRetrieve( 'CRM_Report_DAO_Instance',
                                      $params,
                                      $instanceInfo );

        $from          = '"' . $domainEmailName . '" <' . $domainEmailAddress . '>';
        $toDisplayName = "";//$domainEmailName;
        $toEmail       = $instanceInfo['email_to'];
        $ccEmail       = $instanceInfo['email_cc'];
        $subject       = $instanceInfo['email_subject'];

        require_once 'Mail/mime.php';
        require_once "CRM/Utils/Mail.php";
        return CRM_Utils_Mail::send( $from, 
                                     $toDisplayName, 
                                     $toEmail, 
                                     $subject, 
                                     '',
                                     $ccEmail, 
                                     null,  
                                     null, 
                                     $fileContent, 
                                     $attachments );
    }

    static function export2csv( &$form ) {
        //Mark as a CSV file.
        header('Content-Type: text/csv');
          
        //Force a download and name the file using the current timestamp.
        header('Content-Disposition: attachment; filename=Report_' . $_SERVER['REQUEST_TIME'] . '.csv');
                  
        //Load rows
        $sql = " {$form->_select}  {$form->_from}  {$form->_where}  {$form->_groupBy}  {$form->_having} {$form->_orderBy} ";
        
        $rows = array();
        $form->buildRows( $sql, $rows );
        $form->formatDisplay( $rows );
        require_once 'CRM/Utils/Money.php';
        $config    =& CRM_Core_Config::singleton( );        
        //Output headers if this is the first row.
        $columnHeaders = array_keys( $form->_columnHeaders );

        // Replace internal header names with friendly ones, where available.
        foreach ( $columnHeaders as $header ) {
            if ( isset( $form->_columnHeaders[$header] ) ) {
                $headers[] = html_entity_decode(strip_tags($form->_columnHeaders[$header]['title']));
            }
        }
        //Output the headers.
        echo implode(',', $headers) . "\n";

        foreach ( $rows as $row ) {
            foreach ( $row as $key => $value ) {
                // Remove HTML, unencode entities, and escape quotation marks.
                $row[$key] = '"' . str_replace('"', '""', html_entity_decode(strip_tags($value))) . '"';
                if ( strstr($key, 'link') || strstr($key, 'hover') ) {
                    unset($row[$key]);
                }
                if ( substr($key, 0, 1) == '_' ) {
                    unset($row[$key]);
                }

                if ( CRM_Utils_Array::value( 'type', $form->_columnHeaders[$key] ) & 4 ) {
                    if ( $form->_columnHeaders[$key]['group_by'] == 'MONTH' ||
                         $form->_columnHeaders[$key]['group_by'] ==  'QUARTER' ) {
                        $row[$key] =  CRM_Utils_Date::customFormat( $value, $config->dateformatPartial );
                    } elseif ( $form->_columnHeaders[$key]['group_by'] == 'YEAR' ) {
                        $row[$key] =  CRM_Utils_Date::customFormat( $value, $config->dateformatYear );
                    } else {
                        $row[$key] =  CRM_Utils_Date::customFormat( $value );
                    }
                } else if ( CRM_Utils_Array::value( 'type', $form->_columnHeaders[$key] ) == 1024 ) {
                    $row[$key] =  CRM_Utils_Money::format( $value );
                }
            }
            //Output the data row.
            echo implode(',', $row) . "\n";
        }
        exit( );
    }

    static function add2group( &$form , $groupID ) {

        if ( is_numeric( $groupID ) && isset( $form->_aliases['civicrm_contact'] ) ) {

            require_once 'CRM/Contact/BAO/GroupContact.php';
            $sql = "SELECT DISTINCT {$form->_aliases['civicrm_contact']}.id AS contact_id {$form->_from} {$form->_where} ";
            $dao = CRM_Core_DAO::executeQuery( $sql );

            $contact_ids = array();                        
            // Add resulting contacts to group
            while ( $dao->fetch( ) ) {
                $contact_ids[] = $dao->contact_id;
            }

            CRM_Contact_BAO_GroupContact::addContactsToGroup( $contact_ids, $groupID );
        } 
    }
    static function getInstanceID() {

        $config    =& CRM_Core_Config::singleton( );
        $arg       = explode( '/', $_GET[$config->userFrameworkURLVar] );
        $secondArg = CRM_Utils_Array::value( 2, $arg );
        require_once 'CRM/Utils/Rule.php';
        if ( $arg[1]    == 'report' &&
             $secondArg == 'instance' ) {
            if ( CRM_Utils_Rule::positiveInteger( $arg[3] ) ) {
                return $arg[3];
            }
        }
    }
    static function isInstancePermission( $instanceId ) {
        if ( !( $instanceId ) ) {
            return true;
        }
        $params = array( 'id' => $instanceId );
        $instanceValues = array( );
        CRM_Core_DAO::commonRetrieve( 'CRM_Report_DAO_Instance',
                                      $params,
                                      $instanceValues );
        $instanceValues['permission'] = unserialize( $instanceValues['permission'] );
        if ( $instanceValues['permission'][0][0] && 
             ( !(CRM_Core_Permission::checkMenu( $instanceValues['permission'][0], 
                                                 $instanceValues['permission'][1] ) ||
                 CRM_Core_Permission::check( 'administer Reports' ) )
               ) ) {
            return false;
        }
        
        return true;
    }
}
