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
 * This class generates form components for Financial Type
 *
 */
class CRM_Financial_Form_BatchTransaction extends CRM_Contribute_Form {
  static $_links = null;
  static $_entityID;

  /**
   * Function to build the form
   *
   * @return None
   * @access public
   */
  public function buildQuickForm() {
    parent::buildQuickForm();
    $this->_entityID = CRM_Utils_Request::retrieve('bid' , 'Positive');

    $this->addButtons(
      array(
        array('type' => 'submit',
          'name' => ts('Search'),
          'isDefault' => true   
        )
      )
    );
    $this->addElement('checkbox', 'toggleSelect', null, null,
      array('onclick' => "toggleTaskAction( true ); return toggleCheckboxVals('mark_x_',this);"));
    $this->applyFilter('__ALL__', 'trim');

    $this->addElement('hidden', 'batch_id' ,'11');

    $this->add('text', 'name', ts('Batch Name'));

  }


  /**
   * Function to process the form
   *
   * @access public
   * @return None
   */
  public function postProcess() {

    $contactID = CRM_Utils_Type::escape(1, 'Integer');
    $context   = CRM_Utils_Type::escape('batch', 'String');
  
    $params['batch_id'] = $_POST['batch_id'];
    $params['contact_id'] = $contactID;
    $params['context'   ] = $context;

    $this->_returnvalues = array(
      'civicrm_financial_item.contact_id',
      'sort_name',
      'amount',
      'contact_type',
      'contact_sub_type',
      'transaction_date',
      'name'
    );
    $this->_columnHeader = array(
      'contact_type' => '',
      'sort_name' => ts('Contact Name'),
      'amount' => ts('Amount'),
      'transaction_date' => ts('Received'),
      'name' => ts('Type')
    );

    $financialItem = CRM_Financial_BAO_EntityFinancialItem::getBatchFinancialItems($this->_entityID, $this->_returnvalues, $notPresent = 1);

    while ($financialItem->fetch()) {
      $row = array();
      foreach ($this->_columnHeader as $columnKey => $columnValue) {
        if ($financialItem->contact_sub_type && $columnKey == 'contact_type') {
          $row[$columnKey] = $financialItem->contact_sub_type;
          continue;
        }
        $row[$columnKey] = $financialItem->$columnKey;
      }
      $row['checkbox'] = CRM_Core_Form::CB_PREFIX . $financialItem->id;

      $row['action'] = CRM_Core_Action::formLink( self::links(), null, array('id' => $financialItem->id));
      $row['contact_type' ] = CRM_Contact_BAO_Contact_Utils::getImage(CRM_Utils_Array::value('contact_sub_type',$row) ?
                              CRM_Utils_Array::value('contact_sub_type',$row) : CRM_Utils_Array::value('contact_type',$row) ,false, $financialItem->contact_id);
      $this->_searchRows[] = $row;
      $this->addElement('checkbox', $row['checkbox'],
        null, null,
        array('onclick' => "toggleTaskAction( true ); return checkSelectedBox('" . $row['checkbox'] . "');"));
    }
    $this->assign('searchColumnHeader', $this->_columnHeader);
    $this->assign('searchRows',  $this->_searchRows);

  }

  function &links() {
    if (!(self::$_links)) {
      self::$_links = array(
        'view'  => array(
          'name'  => ts('View'),
          'url'   => 'civicrm/admin/financial/financialType/accounts',
          'qs'    => 'reset=1&action=browse&aid=%%id%%',
          'title' => ts('Accounts'),
        ),
        'assign' => array(
          'name'  => ts('Assign'),
          'ref'   => 'disable-action',
          'title' => ts('Disable Financial Type'),
          'extra' => 'onclick = "assignRemove( %%id%%,\'' . 'assign' . '\' );"',
        )
      );
    }
    return self::$_links;
  }
}


