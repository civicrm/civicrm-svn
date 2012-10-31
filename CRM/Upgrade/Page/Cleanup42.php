<?php
/*
 +--------------------------------------------------------------------+
 | CiviCRM version 4.2                                                |
 +--------------------------------------------------------------------+
 | Copyright CiviCRM LLC (c) 2004-2012                                |
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
class CRM_Upgrade_Page_Cleanup42  extends CRM_Core_Page {
  public function run() {
    $rows     = CRM_Upgrade_Incremental_php_FourTwo::deleteInvalidPairs();
    $template = CRM_Core_Smarty::singleton();

    $columnHeaders = array("Contact ID", "ContributionID", "Contribution Status", "MembershipID", 
                           "Membership Type", "Start Date", "End Date", "Membership Status", "Action");
    $template->assign('columnHeaders', $columnHeaders);
    $template->assign('rows', $rows);

    $message = !empty($rows) ? ts('The following records have been processed. Membership records with action:') : ts('Could not find any records to process.');
    $template->assign('message', $message);

    $content = $template->fetch('CRM/common/upgradeCleanup.tpl');
    echo CRM_Utils_System::theme('page', $content, TRUE, $this->_print, FALSE, TRUE);
  }
}