<?php

/*
  +--------------------------------------------------------------------+
  | CiviCRM version 4.3                                                |
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
 * Page for displaying list of financial batches
 */
class CRM_Financial_Page_BatchTransaction extends CRM_Core_Page_Basic {
  /**
   * The action links that we need to display for the browse screen
   *
   * @var array
   * @static
   */
  static $_links = null;
  static $_entityID;

  static $_columnHeader = null;
  static $_returnvalues = null;
  /**
   * Get BAO Name
   *
   * @return string Classname of BAO.
   */
  function getBAOName() {
    return 'CRM_Core_BAO_Batch';
  }

  /**
   * Get action Links
   *
   * @return array (reference) of action links
   */
  function &links() {
    if (!(self::$_links)) {
      self::$_links = array(
        'view'  => array(
          'name'  => ts('View'),
          'url'   => 'civicrm/contact/view/contribution',
          'qs'    => 'reset=1&id=%%contid%%&cid=%%cid%%&action=view&context=contribution&selectedChild=contribute',
          'title' => ts('Accounts'),
        ),
        'remove'  => array(
          'name'  => ts('Remove'),
          'title' => ts('Edit Financial Type'),
          'extra' => 'onclick = "assignRemove( %%id%%,\'' . 'remove' . '\' );"',
        )
      );
    }
    return self::$_links;
  }

  /**
   * Run the page.
   *
   * This method is called after the page is created. It checks for the
   * type of action and executes that action.
   * Finally it calls the parent's run method.
   *
   * @return void
   * @access public
   *
   */
  function run() {
    // get the requested action
    $action = CRM_Utils_Request::retrieve('action', 'String', $this, false, 'browse'); // default to 'browse'

    // assign vars to templates
    $this->assign('action', $action);

    $this->_entityID = CRM_Utils_Request::retrieve( 'bid' , 'Positive' );

    $this->edit($action, $this->_entityID ) ;
    return parent::run();
  }

  /**
   * Browse all financial batch transactions
   *
   *
   * @return void
   * @access public
   * @static
   */
  function browse() {
    $financialitems = array();
    $this->_returnvalues = array( 
      'civicrm_financial_item.contact_id',
      'civicrm_contribution.id as contributionID',
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
      'amount'   => ts('Amount'),
      'transaction_date' => ts('Received'),
      'name' => ts('Type')
    );
    $this->_entityID = 1;
    $this->assign('entityID', $this->_entityID);
    $financialItem = CRM_Financial_BAO_EntityFinancialItem::getBatchFinancialItems($this->_entityID, $this->_returnvalues);
    $financialitems = array();
    while ($financialItem->fetch()) {
      $row = array();
      foreach ($this->_columnHeader as $columnKey => $columnValue) {
        if ($financialItem->contact_sub_type && $columnKey == 'contact_type') {
          $row[$columnKey] = $financialItem->contact_sub_type;
          continue;
        }
        $row[$columnKey] = $financialItem->$columnKey;
      }
      $row['checkbox'] = 'mark_y_'. $financialItem->id;
      $row['action'] = CRM_Core_Action::formLink(self::links(), null, array('id' => $financialItem->id, 'contid' => $financialItem->contributionID, 'cid' => $financialItem->contact_id));
      $row['contact_type' ] = CRM_Contact_BAO_Contact_Utils::getImage( CRM_Utils_Array::value('contact_sub_type',$row) ?
                              CRM_Utils_Array::value('contact_sub_type',$row) : CRM_Utils_Array::value('contact_type',$row) ,false, $financialItem->contact_id);
      $financialitems[] = $row;
    }
    $this->assign('columnHeader', $this->_columnHeader);
    $this->assign('rows', $financialitems);
  }

  /**
   * Get name of edit form
   *
   * @return string Classname of edit form.
   */
  function editForm() {
    return 'CRM_Financial_Form_BatchTransaction';
  }

  /**
   * Get edit form name
   *
   * @return string name of this page.
   */
  function editName() {
    return 'Batch';
  }

  /**
   * Get user context.
   *
   * @return string user context.
   */
  function userContext($mode = null) {
    return 'civicrm/batchtransaction';
  }

}


