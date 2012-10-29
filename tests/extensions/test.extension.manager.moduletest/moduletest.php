<?php

function _moduletest_inc($name) {
  global $_test_extension_manager_moduletest_counts;
  $_test_extension_manager_moduletest_counts[$name] = 1 + (int) $_test_extension_manager_moduletest_counts[$name];
}

/**
 * Implemenation of hook_civicrm_install
 */
function moduletest_civicrm_install() {
  _moduletest_inc('install');
}

/**
 * Implemenation of hook_civicrm_uninstall
 */
function moduletest_civicrm_uninstall() {
  _moduletest_inc('uninstall');
}

/**
 * Implemenation of hook_civicrm_enable
 */
function moduletest_civicrm_enable() {
  _moduletest_inc('enable');
}

/**
 * Implemenation of hook_civicrm_disable
 */
function moduletest_civicrm_disable() {
  _moduletest_inc('disable');
}
