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

require_once 'CRM/Contact/Form/Search/Custom/Basic.php';

require_once 'CRM/Contact/Form/Search/Interface.php';

class CRM_Contribute_Form_Search_Custom_BatchSearch extends CRM_Contact_Form_Search_Custom_Basic implements CRM_Contact_Form_Search_Interface {

  protected $_formValues;
  public $_useDistinct = false;
  public $_tables;
  public $_whereTables;
  public $_context;
  
  function __construct( &$formValues ) {     
    parent::__construct( $formValues );
    $this->_formValues = $formValues;
    
    /**
     * Define the columns for search result rows
     */
    $this->_columns = array( 
      ts( 'Batch Name' ) => 'name',
      ts( ' '   ) => 'contact_type',
      ts( 'Created By' ) => 'sort_name',
      ts( 'Description'   ) => 'description',
      ts( 'Payment Instrument'   ) => 'payment_instrument',
      ts( 'Type'   ) => 'batch_type',
      ts( 'Entered transactions'   ) => 'manual_number_trans',
      ts( 'Assigned transactions'   ) => 'assigned_number_trans',
      ts( 'Entered total'   ) => 'manual_total',
      ts( 'Assigned total'   ) => 'assigned_total',
      ts( 'Opened'   ) => 'created_date',
      ts( 'Closed'   ) => 'modified_date',
      ts( 'id'   ) => 'id',
     );
    //end custom fields
    require_once "CRM/Utils/Request.php";
    $this->_context = CRM_Utils_Request::retrieve( 'context', 'String' );
  }
  
  function buildForm( &$form ) {
    /**
     * You can define a custom title for the search form
     */
      
    // $a = $form->getVar('_modeValue');
    // $a['taskClassName'] = 'CRM_Contribute_Task';
    //$form->setVar('_modeValue', $a);
    $form->setVar('_componentMode', 2 );
    // // CRM_Core_Error::debug( '$form', $form );
    // // CRM_Core_Error::debug( '$this', $this );
    // // exit;
    if ( CRM_Utils_Array::value( 'status_name', $this->_formValues ) ) {
      $this->_context = CRM_Utils_Array::value( 'status_name', $this->_formValues );
    }
    $form->setVar('_financialBatchStatus', $this->_context);
    
    if ( $this->_context == 'Open' ) {
      $this->setTitle( 'Find Open Batches' );
    } elseif ( $this->_context == 'Closed' ) {
        $this->setTitle( 'Find Closed Batches' );
    }
    
    /**
     * Define the search form fields here
     */
    require_once 'CRM/Contribute/PseudoConstant.php';
    
    // Text box for Enroll Trans no
    $form->add( 'text', 'name', ts( 'Batch Name' ) );
    
    $form->add( 'select', 
                'type_id', 
                ts( 'Batch Type' ), 
                array( ''=> ts( '- Select Batch Type -' ) ) + CRM_Contribute_PseudoConstant::accountOptionValues( 'batch_type' ) );
    
    // $form->add('text', 'close_date', ts('Closed Date'), CRM_Core_DAO::getAttribute( 'CRM_Financial_DAO_FinancialBatch', 'close_date' ) );
    // $form->add('text', 'open_date', ts('Open Date'), CRM_Core_DAO::getAttribute( 'CRM_Financial_DAO_FinancialBatch', 'open_date' ) );
    
    $form->add( 'select', 
                'payment_instrument_id', 
                ts( 'Payment Instrument' ), 
                array( ''=> ts( '- Select Payment Instrument -' ) ) + CRM_Contribute_PseudoConstant::paymentInstrument( ),
                false );
    
    $form->add('text', 'manual_total', ts('Total Amount'), CRM_Core_DAO::getAttribute( 'CRM_Financial_DAO_FinancialBatch', 'manual_total' ) );
    
    $form->add('text', 'manual_number_trans', ts('Number of Transactions'), CRM_Core_DAO::getAttribute( 'CRM_Financial_DAO_FinancialBatch', 'manual_number_trans' ) );
    $form->add('text', 'sort_name', ts('Created By'), CRM_Core_DAO::getAttribute( 'CRM_Contact_DAO_Contact', 'sort_name' ) );
    $form->addElement('hidden', 'status_name' , $this->_context );
    
    require_once "CRM/Core/Permission.php";
    $permission = CRM_Core_Permission::getPermission( );
    
    // require_once 'CRM/Financial/Task.php';
    // $tasks = array( '' => ts('- actions -') ) + CRM_Financial_Task::permissionedTaskTitles( $permission );
    
    // $form->add('select', 'task'   , ts('Actions:') . ' '    , $tasks    ); 
    // $form->add('submit', $form->_actionButtonName, ts('Go'),
    //                array( 'class'   => 'form-submit',
    //                       'id'      => 'Go',
    //  
    $form->assign( 'elements', array( 'name', 'sort_name', 'type_id', 'close_date', 'open_date', 'payment_instrument_id', 'manual_number_trans', 'manual_total', ) );

  }
  
