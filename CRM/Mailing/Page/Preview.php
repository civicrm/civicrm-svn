<?php 

/*
 +--------------------------------------------------------------------+
 | CiviCRM version 1.9                                                |
 +--------------------------------------------------------------------+
 | Copyright CiviCRM LLC (c) 2004-2007                                |
 +--------------------------------------------------------------------+
 | This file is a part of CiviCRM.                                    |
 |                                                                    |
 | CiviCRM is free software; you can copy, modify, and distribute it  |
 | under the terms of the Affero General Public License Version 1,    |
 | March 2002.                                                        |
 |                                                                    |
 | CiviCRM is distributed in the hope that it will be useful, but     |
 | WITHOUT ANY WARRANTY; without even the implied warranty of         |
 | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.               |
 | See the Affero General Public License for more details.            |
 |                                                                    |
 | You should have received a copy of the Affero General Public       |
 | License along with this program; if not, contact CiviCRM LLC       |
 | at info[AT]civicrm[DOT]org.  If you have questions about the       |
 | Affero General Public License or the licensing  of CiviCRM,        |
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

require_once 'CRM/Core/Page.php';
/**
 * a page for mailing preview
 */
class CRM_Mailing_Page_Preview extends CRM_Core_Page
{

    /** 
     * run this page (figure out the action needed and perform it).
     * 
     * @return void
     */ 
    function run()
    {
        require_once 'CRM/Mailing/BAO/Mailing.php';

        $session =& CRM_Core_Session::singleton();

        $options = array();
        $session->getVars($options, 'CRM_Mailing_Controller_Send_');
        
        $type = CRM_Utils_Request::retrieve('type', 'String', CRM_Core_DAO::$_nullObject, false, 'text');

        // FIXME: the below and CRM_Mailing_Form_Test::testMail()
        // should be refactored

        $mailing =& new CRM_Mailing_BAO_Mailing();
        $mailing->id = $options['mailing_id'];
        $mailing->find(true);
 
        $mime =& $mailing->compose(null, null, null, $session->get('userID'), $options['from_email'], $options['from_email'], true);

        // there doesn't seem to be a way to get to Mail_Mime's text and HTML
        // parts, so we steal a peek at Mail_Mime's private properties, render 
        // them and exit
        $mime->get();
        if ($type == 'html') {
            header('Content-Type: text/html; charset=utf-8');
            print $mime->_htmlbody;
        } else {
            header('Content-Type: text/plain; charset=utf-8');
            print $mime->_txtbody;
        }
        exit;
    }

}

?>
