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
 | CiviCRM is distributed in the hope that it will be usefusul, but     |
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
class CRM_Contribute_Form_Contribution extends CRM_Contribute_Form_AbstractEditPayment {
  const FA_ASSET_ACCOUNT_RELATION = 6;

  /**
   * Function to set variables up before form is built
   *
   * @return void
   * @access public
   */
  public function preProcess() {
    //check permission for action.
    if (!CRM_Core_Permission::checkActionPermission('CiviContribute', $this->_action)) {
      CRM_Core_Error::fatal(ts('You do not have permission to access this page'));
    }

    $this->_cdType = CRM_Utils_Array::value('type', $_GET);

    $this->assign('cdType', FALSE);
    if ($this->_cdType) {
      $this->assign('cdType', TRUE);
      CRM_Custom_Form_CustomData::preProcess($this);
      return;
    }

    $this->_formType = CRM_Utils_Array::value('formType', $_GET);

    // get price set id.
    $this->_priceSetId = CRM_Utils_Array::value('priceSetId', $_GET);
    $this->set('priceSetId', $this->_priceSetId);
    $this->assign('priceSetId', $this->_priceSetId);

    //get the pledge payment id
    $this->_ppID = CRM_Utils_Request::retrieve('ppid', 'Positive', $this);

    //get the contact id
    $this->_contactID = CRM_Utils_Request::retrieve('cid', 'Positive', $this);

    //get the action.
    $this->_action = CRM_Utils_Request::retrieve('action', 'String', $this, FALSE, 'add');
    $this->assign('action', $this->_action);

    //get the contribution id if update
    $this->_id = CRM_Utils_Request::retrieve('id', 'Positive', $this);
    if (!empty($this->_id)) {
      $this->assign('contribID', $this->_id);
    }

    $this->_context = CRM_Utils_Request::retrieve('context', 'String', $this);
    $this->assign('context', $this->_context);

    $this->_compId = CRM_Utils_Request::retrieve('compId', 'Positive', $this);

    $this->_compContext = CRM_Utils_Request::retrieve('compContext', 'String', $this);

    //set the contribution mode.
    $this->_mode = CRM_Utils_Request::retrieve('mode', 'String', $this);

    $this->assign('contributionMode', $this->_mode);

    $this->_paymentProcessor = array('billing_mode' => 1);

    $this->assign('showCheckNumber', TRUE);

    $this->_fromEmails = CRM_Core_BAO_Email::getFromEmail();
    $this->assignProcessors();

    if ($this->_contactID) {
      list($this->userDisplayName, $this->userEmail) = CRM_Contact_BAO_Contact_Location::getEmailDetails($this->_contactID);
      $this->assign('displayName', $this->userDisplayName);
    }

    // also check for billing information
    // get the billing location type
    $this->assignBillingType();

    $this->_fields = array();

    CRM_Core_Payment_Form::setPaymentFieldsByType(CRM_Utils_Array::value('payment_type', $this->_processors), $this);

    if ($this->_action & CRM_Core_Action::DELETE) {
      return;
    }

    $config = CRM_Core_Config::singleton();
    if (in_array('CiviPledge', $config->enableComponents) && !$this->_formType) {
      $this->preProcessPledge();
    }

    $this->_values = array();

    // current contribution id
    if ($this->_id) {
      $this->assignPremiumProduct($this->_id);
      $this->buildValuesAndAssignOnline_Note_Type($this->_id, $this->_values);
    }

    // when custom data is included in this page
    if (CRM_Utils_Array::value('hidden_custom', $_POST)) {
      $this->applyCustomData('Contribution', CRM_Utils_Array::value('financial_type_id', $_POST), $this->_id);
    }

    $this->_lineItems = array();
    if ($this->_id) {
      if (empty($this->_compId)) {
        $lineItem = CRM_Price_BAO_LineItem::getLineItems($this->_id, 'contribution', 1);
      }
      else {
        $this->assign('compId', $this->_compId);
        $lineItem = CRM_Price_BAO_LineItem::getLineItems($this->_compId);
      }
      empty($lineItem) ? NULL : $this->_lineItems[] = $lineItem;
    }
    $this->assign('lineItem', empty($this->_lineItems) ? FALSE : $this->_lineItems);
  }

  function setDefaultValues() {
    if ($this->_cdType) {
      return CRM_Custom_Form_CustomData::setDefaultValues($this);
    }

    $defaults = $this->_values;

    //set defaults for pledge payment.
    if ($this->_ppID) {
      $defaults['total_amount'] = CRM_Utils_Array::value('scheduled_amount', $this->_pledgeValues['pledgePayment']);
      $defaults['honor_type_id'] = CRM_Utils_Array::value('honor_type_id', $this->_pledgeValues);
      $defaults['honor_contact_id'] = CRM_Utils_Array::value('honor_contact_id', $this->_pledgeValues);
      $defaults['financial_type_id'] = CRM_Utils_Array::value('financial_type_id', $this->_pledgeValues);
      $defaults['currency'] = CRM_Utils_Array::value('currency', $this->_pledgeValues);
      $defaults['option_type'] = 1;
    }

    if ($this->_action & CRM_Core_Action::UPDATE) {
      $financialTrxnId = CRM_Core_BAO_FinancialTrxn::getFinancialTrxnIds($defaults['contribution_id']);
      if (CRM_Utils_Array::value('financialTrxnId', $financialTrxnId)) {
        $defaults['to_financial_account_id'] = CRM_Core_DAO::getFieldValue('CRM_Financial_DAO_FinancialTrxn', $financialTrxnId['financialTrxnId'], 'to_financial_account_id');
      }
    }

    $fields = array();
    if ($this->_action & CRM_Core_Action::DELETE) {
      return $defaults;
    }

    if ($this->_mode) {
      $billingFields = array();
      foreach ($this->_fields as $name => $dontCare) {
        if (strpos($name, 'billing_') === 0) {
          $name = $idName = substr($name, 8);
          if (in_array($name, array(
            "state_province_id-$this->_bltID",
            "country_id-$this->_bltID"
          ))
          ) {
            $name = str_replace('_id', '', $name);
          }
          $billingFields[$name] = 'billing_' . $idName;
        }
        $fields[$name] = 1;
      }

      if ($this->_contactID) {
        CRM_Core_BAO_UFGroup::setProfileDefaults($this->_contactID, $fields, $defaults);
      }
      foreach ($billingFields as $name => $billingName) {
        $defaults[$billingName] = CRM_Utils_Array::value($name, $defaults);
      }

      $config = CRM_Core_Config::singleton();
      // set default country from config if no country set
      if (!CRM_Utils_Array::value("billing_country_id-{$this->_bltID}", $defaults)) {
        $defaults["billing_country_id-{$this->_bltID}"] = $config->defaultContactCountry;
      }

      // now fix all state country selectors
      CRM_Core_BAO_Address::fixAllStateSelects($this, $defaults);
    }

    if ($this->_id) {
      $this->_contactID = $defaults['contact_id'];
    }

    // fix the display of the monetary value, CRM-4038
    if (isset($defaults['total_amount'])) {
      $defaults['total_amount'] = CRM_Utils_Money::format($defaults['total_amount'], NULL, '%a');
    }

    if (isset($defaults['non_deductible_amount'])) {
      $defaults['non_deductible_amount'] = CRM_Utils_Money::format($defaults['non_deductible_amount'], NULL, '%a');
    }

    if (isset($defaults['fee_amount'])) {
      $defaults['fee_amount'] = CRM_Utils_Money::format($defaults['fee_amount'], NULL, '%a');
    }

    if (isset($defaults['net_amount'])) {
      $defaults['net_amount'] = CRM_Utils_Money::format($defaults['net_amount'], NULL, '%a');
    }

    if ($this->_contributionType) {
      $defaults['financial_type_id'] = $this->_contributionType;
    }

    if (CRM_Utils_Array::value('is_test', $defaults)) {
      $this->assign('is_test', TRUE);
    }

    if (isset($defaults['honor_contact_id'])) {
      $honorDefault = $ids = array();
      $this->_honorID = $defaults['honor_contact_id'];
      $honorType = CRM_Core_PseudoConstant::honor();
      $idParams = array(
        'id' => $defaults['honor_contact_id'],
        'contact_id' => $defaults['honor_contact_id'],
      );
      CRM_Contact_BAO_Contact::retrieve($idParams, $honorDefault, $ids);

      $defaults['honor_prefix_id'] = CRM_Utils_Array::value('prefix_id', $honorDefault);
      $defaults['honor_first_name'] = CRM_Utils_Array::value('first_name', $honorDefault);
      $defaults['honor_last_name'] = CRM_Utils_Array::value('last_name', $honorDefault);
      $defaults['honor_email'] = CRM_Utils_Array::value('email', $honorDefault['email'][1]);
      $defaults['honor_type'] = $honorType[$defaults['honor_type_id']];
    }

    $this->assign('showOption', TRUE);
    // for Premium section
    if ($this->_premiumID) {
      $this->assign('showOption', FALSE);
      $options = isset($this->_options[$this->_productDAO->product_id]) ? $this->_options[$this->_productDAO->product_id] : "";
      if (!$options) {
        $this->assign('showOption', TRUE);
      }
      $options_key = CRM_Utils_Array::key($this->_productDAO->product_option, $options);
      if ($options_key) {
        $defaults['product_name'] = array($this->_productDAO->product_id, trim($options_key));
      }
      else {
        $defaults['product_name'] = array($this->_productDAO->product_id);
      }
      if ($this->_productDAO->fulfilled_date) {
        list($defaults['fulfilled_date']) = CRM_Utils_Date::setDateDefaults($this->_productDAO->fulfilled_date);
      }
    }

    if (isset($this->userEmail)) {
      $this->assign('email', $this->userEmail);
    }

    if (CRM_Utils_Array::value('is_pay_later', $defaults)) {
      $this->assign('is_pay_later', TRUE);
    }
    $this->assign('contribution_status_id', CRM_Utils_Array::value('contribution_status_id', $defaults));

    $dates = array('receive_date', 'receipt_date', 'cancel_date', 'thankyou_date');
    foreach ($dates as $key) {
      if (CRM_Utils_Array::value($key, $defaults)) {
        list($defaults[$key],
          $defaults[$key . '_time']
          ) = CRM_Utils_Date::setDateDefaults(CRM_Utils_Array::value($key, $defaults),
          'activityDateTime'
        );
      }
    }

    if (!$this->_id && !CRM_Utils_Array::value('receive_date', $defaults)) {
      list($defaults['receive_date'],
        $defaults['receive_date_time']
        ) = CRM_Utils_Date::setDateDefaults(NULL, 'activityDateTime');
    }

    $this->assign('receive_date', CRM_Utils_Date::processDate(CRM_Utils_Array::value('receive_date', $defaults),
      CRM_Utils_Array::value('receive_date_time', $defaults)
    ));
    $currency = CRM_Utils_Array::value('currency', $defaults);
    $this->assign('currency', $currency);
    // Hack to get currency info to the js layer. CRM-11440
    CRM_Utils_Money::format(1);
    $this->assign('currencySymbol', CRM_Utils_Array::value($currency, CRM_Utils_Money::$_currencySymbols));
    $this->assign('totalAmount', CRM_Utils_Array::value('total_amount', $defaults));

    //inherit campaign from pledge.
    if ($this->_ppID && CRM_Utils_Array::value('campaign_id', $this->_pledgeValues)) {
      $defaults['campaign_id'] = $this->_pledgeValues['campaign_id'];
    }

    $this->_defaults = $defaults;
    return $defaults;
  }

