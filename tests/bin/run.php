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
require_once 'tests/phpunit/CiviTest/civicrm.settings.php';

$needle = "tests" . DIRECTORY_SEPARATOR . "phpunit" . DIRECTORY_SEPARATOR;
if (empty($argv[1])) {
  die("The test file to run is mandatory.\n Usage: php tests/bin/run.php tests/phpunit/api/v3/SyntaxConformanceAllEntitiesTest.php\n");
}
$testFile = $argv[1];
// .php
$fileName           = substr($testFile, strpos($testFile, $needle) + strlen($needle), -4);
$className          = str_replace(DIRECTORY_SEPARATOR, '_', $fileName);
$_SERVER['argv'][1] = $className;
require_once 'PHPUnit/Util/Filter.php';

PHPUnit_Util_Filter::addFileToFilter(__FILE__, 'PHPUNIT');
require 'PHPUnit/TextUI/Command.php';
define('PHPUnit_MAIN_METHOD', 'PHPUnit_TextUI_Command::main');
require_once 'CRM/Core/ClassLoader.php';
$classLoader = new CRM_Core_ClassLoader();
$classLoader->register();
$command = new PHPUnit_TextUI_Command;
$command->run($_SERVER['argv'], TRUE);

