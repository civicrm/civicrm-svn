<?php
/*
 +--------------------------------------------------------------------+
 | CiviCRM version 1.7                                                |
 +--------------------------------------------------------------------+
 | Copyright CiviCRM LLC (c) 2004-2007                                |
 +--------------------------------------------------------------------+
 | This file is a part of CiviCRM.                                    |
 |                                                                    |
 | CiviCRM is free software; you can copy, modify, and distribute it  |
 | under the terms of the Affero General Public License Version 1,    |
 | March 2002.                                                        |
 |                                                                    |
 | CiviCRM is distributed in the hope that it will be useful, but     |
 | WITHOUT ANY WARRANTY; without even the implied warranty of         |
 | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.               |
 | See the Affero General Public License for more details.            |
 |                                                                    |
 | You should have received a copy of the Affero General Public       |
 | License along with this program; if not, contact the Social Source |
 | Foundation at info[AT]civicrm[DOT]org.  If you have questions      |
 | about the Affero General Public License or the licensing  of       |
 | of CiviCRM, see the Social Source Foundation CiviCRM license FAQ   |
 | http://www.civicrm.org/licensing/                                  |
 +--------------------------------------------------------------------+
*/

/**
 *
 * @package CRM
 * @author Donald A. Lobo <lobo@civicrm.org>
 * @copyright CiviCRM LLC (c) 2004-2007
 * $Id$
 *
 */

/**
 * Files required
 */
require_once 'CRM/Event/PseudoConstant.php';
require_once 'CRM/Event/Selector/Search.php';
require_once 'CRM/Core/Selector/Controller.php';
require_once 'CRM/Contact/BAO/SavedSearch.php';

/**
 * This file is for civievent search
 */
class CRM_Event_Form_Search extends CRM_Core_Form
{
    /** 
     * Are we forced to run a search 
     * 
     * @var int 
     * @access protected 
     */ 
    protected $_force; 

    /** 
     * name of search button 
     * 
     * @var string 
     * @access protected 
     */ 
    protected $_searchButtonName;

    /** 
     * name of print button 
     * 
     * @var string 
     * @access protected 
     */ 
    protected $_printButtonName; 
 
    /** 
     * name of action button 
     * 
     * @var string 
     * @access protected 
     */ 
    protected $_actionButtonName;

    /** 
     * form values that we will be using 
     * 
     * @var array 
     * @access protected 
     */ 
    protected $_formValues; 

    /**
     * the params that are sent to the query
     * 
     * @var array 
     * @access protected 
     */ 
    protected $_queryParams;
    
    /** 
     * have we already done this search 
     * 
     * @access protected 
     * @var boolean 
     */ 
    protected $_done; 

    /**
     * are we restricting ourselves to a single contact
     *
     * @access protected  
     * @var boolean  
     */  
    protected $_single = false;

    /** 
     * are we restricting ourselves to a single contact 
     * 
     * @access protected   
     * @var boolean   
     */   
    protected $_limit = null;

    /** 
     * what context are we being invoked from 
     *    
     * @access protected      
     * @var string 
     */      
    protected $_context = null; 
    
    /** 
     * prefix for the controller
     * 
     */
    protected $_prefix = "event_";

    protected $_defaults;


