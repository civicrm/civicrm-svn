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

require_once 'CRM/Core/Form.php';
require_once 'CRM/Campaign/BAO/Petition.php';
require_once 'CRM/Core/PseudoConstant.php';
       
/**
 * This class generates form components for processing a petition signature 
 * 
 */

class CRM_Campaign_Form_Petition extends CRM_Core_Form
{
    
    /**
     * the id of the contact associated with this signature
     *
     * @var int
     * @public
     */
    public $_contactID;    
    
    /**
     * the id of the survey (petition) we are proceessing
     *
     * @var int
     * @protected
     */
    public $_surveyId;
    
    /**
     * values to use for custom profiles
     *
     * @var array
     * @protected
     */
    public $_values;    
    
    
    public function preProcess()
    {
        //get userID from session
        $session = CRM_Core_Session::singleton( );   
    
	    //get the contact id for this user if logged in
        $this->_contactID =  $session->get( 'userID' );  
    	
    	//get the survey id
        $this->_surveyId 	= CRM_Utils_Request::retrieve('sid', 'Positive', $this );
               
        // get the profile ids to add pre/post email signature field
        require_once 'CRM/Core/BAO/UFJoin.php'; 
        $ufJoinParams = array( 'entity_table' => 'civicrm_survey',   
                               'entity_id'    => $this->_surveyId );   
        list( $this->_values['custom_pre_id'],
              $this->_values['custom_post_id'] ) = CRM_Core_BAO_UFJoin::getUFGroupIds( $ufJoinParams );              
    }
    
    /**
     * This function sets the default values for the form. Note that in edit/view mode
     * the default values are retrieved from the database
     * 
     * @access public
     * @return None
     */
    function setDefaultValues( ) 
    {
        $defaults = array();
        // if user is logged in, display their email in the signature email field
        if ( isset( $this->_contactID ) ) {
        	require_once 'CRM/Contact/BAO/Contact/Location.php';
        	list( $this->userDisplayName, 
                    $this->userEmail ) = CRM_Contact_BAO_Contact_Location::getEmailDetails( $this->_contactID );
        	$defaults['email'] = $this->userEmail;
        }

		return $defaults;
    }
    
    public function buildQuickForm()
    {
        $this->applyFilter('__ALL__','trim');
       
		// add email
        $this->add( 'text', 'email', ts('Email Address'), array( 'size' => 50, 'maxlength' => 60 ), true );  
        $this->addRule('email', ts('Email Address is required field') , 'required');

	   // add buttons
       $this->addButtons(array(
                                array ('type'      => 'next',
                                       'name'      => ts('Sign'),
                                       'isDefault' => true),
                                array ('type'      => 'cancel',
                                       'name'      => ts('Cancel')),
                                )
                          ); 
                          
		// add profiles
        $this->buildCustom( $this->_values['custom_pre_id'] , 'customPre'  );
        $this->buildCustom( $this->_values['custom_post_id'], 'customPost' );    
    }
    
    /**
     * This function is used to add the rules (mainly global rules) for form.
     * All local rules are added near the element
     *
     * @return None
     * @access public
     * @see valid_date
     */
    
    static function formRule( $fields, $files, $errors )
    {
        $errors = array( );
        
        return empty($errors) ? true : $errors;
    }

    /**
     * Form submission of petition signature
     *
     * @access public
     * @return None
     */
    public function postProcess() 
    {

        $session = CRM_Core_Session::singleton( );
        // format params
        $params['last_modified_id'] = $session->get( 'userID' );
        $params['last_modified_date'] = date('YmdHis');
        
        if ( $this->_action & CRM_Core_Action::ADD ) { 
            $params['created_id']   = $session->get( 'userID' );
            $params['created_date'] = date('YmdHis');
        } 
        
        if ( isset( $this->_surveyId ) ) {
            $params['sid'] = $this->_surveyId;
        }
        
        if ( isset( $this->_contactID ) ) {
            $params['cid'] = $this->_contactID;
        }
        
		//	$this->_params['ip_address'] = CRM_Utils_System::ipAddress( );
		
        $result = CRM_Campaign_BAO_Petition::createSignature( $params );
        
        if ( $result ) {
            $statusMsg = ts( 'Petition signature has been saved. ' );
            $session = CRM_Core_Session::singleton();
            CRM_Core_Session::setStatus( $statusMsg );
            $session->pushUserContext(CRM_Utils_System::url('civicrm/dashboard', 'reset=1'));
        }        
    }   
    
    /**  
     * Function to add the custom profiles
     *  
     * @return None  
     * @access public  
     */ 
    function buildCustom( $id, $name, $viewOnly = false ) 
    {

        if ( $id ) {
            require_once 'CRM/Core/BAO/UFGroup.php';
            require_once 'CRM/Profile/Form.php';
            $session = CRM_Core_Session::singleton( );
            $contactID = $this->_contactID;	            

            $fields = null;
            if ( $contactID ) {
                require_once "CRM/Core/BAO/UFGroup.php";
                if ( CRM_Core_BAO_UFGroup::filterUFGroups($id, $contactID)  ) {
                    $fields = CRM_Core_BAO_UFGroup::getFields( $id, false,CRM_Core_Action::ADD );
                }
            } else {
                $fields = CRM_Core_BAO_UFGroup::getFields( $id, false,CRM_Core_Action::ADD ); 
            }

            if ( $fields ) {
                // unset any email-* fields since we already collect it, CRM-2888
                foreach ( array_keys( $fields ) as $fieldName ) {
                    if ( substr( $fieldName, 0, 6 ) == 'email-' ) {
                        unset( $fields[$fieldName] );
                    }
                }
                               
                $this->assign( $name, $fields );
                
                $addCaptcha = false;
                foreach($fields as $key => $field) {
                    if ( $viewOnly &&
                         isset( $field['data_type'] ) &&
                         $field['data_type'] == 'File' || ( $viewOnly && $field['name'] == 'image_URL' ) ) {
                        // ignore file upload fields
                        continue;
                    }

                
                    CRM_Core_BAO_UFGroup::buildProfile($this, $field, CRM_Profile_Form::MODE_CREATE, $contactID, true );
                    $this->_fields[$key] = $field;
                    if ( $field['add_captcha'] ) {
                        $addCaptcha = true;
                    }
                }

                if ( $addCaptcha &&
                     ! $viewOnly ) {
                    require_once 'CRM/Utils/ReCAPTCHA.php';
                    $captcha =& CRM_Utils_ReCAPTCHA::singleton( );
                    $captcha->add( $this );
                    $this->assign( "isCaptcha" , true );
                }                
                
            }
        }
    }
        
    
    
}
    
?>