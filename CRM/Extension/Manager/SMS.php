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
 * This class stores logic for managing CiviCRM extensions.
 *
 * @package CRM
 * @copyright CiviCRM LLC (c) 2004-2012
 * $Id$
 *
 */
class CRM_Extension_Manager_SMS extends CRM_Extension_Manager_Base {

  public function __construct() {
    parent::__construct(TRUE);
  }

  /**
   * {@inheritdoc}
   */
  public function onPreInstall(CRM_Extension_Info $info) {
    $groupID = CRM_Core_DAO::getFieldValue('CRM_Core_DAO_OptionGroup', 'sms_provider_name','id','name');
    $params  = 
      array('option_group_id' => $groupID,
            'label' => $info->label,
            'value' => $info->key,
            'name'  => $info->name,
            'is_default' => 1,
            'is_active'  => 1,
            'version'    => 3,);
    require_once 'api/api.php';
    $result = civicrm_api( 'option_value','create',$params );
  }

  /**
   * {@inheritdoc}
   */
  public function onPostInstall(CRM_Extension_Info $info) {
  }

  /**
   * {@inheritdoc}
   */
  public function onPreUninstall(CRM_Extension_Info $info) {
    $optionID = CRM_Core_DAO::getFieldValue('CRM_Core_DAO_OptionValue', $info->name,'id','name');
    if ($optionID)
      CRM_Core_BAO_OptionValue::del($optionID); 

    $filter    =  array('name'  => $info->key  );
    $Providers =  CRM_SMS_BAO_Provider::getProviders(False, $filter, False);
    if ($Providers){
      foreach($Providers as $key => $value){
        CRM_SMS_BAO_Provider::del($value['id']);
      }
    }
  }
  
  /**
   * {@inheritdoc}
   */
  public function onPreDisable(CRM_Extension_Info $info) {
    $optionID = CRM_Core_DAO::getFieldValue('CRM_Core_DAO_OptionValue', $info->name,'id','name');
    if ($optionID)
      CRM_Core_BAO_OptionValue::setIsActive($optionID, FALSE);

    $filter    =  array('name' =>  $info->key);
    $Providers =  CRM_SMS_BAO_Provider::getProviders(False, $filter, False);
    if ($Providers){
      foreach($Providers as $key => $value){
        CRM_SMS_BAO_Provider::setIsActive($value['id'], FALSE); 
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public function onPreEnable(CRM_Extension_Info $info) {
    $optionID = CRM_Core_DAO::getFieldValue('CRM_Core_DAO_OptionValue', $info->name ,'id','name');
    if ($optionID)
      CRM_Core_BAO_OptionValue::setIsActive($optionID, TRUE); 

    $filter    =  array('name' => $info->key );
    $Providers =  CRM_SMS_BAO_Provider::getProviders(False, $filter, False);
    if ($Providers){
      foreach($Providers as $key => $value){
        CRM_SMS_BAO_Provider::setIsActive($value['id'], TRUE); 
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public function onPostEnable(CRM_Extension_Info $info) {
  }
}
