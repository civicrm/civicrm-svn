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
 * Dummy page for details of address 
 *
 */
class CRM_Contact_Page_Inline_Address {

  /**
   * Run the page.
   *
   * This method is called after the page is created.
   *
   * @return void
   * @access public
   *
   */
  function run() {
    // get the emails for this contact
    $contactId = CRM_Utils_Request::retrieve('cid', 'Positive', CRM_Core_DAO::$_nullObject, TRUE, NULL, $_REQUEST);
    $locBlockNo = CRM_Utils_Request::retrieve('locno', 'Positive', $this, TRUE, NULL, $_REQUEST);
 
    $locationTypes = CRM_Core_PseudoConstant::locationDisplayName();

    $entityBlock = array('contact_id' => $contactId);
    $address = CRM_Core_BAO_Address::getValues($entityBlock);
    if (!empty($address)) {
      foreach ($address as $key => & $value) {
        $value['location_type'] = $locationTypes[$value['location_type_id']];
      }
    }

    // FIX ME: once we retrieve only address that we need below
    // code should be delete
    // we just need current address block
    $currentAddressBlock = $address[$locBlockNo]; 

    // get contact name of shared contact names
    $sharedAddresses = array();
    //FIX ME - just send address that we need
    $defaults['address'] = $address;
    $shareAddressContactNames = CRM_Contact_BAO_Contact_Utils::getAddressShareContactNames($defaults['address']);
    foreach ($defaults['address'] as $key => $addressValue) {
      if (CRM_Utils_Array::value('master_id', $addressValue) &&
        !$shareAddressContactNames[$addressValue['master_id']]['is_deleted']
      ) {
        $sharedAddresses[$key]['shared_address_display'] = array(
          'address' => $addressValue['display'],
          'name' => $shareAddressContactNames[$addressValue['master_id']]['name'],
        );
      }
    }

    // add custom data of type address
    $page = new CRM_Core_Page();
    $groupTree = CRM_Core_BAO_CustomGroup::getTree( 'Address',
      $page, $contactId
    );
    
    // we setting the prefix to dnc_ below so that we don't overwrite smarty's grouptree var.
    $customData = array();
    $customData['custom'] = CRM_Core_BAO_CustomGroup::buildCustomDataView( $page, $groupTree, FALSE, NULL, "dnc_");
    $page->assign("dnc_viewCustomData", NULL);

    $currentAddressBlock = CRM_Utils_Array::crmArrayMerge( $currentAddressBlock, $customData);
    
    $template = CRM_Core_Smarty::singleton();
    $template->assign('contactId', $contactId);
    $template->assign('add', $currentAddressBlock);
    $template->assign('locationIndex', $locBlockNo);
    $template->assign('sharedAddresses', $sharedAddresses);
    
    echo $content = $template->fetch('CRM/Contact/Page/Inline/Address.tpl');
    CRM_Utils_System::civiExit();
  }
}

