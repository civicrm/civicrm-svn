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

/**
 * Files required
 */
require_once 'CRM/Core/Form.php';

class CRM_Campaign_Form_Gotv extends CRM_Core_Form 
{
    /** 
     * Are we forced to run a search 
     * 
     * @var int 
     * @access protected 
     */ 
    protected $_force; 
    
    /** 
     * processing needed for buildForm and later 
     * 
     * @return void 
     * @access public 
     */ 
    function preProcess( ) 
    {
        $this->_search   = CRM_Utils_Array::value( 'search', $_GET );
        $this->_force    = CRM_Utils_Request::retrieve( 'force',    'Boolean',   $this, false ); 
        $this->_surveyId = CRM_Utils_Request::retrieve( 'surveyId', 'Positive',  $this ); 
        
        $this->assign( 'buildSelector', $this->_search );
        $this->assign( 'searchParams',  json_encode( $this->get( 'searchParams' ) ) );
        $this->assign( 'force',         $this->_force );
        
        //set the form title.
        CRM_Utils_System::setTitle( ts( 'Voter List' ) );
    }
    
    /**
     * Build the form
     *
     * @access public
     * @return void
     */
    function buildQuickForm( ) 
    {
        if ( $this->_search ) return;
        require_once 'CRM/Campaign/BAO/Survey.php';
        $attributes = CRM_Core_DAO::getAttribute( 'CRM_Core_DAO_Address' );
        
        $this->add( 'text', 'sort_name',       ts( 'Contact Name'   ), 
                    CRM_Core_DAO::getAttribute('CRM_Contact_DAO_Contact', 'sort_name' ) );
        $this->add( 'text', 'street_name',     ts( 'Street Name'    ), $attributes['street_name']    );
        $this->add( 'text', 'street_number',   ts( 'Street Number'  ), $attributes['street_number']  );
        $this->add( 'text', 'street_type',     ts( 'Street Type'    ), $attributes['street_type']    );
        $this->add( 'text', 'street_address',  ts( 'Street Address' ), $attributes['street_address'] );
        $this->add( 'text', 'city',            ts( 'City'           ), $attributes['city']           );
        
        $showInterviewer = false;
        $defaults = array( );
        if ( CRM_Core_Permission::check( 'administer CiviCampaign' ) ) {
            $showInterviewer = true;
            //autocomplete url
            $dataUrl = CRM_Utils_System::url( 'civicrm/ajax/rest',
                                              'className=CRM_Contact_Page_AJAX&fnName=getContactList&json=1&reset=1',
                                              false, null, false );
            
            $this->assign( 'dataUrl',$dataUrl );
            $this->add( 'text',   'survey_interviewer_name', ts( 'Select Interviewer' ) );
            $this->add( 'hidden', 'survey_interviewer_id', '',array( 'id' => 'survey_interviewer_id' ) );
            
            $session = CRM_core_Session::singleton( );
            $userId  = $session->get( 'userID' );
            if ( $userId ) {
                $defaults['survey_interviewer_id']    = $userId;
                $defaults['survey_interviewer_name']  = CRM_Core_DAO::getFieldValue( 'CRM_Contact_DAO_Contact',
                                                                                     $userId,
                                                                                     'sort_name',
                                                                                     'id' );
            }
        }
        $this->assign( 'showInterviewer', $showInterviewer );
        
        //build ward and precinct custom fields.
        $query = '
    SELECT  fld.id, fld.label 
      FROM  civicrm_custom_field fld 
INNER JOIN  civicrm_custom_group grp on fld.custom_group_id = grp.id
     WHERE  grp.name = %1';        
        $dao = CRM_Core_DAO::executeQuery( $query, array( 1 => array( 'Voter_Info', 'String' ) ) );
        $customSearchFields = array( );
        require_once 'CRM/Core/BAO/CustomField.php';
        while ( $dao->fetch( ) ) {
            foreach ( array( 'ward', 'precinct' ) as $name ) {
                if ( stripos( $name, $dao->label ) !== false  ) {
                    $fieldId   = $dao->id;
                    $fieldName = 'custom_'.$dao->id;
                    $customSearchFields[$name] = $fieldName;
                    CRM_Core_BAO_CustomField::addQuickFormElement( $this, $fieldName, $fieldId, false, false );
                    break;
                }
            }
        }
        $this->assign( 'customSearchFields',  $customSearchFields );
        
        $surveys = CRM_Campaign_BAO_Survey::getSurveyList( );
        $this->add( 'select', 'campaign_survey_id', ts('Survey'), $surveys, true );
        
        //build the array of all search params.
        $this->_searchParams = array( );
        foreach  ( $this->_elements as $element ) {
            $name = $element->_attributes['name'];
            $this->_searchParams[$name] = $name;
            
        }
        $this->set( 'searchParams',    $this->_searchParams );
        $this->assign( 'searchParams', json_encode( $this->_searchParams ) ); 

        if ( $this->_force && !$this->_surveyId ) {
            // use default survey id
            require_once 'CRM/Campaign/DAO/Survey.php';
            $dao = new CRM_Campaign_DAO_Survey( );
            $dao->is_active  = 1;
            $dao->is_default = 1;   
            if ( $dao->find( true ) ) {
                $this->_surveyId = $dao->id;
            }
            if ( !$this->_surveyId ) CRM_Core_Error::fatal('Could not find valid Survey Id.'); 
        }
        if ( $this->_surveyId ) $defaults['campaign_survey_id'] = $this->_surveyId;
        
        $this->setDefaults( $defaults );
    }

}
