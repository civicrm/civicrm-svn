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

require_once 'CRM/Core/Form.php';

/**
 * This class generates form components for Location Type
 * 
 */
class CRM_Admin_Form_Preferences extends CRM_Core_Form
{
    protected $_system    = false;
    protected $_contactID = null;
    protected $_action    = null;

    protected $_cbs       = null;

    protected $_checkbox  = null;

    protected $_varNames  = null;

    protected $_config    = null;

    protected $_params    = null;

    function preProcess( ) {
        $this->_contactID = CRM_Utils_Request::retrieve( 'cid', 'Positive',
                                                         $this, false );
        $this->_system    = CRM_Utils_Request::retrieve( 'system', 'Boolean',
                                                         $this, false, true );
        $this->_action    = CRM_Utils_Request::retrieve( 'action', 'String',
                                                         $this, false, 'update' );
        if ( isset($action) ) {
            $this->assign( 'action', $action );
        }

        $session = CRM_Core_Session::singleton( );

        $this->_config = new CRM_Core_DAO( );

        if ( $this->_system ) {
            if ( CRM_Core_Permission::check( 'administer CiviCRM' ) ) {
                $this->_contactID = null;
            } else {
                CRM_Utils_System::fatal( 'You do not have permission to edit preferences' );
            }
            $this->_config->contact_id = null;
        } else {
            if ( ! $this->_contactID ) {
                $this->_contactID = $session->get( 'userID' );
                if ( ! $this->_contactID ) {
                    CRM_Utils_System::fatal( 'Could not retrieve contact id' );
                }
                $this->set( 'cid', $this->_contactID );
            }
            $this->_config->contact_id = $this->_contactID;
        }

        require_once 'CRM/Core/BAO/Setting.php';
        foreach ( $this->_varNames as $groupName => $settingNames ) {
            $values = CRM_Core_BAO_Setting::getItem( $groupName );
            foreach ( $values as $name => $value ) {
                $this->_config->$name = $value;
            }
        }
        $session->pushUserContext( CRM_Utils_System::url('civicrm/admin/setting', 'reset=1') );
    }

    function cbsDefaultValues( &$defaults ) {
        if ( empty( $this->_cbs ) ) {
            return;
        }

        require_once 'CRM/Core/BAO/CustomOption.php';
        foreach ( $this->_cbs as $name => $title ) {
            if ( isset( $this->_config->$name ) &&
                 $this->_config->$name ) {
                $value = explode( CRM_Core_DAO::VALUE_SEPARATOR,
                                  substr( $this->_config->$name, 1, -1 ) );
                if ( ! empty( $value ) ) {
                    $defaults[$name] = array( );
                    foreach ( $value as $n => $v ) {
                        $defaults[$name][$v] = 1;
                    }
                }
            }
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
        parent::buildQuickForm( );

        require_once 'CRM/Core/OptionGroup.php';
        if ( ! empty( $this->_cbs ) ) {
            foreach ( $this->_cbs as $name => $title ) {
                $options = array_flip( CRM_Core_OptionGroup::values( $name, false, false, true ) );
                $newOptions = array( );
                foreach ( $options as $key => $val ) {
                    $newOptions[ $key ] = $val;
                }
                $this->addCheckBox( $name, $title, 
                                    $newOptions,
                                    null, null, null, null,
                                    array( '&nbsp;&nbsp;', '&nbsp;&nbsp;', '<br/>' ) );
            }
        }

        if ( ! empty( $this->_checkbox ) ) {
            foreach ( $this->_checkbox as $name => $title ) {
                $this->addElement( 'checkbox',
                                   $name,
                                   $title );
            }
        }

        if ( ! empty( $this->_text ) ) {
            foreach ( $this->_text as $name => $title ) {
                $this->addElement( 'text',
                                   $name,
                                   $title,
                                   array( 'maxlength' => 64,
                                          'size'      => 32 ) );
            }
        }

        if ( ! empty( $this->_varNames ) ) {
            foreach ( $this->_varNames as $groupName => $groupValues ) {
                foreach ( $groupValues as $fieldName => $fieldValue ) {
                    if ( $fieldValue['html_type'] == 'text' ) {
                        $this->addElement( 'text',
                                           $fieldName,
                                           $fieldValue['title'],
                                           array( 'maxlength' => 64,
                                                  'size'      => 32 ) );
                    }

                    if ( $fieldValue['html_type'] == 'textarea' ) {
                        $this->addElement( 'textarea',
                                           $fieldName,
                                           $fieldValue['title'] );
                    }

                    if ( $fieldValue['html_type'] == 'checkbox' ) {
                        $this->addElement( 'checkbox',
                                           $fieldName,
                                           $fieldValue['title'] );
                    }
                }
            }
        }

        $this->addButtons( array(
                                 array ( 'type'      => 'next',
                                         'name'      => ts('Save'),
                                         'isDefault' => true   ),
                                 array ( 'type'      => 'cancel',
                                         'name'      => ts('Cancel') ),
                                 )
                           );

        if ($this->_action == CRM_Core_Action::VIEW ) {
            $this->freeze( );
        }
       
    }

    /**
     * Function to process the form
     *
     * @access public
     * @return None
     */
    public function postProcess() 
    {
        if ( ! empty( $this->_cbs ) ) {
            foreach ( $this->_cbs as $name => $title ) {
                if ( CRM_Utils_Array::value( $name, $this->_params ) &&
                     is_array( $this->_params[$name] ) ) {
                    $this->_config->$name = 
                        CRM_Core_DAO::VALUE_SEPARATOR .
                        implode( CRM_Core_DAO::VALUE_SEPARATOR,
                                 array_keys( $this->_params[$name] ) ) .
                        CRM_Core_DAO::VALUE_SEPARATOR;
                } else {
                    $this->_config->$name = null;
                }
            }
        }

        if ( ! empty( $this->_checkbox ) ) {
            foreach ( $this->_checkbox as $name => $title ) {
                $this->_config->$name = CRM_Utils_Array::value( $name, $this->_params ) ? 1 : 0;
            }
        }
 
        if ( ! empty( $this->_text ) ) {
            foreach ( $this->_text as $name => $title ) {
                $this->_config->$name = CRM_Utils_Array::value( $name, $this->_params );
            }
        }

        foreach ( $this->_varNames as $groupName => $groupValues ) {
            foreach ( $groupValues as $settingName => $fieldValues ) {
                $settingValue = isset( $this->_config->$settingName ) ? $this->_config->$settingName : null;
                CRM_Core_BAO_Setting::setItem( $settingValue,
                                               $groupName,
                                               $settingName );
            }
        }
    }//end of function

}


