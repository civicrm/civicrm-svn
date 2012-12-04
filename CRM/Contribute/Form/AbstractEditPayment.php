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
 | CiviCRM is distributed in the hope that it will be usefusul, but   |
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
 * This class generates form components for processing a contribution
 *
 */
class CRM_Contribute_Form_AbstractEditPayment extends CRM_Core_Form {
  public $_mode;

  public $_action;

  public $_bltID;

  public $_fields;

  public $_paymentProcessor;
  public $_recurPaymentProcessors;

  public $_processors;

  /**
   * the id of the contribution that we are proceessing
   *
   * @var int
   * @public
   */
  public $_id;

  /**
   * the id of the premium that we are proceessing
   *
   * @var int
   * @public
   */
  public $_premiumID = NULL;
  public $_productDAO = NULL;

  /**
   * the id of the note
   *
   * @var int
   * @public
   */
  public $_noteID;

  /**
   * the id of the contact associated with this contribution
   *
   * @var int
   * @public
   */
  public $_contactID;

  /**
   * the id of the pledge payment that we are processing
   *
   * @var int
   * @public
   */
  public $_ppID;

  /**
   * the id of the pledge that we are processing
   *
   * @var int
   * @public
   */
  public $_pledgeID;

  /**
   * is this contribution associated with an online
   * financial transaction
   *
   * @var boolean
   * @public
   */
  public $_online = FALSE;

  /**
   * Stores all product option
   *
   * @var array
   * @public
   */
  public $_options;

  /**
   * stores the honor id
   *
   * @var int
   * @public
   */
  public $_honorID = NULL;

  /**
   * Store the contribution Type ID
   *
   * @var array
   */
  public $_contributionType;

  /**
   * The contribution values if an existing contribution
   */
  public $_values;

  /**
   * The pledge values if this contribution is associated with pledge
   */
  public $_pledgeValues;

  public $_contributeMode = 'direct';

  public $_context;

  public $_compId;

  /*
   * Store the line items if price set used.
   */
  public $_lineItems;

  protected $_formType;
  protected $_cdType;

  public function buildValuesAndAssignOnline_Note_Type($id, &$values) {
    $ids = array();
    $params = array('id' => $id);
    CRM_Contribute_BAO_Contribution::getValues($params, $values, $ids);

    //do check for online / recurring contributions
    $fids = CRM_Core_BAO_FinancialTrxn::getFinancialTrxnIds($id, 'civicrm_contribution');
    $this->_online = CRM_Utils_Array::value('entityFinancialTrxnId', $fids);
    //don't allow to update all fields for recuring contribution.
    if (!$this->_online) {
      $this->_online = CRM_Utils_Array::value('contribution_recur_id', $values);
    }
    $this->assign('isOnline', $this->_online ? TRUE : FALSE);

    //unset the honor type id:when delete the honor_contact_id
    //and edit the contribution, honoree infomation pane open
    //since honor_type_id is present
    if (!CRM_Utils_Array::value('honor_contact_id', $values)) {
      unset($values['honor_type_id']);
    }
    //to get note id
    $daoNote = new CRM_Core_BAO_Note();
    $daoNote->entity_table = 'civicrm_contribution';
    $daoNote->entity_id = $id;
    if ($daoNote->find(TRUE)) {
      $this->_noteID = $daoNote->id;
      $values['note'] = $daoNote->note;
    }
    $this->_contributionType = $values['financial_type_id'];

    $csParams = array('contribution_id' => $id);
    $softCredit = CRM_Contribute_BAO_Contribution::getSoftContribution($csParams, TRUE);

    if (CRM_Utils_Array::value('soft_credit_to', $softCredit)) {
      $softCredit['sort_name'] = CRM_Core_DAO::getFieldValue('CRM_Contact_DAO_Contact',
        $softCredit['soft_credit_to'], 'sort_name'
      );
    }
    $values['soft_credit_to'] = CRM_Utils_Array::value('sort_name', $softCredit);
    $values['softID'] = CRM_Utils_Array::value('soft_credit_id', $softCredit);
    $values['soft_contact_id'] = CRM_Utils_Array::value('soft_credit_to', $softCredit);

    if (CRM_Utils_Array::value('pcp_id', $softCredit)) {
      $pcpId = CRM_Utils_Array::value('pcp_id', $softCredit);
      $pcpTitle = CRM_Core_DAO::getFieldValue('CRM_PCP_DAO_PCP', $pcpId, 'title');
      $contributionPageTitle = CRM_PCP_BAO_PCP::getPcpPageTitle($pcpId, 'contribute');
      $values['pcp_made_through'] = CRM_Utils_Array::value('sort_name', $softCredit) . " :: " . $pcpTitle . " :: " . $contributionPageTitle;
      $values['pcp_made_through_id'] = CRM_Utils_Array::value('pcp_id', $softCredit);
      $values['pcp_display_in_roll'] = CRM_Utils_Array::value('pcp_display_in_roll', $softCredit);
      $values['pcp_roll_nickname'] = CRM_Utils_Array::value('pcp_roll_nickname', $softCredit);
      $values['pcp_personal_note'] = CRM_Utils_Array::value('pcp_personal_note', $softCredit);
    }
  }

  public function assignPremiumProduct($id) { //to get Premium id
    $sql = "
SELECT *
FROM   civicrm_contribution_product
WHERE  contribution_id = {$id}
";
    $dao = CRM_Core_DAO::executeQuery($sql,
      CRM_Core_DAO::$_nullArray
    );
    if ($dao->fetch()) {
      $this->_premiumID = $dao->id;
      $this->_productDAO = $dao;
    }
    $dao->free();
  }

