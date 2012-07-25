<?php
// vim: set si ai expandtab tabstop=4 shiftwidth=4 softtabstop=4:

/**
 *  File for the CiviUnitTestCase class
 *
 *  (PHP 5)
 *
 *   @copyright Copyright TTTP (C) 2011
 *   @license   http://www.fsf.org/licensing/licenses/agpl-3.0.html
 *              GNU Affero General Public License version 3
 *   @version   $Id: CiviUnitTestCase.php 37694 2011-11-25 11:23:15Z eileen $
 *   @package   CiviCRM
 *
 *   This file is part of CiviCRM
 *
 *   CiviCRM is free software; you can redistribute it and/or
 *   modify it under the terms of the GNU Affero General Public License
 *   as published by the Free Software Foundation; either version 3 of
 *   the License, or (at your option) any later version.
 *
 *   CiviCRM is distributed in the hope that it will be useful,
 *   but WITHOUT ANY WARRANTY; without even the implied warranty of
 *   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *   GNU Affero General Public License for more details.
 *
 *   You should have received a copy of the GNU Affero General Public
 *   License along with this program.  If not, see
 *   <http://www.gnu.org/licenses/>.
 */

$GLOBALS['base_dir'] = dirname(dirname(dirname(__FILE__)));
$tools_pkgs_dir      = $GLOBALS['base_dir'] . DIRECTORY_SEPARATOR . 'tools' . DIRECTORY_SEPARATOR . 'packages';
$tests_dir           = $GLOBALS['base_dir'] . DIRECTORY_SEPARATOR . 'tests' . DIRECTORY_SEPARATOR . 'phpunit';
$civi_pkgs_dir       = $GLOBALS['base_dir'] . DIRECTORY_SEPARATOR . 'packages';
ini_set('safe_mode', 0);
ini_set('include_path',
  "{$GLOBALS['base_dir']}" . PATH_SEPARATOR .
  "$tools_pkgs_dir" . PATH_SEPARATOR .
  "$tests_dir" . PATH_SEPARATOR .
  "$civi_pkgs_dir" . PATH_SEPARATOR . ini_get('include_path')
);
#  Relying on system timezone setting produces a warning,
#  doing the following prevents the warning message
if (file_exists('/etc/timezone')) {
  $timezone = trim(file_get_contents('/etc/timezone'));
  if (ini_set('date.timezone', $timezone) === FALSE) {
    echo "ini_set( 'date.timezone', '$timezone' ) failed\n";
  }
}

# Crank up the memory
ini_set('memory_limit', '2G');

error_reporting(E_ALL);

define('CIVICRM_SETTINGS_PATH', 'tests/phpunit/CiviTest/civicrm.settings.php');
require_once CIVICRM_SETTINGS_PATH;

if (empty($argv[1])) {
  $_SERVER['argv'][1] = "api_v3_AllTests";
  echo ("Running all api v3 tests.\n Tip: you can also limit the tests to one action: php tests/bin/api3.php [Entity] [Action]\n eg. php tests/bin/api3.php Contact\n or  php tests/bin/api3.php Contact Get\n");
}
else {
  if (strtolower($argv[1]) === $argv[1]) {
    die("FATAL: entity name (and action) must be CamelCased.\n Usage: php tests/bin/api3.php Contact #not contact.\n");
  }
  $className = "api_v3_" . $argv[1] . "Test";
  // action
  if (!empty($argv[2])) {
    if (strtolower($argv[2]) === $argv[2]) {
      die("FATAL: action name must be CamelCased.\n Usage: php tests/bin/api3.php " . $argv[1] . " Get #not get.\n");
    }
    $_SERVER['argv'][3] = $className;
    $_SERVER['argv'][1] = "--filter";
    $_SERVER['argc']++;
  }
  else {
    $_SERVER['argv'][1] = $className;
    echo ("Running all api tests for " . $argv[1] . "\n Tip: you can also limit the tests to one entity: php tests/bin/api3.php [Entity] [Action]\n eg. php tests/bin/api3.php " . $argv[1] . " Get");
  }
}

require_once 'PHPUnit/Util/Filter.php';
PHPUnit_Util_Filter::addFileToFilter(__FILE__, 'PHPUNIT');
require 'PHPUnit/TextUI/Command.php';
define('PHPUnit_MAIN_METHOD', 'PHPUnit_TextUI_Command::main');

require_once 'CRM/Core/ClassLoader.php';
CRM_Core_ClassLoader::singleton()->register();
$command = new PHPUnit_TextUI_Command;
$command->run($_SERVER['argv'], TRUE);

