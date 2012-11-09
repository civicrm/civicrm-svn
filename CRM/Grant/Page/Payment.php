<?php

/*
 +--------------------------------------------------------------------+
 | CiviCRM version 4.0                                                |
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

require_once 'CRM/Core/Page.php';
require_once 'CRM/Grant/BAO/GrantProgram.php';


/**
 * Page for displaying list of contribution types
 */
class CRM_Grant_Page_Payment extends CRM_Core_Page
{

    
    function run( ) 
    {
        $action = CRM_Utils_Request::retrieve('action', 'String',
                                              $this, false, 0 );
        if ( $action & CRM_Core_Action::VIEW ) { 
            $this->view( $action ); 
        } elseif ( $action & ( CRM_Core_Action::STOP) ) {
            $this->stop( $action );
        } elseif ( $action & ( CRM_Core_Action::REPRINT ) ) {
            $this->reprint( $action ); 
        } else {
            $this->withdraw( $action ); 
        }
        $this->assign('action', $action);
        return parent::run( );
    }


    function view( $action ) 
    {   
        $controller = new CRM_Core_Controller_Simple( 'CRM_Grant_Form_Payment_View', ts(''), $action );
        $controller->setEmbedded( true );  
        $result = $controller->process();
        $result = $controller->run();
    }

    function stop( $action ) 
    {   
        $controller = new CRM_Core_Controller_Simple( 'CRM_Grant_Form_Payment_View', ts(''), $action );
        $controller->setEmbedded( true );  
        $result = $controller->process();
        $result = $controller->run();
    }

    function reprint( $action ) 
    {   
        $controller = new CRM_Core_Controller_Simple( 'CRM_Grant_Form_Payment_View', ts(''), $action );
        $controller->setEmbedded( true );  
        $result = $controller->process();
        $result = $controller->run();
    }

    function withdraw( $action ) 
    {   
        $controller = new CRM_Core_Controller_Simple( 'CRM_Grant_Form_Payment_View', ts(''), $action );
        $controller->setEmbedded( true );  
        $result = $controller->process();
        $result = $controller->run();
    }
}