  /**
   * Function to build the form
   *
   * @return void
   * @access public
   */
  public function buildQuickForm() {
    if ($this->_cdType) {
      CRM_Custom_Form_CustomData::buildQuickForm($this);
      return;
    }
    if ($this->_action & CRM_Core_Action::ADD) {
      CRM_Price_BAO_Field::initialPayCreate($this, 'contribution', 'offline');
    }

    // build price set form.
    $buildPriceSet = FALSE;
    if (empty($this->_lineItems) &&
      ($this->_priceSetId || CRM_Utils_Array::value('price_set_id', $_POST))
    ) {
      $buildPriceSet = TRUE;
      $getOnlyPriceSetElements = TRUE;
      if (!$this->_priceSetId) {
        $this->_priceSetId = $_POST['price_set_id'];
        $getOnlyPriceSetElements = FALSE;
      }

      $this->set('priceSetId', $this->_priceSetId);
      CRM_Price_BAO_Set::buildPriceSet($this);

      // get only price set form elements.
      if ($getOnlyPriceSetElements) {
        return;
      }
    }
    // use to build form during form rule.
    $this->assign('buildPriceSet', $buildPriceSet);

    $showAdditionalInfo = FALSE;


    $defaults = $this->_values;
    $additionalDetailFields = array(
      'note',
      'thankyou_date',
      'invoice_id',
      'non_deductible_amount',
      'fee_amount',
      'net_amount'
    );
    foreach ($additionalDetailFields as $key) {
      if (!empty($defaults[$key])) {
        $defaults['hidden_AdditionalDetail'] = 1;
        break;
      }
    }

    $honorFields = array(
      'honor_type_id',
      'honor_prefix_id',
      'honor_first_name',
      'honor_lastname',
      'honor_email',
    );
    foreach ($honorFields as $key) {
      if (!empty($defaults[$key])) {
        $defaults['hidden_Honoree'] = 1;
        break;
      }
    }

    //check for honoree pane.
    if ($this->_ppID && CRM_Utils_Array::value('honor_contact_id', $this->_pledgeValues)) {
      $defaults['hidden_Honoree'] = 1;
    }

    if ($this->_productDAO) {
      if ($this->_productDAO->product_id) {
        $defaults['hidden_Premium'] = 1;
      }
    }

    if ($this->_noteID &&
      isset($this->_values['note'])
    ) {
      $defaults['hidden_AdditionalDetail'] = 1;
    }

    $paneNames = array(
      ts('Additional Details') => 'AdditionalDetail',
      ts('Honoree Information') => 'Honoree'
    );

    //Add Premium pane only if Premium is exists.
    $dao = new CRM_Contribute_DAO_Product();
    $dao->is_active = 1;

    if ($dao->find(TRUE)) {
      $paneNames[ts('Premium Information')] = 'Premium';
    }

    $ccPane = NULL;
    if ($this->_mode) {
      if (CRM_Utils_Array::value('payment_type', $this->_processors) & CRM_Core_Payment::PAYMENT_TYPE_DIRECT_DEBIT
      ) {
        $ccPane = array(ts('Direct Debit Information') => 'DirectDebit');
      }
      else {
        $ccPane = array(ts('Credit Card Information') => 'CreditCard');
      }
    }
    if (is_array($ccPane)) {
      $paneNames = array_merge($ccPane, $paneNames);
    }

    $buildRecurBlock = FALSE;
    foreach ($paneNames as $name => $type) {
      $urlParams = "snippet=4&formType={$type}";
      if ($this->_mode) {
        $urlParams .= "&mode={$this->_mode}";
      }

      $open = 'false';
      if ($type == 'CreditCard' ||
        $type == 'DirectDebit'
      ) {
        $open = 'true';
      }

      $allPanes[$name] = array(
        'url' => CRM_Utils_System::url('civicrm/contact/view/contribution', $urlParams),
        'open' => $open,
        'id' => $type
      );

      // see if we need to include this paneName in the current form
      if ($this->_formType == $type ||
        CRM_Utils_Array::value("hidden_{$type}", $_POST) ||
        CRM_Utils_Array::value("hidden_{$type}", $defaults)
      ) {
        $showAdditionalInfo = TRUE;
        $allPanes[$name]['open'] = 'true';
      }

      if ($type == 'CreditCard') {
        $buildRecurBlock = TRUE;
        $this->add('hidden', 'hidden_CreditCard', 1);
        CRM_Core_Payment_Form::buildCreditCard($this, TRUE);
      }
      elseif ($type == 'DirectDebit') {
        $buildRecurBlock = TRUE;
        $this->add('hidden', 'hidden_DirectDebit', 1);
        CRM_Core_Payment_Form::buildDirectDebit($this, TRUE);
      }
      else {
        eval('CRM_Contribute_Form_AdditionalInfo::build' . $type . '( $this );');
      }
    }
    if (empty($this->_recurPaymentProcessors)) {
      $buildRecurBlock = FALSE;
    }
    if ($buildRecurBlock) {
      CRM_Contribute_Form_Contribution_Main::buildRecur($this);
      $this->setDefaults(array('is_recur' => 0));
    }
    $this->assign('buildRecurBlock', $buildRecurBlock);
    $qfKey = $this->controller->_key;
    $this->assign('qfKey', $qfKey);
    $this->assign('allPanes', $allPanes);
    $this->assign('showAdditionalInfo', $showAdditionalInfo);

    if ($this->_formType) {
      $this->assign('formType', $this->_formType);
      return;
    }

    $this->applyFilter('__ALL__', 'trim');

    if ($this->_action & CRM_Core_Action::UPDATE) {
      if (empty($this->_compId)) {
        $lineItem = CRM_Price_BAO_LineItem::getLineItems($this->_id, 'contribution');
        $txrnLineTotal = CRM_Core_BAO_FinancialTrxn::getFinancialTrxnLineTotal($this->_id, 'civicrm_contribution');
      }
      else {
        $lineItem = CRM_Price_BAO_LineItem::getLineItems($this->_compId);
        $txrnLineTotal = CRM_Core_BAO_FinancialTrxn::getFinancialTrxnLineTotal($this->_compId, 'civicrm_participant');
      }

      foreach ($lineItem as $key => $value) {
        $params['entity_id'] = $key;
        $params['entity_table'] = 'civicrm_line_item';
        $financialItem = CRM_Financial_BAO_FinancialItem::retrieve($params, CRM_Core_DAO::$_nullArray);
        $fid[] = $financialItem->id;
      }

      $lineItemTotal = $lineTotal = array();
      if (is_array($txrnLineTotal)) {
        foreach ($txrnLineTotal as $lineKey => $lineValue) {
          $i = $trxAmount = 0;
          foreach ($lineValue as $itemKey => $itemValue) {
            $entityParams['entity_table'] = 'civicrm_financial_item';
            $entityParams['entity_id'] = $fid[$i++];
            $prevAmount = CRM_Financial_BAO_FinancialItem::retrievePreviousAmount($entityParams);
            $lineItemTotal[$itemKey] = $prevAmount;
            if (!array_key_exists($itemKey, $lineItemTotal)) {
              $lineItemTotal[$itemKey] = 0;
            }
          }
          if (!empty($lineTotal)) {
            $lineTotal[] += $prevAmount;
          }
          else {
            $lineTotal[] = $prevAmount;
          }
        }
      }

      $lineTotal = CRM_Utils_Array::value(0, $lineTotal);
      if (!empty($lineKey)) {
        $paramsEntity = array(
          'entity_table' => 'civicrm_financial_trxn',
          'financial_trxn_id' => $lineKey
        );
        $entityFID = CRM_Financial_BAO_FinancialItem::retrieveEntityFinancialTrxn($paramsEntity);
        if (empty($entityFID)) {
          $paramsEntity = array(
            'entity_table' => 'civicrm_contribution',
            'financial_trxn_id' => $lineKey
          );
          $entityTrxn = CRM_Financial_BAO_FinancialItem::retrieveEntityFinancialTrxn($paramsEntity);
        }
        else {
          foreach ($entityFID as $key => $value) {
            $paramsEntity = array(
              'entity_table' => 'civicrm_contribution',
              'financial_trxn_id' => $entityFID[$key]['entity_id']
            );
            $entityTrxn1 = CRM_Financial_BAO_FinancialItem::retrieveEntityFinancialTrxn($paramsEntity);
            $paramsEntity = array(
              'entity_table' => 'civicrm_financial_trxn',
              'entity_id' => $entityFID[$key]['entity_id']
            );
            $entityTrxn = CRM_Financial_BAO_FinancialItem::retrieveEntityFinancialTrxn($paramsEntity);
            $entityTrxn = array_merge_recursive($entityTrxn1, $entityTrxn);
          }
        }

        foreach ($entityTrxn as $key => $value) {
          $trxnParams['id'] = $value['financial_trxn_id'];
          $trxn = CRM_Core_BAO_FinancialTrxn::retrieve($trxnParams, CRM_Core_DAO::$_nullArray);

          $pricefieldTotal['trxn'][$trxn->id]['trxn_total'] = $value['amount'];
          $pricefieldTotal['trxn'][$trxn->id]['trxn_date'] = $trxn->trxn_date;
          if (!empty($trxn->trxn_type)) {
            $pricefieldTotal['trxn'][$trxn->id]['trxn_type'] = $trxn->trxn_type;
          }
        }

        $pricefieldTotal['lineItems'] = $lineItemTotal;
        $pricefieldTotal['total'] = 0;
        foreach ($pricefieldTotal['lineItems'] as $key => $value) {
          $pricefieldTotal['total'] += $value;
        }
        $this->assign('pricefieldTotal', $pricefieldTotal);
      }

      if (empty($this->_lineItems)) {
        $element = $this->addElement('text', 'paid', 'Paid', array(
          'READONLY' => TRUE,
          'style' => "background-color:#EBECE4",
          'size' => 4,
          'price' => $lineTotal
        ));
        $element->setValue($lineTotal);
        $owingAmount = (float) $this->_values['total_amount'] - $lineTotal;
        $element = $this->addElement('text', 'owing', 'Owing', array(
          'READONLY' => TRUE,
          'style' => "background-color:#EBECE4",
          'size' => 4
        ));

        $element->setValue($owingAmount);
        $this->addElement('text', '', 'Owing', array(
          'READONLY' => TRUE,
          'style' => "background-color:#EBECE4",
          'price' => $owingAmount,
          'size' => 4
        ));
        $this->addElement('checkbox', 'pay_full', ts('Pay Full'), '', array('class' => 'payfull'));
        $this->addElement('text', 'initial_amount', 'Amount of Current Payment Contribution Total', array(
          'size' => 4
        ));
      }

      $this->addElement('checkbox', 'ch_price', ts('Send Receipt?'));
    }
    $this->addElement('text', 'initial_amount', 'Payment Amount', array('size' => 4));

    if ($this->_action & CRM_Core_Action::DELETE) {
      $this->addButtons(array(
          array(
            'type' => 'next',
            'name' => ts('Delete'),
            'spacing' => '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;',
            'isDefault' => TRUE,
          ),
          array(
            'type' => 'cancel',
            'name' => ts('Cancel'),
          )
        )
      );
      return;
    }

    //need to assign custom data type and subtype to the template
    $this->assign('customDataType', 'Contribution');
    $this->assign('customDataSubType', $this->_contributionType);
    $this->assign('entityID', $this->_id);

    if ($this->_context == 'standalone') {
      CRM_Contact_Form_NewContact::buildQuickForm($this);
    }

    $attributes = CRM_Core_DAO::getAttribute('CRM_Contribute_DAO_Contribution');

    $this->add('select', 'financial_type_id',
      ts('Financial Type'),
      array('' => ts('- select -')) + CRM_Contribute_PseudoConstant::financialType(),
      TRUE
    );

    if (!$this->_mode) {
      $this->add('select', 'payment_instrument_id',
        ts('Paid By'),
        array('' => ts('- select -')) + CRM_Contribute_PseudoConstant::paymentInstrument(),
        FALSE, array('onChange' => "return showHideByValue('payment_instrument_id','4','checkNumber','table-row','select',false);")
      );
    }

    $this->add('text', 'trxn_id', ts('Transaction ID'), $attributes['trxn_id']);

    //add receipt for offline contribution
    $this->addElement('checkbox', 'is_email_receipt', ts('Send Receipt?'));

    $this->add('select', 'from_email_address', ts('Receipt From'), $this->_fromEmails);

    $status = CRM_Contribute_PseudoConstant::contributionStatus();
    // suppressing contribution statuses that are NOT relevant to pledges (CRM-5169)
    if ($this->_ppID) {
      $statusName = CRM_Contribute_PseudoConstant::contributionStatus(NULL, 'name');
      foreach (array(
                 'Cancelled',
                 'Failed',
                 'In Progress'
               ) as $suppress) {
        unset($status[CRM_Utils_Array::key($suppress, $statusName)]);
      }
    }

    $this->add('select', 'contribution_status_id',
      ts('Contribution Status'),
      $status,
      FALSE, array(
        'onClick' => "if (this.value != 3) {  status();} else return false",
        'onChange' => "return showHideByValue('contribution_status_id','3','cancelInfo','table-row','select',false);",
      )
    );

    // add various dates
    $this->addDateTime('receive_date', ts('Received'), FALSE, array('formatType' => 'activityDateTime'));

    if ($this->_online) {
      $this->assign('hideCalender', TRUE);
    }
    $this->add('text', 'check_number', ts('Check Number'), $attributes['check_number']);

    $this->addDateTime('receipt_date', ts('Receipt Date'), FALSE, array('formatType' => 'activityDateTime'));
    $this->addDateTime('cancel_date', ts('Cancelled Date'), FALSE, array('formatType' => 'activityDateTime'));

    $this->add('textarea', 'cancel_reason', ts('Cancellation Reason'), $attributes['cancel_reason']);

    $recurJs = NULL;
    if ($buildRecurBlock) {
      $recurJs = array('onChange' => "buildRecurBlock( this.value ); return false;");
    }
    $element = $this->add('select',
      'payment_processor_id',
      ts('Payment Processor'),
      $this->_processors,
      NULL,
      $recurJs
    );

    if ($this->_online) {
      $element->freeze();
    }

    if (empty($this->_lineItems)) {
      $buildPriceSet = FALSE;
      $priceSets = CRM_Price_BAO_Set::getAssoc(FALSE, 'CiviContribute');
      if (!empty($priceSets) && !$this->_ppID) {
        $buildPriceSet = TRUE;
      }

      // don't allow price set for contribution if it is related to participant, or if it is a pledge payment
      // and if we already have line items for that participant. CRM-5095
      if ($buildPriceSet && $this->_id) {
        $componentDetails = CRM_Contribute_BAO_Contribution::getComponentDetails($this->_id);
        $pledgePaymentId = CRM_Core_DAO::getFieldValue('CRM_Pledge_DAO_PledgePayment',
          $this->_id,
          'id',
          'contribution_id'
        );
        if ($pledgePaymentId) {
          $buildPriceSet = FALSE;
        }
        if ($participantID = CRM_Utils_Array::value('participant', $componentDetails)) {
          $participantLI = CRM_Price_BAO_LineItem::getLineItems($participantID);
          if (!CRM_Utils_System::isNull($participantLI)) {
            $buildPriceSet = FALSE;
          }
        }
      }

      $hasPriceSets = FALSE;
      if ($buildPriceSet) {
        $hasPriceSets = TRUE;
        $element = $this->add('select', 'price_set_id', ts('Choose price set'),
          array(
            '' => ts('Choose price set')
          ) + $priceSets,
          NULL, array('onchange' => "buildAmount( this.value );")
        );
        if ($this->_online) {
          $element->freeze();
        }
      }
      $this->assign('hasPriceSets', $hasPriceSets);
      $currencyFreeze = FALSE;
      if ($this->_online || $this->_ppID) {
        $attributes['total_amount'] = array_merge($attributes['total_amount'], array(
          'READONLY' => TRUE,
          'style' => "background-color:#EBECE4"
        ));
        $optionTypes = array(
          '1' => ts('Adjust Pledge Payment Schedule?'),
          '2' => ts('Adjust Total Pledge Amount?'),
        );
        $this->addRadio('option_type',
          NULL,
          $optionTypes,
          array(), '<br/>'
        );

        $currencyFreeze = TRUE;
      }

      $this->addMoney('total_amount',
        ts('Total Amount'),
        ($hasPriceSets) ? FALSE : TRUE,
        $attributes['total_amount'],
        TRUE, 'currency', NULL, $currencyFreeze
      );
    }

    $this->add('text', 'source', ts('Source'), CRM_Utils_Array::value('source', $attributes));

    //CRM-7362 --add campaigns.
    CRM_Campaign_BAO_Campaign::addCampaign($this, CRM_Utils_Array::value('campaign_id', $this->_values));

    // CRM-7368 allow user to set or edit PCP link for contributions
    $siteHasPCPs = CRM_Contribute_PseudoConstant::pcPage();
    if (!CRM_Utils_Array::crmIsEmptyArray($siteHasPCPs)) {
      $this->assign('siteHasPCPs', 1);
      $pcpDataUrl = CRM_Utils_System::url('civicrm/ajax/rest',
        "className=CRM_Contact_Page_AJAX&fnName=getPCPList&json=1&context=contact&reset=1",
        FALSE, NULL, FALSE
      );
      $this->assign('pcpDataUrl', $pcpDataUrl);
      $this->addElement('text', 'pcp_made_through', ts('Credit to a Personal Campaign Page'));
      $this->addElement('hidden', 'pcp_made_through_id', '', array('id' => 'pcp_made_through_id'));
      $this->addElement('checkbox', 'pcp_display_in_roll', ts('Display in Honor Roll?'), NULL);
      $this->addElement('text', 'pcp_roll_nickname', ts('Name (for Honor Roll)'));
      $this->addElement('textarea', 'pcp_personal_note', ts('Personal Note (for Honor Roll)'));
    }

    $dataUrl = CRM_Utils_System::url('civicrm/ajax/rest',
      "className=CRM_Contact_Page_AJAX&fnName=getContactList&json=1&context=contact&reset=1&context=softcredit&id={$this->_id}",
      FALSE, NULL, FALSE
    );
    $this->assign('dataUrl', $dataUrl);
    $this->addElement('text', 'soft_credit_to', ts('Soft Credit To'));
    // Tell tpl to hide Soft Credit field if contribution is linked directly to a PCP Page
    if (CRM_Utils_Array::value('pcp_made_through_id', $this->_values)) {
      $this->assign('pcpLinked', 1);
    }
    $this->addElement('hidden', 'soft_contact_id', '', array('id' => 'soft_contact_id'));

    $js = NULL;
    if (!$this->_mode) {
      $js = array('onclick' => "return verify( );");
    }

    $mailingInfo = CRM_Core_BAO_Setting::getItem(CRM_Core_BAO_Setting::MAILING_PREFERENCES_NAME,
      'mailing_backend'
    );
    $this->assign('outBound_option', $mailingInfo['outBound_option']);

    $this->addButtons(array(
        array(
          'type' => 'upload',
          'name' => ts('Save'),
          'js' => $js,
          'isDefault' => TRUE
        ),
        array(
          'type' => 'upload',
          'name' => ts('Save and New'),
          'js' => $js,
          'subName' => 'new'
        ),
        array(
          'type' => 'cancel',
          'name' => ts('Cancel')
        ),
      )
    );

    $this->addFormRule(array('CRM_Contribute_Form_Contribution', 'formRule'), $this);

    if ($this->_action & CRM_Core_Action::VIEW) {
      $this->freeze();
    }
  }

