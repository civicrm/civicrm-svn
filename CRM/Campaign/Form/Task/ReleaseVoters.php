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
   
    /**
     * build all the data structures needed to build the form
     *
     * @return void
     * @access public
     */
    function preProcess( ) {
        parent::preProcess( );

        //get the survey id from user submitted values.
        $this->_surveyId = CRM_Utils_Array::value( 'survey_id', $this->get( 'formValues' ) );
        $isHeld          = CRM_Utils_Array::value( 'status_id', $this->get( 'formValues' ) );
        $surveyStatus    = CRM_Campaign_BAO_Survey::getSurveyActivityStatus( 'held' );
        $surveyActType   = CRM_Campaign_BAO_Survey::getSurveyActivityType( );

        if ( !$this->_surveyId || !$isHeld || !in_array( $isHeld, array_keys($surveyStatus) ) ) {
            CRM_Core_Error::statusBounce( ts( "Please search with 'Survey' filter and 'Survey Status' should be %1, to apply this action.", array( '%1' => implode( ' OR ', $surveyStatus ) ) ) );
        }
        
        $session = CRM_Core_Session::singleton( );

        if ( empty($this->_contactIds) || !($session->get('userID')) ) {
            CRM_Core_Error::statusBounce( ts( "Could not find contacts for release voters resevation Or Missing Interviewer contact.") );
        }
        $this->_interviewerId = $session->get('userID');

        $surveyDetails = array( );
        $params        = array( 'id' => $this->_surveyId );
        $this->_surveyDetails = CRM_Campaign_BAO_Survey::retrieve($params, $surveyDetails);

        // get held contacts by interviewer
        $query = "SELECT COUNT(*) FROM civicrm_activity source INNER JOIN civicrm_activity_assignment assignment ON ( assignment.activity_id = source.id ) WHERE source.activity_type_id IN(". implode( ',', array_keys($surveyActType) ) .") AND source.status_id IN (". implode( ',', array_keys($surveyStatus) ) .") AND source.source_record_id = %1 AND assignment.assignee_contact_id = %2";

        $numVoters = CRM_Core_DAO::singleValueQuery( $query, array( 1 => array( $this->_surveyId, 'Integer' ), 2 => array( $this->_interviewerId, 'Integer' ) ) );

        if ( !isset($numVoters) || ($numVoters < 1) ) {
            CRM_Core_Error::statusBounce( ts( "All voters held by you are already released for this survey.") );
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
       
        $this->addDefaultButtons( ts('Release Voters') );
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
        
        $heldContacts = array( );
        
        $surveyStatus    = CRM_Campaign_BAO_Survey::getSurveyActivityStatus( 'held' );
        $surveyActType   = CRM_Campaign_BAO_Survey::getSurveyActivityType( );
        
        // interviewer can release only those contacts which are held
        // by himself
        $query = "SELECT DISTINCT(target.activity_id) as activity_id FROM civicrm_activity_target target INNER JOIN civicrm_activity source ON( target.activity_id = source.id ) INNER JOIN civicrm_activity_assignment assignment ON ( assignment.activity_id = source.id ) WHERE source.status_id IN (". implode( ',',  array_keys($surveyStatus) ) .") AND source.activity_type_id IN(". implode( ',', array_keys($surveyActType) ) .") AND source.source_record_id = %1  AND assignment.assignee_contact_id = %2 AND target.target_contact_id IN (". implode(',', $this->_contactIds) .") ";

        $findHeld = CRM_Core_DAO::executeQuery( $query, array( 1 => array( $this->_surveyId, 'Integer'), 2 => array( $this->_interviewerId, 'Integer') ) );
        
        while( $findHeld->fetch() ) {
            $heldContacts[$findHeld->activity_id] = $findHeld->activity_id; 
        }

        $statusReleased =  CRM_Utils_Array::value( 'Completed', array_flip(CRM_Campaign_BAO_Survey::getSurveyActivityStatus('released')) );

        if ( !empty($heldContacts) ) {
            $query = "UPDATE civicrm_activity source INNER JOIN civicrm_activity_assignment assignment ON (source.id = assignment.activity_id ) SET source.status_id = %1 WHERE source.source_record_id = %2 AND assignment.assignee_contact_id = %3 AND source.id IN (". implode(',', $heldContacts ) .")";
            CRM_Core_DAO::executeQuery( $query, array( 1 => array( $statusReleased, 'Integer'), 2 => array( $this->_surveyId, 'Integer'), 3 => array( $this->_interviewerId, 'Integer') ) );
        }
        
        $status = array( );
        if ( count($heldContacts) > 0 ) {
            $status[ ] = ts("%1 voters has been released.", array( 1 => count($heldContacts) ) );
        }
        if ( count($this->_contactIds) > count($heldContacts) ) {
            $status[ ] = ts("%1 voters did not release.", array( 1 => (count($this->_contactIds) > count($heldContacts)) ) );  
        }
        
        if ( !empty($status) ) {
            CRM_Core_Session::setStatus( implode('&nbsp;', $status) );
        } 
    }

}