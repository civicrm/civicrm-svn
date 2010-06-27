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
    protected $_userContext;

    protected $_groupTree;
    
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
        $surveyTypeId = CRM_Core_DAO::getFieldValue( 'CRM_Campaign_DAO_Survey', 
                                                     $this->_surveyId, 
                                                     'survey_type_id' );
        //get custom group ids.
        $surveyCustomGroups = CRM_Campaign_BAO_Survey::getSurveyCustomGroups( array( $surveyTypeId ) );
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
                                                       $surveyTypeId );
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
        $surveyFields = array( );
        foreach ( $this->_groupTree as $grpId => $grpVals ) {
            if ( !is_array( $grpVals['fields'] ) ) continue;
            foreach ( $grpVals['fields'] as $fId => $fVals )  { 
                $surveyFields[$fId] = $fVals;
                
            }
        }
        
        require_once "CRM/Core/BAO/CustomField.php";
        foreach ( $this->_contactIds as $contactId ) {
            foreach ( $surveyFields as $fldId => &$field ) {
                $fieldId       = $field['id'];                 
                $elementName   = $field['element_name'];
                $fieldName     = "field[$contactId][$elementName]";
                CRM_Core_BAO_CustomField::addQuickFormElement( $this, $fieldName, $fieldId );
            }
        }
        $this->assign( 'surveyFields', $surveyFields );
        
        $this->addButtons( array(
                                 array ( 'type'      => 'submit',
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
    public function postProcess() 
    {
        $params = $this->controller->exportValues( $this->_name );
        
        CRM_Core_Error::debug( '$params', $params );
        exit( );
    }
}

