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
class CRM_Campaign_Form_Task_ReleaseVoters extends CRM_Campaign_Form_Task {

    /**
     * survet id
     *
     * @var int
     */
    protected $_surveyId;
    
    /**
     * number of voters
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
    
    protected $_surveyActivities;
    
    /**
     * build all the data structures needed to build the form
     *
     * @return void
     * @access public
     */
    function preProcess( ) {
        parent::preProcess( );

        require_once 'CRM/Core/PseudoConstant.php';
        
        //get the survey id from user submitted values.
        $this->_surveyId      = CRM_Utils_Array::value( 'campaign_survey_id',    $this->get( 'formValues' ) );
        $this->_interviewerId = CRM_Utils_Array::value( 'survey_interviewer_id', $this->get( 'formValues' ) );
        
        $activityStatus = CRM_Core_PseudoConstant::activityStatus( 'name' );
        $surveyActType  = CRM_Campaign_BAO_Survey::getSurveyActivityType( );
        
        if ( !$this->_surveyId ) {
            CRM_Core_Error::statusBounce( ts( "Please search with 'Survey', to apply this action.") );
        }
        if ( !$this->_interviewerId ) {
            CRM_Core_Error::statusBounce( ts( 'Missing Interviewer contact.' ) );
        }
        if ( !is_array( $this->_contactIds ) || empty( $this->_contactIds ) ) {
            CRM_Core_Error::statusBounce( ts( 'Could not find contacts for release voters resevation.') );
        }
        
        $surveyDetails = array( );
        $params        = array( 'id' => $this->_surveyId );
        $this->_surveyDetails = CRM_Campaign_BAO_Survey::retrieve( $params, $surveyDetails );
        
        require_once 'CRM/Core/PseudoConstant.php';
        $activityStatus = CRM_Core_PseudoConstant::activityStatus( 'name' );
        $statusIds = array( );
        foreach ( array( 'Scheduled', 'Completed' ) as $name ) {
            if ( $statusId = array_search( $name, $activityStatus ) ) $statusIds[] = $statusId; 
        }
        //fetch the target survey activities.
        $this->_surveyActivities = CRM_Campaign_BAO_Survey::getSurveyActivities( $this->_surveyId, 
                                                                                 $this->_interviewerId,
                                                                                 $statusIds );
        if ( count( $this->_surveyActivities ) < 1 ) {
            CRM_Core_Error::statusBounce( ts( 'We could not found voter for this survey to release.') );
        }
        
        $this->assign( 'surveyTitle', $surveyDetails['title'] );
    }

    /**
     * Build the form
     *
     * @access public
     * @return void
     */
    function buildQuickForm( ) {
       
        $this->addDefaultButtons( ts('Release Voters'), 'done' );
    }

    function addRules( )
    {
        $this->addFormRule( array( 'CRM_Campaign_Form_Task_ReleaseVoters', 'formRule'), $this );
    }
    
    static function formRule( $params, $rules, &$form ) {
        $errors = array();
        return $errors;
    }

    function postProcess( ) {
        //get the submitted values in an array
        $params    = $this->controller->exportValues( $this->_name );
        
        require_once 'CRM/Core/PseudoConstant.php';

        $heldContacts   = array( );
        $activityStatus = CRM_Core_PseudoConstant::activityStatus( 'name' );
        $surveyActType  = CRM_Campaign_BAO_Survey::getSurveyActivityType( );
        
        // Interviewer can release only those contacts which are
        // held (is_deleted != 1) by himself
        $query = "SELECT DISTINCT(target.activity_id) as activity_id FROM civicrm_activity_target target INNER JOIN civicrm_activity source ON( target.activity_id = source.id ) INNER JOIN civicrm_activity_assignment assignment ON ( assignment.activity_id = source.id ) WHERE source.status_id IN (". implode( ',',  array_keys($activityStatus) ) .") AND source.activity_type_id IN(". implode( ',', array_keys($surveyActType) ) .") AND source.source_record_id = %1  AND (source.is_deleted = 0 OR source.is_deleted IS NULL) AND assignment.assignee_contact_id = %2 AND target.target_contact_id IN (". implode(',', $this->_contactIds) .") ";

        $findHeld = CRM_Core_DAO::executeQuery( $query, array( 1 => array( $this->_surveyId, 'Integer'), 2 => array( $this->_interviewerId, 'Integer') ) );
        
        while( $findHeld->fetch() ) {
            $heldContacts[$findHeld->activity_id] = $findHeld->activity_id; 
        }

        if ( !empty($heldContacts) ) {
            $query = "UPDATE civicrm_activity source INNER JOIN civicrm_activity_assignment assignment ON (source.id = assignment.activity_id ) SET source.is_deleted = 1 WHERE source.source_record_id = %1 AND assignment.assignee_contact_id = %2 AND source.id IN (". implode(',', $heldContacts ) .")";
            CRM_Core_DAO::executeQuery( $query, array( 1 => array( $this->_surveyId, 'Integer'), 2 => array( $this->_interviewerId, 'Integer') ) );
        }
        
        $status = array( );
        if ( count($heldContacts) > 0 ) {
            $status[ ] = ts("%1 voters has been released.", array( 1 => count($heldContacts) ) );
        }
        if ( count($this->_contactIds) > count($heldContacts) ) {
            $status[ ] = ts("%1 voters did not release.", array( 1 => (count($this->_contactIds) - count($heldContacts)) ) );  
        }
        
        if ( !empty($status) ) {
            CRM_Core_Session::setStatus( implode('&nbsp;', $status) );
        } 
    }

}