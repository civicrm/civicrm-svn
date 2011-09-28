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

require_once 'CRM/Core/Form.php';
require_once 'CRM/Core/BAO/CMSUser.php';

/**
 * This class generates form components generic to useradd
 *
 */
class CRM_Contact_Form_Task_Useradd extends CRM_Core_Form
{
    /**
     * The contact id, used when adding user
     *
     * @var int
     */
    protected $_contactId;

    function preProcess( ) {
        $defaults = array( );
        
        $params   = array( );
        $defaults = array( );
        $ids      = array( );
        
        $this->_contactId = CRM_Utils_Request::retrieve( 'cid', 'Positive', $this, true );
        $params['id'] = $params['contact_id'] = $this->_contactId;
        $contact = CRM_Contact_BAO_Contact::retrieve( $params, $defaults, $ids );

        // set title to "Note - "+Contact Name    
        $displayName = $contact->display_name;
        $pageTitle = 'Create new User for Contact - '.$displayName;
        $this->assign( 'pageTitle', $pageTitle );
    }

    /**
     * This function sets the default values for the form. Note that in edit/view mode
     * the default values are retrieved from the database
     * 
     * @access public
     * @return None
     */
    function setDefaultValues( ) {
        $this->_contactId = CRM_Utils_Request::retrieve( 'cid', 'Positive', $this, true );
        $defaults = $params = $results = array( );
        $params['id'] = $params['contact_id'] = $this->_contactId;
        $contact = CRM_Contact_BAO_Contact::retrieve( $params, $results );

        
        $defaults['contactID'] = $this->_contactId;
        $defaults['name'] = $contact->display_name;
        if ( ! empty( $contact->email ) ) {
            $defaults['email'] = $contact->email[1]['email'];
        }

        return $defaults;
    }

    /**
     * Function to actually build the form
     *
     * @return None
     * @access public
     */
    public function buildQuickForm( ) {

        CRM_Utils_System::setTitle( ts('Create new User'));
       
        $this->add('text', 'name' , ts('Full Name:') , array('size' => 20, 'readonly' => true));
        $this->add('text', 'cms_name' , ts('Username:') , array('size' => 20));
        $this->addRule('cms_name','Enter a valid username','minlength',2);
        $this->add('password', 'cms_pass' , ts('Password:') , array('size' => 20));
        $this->add('password', 'cms_confirm_pass' , ts('Password confirm:') , array('size' => 20));
        $this->addRule(array('cms_pass','cms_confirm_pass'), 'ERROR: Password mismatch', 'compare');
        $this->add('text', 'email' , ts('Email:') , array('size' => 20, 'readonly' => true));
        
        $this->add('hidden', 'contactID');

        $this->addButtons( array(
                                 array ( 'type'      => 'next',
                                         'name'      => ts('Add'),
                                         'isDefault' => true   ),
                                 array ( 'type'       => 'cancel',
                                         'name'      => ts('Cancel') ),
                                 )
                           );
        $this->setDefaults( $this->setDefaultValues() );
    }
  
    /**
     *
     * @access public
     * @return None
     */
    public function postProcess( )
    {
        // store the submitted values in an array
        $params = $this->exportValues();

        
        CRM_Core_BAO_CMSUser::create($params, 'email');
        CRM_Core_Session::setStatus( ts('User has been added.') );
    
    }//end of function
}
