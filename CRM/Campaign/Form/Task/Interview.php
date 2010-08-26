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

require_once 'CRM/Campaign/Form/Task.php';

/**
 * This class provides the functionality to record voter's interview.
 */
class CRM_Campaign_Form_Task_Interview extends CRM_Campaign_Form_Task {
    
    /**
     * the title of the group
     *
     * @var string
     */
    protected $_title;
    
    /**
     * variable to store redirect path
     *
     */
    private $_userContext;
    
    private $_groupTree;
    
    private $_surveyFields;
    
    private $_surveyTypeId;
    
    private $_interviewerId;
    
    private $_ufGroupId;
    
    private $_surveyActivityIds;
    
    private $_votingTab = false;
    
    /**
     * build all the data structures needed to build the form
     *
     * @return void
     * @access public
     */
    function preProcess( ) 
    {
        $this->_votingTab          = $this->get( 'votingTab' );
        $this->_reserveToInterview = $this->get( 'reserveToInterview' );
        if ( $this->_votingTab ) {
            //time being hack.
            $this->_surveyId      = 1;
            $this->_contactIds    = array( 102 );
            $this->_interviewerId = 102;
        } else if ( $this->_reserveToInterview ) {
            //user came from reserve form.
            foreach ( array( 'surveyId', 'contactIds', 'interviewerId' ) as $fld ) {
                $this->{"_$fld"} = $this->get( $fld ); 
            }
        } else {
            parent::preProcess( );  
            //get the survey id from user submitted values.
            $this->_surveyId      = CRM_Utils_Array::value( 'campaign_survey_id',    $this->get( 'formValues' ) );
            $this->_interviewerId = CRM_Utils_Array::value( 'survey_interviewer_id', $this->get( 'formValues' ) );
        }
        
        //get the contact read only fields to display.
        require_once 'CRM/Core/BAO/Preferences.php';
        $readOnlyFields = array_merge( array( 'sort_name' => ts( 'Name' ) ),
                                       CRM_Core_BAO_Preferences::valueOptions( 'contact_autocomplete_options',
                                                                               true, null, false, 'name', true ) );
        //get the read only field data.
        $returnProperties  = array_fill_keys( array_keys( $readOnlyFields ), 1 );
        
        //get the profile id.
        require_once 'CRM/Core/BAO/UFJoin.php'; 
        $ufJoinParams = array( 'entity_id'    => $this->_surveyId,
                               'entity_table' => 'civicrm_survey',   
                               'module'       => 'CiviCampaign' );
        $this->_ufGroupId = CRM_Core_BAO_UFJoin::findUFGroupId( $ufJoinParams );
        
        //validate all voters for required activity.
        //get the survey activities for given voters.
        require_once 'CRM/Campaign/BAO/Survey.php';
        $this->_surveyActivityIds = CRM_Campaign_BAO_Survey::voterActivityDetails( $this->_surveyId, 
                                                                                   $this->_contactIds,
                                                                                   $this->_interviewerId );
        
        require_once 'CRM/Core/PseudoConstant.php';
        $activityStatus    = CRM_Core_PseudoConstant::activityStatus( 'name' );
        $scheduledStatusId = array_search( 'Scheduled', $activityStatus );
        
        $activityIds = array( );
        foreach ( $this->_contactIds as $key => $voterId ) {
            $actVals    = CRM_Utils_Array::value( $voterId, $this->_surveyActivityIds );
            $statusId   = CRM_Utils_Array::value( 'status_id',   $actVals );
            $activityId = CRM_Utils_Array::value( 'activity_id', $actVals );
            if ( $activityId && 
                 $statusId &&
                 $scheduledStatusId == $statusId ) {
                $activityIds["activity_id_{$voterId}"] = $activityId;
            } else {
                unset( $this->_contactIds[$key] ); 
            }
        }
        
        //retrieve the contact details.
        $voterDetails = CRM_Campaign_BAO_Survey::voterDetails( $this->_contactIds, $returnProperties );
        
        $this->assign( 'votingTab',      $this->_votingTab );
        $this->assign( 'voterIds',       $this->_contactIds );
        $this->assign( 'voterDetails',   $voterDetails );
        $this->assign( 'readOnlyFields', $readOnlyFields );
        $this->assign( 'interviewerId',  $this->_interviewerId );
        $this->assign( 'surveyActivityIds', json_encode( $activityIds ) );
        
        //validate the required ids.
        $this->validateIds( );
        
        //set the title.
        $this->_surveyTypeId = CRM_Core_DAO::getFieldValue( 'CRM_Campaign_DAO_Survey', 
                                                            $this->_surveyId, 
                                                            'activity_type_id' );
        require_once 'CRM/Core/PseudoConstant.php';
        $activityTypes = CRM_Core_PseudoConstant::activityType( );
        CRM_Utils_System::setTitle( ts( 'Record %1 Responses', array( 1 => $activityTypes[$this->_surveyTypeId] ) ) );
    }
    