  /**
   * global form rule
   *
   * @param array $fields  the input form values
   * @param array $files   the uploaded files if any
   * @param array $options additional user data
   *
   * @return true if no errors, else array of errors
   * @access public
   * @static
   */
  static function formRule($fields, $files, $self) {
    $errors = array();

    //check if contact is selected in standalone mode
    if (isset($fields['contact_select_id'][1]) && !$fields['contact_select_id'][1]) {
      $errors['contact[1]'] = ts('Please select a contact or create new contact');
    }

    if (isset($fields['honor_type_id'])) {
      if (!((CRM_Utils_Array::value('honor_first_name', $fields) &&
        CRM_Utils_Array::value('honor_last_name', $fields)
      ) ||
        CRM_Utils_Array::value('honor_email', $fields)
      )
      ) {
        $errors['honor_first_name'] = ts('Honor First Name and Last Name OR an email should be set.');
      }
    }

    //check for Credit Card Contribution.
    if ($self->_mode) {
      if (empty($fields['payment_processor_id'])) {
        $errors['payment_processor_id'] = ts('Payment Processor is a required field.');
      }
    }

    // do the amount validations.
    if (!CRM_Utils_Array::value('total_amount', $fields) && empty($self->_lineItems)) {
      if ($priceSetId = CRM_Utils_Array::value('price_set_id', $fields)) {
        CRM_Price_BAO_Field::priceSetValidation($priceSetId, $fields, $errors);
      }
    }

    // if honor roll fields are populated but no PCP is selected
    if (!CRM_Utils_Array::value('pcp_made_through_id', $fields)) {
      if (CRM_Utils_Array::value('pcp_display_in_roll', $fields) ||
        CRM_Utils_Array::value('pcp_roll_nickname', $fields) ||
        CRM_Utils_Array::value('pcp_personal_note', $fields)
      ) {
        $errors['pcp_made_through'] = ts('Please select a Personal Campaign Page, OR uncheck Display in Honor Roll and clear both the Honor Roll Name and the Personal Note field.');
      }
    }

    CRM_Financial_BAO_FinancialAccount::financialAccountValidation($fields, $errors);
    if (isset($errors['financial_type_id'])) {
      $flag = TRUE;
    }
    if (CRM_Utils_Array::value('fee_amount', $fields)) {
      $financialAccount = array();
      CRM_Core_PseudoConstant::populate($financialAccount,
        'CRM_Financial_DAO_EntityFinancialAccount',
        $all = TRUE,
        $retrieve = 'financial_account_id',
        $filter = NULL,
        " account_relationship = 5 AND entity_id = {$fields['financial_type_id']} ");
      if (!current($financialAccount)) {
        $errors['financial_type_id'] = "Financial Account of account relationship of ";
        if ($flag) {
          $errors['financial_type_id'] .= "'Asset Account of' and ";
        }
        $errors['financial_type_id'] .= "'Expense Account is' is not configured for this Financial Type";
      }
    }
    CRM_Price_BAO_Field::initialPayValidation($fields, $files, $self, $errors);
    return $errors;
  }

