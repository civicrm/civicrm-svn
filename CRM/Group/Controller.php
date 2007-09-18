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

require_once 'CRM/Core/Controller.php';

class CRM_Group_Controller extends CRM_Core_Controller {

    /**
     * class constructor
     */
    function __construct( $title = null, $action = CRM_Core_Action::NONE, $modal = true ) {
        parent::__construct( $title, $modal );

        require_once 'CRM/Group/StateMachine.php';
        $this->_stateMachine =& new CRM_Group_StateMachine( $this, $action );

        // create and instantiate the pages
        $this->addPages( $this->_stateMachine, $action );

        // hack for now, set Search to Basic mode
        $this->_pages['Search']->setAction( CRM_Core_Action::BASIC );

        // add all the actions
        $config =& CRM_Core_Config::singleton( );
        
        //changes for custom data type File
        $session = & CRM_Core_Session::singleton( );
        $uploadNames = $session->get( 'uploadNames' );
        $config =& CRM_Core_Config::singleton( );
        
        if ( is_array( $uploadNames ) && ! empty ( $uploadNames ) ) {
            $uplaodArray = $uploadNames;
            $this->addActions( $config->customFileUploadDir, $uplaodArray );
            $uploadNames = $session->set( 'uploadNames',null );
        } else {
            $this->addActions( );
        }
    }

    function run( ) {
        return parent::run( );
    }

}

?>
