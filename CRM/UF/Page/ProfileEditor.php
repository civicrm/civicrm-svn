<?php

require_once 'CRM/Core/Page.php';

/**
 * This class is not a real page -- it contains helpers for rendering the profile-selector and profile-editor
 * widgets
 */
class CRM_UF_Page_ProfileEditor extends CRM_Core_Page {
  function run() {
    CRM_Core_Error::fatal('This is not a real page!');
  }

  static function registerProfileScripts() {
    static $loaded = FALSE;
    if ($loaded) {
      return;
    }
    $loaded = TRUE;

    CRM_Core_Resources::singleton()
      ->addSetting(array(
        'civiSchema' => self::getSchema(),
        'PseudoConstant' => array(
          'locationType' => CRM_Core_PseudoConstant::locationType(),
          'phoneType' => CRM_Core_PseudoConstant::phoneType(),
        ),
        'initialProfileList' => civicrm_api('UFGroup', 'get', array(
          'version' => 3,
          'sequential' => 1,
          'rowCount' => 1000, // FIXME
        )),
        'profilePreviewKey' => CRM_Core_Key::get('CRM_UF_Form_Inline_Preview', TRUE),
      ))
      ->addScriptFile('civicrm', 'packages/backbone/json2.js', 100, 'html-header', FALSE)
      ->addScriptFile('civicrm', 'packages/backbone/underscore.js', 110, 'html-header', FALSE)
      ->addScriptFile('civicrm', 'packages/backbone/backbone.js', 120, 'html-header')
      ->addScriptFile('civicrm', 'packages/backbone/backbone.marionette.js', 125, 'html-header', FALSE)
      ->addScriptFile('civicrm', 'packages/backbone/backbone.collectionsubset.js', 125, 'html-header', FALSE)
      ->addScriptFile('civicrm', 'packages/backbone-forms/distribution/backbone-forms.js', 130, 'html-header', FALSE)
      ->addScriptFile('civicrm', 'packages/backbone-forms/distribution/adapters/backbone.bootstrap-modal.min.js', 140, 'html-header', FALSE)
      ->addScriptFile('civicrm', 'packages/backbone-forms/distribution/editors/list.min.js', 140, 'html-header', FALSE)
      ->addStyleFile('civicrm', 'packages/backbone-forms/distribution/templates/default.css', 140, 'html-header')
      ->addStyleFile('civicrm', 'css/crm.designer.css', 140, 'html-header')
      ->addScriptFile('civicrm', 'js/crm.backbone.js', 150)
      ->addScriptFile('civicrm', 'js/model/crm.schema-mapped.js', 200)
      ->addScriptFile('civicrm', 'js/model/crm.uf.js', 200)
      ->addScriptFile('civicrm', 'js/model/crm.designer.js', 200)
      ->addScriptFile('civicrm', 'js/model/crm.profile-selector.js', 200)
      ->addScriptFile('civicrm', 'js/view/crm.designer.js', 200)
      ->addScriptFile('civicrm', 'js/view/crm.profile-selector.js', 200)
      ->addScriptFile('civicrm', 'js/jquery/jquery.crmProfileSelector.js', 250)
      ->addScriptFile('civicrm', 'js/crm.designerapp.js', 250);

    CRM_Core_Region::instance('page-header')->add(array(
      'template' => 'CRM/UF/Page/ProfileTemplates.tpl',
    ));
  }

  /**
   * AJAX callback
   */
  static function getSchemaJSON() {
    echo json_encode(self::getSchema());
    CRM_Utils_System::civiExit();
  }

