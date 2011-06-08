<?php

/*
 +--------------------------------------------------------------------+
 | CiviCRM version 3.4                                                |
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

require_once 'CRM/Core/Component/Config.php';

class CRM_Mailing_Config extends CRM_Core_Component_Config {

    /**
      * What should be the verp separator we use
      *
      * @var char
      */
    public $verpSeparator = '.';

    /**
     * How long should we wait before checking for new outgoing mailings?
     *
     * @var int
     */
    public $mailerPeriod    = 180;

   /**
    * TODO
    *
    * @var int
    */
    public $mailerSpoolLimit = 0;
                           
   /**
    * How many emails should CiviMail deliver on a given run
    *
    * @var int
    */
    public $mailerBatchLimit = 0;

    /**
     * How large should each mail thread be
     *
     * @var int
     */
    public $mailerJobSize = 0;

}


