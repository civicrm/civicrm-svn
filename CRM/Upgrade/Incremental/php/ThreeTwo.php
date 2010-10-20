<?php 

/*
 +--------------------------------------------------------------------+
 | CiviCRM version 3.2                                                |
 +--------------------------------------------------------------------+
 | Copyright CiviCRM LLC (c) 2004-2010                                |
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
 * @copyright CiviCRM LLC (c) 2004-2010
 * $Id$
 *
 */

class CRM_Upgrade_Incremental_php_ThreeTwo {
    
    function verifyPreDBstate ( &$errors ) {
        return true;
    }
    
    function upgrade_3_2_alpha1( $rev ) 
    {
        //CRM-5666 -if user already have 'access CiviCase'
        //give all new permissions and drop access CiviCase.
        $config = CRM_Core_Config::singleton( );
        if ( $config->userFramework == 'Drupal' ) {
            db_query( "UPDATE {permission} SET perm = REPLACE( perm, 'access CiviCase', 'access my cases and activities, access all cases and activities, administer CiviCase' )" );
            //insert core acls.
            $casePermissions = array( 'delete in CiviCase',
                                      'administer CiviCase', 
                                      'access my cases and activities', 
                                      'access all cases and activities', );
            require_once 'CRM/ACL/DAO/ACL.php';
            $aclParams = array( 'name'         => 'Core ACL',
                                'deny'         => 0,
                                'acl_id'       => NULL,
                                'object_id'    => NULL,
                                'acl_table'    => NULL,
                                'entity_id'    => 1,
                                'operation'    => 'All',
                                'is_active'    => 1,
                                'entity_table' => 'civicrm_acl_role' );
            foreach ( $casePermissions as $per ) {
                $aclParams['object_table'] = $per;
                $acl = new CRM_ACL_DAO_ACL( );
                $acl->object_table = $per;
                if ( !$acl->find( true ) ) {
                    $acl->copyValues( $aclParams );
                    $acl->save( );
                }
            }
            //drop 'access CiviCase' acl
            CRM_Core_DAO::executeQuery( "DELETE FROM civicrm_acl WHERE object_table = 'access CiviCase'" );
        }
        
        $upgrade =& new CRM_Upgrade_Form( );
        $upgrade->processSQL( $rev );
    }

