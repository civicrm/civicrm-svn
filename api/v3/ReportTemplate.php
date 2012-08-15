<?php

/**
 *  Add a OptionValue. OptionValues are used to classify CRM entities (including Contacts, Groups and Actions).
 *
 * Allowed @params array keys are:
 *
 * {@example OptionValueCreate.php}
 *
 * @return array of newly created option_value property values.
 * {@getfields OptionValue_create}
 * @access public
 */
function civicrm_api3_report_template_create($params) {
  require_once 'api/v3/OptionValue.php';
  $params['option_group_id'] = CRM_Core_DAO::getFieldValue(
    'CRM_Core_DAO_OptionGroup', 'report_template', 'id', 'name'
  );
  return civicrm_api3_option_value_create($params);
}

/*
 * Adjust Metadata for Create action
 * 
 * The metadata is used for setting defaults, documentation & validation
 * @param array $params array or parameters determined by getfields
 */
function _civicrm_api3_report_template_create_spec(&$params) {
  require_once 'api/v3/OptionValue.php';
  _civicrm_api3_option_value_create_spec($params);
  $params['weight']['api.default'] = 'next';
  $params['value']['api.aliases'] = array('report_url');
  $params['name']['api.aliases'] = array('class_name');
  // $params['component']['api.required'] = TRUE;
}

/**
 * Deletes an existing ReportTemplate
 *
 * @param  array  $params
 *
 * {@example ReportTemplateDelete.php 0}
 *
 * @return array Api result
 * {@getfields ReportTemplate_create}
 * @access public
 */
function civicrm_api3_report_template_delete($params) {
  require_once 'api/v3/OptionValue.php';
  return civicrm_api3_option_value_delete($params);
}
