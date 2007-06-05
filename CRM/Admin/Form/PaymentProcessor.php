<?php

/*
 +--------------------------------------------------------------------+
 | CiviCRM version 1.7                                                |
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
 * $Id: PaymentProcessor.php 9702 2007-05-29 23:57:16Z lobo $
 *
 */

require_once 'CRM/Admin/Form.php';

/**
 * This class generates form components for Location Type
 * 
 */
class CRM_Admin_Form_PaymentProcessor extends CRM_Admin_Form
{
    protected $_id     = null;

    protected $_testID = null;

    protected $_fields = null;

    protected $_ppDAO;

    function preProcess( ) {
        parent::preProcess( );

        // get the payment processor meta information
        $this->_ppType = CRM_Utils_Request::retrieve( 'pp', 'String', $this, false, 'PayPal_Standard' );
        require_once 'CRM/Core/DAO/PaymentProcessorType.php';
        $this->_ppDAO =& new CRM_Core_DAO_PaymentProcessorType( );
        $this->_ppDAO->name = $this->_ppType;
        if ( ! $this->_ppDAO->find( true ) ) {
            CRM_Core_Error::fatal( ts( 'Could not find payment processor meta information' ) );
        }

        if ( $this->_id ) {
            $refreshURL = CRM_Utils_System::url( 'civicrm/admin/paymentProcessor',
                                                 "reset=1&action=update&id={$this->_id}" );
        } else {
            $refreshURL = CRM_Utils_System::url( 'civicrm/admin/paymentProcessor',
                                                 "reset=1&action=add",
                                                 true, null, false );
        }
        $this->assign( 'refreshURL', $refreshURL );

        $this->_fields = array(
                               array( 'name'  => 'user_name',
                                      'label' => $this->_ppDAO->user_name_label ),
                               array( 'name'  => 'password',
                                      'label' => $this->_ppDAO->password_label ),
                               array( 'name'  => 'signature',
                                      'label' => $this->_ppDAO->signature_label ),
                               array( 'name'  => 'subject',
                                      'label' => $this->_ppDAO->subject_label ),
                               array( 'name'  => 'url_site',
                                      'label' => ts( 'Site URL' ),
                                      'rule'  => 'url',
                                      'msg'   => ts( 'Enter a valid URL' ) ),
                               );

        if ( ! empty( $this->_ppDAO->url_button_default ) ) {
            $this->_fields[] = array( 'name'  => 'url_button',
                                      'label' => ts( 'Button URL' ),
                                      'rule'  => 'url',
                                      'msg'   => ts( 'Enter a valid URL' ) );
        }
    }

    /**
     * Function to build the form
     *
     * @return None
     * @access public
     */
    public function buildQuickForm( $check = false ) 
    {
        $attributes = CRM_Core_DAO::getAttribute( 'CRM_Core_DAO_PaymentProcessor' );

        $this->add( 'text', 'name', ts( 'Name' ),
                    $attributes['name'], true );
        $this->add( 'text', 'description', ts( 'Description' ),
                    $attributes['description'] );

        $types = array('select' => '- select -') + CRM_Core_PseudoConstant::paymentProcessorType( );
        $this->add( 'select', 'payment_processor_type', ts( 'Payment Processor Type' ), $types, true,
                    array('onchange' => "reload(true)") );
                   
        
        // is this processor active ?
        $this->add('checkbox', 'is_active' , ts('Is this Payment Processor active?') );
        $this->add('checkbox', 'is_default', ts('Is this Payment Processor the default?') );


        foreach ( $this->_fields as $field ) {
            if ( empty( $field['label'] ) ) {
                continue;
            }

            $this->add( 'text', $field['name'],
                        $field['label'], $attributes['name'] );
            $this->add( 'text', "test_{$field['name']}",
                        $field['label'], $attributes['name'] );
            if ( CRM_Utils_Array::value( 'rule', $field ) ) {
                $this->addRule( $field['name']         , $field['msg'], $field['rule'] );
                $this->addRule( "test_{$field['name']}", $field['msg'], $field['rule'] );
            }
        }

        $this->addFormRule( array( 'CRM_Admin_Form_PaymentProcessor', 'formRule' ) );

        parent::buildQuickForm( );
    }

