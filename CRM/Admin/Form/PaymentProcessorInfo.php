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
 * $Id: PaymentProcessorInfo.php 9702 2007-05-29 23:57:16Z lobo $
 *
 */

require_once 'CRM/Admin/Form.php';

/**
 * This class generates form components for Location Type
 * 
 */
class CRM_Admin_Form_PaymentProcessorInfo extends CRM_Admin_Form
{
    protected $_id     = null;

    protected $_fields = null;

    function preProcess( ) {
        parent::preProcess( );

        $this->_fields = array(
                               array( 'name'     => 'name',
                                      'label'    => ts( 'Name' ),
                                      'required' => true ),
                               array( 'name'     => 'title',
                                      'label'    => ts( 'Title' ),
                                      'required' => true ),
                               array( 'name'     => 'billing_mode',
                                      'label'    => ts( 'Billing Mode' ),
                                      'required' => true,
                                      'rule'     => 'positiveInteger',
                                      'msg'      => ts( 'Enter a positive integer' ) ),
                               array( 'name'     => 'description',
                                      'label'    => ts( 'Description' ) ),
                               array( 'name'     => 'user_name_label',
                                      'label'    => ts( 'User Name Label' ) ),
                               array( 'name'     => 'password_label',
                                      'label'    => ts( 'Password Label' ) ),
                               array( 'name'     => 'signature_label',
                                      'label'    => ts( 'Signature Label' ) ),
                               array( 'name'     => 'subject_label',
                                      'label'    => ts( 'Subject Label' ) ),
                               array( 'name'     => 'class_name',
                                      'label'    => ts( 'PHP class name' ),
                                      'required' => true ),
                               array( 'name'     => 'url_site_default',
                                      'label'    => ts( 'Live Site URL' ),
                                      'required' => true,
                                      'rule'     => 'url',
                                      'msg'      => ts( 'Enter a valid URL' ) ),
                               array( 'name'     => 'url_button_default',
                                      'label'    => ts( 'Live Button URL' ),
                                      'rule'     => 'url',
                                      'msg'      => ts( 'Enter a valid URL' ) ),
                               array( 'name'     => 'url_site_test_default',
                                      'label'    => ts( 'Test Site URL' ),
                                      'required' => true,
                                      'rule'     => 'url',
                                      'msg'      => ts( 'Enter a valid URL' ) ),
                               array( 'name'     => 'url_button_test_default',
                                      'label'    => ts( 'Test Button URL' ),
                                      'rule'     => 'url',
                                      'msg'      => ts( 'Enter a valid URL' ) ),
                               );
    }

    /**
     * Function to build the form
     *
     * @return None
     * @access public
     */
    public function buildQuickForm( $check = false ) 
    {
        $attributes = CRM_Core_DAO::getAttribute( 'CRM_Core_DAO_PaymentProcessorInfo' );

        foreach ( $this->_fields as $field ) {
            $required = CRM_Utils_Array::value( 'required', $field, false );
            $this->add( 'text', $field['name'],
                        $field['label'], $attributes['name'], $required );
            if ( CRM_Utils_Array::value( 'rule', $field ) ) {
                $this->addRule( $field['name']         , $field['msg'], $field['rule'] );
            }
        }

        // is this processor active ?
        $this->add('checkbox', 'is_active' , ts('Is this Payment Processor Info active?') );
        $this->add('checkbox', 'is_default', ts('Is this Payment Processor Info the default?') );
        $this->add('checkbox', 'is_recur'  , ts('Does this Payment Processor Info support recurring donations?') );

        parent::buildQuickForm( );
    }

    function setDefaultValues( ) {
        $defaults = array( );

        if ( ! $this->_id ) {
            $defaults['is_active'] = $defaults['is_default'] = 1;
            $defaults['user_name_label'] = ts( 'User Name' );
            $defaults['password_label']  = ts( 'Password' );
            $defaults['signature_label'] = ts( 'Signature' );
            $defaults['subject_label']   = ts( 'Subject' );
            return $defaults;
        }

        $domainID = CRM_Core_Config::domainID( );

        $dao =& new CRM_Core_DAO_PaymentProcessorInfo( );
        $dao->id        = $this->_id;
        $dao->domain_id = $domainID;

        if ( ! $dao->find( true ) ) {
            return $defaults;
        }

        CRM_Core_DAO::storeValues( $dao, $defaults );
        
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

        $dao =& new CRM_Core_DAO_PaymentProcessorInfo( );

        $dao->id         = $this->_id;
        $dao->domain_id  = $domainID;
        $dao->is_default = CRM_Utils_Array::value( 'is_default', $values, 0 );
        $dao->is_active  = CRM_Utils_Array::value( 'is_active' , $values, 0 );
        $dao->is_recur   = CRM_Utils_Array::value( 'is_recur'  , $values, 0 );

        $dao->name         = $values['name'];
        $dao->description  = $values['description'];
        
        foreach ( $this->_fields as $field ) {
            $dao->{$field['name']} = trim( $values[$field['name']] );
            if ( empty( $dao->{$field['name']} ) ) {
                $dao->{$field['name']} = 'null';
            }
        }
        $dao->save( );
    }

}

?>