  function summary( ) {
    return null;
  }
  
  function changetemplateFile( &$a) {
    $a = 'CRM_Contribute_Task';
  }

  /**
   * Define the smarty template used to layout the search form and results listings.
   */
  function templateFile( ) {
    $template = CRM_Core_Smarty::singleton( );
    $vars =& $template->_tpl_vars['rows'];
    $selectorLabel =& $template->_tpl_vars['selectorLabel'];
    $selectorLabel = 'Batches';
    $this->changetemplateFile( & $template->_tpl_vars['taskClassName'] );
    if ( $vars ) {
      $this->alterRows( $vars );
    }
    return 'CRM/Contribute/Form/Search/Custom/BatchSearch.tpl';
    //return 'CRM/Contact/Form/Search/Custom.tpl';
  }

  /**
   * Construct the search query
   */       
  function all( $offset = 0, $rowcount = 0, $sort = null,
                $includeContactIDs = false, $onlyIDs = false ) {
      
    // SELECT clause must include contact_id as an alias for civicrm_contact.id
    if ( $onlyIDs ) {
      $select  = 'fb.id as batch_id'; 
    } else {
      $select = "
cb.`id` AS contact_id,
cc.`id` AS id,
cc.`contact_type` AS contact_type,
cc.`contact_type` AS contact_types,
cc.`contact_sub_type`,
cc.`sort_name`, 
cb.`name`, 
cb.`description`, 
fb.`manual_number_trans`, 
fb.`manual_total`, 
cb.`created_date`, 
cb.`modified_date`, 
covt.`label` AS `batch_type`, 
covp.`label` AS `payment_instrument`,
count( cefi.entity_id ) AS `assigned_number_trans`,
sum( cfi.total_amount ) AS `assigned_total`
"; 
    }
    $from  = $this->from( );
    $where =$this->where( $includeContactIDs );
    if ( ! empty( $where ) ) {
      $where = "WHERE $where ";
    }
    $groupBy = " GROUP BY cb.id ";
    $sql = " SELECT $select FROM   $from $where $groupBy";
    //no need to add order when only contact Ids.
    if ( !$onlyIDs ) {
      // Define ORDER BY for query in $sort, with default value
      if ( ! empty( $sort ) ) {
        if ( is_string( $sort ) ) {
          $sql .= " ORDER BY $sort ";
        } else {
          $sql .= ' ORDER BY ' . trim( $sort->orderBy() );
        }
      } else {
        $sql .= 'ORDER BY cb.name';
      }
    }
    if ( $rowcount > 0 && $offset >= 0 ) {
      $sql .= " LIMIT $offset, $rowcount ";
    } 
    return $sql;
    
  }

  function alterRow( &$row ) {
  }

  // Alters the date display in the Activity Date Column. We do this after we already have 
  // the result so that sorting on the date column stays pertinent to the numeric date value
  function alterRows( &$row ) {
    $links = array(
       'transaction' =>  array(
                               'name'  => ts( 'Transaction' ),
                               'url'   => 'civicrm/batchtransaction',
                               'qs'    => 'reset=1&bid=%%id%%',
                               'title' => ts( 'View all Transaction' ),
                               ),
       'edit' =>    array(
                          'name'  => ts( 'Edit' ),
                          'url'   => 'civicrm/financial/batch',
                          'qs'    => 'reset=1&action=update&id=%%id%%',
                          'title' => ts( 'Edit Batch' ),
                          ), 
       'close' =>    array(
                           'name'  => ts( 'Close' ),
                           'title' => ts( 'Close Batch' ), 
                           'extra' => 'onclick = "closeReopen( %%id%%,\'' . 'close' . '\' );"',
                           ),
       'export' =>  array(
                          'name'  => ts( 'Export' ),
                          'url'   => 'civicrm/financial/batch',
                          'qs'    => 'reset=1&action=export&id=%%id%%',
                          'title' => ts( 'Export Batch' ),
                          ),
       'reopen' =>  array(
                          'name'  => ts( 'ReOpen' ),
                          'title' => ts( 'ReOpen Batch' ), 
                          'extra' => 'onclick = "closeReopen( %%id%%,\'' . 'reopen' . '\' );"',
                          )  
       
                   );
    require_once( 'CRM/Contact/BAO/Contact/Utils.php' );
    foreach( $row as $rowKey => $rowValue ){
      if ( CRM_Utils_Array::value('status_name', $this->_formValues) == 'Open' ){
        unset($links['reopen']);
      } elseif ( CRM_Utils_Array::value('status_name', $this->_formValues) == 'Closed' ) {
        unset($links['close']);
        unset($links['edit']);
      }
      $row[$rowKey]['action'] = CRM_Core_Action::formLink( $links, null, array( 'id' => $rowValue['contact_id'] ) );
      $row[$rowKey]['contact_type' ] = CRM_Contact_BAO_Contact_Utils::getImage( CRM_Utils_Array::value('contact_sub_type',$rowValue) ? 
         CRM_Utils_Array::value('contact_sub_type',$rowValue) : CRM_Utils_Array::value('contact_types',$rowValue) ,false,$rowValue['id']);
    }
  }
  