    static function formRule( &$fields ) {
        // make sure that at least one of live or test is present
        // and we have at least name and url_site 
        // would be good to make this processor specific
        $errors = array( );

        if ( ! ( self::checkSection( $fields, $errors ) ||
                 self::checkSection( $fields, $errors, 'test' ) ) ) {
            $errors['_qf_default'] = ts( 'You must have at least the test or live section filled' );
        }

        if ( ! empty( $errors ) ) {
            return $errors;
        }

        return empty( $errors ) ? true : $errors;
    }

    static function checkSection( &$fields, &$errors, $section = null ) {
        $names = array( 'user_name', 'url_site' );
        
        $present    = false;
        $allPresent = true;
        foreach ( $names as $name ) {
            if ( $section ) {
                $name = "{$section}_$name";
            }
            if ( ! empty( $fields[$name] ) ) {
                $present = true;
            } else {
                $allPresent = false;
            }
        }

        if ( $present ) {
            if ( ! $allPresent ) {
                $errors['_qf_default'] = ts( 'You must have at least the user_name and url specified' );
            }
        }
        return $present;
    }

    function setDefaultValues( ) {
        $defaults = array( );

        $defaults['payment_processor_type'] = $this->_ppType;

        if ( ! $this->_id ) {
            $defaults['is_active'] = $defaults['is_default'] = 1;
            $defaults['url_site'] = $this->_ppDAO->url_site_default;
            $defaults['url_button'] = $this->_ppDAO->url_button_default;
            $defaults['test_url_site'] = $this->_ppDAO->url_site_test_default;
            $defaults['test_url_button'] = $this->_ppDAO->url_button_test_default;
            return $defaults;
        }

        $domainID = CRM_Core_Config::domainID( );

        $dao =& new CRM_Core_DAO_PaymentProcessor( );
        $dao->id        = $this->_id;
        $dao->domain_id = $domainID;

        if ( ! $dao->find( true ) ) {
            return $defaults;
        }

        CRM_Core_DAO::storeValues( $dao, $defaults );
        
        // now get testID
        $testDAO =& new CRM_Core_DAO_PaymentProcessor( );
        $testDAO->name      = $dao->name;
        $testDAO->is_test   = 1;
        $testDAO->domain_id = $domainID;
        if ( $testDAO->find( true ) ) {
            $this->_testID = $testDAO->id;

            foreach ( $this->_fields as $field ) {
                $testName = "test_{$field['name']}";
                $defaults[$testName] = $testDAO->{$field['name']};
            }
        }    
        return $defaults;
    }

    /**
     * Function to process the form
     *
     * @access public
     * @return None
     */
    public function postProcess() 
    {
        $values = $this->controller->exportValues( $this->_name );

        $domainID = CRM_Core_Config::domainID( );

        if ( CRM_Utils_Array::value( 'is_default', $values ) ) {
            $query = "
UPDATE civicrm_payment_processor
   SET is_default = 0
 WHERE domain_id = $domainID;
";
            CRM_Core_DAO::executeQuery( $query, CRM_Core_DAO::$_nullArray );
        }

        $this->updatePaymentProcessor( $values, $domainID, false );
        $this->updatePaymentProcessor( $values, $domainID, true );

    }//end of function

    function updatePaymentProcessor( &$values, $domainID, $test ) {
        $dao =& new CRM_Core_DAO_PaymentProcessor( );

        $dao->id         = $test ? $this->_testID : $this->_id;
        $dao->domain_id  = $domainID;
        $dao->is_test    = $test;
        if ( ! $test ) {
            $dao->is_default = CRM_Utils_Array::value( 'is_default', $values, 0 );
        } else {
            $dao->is_default = 0;
        }
        $dao->is_active  = CRM_Utils_Array::value( 'is_active' , $values, 0 );

        $dao->name                   = $values['name'];
        $dao->description            = $values['description'];
        $dao->payment_processor_type = $values['payment_processor_type'];
        
        foreach ( $this->_fields as $field ) {
            $fieldName = $test ? "test_{$field['name']}" : $field['name'];
            $dao->{$field['name']} = trim( $values[$fieldName] );
            if ( empty( $dao->{$field['name']} ) ) {
                $dao->{$field['name']} = 'null';
            }
        }

        // also copy meta fields from the info DAO
        $dao->is_recur     = $this->_ppDAO->is_recur;
        $dao->billing_mode = $this->_ppDAO->billing_mode;
        $dao->class_name   = $this->_ppDAO->class_name;

        $dao->save( );
    }

}

?>
