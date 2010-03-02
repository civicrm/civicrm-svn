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
 * This class generates form components for OpenCase Activity
 * 
 */
class CRM_Case_Form_Activity_LinkCases
{
    static function preProcess( &$form ) 
    { 
        if ( !isset($form->_caseId) ) {
            CRM_Core_Error::fatal(ts('Case Id not found.'));
        }
    }
    
    /**
     * This function sets the default values for the form. For edit/view mode
     * the default values are retrieved from the database
     * 
     * @access public
     * @return None
     */
    function setDefaultValues( &$form ) 
    {
        return $defaults = array();
    }
    
    static function buildQuickForm( &$form ) 
    {
        $form->add( 'text', 'link_to_case', ts( 'Link To Case' ) );
        $form->add( 'hidden', 'link_to_case_id', '', array( 'id' => 'link_to_case_id') );
    }
    
    /**
     * global validation rules for the form
     *
     * @param array $values posted values of the form
     *
     * @return array list of errors to be posted back to the form
     * @static
     * @access public
     */
    static function formRule( $values, $files, $form ) 
    {
        $errors = array( );
        if ( !CRM_Utils_Array::value( 'link_to_case_id', $values ) ) {
            $errors['link_to_case'] = ts( 'Please select a case to link.' );
        } else if ( $values['link_to_case_id'] == $form->_caseId ) {
            $errors['link_to_case'] = ts( 'Please select some other case to link.' );
        }
        
        return empty( $errors ) ? true : $errors ;
    }
    
    /**
     * Function to process the form
     *
     * @access public
     * @return None
     */
    public function beginPostProcess( &$form, &$params ) 
    {
        $params['id'] = $params['case_id'];
    }
    
    /**
     * Function to process the form
     *
     * @access public
     * @return None
     */
    public function endPostProcess( &$form, &$params ) 
    { 
        CRM_Core_Error::debug( '$params', $params );
        exit;
        
    }
}
