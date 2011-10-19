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

require_once 'CRM/Event/Form/ManageEvent.php';
require_once 'CRM/Event/BAO/Event.php';
require_once 'CRM/Core/OptionGroup.php';

/**
 * This class generates form components for Conference Slots
 * 
 */
class CRM_Event_Form_ManageEvent_Conference extends CRM_Event_Form_ManageEvent
{
    /**
     * Page action
     */
    public $_action;

    /**
     * in Date
     */
    private $_inDate;
    /** 
     * Function to set variables up before form is built 
     *                                                           
     * @return void 
     * @access public 
     */ 
    function preProcess( ) 
    {
        parent::preProcess( );
    }

    /**
     * This function sets the default values for the form. For edit/view mode
     * the default values are retrieved from the database
     *
     * @access public
     * @return None
     */
    function setDefaultValues( )
    {  
        $parentDefaults = parent::setDefaultValues( );
        
        $eventId = $this->_id;
        $params   = array( );
        $defaults = array( );
        if ( isset( $eventId ) ) {
            $params = array( 'id' => $eventId );
        }
        
        CRM_Event_BAO_Event::retrieve( $params, $defaults );
               
        if ( isset( $eventId ) ) {
                //$defaults['price_set_id'] = $price_set_id;
        }

        $defaults = array_merge( $defaults, $parentDefaults );
        $defaults['id'] = $eventId;
        
        //$this->assign('inDate', $this->_inDate );
       
        return $defaults;
    }
    
    /**
     * Function to build the form
     *
     * @return None
     * @access public
     */
    public function buildQuickForm( ) 
    {
        require_once 'CRM/Utils/Money.php';

        $this->addElement('textarea', 'slot_label', ts( 'Slot Label' ),  
                          CRM_Core_DAO::getAttribute( 'CRM_Event_DAO_Event', 'slot_label' ), 
                          false );

        $this->addElement('textarea', 'parent_event_id', ts( 'Parent Event' ),  
                          CRM_Core_DAO::getAttribute( 'CRM_Event_DAO_Event', 'parent_event_id' ), 
                          false );
        parent::buildQuickForm();
    }
    
    public function postProcess()
    {
        $params = array( );
        $params = $this->exportValues( );
        
        //update events table
        require_once 'CRM/Event/BAO/Event.php';
        $params['id'] = $this->_id;
        CRM_Event_BAO_Event::add( $params );

        parent::endPostProcess( );
    }
    
    /**
     * Return a descriptive name for the page, used in wizard header
     *
     * @return string
     * @access public
     */
    public function getTitle( ) 
    {
        return ts('Conference Slots');
    }

}