    function validateIds( ) 
    {
        $required = array( 'surveyId'      => ts( 'Could not find Survey.'),
                           'interviewerId' => ts( 'Could not find Interviewer.' ),
                           'contactIds'    => ts( 'Could not find valid activities for selected voters.') );
        
        $errorMessages = array( );
        foreach ( $required as $fld => $msg ) {
            if ( empty( $this->{"_$fld"} ) ) {
                if ( !$this->_votingTab ) {
                    CRM_Core_Error::statusBounce( $msg );
                    break;
                }
                $errorMessages[] = $msg;
            }
        }
        
        $this->assign( 'errorMessages', $errorMessages );
    }
    
    /**
     * Build the form
     *
     * @access public
     * @return void
     */
    function buildQuickForm( ) 
    {
        
        
        $this->assign( 'surveyTypeId', $this->_surveyTypeId );
        
        $resultOptions = array( );
        $resuldId      = CRM_Core_DAO::getFieldValue( 'CRM_Campaign_DAO_Survey', 
                                                      $this->_surveyId, 
                                                      'result_id' ); 
        
        if ( $resuldId ) {
            $resultOptions = CRM_Core_OptionGroup::valuesByID( $resuldId );
            $resultOptions = array_combine( $resultOptions, $resultOptions );
        }

        //pickup the uf fields.
        $this->_surveyFields = array( );
        if ( $this->_ufGroupId ) {
            require_once 'CRM/Core/BAO/UFGroup.php';
            $this->_surveyFields = CRM_Core_BAO_UFGroup::getFields( $this->_ufGroupId, 
                                                                    false, CRM_Core_Action::VIEW );
        }
        
        if ( empty($resultOptions) ) {
            CRM_Core_Error::statusBounce( ts( 'Oops, It looks like there is no result field or profile configured to conduct voter interview.' ) );
        }
        
        $exposedSurveyFields = array( );

        //build all fields.
        foreach ( $this->_contactIds as $contactId ) {
            //build the profile fields.
            foreach ( $this->_surveyFields as $name => $field ) {
                if ( $customFieldID = CRM_Core_BAO_CustomField::getKeyID( $name ) ) {
                    $customValue = CRM_Utils_Array::value( $customFieldID, $customFields );
                    // allow custom fields from profile which are having
                    // the activty type same of that selected survey.
                    if ( ( $this->_surveyTypeId == $customValue['extends_entity_column_value'] ) ||
                         CRM_Utils_System::isNull( $customValue['extends_entity_column_value'] ) ) {
                        CRM_Core_BAO_UFGroup::buildProfile( $this, $field, null, $contactId );
                        $exposedSurveyFields[$name] = $field;
                    }
                } 
            }
            
            //build the result field.
            $this->add( 'select', "field[$contactId][result]", ts('Result'), 
                        array( '' => ts('- select -') ) + $resultOptions );
            
            $this->add( 'text', "field[{$contactId}][note]", ts('Note') );
            
        }
        $this->assign( 'surveyFields', $exposedSurveyFields );
        
        //no need to get qf buttons.
        if ( $this->_votingTab ) return;  
        
        $buttons = array( array ( 'type'      => 'cancel',
                                  'name'      => ts('Done'),
                                  'subName'   => 'interview',
                                  'isDefault' => true   ) );
        
        $manageCampaign = CRM_Core_Permission::check( 'manage campaign' );
        $adminCampaign  = CRM_Core_Permission::check( 'administer CiviCampaign' );
        if ( $manageCampaign ||
             $adminCampaign  ||
             CRM_Core_Permission::check( 'release campaign contacts' ) ) { 
            $buttons[] = array ( 'type'      => 'next',
                                 'name'      => ts('Release Voters >>'),
                                 'subName'   => 'interviewToRelease' );
        }
        if ( $manageCampaign ||
             $adminCampaign  ||
             CRM_Core_Permission::check( 'reserve campaign contacts' ) ) { 
            $buttons[] = array ( 'type'      => 'done',
                                 'name'      => ts('Reserve More Voters >>'),
                                 'subName'   => 'interviewToReserve' );
        }
        
        $this->addButtons( $buttons );
        
    }
    
