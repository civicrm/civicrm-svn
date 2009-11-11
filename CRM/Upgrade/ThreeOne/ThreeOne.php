<?php

/*
 +--------------------------------------------------------------------+
 | CiviCRM version 3.1                                                |
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

require_once 'CRM/Upgrade/Form.php';
require_once 'CRM/Core/OptionGroup.php';
require_once 'CRM/Core/OptionValue.php';

class CRM_Upgrade_ThreeOne_ThreeOne extends CRM_Upgrade_Form {

    function verifyPreDBState( &$errorMessage ) {
        $latestVer  = CRM_Utils_System::version();
        
        $errorMessage = ts('Pre-condition failed for upgrade to %1.', array( 1 => $latestVer ));

        // check table, if the db is 3.1
        if ( CRM_Core_DAO::checkTableExists( 'civicrm_acl_contact_cache' ) ) {
            $errorMessage =  ts("Database check failed - it looks like you have already upgraded to the latest version (v%1) of the database. OR If you think this message is wrong, it is very likely that this a partially upgraded db and you will need to reload the correct db on which upgrade was never tried.", array( 1 => $latestVer ));
            return false;
        } 

        // check table-column, if the db is 3.1 
        if ( CRM_Core_DAO::checkFieldExists( 'civicrm_custom_field', 'date_format' ) ) {
            $errorMessage =  ts("Database check failed - it looks like you have already upgraded to the latest version (v%1) of the database. OR If you think this message is wrong, it is very likely that this a partially upgraded db and you will need to reload the correct db on which upgrade was never tried.", array( 1 => $latestVer ));
            return false;
        } 
        
        //check previous version table e.g 3.0.*
        if ( ! CRM_Core_DAO::checkTableExists( 'civicrm_participant_status_type' ) ||
             ! CRM_Core_DAO::checkTableExists( 'civicrm_navigation' ) ) {
            $errorMessage .= ' Few important tables were found missing.';
            return false;
        }
        
        // check fields which MUST be present if a proper 3.0.* db
        if ( ! CRM_Core_DAO::checkFieldExists( 'civicrm_relationship_type', 'label_a_b' )      ||
             ! CRM_Core_DAO::checkFieldExists( 'civicrm_mapping_field',     'im_provider_id' ) ||
             ! CRM_Core_DAO::checkFieldExists( 'civicrm_contact',           'email_greeting_id' ) ) {
            // db looks to have stuck somewhere between 2.2 & 3.0
            $errorMessage .= ' Few important fields were found missing in some of the tables.';
            return false;
        }

        return true;
    }
    
    function upgrade( $rev ) {

        $upgrade =& new CRM_Upgrade_Form( );

        //Run the SQL file
        $upgrade->processSQL( $rev );

        // fix for CRM-5162
        // we need to encrypt all smtpPasswords if present
        require_once "CRM/Core/DAO/Preferences.php";
        $mailingDomain =& new CRM_Core_DAO_Preferences();
        $mailingDomain->find( );
        while ( $mailingDomain->fetch( ) ) {
            if ( $mailingDomain->mailing_backend ) {
                $values = unserialize( $mailingDomain->mailing_backend );
                
                if ( isset( $values['smtpPassword'] ) ) {
                    require_once 'CRM/Utils/Crypt.php';
                    $values['smtpPassword'] = CRM_Utils_Crypt::encrypt( $values['smtpPassword'] );
                    $mailingDomain->mailing_backend = serialize( $values );
                    $mailingDomain->save( );
                }
            }
        }
        
        require_once "CRM/Core/DAO/Domain.php";
        $domain =& new CRM_Core_DAO_Domain();
        $domain->selectAdd( );
        $domain->selectAdd( 'config_backend' );
        $domain->find(true);
        if ( $domain->config_backend ) {
            $defaults = unserialize($domain->config_backend);
            if ( $dateFormat = CRM_Utils_Array::value( 'dateformatQfDate', $defaults ) ) {
                $dateFormatArray =  explode(" ", $dateFormat );

                //replace new date format based on previous month format
                //%b month name [abbreviated]
                //%B full month name ('January'..'December')
                //%m decimal number, 0-padded ('01'..'12')                

                if ( $dateFormat == '%b %d %Y' ) {
                    $defaults['dateInputFormat']= 'mm/dd/yy';
                } else if ( $dateFormat == '%d-%b-%Y') {
                    $defaults['dateInputFormat']= 'dd-mm-yy';
                } else if ( in_array( '%b', $dateFormatArray ) ) {
                    $defaults['dateInputFormat']= 'M d, yy';
                } else if ( in_array( '%B', $dateFormatArray ) ) {
                    $defaults['dateInputFormat']= 'MM d, yy';
                } else {
                    $defaults['dateInputFormat']= 'mm/dd/yy';
                }
            }
            // %p - lowercase ante/post meridiem ('am', 'pm')
            // %P - uppercase ante/post meridiem ('AM', 'PM')
            if ( $dateTimeFormat = CRM_Utils_Array::value( 'dateformatQfDatetime', $defaults ) ) {
                $defaults['timeInputFormat'] = 2;
                $dateTimeFormatArray =  explode(" ", $dateFormat );
                if ( in_array('%P', $dateTimeFormatArray) || in_array('%p', $dateTimeFormatArray)) {
                    $defaults['timeInputFormat'] = 1;
                }
                unset($defaults['dateformatQfDatetime']);
            }

            unset($defaults['dateformatQfDate']);
            unset($defaults['dateformatTime']);
            require_once "CRM/Core/BAO/Setting.php";
            CRM_Core_BAO_Setting::add($defaults);                            
        }
        
        $sql     = "SELECT id, form_values FROM civicrm_report_instance";
        $instDAO = CRM_Core_DAO::executeQuery( $sql );
        while ( $instDAO->fetch( ) ) {
            $fromVal = @unserialize($instDAO->form_values);
            foreach ( (array)$fromVal as $key => $value ) {
                if ( strstr( $key, '_relative' ) ) {
                    $elementName =  substr($key, 0, (strlen($key) - strlen('_relative') ) );
                    
                    $fromNamekey = $elementName . '_from';
                    $toNamekey   = $elementName . '_to';
                    
                    $fromNameVal = $fromVal[$fromNamekey];
                    $toNameVal   = $fromVal[$toNamekey];
                    //check 'choose date range' is set
                    if ( $value == '0' ) {
                        if ( CRM_Utils_Date::isDate($fromNameVal) ) {
                            $fromDate= CRM_Utils_Date::setDateDefaults(CRM_Utils_Date::format($fromNameVal));
                            $fromNameVal = $fromDate[0];
                        } else {
                            $fromNameVal = '';
                        }
                        
                        if ( CRM_Utils_Date::isDate($toNameVal) ) {
                            $toDate= CRM_Utils_Date::setDateDefaults(CRM_Utils_Date::format($toNameVal));
                            $toNameVal = $toDate[0];
                        } else {
                            $toNameVal = '';
                        }
                    } else {
                        $fromNameVal = '';
                        $toNameVal   = '';
                    }
                    $fromVal[$fromNamekey] = $fromNameVal;
                    $fromVal[$toNamekey]   = $toNameVal;
                    continue; 
                }
            }

            $fromVal   = serialize($fromVal);
            $updateSQL = "UPDATE civicrm_report_instance SET form_values = '{$fromVal}' WHERE id = {$instDAO->id}";
            CRM_Core_DAO::executeQuery( $updateSQL );
        }
        
        $customFieldSQL = "SELECT id, date_format FROM civicrm_custom_field WHERE data_type = 'Date' ";
        $customDAO      = CRM_Core_DAO::executeQuery( $customFieldSQL );
        while ( $customDAO->fetch( ) ) {
            $datePartKey =$dateParts   = explode(CRM_Core_DAO::VALUE_SEPARATOR ,$customDAO->date_format);                    
            $dateParts   = array_combine($datePartKey, $dateParts);
            
            $year       = CRM_Utils_Array::value('Y', $dateParts);
            $month      = CRM_Utils_Array::value('M', $dateParts);
            $date       = CRM_Utils_Array::value('d', $dateParts);
            $hour       = CRM_Utils_Array::value('h', $dateParts);
            $minute     = CRM_Utils_Array::value('i', $dateParts);
            $timeFormat = CRM_Utils_Array::value('A', $dateParts);
            
            $newDateFormat = 'mm/dd/yy';
            if ($year && $month && $date ) {
                $newDateFormat = 'mm/dd/yy';
            } else if (!$year && $month && $date ) {
                $newDateFormat = 'mm/dd';
            }
            
            $newTimeFormat = 'NULL';
            if ( $timeFormat && $hour == 'h') {
                $newTimeFormat = 1;    
            } else if ($hour) {
                $newTimeFormat = 2;            
            }
            $updateSQL = "UPDATE civicrm_custom_field SET date_format = '{$newDateFormat}', time_format = {$newTimeFormat} WHERE id = {$customDAO->id}";
            CRM_Core_DAO::executeQuery( $updateSQL );
        }

        $template = & CRM_Core_Smarty::singleton( );
        $afterUpgradeMessage = '';
        if ( $afterUpgradeMessage = $template->get_template_vars('afterUpgradeMessage') ) $afterUpgradeMessage .= "<br/><br/>";
        $afterUpgradeMessage .= ts("Date Input Format has been set to %1 format. If you want to use a different format please check Date settings", array( 1 => $defaults['dateInputFormat']) );
        $template->assign('afterUpgradeMessage', $afterUpgradeMessage);
    }
}
