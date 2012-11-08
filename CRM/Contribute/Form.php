<?php
/*
 +--------------------------------------------------------------------+
 | CiviCRM version 4.2                                                |
 +--------------------------------------------------------------------+
 | Copyright CiviCRM LLC (c) 2004-2012                                |
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
 * @copyright CiviCRM LLC (c) 2004-2012
 * $Id$
 *
 */

/**
 * This class generates form components generic to Mobile provider
 *
 */
class CRM_Contribute_Form extends CRM_Core_Form {

  /**
   * The id of the object being edited / created
   *
   * @var int
   */
  protected $_id;

  /**
   * The name of the BAO object for this form
   *
   * @var string
   */
  protected $_BAOName; function preProcess() {
    $this->_id = $this->get('id');
    $this->_BAOName = $this->get('BAOName');
  }

  /**
   * This function sets the default values for the form. MobileProvider that in edit/view mode
   * the default values are retrieved from the database
   *
   * @access public
   *
   * @return None
   */
  function setDefaultValues() {
        require_once 'CRM/Utils/Array.php';
    $defaults = array();
    $params = array();


    if (isset($this->_id)) {
      $params = array('id' => $this->_id);
            if( !empty( $this->_BAOName ) ){
      require_once (str_replace('_', DIRECTORY_SEPARATOR, $this->_BAOName) . ".php");
      eval($this->_BAOName . '::retrieve( $params, $defaults );');
    }
        }
        if ($this->_action == CRM_Core_Action::DELETE && CRM_Utils_Array::value('name', $defaults) ) {
      $this->assign('delName', $defaults['name']);
    }
    elseif ($this->_action == CRM_Core_Action::ADD) {
            $condition = " AND is_default = 1";
            $values = CRM_Core_OptionGroup::values( 'financial_account_type', false, false, false, $condition );
            $defaults['financial_account_type_id'] = array_keys( $values );
      $defaults['is_active'] = 1;
        }elseif ($this->_action & CRM_Core_Action::UPDATE){
            $organisationId = CRM_Core_DAO::getFieldValue( 'CRM_Financial_DAO_FinancialAccount', $this->_id, 'contact_id' );
            if( !empty( $organisationId ) ){
                $contactParams = array( 'id'      => $organisationId,
                                        'version' => 3, 
                                        );
                $contactName = civicrm_api( 'Contact', 'Get', $contactParams);
                $defaults['organisation_name'] = $contactName['values'][$organisationId]['sort_name'];
                $defaults['parent_id'] = $contactName['id'];
            }
            $parentId = CRM_Core_DAO::getFieldValue( 'CRM_Financial_DAO_FinancialAccount', $this->_id, 'parent_id' );
            $this->assign('parentId', $parentId ); 
    }

    return $defaults;
  }

  /**
   * Function to actually build the form
   *
   * @return None
   * @access public
   */
  public function buildQuickForm() {
    $this->addButtons(array(
        array(
          'type' => 'next',
          'name' => ts('Save'),
          'isDefault' => TRUE,
        ),
        array(
          'type' => 'cancel',
          'name' => ts('Cancel'),
        ),
      )
    );

    if ($this->_action & CRM_Core_Action::DELETE) {
      $this->addButtons(array(
          array(
            'type' => 'next',
            'name' => ts('Delete'),
            'isDefault' => TRUE,
          ),
          array(
            'type' => 'cancel',
            'name' => ts('Cancel'),
          ),
        )
      );
    }
  }
}

