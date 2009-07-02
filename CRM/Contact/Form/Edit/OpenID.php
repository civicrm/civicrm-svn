<?php

/*
 +--------------------------------------------------------------------+
 | CiviCRM version 2.2                                                |
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

/**
 * form helper class for an OpenID object
 */
class CRM_Contact_Form_Edit_OpenID
{
    /**
     * build the form elements for an open id object
     *
     * @param CRM_Core_Form $form       reference to the form object
     * @param array         $location   the location object to store all the form elements in
     * @param int           $locationId the locationId we are dealing with
     * @param int           $count      the number of blocks to create
     *
     * @return void
     * @access public
     * @static
     */
    static function buildQuickForm( &$form ) {
        
        //FIXME : &$location, $locationId, $count
        
        $blockId = ( $form->get( 'OpenID_Block_Count' ) ) ? $form->get( 'OpenID_Block_Count' ) : 1;
        
        require_once 'CRM/Core/BAO/Preferences.php';
        if ( CRM_Utils_Array::value( 'openid', CRM_Core_BAO_Preferences::valueOptions( 'address_options', true, null, true ) ) ) {
            $form->assign('showOpenID', true);
            $form->addElement('text', "openid[$blockId][openid]", ts('OpenID'),
                              CRM_Core_DAO::getAttribute('CRM_Core_DAO_OpenID', 'openid'));
            
            //Location Index
            $form->addElement( 'hidden', 'hidden_OpenID_Count', $blockId, array( 'id' => 'hidden_OpenID_Count') );  
            
            //Block type
            $form->addElement('select',"openid[$blockId][location_type_id]", '' , CRM_Core_PseudoConstant::locationType());
            
            $config=& CRM_Core_Config::singleton( );
            if ( $config->userFramework == 'Standalone' ) { 
                $location[$locationId]['openid'][$blockId]['allowed_to_login'] = 
                    $form->addElement('advcheckbox', "openid[$blockId][allowed_to_login]", null, ts('Allowed to Login'));
            }
            
            //is_Primary radio
            $js = array( 'id' => 'primary_openid', 'onClick' => 'singleSelect(this, this.id);');
            $form->addElement('radio', "openid[$blockId][is_primary]", null,'', $blockId, $js );
        }
    }
}

