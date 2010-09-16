<?php

/*
 +--------------------------------------------------------------------+
 | CiviCRM version 3.2                                                |
 +--------------------------------------------------------------------+
 | Copyright CiviCRM LLC (c) 2004-2010                                |
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
 * @copyright CiviCRM LLC (c) 2004-2010
 * $Id$
 *
 */

require_once 'CRM/Core/Page.php';
require_once 'CRM/Contribute/DAO/ContributionPage.php';

/**
 * Create a page for displaying Contribute Pages
 * Contribute Pages are pages that are used to display
 * donations of different types. Pages consist
 * of many customizable sections which can be
 * accessed.
 *
 * This page provides a top level browse view
 * of all the donation pages in the system.
 *
 */
class CRM_Contribute_Page_ContributionPageEdit extends CRM_Core_Page
{

    /**
     * Run the page.
     *
     * This method is called after the page is created. It checks for the  
     * type of action and executes that action.
     * Finally it calls the parent's run method.
     *
     * @return void
     * @access public
     *
     */
    function run()
    {
        // get the requested action
        $action = CRM_Utils_Request::retrieve('action', 'String',
                                              $this, false, 'browse'); // default to 'browse'
        
        $config = CRM_Core_Config::singleton( );
        if ( in_array("CiviMember", $config->enableComponents) ) {
            $this->assign('CiviMember', true );
        }
        
        // assign vars to templates
        $this->assign('action', $action);
        $this->_id = CRM_Utils_Request::retrieve('id', 'Positive',
                                                 $this, false, 0);
        
        if ( ! $this->_id ) {
            $dao = new CRM_Contribute_DAO_ContributionPage( ); 
            $dao->save( ); 
 
            $this->_id = $dao->id; 
            $this->set( 'id', $dao->id );
        }

        $this->assign( 'id', $this->_id );
        $this->assign( 'title', CRM_Core_DAO::getFieldValue( 'CRM_Contribute_DAO_ContributionPage', $this->_id, 'title'));
        $this->assign( 'is_active', CRM_Core_DAO::getFieldValue( 'CRM_Contribute_DAO_ContributionPage', $this->_id, 'is_active'));
        CRM_Utils_System::setTitle( ts('Configure Contribution Page') );
       
        return parent::run();
    }

}

