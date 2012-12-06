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

class CRM_Financial_BAO_ExportFormat_IIF extends CRM_Financial_BAO_ExportFormat {

  // Tab character. Some people's editors replace tabs with spaces so I'm scared to use actual tabs.
  // Can't set it here using chr() because static. Same thing if a const. So it's set in constructor.
  static $SEPARATOR;
  
  // For this phase, we always output these records too so that there isn't data referenced in the journal entries that isn't defined anywhere.
  // Possibly in the future this could be selected by the user.
  public static $complementaryTables = array(
      'ACCNT',
      'CUST',
  );
  
  // This field is required. We use the grouping column in civicrm_option_value for the financial_account_type option group to map to the right code.
  // - So this variable below isn't actually used anywhere, but is good to keep here for reference.
  public static $accountTypes = array(
    'AP' => 'Accounts payable',
    'AR' => 'Accounts receivable',
    'BANK' => 'Checking or savings',
    'CCARD' => 'Credit card account',
    'COGS' => 'Cost of goods sold',
    'EQUITY' => 'Capital/Equity',
    'EXEXP' => 'Other expense',
    'EXINC' => 'Other income',
    'EXP' => 'Expense',
    'FIXASSET' => 'Fixed asset',
    'INC' => 'Income',
    'LTLIAB' => 'Long term liability',
    'NONPOSTING' => 'Non-posting account',
    'OASSET' => 'Other asset',
    'OCASSET' => 'Other current asset',
    'OCLIAB' => 'Other current liability',
  );
  
  /**
   * class constructor
   */
  function __construct() {
    parent::__construct();
    self::$SEPARATOR = chr(9);
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
    self::assign( 'accounts', $this->_exportParams['accounts'] );
  }

  function exportCUST() {
    self::assign( 'contacts', $this->_exportParams['contacts'] );
  }
  
  function exportTRANS() {
    self::assign( 'journalEntries', $this->_exportParams['journalEntries'] );
  }
    
  function getTemplateFileName() {
    return 'CRM/Financial/ExportFormat/IIF.tpl';
  }

  /*
   * $s the input string
   * $type can be string, date, or notepad
   */
  static function format( $s, $type = 'string' ) {
    // If I remember right there's a couple things:
    // NOTEPAD field needs to be surrounded by quotes and then get rid of double quotes inside, also newlines should be literal \n, and ditch any ascii 0x0d's.
    // Date handling has changed over the years. It used to only understand mm/dd/yy but I think now it might depend on your OS settings. Sometimes mm/dd/yyyy works but sometimes it wants yyyy/mm/dd, at least where I had used it.
    // In all cases need to do something with tabs in the input.
    
    $s1 = str_replace( self::$SEPARATOR, '\t', $s );
    switch( $type ) {
      case 'date':
        $sout = date( 'Y/m/d', strtotime( $s1 ) );
        break;
      case 'string':
      case 'notepad':
        $s2 = str_replace( "\n", '\n', $s1 );
        $s3 = str_replace( "\r", '', $s2 );
        $s4 = str_replace( '"', "'", $s3 );
        if ( $type == 'notepad' ) {
          $sout = '"' . $s4 . '"';
        } else {
          $sout = $s4;
        }
        break;
    }
    
    return $sout;
  }
}