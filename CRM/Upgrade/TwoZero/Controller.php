<?php

/*
 +--------------------------------------------------------------------+
 | CiviCRM version 2.0                                                |
 +--------------------------------------------------------------------+
 | Copyright CiviCRM LLC (c) 2004-2007                                |
 +--------------------------------------------------------------------+
 | This file is a part of CiviCRM.                                    |
 |                                                                    |
 | CiviCRM is free software; you can copy, modify, and distribute it  |
 | under the terms of the GNU Affero General Public License           |
 | Version 3, 19 November 2007.                                       |
 |                                                                    |
 | CiviCRM is distributed in the hope that it will be useful, but     |
 | WITHOUT ANY WARRANTY; without even the implied warranty of         |
 | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.               |
 | See the GNU Affero General Public License for more details.        |
 |                                                                    |
 | You should have received a copy of the GNU Affero General Public   |
 | License along with this program; if not, contact CiviCRM LLC       |
 | at info[AT]civicrm[DOT]org. If you have questions about the        |
 | GNU Affero General Public License or the licensing of CiviCRM,     |
 | see the CiviCRM license FAQ at http://civicrm.org/licensing        |
 +--------------------------------------------------------------------+
*/

/**
 *
 * @package CRM
 * @copyright CiviCRM LLC (c) 2004-2007
 * $Id$
 *
 */

require_once 'CRM/Upgrade/Controller.php';

class CRM_Upgrade_TwoZero_Controller extends CRM_Upgrade_Controller {

    function &getPages( ) {
        return array( 'CRM_Upgrade_TwoZero_Form_Step1' => null,
                      'CRM_Upgrade_TwoZero_Form_Step2' => null,
                      'CRM_Upgrade_TwoZero_Form_Step3' => null,
                      'CRM_Upgrade_TwoZero_Form_Step4' => null,
                      'CRM_Upgrade_TwoZero_Form_Step5' => null,
                      'CRM_Upgrade_TwoZero_Form_Step6' => null
                      );
    }

}

?>
