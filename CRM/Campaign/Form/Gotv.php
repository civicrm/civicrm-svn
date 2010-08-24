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
    
    protected $_votingTab = false;
    
    /** 
     * processing needed for buildForm and later 
     * 
     * @return void 
     * @access public 
     */ 
    function preProcess( ) 
    {
        $this->_search    = CRM_Utils_Array::value( 'search', $_GET );
        $this->_force     = CRM_Utils_Request::retrieve( 'force',    'Boolean',   $this, false ); 
        $this->_surveyId  = CRM_Utils_Request::retrieve( 'surveyId', 'Positive',  $this );
        $this->_votingTab = $this->get( 'votingTab' );
        
        $this->assign( 'buildSelector', $this->_search );
        $this->assign( 'searchParams',  json_encode( $this->get( 'searchParams' ) ) );
        $this->assign( 'force',         $this->_force );
        $this->assign( 'votingTab',     $this->_votingTab );
        
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
        
        //build common search form.
        require_once 'CRM/Campaign/BAO/Query.php';
        CRM_Campaign_BAO_Query::buildSearchForm( $this );
        
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
        if ( $this->_surveyId ) $this->setDefaults( array( 'campaign_survey_id' => $this->_surveyId ) );
    }

}
