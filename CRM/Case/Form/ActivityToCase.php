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
 * This class generates form components for building activity to a case
 * 
 */
require_once 'CRM/Core/Form.php';
require_once 'CRM/Activity/Form/Activity.php';

class CRM_Case_Form_ActivityToCase extends CRM_Core_Form
{
    /**
     * Function to build the form
     *
     * @return None
     * @access public
     */
    function preProcess( ) 
    {
        $this->_activityId = CRM_Utils_Array::value( 'activityId', $_GET );
        $this->_clientId   = CRM_Utils_Array::value( 'cid', $_GET );
    }
    /**
     * This function sets the default values for the form. For edit/view mode
     * the default values are retrieved from the database
     * 
     * @access public
     * @return None
     */
    function setDefaultValues( ) 
    {
        $params = $targetContactValues = array( );
        if ( isset( $this->_activityId ) ) {
            $params = array( 'id' => $this->_activityId );
            CRM_Activity_BAO_Activity::retrieve( $params, $defaults );
            $defaults['case_subject'] = $defaults['subject'];
            
            if ( !CRM_Utils_Array::crmIsEmptyArray( $defaults['target_contact'] ) ) {
                $targetContactValues = array_combine( array_unique( $defaults['target_contact'] ),                                                       explode(';', trim($defaults['target_contact_value'] ) ) );
                // exclude the contact id of client
                if ( array_key_exists ( $this->_clientId, $targetContactValues ) ) {
                    unset( $targetContactValues[$this->_clientId] );
                }
            }
        }
        
        $this->assign( 'targetContactValues', empty( $targetContactValues ) ? false : $targetContactValues );
        
        return $defaults;
    }
    
    /**
     * Function to build the form
     *
     * @return None
     * @access public
     */
    public function buildQuickForm( )
    { 
        // tokeninput url
        $tokenUrl = CRM_Utils_System::url( "civicrm/ajax/checkemail", "noemail=1" );
        $this->assign( 'tokenUrl', $tokenUrl );
        
        $this->add( 'text', 'unclosed_cases', ts( 'Build Activity To Case' ) );
        $this->add( 'hidden', 'unclosed_case_id', '', array( 'id' => 'open_case_id' ) );
        
        $this->add( 'text', 'target_id', ts( 'With Contacts' ) );        
        
        $this->add( 'text', 'case_subject', ts( 'New Subject' ), array( 'size'=> 50));
    }
}