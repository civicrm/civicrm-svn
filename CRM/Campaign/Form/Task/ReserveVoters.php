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

require_once 'CRM/Campaign/Form/Task.php';
require_once 'CRM/Campaign/BAO/Survey.php';

/**
 * This class provides the functionality to add contacts for
 * voter reservation.
 */
class CRM_Campaign_Form_Task_ReserveVoters extends CRM_Campaign_Form_Task {

    /**
     * survet id`
     *
     * @var int
     */
    protected $_surveyId;
    
    /**
     * interviewer id
     *
     * @var int
     */
    protected $_interviewerId;

    /**
     * survey details
     *
     * @var object
     */
    protected $_surveyDetails;

    /**
     * number of voters
     *
     * @var int
     */
    protected $_numVoters;
   
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
        $this->_interviewerId = $session->get('userID');

    }

    /**
     * Build the form
     *
     * @access public
     * @return void
     */
    function buildQuickForm( ) {
       
        $surveys = CRM_Campaign_BAO_Survey::getSurveyList( );
        $this->add('select', 'survey_id', ts('Survey'), array( '' => ts('- select -') ) + $surveys, true );
        $this->addDefaultButtons( ts('Add Voter Reservation') );
    }

    function addRules( )
    {
        $this->addFormRule( array( 'CRM_Campaign_Form_Task_ReserveVoters', 'formRule'), $this );
    }
    
    static function formRule( $params, $rules, &$form ) {
        $errors = array();
        $surveyDetails = array( );
        
        if ( CRM_Utils_Array::value('survey_id', $params) )  {
            require_once 'CRM/Core/PseudoConstant.php';

            $activityStatus = CRM_Core_PseudoConstant::activityStatus( 'name' );
            $surveyActType  = CRM_Campaign_BAO_Survey::getSurveyActivityType( );

            $form->_surveyId = $params['survey_id'];
            $params          = array( 'id' => $form->_surveyId );
            $form->_surveyDetails = CRM_Campaign_BAO_Survey::retrieve($params, $surveyDetails);

            // held survey activities 
            // activities are considered as held if is_deleted != 1
            $query = "SELECT COUNT(*) FROM civicrm_activity WHERE activity_type_id IN(". implode( ',', array_keys($surveyActType) ) .") AND status_id IN (". implode( ',', array_keys($activityStatus) ) .") AND source_record_id = %1 AND is_deleted != 1";
            
            $numVoters = CRM_Core_DAO::singleValueQuery( $query, array( 1 => array( $form->_surveyId, 'Integer') ) );
            $form->_numVoters = isset($numVoters)? $numVoters : 0;
            
            if ( CRM_Utils_Array::value('max_number_of_contacts', $surveyDetails) &&
                 $form->_numVoters &&
                 ( $surveyDetails['max_number_of_contacts'] <= $form->_numVoters ) ) {
                $errors['survey_id'] = ts( "Voter Reservation is full for this survey." );
            } else if ( CRM_Utils_Array::value('default_number_of_contacts',$surveyDetails) ) {
                if ( count($form->_contactIds) > $surveyDetails['default_number_of_contacts'] ) {
                    $errors['survey_id'] = ts( "You can select maximum %1 contact(s) at a time for voter reservation of this survey.", array( 1 => $surveyDetails['default_number_of_contacts']) );
                }
            }
        }
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

        require_once 'CRM/Activity/BAO/Activity.php';
        require_once 'CRM/Core/PseudoConstant.php';

        $this->_surveyId   = CRM_Utils_Array::value( 'survey_id', $params);
        $duplicateContacts = array( );

        $activityStatus = CRM_Core_PseudoConstant::activityStatus( 'name' );
        $surveyActType  = CRM_Campaign_BAO_Survey::getSurveyActivityType( );

        // duplicate contacts: contact with survey activity status is Held
        $query = "SELECT DISTINCT(target.target_contact_id) as contact_id FROM civicrm_activity_target target INNER JOIN civicrm_activity source ON( target.activity_id = source.id ) WHERE source.status_id IN (". implode( ',',  array_keys($activityStatus) ) .") AND source.is_deleted != 1 AND source.activity_type_id IN(". implode( ',', array_keys($surveyActType) ) .") AND source.source_record_id = %1  AND target.target_contact_id IN (". implode(',', $this->_contactIds) .") ";
        $findDuplicate = CRM_Core_DAO::executeQuery( $query, array( 1 => array( $this->_surveyId, 'Integer') ) );
        
        while( $findDuplicate->fetch() ) {
            $duplicateContacts[$findDuplicate->contact_id] = $findDuplicate->contact_id; 
        }

        $surveyDetails = $this->_surveyDetails;
        $maxVoters     = $surveyDetails->max_number_of_contacts;

        $countVoters = 0;
        $statusHeld  = CRM_Utils_Array::value( 'Scheduled', array_flip($activityStatus) );
        
        foreach ( $this->_contactIds as $cid ) {
            if ($maxVoters && ($maxVoters <= ($this->_numVoters + $countVoters) ) ) {
                break;
            }
            if ( in_array($cid ,$duplicateContacts) ) {
                continue;
            }
            
            $countVoters++;
            $activityParams = array( 'source_contact_id'   => $this->_interviewerId,
                                     'assignee_contact_id' => array( $this->_interviewerId ),
                                     'target_contact_id'   => array( $cid ),
                                     'source_record_id'    => $this->_surveyId,
                                     'activity_type_id'    => $surveyDetails->activity_type_id,
                                     'subject'             => ts('Voter Reservation'),
                                     'activity_date_time'  => date('YmdHis'),
                                     'status_id'           => $statusHeld
                                     );  
            if ( $surveyDetails->campaign_id ) {
                $activityParams['campaign_id'] = $surveyDetails->campaign_id;
            }

            CRM_Activity_BAO_Activity::create( $activityParams );
        }
        
        $status = array( );
        if ( $countVoters > 0 ) {
            $status[] = ts('Voter Reservation has been added for %1 Contact(s).', array( 1 => $countVoters ));
        }
        if ( count($this->_contactIds) > $countVoters ) {
            $status[] = ts('Voter Reservation did not add for %1 Contact(s).', array( 1 => ( count($this->_contactIds) - $countVoters) ) );
        }
        if ( !empty($status) ) {
            CRM_Core_Session::setStatus( implode('&nbsp;', $status) );
        }
    }
}


