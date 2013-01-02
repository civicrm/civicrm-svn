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
 * @see http://wiki.civicrm.org/confluence/display/CRM/CiviAccounts+Specifications+-++Batches#CiviAccountsSpecifications-Batches-%C2%A0Overviewofimplementation
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
    return $exportParams;
  }

  function output($fileName = NULL) {
    $tplFile = $this->getTemplateFileName();
    $out = self::getTemplate()->fetch( $tplFile );
        
    if ($this->getFileExtension() == 'csv') {
      self::createActivityExport($this->_exportParams['batchIds']['batchID'], $fileName);
      self::createCSVDownload($fileName);
    }
    else {
      self::createIIFDownload($out);
    }
  }

  function getMimeType() {
    return 'text/plain';
  }

  function getFileExtension() {
    return 'txt';
  }

  // Override this if appropriate
  function getTemplateFileName() {
    return null;
  }

  static function &getTemplate() {
    return self::$_template;
  }

  function assign($var, $value = NULL) {
    self::$_template->assign($var, $value);
  }

  /*
   * This gets called for every item of data being compiled before being sent to the exporter for output.
   * 
   * Depending on the output format might want to override this, e.g. for IIF tabs need to be escaped etc,
   * but for CSV it doesn't make sense because php has built in csv output functions.
   */
  static function format( $s, $type = 'string' ) {
    if (!empty($s)) {
      return $s;
    }
    else {
      return NULL;
    }
  }

  function createCSVDownload($fileName) {
    $config = CRM_Core_Config::singleton();
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename='.CRM_Utils_File::cleanFileName(basename($fileName)));
    readfile($config->customFileUploadDir.CRM_Utils_File::cleanFileName(basename($fileName)));
    CRM_Utils_System::civiExit();
  }

  function createIIFDownload($out) {
    $config = CRM_Core_Config::singleton();
    $fileName = $config->uploadDir.'Financial_Transactions_'.date('YmdHis').'.iif' ;
    $buffer = fopen($fileName, 'w');
    fwrite($buffer, $out);
    fclose($buffer);

    self::createActivityExport($this->_exportParams['batchIds']['batchID'], $fileName);
    header('Content-Type: text/plain');
    header('Content-Disposition: attachment; filename='.CRM_Utils_File::cleanFileName(basename($fileName)));
    readfile($config->customFileUploadDir.CRM_Utils_File::cleanFileName(basename($fileName)));
    CRM_Utils_System::civiExit();
  }

  static function createActivityExport($batchIds, $fileName) {
    $session = CRM_Core_Session::singleton();
    $values = array();
    $params = array('id' => $batchIds);
    CRM_Batch_BAO_Batch::retrieve($params, $values);

    $createdBy = CRM_Contact_BAO_Contact::displayName($values['created_id']);
    $modifiedBy = CRM_Contact_BAO_Contact::displayName($values['modified_id']);

    $values['payment_instrument_id'] = '';
    if (isset($values['payment_instrument_id'])) {
      $paymentInstrument = array_flip(CRM_Contribute_PseudoConstant::paymentInstrument('label'));
      $values['payment_instrument_id'] = array_search($values['payment_instrument_id'], $paymentInstrument);
    }
    $details = '<p>' . ts('Record: ') . $values['title'] . '</p><p>' . ts('Description: ') . $values['description'] . '</p><p>' . ts('Created By: ') . $createdBy . '</p><p>' . ts('Created Date: ') . $values['created_date'] . '</p><p>' . ts('Last Modified By: ') . $modifiedBy . '</p><p>' . ts('Payment Instrument: ') . $values['payment_instrument_id'] . '</p>';

    //create activity. 
    $activityTypes = CRM_Core_PseudoConstant::activityType(TRUE, FALSE, FALSE, 'name');
    $activityParams = array(
      'activity_type_id' => array_search('Export Accounting Batch', $activityTypes),
      'subject' => 'Total ['.$values['total'].'], Count ['.$values['item_count'].'], Batch ['.$values['title'].']',
      'status_id' => 2,
      'activity_date_time' => date('YmdHis'),
      'source_contact_id' => $session->get('userID'),
      'source_record_id' => $values['id'],
      'target_contact_id' => $session->get('userID'),
      'details' => $details,
      'attachFile_1' => array (
        'uri' => $fileName,
        'type' => 'text/csv',
        'location' => $fileName,
        'upload_date' => date('YmdHis'),
      ),
    );
    $activity = CRM_Activity_BAO_Activity::create($activityParams);
       
    return $activity;
  }
}