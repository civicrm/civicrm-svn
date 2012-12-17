<?php

/*
 +--------------------------------------------------------------------+
 | CiviCRM version 4.0                                                |
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
 | see the CiviCRM license FAQ at http://civicrm.org/licensing        |
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
 * This class generates form components for Financial Batch
 * 
 */
class CRM_Financial_Form_FinancialBatch extends CRM_Contribute_Form {
      
  /**
   * The financial batch id, used when editing the field
   *
   * @var int
   * @access protected
   */
  protected $_id;

  /**
   * Function to set variables up before form is built
   *
   * @return void
   * @access public
   */

  public function preProcess() { 
    $this->_id = CRM_Utils_Request::retrieve('id', 'Positive', $this);
    parent::preProcess();
    $session = CRM_Core_Session::singleton();
    if ($this->_id) {
      $permissions = array( 
        CRM_Core_Action::UPDATE => array('permission' => array('edit own manual batches','edit all manual batches'),
          'actionName' => 'edit'),
        CRM_Core_Action::EXPORT => array('permission' => array('export own manual batches', 'export all manual batches'),
          'actionName' => 'export'),
        CRM_Core_Action::DELETE => array('permission' => array('delete own manual batches', 'delete all manual batches'),
          'actionName' => 'delete'),
      );
      $createdID = CRM_Core_DAO::getFieldValue('CRM_Batch_DAO_Batch', $this->_id, 'created_id');
      if (CRM_Utils_Array::value($this->_action, $permissions)) {
        $this->checkPermissions($this->_action, $permissions[$this->_action]['permission'], $createdID, $session->get('userID'), $permissions[$this->_action]['actionName'] );
      }

      $status = CRM_Core_DAO::getFieldValue('CRM_Batch_DAO_Batch', $this->_id, 'status_id');            
      $batchStatus = CRM_Core_PseudoConstant::accountOptionValues('batch_status');
      //FIXME
      //if (CRM_Utils_Array::value($status, $batchStatus) != 'Open') {
      // CRM_Core_Error::statusBounce(ts("You cannot edit {$batchStatus[$status]} Batch")); 
      //}
    }
  }

