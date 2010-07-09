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
    
    private $_ufGroupId;
    
    private $_surveyActivityIds;
    
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
        
        //get the profile id.
        require_once 'CRM/Core/BAO/UFJoin.php'; 
        $ufJoinParams = array( 'entity_id'    => $this->_surveyId,
                               'entity_table' => 'civicrm_survey',   
                               'module'       => 'CiviCampaign' );
        $this->_ufGroupId = CRM_Core_BAO_UFJoin::findUFGroupId( $ufJoinParams );
        
        //retrieve the contact details.
        require_once 'CRM/Campaign/BAO/Survey.php';
        $voterDetails = CRM_Campaign_BAO_Survey::voterDetails( $this->_contactIds, $returnProperties );
        
        //get the survey activities for given voters.
        $this->_surveyActivityIds = CRM_Campaign_BAO_Survey::voterActivityDetails( $this->_surveyId, 
                                                                                   $this->_contactIds );
        
        $this->assign( 'voterIds',       $this->_contactIds );
        $this->assign( 'voterDetails',   $voterDetails );
        $this->assign( 'readOnlyFields', $readOnlyFields );
        $this->assign( 'interviewerId',  $this->_interviewerId );
        $this->assign( 'campaignId',     $this->_campaignId );
        
        $activityIds = array( );
        foreach ( $this->_surveyActivityIds as $voterId => $actId ) {
            $activityIds["activity_id_{$voterId}"] = $actId;
        }
        $this->assign( 'surveyActivityIds', json_encode( $activityIds ) );
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
        $allCustomFieldOptions = $allOptions = array( );
        require_once 'CRM/Core/BAO/CustomGroup.php';
        foreach ( $customGrpIds as $customGrpId ) {
            $groupDetails = CRM_Core_BAO_CustomGroup::getGroupDetail( $customGrpId );
            foreach ( $groupDetails as $grpId => $grpVals ) {
                if ( is_array( $grpVals['fields'] ) ) {
                    foreach ( $grpVals['fields'] as $fldId => $fldVals ) {
                        $optionGroupId = CRM_Utils_Array::value( 'option_group_id', $fldVals );
                        if ( !$optionGroupId ) continue;
                        $options = CRM_Core_BAO_CustomOption::getCustomOption( $fldId ); 
                        if ( !empty( $options ) ) {
                            $allCustomFieldOptions[$fldId] = $options;
                            foreach ( $options as $optId => $optVals ) {
                                $label = $optVals['label'];
                                $allOptions[$label] = $label;
                            }
                        }
                    }
                }
            }
        }
        
        $addResultField = false;
        if ( !CRM_Utils_System::isNull( $allOptions ) ) {
            $addResultField = true;
        }
        $this->assign( 'hasResultField', $addResultField );
        
        //pickup the uf fields.
        $this->_surveyFields = array( );
        if ( $this->_ufGroupId ) {
            require_once 'CRM/Core/BAO/UFGroup.php';
            $this->_surveyFields = CRM_Core_BAO_UFGroup::getFields( $this->_ufGroupId, 
                                                                    false, CRM_Core_Action::VIEW );
        }
        
        //build all fields.
        foreach ( $this->_contactIds as $contactId ) {
            //build the profile fields.
            foreach ( $this->_surveyFields as $name => $field ) {
                if ( $customFieldID = CRM_Core_BAO_CustomField::getKeyID( $name ) ) {
                    $customValue = CRM_Utils_Array::value( $customFieldID, $customFields );
                    if ( ( $this->_surveyTypeId == $customValue['extends_entity_column_value'] ) ||
                         CRM_Utils_System::isNull( $customValue['extends_entity_column_value'] ) ) {
                        CRM_Core_BAO_UFGroup::buildProfile( $this, $field, null, $contactId );
                    }
                } else {
                    // handle non custom fields
                    CRM_Core_BAO_UFGroup::buildProfile( $this, $field, null, $contactId );
                }
            }
            
            //build the result field.
            if ( $addResultField ) {
                $this->add( 'select', "field[$contactId][result]", ts('Result'), 
                            array( '' => ts('- select -') ) + $allOptions );
            }
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
        require_once 'CRM/Activity/BAO/Activity.php';
        foreach ( $params['field'] as $voterId => &$values ) {
            $values['voter_id']         = $voterId;
            $values['campaign_id']      = $this->_campaignId;
            $values['interviewer_id']   = $this->_interviewerId;
            $values['activity_type_id'] = $this->_surveyTypeId;
            $values['activity_id']      = CRM_Utils_Array::value( $voterId, $this->_surveyActivityIds );
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
        
        //update status of activity record to completed.
        CRM_Core_DAO::setFieldValue( 'CRM_Activity_DAO_Activity', $activityId, 'status_id', $statusId );
        
        //set the result.
        if ( $result = CRM_Utils_Array::value( 'result', $params ) ) {
            CRM_Core_DAO::setFieldValue( 'CRM_Activity_DAO_Activity', $activityId, 'result', $result );
        }
        
        return $activityId; 
    }

}

