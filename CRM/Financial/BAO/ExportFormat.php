<?php
/*
 +--------------------------------------------------------------------+
 | CiviCRM version 4.2                                             |
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
 * @package CRM
 * @copyright CiviCRM LLC (c) 2004-2011
 * $Id$
 *
 */

/**
 * Base class for Export Formats
 * Create a subclass for a specific format.
 *
 */

class CRM_Financial_BAO_ExportFormat {
  
  /*
   * Array of data which the individual export formats will output in the desired format
   */
  protected $_exportParams;
  
  /*
   * smarty template
   */
  static protected $_template;
  
  /**
   * class constructor
   */
  function __construct() {
    if ( !isset( self::$_template ) ) {
      self::$_template = CRM_Core_Smarty::singleton();
    }
  }
  
  // Override to assemble the appropriate subset of financial data for the specific export format
  function export( $exportParams ) {
    $this->_exportParams = $exportParams;
  }

  function output() {
    self::getTemplate()->fetch( $this->getTemplateFileName() );  
  }
  
  // Override this if appropriate
  function getTemplateFileName() {
    return null;
  }
  
  static function &getTemplate() {
    return self::$_template;
  }

  static function assign($var, $value = NULL) {
    self::$_template->assign($var, $value);
  }
  
  /*
   * This gets called for every item of data being compiled before being sent to the exporter for output.
   * 
   * Depending on the output format might want to override this, e.g. for IIF tabs need to be escaped etc,
   * but for CSV it doesn't make sense because php has built in csv output functions.
   */
  static function format( $s, $type = 'string' ) {
    return $s;
  }
}