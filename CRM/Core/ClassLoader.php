<?php
/* 
 +--------------------------------------------------------------------+ 
 | CiviCRM version 4.1                                                | 
 +--------------------------------------------------------------------+ 
 | Copyright CiviCRM LLC (c) 2004-2011                                | 
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
 * 
 * @package CRM 
 * @copyright CiviCRM LLC (c) 2004-2011 
 * $Id$ 
 * 
 */ 

class CRM_Core_ClassLoader {

    /**
     * Registers this instance as an autoloader.
     *
     * @param Boolean $prepend Whether to prepend the autoloader or not
     *
     * @api
     */
    function register($prepend = false) {
        if (version_compare(PHP_VERSION, '5.3.0') >= 0) {
            spl_autoload_register(array($this, 'loadClass'), true, $prepend);
        }
        else {
            // http://www.php.net/manual/de/function.spl-autoload-register.php#107362
            // "when specifying the third parameter (prepend), the function will fail badly in PHP 5.2"
            spl_autoload_register(array($this, 'loadClass'), true);
        }
    }

    function loadClass($class) {
        if (
            // Only load classes that clearly belong to CiviCRM.
            0 === strncmp($class, 'CRM_', 4) &&
            // Do not load PHP 5.3 namespaced classes.
            // (in a future version, maybe)
            FALSE === strpos($class, '\\')
        ) {
            $file = strtr($class, '_', '/') . '.php';
            // There is some question about the best way to do this.
            // "require_once" is nice because it's simple and throws
            // intelligible errors.  The down side is that autoloaders
            // down the chain cannot try to find the file if we fail.
            require_once($file);
        }
    }
}
