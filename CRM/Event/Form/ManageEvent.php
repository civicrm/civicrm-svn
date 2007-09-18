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

require_once 'CRM/Core/Form.php';

/**
 * This class generates form components for processing Event  
 * 
 */
class CRM_Event_Form_ManageEvent extends CRM_Core_Form
{
    /**
     * the id of the event we are proceessing
     *
     * @var int
     * @protected
     */
    public $_id;

    /**
     * is this the first page?
     *
     * @var boolean 
     * @access protected  
     */  
    protected $_first = false;
 
    /** 
     * are we in single form mode or wizard mode?
     * 
     * @var boolean
     * @access protected 
     */ 
    protected $_single;
    
    
    /** 
     * Function to set variables up before form is built 
     *                                                           
     * @return void 
     * @access public 
     */ 
    function preProcess( ) 
    {
        $this->_action = CRM_Utils_Request::retrieve('action', 'String', $this, false);

        $this->_id = CRM_Utils_Request::retrieve( 'id', 'Positive', $this );

        $this->_single = $this->get( 'single' );

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
        $defaults = array( );
        if ( isset( $this->_id ) ) {
            $params = array( 'id' => $this->_id );
            require_once 'CRM/Event/BAO/Event.php';
            CRM_Event_BAO_Event::retrieve($params, $defaults);
        } else {
            $defaults['is_active'] = 1;
            $defaults['style']     = 'Inline';
        }
        
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
        $className = CRM_Utils_System::getClassName($this);
        $session = & CRM_Core_Session::singleton( );
        $uploadNames = $session->get( 'uploadNames' );
        if ( is_array( $uploadNames ) && ! empty ( $uploadNames ) 
             && $className == 'CRM_Event_Form_ManageEvent_EventInfo' ) {
            $buttonType = 'upload';
        } else {
            $buttonType = 'next';
        }

        $buttons = array( );
        if ( $this->_single ) {
            $this->addButtons(array(
                                    array ( 'type'      => $buttonType,
                                            'name'      => ts('Save'),
                                            'spacing'   => '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;',
                                            'isDefault' => true   ),
                                    array ( 'type'      => 'cancel',
                                            'name'      => ts('Cancel') ),
                                    )
                              );
        } else {
            $buttons = array( );
            if ( ! $this->_first ) {
                $buttons[] =  array ( 'type'      => 'back', 
                                      'name'      => ts('<< Previous'), 
                                      'spacing'   => '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;' );
            }
            $buttons[] = array ( 'type'      => $buttonType,
                                 'name'      => ts('Continue >>'),
                                 'spacing'   => '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;',
                                 'isDefault' => true   );
            $buttons[] = array ( 'type'      => 'cancel',
                                 'name'      => ts('Cancel') );
            
            $this->addButtons( $buttons );

        }
    }
}
?>
