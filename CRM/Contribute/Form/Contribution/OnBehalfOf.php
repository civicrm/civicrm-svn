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
 * @copyright CiviCRM LLC (c) 2004-2011
 * $Id$
 *
 */
require_once 'CRM/Contact/BAO/Relationship.php';
require_once 'CRM/Core/BAO/UFGroup.php';
require_once 'CRM/Member/BAO/Membership.php';

class CRM_Contribute_Form_Contribution_OnBehalfOf
{
    /** 
     * Function to set variables up before form is built 
     *                                                           
     * @return void 
     * @access public 
     */ 
    static function preProcess( &$form )
    {
        $session   = CRM_Core_Session::singleton( );
        $contactID = $session->get( 'userID' );
        $form->_relatedOrganizationFound = false;
               
        if ( $contactID ) {
            $form->_employers = CRM_Contact_BAO_Relationship::getPermissionedEmployer( $contactID );
            if ( !empty( $form->_employers ) ) {
                $form->_relatedOrganizationFound = true;
                
                $locDataURL = CRM_Utils_System::url( 'civicrm/ajax/permlocation', 'cid=', false, null, false );
                $form->assign( 'locDataURL', $locDataURL );
                
                $dataURL = CRM_Utils_System::url( 'civicrm/ajax/employer', 'cid=' . $contactID, false, null, false );
                $form->assign( 'employerDataURL', $dataURL );
            }
            
            $form->assign( 'relatedOrganizationFound', $form->_relatedOrganizationFound );
            
            if ( count( $form->_employers ) == 1 ) {
                foreach ( $form->_employers as $id => $value ) {
                    $form->_organizationName = $value['name'];
                    $orgId = $id;
                }
                $form->assign( 'orgId', $orgId );
                $form->assign( 'organizationName', $form->_organizationName );
            }
        }
    }

    /**
     * Function to build form for related contacts / on behalf of organization.
     * 
     * @param $form              object  invoking Object
     * @param $contactType       string  contact type
     * @param $title             string  fieldset title
     *
     * @static
     */
    static function buildQuickForm( &$form ) 
    {
        $form->assign( 'fieldSetTitle', ts('Organization Details') );
        $form->assign( 'buildOnBehalfForm', true );
                
        $session   = CRM_Core_Session::singleton( );
        $contactID = $session->get( 'userID' );

        if ( $contactID && count( $form->_employers ) >= 1 ) {
            $form->add('text', 'organization_id', ts('Select an existing related Organization OR Enter a new one') );
            $form->add('hidden', 'onbehalfof_id', '', array( 'id' => 'onbehalfof_id' ) );
            
            $orgOptions = array( 0 => ts('Select existing organization'),
                                 1 => ts('Create new organization') );
            
            $form->addRadio( 'org_option', ts('options'), $orgOptions );
            $form->setDefaults( array( 'org_option' => 0 ) );
            $form->add( 'checkbox', 'mode', '' );
        }
        
        $profileId     = CRM_Core_DAO::getFieldValue( 'CRM_Core_DAO_UFGroup', 
                                                      'on_behalf_organization', 'id', 'name' );
        $profileFields = CRM_Core_BAO_UFGroup::getFields( $profileId, false, CRM_Core_Action::VIEW, null, null, false,
                                                          null, false, null, CRM_Core_Permission::CREATE, null );
        $form->assign( 'profileId', $profileId );
                
        foreach ( $profileFields as $name => $field ) {
            CRM_Core_BAO_UFGroup::buildProfile( $form, $field, null, null, false, true );
        }

        $form->addElement( 'hidden', 'hidden_onbehalf_profile', 1 );
    }
}