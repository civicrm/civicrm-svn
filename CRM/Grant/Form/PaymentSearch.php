<?php

/*
 +--------------------------------------------------------------------+
 | CiviCRM version 4.1                                                |
 +--------------------------------------------------------------------+
 | Copyright CiviCRM LLC (c) 2004-2011                                |
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
 | see the CiviCRM license FAQ at http://civicrm.org/licensing   
 +--------------------------------------------------------------------+
*/

/**
 *
 * @package CRM
 * @copyright CiviCRM LLC (c) 2004-2011
 * $Id$
 *
 */

/**
 * Files required
 */
require_once 'CRM/Grant/PseudoConstant.php';
require_once 'CRM/Grant/Selector/PaymentSearch.php';
require_once 'CRM/Core/Selector/Controller.php';
require_once 'CRM/Contact/BAO/SavedSearch.php';

/**
 * This file is for civigrant search
 */
class CRM_Grant_Form_PaymentSearch extends CRM_Core_Form
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
    protected $_prefix = "grant_";

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
        $this->_reset    = CRM_Utils_Request::retrieve( 'reset', 'Boolean', CRM_Core_DAO::$_nullObject ); 
        $this->_force    = CRM_Utils_Request::retrieve( 'force', 'Boolean',  $this, false ); 
        $this->_download = CRM_Utils_Request::retrieve( 'download', 'String',  $this, false );
        $this->_batchId  = CRM_Utils_Request::retrieve( 'bid', 'Positive',  $this, false );
        $this->_limit    = CRM_Utils_Request::retrieve( 'limit', 'Positive', $this );
        $this->_context  = CRM_Utils_Request::retrieve( 'context', 'String', $this, false, 'search' );
        
        $this->assign( "context", $this->_context );
        
        // get user submitted values
        // get it from controller only if form has been submitted, else preProcess has set this
        if ( ! empty( $_POST ) ) { 
            $this->_formValues = $this->controller->exportValues( $this->_name );
        } else {
            $this->_formValues = $this->get( 'formValues' ); 
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
        require_once 'CRM/Grant/BAO/PaymentSearch.php';
        $this->_queryParams = CRM_Grant_BAO_PaymentSearch::convertFormValues( $this->_formValues );
        $selector = new CRM_Grant_Selector_PaymentSearch( $this->_queryParams,
                                                          $this->_action,
                                                          null,
                                                          $this->_single,
                                                          $this->_limit,
                                                          $this->_context );
        
        $prefix = null;
        if ( $this->_context == 'user' ) {
            $prefix = $this->_prefix;
        }
        $this->assign( "{$prefix}limit", $this->_limit );
        $this->assign( "{$prefix}single", $this->_single );
        $controller = new CRM_Core_Selector_Controller($selector ,  
                                                       $this->get( CRM_Utils_Pager::PAGE_ID ),  
                                                       $sortID,  
                                                       CRM_Core_Action::VIEW, 
                                                       $this, 
                                                       CRM_Core_Selector_Controller::TRANSFER,
                                                       $prefix);
        
        $controller->setEmbedded( true ); 
        $controller->moveFromSessionToTemplate();
        $this->assign( 'summary', $this->get( 'summary' ) ); 
        $download = false;
        if ( CRM_Utils_Array::value( 'bid', $_GET ) ) {
            $download = true;
        }
        if ( $this->_download && $download ) {
            global $base_url;
            $config = CRM_Core_Config::singleton();
            $directory = strstr( $config->customFileUploadDir, 'sites' );;
            $config = CRM_Core_Config::singleton();
            $file_name = $base_url.'/'.$directory.$this->_download;
            $this->assign( 'download', $file_name ); 
        }
    }
    
    /**
     * Build the form
     *
     * @access public
     * @return void
     */
    function buildQuickForm( ) 
    { 
        require_once 'CRM/Core/OptionGroup.php';
        $paymentStatus = CRM_Core_OptionGroup::values( 'grant_payment_status' );
        $this->add('select', 'payment_status_id',  ts( 'Status' ),
                   array( '' => ts( '- select -' ) ) + $paymentStatus);
        
        $this->addElement('text', 'payment_batch_number', ts('Batch Number'), array( 'size' => 8, 'maxlength' => 8 ) );
       
        $this->addElement('text', 'payment_number', ts('Payment Number'), array( 'size' => 8, 'maxlength' => 8 ) );
       
        $this->addDate( 'payment_created_date_low', ts('From'), false, array( 'formatType' => 'searchDate') );
        $this->addDate( 'payment_created_date_high', ts('To'), false, array( 'formatType' => 'searchDate') );
        
        $this->addElement('text', 'payable_to_name', ts('Payee name'), CRM_Core_DAO::getAttribute('CRM_Grant_DAO_GrantPayment', 'payable_to_name') );
 
        $this->add('text', 'amount', ts('Amount'), array( 'size' => 8, 'maxlength' => 8 ) ); 
        //$this->addRule('amount', ts('Please enter a valid money value (e.g. %1).', array(1 => CRM_Utils_Money::format('99.99', ' '))), 'money');
       
        
        /* 
         * add form checkboxes for each row. This is needed out here to conform to QF protocol 
         * of all elements being declared in builQuickForm 
         */ 

        $rows = $this->get( 'rows' );
        
        if ( is_array( $rows ) )  {
            $this->assign('rows', $rows);
            $this->assign('hideTask', 1 );
            if ( !$this->_single ) {
                $this->addElement( 'checkbox', 'toggleSelect', null, null, array( 'onchange' => "toggleTaskAction( true );return toggleCheckboxVals('mark_x_',this);" ) ); 
                foreach ($rows as $row) {
                    $this->addElement( 'checkbox', CRM_Utils_Array::value( 'checkbox', $row ), 
                                       null, null, 
                                       array( 'onclick' => " toggleTaskAction( true )return;checkSelectedBox('" . CRM_Utils_Array::value( 'checkbox', $row ) . "');" )
                                      
                                       ); 
                }
            }

            $total = $cancel = 0;

            require_once "CRM/Core/Permission.php";
            $permission = CRM_Core_Permission::getPermission( );

            require_once 'CRM/Grant/Task.php';
            $tasks = array( '' => ts('- actions -') );
            $permissionedTask = CRM_Grant_Task::permissionedTaskTitles( $permission );
            if ( is_array( $permissionedTask ) && !CRM_Utils_System::isNull( $permissionedTask ) ) {
                $tasks += $permissionedTask;
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
            $this->addElement('radio', 'radio_ts', null, '', 'ts_all', array( 'onchange' => $this->getName().".toggleSelect.checked = false; toggleCheckboxVals('mark_x_',this); toggleTaskAction( true );" ) );
        }
        
    //add buttons 
        $this->addButtons( array( 
                                 array ( 'type'      => 'refresh', 
                                         'name'      => ts('Search'), 
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
        

        if ( !empty( $_GET['bid'] ) ) {
            $this->_formValues['payment_batch_number'] = $_GET['bid'];
        }
        $this->fixFormValues( );

        require_once 'CRM/Grant/BAO/PaymentSearch.php';
        $this->_queryParams = CRM_Grant_BAO_PaymentSearch::convertFormValues( $this->_formValues );
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
      

        $selector = new CRM_Grant_Selector_PaymentSearch( $this->_queryParams,
                                                          $this->_action,
                                                          null,
                                                          $this->_single,
                                                          $this->_limit,
                                                          $this->_context );
        
        $selector->setKey( $this->controller->_key );
       
        
        $prefix = null;
        if ( $this->_context == 'basic' || $this->_context == 'user') {
            $prefix = $this->_prefix;

        }
       
        $controller = new CRM_Core_Selector_Controller($selector , 
                                                       $this->get( CRM_Utils_Pager::PAGE_ID ), 
                                                       $sortID, 
                                                       CRM_Core_Action::VIEW,
                                                       $this,
                                                       CRM_Core_Selector_Controller::SESSION,
                                                       $prefix);
        
        $controller->setEmbedded( true ); 
        
        $query   =& $selector->getQuery( );
        if ( $this->_context == 'user' ) {
            $query->setSkipPermission( true );
        }
        $controller->run(); 
    }
    

    /**
     * Set the default form values
     *
     * @access protected
     * @return array the default array reference
     */
    function &setDefaultValues( ) 
        {
            return $this->_formValues;
        }

    function fixFormValues( )
    {
        // if this search has been forced
        // then see if there are any get values, and if so over-ride the post values
        // note that this means that GET over-rides POST :)
        
        if ( ! $this->_force ) {
            return;
        }

        $status = CRM_Utils_Request::retrieve( 'status', 'String',
                                               CRM_Core_DAO::$_nullObject );
        if ( $status ) {
            $this->_formValues['payment_status_id'] = $status;
            $this->_defaults  ['payment_status_id'] = $status;
        }

        $cid = CRM_Utils_Request::retrieve( 'cid', 'Positive', $this );

        if ( $cid ) {
            $cid = CRM_Utils_Type::escape( $cid, 'Integer' );
            if ( $cid > 0 ) {
                $this->_formValues['contact_id'] = $cid;
                
                // also assign individual mode to the template
                $this->_single = true;

            }
        }    
    }

    function getFormValues( ) {
        return null;
    }
    
    /**
     * Return a descriptive name for the page, used in wizard header
     *
     * @return string
     * @access public
     */
    public function getTitle( ) 
    {
        return ts( 'Search Grant Payments' );
    } 

    function run( ) 
    {
        $action = CRM_Utils_Request::retrieve('action', 'String',
                                              $this, false, 0 );
        
        if ( $action & CRM_Core_Action::VIEW ) { 
            $this->view( $action); 
        } else if ( $action & ( CRM_Core_Action::UPDATE | CRM_Core_Action::ADD | CRM_Core_Action::DELETE ) ) {
            $this->edit( $action);
        } else {
            $this->browse( ); 
        }
        $this->assign('action', $action);
        return parent::run( );
    }
}

