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
require_once 'CRM/Campaign/BAO/Survey.php';

/**
 * This class generates form components for processing a survey 
 * 
 */

class CRM_Campaign_Form_Survey extends CRM_Core_Form
{
    /**
     * The id of the object being edited
     *
     * @var int
     */
    protected $_surveyId;
    
    /**
     * action
     *
     * @var int
     */
    protected $_action;
    
    /**
     * Function to set variables up before form is built
     * 
     * @param null
     * 
     * @return void
     * @access public
     */
    public function preProcess()
    {
        $this->_action   = CRM_Utils_Request::retrieve('action', 'String', $this );
        
        if ( $this->_action & ( CRM_Core_Action::UPDATE | CRM_Core_Action::DELETE ) ) {
            $this->_surveyId = CRM_Utils_Request::retrieve('id', 'Positive', $this, true);
        }
        $this->assign( 'action', $this->_action );
    }
    
    /**
     * This function sets the default values for the form. Note that in edit/view mode
     * the default values are retrieved from the database
     * 
     * @param null
     * 
     * @return array    array of default values
     * @access public
     */
    function setDefaultValues()
    {
        $defaults = array();

        if ( $this->_surveyId ) {
            $params = array( 'id' => $this->_surveyId );
            CRM_Campaign_BAO_Survey::retrieve( $params, $defaults );
        }

        return $defaults;
    }

    /**
     * Function to actually build the form
     *
     * @param null
     * 
     * @return void
     * @access public
     */
    public function buildQuickForm()
    {

        if ( $this->_action & CRM_Core_Action::DELETE ) {
            
            $this->addButtons( array(
                                     array ( 'type'      => 'next',
                                             'name'      => ts('Delete'),
                                             'isDefault' => true   ),
                                     array ( 'type'      => 'cancel',
                                             'name'      => ts('Cancel') ),
                                     )
                               );
            return;
        }

        require_once 'CRM/Core/OptionGroup.php';
        require_once 'CRM/Event/PseudoConstant.php';
        
        // Survey Type id
        require_once 'CRM/Core/PseudoConstant.php';
        $surveyType = CRM_Core_PseudoConstant::surveyType();
        $this->add('select', 'survey_type_id', ts('Select Survey Type'),  array( '' => ts( '- select -' ) ) +$surveyType, true );
        
        // FIX ME : Add Activity Type Id for Survey
        //$activityTName = CRM_Core_OptionGroup::values( 'activity_type', false, false, false, 'AND v.value = '.$this->_activityTypeId , 'name' );
        $activityTypes = CRM_Core_OptionGroup::values( 'activity_type', false, false, false, false , 'name' );
        // Activity Type id
        $this->add('select', 'activity_type_id', ts('Select Activity Type'), $activityTypes, true );
        
        // Campaign id
        require_once 'CRM/Campaign/BAO/Campaign.php';
        $campaigns = CRM_Campaign_BAO_Campaign::getAllCampaign();
        $this->add('select', 'campaign_id', ts('Select Campaign'), array( '' => ts( '- select -' ) ) +$campaigns, true );
        
        // FIX ME : Add Custom Group Id for Survey
        // custom group id
        $this->add('select', 'custom_group_id', ts('Select Custom Group'), array( '1' => 'Mumbai' , '1' => 'Pune', '3' => 'Chennai', '4' => 'Goa'), true );
        
        // script / instructions
        $this->add( 'textarea', 'instructions', ts('Instructions for volunteers'), array( 'rows' => 5, 'cols' => 40 ) );
        
        // release frequency unit
        $this->add('select', 'release_frequency_unit', ts('Release Frequency Unit'), array( 'day' => 'Day' , 'week' => 'Week', 'month' => 'Month', 'year' => 'Year'), true );
        
        // release frequency interval
        $this->add('text', 'release_frequency_interval', ts('Release Frequency Interval'), CRM_Core_DAO::getAttribute('CRM_Campaign_DAO_Survey', 'release_frequency_interval'), true );

        $this->addRule('release_frequency_interval', ts('Frequenct interval should be a positive number') , 'positiveInteger');

        // max number of contacts
        $this->add('text', 'max_number_of_contacts', ts('Maximum number of contacts '), CRM_Core_DAO::getAttribute('CRM_Campaign_DAO_Survey', 'max_number_of_contacts') );

        $this->addRule('max_number_of_contacts', ts('Maximum number of contacts should be a positive number') , 'positiveInteger');
        
        // default number of contacts
        $this->add('text', 'default_number_of_contacts', ts('Default number of contacts'), CRM_Core_DAO::getAttribute('CRM_Campaign_DAO_Survey', 'default_number_of_contacts') );
        $this->addRule('default_number_of_contacts', ts('Default number of contacts should be a positive number') , 'positiveInteger');    
        
        // is active ?
        $this->add('checkbox', 'is_active', ts('Is Active?'));
        
        // is default ?
        $this->add('checkbox', 'is_default', ts('Is Default?'));

        // add buttons
        $this->addButtons(array(
                                array ('type'      => 'next',
                                       'name'      => ts('Save'),
                                       'isDefault' => true),
                                array ('type'      => 'next',
                                       'name'      => ts('Save and New'),
                                       'subName'   => 'new'),
                                array ('type'      => 'cancel',
                                       'name'      => ts('Cancel')),
                                )
                          ); 
        
        // add a form rule to check default value
        $this->addFormRule( array( 'CRM_Campaign_Form_Survey', 'formRule' ),$this );

    }
    
    /**
     * global validation rules for the form
     *
     */
    static function formRule( $fields, $files, $form ) {
        
        $errors = array( );
        
        return empty($errors) ? true : $errors;
    }   
    
    /**
     * Process the form
     * 
     * @param null
     * 
     * @return void
     * @access public
     */
    public function postProcess()
    {
        // store the submitted values in an array
        $params = $this->controller->exportValues( $this->_name );
        
        $session = CRM_Core_Session::singleton( );

        $params['last_modified_id'] = $session->get( 'userID' );
        $params['last_modified_date'] = date('YmdHis');
        
        if ( $this->_surveyId ) {

            if ( $this->_action & CRM_Core_Action::DELETE ) {
                CRM_Campaign_BAO_Survey::del( $this->_surveyId );
                CRM_Core_Session::setStatus(ts(' Survey has been deleted.'));
                $session->replaceUserContext( CRM_Utils_System::url('civicrm/survey/browse', 'reset=1' ) ); 
                return;
            }

            $params['id'] = $this->_surveyId;
        } else { 
            $params['created_id']   = $session->get( 'userID' );
            $params['created_date'] = date('YmdHis');
        } 

        $surveyId = CRM_Campaign_BAO_Survey::create( $params  );
        
        if( ! is_a( $surveyId, 'CRM_Core_Error' ) ) {
            CRM_Core_Session::setStatus(ts('Survey has been saved.'));
        }
        
        $buttonName = $this->controller->getButtonName( );
        $session = CRM_Core_Session::singleton( );
        if ( $buttonName == $this->getButtonName( 'next', 'new' ) ) {
            CRM_Core_Session::setStatus(ts(' You can add another Survey.'));
            $session->replaceUserContext( CRM_Utils_System::url('civicrm/survey/manage', 'reset=1&action=add' ) );
        } else {
            $session->replaceUserContext( CRM_Utils_System::url('civicrm/survey/browse', 'reset=1' ) ); 
        }
    }
 }


?>