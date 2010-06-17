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

require_once 'CRM/Contact/Form/Task.php';
require_once 'CRM/Campaign/BAO/Survey.php';

/**
 * This class provides the functionality to add contacts for
 * voter reservation.
 */
class CRM_Contact_Form_Task_VoterReservation extends CRM_Contact_Form_Task {

    /**
     * custom data table
     *
     */
    CONST
        ACTIVITY_SURVEY_DETAIL_TABLE = 'civicrm_value_survey_activity_details';
    
    /**
     * build all the data structures needed to build the form
     *
     * @return void
     * @access public
     */
    function preProcess( ) {
        parent::preProcess( );
        $session = CRM_Core_Session::singleton( );
        
        if ( empty($this->_contactIds) || !($session->get('userID')) ) {
            CRM_Core_Error::statusBounce( ts( "Could not find contacts for voter reservation Or Missing Interviewer contact.") );
        }
    }

    /**
     * Build the form
     *
     * @access public
     * @return void
     */
    function buildQuickForm( ) {
        // survey list
        $surveys = CRM_Campaign_BAO_Survey::getSurveyList( );
        $this->add('select', 'survey_id', ts('Select Survey'), array( '' => ts('- select -') ) + $surveys , true );

        $this->addDefaultButtons( ts('Add Voter Reservation') );
    }

    function addRules( )
    {
        $this->addFormRule( array( 'CRM_Contact_Form_Task_VoterReservation', 'formRule' ) );
    }
    
    static function formRule( $form, $rule) {
        $errors = array();
        return $errors;
    }
    /**
     * process the form after the input has been submitted and validated
     *
     * @access public
     * @return None
     */
    public function postProcess( ) {
        //get the submitted values in an array
        $params  = $this->controller->exportValues( $this->_name );
        $session = CRM_Core_Session::singleton( );
        
        require_once 'CRM/Activity/BAO/Activity.php';
        require_once 'CRM/Contact/BAO/Contact.php';
        require_once 'CRM/Core/BAO/CustomField.php';
        require_once 'CRM/Core/BAO/CustomGroup.php';
        require_once 'CRM/Core/BAO/CustomValueTable.php';
     
        $this->_groupId = CRM_Core_DAO::getFieldValue( 'CRM_Core_DAO_CustomGroup', 
                                                       'civicrm_value_survey_activity_details' , 'id', 'table_name' );

        $groupTree = CRM_Core_BAO_CustomGroup::getTree( 'Activity',
                                                        $this,
                                                        null,
                                                        $this->_groupId );
        
        $activityGroupTree = CRM_Core_BAO_CustomGroup::formatGroupTree( $groupTree, 1, $this );

        $fieldMapper = array( );
        foreach( $activityGroupTree[$this->_groupId]['fields'] as $fieldId => $field ) {
            $fieldMapper[$field['column_name']] = $field['element_name'];
        }
        
        $customFields = CRM_Core_BAO_CustomField::getFields( 'Activity' );
       
        $surveyParams  = array( 'id' => $params['survey_id'] );
        $default       = array( );
        $surveyDeatils = CRM_Campaign_BAO_Survey::retrieve( $surveyParams, $default );
        $contactId     = $session->get( 'userID' );

        list( $cName, $cEmail, $doNotEmail, $onHold, $isDeceased ) = CRM_Contact_BAO_Contact::getContactDetails( $contactId );

        $fieldParams[$fieldMapper['survey_id']]                = $params['survey_id'];
        $fieldParams[$fieldMapper['status_id']]                = 'H';
        $fieldParams[$fieldMapper['interviewer_id']]           = $contactId;
        $fieldParams[$fieldMapper['interviewer_display_name']] = $cName;
        $fieldParams[$fieldMapper['interviewer_email']]        = $cEmail;
        $fieldParams[$fieldMapper['interviewer_ip']]           = $_SERVER['REMOTE_ADDR'];

        foreach ( $this->_contactIds as $cid ) {
            $activityParams = array( );  

            $activityParams['source_contact_id']   = $contactId;
            $activityParams['assignee_contact_id'] = array( $contactId );
            $activityParams['target_contact_id']   = array( $cid );
            $activityParams['activity_type_id' ]   = $surveyDeatils->activity_type_id;
            $activityParams['subject']             = ts('Voter Reservation');
            $activityParams['status_id']           = 1;        

            $result = CRM_Activity_BAO_Activity::create( $activityParams );

            $fieldParams[$fieldMapper['subject_display_name']] = CRM_Contact_BAO_Contact::displayName( $cid );
            
            if ( $result ) {
                CRM_Core_BAO_CustomValueTable::postProcess( $fieldParams,
                                                            $customFields,
                                                            'civicrm_activity',
                                                            $result->id,
                                                            'Activity' );
                
            }
        }
        
        CRM_Core_Session::setStatus( ts('Voter Reservation has been added for %1 Contacts.', array( 1 => count($this->_contactIds) ) ) );
    }
}


