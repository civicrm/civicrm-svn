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

/**
 * This class generates form components for processing a campaign 
 * 
 */

class CRM_Campaign_Form_Campaign extends CRM_Core_Form
{
    
    public $_action;
    
    /**
     * the id of the campaign we are proceessing
     *
     * @var int
     * @protected
     */
    public $_campaignId;
    
    public function preProcess()
    {
        
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
       
    }

    public function buildQuickForm()
    {
        $this->applyFilter('__ALL__','trim');
        $attributes = CRM_Core_DAO::getAttribute('CRM_Campaign_DAO_Campaign');
       
        // add comaign title.
        $this->addElement('text', 'name', ts('Campaign Name'), $attributes['name'] );
        
        // add comaign title.
        $this->addElement('text', 'title', ts('Campaign Title'), $attributes['title'] );
        
        // add description
        $this->addElement('textarea', 'description', ts('Description'), $attributes['description'] );

        // add campaign start date
        $this->addDateTime('start_date', ts('Start Date'), true, array( 'formatType' => 'campaignDateTime') );
        
        // add campaign end date
        $this->addDateTime('end_date', ts('End Date'), false, array( 'formatType' => 'campaignDateTime') );

        // add campaign type
        $type = array(
                      1 => "Type1",
                      2 => "Type2"
                      );
        if ( $type ) {
            $this->addElement('select', 'campaign_type_id', ts('Compaign Type'), array('' => '') + $type );
           
        }
        
        // add campaign status
        $status = array(
                        1 => "Status 1",
                        2 => "Status 2"
                        );
        if ( $status ) {
            $this->addElement('select', 'status_id', ts('Compaign Status'), array('' => '') + $status );
           
        }
        // add External Identifire Element
        $this->add('text', 'external_identifier', ts('External Id'), 
                   CRM_Core_DAO::getAttribute('CRM_Campaign_DAO_Campaign', 'external_identifier'), false);
        
        // is this Campaign active
        $this->addElement('checkbox', 'is_active', ts('Is this Campaign Active?') );

        $this->addButtons( array(
                                 array ( 'type'      => 'upload',
                                         'name'      => ts('Save'),
                                         'subName'   => 'view',
                                         'isDefault' => true   ),
                                 array ( 'type'      => 'upload',
                                         'name'      => ts('Save and New'),
                                         'spacing'   => '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;',
                                         'subName'   => 'new' ),
                                 array ( 'type'       => 'cancel',
                                         'name'      => ts('Cancel') ) ) );
        
        
    
    }
    
    /**
     * This function is used to add the rules (mainly global rules) for form.
     * All local rules are added near the element
     *
     * @return None
     * @access public
     * @see valid_date
     */
    
    static function formRule( $fields, $errors, $contactId = null )
    {
    }

    /**
     * Form submission of new/edit campaign is processed.
     *
     * @access public
     * @return None
     */
    public function postProcess() 
    {
        $params = $this->controller->exportValues( $this->_name );
         
        require_once 'CRM/Campaign/BAO/Campaign.php';
        $result = CRM_Campaign_BAO_Campaign::create( $params );
        
        if ( $result ) {
            $statusMsg = ts( 'New Campaign '.$result->name.' has been saved succesfully' );
            $session = CRM_Core_Session::singleton();
            $session->pushUserContext(CRM_Utils_System::url('civicrm/dashboard', 'reset=1'));
            CRM_Core_Session::setStatus( $statusMsg );
        }
        
    }
    
}

?>