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
 * This class stores logic for managing CiviCRM extensions.
 *
 * @package CRM
 * @copyright CiviCRM LLC (c) 2004-2010
 * $Id$
 *
 */

require_once 'CRM/Core/Config.php';

class CRM_Core_Extensions_Payment
{



    public function __construct( $ext ) {
        $this->ext = $ext;
        $this->paymentProcessorTypes = $this->_getAllPaymentProcessorTypes();
    }
    
    public function install( ) {
        if( array_key_exists( $this->ext->key, $this->paymentProcessorTypes ) ) {
            CRM_Core_Error::fatal( 'This payment processor type is already registered.' );
        }

        $dao = new CRM_Core_DAO_PaymentProcessorType( );

        $dao->is_active  = 1;
        $dao->class_name             = $this->ext->key;
        $dao->title                  = $this->ext->name;

        $dao->name                   = $this->ext->name;            
        $dao->user_name_label        = $this->ext->userNameLabel;
        $dao->password_label         = $this->ext->passwordLabel;
        $dao->signature_label        = $this->ext->signatureLabel;
        $dao->subject_label          = $this->ext->subjectLabel;
        $dao->url_site_default       = $this->ext->urlSiteDefault;
        $dao->url_api_default        = $this->ext->urlApiDefault;
        $dao->url_recur_default      = $this->ext->urlRecurDefault;
        $dao->url_site_test_default  = $this->ext->urlSiteTestDefault;
        $dao->url_api_test_default   = $this->ext->urlApiTestDefault;
        $dao->url_recur_test_default = $this->ext->urlRecurTestDefault 
        $dao->billing_mode           = $this->ext->billingMode
        $dao->is_recur               = $this->ext->isRecur;
        $dao->payment_type           = $this->ext->paymentType;
        $dao->description            = $this->ext->description;

        foreach ( $this->_fields as $field ) {
            $dao->{$field['name']} = trim( $values[$field['name']] );
            if ( empty( $dao->{$field['name']} ) ) {
                $dao->{$field['name']} = 'null';
            }
        }
        $dao->save( );
        
    }

    public function uninstall( ) {        
        if( ! array_key_exists( $this->ext->key, $this->paymentProcessorTypes ) ) {
            CRM_Core_Error::fatal( 'This payment processor type is not registered.' );
        }
        
        require_once "CRM/Core/BAO/PaymentProcessorType.php";
        CRM_Core_BAO_PaymentProcessorType::del( $this->paymentProcessorTypes[$this->ext->key] );
    }
    
    public function disable() {
    }
    
    public function enable() {
    }

    private function _getAllPaymentProcessorTypes() {
        $ppt = array();
        require_once "CRM/Core/DAO/PaymentProcessorType.php";
        require_once "CRM/Core/DAO.php";
        $dao = new CRM_Core_DAO_PaymentProcessorType();
        $dao->find( );
        while ($dao->fetch( )) {
            $ppt[$dao->class_name] = $dao->id;
        }
        return $ppt;
    }
    
}