    function upgrade_3_2_beta4($rev)
    {
        $upgrade = new CRM_Upgrade_Form;
        
        $config =& CRM_Core_Config::singleton();
        $seedLocale = $config->lcMessages;

        //handle missing civicrm_uf_field.help_pre
        $hasLocalizedPreHelpCols = false;
        
        // CRM-6451: for multilingual sites we need to find the optimal
        // locale to use as the final civicrm_membership_status.name column
        $domain = new CRM_Core_DAO_Domain;
        $domain->find(true);
        $locales = array( );
        if ($domain->locales) {
            $locales = explode(CRM_Core_DAO::VALUE_SEPARATOR, $domain->locales);
            // optimal: an English locale
            foreach (array('en_US', 'en_GB', 'en_AU') as $loc) {
                if (in_array($loc, $locales)) {
                    $seedLocale = $loc;
                    break;
                }
            }

            // if no English and no $config->lcMessages: use the first available
            if ( !$seedLocale ) $seedLocale = $locales[0];

            $upgrade->assign('seedLocale', $seedLocale);
            $upgrade->assign('locales',    $locales);
            
            $localizedColNames = array( );
            foreach ( $locales as $loc ) {
                $localizedName = "help_pre_{$loc}";
                $localizedColNames[$localizedName] = $localizedName;
            }
            $columns = CRM_Core_DAO::executeQuery( 'SHOW COLUMNS FROM civicrm_uf_field' );
            while ( $columns->fetch( ) ) {
                if ( strpos( $columns->Field, 'help_pre' ) !== false &&
                     in_array( $columns->Field, $localizedColNames ) ) {
                    $hasLocalizedPreHelpCols = true;
                    break;
                }
            }
        }
        $upgrade->assign( 'hasLocalizedPreHelpCols',  $hasLocalizedPreHelpCols);
        
        $upgrade->processSQL($rev);

        // now civicrm_membership_status.name has possibly localised strings, so fix them
        $i18n = new CRM_Core_I18n($seedLocale);
        $statuses = array(
            array(
                'name'                        => 'New',
                'start_event'                 => 'join_date',
                'end_event'                   => 'join_date',
                'end_event_adjust_unit'       => 'month',
                'end_event_adjust_interval'   => '3',
                'is_current_member'           => '1',
                'is_admin'                    => '0',
                'is_default'                  => '0',
                'is_reserved'                 => '0',
            ),
            array(
                'name'                        => 'Current',
                'start_event'                 => 'start_date',
                'end_event'                   => 'end_date',
                'is_current_member'           => '1',
                'is_admin'                    => '0',
                'is_default'                  => '1',
                'is_reserved'                 => '0',
            ),
            array(
                'name'                        => 'Grace',
                'start_event'                 => 'end_date',
                'end_event'                   => 'end_date',
                'end_event_adjust_unit'       => 'month',
                'end_event_adjust_interval'   => '1',
                'is_current_member'           => '1',
                'is_admin'                    => '0',
                'is_default'                  => '0',
                'is_reserved'                 => '0',
            ),
            array(
                'name'                        => 'Expired',
                'start_event'                 => 'end_date',
                'start_event_adjust_unit'     => 'month',
                'start_event_adjust_interval' => '1',
                'is_current_member'           => '0',
                'is_admin'                    => '0',
                'is_default'                  => '0',
                'is_reserved'                 => '0',
            ),
            array(
                'name'                        => 'Pending',
                'start_event'                 => 'join_date',
                'end_event'                   => 'join_date',
                'is_current_member'           => '0',
                'is_admin'                    => '0',
                'is_default'                  => '0',
                'is_reserved'                 => '1',
            ),
            array(
                'name'                        => 'Cancelled',
                'start_event'                 => 'join_date',
                'end_event'                   => 'join_date',
                'is_current_member'           => '0',
                'is_admin'                    => '0',
                'is_default'                  => '0',
                'is_reserved'                 => '0',
            ),
            array(
                'name'                        => 'Deceased',
                'is_current_member'           => '0',
                'is_admin'                    => '1',
                'is_default'                  => '0',
                'is_reserved'                 => '1',
            ),
        );

        require_once 'CRM/Member/DAO/MembershipStatus.php';
        $statusIds = array( );
        $insertedNewRecord = false;
        foreach ($statuses as $status) {
            $dao = new CRM_Member_DAO_MembershipStatus;

            // try to find an existing English status
            $dao->name = $status['name'];

//             // if not found, look for translated status name
//             if (!$dao->find(true)) {
//                 $found     = false;
//                 $dao->name = $i18n->translate($status['name']);
//             }
            
            // if found, update name and is_reserved
            if ($dao->find(true)) {
                $dao->name        = $status['name'];
                $dao->is_reserved = $status['is_reserved'];
                if ( $status['is_reserved'] ) {
                    $dao->is_active = 1; 
                }
                // if not found, prepare a new row for insertion
            } else {
                $insertedNewRecord = true;
                foreach ($status as $property => $value) {
                    $dao->$property = $value;
                }
                $dao->weight = CRM_Utils_Weight::getDefaultWeight('CRM_Member_DAO_MembershipStatus');
            }
            
            // add label (translated name) and save (UPDATE or INSERT)
            $dao->label = $i18n->translate($status['name']);
            $dao->save();
            
            $statusIds[$dao->id] = $dao->id;
        }
        
        //disable all status those are customs.
        if ( $insertedNewRecord  ) {
            $sql = '
UPDATE  civicrm_membership_status 
   SET  is_active = 0 
 WHERE  id NOT IN ( ' . implode( ',', $statusIds ) . ' )';
            CRM_Core_DAO::executeQuery( $sql );
        }
    
    }
    
