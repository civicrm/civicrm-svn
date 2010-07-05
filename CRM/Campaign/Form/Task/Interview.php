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
    
    private $_campaignId;
    
    /**
     * build all the data structures needed to build the form
     *
     * @return void
     * @access public
     */
    function preProcess( ) 
    {
        parent::preProcess( );
        
        //get the survey id from user submitted values.
        $this->_surveyId = CRM_Utils_Array::value( 'survey_id',$this->get( 'formValues' ) );
        
        $this->_surveyId = 1;
        if ( !$this->_surveyId ) {
            CRM_Core_Error::statusBounce( ts( "Could not find Survey Id.") );
        }
        $this->_campaignId = CRM_Core_DAO::getFieldValue( 'CRM_Campaign_DAO_Survey', $this->_surveyId, 'campaign_id' );
        
        $session = CRM_Core_Session::singleton( );
        $this->_interviewerId = $session->get('userID');
        
        //get the contact read only fields to display.
        require_once 'CRM/Core/BAO/Preferences.php';
        $readOnlyFields = array_merge( array( 'sort_name' => ts( 'Name' ) ),
                                       CRM_Core_BAO_Preferences::valueOptions( 'contact_autocomplete_options',
                                                                               true, null, false, 'name', true ) );
        //get the read only field data.
        $returnProperties  = array_fill_keys( array_keys( $readOnlyFields ), 1 );
        
        //retrieve the contact details.
        require_once 'CRM/Campaign/BAO/Survey.php';
        $voterDetails = CRM_Campaign_BAO_Survey::voterDetails( $this->_contactIds, $returnProperties );
        
        $this->assign( 'voterIds',       $this->_contactIds );
        $this->assign( 'voterDetails',   $voterDetails );
        $this->assign( 'readOnlyFields', $readOnlyFields );
        $this->assign( 'interviewerId',  $this->_interviewerId );
        $this->assign( 'campaignId',     $this->_campaignId );
    }
    
    /**
     * Build the form
     *
     * @access public
     * @return void
     */
    function buildQuickForm( ) 
    {
        //get the survey type id.
        $this->_surveyTypeId = CRM_Core_DAO::getFieldValue( 'CRM_Campaign_DAO_Survey', 
                                                            $this->_surveyId, 
                                                            'activity_type_id' );
        
        $this->assign( 'surveyTypeId', $this->_surveyTypeId );
        
        //get custom group ids.
        $surveyCustomGroups = CRM_Campaign_BAO_Survey::getSurveyCustomGroups( array( $this->_surveyTypeId ) );
        $customGrpIds = array_keys( $surveyCustomGroups );
        
        //build the group tree for given survey.
        $this->_groupTree = array( );
        require_once 'CRM/Core/BAO/CustomGroup.php';
        foreach ( $customGrpIds as $customGrpId ) {
            //get the tree
            $tree = CRM_Core_BAO_CustomGroup::getTree( 'Activity', 
                                                       CRM_Core_DAO::$_nullObject,
                                                       null,
                                                       $customGrpId,
                                                       $this->_surveyTypeId );
            //simplified formatted groupTree
            $tree = CRM_Core_BAO_CustomGroup::formatGroupTree( $tree, 
                                                               1, 
                                                               CRM_Core_DAO::$_nullObject );
            //build complete group tree.
            foreach ( $tree as $grpId => $values ) {
                $this->_groupTree[$grpId] = $values; 
            }
        }
        $this->assign( 'groupTree', $this->_groupTree );
        
        //get the fields.
        $this->_surveyFields = array( );
        foreach ( $this->_groupTree as $grpId => $grpVals ) {
            if ( !is_array( $grpVals['fields'] ) ) continue;
            foreach ( $grpVals['fields'] as $fId => $fVals )  { 
                $this->_surveyFields[$fId] = $fVals;
            }
        }
        
        require_once "CRM/Core/BAO/CustomField.php";
        foreach ( $this->_contactIds as $contactId ) {
            foreach ( $this->_surveyFields as $fldId => &$field ) {
                $fieldId       = $field['id'];                 
                $elementName   = $field['element_name'];
                $fieldName     = "field[$contactId][$elementName]";
                CRM_Core_BAO_CustomField::addQuickFormElement( $this, $fieldName, $fieldId, false, false );
            }
            
            //hack to get control for interview ids during ajax calls.
            $this->add( 'text', "field[$contactId][interview_id]" );
        }
        $this->assign( 'surveyFields', $this->_surveyFields );
        
        $this->addButtons( array(
                                 array ( 'type'      => 'next',
                                         'name'      => ts('Record Voters Interview'),
                                         'isDefault' => true   ),
                                 array ( 'type'      => 'cancel',
                                         'name'      => ts('Cancel') ),
                                 )
                           );
        
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
        $params = $this->controller->exportValues( $this->_name );
        
        //process survey.
        //1. create an activity w/ each contact
        //2. store custom data as activity custom data.
        
        require_once 'CRM/Activity/BAO/Activity.php';
        foreach ( $params['field'] as $voterId => &$values ) {
            $values['voter_id']         = $voterId;
            $values['campaign_id']      = $this->_campaignId;
            $values['interviewer_id']   = $this->_interviewerId;
            $values['activity_type_id'] = $this->_surveyTypeId;
            self::registerInterview( $values );
        }
        
    }

    static function registerInterview( $params )
    {
        $interviewId  = CRM_Utils_Array::value( 'interview_id',     $params );
        $surveyTypeId = CRM_Utils_Array::value( 'activity_type_id', $params );
        if ( !is_array( $params ) || !$surveyTypeId ) {
            return $interviewId;
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
        
        static $actTypeId;
        if ( !$actTypeId ) { 
            require_once 'CRM/Core/PseudoConstant.php';
            $actTypeId = array_search( 'Interview', CRM_Core_PseudoConstant::activityType( true, false, false, 'name') );
        }
        
        //create a activity record for given survey interview.
        $actParams = array( 'subject' => ts( 'Interview' ) );
        
        //carry id for update.
        if ( $interviewId ) $actParams['id'] = $interviewId;
        
        //format custom fields.
        $actParams['custom'] = CRM_Core_BAO_CustomField::postProcess( $params,
                                                                      $surveyFields,
                                                                      $interviewId,
                                                                      'Activity' );
        
        //create an activity record.
        $actParams['source_contact_id']  = $params['interviewer_id'];
        $actParams['target_contact_id']  = $params['voter_id'];
        $actParams['activity_type_id']   = $actTypeId;
        $actParams['activity_date_time'] = date( 'Ymdhis' );
        $actParams['campaign_id']        = $params['campaign_id'];
        
        //create an activity record.
        require_once 'CRM/Activity/BAO/Activity.php';
        $activity = CRM_Activity_BAO_Activity::create( $actParams );
        $interviewId = $activity->id; 
        
        return $interviewId; 
    }
    
}

