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
class CRM_Campaign_Form_Survey_Results extends CRM_Campaign_Form_Survey {

  /* values
     *
     * @var array
     */
  public $_values;

  CONST NUM_OPTION = 11;

  public function preProcess() {
    parent::preProcess();

    $this->_values = $this->get('values');
    if (!is_array($this->_values)) {
      $this->_values = array();
      if ($this->_surveyId) {
        $params = array('id' => $this->_surveyId);
        CRM_Campaign_BAO_Survey::retrieve($params, $this->_values);
      }
      $this->set('values', $this->_values);
    }
  }

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
    $defaults = $this->_values;

    // set defaults for weight.
    for ($i = 1; $i <= self::NUM_OPTION; $i++) {
      $defaults["option_weight[{$i}]"] = $i;
    }

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
    $optionGroups = CRM_Campaign_BAO_Survey::getResultSets();

    if (empty($optionGroups)) {
      $optionTypes = array('1' => ts('Create new result set'));
    }
    else {
      $optionTypes = array('1' => ts('Create a new result set'),
        '2' => ts('Use existing result set'),
      );
      $this->add('select',
        'option_group_id',
        ts('Select Result Set'),
        array(
          '' => ts('- select -')) + $optionGroups, FALSE,
        array('onChange' => 'loadOptionGroup( )')
      );
    }

    $element = &$this->addRadio('option_type',
      ts('Survey Responses'),
      $optionTypes,
      array(
        'onclick' => "showOptionSelect();"), '<br/>', TRUE
    );

    if (empty($optionGroups) || !CRM_Utils_Array::value('result_id', $this->_values)) {
      $this->setdefaults(array('option_type' => 1));
    }
    elseif (CRM_Utils_Array::value('result_id', $this->_values)) {
      $this->setdefaults(array(
        'option_type' => 2,
          'option_group_id' => $this->_values['result_id'],
        ));
    }

    // form fields of Custom Option rows
    $defaultOption = array();
    $_showHide = new CRM_Core_ShowHideBlocks('', '');

    $optionAttributes = CRM_Core_DAO::getAttribute('CRM_Core_DAO_OptionValue');
    $optionAttributes['label']['size'] = $optionAttributes['value']['size'] = 25;

    for ($i = 1; $i <= self::NUM_OPTION; $i++) {
      //the show hide blocks
      $showBlocks = 'optionField_' . $i;
      if ($i > 2) {
        $_showHide->addHide($showBlocks);
        if ($i == self::NUM_OPTION) {
          $_showHide->addHide('additionalOption');
        }
      }
      else {
        $_showHide->addShow($showBlocks);
      }

      $this->add('text', 'option_label[' . $i . ']', ts('Label'),
        $optionAttributes['label']
      );

      // value
      $this->add('text', 'option_value[' . $i . ']', ts('Value'),
        $optionAttributes['value']
      );

      // weight
      $this->add('text', "option_weight[$i]", ts('Order'),
        $optionAttributes['weight']
      );

      $this->add('text', 'option_interval[' . $i . ']', ts('Recontact Interval'),
        CRM_Core_DAO::getAttribute('CRM_Campaign_DAO_Survey', 'release_frequency')
      );

      $defaultOption[$i] = $this->createElement('radio', NULL, NULL, NULL, $i);
    }

    //default option selection
    $this->addGroup($defaultOption, 'default_option');

    $_showHide->addToTemplate();

    $this->addFormRule(array('CRM_Campaign_Form_Survey_Results', 'formRule'), $this);