  /**
   * Function to build the form
   *
   * @return None
   * @access public
   */
  public function buildQuickForm() {
    parent::buildQuickForm();       
    if (isset( $this->_id)) {
      $this->_title = CRM_Core_DAO::getFieldValue('CRM_Batch_DAO_Batch', $this->_id, 'name');
      CRM_Utils_System::setTitle($this->_title .' - '.ts( 'Financial Batch'));
      $this->assign('batchTitle', $this->_title);
    }
    if ($this->_action & (CRM_Core_Action::CLOSE | CRM_Core_Action::REOPEN | CRM_Core_Action::EXPORT)) { 
      if ($this->_action & CRM_Core_Action::CLOSE) {
        $buttonName = 'Close Batch';
      } elseif ($this->_action & CRM_Core_Action::REOPEN) {
        $buttonName = 'ReOpen Batch';
      } elseif ($this->_action & CRM_Core_Action::EXPORT) {
        $buttonName = 'Export Batch';
      }
      $this->addButtons(
        array( 
          array( 
            'type' => 'next', 
            'name' => ts($buttonName), 
            'spacing' => '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;', 
            'isDefault' => true   
          ), 
          array( 
            'type' => 'cancel', 
            'name' => ts('Cancel')
          ), 
        ) 
      );
      $this->assign('actionName', $buttonName);
      return;
    }
         
    $this->applyFilter('__ALL__', 'trim');
         
    $this->addButtons( 
      array(
        array( 
          'type' => 'next',
          'name' => ts('Save'),
          'isDefault' => true
        ),
        array( 
          'type' => 'next',
          'name' => ts('Save and New'),
          'subName' => 'new'
        ),
        array( 
          'type' => 'cancel',
          'name' => ts('Cancel')
        ),
      )
    );
    
    if ($this->_action & CRM_Core_Action::UPDATE && $this->_id) {
            
      $element = $this->add('select', 'type_id', ts('Batch Type'), 
        array('' => ts('- Select Batch Type -')) + CRM_Core_PseudoConstant::accountOptionValues('batch_type'));
            
      $element->freeze();
      if ($flag = CRM_Core_Permission::check('edit all manual batches')) {
        $dataURL = CRM_Utils_System::url('civicrm/ajax/getContactList', 'json=1&users=1', false, null, false);
        $this->assign('dataURL', $dataURL);
        $this->add('text', 'contact_name', ts('Created By'), CRM_Core_DAO::getAttribute('CRM_Batch_DAO_Batch', 'created_id'));
        $this->add('hidden', 'created_id', '', array('id' => 'created_id'));
      }
      
      $element = $this->add('text', 'modified_date', ts('Modified Date'), CRM_Core_DAO::getAttribute('CRM_Batch_DAO_Batch', 'modified_date'));
      $element->freeze();
      $element = $this->add('text', 'created_date', ts('Opened Date'), CRM_Core_DAO::getAttribute('CRM_Batch_DAO_Batch', 'created_date'));
      $element->freeze();     
      $batchStatus = CRM_Core_PseudoConstant::accountOptionValues('batch_status');
      //unset exported status
      $exportedStatusId = CRM_Utils_Array::key('Exported', $batchStatus );
      unset($batchStatus[$exportedStatusId]);
      $this->add('select', 'status_id', ts('Batch Status'), array('' => ts('- Select Batch Status -')) + $batchStatus, true); 
    }
      
    $this->add('text', 'name', ts('Batch Name'), CRM_Core_DAO::getAttribute('CRM_Batch_DAO_Batch', 'name'), true);
        
    $this->add('text', 'description', ts('Description'), CRM_Core_DAO::getAttribute('CRM_Batch_DAO_Batch', 'description'));

    $this->add('select', 'payment_instrument_id', ts('Payment Instrument'), 
      array('' => ts('- Select Payment Instrument -')) + CRM_Contribute_PseudoConstant::paymentInstrument(),
      false 
    );
        
    $this->add('text', 'total', ts('Total Amount'), CRM_Core_DAO::getAttribute('CRM_Batch_DAO_Batch', 'total'), true);
        
    $this->add('text', 'item_count', ts('Number of Transactions'), CRM_Core_DAO::getAttribute('CRM_Batch_DAO_Batch', 'item_count'), true);      
    $this->addFormRule(array('CRM_Financial_Form_FinancialBatch', 'formRule'), $this);    
   } 

  /**
   * This function sets the default values for the form. Note that in edit/view mode
   * the default values are retrieved from the database
   *
   * @access public
   *
   * @return void
   */
  function setDefaultValues() {
    $defaults = parent::setDefaultValues();
    list($defaults['modified_date'], $defaults['modified_time']) = CRM_Utils_Date::setDateDefaults(CRM_Utils_Array::value('modified_date', $defaults));
    list($defaults['created_date'], $defaults['created_time']) = CRM_Utils_Date::setDateDefaults(CRM_Utils_Array::value('created_date', $defaults));
    return $defaults; 
  }

   /**
    * global validation rules for the form
    *
    * @param array $fields posted values of the form
    *
    * @return array list of errors to be posted back to the form
    * @static
    * @access public
    */
   static function formRule($values, $files, $self) {
     $errorMsg = array();
     if (!is_numeric($values['item_count'])) {
       $errorMsg['item_count'] = ts('Number of Transactions should be numeric');
     }
     if (!is_numeric($values['total'])) {
       $errorMsg['total'] = ts('Total Amount should be numeric');
     }
     if (CRM_Utils_Array::value('created_date', $values) && date('Y-m-d') < date('Y-m-d', strtotime($values['created_date']))) {
       $errorMsg['created_date'] = ts('Created date cannot be greater than current date');
     }
     return CRM_Utils_Array::crmIsEmptyArray($errorMsg) ? true : $errorMsg; 
   } 