    /** 
     * processing needed for buildForm and later 
     * 
     * @return void 
     * @access public 
     */ 
    function preProcess( ) 
    { 
        /** 
         * set the button names 
         */ 
        $this->_searchButtonName = $this->getButtonName( 'refresh' ); 
        $this->_printButtonName  = $this->getButtonName( 'next'   , 'print' ); 
        $this->_actionButtonName = $this->getButtonName( 'next'   , 'action' ); 
        
        $this->_done = false;
        $this->defaults = array( );
        
        /* 
         * we allow the controller to set force/reset externally, useful when we are being 
         * driven by the wizard framework 
         */ 
        $this->_reset   = CRM_Utils_Request::retrieve( 'reset', 'Boolean', CRM_Core_DAO::$_nullObject ); 
        $this->_force   = CRM_Utils_Request::retrieve( 'force', 'Boolean',  $this, false ); 
        $this->_limit   = CRM_Utils_Request::retrieve( 'limit', 'Positive', $this );
        $this->_context = CRM_Utils_Request::retrieve( 'context', 'String', $this );
        $this->_ssID    = CRM_Utils_Request::retrieve( 'ssID', 'Positive',  $this );
        
        $this->assign( "{$this->_prefix}limit", $this->_limit );
          
        // get user submitted values  
        // get it from controller only if form has been submitted, else preProcess has set this  
        if ( ! empty( $_POST ) ) { 
            $this->_formValues = $this->controller->exportValues( $this->_name );  
        } else {
            $this->_formValues = $this->get( 'formValues' ); 
        } 

        if ( empty( $this->_formValues ) ) {
            if ( isset( $this->_ssID ) ) {
                $this->_formValues = CRM_Contact_BAO_SavedSearch::getFormValues( $this->_ssID );
            }
        }
        
        if ( $this->_force ) { 
            $this->postProcess( );
            $this->set( 'force', 0 );
        }
      
        $sortID = null; 
        if ( $this->get( CRM_Utils_Sort::SORT_ID  ) ) { 
            $sortID = CRM_Utils_Sort::sortIDValue( $this->get( CRM_Utils_Sort::SORT_ID  ), 
                                                   $this->get( CRM_Utils_Sort::SORT_DIRECTION ) ); 
        } 
        
        require_once 'CRM/Contact/BAO/Query.php';
        $this->_queryParams =& CRM_Contact_BAO_Query::convertFormValues( $this->_formValues ); 
        $selector =& new CRM_Event_Selector_Search( $this->_queryParams,
                                                    $this->_action,
                                                    null,
                                                    $this->_single,
                                                    $this->_limit,
                                                    $this->_context ); 
        $prefix = null;
        if ( $this->_context == 'basic' || $this->_context == 'user' ) {
            $prefix = $this->_prefix;
        }
        
        $controller =& new CRM_Core_Selector_Controller($selector ,  
                                                        $this->get( CRM_Utils_Pager::PAGE_ID ),  
                                                        $sortID,  
                                                        CRM_Core_Action::VIEW, 
                                                        $this, 
                                                        CRM_Core_Selector_Controller::TRANSFER,
                                                        $prefix);
        
        $controller->setEmbedded( true ); 
        $controller->moveFromSessionToTemplate(); 
        
        $this->assign( 'summary', $this->get( 'summary' ) );
    }
    
    /**
     * Build the form
     *
     * @access public
     * @return void
     */
    function buildQuickForm( ) 
    {
        $this->addElement('text', 'sort_name', ts('Participant'), CRM_Core_DAO::getAttribute('CRM_Contact_DAO_Contact', 'sort_name') );
        
        require_once 'CRM/Event/BAO/Query.php';
        CRM_Event_BAO_Query::buildSearchForm( $this );

        /* 
         * add form checkboxes for each row. This is needed out here to conform to QF protocol 
         * of all elements being declared in builQuickForm 
         */ 
        $rows = $this->get( 'rows' ); 
        if ( is_array( $rows ) ) {
            if ($this->_context == 'search') {
                $this->addElement( 'checkbox', 'toggleSelect', null, null, array( 'onchange' => "return toggleCheckboxVals('mark_x_',this.form);" ) ); 
                foreach ($rows as $row) { 
                    $this->addElement( 'checkbox', $row['checkbox'], 
                                       null, null, 
                                       array( 'onclick' => "return checkSelectedBox('" . $row['checkbox'] . "', '" . $this->getName() . "');" )
                                       ); 
                }
            }
            
            $total = $cancel = 0;
            $this->assign( "{$this->_prefix}single", $this->_single );
            
            // also add the action and radio boxes
            require_once 'CRM/Event/Task.php';
            $tasks = array( '' => ts('- more actions -') ) + CRM_Event_Task::tasks( );
            if ( isset( $this->_ssID ) ) {
                require_once "CRM/Core/Permission.php";
                $permission = CRM_Core_Permission::getPermission( );
                if ( $permission == CRM_Core_Permission::EDIT ) {
                    require_once "CRM/Contact/Task.php";
                    $tasks = $tasks + CRM_Event_Task::optionalTaskTitle();
                }
                    
                $savedSearchValues = array( 'id' => $this->_ssID,
                                            'name' => CRM_Contact_BAO_SavedSearch::getName( $this->_ssID, 'title' ) );
                $this->assign_by_ref( 'savedSearch', $savedSearchValues );
                $this->assign( 'ssID', $this->_ssID );
            }

            $this->add('select', 'task'   , ts('Actions:') . ' '    , $tasks    ); 
            $this->add('submit', $this->_actionButtonName, ts('Go'), 
                       array( 'class' => 'form-submit', 
                              'onclick' => "return checkPerformAction('mark_x', '".$this->getName()."', 0);" ) ); 
            
            $this->add('submit', $this->_printButtonName, ts('Print'), 
                       array( 'class' => 'form-submit', 
                              'onclick' => "return checkPerformAction('mark_x', '".$this->getName()."', 1);" ) ); 
            
            
            // need to perform tasks on all or selected items ? using radio_ts(task selection) for it 
            $this->addElement('radio', 'radio_ts', null, '', 'ts_sel', array( 'checked' => 'checked') ); 
            $this->addElement('radio', 'radio_ts', null, '', 'ts_all', array( 'onchange' => $this->getName().".toggleSelect.checked = false; toggleCheckboxVals('mark_x_',".$this->getName()."); return false;" ) );
        }
        
        // add buttons 
        $this->addButtons( array( 
                                 array ( 'type'      => 'refresh', 
                                         'name'      => ts('Search') , 
                                         'isDefault' => true     ) 
                                 )    );
    }
    
