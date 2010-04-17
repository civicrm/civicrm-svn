<?php

/*
 +--------------------------------------------------------------------+
 | CiviCRM version 3.1                                                |
 +--------------------------------------------------------------------+
 | Copyright CiviCRM LLC (c) 2004-2010                                |
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
 | License and the CiviCRM Licensing Exception along                  |
 | with this program; if not, contact CiviCRM LLC                     |
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

/**
 * This class generates form element for free tag widget
 * 
 */
class CRM_Core_Form_Tag
{
    /**
     * Function to build tag widget if correct parent is passed
     * 
     * @param object  $form form object
     * @param string  $parentName parent name ( tag name)
     * @param string  $entityTable entitytable 'eg: civicrm_contact'
     * @param int     $entityId    entityid  'eg: contact id'
     *
     * @return void
     * @access public
     * @static
     */
    static function buildQuickForm( &$form, $parentName, $entityTable, $entityId ) {
        // get the parent id for tag list input for keyword
        $parentId = CRM_Core_DAO::getFieldValue( 'CRM_Core_DAO_Tag', $parentName, 'id',  'name' );
        
        // check if parent exists
        $entityTags = array( );
        if ( $parentId ) {
            $form->assign( 'parentId', $parentId );        

            //tokeninput url
            $tagUrl = CRM_Utils_System::url( 'civicrm/ajax/taglist',
                                               "parentId={$parentId}",
                                               false, null, false );
                                               
            $form->assign( 'tagUrl', $tagUrl );
            $form->assign( 'entityTable', $entityTable );
            
            if ( $entityId ) {
                $form->assign( 'entityId', $entityId );
                require_once 'CRM/Core/BAO/EntityTag.php';
                $entityTags = CRM_Core_BAO_EntityTag::getChildEntityTags( $parentId, $entityId, $entityTable );
            
                if ( !empty( $entityTags ) ) {
                    $form->assign( 'entityTags', json_encode( $entityTags ) );
                }
            }
        }
    }
}