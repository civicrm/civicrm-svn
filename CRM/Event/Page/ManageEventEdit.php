<?php

/*
 +--------------------------------------------------------------------+
 | CiviCRM version 2.1                                                |
 +--------------------------------------------------------------------+
 | Copyright CiviCRM LLC (c) 2004-2008                                |
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

require_once 'CRM/Core/Page.php';
require_once 'CRM/Event/DAO/Event.php';


class CRM_Event_Page_ManageEventEdit extends CRM_Core_Page {

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
        
        $config =& CRM_Core_Config::singleton( );
        if ( in_array("CiviEvent", $config->enableComponents) ) {
            $this->assign('CiviEvent', true );
        }

        $this->_id = CRM_Utils_Request::retrieve('id', 'Positive',
                                                 $this, false, 0);
        // assign vars to templates
        $this->assign('action', $action);
        $this->assign( 'id', $this->_id );
        
        $subPage = CRM_Utils_Request::retrieve('subPage', 'String',
                                               $this );
        
        $this->assign( 'title', CRM_Core_DAO::getFieldValue( 'CRM_Event_DAO_Event', $this->_id, 'title'));

        $title = CRM_Core_DAO::getFieldValue( 'CRM_Event_DAO_Event', $this->_id, 'title');
        CRM_Utils_System::setTitle(ts('Configure Event - %1', array(1 => $title)));

        $participantListingID = CRM_Core_DAO::getFieldValue( 'CRM_Event_DAO_Event',
                                                           $this->_id,
                                                           'participant_listing_id' );
        if ( $participantListingID ) {
            $participantListingURL = CRM_Utils_System::url( 'civicrm/event/participant',
                                                            "reset=1&id={$this->_id}" );
            $this->assign( 'participantListingURL', $participantListingURL );
        }

        $form = null;
        switch ( $subPage ) {
      
        case 'EventInfo':
            $form = 'CRM_Event_Form_ManageEvent_EventInfo';
            break;

        case 'Location':
            $form = 'CRM_Event_Form_ManageEvent_Location';
            break;

        case 'Fee':
            $form = 'CRM_Event_Form_ManageEvent_Fee';
            break;

        case 'Registration':
            $form = 'CRM_Event_Form_ManageEvent_Registration';
            break;

        case 'Friend':
            $form = 'CRM_Friend_Form_Event';
            break;
        }

        if ( $form ) {
            $session =& CRM_Core_Session::singleton( );

            require_once 'CRM/Core/Controller/Simple.php'; 
            $controller =& new CRM_Core_Controller_Simple($form, $subPage, $action); 
            $session =& CRM_Core_Session::singleton(); 
            $session->pushUserContext( CRM_Utils_System::url( CRM_Utils_System::currentPath( ), 'action=update&reset=1&id=' . $this->_id ) );
            $controller->set('id', $this->_id); 
            $controller->set('single', true );
            $controller->process(); 
            return $controller->run(); 
        }

        return parent::run();
    }


    /**
     * Browse Manage Event
     *
     * @return void
     * @access public
     * @static
     */
    function browse($action=null)
    {
        
        // get all custom groups sorted by weight
        $event =  array();
        $dao      =& new CRM_Event_DAO_Event();

        // set the domain_id parameter
        $config =& CRM_Core_Config::singleton( );
        $dao->domain_id = $config->domainID( );

        $dao->orderBy('title');
        $dao->find();

        while ($dao->fetch()) {
           
            // form all action links
            $action = array_sum(array_keys($this->actionLinks()));
            
            // update enable/disable links depending on custom_group properties.
            if ($dao->is_active) {
                $action -= CRM_Core_Action::ENABLE;
            } else {
                $action -= CRM_Core_Action::DISABLE;
            }
            
            $event[$dao->id]['action'] = CRM_Core_Action::formLink(self::actionLinks(), $action, 
                                                                          array('id' => $dao->id));
        }
        $this->assign('rows', $event);
    }
}