   /**
    * Function to process the form
    *
    * @access public
    * @return None
    */
   public function postProcess() {
     $session = CRM_Core_Session::singleton();
     $params = $ids = array();
     $params = $this->exportValues();
     $batchStatus = CRM_Core_PseudoConstant::accountOptionValues('batch_status');
     if ($this->_id) {
       $ids['batchID'] = $this->_id;
       $params['id'] = $this->_id;
     }
     if ($this->_action & CRM_Core_Action::EXPORT) {
       $params['status_id'] = CRM_Utils_Array::key('Exported', $batchStatus);
     }
     // store the submitted values in an array
     $params['modified_date'] = date('YmdHis');
     $params['modified_id'] = $session->get('userID');
     if (CRM_Utils_Array::value('created_date', $params)) {
       $params['created_date'] = CRM_Utils_Date::processDate($params['created_date']);
     }
     if ($this->_action & CRM_Core_Action::ADD) {
       $batchType = CRM_Core_PseudoConstant::accountOptionValues('batch_type');
       $params['type_id'] = CRM_Utils_Array::key('Manual batch', $batchType);  
       $params['status_id'] = CRM_Utils_Array::key('Open', $batchStatus);
       $params['title'] = CRM_Utils_Array::value('name', $params);
       $params['created_date'] = date('YmdHis');
       $params['created_id'] = $session->get('userID');
       $details = "{$params['name']} batch has been created by this contact.";
     }
     
     if ($this->_action & CRM_Core_Action::UPDATE && $this->_id) {
       $params['title'] = CRM_Utils_Array::value('name', $params);
       $details = "{$params['name']} batch has been edited by this contact.";
       if (CRM_Utils_Array::value($params['status_id'], $batchStatus) == 'Closed') {
         $details = "{$params['name']} batch has been closed by this contact.";
       }
     } 
     $batch = CRM_Batch_BAO_Batch::create($params, $ids, $context = 'financialBatch');
     
     if ($this->_action & CRM_Core_Action::EXPORT) {
       CRM_Batch_BAO_Batch::exportFinancialBatch($ids);
     } 
     $activityTypes = CRM_Core_PseudoConstant::activityType(TRUE, FALSE, FALSE, 'name');
     //create activity. 
     $activityParams = array( 
       'activity_type_id' => array_search('Export of Financial Transactions Batch', $activityTypes), 
       'subject' => CRM_Utils_Array::value('name', $params). "- Batch", 
       'status_id' => 2, 
       'priority_id' => 2,
       'activity_date_time' => date('YmdHis'),
       'source_contact_id' => $session->get('userID'),
       'source_contact_qid' => $session->get('userID'),
       'details' => $details,    
     );
     $activity = CRM_Activity_BAO_Activity::create($activityParams);
     $buttonName = $this->controller->getButtonName();

     $session = CRM_Core_Session::singleton();
     if ($buttonName == $this->getButtonName('next', 'new')) {
       CRM_Core_Session::setStatus(ts(' You can add another Financial Batch.'));
       $session->replaceUserContext(CRM_Utils_System::url('civicrm/financial/batch', 
         "reset=1&action=add"));
     } elseif (CRM_Utils_Array::value($batch->status_id, $batchStatus) == 'Closed') {
       if ($batch->name) {
         CRM_Core_Session::setStatus(ts("'{$batch->name}' batch has been saved."));
       }
       $session->replaceUserContext(CRM_Utils_System::url('civicrm','reset=1'));
     } else {
       if ($batch->name) {
         CRM_Core_Session::setStatus(ts("'{$batch->name}' batch  has been saved."));
       }
       $session->replaceUserContext(CRM_Utils_System::url('civicrm/batchtransaction', 
         "reset=1&bid={$batch->id}"));
     }     
   } 

   /**
    * global validation rules for the form
    *
    * @param array $fields posted values of the form
    *
    * @return array list of errors to be posted back to the form
    * @static
    * @access public
    */
  function checkPermissions($action, $permissions, $createdID, $userContactID, $actionName) {
    if ((CRM_Core_Permission::check($permissions[0]) || CRM_Core_Permission::check($permissions[1]))) {
      if (CRM_Core_Permission::check($permissions[0]) && $userContactID != $createdID && !CRM_Core_Permission::check($permissions[1])) {
        CRM_Core_Error::statusBounce(ts('You dont have permission to {$actionName} this batch'));
      }
    } else {
      CRM_Core_Error::statusBounce(ts('You dont have permission to {$actionName} this batch'));
    }
  } 
}

 