  // Regular JOIN statements here to limit results to contacts who have activities.
  function from( ) {
      $from = " `civicrm_batch` AS cb
LEFT JOIN `civicrm_financial_batch` AS fb ON fb.`batch_id` = cb.`id`
LEFT JOIN `civicrm_contact` AS cc ON cc.`id` = cb.`created_id`
LEFT JOIN `civicrm_option_value` AS covt ON covt.`value` = cb.`type_id`
INNER JOIN `civicrm_option_group` AS cogt ON covt.`option_group_id` = cogt.`id` AND cogt.`name` = 'batch_type'
LEFT JOIN `civicrm_option_value` AS covp ON covp.`value` = fb.`payment_instrument_id` AND covp.`option_group_id` = 10
LEFT JOIN `civicrm_option_value` AS covs ON covs.`value` = cb.`status_id`
INNER JOIN `civicrm_option_group` AS cogs ON covs.`option_group_id` = cogs.`id` AND cogs.`name` = 'batch_status'
LEFT JOIN civicrm_entity_financial_item cefi ON cefi.entity_id = cb.id
LEFT JOIN civicrm_financial_item cfi ON cfi.id = cefi.financial_item_id
";
      
  }     
  
  /*
   * WHERE clause is an array built from any required JOINS plus
   * conditional filters based on search criteria field values
   *
   */
  function where( $includeContactIDs = false ) {
    $clauses = array();
    
    if ( CRM_Utils_Array::value('name', $this->_formValues ) )
      $clauses[] = "( cb.name LIKE '{$this->_formValues['name']}%' )";
    
    if ( CRM_Utils_Array::value('sort_name', $this->_formValues ) )
      $clauses[] = "( cc.sort_name LIKE '%{$this->_formValues['sort_name']}%' )";
    
    if ( CRM_Utils_Array::value('batch_type_id', $this->_formValues ) )
      $clauses[] = "( cb.batch_type_id LIKE '{$this->_formValues['batch_type_id']}' )";
    
    if ( CRM_Utils_Array::value('payment_instrument_id', $this->_formValues ) )
      $clauses[] = "( fb.payment_instrument_id LIKE '{$this->_formValues['payment_instrument_id']}' )";

    if ( CRM_Utils_Array::value('manual_total', $this->_formValues ) )
      $clauses[] = "( fb.manual_total LIKE '{$this->_formValues['manual_total']}' )";
    
    if ( CRM_Utils_Array::value('manual_number_trans', $this->_formValues ) )
      $clauses[] = "( fb.manual_number_trans LIKE '{$this->_formValues['manual_number_trans']}' )";
    
    if ( CRM_Utils_Array::value( 'status_name', $this->_formValues ) )
      $clauses[] = "( covs.`label` LIKE '{$this->_formValues['status_name']}' )";
    
    // if( CRM_Utils_Array::value('', $this->_formValues ) )
    //     $clauses[] = "( fb. LIKE '{$this->_formValues['']}' )";
    
    return implode( ' AND ', $clauses );
  }
  
  /* 
   * Functions below generally don't need to be modified
   */
  function count( ) {
    $sql = $this->all( );
    $dao = CRM_Core_DAO::executeQuery( $sql,
       CRM_Core_DAO::$_nullArray );
    return $dao->N;
  }
  
  function contactIDs( $offset = 0, $rowcount = 0, $sort = null ) { 
    return $this->all( $offset, $rowcount, $sort,  false, true );
  }
  
  function &columns( ) {
    if ( CRM_Utils_Array::value( 'status_name', $this->_formValues ) == 'Open' )
      unset( $this->_columns['Closed'] );
    return $this->_columns;
  }

  function setTitle( $title ) {
    if ( $title ) {
      CRM_Utils_System::setTitle( $title );
    } else {
      CRM_Utils_System::setTitle(ts('Search'));
    }
  }
  
}
