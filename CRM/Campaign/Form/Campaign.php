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
        $this->_action = CRM_Utils_Request::retrieve('action', 'String', $this );
        $this->_campaignId = CRM_Utils_Request::retrieve('cmpid', 'Positive', $this );
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
        // if we are editing....
        if ( isset( $this->_campaignId ) ) {
            $params = array( 'id' => $this->_campaignId );
            require_once 'CRM/Campaign/BAO/Campaign.php';
            CRM_Campaign_BAO_Campaign::retrieve( $params, $defaults );
        }
        
        if ( isset( $defaults['start_date'] ) ) { 
            list( $defaults['start_date'], 
                  $defaults['start_date_time'] ) = CRM_Utils_Date::setDateDefaults( $defaults['start_date'], 'start_date' );
        } else {
            list( $defaults['start_date'], 
                  $defaults['start_date_time'] ) = CRM_Utils_Date::setDateDefaults( );
        }
        
        if ( isset( $defaults['end_date'] ) ) { 
            list( $defaults['end_date'], 
                  $defaults['end_date_time'] ) = CRM_Utils_Date::setDateDefaults( $defaults['end_date'], 'end_date' );
        }
        
        if ( !isset( $defaults['is_active'] ) ) {
            $defaults['is_active'] = 1;
        }
       
        return $defaults;
    }
    
    public function buildQuickForm()
    {
        $this->applyFilter('__ALL__','trim');
        $attributes = CRM_Core_DAO::getAttribute('CRM_Campaign_DAO_Campaign');
       
        // add comaign title.
        $this->addElement('text', 'name', ts('Campaign Name'), $attributes['name'] );
        $this->addRule('name', ts('Campaign Name is required field') , 'required');
        
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
            foreach ( $type as $name => $label) {
                $types[] = HTML_QuickForm::createElement('advcheckbox', $name, null, $label );
            }
            $this->addGroup( $types, 'campaign_type_id', ts('Campaign Type'), '&nbsp;');
        }
        
        // add campaign status
        $status = array(
                        1 => "Status 1",
                        2 => "Status 2"
                        );
        if ( $status ) {
            $this->addElement('select', 'status_id', ts('Compaign Status'), array('' => ts( '- select -' )) + $status );
           
        }
        // add External Identifire Element
        $this->add('text', 'external_identifier', ts('External Id'), 
                   CRM_Core_DAO::getAttribute('CRM_Campaign_DAO_Campaign', 'external_identifier'), false);
        
        // add Campaign Parent Id
        require_once 'CRM/Campaign/BAO/Campaign.php';
        $campaigns = CRM_Campaign_BAO_Campaign::getAllCampaign( $this->_campaignId );
        
        if ( $campaigns ) {
            $this->addElement('select', 'campaign_id', ts('Parent Id'), 
                              array('' => ts( '- select Parent -' )) + $campaigns );
        }
        
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
                                 array ( 'type'      => 'cancel',
                                         'name'      => ts('Cancel') ) ) 
                           );
        
        
    
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
     * Form submission of new/edit campaign is processed.
     *
     * @access public
     * @return None
     */
    public function postProcess() 
    {
        // store the submitted values in an array
        $params = $this->controller->exportValues( $this->_name );
         
        // format params
        $params['start_date'] = CRM_Utils_Date::processDate( $params['start_date'], $params['start_date_time'] );
        $params['end_date'  ] = CRM_Utils_Date::processDate( $params['end_date'], $params['end_date_time'], true );
        $params['is_active' ] = CRM_Utils_Array::value('is_active', $params, false);
        
        if ( $this->_action & CRM_Core_Action::ADD ) { 
            $session = CRM_Core_Session::singleton( );
            $params['created_id']   = $session->get( 'userID' );
            $params['created_date'] = date('YmdHis');
        } 
        
        if ( isset( $this->_campaignId ) ) {
            $params['id'] = $this->_campaignId;
        }
        
        require_once 'CRM/Campaign/BAO/Campaign.php';
        $result = CRM_Campaign_BAO_Campaign::create( $params );
        
        if ( $result ) {
            $statusMsg = ts( 'New Campaign '.$result->name.' has been saved succesfully' );
            $session = CRM_Core_Session::singleton();
            $session->pushUserContext(CRM_Utils_System::url('civicrm/dashboard', 'reset=1'));
            CRM_Core_Session::setStatus( $statusMsg );
        }
        
        $buttonName = $this->controller->getButtonName( );
        $session = CRM_Core_Session::singleton( );
        if ( $buttonName == $this->getButtonName( 'next', 'new' ) ) {
            CRM_Core_Session::setStatus(ts(' You can add another Campaign.'));
            $session->replaceUserContext( CRM_Utils_System::url('civicrm/campaign/add', 'reset=1&action=add' ) );
            
        }
    }    
}
    
?>