    function upgrade_3_2_1($rev)
    {
        //CRM-6565 check if Activity Index is already exists or not.
        $addActivityTypeIndex = true;
        $indexes = CRM_Core_DAO::executeQuery( 'SHOW INDEXES FROM civicrm_activity' );
        while ( $indexes->fetch( ) ) {
            if( $indexes->Key_name == 'UI_activity_type_id' ){
                $addActivityTypeIndex = false;
            }
        }
        // CRM-6563: restrict access to the upload dir, tighten access to the config-and-log dir
        $config =& CRM_Core_Config::singleton();
        require_once 'CRM/Utils/File.php';
        CRM_Utils_File::restrictAccess($config->uploadDir);
        CRM_Utils_File::restrictAccess($config->configAndLogDir);
        $upgrade = new CRM_Upgrade_Form;
        $upgrade->assign( 'addActivityTypeIndex', $addActivityTypeIndex );
        $upgrade->processSQL($rev);
    }
    
    function upgrade_3_2_4( $rev )
    {
        // CRM-5461 -scan the entire option value table 
        // and lets correct duplicate value and weight.
        require_once 'CRM/Core/DAO/OptionValue.php';
        $optionGrpIds = array( );
        foreach ( array( 'value', 'weight' ) as $field ) {
            $main = new CRM_Core_DAO_OptionValue( );
            $main->find( true );
            while ( $main->fetch( ) ) {
                $optGrpId  = $main->option_group_id;
                $duplicate = new CRM_Core_DAO_OptionValue( );
                $duplicate->$field = $main->$field;
                $duplicate->option_group_id = $optGrpId;
                $duplicate->orderBy( 'id' );
                $duplicate->find( true );
                $maxValue = null;
                while ( $duplicate->fetch( ) && 
                        $duplicate->id > $main->id ) {
                    if ( !$maxValue ) {
                        $sql = "
SELECT  max(round(val.{$field})) as value 
  FROM  civicrm_option_value val 
 WHERE  val.option_group_id = %1";
                        $maxValue = CRM_Core_DAO::singleValueQuery( $sql, array( 1 => array( $optGrpId, 'Integer' ) ) );
                    }
                    $duplicate->$field = ++$maxValue;
                    $duplicate->save( );
                    $optionGrpIds[$optGrpId] = $optGrpId;
                }
                $duplicate->free( );
            }
            $main->free( );
        }
        
        if ( !empty( $optionGrpIds ) ) {
            $sql = '
SELECT  id, label, description 
  FROM  civicrm_option_group 
 WHERE  id IN ( ' .implode(', ',$optionGrpIds) .' )';
            $optGrp = CRM_Core_DAO::executeQuery( $sql );
            $urlParams = 'reset=1&group=';
            $urlString = 'civicrm/admin/options/';
            $msg = ts('We have updated duplicate option value and weight of option groups, you can confirm here');
            $urls = array( );
            while ( $optGrp->fetch( ) ) {
                $urls[] = ts( "<a href='%1'>%2</a>", 
                              array( 1 => CRM_Utils_System::url( 'civicrm/admin/optionValue',
                                                                 "reset=1&action=browse&gid={$optGrp->id}"),
                                     2 => ($optGrp->label) ? $optGrp->label : $optGrp->description ) );
            }
            if ( !empty( $urls ) ) { 
                $session = CRM_Core_Session::singleton( );
                $session->set( 'upgradeStatusMessage', $msg . ' ' .implode( ', ', $urls ) .'.' );
            }
        }
        
        $upgrade = new CRM_Upgrade_Form( );
        $upgrade->processSQL( $rev );
    }
    
  }