  /**
   * Function to process the form
   *
   * @access public
   *
   * @return void
   */
  public function postProcess() {
    if ($this->_action & CRM_Core_Action::DELETE) {
      CRM_Contribute_BAO_Contribution::deleteContribution($this->_id);
      return;
    }

    // get the submitted form values.
    $submittedValues = $this->controller->exportValues($this->_name);
    // If Contribution action is update then calculate and set initial amount 
    if ($this->_action & CRM_Core_Action::UPDATE) {
      $totalPrice = 0;
      if (CRM_Utils_Array::value('txt-price', $this->_submitValues)) {
        foreach ($this->_submitValues['txt-price'] as $priceKey => $priceValue) {
          $totalPrice = $totalPrice + $priceValue;
        }
      }
      $submittedValues['initial_amount'] = $totalPrice;
    }

    // process price set and get total amount and line items.
    $lineItem = array();
    $priceSetId = $pId = NULL;
    $priceSetId = CRM_Utils_Array::value('price_set_id', $submittedValues);
    if (empty($priceSetId) && !$this->_id) {
      $this->_priceSetId = $priceSetId = CRM_Core_DAO::getFieldValue('CRM_Price_DAO_Set', 'default_contribution_amount', 'id', 'name');
      $this->_priceSet = current(CRM_Price_BAO_Set::getSetDetail($priceSetId));
      $fieldID = key($this->_priceSet['fields']);
      $fieldValueId = key($this->_priceSet['fields'][$fieldID]['options']);
      $this->_priceSet['fields'][$fieldID]['options'][$fieldValueId]['amount'] = $submittedValues['total_amount'];
      $submittedValues['price_' . $fieldID] = 1;
    }

    if ($priceSetId) {
      CRM_Price_BAO_Set::processAmount($this->_priceSet['fields'],
        $submittedValues, $lineItem[$priceSetId]);
      $submittedValues['total_amount'] = CRM_Utils_Array::value('amount', $submittedValues);
    }
    if (!$priceSetId && CRM_Utils_Array::value('total_amount', $submittedValues) && $this->_id) {
      // 10117 update th line items for participants
      $pId = ($this->_compId && $this->_context == 'participant') ? $this->_compId : CRM_Core_DAO::getFieldValue('CRM_Event_DAO_ParticipantPayment', $this->_id, 'participant_id', 'contribution_id'); //CRM-10964
      if ($pId) {
        $entityTable = 'participant';
        $entityID = $pId;
        $participantParams = array(
          'fee_amount' => $submittedValues['total_amount'],
          'id' => $entityID
        );
        CRM_Event_BAO_Participant::add($participantParams);
      }
      else {
        $entityTable = 'contribution';
        $entityID = $this->_id;
      }

      $lineItems = CRM_Price_BAO_LineItem::getLineItems($entityID, $entityTable);
      $itemId = key($lineItems);
      $fieldType = NULL;
      if ($itemId && CRM_Utils_Array::value('price_field_id', $lineItems[$itemId])) {
        $fieldType = CRM_Core_DAO::getFieldValue('CRM_Price_DAO_Field', $lineItems[$itemId]['price_field_id'], 'html_type');
      }
      $lineItems[$itemId]['unit_price'] = $lineItems[$itemId]['line_total'] = CRM_Utils_Rule::cleanMoney(CRM_Utils_Array::value('total_amount', $submittedValues));
      $lineItems[$itemId]['id'] = $itemId;
      // 10117 update th line items for participants
      $this->_priceSetId = CRM_Core_DAO::getFieldValue('CRM_Price_DAO_Field', $lineItems[$itemId]['price_field_id'], 'price_set_id');
      $lineItem[$this->_priceSetId] = $lineItems;
    }
    $isQuickConfig = 0;
    if ($this->_priceSetId && CRM_Core_DAO::getFieldValue('CRM_Price_DAO_Set', $this->_priceSetId, 'is_quick_config')) {
      $isQuickConfig = 1;
    }

    if (!CRM_Utils_Array::value('total_amount', $submittedValues)) {
      $submittedValues['total_amount'] = CRM_Utils_Array::value('total_amount', $this->_values);
    }
    $this->assign('lineItem', !empty($lineItem) && !$isQuickConfig ? $lineItem : FALSE);

    if (CRM_Utils_Array::value('soft_credit_to', $submittedValues)) {
      $submittedValues['soft_credit_to'] = $submittedValues['soft_contact_id'];
    }

    // set the contact, when contact is selected
    if (CRM_Utils_Array::value('contact_select_id', $submittedValues)) {
      $this->_contactID = $submittedValues['contact_select_id'][1];
    }

    $config = CRM_Core_Config::singleton();
    $session = CRM_Core_Session::singleton();

    //Credit Card Contribution.
    if ($this->_mode) {
      $this->processCreditCard($submittedValues, $config, $session, $pId, $lineItem);
    }
    else {
      //Offline Contribution.
      $submittedValues = $this->unsetCreditCardFields($submittedValues);

      // get the required field value only.
      $formValues = $submittedValues;
      $params = $ids = array();

      $params['contact_id'] = $this->_contactID;

      $assetRelation = key(CRM_CORE_PseudoConstant::accountOptionValues('account_relationship', NULL, " AND v.name LIKE 'Asset Account of' "));
      $params['to_financial_account_id'] = $this->getFinancialAccount($formValues['financial_type_id'], $assetRelation);
      $params['currency'] = $this->getCurrency($submittedValues);

      $fields = array(
        'financial_type_id',
        'contribution_status_id',
        'payment_instrument_id',
        'cancel_reason',
        'source',
        'check_number',
        'soft_credit_to',
        'pcp_made_through_id',
        'pcp_display_in_roll',
        'pcp_roll_nickname',
        'pcp_personal_note',
      );
      foreach ($fields as $f) {
        $params[$f] = CRM_Utils_Array::value($f, $formValues);
      }

      if ($softID = CRM_Utils_Array::value('softID', $this->_values)) {
        $params['softID'] = $softID;
      }
      //if priceset is used, no need to cleanup money
      //CRM-5740
      if ($priceSetId) {
        $params['skipCleanMoney'] = 1;
      }

      $dates = array(
        'receive_date',
        'receipt_date',
        'cancel_date',
      );

      foreach ($dates as $d) {
        $params[$d] = CRM_Utils_Date::processDate($formValues[$d], $formValues[$d . '_time'], TRUE);
      }

      if (CRM_Utils_Array::value('is_email_receipt', $formValues)) {
        $params['receipt_date'] = date("Y-m-d");
      }

      if ($params['contribution_status_id'] == CRM_Core_OptionGroup::getValue('contribution_status', 'Cancelled', 'name')) {
        if (CRM_Utils_System::isNull(CRM_Utils_Array::value('cancel_date', $params))) {
          $params['cancel_date'] = date("Y-m-d");
        }
      }
      else {
        $params['cancel_date'] = $params['cancel_reason'] = 'null';
      }

      // Set is_pay_later flag for back-office offline Pending status contributions CRM-8996
      if ($params['contribution_status_id'] == CRM_Core_OptionGroup::getValue('contribution_status', 'Pending', 'name')) {
        $params['is_pay_later'] = 1;
      }

      $ids['contribution'] = $params['id'] = $this->_id;

      //Add Additional common information  to formatted params
      CRM_Contribute_Form_AdditionalInfo::postProcessCommon($formValues, $params, $this);

      $now = date('YmdHis');
      //create contribution.
      $contribution = CRM_Contribute_BAO_Contribution::create($params, $ids);
      $init_amount = array();
      foreach ($submittedValues as $key => $value) {
        if (strstr($key, 'txt-price')) {
          $init_amount[$key] = $value;
        }
        else {
          if (strstr($key, 'txt-allprice')) {
            $init_amount[$key] = $value;
          }
        }
      }
      $contribution->init_amount = $init_amount;
      //create entry in FinancialTrxn table
      if (!empty($contribution->id)) {
        if ($this->_action & CRM_Core_Action::ADD) {
          $trxnParams = array(
            'contribution_id' => $contribution->id,
            'to_financial_account_id' => $params['to_financial_account_id'],
            'trxn_date' => $now,
            'total_amount' => !empty($submittedValues['initial_amount']) ? $submittedValues['initial_amount'] : $params['total_amount'],
            'fee_amount' => CRM_Utils_Array::value('fee_amount', $params),
            'net_amount' => CRM_Utils_Array::value('net_amount', $params),
            'currency' => $params['currency'],
            'trxn_id' => $params['trxn_id'],
            'status_id' => $contribution->contribution_status_id,
            'trxn_result_code' => (!empty($contribution->trxn_result_code) ? $contribution->trxn_result_code : FALSE),
          );
          if ($paymentProcessorId = CRM_Utils_Array::value('id', $this->_paymentProcessor)) {
            $trxnParams['payment_processor_id'] = $paymentProcessorId;
          }

          $trxn = CRM_Core_BAO_FinancialTrxn::create($trxnParams);
          $mainTrxnId = $trxn->id;
        }

        if ($contribution->id && $this->_action & CRM_Core_Action::UPDATE) {
          $total_amount = 0;
          if (CRM_Utils_Array::value('txt-price', $this->_submitValues)) {
            foreach ($this->_submitValues['txt-price'] as $key => $value) {
              $total_amount += $value;
            }
          }
          $itemsParams['amount'] = $total_amount;
        }
        else {
          $itemsParams['amount'] = $trxn->total_amount;
        }

        if ($this->_action & CRM_Core_Action::ADD && CRM_Utils_Array::value('fee_amount', $trxnParams)) {
          $ExpenceRelation = key(CRM_CORE_PseudoConstant::accountOptionValues('account_relationship', NULL, " AND v.name LIKE 'Expense Account is' ", FALSE));
          $trxnParams['from_financial_account_id'] = $this->getFinancialAccount($formValues['financial_type_id'], self::FA_ASSET_ACCOUNT_RELATION);
          $trxnParams['to_financial_account_id'] = $this->getFinancialAccount($formValues['financial_type_id'], $ExpenceRelation);
          $trxnEntityTable['entity_table'] = 'civicrm_financial_trxn';
          $trxnEntityTable['entity_id'] = $trxn->id;
          $total_amount = 0;
          if (!empty($this->_submitValues['txt-price'])) {
            foreach ($this->_submitValues['txt-price'] as $key => $value) {
              $total_amount += $value;
            }
          }
          else {
            $total_amount = $this->_submitValues['initial_amount'];
          }

          $trxnParams['total_amount'] = $total_amount;
          // FIXME: What about fee_amount and net_amount?
          $trxn = CRM_Core_BAO_FinancialTrxn::create($trxnParams, $trxnEntityTable);
        }

        if ($this->_action & CRM_Core_Action::UPDATE) {
          $ExpenceRelation = key(CRM_CORE_PseudoConstant::accountOptionValues('account_relationship', NULL, " AND v.name LIKE 'Expense Account is' ", FALSE));
          $trxnParams['from_financial_account_id'] = $this->getFinancialAccount($formValues['financial_type_id'], self::FA_ASSET_ACCOUNT_RELATION);
          $trxnParams['to_financial_account_id'] = $this->getFinancialAccount($formValues['financial_type_id'], $ExpenceRelation);
          $entityParams = array(
            'entity_table' => 'civicrm_contribution',
            'entity_id' => $this->_values['contribution_id']
          );
          $entityTrxn = CRM_Financial_BAO_FinancialItem::retrieveEntityFinancialTrxn($entityParams);
          foreach ($entityTrxn as $key => $value) {
            $fid = $value['financial_trxn_id'];
          }
          $trxnEntityTable['entity_table'] = 'civicrm_financial_trxn';
          $mainTrxnId = $trxnEntityTable['entity_id'] = $fid;
          $total_amount = 0;
          if (!empty($this->_submitValues['txt-price'])) {
            foreach ($this->_submitValues['txt-price'] as $key => $value) {
              $total_amount += $value;
            }
          }
          else {
            $total_amount = $this->_submitValues['initial_amount'];
          }

          $trxnParams['total_amount'] = $total_amount;
          $trxnParams['fee_amount'] = $params['fee_amount'];
          $trxnParams['net_amount'] = $params['net_amount'];
          $trxn = CRM_Core_BAO_FinancialTrxn::create($trxnParams, $trxnEntityTable);
          $trxnId['id'] = $trxn->id;
        }
      }

      // 10117 update th line items for participants
      if ($this->_context != 'participant' && !$pId) {
        $entityID = $contribution->id;
        $entityTable = 'contribution';
      }
      //create entry in FinancialTrxn table
      if (!empty($contribution->id)) {
        $trxnParams = array(
          'contribution_id' => $contribution->id,
          'to_financial_account_id' => $params['to_financial_account_id'],
          'trxn_date' => $now,
          'total_amount' => $params['total_amount'],
          'fee_amount' => CRM_Utils_Array::value('fee_amount', $params),
          'net_amount' => CRM_Utils_Array::value('net_amount', $params),
          'currency' => $params['currency'],
          'trxn_id' => $params['trxn_id'],
          'status_id' => $contribution->contribution_status_id,
          'trxn_result_code' => (!empty($contribution->trxn_result_code) ? $contribution->trxn_result_code : FALSE),
        );

        if ($paymentProcessorId = CRM_Utils_Array::value('id', $this->_paymentProcessor)) {
          $trxnParams['payment_processor_id'] = $paymentProcessorId;
        }
        require_once 'CRM/Core/BAO/FinancialTrxn.php';
        $trxn = CRM_Core_BAO_FinancialTrxn::create($trxnParams);
      }
      // process line items, until no previous line items.

      if (empty($this->_lineItems) && $contribution->id && !empty($lineItem) && $this->_action & CRM_Core_Action::ADD) {
        if (empty($contribution->init_amount)) {
          foreach ($lineItem as $line) {
            foreach ($line as $key => $value) {
              $contribution->init_amount['txt-price_' . $value['price_field_id']] = array($key => $this->_submitValues['initial_amount']);
            }
          }
        }

        // CRM-11361 - allow 0 for partial (initial_amount) payment but if they didn't choose partial and initial is blank then set initial to total.
        // int_amount is the checkbox
        if ($this->_submitValues['initial_amount'] === '') {
          if (empty($this->_submitValues['int_amount'])) {
            $this->_submitValues['initial_amount'] = $this->_submitValues['total_amount'];
          }
          else {
            $this->_submitValues['initial_amount'] = 0;
          }
        }

        if ($this->_submitValues['initial_amount'] < $this->_submitValues['total_amount']) {
          $contrib['contribution_status_id'] = CRM_Core_OptionGroup::getValue('contribution_status', 'Pending', 'name');
          $ids['contribution'] = $contribution->id;
          CRM_Contribute_BAO_Contribution::create($contrib, $ids);
        }
        CRM_Price_BAO_LineItem::processPriceSet($contribution->id, $lineItem, $contribution);
      }
      else {

        if (empty($this->_lineItems)) {
          $entityTrxn = CRM_Financial_BAO_FinancialItem::retrieveEntityFinancialTrxn(array(
            'entity_table' => 'civicrm_contribution',
            'entity_id' => $this->_values['contribution_id']
          ));
          foreach ($entityTrxn as $key => $value) {
            $entityTrxn = CRM_Financial_BAO_FinancialItem::retrieveEntityFinancialTrxn(array(
              'entity_table' => 'civicrm_financial_item',
              'financial_trxn_id' => $value['financial_trxn_id']
            ));
          }
          foreach ($entityTrxn as $value) {
            $fid = $value['entity_id'];
          }
          $prevAmount = CRM_Financial_BAO_FinancialItem::retrievePreviousAmount(array(
            'entity_table' => 'civicrm_financial_item',
            'entity_id' => $fid
          ));
          $entityID = CRM_Financial_BAO_FinancialItem::retrieveMaxEntityFinancialTrxn(array(
            'financial_trxn_id' => $trxnId['id'],
            'entity_table' => 'civicrm_financial_trxn',
          ));
          CRM_Financial_BAO_FinancialItem::createEntityTrxn(array(
            'entity_table' => 'civicrm_financial_item',
            'entity_id' => $fid,
            'financial_trxn_id' => $entityID->financial_trxn_id,
            'amount' => (Float) ($this->_submitValues['initial_amount'] - $prevAmount)
          ));
          $prevamount = CRM_Financial_BAO_FinancialItem::retrievePreviousAmount(array(
            'entity_table' => 'civicrm_financial_item',
            'entity_id' => $fid,
          ));
          $fTrxn['id'] = $fid;
          $financialItem = new CRM_Financial_DAO_FinancialItem();
          $financialItem->id = $fid;
          $financialItem->find(TRUE);
          if ($prevamount < $financialItem->amount && $prevamount != 0) {
            $financialItem->status_id = 2;
          }
          elseif ($prevamount == 0) {
            $financialItem->status_id = 3;
          }
          elseif ($prevamount == $financialItem->amount) {
            $financialItem->status_id = 1;
            $contrib['contribution_status_id'] = 1;
            $ids['contribution'] = $contribution->id;
            CRM_Contribute_BAO_Contribution::create($contrib, $ids);
          }
          $financialItem->transaction_date = NULL;
          $financialItem->save();
        }
        else {
          foreach ($this->_lineItems[0] as $key => $value) {
            $financialItemID = CRM_Core_DAO::getFieldValue('CRM_Financial_DAO_FinancialItem', $key, 'id', 'entity_id');
            $entityParams = array(
              'entity_table' => 'civicrm_financial_item',
              'entity_id' => $financialItemID,
            );
            $prevAmount = CRM_Financial_BAO_FinancialItem::retrievePreviousAmount($entityParams);

            $etrxn = CRM_Financial_BAO_FinancialItem::createEntityTrxn(array(
              'entity_table' => "civicrm_financial_item",
              'entity_id' => $financialItemID,
              'financial_trxn_id' => $trxnId['id'],
              'amount' => (Float) ($this->_submitValues['txt-price'][$value['price_field_value_id']] - $prevAmount)
            ));
            $prevamount = CRM_Financial_BAO_FinancialItem::retrievePreviousAmount($entityParams);
            $financialItem = new CRM_Financial_DAO_FinancialItem();
            $financialItem->id = $financialItemID;
            $financialItem->find(TRUE);
            if ($prevamount < $financialItem->amount && $prevamount != 0) {
              $financialItem->status_id = 2;
            }
            elseif ($prevamount == 0) {
              $financialItem->status_id = 3;
            }
            elseif ($prevamount == $financialItem->amount) {
              $financialItem->status_id = 1;
            }
            $financialItem->transaction_date = NULL;
            $financialItem->save();
          }
          $linetotal = 0;
          foreach ($this->_submitValues['txt-price'] as $value) {
            $linetotal += $value;
          }
          if ($this->_values['total_amount'] == $linetotal) {
            $contrib['contribution_status_id'] = 1;
          }
          else {
            $contrib['contribution_status_id'] = 2;
          }
          $ids['contribution'] = $contribution->id;
          CRM_Contribute_BAO_Contribution::create($contrib, $ids);
        }
      }
      // process associated membership / participant, CRM-4395
      $relatedComponentStatusMsg = NULL;
      if ($contribution->id && $this->_action & CRM_Core_Action::UPDATE) {
        $relatedComponentStatusMsg = $this->updateRelatedComponent($contribution->id,
          $contribution->contribution_status_id,
          CRM_Utils_Array::value('contribution_status_id',
            $this->_values
          )
        );
      }

      //process  note
      if ($contribution->id && isset($formValues['note'])) {
        CRM_Contribute_Form_AdditionalInfo::processNote($formValues, $this->_contactID, $contribution->id, $this->_noteID);
      }

      //process premium
      if ($contribution->id && isset($formValues['product_name'][0])) {
        CRM_Contribute_Form_AdditionalInfo::processPremium($formValues, $contribution->id,
          $this->_premiumID, $this->_options, $mainTrxnId
        );
      }

      //send receipt mail.
      if ($contribution->id && CRM_Utils_Array::value('is_email_receipt', $formValues)) {
        $formValues['contact_id'] = $this->_contactID;
        $formValues['contribution_id'] = $contribution->id;

        // to get 'from email id' for send receipt
        $this->fromEmailId = $formValues['from_email_address'];
        $sendReceipt = CRM_Contribute_Form_AdditionalInfo::emailReceipt($this, $formValues);
      }

      $pledgePaymentId = CRM_Core_DAO::getFieldValue('CRM_Pledge_DAO_PledgePayment',
        $contribution->id,
        'id',
        'contribution_id'
      );
      //update pledge payment status.
      if ((($this->_ppID && $contribution->id) && $this->_action & CRM_Core_Action::ADD) ||
        (($pledgePaymentId) && $this->_action & CRM_Core_Action::UPDATE)
      ) {

        if ($this->_ppID) {
          //store contribution id in payment record.
          CRM_Core_DAO::setFieldValue('CRM_Pledge_DAO_PledgePayment', $this->_ppID, 'contribution_id', $contribution->id);
        }
        else {
          $this->_ppID = CRM_Core_DAO::getFieldValue('CRM_Pledge_DAO_PledgePayment',
            $contribution->id,
            'id',
            'contribution_id'
          );
          $this->_pledgeID = CRM_Core_DAO::getFieldValue('CRM_Pledge_DAO_PledgePayment',
            $contribution->id,
            'pledge_id',
            'contribution_id'
          );
        }

        $adjustTotalAmount = FALSE;
        if (CRM_Utils_Array::value('option_type', $formValues) == 2) {
          $adjustTotalAmount = TRUE;
        }

        $updatePledgePaymentStatus = FALSE;
        //do only if either the status or the amount has changed
        if ($this->_action & CRM_Core_Action::ADD) {
          $updatePledgePaymentStatus = TRUE;
        }
        elseif ($this->_action & CRM_Core_Action::UPDATE && (($this->_defaults['contribution_status_id'] != $formValues['contribution_status_id']) ||
          ($this->_defaults['total_amount'] != $formValues['total_amount']))
        ) {
          $updatePledgePaymentStatus = TRUE;
        }

        if ($updatePledgePaymentStatus) {
          CRM_Pledge_BAO_PledgePayment::updatePledgePaymentStatus($this->_pledgeID,
            array($this->_ppID),
            $contribution->contribution_status_id,
            NULL,
            $contribution->total_amount,
            $adjustTotalAmount
          );
        }

        $statusMsg = ts('The contribution record has been saved.');
        if (CRM_Utils_Array::value('is_email_receipt', $formValues) && $sendReceipt) {
          $statusMsg .= ' ' . ts('A receipt has been emailed to the contributor.');
        }

        if ($relatedComponentStatusMsg) {
          $statusMsg .= ' ' . $relatedComponentStatusMsg;
        }

        CRM_Core_Session::setStatus($statusMsg, ts('Saved'), 'success');
        //Offline Contribution ends.
      }

      $buttonName = $this->controller->getButtonName();
      if ($this->_context == 'standalone') {
        if ($buttonName == $this->getButtonName('upload', 'new')) {
          $session->replaceUserContext(CRM_Utils_System::url('civicrm/contribute/add',
            'reset=1&action=add&context=standalone'
          ));
        }
        else {
          $session->replaceUserContext(CRM_Utils_System::url('civicrm/contact/view',
            "reset=1&cid={$this->_contactID}&selectedChild=contribute"
          ));
        }
      }
      elseif ($this->_context == 'contribution' && $this->_mode && $buttonName == $this->getButtonName('upload', 'new')) {
        $session->replaceUserContext(CRM_Utils_System::url('civicrm/contact/view/contribution',
          "reset=1&action=add&context={$this->_context}&cid={$this->_contactID}&mode={$this->_mode}"
        ));
      }
      elseif ($buttonName == $this->getButtonName('upload', 'new')) {
        $session->replaceUserContext(CRM_Utils_System::url('civicrm/contact/view/contribution',
          "reset=1&action=add&context={$this->_context}&cid={$this->_contactID}"
        ));
      }
    }
  }

