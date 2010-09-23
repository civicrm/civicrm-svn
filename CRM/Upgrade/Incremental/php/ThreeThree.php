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

class CRM_Upgrade_Incremental_php_ThreeThree {
    
    function verifyPreDBstate ( &$errors ) {
        return true;
    }
    
    function upgrade_3_3_alpha1( $rev ) 
    {
        $config = CRM_Core_Config::singleton( );
        if ( $config->userFramework == 'Drupal' ) {
            // CRM-6426 - make civicrm profiles permissioned on drupal my account
            require_once 'CRM/Utils/System/Drupal.php';
            CRM_Utils_System_Drupal::updateCategories( );
        }
        
        // CRM-6846
        // insert name column for custom field table.
        // make sure name for custom field, group and 
        // profile should be unique and properly munged.
        $colQuery = 'ALTER TABLE `civicrm_custom_field` ADD `name` VARCHAR( 64 ) CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL DEFAULT NULL AFTER `custom_group_id` ';
        CRM_Core_DAO::executeQuery( $colQuery, CRM_Core_DAO::$_nullArray, true, null, false, false );
        
        require_once 'CRM/Utils/String.php';
        require_once 'CRM/Core/DAO/CustomField.php';
        $customFldCntQuery = 'select count(*) from civicrm_custom_field where name like %1 and id != %2';
        $customField = new CRM_Core_DAO_CustomField( );
        $customField->selectAdd( );
        $customField->selectAdd( 'id, label' );
        $customField->find( );
        while ( $customField->fetch( ) ) {
            $name   = CRM_Utils_String::munge( $customField->label, '_', 64 );
            $fldCnt = CRM_Core_DAO::singleValueQuery( $customFldCntQuery,
                                                      array( 1 => array( $name,            'String'  ),
                                                             2 => array( $customField->id, 'Integer' ) ), true, false );
            if ( $fldCnt ) $name = CRM_Utils_String::munge( "{$name}_" . rand( ), '_', 64 );
            $customFieldQuery = "
Update `civicrm_custom_field`
SET `name` = %1
WHERE id = %2
";
            $customFieldParams = array( 1 => array( $name, 'String' ),
                                        2 => array( $customField->id, 'Integer' ) );
            CRM_Core_DAO::executeQuery( $customFieldQuery, $customFieldParams, true, null, false, false );
        }
        $customField->free( );
        
        require_once 'CRM/Core/DAO/CustomGroup.php';
        $customGrpCntQuery = 'select count(*) from civicrm_custom_group where name like %1 and id != %2';
        $customGroup = new CRM_Core_DAO_CustomGroup( );
        $customGroup->selectAdd( );
        $customGroup->selectAdd( 'id, title' );
        $customGroup->find( );
        while ( $customGroup->fetch( ) ) {
            $name   = CRM_Utils_String::munge( $customGroup->title, '_', 64 );
            $grpCnt = CRM_Core_DAO::singleValueQuery( $customGrpCntQuery,
                                                      array( 1 => array( $name,            'String'  ),
                                                             2 => array( $customGroup->id, 'Integer' ) ) );
            if ( $grpCnt ) $name = CRM_Utils_String::munge( "{$name}_" . rand( ), '_', 64 );
            CRM_Core_DAO::setFieldValue( 'CRM_Core_DAO_CustomGroup', $customGroup->id, 'name', $name );
        }
        $customGroup->free( );
        
        require_once 'CRM/Core/DAO/UFGroup.php';
        $ufGrpCntQuery = 'select count(*) from civicrm_uf_group where name like %1 and id != %2';
        $ufGroup = new CRM_Core_DAO_UFGroup( );
        $ufGroup->selectAdd( );
        $ufGroup->selectAdd( 'id, title' );
        $ufGroup->find( );
        while ( $ufGroup->fetch( ) ) {
            $name     = CRM_Utils_String::munge( $ufGroup->title, '_', 64 );
            $ufGrpCnt = CRM_Core_DAO::singleValueQuery( $ufGrpCntQuery,
                                                        array( 1 => array( $name,            'String'  ),
                                                               2 => array( $ufGroup->id, 'Integer' ) ) );
            if ( $ufGrpCnt ) $name = CRM_Utils_String::munge( "{$name}_" . rand( ), '_', 64 );
            CRM_Core_DAO::setFieldValue( 'CRM_Core_DAO_UFGroup', $ufGroup->id, 'name', $name );
        }
        $ufGroup->free( );
        
        $upgrade =& new CRM_Upgrade_Form( );
        $upgrade->processSQL( $rev );
    }
    
}