    parent::buildQuickForm();
  }

  /**
   * global validation rules for the form
   *
   */
  static function formRule($fields, $files, $form) {
    $errors = array();

    if (CRM_Utils_Array::value('option_label', $fields) &&
      CRM_Utils_Array::value('option_value', $fields) &&
      (count(array_filter($fields['option_label'])) == 0) &&
      (count(array_filter($fields['option_value'])) == 0)
    ) {
      $errors['option_label[1]'] = ts('Enter atleast one response option.');
      return $errors;
    }
    elseif (!CRM_Utils_Array::value('option_label', $fields) &&
      !CRM_Utils_Array::value('option_value', $fields)
    ) {
      return $errors;
    }

    if ($fields['option_type'] == 2 &&
      !CRM_Utils_Array::value('option_group_id', $fields)
    ) {
      $errors['option_group_id'] = ts("Please select Survey Response set.");
      return $errors;
    }

    $_flagOption = $_rowError = 0;
    $_showHide = new CRM_Core_ShowHideBlocks('', '');

    //capture duplicate Custom option values
    if (!empty($fields['option_value'])) {
      $countValue = count($fields['option_value']);
      $uniqueCount = count(array_unique($fields['option_value']));

      if ($countValue > $uniqueCount) {
        $start = 1;
        while ($start < self::NUM_OPTION) {
          $nextIndex = $start + 1;

          while ($nextIndex <= self::NUM_OPTION) {
            if ($fields['option_value'][$start] == $fields['option_value'][$nextIndex] &&
              !empty($fields['option_value'][$nextIndex])
            ) {

              $errors['option_value[' . $start . ']'] = ts('Duplicate Option values');
              $errors['option_value[' . $nextIndex . ']'] = ts('Duplicate Option values');
              $_flagOption = 1;
            }
            $nextIndex++;
          }
          $start++;
        }
      }
    }

    //capture duplicate Custom Option label
    if (!empty($fields['option_label'])) {
      $countValue = count($fields['option_label']);
      $uniqueCount = count(array_unique($fields['option_label']));

      if ($countValue > $uniqueCount) {
        $start = 1;
        while ($start < self::NUM_OPTION) {
          $nextIndex = $start + 1;

          while ($nextIndex <= self::NUM_OPTION) {
            if ($fields['option_label'][$start] == $fields['option_label'][$nextIndex] && !empty($fields['option_label'][$nextIndex])) {
              $errors['option_label[' . $start . ']'] = ts('Duplicate Option label');
              $errors['option_label[' . $nextIndex . ']'] = ts('Duplicate Option label');
              $_flagOption = 1;
            }
            $nextIndex++;
          }
          $start++;
        }
      }
    }

    for ($i = 1; $i <= self::NUM_OPTION; $i++) {
      if (!$fields['option_label'][$i]) {
        if ($fields['option_value'][$i]) {
          $errors['option_label[' . $i . ']'] = ts('Option label cannot be empty');
          $_flagOption = 1;
        }
        else {
          $_emptyRow = 1;
        }
      }
      elseif (!strlen(trim($fields['option_value'][$i]))) {
        if (!$fields['option_value'][$i]) {
          $errors['option_value[' . $i . ']'] = ts('Option value cannot be empty');
          $_flagOption = 1;
        }
      }

      if (CRM_Utils_Array::value($i, $fields['option_interval']) && !CRM_Utils_Rule::integer($fields['option_interval'][$i])) {
        $_flagOption = 1;
        $errors['option_interval[' . $i . ']'] = ts('Please enter a valid integer.');
      }

      $showBlocks = 'optionField_' . $i;
      if ($_flagOption) {
        $_showHide->addShow($showBlocks);
        $_rowError = 1;
      }

      if (!empty($_emptyRow)) {
        $_showHide->addHide($showBlocks);
      }
      else {
        $_showHide->addShow($showBlocks);
      }

      if ($i == self::NUM_OPTION) {
        $hideBlock = 'additionalOption';
        $_showHide->addHide($hideBlock);
      }

      $_flagOption = $_emptyRow = 0;
    }
    $_showHide->addToTemplate();

    return empty($errors) ? TRUE : $errors;
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

    $params['id'] = $this->_surveyId;

    $updateResultSet = FALSE;
    $resultSetOptGrpId = NULL;
    if ((CRM_Utils_Array::value('option_type', $params) == 2) &&
      CRM_Utils_Array::value('option_group_id', $params)
    ) {
      $updateResultSet = TRUE;
      $resultSetOptGrpId = $params['option_group_id'];
    }

    $recontactInterval = array();
    if ($updateResultSet) {
      $optionValue = new CRM_Core_DAO_OptionValue();
      $optionValue->option_group_id = $resultSetOptGrpId;
      $optionValue->delete();

      $params['result_id'] = $resultSetOptGrpId;
    }
    else {
      $opGroupName = 'civicrm_survey_' . rand(10, 1000) . '_' . date('YmdHis');

      $optionGroup = new CRM_Core_DAO_OptionGroup();
      $optionGroup->name = $opGroupName;
      $optionGroup->title = $this->_values['title'] . ' Result Set';
      $optionGroup->is_active = 1;
      $optionGroup->save();

      $params['result_id'] = $optionGroup->id;
    }

    foreach ($params['option_value'] as $k => $v) {
      if (strlen(trim($v))) {
        $optionValue = new CRM_Core_DAO_OptionValue();
        $optionValue->option_group_id = $params['result_id'];
        $optionValue->label = $params['option_label'][$k];
        $optionValue->name = CRM_Utils_String::titleToVar($params['option_label'][$k]);
        $optionValue->value = trim($v);
        $optionValue->weight = $params['option_weight'][$k];
        $optionValue->is_active = 1;

        if (CRM_Utils_Array::value('default_option', $params) &&
          $params['default_option'] == $k
        ) {
          $optionValue->is_default = 1;
        }

        $optionValue->save();

        if (CRM_Utils_Array::value($k, $params['option_interval'])) {
          $recontactInterval[$optionValue->label] = $params['option_interval'][$k];
        }
      }
    }

    $params['recontact_interval'] = serialize($recontactInterval);
    $survey = CRM_Campaign_BAO_Survey::create($params);

    parent::endPostProcess();
  }
}
