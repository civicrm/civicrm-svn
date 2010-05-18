<?php

/*
 +--------------------------------------------------------------------+
 | CiviCRM version 3.1                                                |
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

require_once 'CRM/Core/Form.php';

class CRM_Admin_Form_StringOverrides extends CRM_Core_Form
{
    protected $_numStrings = 10;
    
    protected $_stringName = null;

    protected $_defaults = null;
    
    function preProcess( ) {
        $this->_addRow = CRM_Utils_Array::value( 'addrow', $_GET );
        if ( $this->_addRow ) {
            $this->assign('addRow', true);
        }
    }
    
    public function setDefaultValues( )
    {
        if ( $this->_defaults !== null ) {
            return $this->_defaults;
        }

        $this->_defaults = array( );
        
        $config = CRM_Core_Config::singleton( );
        
        $name = $this->_stringName = "custom_string_override_{$config->lcMessages}";
        if ( isset( $config->$name ) &&
             is_array( $config->$name ) ) {
            $this->_numStrings = 1;
            foreach ( $config->$name as $old => $newValues ) {
                $this->_defaults["old_{$this->_numStrings}"] = $old;
                $this->_defaults["new_{$this->_numStrings}"] = $newValues['str'];
                $this->_defaults["cb_{$this->_numStrings}"]  = $newValues['cb' ];
                $this->_numStrings++;
            }
            $this->_numStrings += 9;
        } else {
            $this->_numStrings = 10;
        }

        $this->assign( 'numStrings', $this->_numStrings );
        return $this->_defaults;
    }

    /**
     * Function to actually build the form
     *
     * @return None
     * @access public
     */
    public function buildQuickForm( )
    {
        for ( $i = 1 ; $i <= $this->_numStrings; $i++ ) {
            $this->addElement( 'checkbox',
                               "enabled_{$i}",
                               null );
            $this->add( 'textarea',
                        "old_{$i}",
                        null,
                        array( 'rows=1 cols=40' ) );
            $this->add( 'textarea',
                        "new_{$i}",
                        null,
                        array( 'rows=1 cols=40' ) );
            $this->addElement( 'checkbox',
                               "cb_{$i}",
                               null );
        }
        
        $this->addButtons( array(
                                 array ( 'type'      => 'next',
                                         'name'      => ts('Save'),
                                         'isDefault' => true   ),
                                 array ( 'type'      => 'cancel',
                                         'name'      => ts('Cancel') ),
                                 )
                           );
    }

    /**
     * Function to process the form
     *
     * @access public
     * @return None
     */
    public function postProcess() 
    {
        $params = $this->exportValues();
        $overrides['wildcardMatch'] = $overrides['exactMatch'] = array();


        for ( $i = 1 ; $i <= $this->_numStrings; $i++ ) {
            if ( CRM_Utils_Array::value( 'old_'.$i, $params ) && 
                 CRM_Utils_Array::value( 'new_'.$i, $params ) ) {
                if ( array_key_exists( 'cb_'.$i, $params) ) {
                    $overrides['wildcardMatch'] += array($params['old_'.$i]=>$params['new_'.$i]);
                } else {
                    $overrides['exactMatch'] += array($params['old_'.$i]=>$params['new_'.$i]);
                }
            }
        }

        $config = CRM_Core_Config::singleton();
        $stringOverride = array( $config->lcMessages => $overrides );
        $locale_custom_strings = serialize( $stringOverride );
        CRM_Core_Error::debug( '$locale_custom_strings', $locale_custom_strings );
        exit;
        
    }

}


