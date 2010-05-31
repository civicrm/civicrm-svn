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

class CRM_Admin_Form_WordReplacements extends CRM_Core_Form
{
    protected $_numStrings = 10;
    
    protected $_stringName = null;

    protected $_defaults = null;
    
    function preProcess( ) {
        $this->_soInstance = CRM_Utils_Array::value( 'instance', $_GET );
        $this->assign( 'soInstance', $this->_soInstance );
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
        $soInstances = range( 1, $this->_numStrings, 1 );
        $stringOverrideInstances = array( );
        if ( $this->_soInstance ) {
            $soInstances = array( $this->_soInstance );
        } else if ( CRM_Utils_Array::value( 'old', $_POST ) ) {
            $soInstances = $stringOverrideInstances = array_keys( $_POST['old'] );
        } else if ( !empty( $this->_defaults ) && is_array( $this->_defaults ) )  {
            $stringOverrideInstances = array_keys( $this->_defaults['new'] );
            if ( count( $this->_defaults['old'] ) > count( $this->_defaults['new'] ) ) {
                $stringOverrideInstances = array_keys( $this->_defaults['old'] );
            }
        }
        foreach ( $soInstances as $instance ) {
            $this->addElement( 'checkbox', "enabled[$instance]" );
            $this->add( 'textarea', "old[$instance]", null, array( 'rows=1 cols=40' ) );
            $this->add( 'textarea', "new[$instance]", null, array( 'rows=1 cols=40' ) );
            $this->addElement( 'checkbox', "cb[$instance]" );
        }
        if ( $this->_soInstance ) return; 
        
        $this->assign( 'stringOverrideInstances', empty($stringOverrideInstances)?false:$stringOverrideInstances );
        
        $this->addButtons( array(
                                 array ( 'type'      => 'next',
                                         'name'      => ts('Save'),
                                         'isDefault' => true   ),
                                 array ( 'type'      => 'cancel',
                                         'name'      => ts('Cancel') ),
                                 )
                           );
        $this->addFormRule( array( 'CRM_Admin_Form_WordReplacements', 'formRule' ), $this );
    }
    
    /**
     * global validation rules for the form
     *
     * @param array $values posted values of the form
     *
     * @return array list of errors to be posted back to the form
     * @static
     * @access public
     */
    static function formRule( $values ) 
    {
        $errors = array( );
        
        $oldValues  = CRM_Utils_Array::value( 'old', $values );
        $newValues  = CRM_Utils_Array::value( 'new', $values );
        $enabled    = CRM_Utils_Array::value( 'enabled', $values );
        $exactMatch = CRM_Utils_Array::value( 'cb', $values );
        
        foreach ( $oldValues as $k => $v ) { 
            if ( $v && !$newValues[$k] ) {
                $errors['new['.$k.']'] = ts( 'Please Enter the value for Replacement Word' ); 
            } elseif ( !$v && $newValues[$k] ) {
                $errors['old['.$k.']'] = ts( 'Please Enter the value for Original Word' );       
            } elseif ( ( !$newValues[$k] && !$oldValues[$k] ) && ( $enabled[$k] || $exactMatch[$k] ) ) {
                $errors['old['.$k.']'] = ts( 'Please Enter the value for Original Word' );       
                $errors['new['.$k.']'] = ts( 'Please Enter the value for Replacement Word' );       
            } 
        }
        
        return $errors;
    }
    
    /**
     * Function to process the form
     *
     * @access public
     * @return None
     */
    public function postProcess() 
    {
        $params = $this->controller->exportValues( $this->_name );
        
        $enabled['exactMatch'] = $enabled['wildcardMatch']  = $disabled['exactMatch']  = $disabled['wildcardMatch'] =  array();
        for ( $i = 1 ; $i <= $this->_numStrings; $i++ ) {
            if ( CRM_Utils_Array::value( $i, $params['new'] ) && 
                 CRM_Utils_Array::value( $i, $params['old'] ) ) {
                if ( CRM_Utils_Array::value( $i, $params['enabled'] ) )  { 
                    if ( array_key_exists( $i, $params['cb']) ) {
                        $enabled['exactMatch'] += array($params['old'][$i]=>$params['new'][$i]);
                    } else {
                        $enabled['wildcardMatch'] += array($params['old'][$i]=>$params['new'][$i]);
                    }
                } else {
                    if ( array_key_exists( $i, $params['cb']) ) {
                        $disabled['exactMatch'] += array($params['old'][$i]=>$params['new'][$i]);
                    } else {
                        $disabled['wildcardMatch'] += array($params['old'][$i]=>$params['new'][$i]);
                    }  
                }
            }
        }

        $overrides = array ( 'enabled' => $enabled , 'disabled' => $disabled );
        CRM_Core_Error::debug( '$overrides', $overrides );
        exit;
 
        $config = CRM_Core_Config::singleton();
        $stringOverride = array( $config->lcMessages => $overrides );
        $locale_custom_strings = serialize( $stringOverride );
        CRM_Core_Error::debug( '$locale_custom_strings', $locale_custom_strings );
        exit;
        
    }

}