    /**
     * The post processing of the form gets done here.
     *
     * Key things done during post processing are
     *      - check for reset or next request. if present, skip post procesing.
     *      - now check if user requested running a saved search, if so, then
     *        the form values associated with the saved search are used for searching.
     *      - if user has done a submit with new values the regular post submissing is 
     *        done.
     * The processing consists of using a Selector / Controller framework for getting the
     * search results.
     *
     * @param
     *
     * @return void 
     * @access public
     */
    function postProcess( ) 
    {
        if ( $this->_done ) {
            return;
        }
   
        $this->_done = true;
        
        $this->_formValues = $this->controller->exportValues($this->_name);
        $this->fixFormValues( );

        if ( isset( $this->_ssID ) && empty( $_POST ) ) {
            // if we are editing / running a saved search and the form has not been posted
            $this->_formValues = CRM_Contact_BAO_SavedSearch::getFormValues( $this->_ssID );
        }
        
        require_once 'CRM/Contact/BAO/Query.php';
        $this->_queryParams =& CRM_Contact_BAO_Query::convertFormValues( $this->_formValues ); 
        
        $this->set( 'formValues' , $this->_formValues  );
        $this->set( 'queryParams', $this->_queryParams );
        
        $buttonName = $this->controller->getButtonName( );
        if ( $buttonName == $this->_actionButtonName || $buttonName == $this->_printButtonName ) { 
            // check actionName and if next, then do not repeat a search, since we are going to the next page 
            
            // hack, make sure we reset the task values 
            $stateMachine =& $this->controller->getStateMachine( ); 
            $formName     =  $stateMachine->getTaskFormName( ); 
            $this->controller->resetPage( $formName ); 
            return; 
        }
        
        $sortID = null; 
        if ( $this->get( CRM_Utils_Sort::SORT_ID  ) ) { 
            $sortID = CRM_Utils_Sort::sortIDValue( $this->get( CRM_Utils_Sort::SORT_ID  ), 
                                                   $this->get( CRM_Utils_Sort::SORT_DIRECTION ) ); 
        } 
        
        require_once 'CRM/Contact/BAO/Query.php';
        $this->_queryParams =& CRM_Contact_BAO_Query::convertFormValues( $this->_formValues );
        
        $selector =& new CRM_Event_Selector_Search( $this->_queryParams,
                                                    $this->_action,
                                                    null,
                                                    $this->_single,
                                                    $this->_limit,
                                                    $this->_context ); 
        $prefix = null;
        if ( $this->_context == 'basic' || $this->_context == 'user') {
            $prefix = $this->_prefix;
        }
        
        $controller =& new CRM_Core_Selector_Controller($selector , 
                                                        $this->get( CRM_Utils_Pager::PAGE_ID ), 
                                                        $sortID, 
                                                        CRM_Core_Action::VIEW,
                                                        $this,
                                                        CRM_Core_Selector_Controller::SESSION,
                                                        $prefix);
        $controller->setEmbedded( true ); 
        
        $query   =& $selector->getQuery( );
        
        $controller->run(); 
        
    }
    

    /**
     * Set the default form values
     *
     * @access protected
     * @return array the default array reference
     */
    function &setDefaultValues() {
        $defaults = array();
        $defaults = $this->_formValues;
        return $defaults;
    }


    function fixFormValues( )
    {
        // if this search has been forced
        // then see if there are any get values, and if so over-ride the post values
        // note that this means that GET over-rides POST :)
        
        $event = CRM_Utils_Request::retrieve( 'event', 'Positive',
                                              CRM_Core_DAO::$_nullObject );
        if ( $event ) {
            $this->_formValues['event_title'] = CRM_Event_PseudoConstant::event( $event );
            $this->assign( 'event_title_value', $this->_formValues['event_title'] );
        }
        
        $cid = CRM_Utils_Request::retrieve( 'cid', 'Positive', CRM_Core_DAO::$_nullObject );

        if ( $this->_context == 'user' ) {
            $session =& CRM_Core_Session::singleton( );
            $cid = $session->get( 'userID' ); 
        }

        if ( $cid ) {
            $cid = CRM_Utils_Type::escape( $cid, 'Integer' );
            if ( $cid > 0 ) {
                $this->_formValues['contact_id'] = $cid;
                
                // also assign individual mode to the template
                $this->_single = true;
            }
        }
    }
}
?>