  /**
   * This function process contribution related objects.
   */
  protected function updateRelatedComponent($contributionId, $statusId, $previousStatusId = NULL) {
    $statusMsg = NULL;
    if (!$contributionId || !$statusId) {
      return $statusMsg;
    }

    $params = array(
      'contribution_id' => $contributionId,
      'contribution_status_id' => $statusId,
      'previous_contribution_status_id' => $previousStatusId,
    );

    $updateResult = CRM_Contribute_BAO_Contribution::transitionComponents($params);

    if (!is_array($updateResult) ||
      !($updatedComponents = CRM_Utils_Array::value('updatedComponents', $updateResult)) ||
      !is_array($updatedComponents) ||
      empty($updatedComponents)
    ) {
      return $statusMsg;
    }

    // get the user display name.
    $sql = "
   SELECT  display_name as displayName
     FROM  civicrm_contact
LEFT JOIN  civicrm_contribution on (civicrm_contribution.contact_id = civicrm_contact.id )
    WHERE  civicrm_contribution.id = {$contributionId}";
    $userDisplayName = CRM_Core_DAO::singleValueQuery($sql);

    // get the status message for user.
    foreach ($updatedComponents as $componentName => $updatedStatusId) {

      if ($componentName == 'CiviMember') {
        $updatedStatusName = CRM_Utils_Array::value($updatedStatusId,
          CRM_Member_PseudoConstant::membershipStatus()
        );
        if ($updatedStatusName == 'Cancelled') {
          $statusMsg .= ts("<br />Membership for %1 has been Cancelled.", array(1 => $userDisplayName));
        }
        elseif ($updatedStatusName == 'Expired') {
          $statusMsg .= ts("<br />Membership for %1 has been Expired.", array(1 => $userDisplayName));
        }
        elseif ($endDate = CRM_Utils_Array::value('membership_end_date', $updateResult)) {
          $statusMsg .= ts("<br />Membership for %1 has been updated. The membership End Date is %2.",
            array(
              1 => $userDisplayName,
              2 => $endDate,
            )
          );
        }
      }

      if ($componentName == 'CiviEvent') {
        $updatedStatusName = CRM_Utils_Array::value($updatedStatusId,
          CRM_Event_PseudoConstant::participantStatus()
        );
        if ($updatedStatusName == 'Cancelled') {
          $statusMsg .= ts("<br />Event Registration for %1 has been Cancelled.", array(1 => $userDisplayName));
        }
        elseif ($updatedStatusName == 'Registered') {
          $statusMsg .= ts("<br />Event Registration for %1 has been updated.", array(1 => $userDisplayName));
        }
      }

      if ($componentName == 'CiviPledge') {
        $updatedStatusName = CRM_Utils_Array::value($updatedStatusId,
          CRM_Contribute_PseudoConstant::contributionStatus(NULL, 'name')
        );
        if ($updatedStatusName == 'Cancelled') {
          $statusMsg .= ts("<br />Pledge Payment for %1 has been Cancelled.", array(1 => $userDisplayName));
        }
        elseif ($updatedStatusName == 'Failed') {
          $statusMsg .= ts("<br />Pledge Payment for %1 has been Failed.", array(1 => $userDisplayName));
        }
        elseif ($updatedStatusName == 'Completed') {
          $statusMsg .= ts("<br />Pledge Payment for %1 has been updated.", array(1 => $userDisplayName));
        }
      }
    }

    return $statusMsg;
  }

  /**
   * @return array (int $ppId => string $label)
   */
  public function getValidProcessorsAndAssignFutureStartDate() {
    $validProcessors = array();
    $processors = CRM_Core_PseudoConstant::paymentProcessor(FALSE, FALSE, "billing_mode IN ( 1, 3 )");

    foreach ($processors as $ppID => $label) {
      $paymentProcessor = CRM_Core_BAO_PaymentProcessor::getPayment($ppID, $this->_mode);
      // at this stage only Authorize.net has been tested to support future start dates so if it's enabled let the template know
      // to show receive date
      $processorsSupportingFutureStartDate = array('AuthNet');
      if (in_array($paymentProcessor['payment_processor_type'], $processorsSupportingFutureStartDate)) {
        $this->assign('processorSupportsFutureStartDate', TRUE);
      }
      if ($paymentProcessor['payment_processor_type'] == 'PayPal' && !$paymentProcessor['user_name']) {
        continue;
      }
      elseif ($paymentProcessor['payment_processor_type'] == 'Dummy' && $this->_mode == 'live') {
        continue;
      }
      else {
        $paymentObject = CRM_Core_Payment::singleton($this->_mode, $paymentProcessor, $this);
        $error = $paymentObject->checkConfig();
        if (empty($error)) {
          $validProcessors[$ppID] = $label;
        }
        $paymentObject = NULL;
      }
    }
    if (empty($validProcessors)) {
      CRM_Core_Error::fatal(ts('You will need to configure the %1 settings for your Payment Processor before you can submit credit card transactions.', array(1 => $this->_mode)));
    }
    else {
      return array($validProcessors, $paymentProcessor);
    }
  }

  /**
   * Assign billing type id to bltID
   *
   * @return void
   */
  public function assignBillingType() {
    $locationTypes = CRM_Core_PseudoConstant::locationType();
    $this->_bltID = array_search('Billing', $locationTypes);
    if (!$this->_bltID) {
      CRM_Core_Error::fatal(ts('Please set a location type of %1', array(1 => 'Billing')));
    }
    $this->set('bltID', $this->_bltID);
    $this->assign('bltID', $this->_bltID);
  }

}