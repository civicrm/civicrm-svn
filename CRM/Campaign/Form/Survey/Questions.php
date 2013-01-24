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
 * This class generates form components for processing a survey
 *
 */
class CRM_Campaign_Form_Survey_Questions extends CRM_Campaign_Form_Survey {

  /**
   * This function sets the default values for the form. Note that in edit/view mode
   * the default values are retrieved from the database
   *
   * @param null
   *
   * @return array    array of default values
   * @access public
   */
  function setDefaultValues() {
    $defaults = array();

    $ufJoinParams = array(
      'entity_table' => 'civicrm_survey',
      'module' => 'CiviCampaign',
      'entity_id' => $this->_surveyId,
    );

    list($defaults['contact_profile_id'],
         $second) = CRM_Core_BAO_UFJoin::getUFGroupIds($ufJoinParams);
    $defaults['activity_profile_id'] = $second ? array_shift($second) : '';

    return $defaults;
  }

  /**
   * Function to actually build the form
   *
   * @param null
   *
   * @return void
   * @access public
   */
  public function buildQuickForm() {
    $subTypeId = CRM_Core_DAO::getFieldValue('CRM_Campaign_DAO_Survey', $this->_surveyId, 'activity_type_id');
    $allowCoreTypes = CRM_Campaign_BAO_Survey::surveyProfileTypes();
    $allowSubTypes = array(
      'ActivityType' => array($subTypeId),
    );
    $entities = array(
      array('entity_name' => 'contact_1', 'entity_type' => 'IndividualModel'),
      array('entity_name' => 'activity_1', 'entity_type' => 'ActivityModel', 'entity_sub_type' => $subTypeId),
    );
    $this->addProfileSelector('contact_profile_id', ts('Contact Info'), $allowCoreTypes, $allowSubTypes, $entities);
    $this->addProfileSelector('activity_profile_id', ts('Questions'), $allowCoreTypes, $allowSubTypes, $entities);

    parent::buildQuickForm();
  }


  /**
   * Process the form
   *
   * @param null
   *
   * @return void
   * @access public
   */
  public function postProcess() {
    // store the submitted values in an array
    $params = $this->controller->exportValues($this->_name);

    // also update the ProfileModule tables
    $ufJoinParams = array(
      'is_active' => 1,
      'module'    => 'CiviCampaign',
      'entity_table' => 'civicrm_survey',
      'entity_id'    => $this->_surveyId,
    );

    // first delete all past entries
    CRM_Core_BAO_UFJoin::deleteAll($ufJoinParams);

    $uf = array();
    $wt = 2;
    if (!empty($params['contact_profile_id'])) {
      $uf[1] = $params['contact_profile_id'];
      $wt = 1;
    }
    if (!empty($params['activity_profile_id'])) {
      $uf[2] = $params['activity_profile_id'];
    }

    $uf = array_values($uf);
    if (!empty($uf)) {
      foreach ($uf as $weight => $ufGroupId) {
        $ufJoinParams['weight'] = $weight + $wt;
        $ufJoinParams['uf_group_id'] = $ufGroupId;
        CRM_Core_BAO_UFJoin::create($ufJoinParams);
        unset($ufJoinParams['id']);
      }
    }

    parent::endPostProcess();
  }
}
