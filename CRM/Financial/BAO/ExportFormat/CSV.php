<?php

/*
 +--------------------------------------------------------------------+
 | CiviCRM version 4.2                                                |
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

// **************************************
// FIXME: This doesn't do anything. PHP has built-in csv functions, and there will be multiple output files,
// so this is different from IIF.
// **************************************
// TODO: For csv we need to export multiple files. Create a ZIP?
// TODO: There's some csv export code in CRM_Export_BAO_Export that first writes to a temp db table and then
// exports. Decide if want to follow same strategy or just go straight to filesystem.
/*
    CRM_Utils_System::download(CRM_Utils_String::munge($fileName),
      'text/x-csv',
      CRM_Core_DAO::$_nullObject,
      'csv',
      FALSE
    );
 */

class CRM_Financial_BAO_ExportFormat_CSV extends CRM_Financial_BAO_ExportFormat {

  // For this phase, we always output these records too so that there isn't data referenced in the journal entries that isn't defined anywhere.
  // Possibly in the future this could be selected by the user.
  public static $complementaryTables = array(
      'ACCNT',
      'CUST',
  );
  
  /**
   * class constructor
   */
  function __construct() {
    parent::__construct();
  }

  function export( $exportParams ) {   
    parent::export( $exportParams );
    
    foreach( self::$complementaryTables as $rct ) {
      $func = "export{$rct}";
      $this->$func();
    }
    
    // now do general journal entries
    $this->exportTRANS();
    
    $this->output();
  }
  
  function exportACCNT() {
  }

  function exportCUST() {
  }
  
  function exportTRANS() {
  }
}