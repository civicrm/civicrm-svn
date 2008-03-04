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
 *
 * @package CRM
 * @copyright CiviCRM LLC (c) 2004-2007
 * $Id$
 *
 */

require_once 'CRM/Event/Form/Registration.php';

/**
 * This class generates form components for processing Event  
 * 
 */
class CRM_Event_Form_Registration_ThankYou extends CRM_Event_Form_Registration
{
    /** 
     * Function to set variables up before form is built 
     *                                                           
     * @return void 
     * @access public 
     */ 
    function preProcess( ) {
        parent::preProcess( );
        $this->_params = $this->get( 'params' );
        $this->_lineItem = $this->get( 'lineItem' );
        
        CRM_Utils_System::setTitle(CRM_Utils_Array::value('thankyou_title',$this->_values['event_page']));
    }

    /**
     * overwrite action, since we are only showing elements in frozen mode
     * no help display needed
     * @return int
     * @access public
     */
    function getAction( ) 
    {
        if ( $this->_action & CRM_Core_Action::PREVIEW ) {
            return CRM_Core_Action::VIEW | CRM_Core_Action::PREVIEW;
        } else {
            return CRM_Core_Action::VIEW;
        }
    }

    /** 
     * Function to build the form 
     * 
     * @return None 
     * @access public 
     */ 
    public function buildQuickForm( )  
    { 
        $this->assignToTemplate( );

        $this->buildCustom( $this->_values['custom_pre_id'] , 'customPre'  );
        $this->buildCustom( $this->_values['custom_post_id'], 'customPost' );

        $this->assign( 'lineItem', $this->_lineItem );

        if( $this->_params['amount'] == 0 ) {
            $this->assign( 'isAmountzero', 1 );
        }
        $defaults = array( );
        $fields   = array( );
        if( ! empty($this->_fields) ) {
            foreach ( $this->_fields as $name => $dontCare ) {
                $fields[$name] = 1;
            }
        }
        $fields['state_province'] = $fields['country'] = $fields['email'] = 1;
        foreach ($fields as $name => $dontCare ) {
            if ( isset($this->_params[$name]) ) {
                $defaults[$name] = $this->_params[$name];
            }
        }
        $this->setDefaults( $defaults );
        
        require_once 'CRM/Friend/BAO/Friend.php';
        
        $params['entity_id']    = $this->_id;
        $params['entity_table'] = 'civicrm_event_page';
        
        CRM_Friend_BAO_Friend::retrieve( $params, $data ) ;
        if ( $data['is_active'] ) {               
            $friendText = $data['title'];
            $this->assign( 'friendText', $friendText );
            if( $this->_action & CRM_Core_Action::PREVIEW ) {
                $url = CRM_Utils_System::url("civicrm/friend", 
                                             "eid={$this->_id}&reset=1&action=preview&page=event" );
            } else {
                $url = CRM_Utils_System::url("civicrm/tell_a_friend", 
                                             "eid={$this->_id}&reset=1&page=event" );   
            }                    
            $this->assign( 'friendURL', $url );
        }
                             
        $this->freeze();
        
    }
    
    /**
     * Function to process the form
     *
     * @access public
     * @return None
     */
    public function postProcess() 
    {
    }//end of function
    
    /**
     * Return a descriptive name for the page, used in wizard header
     *
     * @return string
     * @access public
     */
    public function getTitle( ) 
    {
        return ts('Thank You Page');
    }
    
}