    /**
     * This function sets the default values for the form.
     * 
     * @access public
     * @return None
     */
    function setDefaultValues( ) 
    {
        return $defaults = array( );
    }
    
    /**
     * process the form after the input has been submitted and validated
     *
     * @access public
     * @return None
     */
    public function postProcess( ) 
    {
        $buttonName = $this->controller->getButtonName( );
        if ( $buttonName == '_qf_Interview_done_interviewToReserve' ) {
            //hey its time to stop cycle.
            CRM_Utils_System::redirect( CRM_Utils_System::url( 'civicrm/survey/search', 'reset=1&op=reserve' ) );
        } else if ( $buttonName == '_qf_Interview_done_interviewToRelease' ) {
            //get ready to jump to release form.
            foreach ( array( 'surveyId', 'contactIds', 'interviewerId' ) as $fld ) {
                $this->controller->set( $fld, $this->{"_$fld"} ); 
            }
            $this->controller->set( 'interviewToRelease', true );
        }
        
        // vote is done through ajax
        return;
        
        $params = $this->controller->exportValues( $this->_name );
        
        //process survey.
        require_once 'CRM/Activity/BAO/Activity.php';
        foreach ( $params['field'] as $voterId => &$values ) {
            $values['voter_id']         = $voterId;
            $values['interviewer_id']   = $this->_interviewerId;
            $values['activity_type_id'] = $this->_surveyTypeId;
            $values['activity_id']      = CRM_Utils_Array::value( 'activity_id', $this->_surveyActivityIds[$voterId] );
            self::registerInterview( $values );
        }
        
    }
    
    static function registerInterview( $params )
    {
        $activityId   = CRM_Utils_Array::value( 'activity_id',      $params );
        $surveyTypeId = CRM_Utils_Array::value( 'activity_type_id', $params );
        if ( !is_array( $params ) || !$surveyTypeId || !$activityId ) {
            return false;
        }
        
        static $surveyFields;
        if ( !is_array( $surveyFields ) ) {
            require_once 'CRM/Core/BAO/CustomField.php';
            $surveyFields = CRM_Core_BAO_CustomField::getFields( 'Activity', 
                                                                 false, 
                                                                 false,
                                                                 $surveyTypeId,
                                                                 null,
                                                                 false, 
                                                                 true );
        }
        
        static $statusId;
        if ( !$statusId ) { 
            require_once 'CRM/Core/PseudoConstant.php';
            $statusId = array_search( 'Completed', CRM_Core_PseudoConstant::activityStatus( 'name' ) );
        }
        
        //format custom fields.
        $customParams = CRM_Core_BAO_CustomField::postProcess( $params,
                                                               $surveyFields,
                                                               $activityId,
                                                               'Activity' );
        require_once 'CRM/Core/BAO/CustomValueTable.php';
        CRM_Core_BAO_CustomValueTable::store( $customParams, 'civicrm_activity', $activityId );
        
        //update activity record.
        require_once 'CRM/Activity/DAO/Activity.php';
        $activity = new CRM_Activity_DAO_Activity( );
        $activity->id = $activityId;
        
        $activity->selectAdd( );
        $activity->selectAdd( 'activity_date_time, status_id, result, subject' ); 
        $activity->find( true );
        $activity->activity_date_time = date( 'Ymdhis' );
        $activity->status_id = $statusId;
        if ( CRM_Utils_Array::value( 'details', $params ) ) {
            $activity->details = $params['details'];
        }
        if ( $result = CRM_Utils_Array::value( 'result', $params ) ) {
            $activity->result = $result;
        }
        $activity->subject = CRM_Utils_Array::value( 'subject', $params, ts('Voter Interview') );
        $activity->save( );
        $activity->free( );
        
        return $activityId; 
    }

}