  public function processCreditCard($submittedValues, $config, $session, $pId, $lineItem) {
    $unsetParams = array(
      'trxn_id',
      'payment_instrument_id',
      'contribution_status_id',
      'cancel_date',
      'cancel_reason',
    );
    foreach ($unsetParams as $key) {
      if (isset($submittedValues[$key])) {
        unset($submittedValues[$key]);
      }
    }

    //Get the rquire fields value only.
    $params = $this->_params = $submittedValues;

    $this->_paymentProcessor = CRM_Core_BAO_PaymentProcessor::getPayment($this->_params['payment_processor_id'],
      $this->_mode
    );

    //get the payment processor id as per mode.
    $params['payment_processor_id'] = $this->_params['payment_processor_id'] = $submittedValues['payment_processor_id'] = $this->_paymentProcessor['id'];


    $now = date('YmdHis');
    $fields = array();

    // we need to retrieve email address
    if ($this->_context == 'standalone' && CRM_Utils_Array::value('is_email_receipt', $submittedValues)) {
      list($this->userDisplayName,
        $this->userEmail
        ) = CRM_Contact_BAO_Contact_Location::getEmailDetails($this->_contactID);
      $this->assign('displayName', $this->userDisplayName);
    }

    //set email for primary location.
    $fields['email-Primary'] = 1;
    $params['email-Primary'] = $this->userEmail;

    // now set the values for the billing location.
    foreach ($this->_fields as $name => $dontCare) {
      $fields[$name] = 1;
    }

    // also add location name to the array
    $params["address_name-{$this->_bltID}"] = CRM_Utils_Array::value('billing_first_name', $params) . ' ' . CRM_Utils_Array::value('billing_middle_name', $params) . ' ' . CRM_Utils_Array::value('billing_last_name', $params);
    $params["address_name-{$this->_bltID}"] = trim($params["address_name-{$this->_bltID}"]);
    $fields["address_name-{$this->_bltID}"] = 1;

    $ctype = CRM_Core_DAO::getFieldValue('CRM_Contact_DAO_Contact',
      $this->_contactID,
      'contact_type'
    );

    $nameFields = array('first_name', 'middle_name', 'last_name');
    foreach ($nameFields as $name) {
      $fields[$name] = 1;
      if (array_key_exists("billing_$name", $params)) {
        $params[$name] = $params["billing_{$name}"];
        $params['preserveDBName'] = TRUE;
      }
    }

    if (CRM_Utils_Array::value('source', $params)) {
      unset($params['source']);
    }
    $contactID = CRM_Contact_BAO_Contact::createProfileContact($params, $fields,
      $this->_contactID,
      NULL, NULL,
      $ctype
    );

    // add all the additioanl payment params we need
    $this->_params["state_province-{$this->_bltID}"] = $this->_params["billing_state_province-{$this->_bltID}"] = CRM_Core_PseudoConstant::stateProvinceAbbreviation($this->_params["billing_state_province_id-{$this->_bltID}"]);
    $this->_params["country-{$this->_bltID}"] = $this->_params["billing_country-{$this->_bltID}"] = CRM_Core_PseudoConstant::countryIsoCode($this->_params["billing_country_id-{$this->_bltID}"]);

    if ($this->_paymentProcessor['payment_type'] & CRM_Core_Payment::PAYMENT_TYPE_CREDIT_CARD) {
      $this->_params['year'] = CRM_Core_Payment_Form::getCreditCardExpirationYear($this->_params);
      $this->_params['month'] = CRM_Core_Payment_Form::getCreditCardExpirationMonth($this->_params);
    }
    $this->_params['ip_address'] = CRM_Utils_System::ipAddress();
    $this->_params['amount'] = $this->_params['total_amount'];
    $this->_params['amount_level'] = 0;
    $this->_params['currencyID'] = CRM_Utils_Array::value('currency',
      $this->_params,
      $config->defaultCurrency
    );
    $this->_params['payment_action'] = 'Sale';
    if (CRM_Utils_Array::value('receive_date', $this->_params)) {
      $this->_params['receive_date'] = CRM_Utils_Date::processDate($this->_params['receive_date'], $this->_params['receive_date_time']);
    }

    if (CRM_Utils_Array::value('soft_credit_to', $params)) {
      $this->_params['soft_credit_to'] = $params['soft_credit_to'];
      $this->_params['pcp_made_through_id'] = $params['pcp_made_through_id'];
    }

    $this->_params['pcp_display_in_roll'] = CRM_Utils_Array::value('pcp_display_in_roll', $params);
    $this->_params['pcp_roll_nickname'] = CRM_Utils_Array::value('pcp_roll_nickname', $params);
    $this->_params['pcp_personal_note'] = CRM_Utils_Array::value('pcp_personal_note', $params);

    //Add common data to formatted params
    CRM_Contribute_Form_AdditionalInfo::postProcessCommon($params, $this->_params, $this);

    if (empty($this->_params['invoice_id'])) {
      $this->_params['invoiceID'] = md5(uniqid(rand(), TRUE));
    }
    else {
      $this->_params['invoiceID'] = $this->_params['invoice_id'];
    }

    // at this point we've created a contact and stored its address etc
    // all the payment processors expect the name and address to be in the
    // so we copy stuff over to first_name etc.
    $paymentParams = $this->_params;
    $paymentParams['contactID'] = $this->_contactID;
    CRM_Core_Payment_Form::mapParams($this->_bltID, $this->_params, $paymentParams, TRUE);

    $contributionType = new CRM_Financial_DAO_FinancialType();
    $contributionType->id = $params['financial_type_id'];
    if (!$contributionType->find(TRUE)) {
      CRM_Core_Error::fatal('Could not find a system table');
    }

    // add some financial type details to the params list
    // if folks need to use it
    $paymentParams['contributionType_name'] = $this->_params['contributionType_name'] = $contributionType->name;
    $paymentParams['contributionPageID'] = NULL;
    if (CRM_Utils_Array::value('is_email_receipt', $this->_params)) {
      $paymentParams['email'] = $this->userEmail;
      $paymentParams['is_email_receipt'] = 1;
    }
    else {
      $paymentParams['is_email_receipt'] = 0;
      $this->_params['is_email_receipt'] = 0;
    }
    if (CRM_Utils_Array::value('receive_date', $this->_params)) {
      $paymentParams['receive_date'] = $this->_params['receive_date'];
    }
    if (CRM_Utils_Array::value('receive_date', $this->_params)) {
      $paymentParams['receive_date'] = $this->_params['receive_date'];
    }

    $result = NULL;

    // For recurring contribution, create Contribution Record first.
    // Contribution ID, Recurring ID and Contact ID needed
    // When we get a callback from the payment processor, CRM-7115
    if (CRM_Utils_Array::value('is_recur', $paymentParams)) {
      $contribution = CRM_Contribute_Form_Contribution_Confirm::processContribution($this,
        $this->_params,
        $result,
        $this->_contactID,
        $contributionType,
        FALSE,
        TRUE,
        FALSE
      );
      $paymentParams['contributionID'] = $contribution->id;
      $paymentParams['contributionTypeID'] = $contribution->financial_type_id;
      $paymentParams['contributionPageID'] = $contribution->contribution_page_id;
      $paymentParams['contributionRecurID'] = $contribution->contribution_recur_id;
    }

    if ($paymentParams['amount'] > 0.0) {
      // force a reget of the payment processor in case the form changed it, CRM-7179
      $payment = CRM_Core_Payment::singleton($this->_mode, $this->_paymentProcessor, $this, TRUE);
      $result = $payment->doDirectPayment($paymentParams);
    }

    if (is_a($result, 'CRM_Core_Error')) {
      //make sure to cleanup db for recurring case.
      if (CRM_Utils_Array::value('contributionID', $paymentParams)) {
        CRM_Core_Error::debug_log_message(CRM_Core_Error::getMessages($result) . "contact id={$this->_contactID} (deleting contribution {$paymentParams['contributionID']}");
        CRM_Contribute_BAO_Contribution::deleteContribution($paymentParams['contributionID']);
      }
      if (CRM_Utils_Array::value('contributionRecurID', $paymentParams)) {
        CRM_Core_Error::debug_log_message(CRM_Core_Error::getMessages($result) . "contact id={$this->_contactID} (deleting recurring contribution {$paymentParams['contributionRecurID']}");
        CRM_Contribute_BAO_ContributionRecur::deleteRecurContribution($paymentParams['contributionRecurID']);
      }

      //set the contribution mode.
      $urlParams = "action=add&cid={$this->_contactID}";
      if ($this->_mode) {
        $urlParams .= "&mode={$this->_mode}";
      }
      if (!empty($this->_ppID)) {
        $urlParams .= "&context=pledge&ppid={$this->_ppID}";
      }
      CRM_Core_Error::displaySessionError($result);
      CRM_Utils_System::redirect(CRM_Utils_System::url('civicrm/contact/view/contribution', $urlParams));
    }

    if ($result) {
      $this->_params = array_merge($this->_params, $result);
    }

    $this->_params['receive_date'] = $now;

    if (CRM_Utils_Array::value('is_email_receipt', $this->_params)) {
      $this->_params['receipt_date'] = $now;
    }
    else {
      $this->_params['receipt_date'] = CRM_Utils_Date::processDate($this->_params['receipt_date'],
        $params['receipt_date_time'], TRUE
      );
    }

    $this->set('params', $this->_params);
    $this->assign('trxn_id', $result['trxn_id']);
    $this->assign('receive_date', $this->_params['receive_date']);

    // result has all the stuff we need
    // lets archive it to a financial transaction
    if ($contributionType->is_deductible) {
      $this->assign('is_deductible', TRUE);
      $this->set('is_deductible', TRUE);
    }

    // set source if not set
    if (empty($this->_params['source'])) {
      $userID = $session->get('userID');
      $userSortName = CRM_Core_DAO::getFieldValue('CRM_Contact_DAO_Contact', $userID,
        'sort_name'
      );
      $this->_params['source'] = ts('Submit Credit Card Payment by: %1', array(1 => $userSortName));
    }

    // build custom data getFields array
    $customFieldsContributionType = CRM_Core_BAO_CustomField::getFields('Contribution', FALSE, FALSE,
      CRM_Utils_Array::value('financial_type_id', $params)
    );
    $customFields = CRM_Utils_Array::crmArrayMerge($customFieldsContributionType,
      CRM_Core_BAO_CustomField::getFields('Contribution', FALSE, FALSE, NULL, NULL, TRUE)
    );
    $params['custom'] = CRM_Core_BAO_CustomField::postProcess($params,
      $customFields,
      $this->_id,
      'Contribution'
    );


    if (!CRM_Utils_Array::value('is_recur', $paymentParams)) {
      $contribution = CRM_Contribute_Form_Contribution_Confirm::processContribution($this,
        $this->_params,
        $result,
        $this->_contactID,
        $contributionType,
        FALSE, FALSE, FALSE
      );
    }
    if (($this->_context != 'participant') && !$pId) {
      $entityID = $contribution->id;
      $entityTable = 'contribution';
    }
    // process line items, until no previous line items.
    if (empty($this->_lineItems) && $entityID && !empty($lineItem)) {
      CRM_Price_BAO_LineItem::processPriceSet($entityID, $lineItem, $contribution, NULL, "civicrm_" . $entityTable);
    }

    //send receipt mail.
    if ($contribution->id &&
      CRM_Utils_Array::value('is_email_receipt', $this->_params)
    ) {
      $this->_params['trxn_id'] = CRM_Utils_Array::value('trxn_id', $result);
      $this->_params['contact_id'] = $this->_contactID;
      $this->_params['contribution_id'] = $contribution->id;
      $sendReceipt = CRM_Contribute_Form_AdditionalInfo::emailReceipt($this, $this->_params, TRUE);
    }

    //process the note
    if ($contribution->id && isset($params['note'])) {
      CRM_Contribute_Form_AdditionalInfo::processNote($params, $contactID, $contribution->id, NULL);
    }
    //process premium
    if ($contribution->id && isset($params['product_name'][0])) {
      $mainTrxnId = CRM_Core_BAO_FinancialTrxn::getFinancialTrxnIds($contribution->id);
      CRM_Contribute_Form_AdditionalInfo::processPremium($params, $contribution->id, NULL, $this->_options, $mainTrxnId['financialTrxnId']);
    }

    //update pledge payment status.
    if ($this->_ppID && $contribution->id) {
      //store contribution id in payment record.
      CRM_Core_DAO::setFieldValue('CRM_Pledge_DAO_PledgePayment', $this->_ppID, 'contribution_id', $contribution->id);

      CRM_Pledge_BAO_PledgePayment::updatePledgePaymentStatus($this->_pledgeID,
        array($this->_ppID),
        $contribution->contribution_status_id,
        NULL,
        $contribution->total_amount
      );
    }

    if ($contribution->id) {
      $statusMsg = ts('The contribution record has been processed.');
      if (CRM_Utils_Array::value('is_email_receipt', $this->_params) && $sendReceipt) {
        $statusMsg .= ' ' . ts('A receipt has been emailed to the contributor.');
      }
      CRM_Core_Session::setStatus($statusMsg, ts('Complete'), 'success');
    }
  }
}