  /**
   * Get a list of Backbone-Form models
   *
   * @return array; keys are model names ("IndividualModel") and values describe 'sections' and 'schema'
   * @see js/model/crm.core.js
   * @see js/model/crm.mappedcore.js
   */
  static function getSchema() {
    // FIXME: Depending on context (eg civicrm/profile/create vs search-columns), it may be appropriate to
    // pick importable or exportable fields
    $availableFields = CRM_Core_BAO_UFField::getAvailableFieldsFlat();

    $civiSchema['IndividualModel'] = self::convertCiviModelToBackboneModel(
      'Individual',
      ts('Individual'),
      CRM_Contact_BAO_Contact::importableFields('Individual', FALSE, FALSE, TRUE, TRUE, TRUE),
      $availableFields
    );

    $extends = array('Activity');
    $activityFields = CRM_Activity_BAO_Activity::getProfileFields();

    if ($activityFields) {
      // campaign related fields.
      if (isset($activityFields['activity_campaign_id'])) {
        $activityFields['activity_campaign_id']['title'] = ts('Campaign');
      }
    }

    $civiSchema['ActivityModel'] = self::convertCiviModelToBackboneModel(
      'Activity',
      ts('Activity'),
      $activityFields,
      $availableFields
    );

    return $civiSchema;
  }

  /**
   * FIXME: Move to somewhere more useful
   * FIXME: Do real mapping of "types"
   * FIXME: Correctly differentiate "field_type" for "Contact" vs "Individual"
   *
   * @param string $title a string to use in section headers
   * @param array $fields list of all core and custom fields which should be displayed
   * @param array $customGroups list of custom groups produced by getGroupDetail()
   * @param array $availableFields list of fields that are allowed in profiles, e.g. $availableFields['my_field']['field_type']
   * @return array with keys 'sections' and 'schema'
   * @see js/model/crm.core.js
   * @see js/model/crm.mappedcore.js
   */
  static function convertCiviModelToBackboneModel($extends, $title, $fields, $availableFields) {
    $locationFields = CRM_Core_BAO_UFGroup::getLocationFields();

    $result = array(
      'schema' => array(), // array($fieldName => $fieldSchema)
      'sections' => array(), // array($sectionName => $section)
    );

    // build field list
    foreach ($fields as $fieldName => $field) {
      if (!isset($field['type']) && !isset($field['html_type'])) {
        continue;
      }
      if (!isset($availableFields[$fieldName])) {
        continue;
      }
      $result['schema'][$fieldName] = array(
        'type' => 'Text', // FIXME,
        'title' => $field['title'],
        'civiFieldType' => $availableFields[$fieldName]['field_type'],
      );
      if (in_array($fieldName, $locationFields)) {
        $result['schema'][$fieldName]['civiIsLocation'] = TRUE;
      }
      if (in_array($fieldName, array('phone'))) { // FIXME what about phone_ext?
        $result['schema'][$fieldName]['civiIsPhone'] = TRUE;
      }
    }

    // build section list
    $result['sections']['default'] = array(
      'title' => $title,
      'is_addable' => FALSE,
    );

    $customGroup = CRM_Core_BAO_CustomGroup::getAllCustomGroupsByBaseEntity($extends);
    $customGroup->orderBy('weight');
    $customGroup->is_active = 1;
    $customGroup->find();
    while ($customGroup->fetch()) {
      $sectionName = 'cg_' . $customGroup->id;
      $section = array(
        'title' => ts('%1: %2', array(1 => $title, 2 => $customGroup->title)),
        'is_addable' => TRUE,
        'custom_group_id' => $customGroup->id,
        'extends_entity_column_id' => $customGroup->extends_entity_column_id,
        'extends_entity_column_value' => CRM_Utils_Array::explodePadded($customGroup->extends_entity_column_value),
      );
      $result['sections'][$sectionName] = $section;
    }

    // put fields in their sections
    $fields = CRM_Core_BAO_CustomField::getFields($extends);
    foreach ($fields as $fieldId => $field) {
      $sectionName = 'cg_' . $field['custom_group_id'];
      $fieldName = 'custom_' . $fieldId;
      if (isset($result['schema'][$fieldName])) {
        $result['schema'][$fieldName]['section'] = $sectionName;
      }
      $result['schema'][$fieldName]['civiIsMultiple'] = (bool) CRM_Core_BAO_CustomField::isMultiRecordField($fieldId);
    }
    return $result;
  }
}
