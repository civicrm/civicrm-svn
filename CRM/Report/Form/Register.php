<?php

/*
 +--------------------------------------------------------------------+
 | CiviCRM version 2.2                                                |
 +--------------------------------------------------------------------+
 | Copyright CiviCRM LLC (c) 2004-2009                                |
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
 * @copyright CiviCRM LLC (c) 2004-2009
 * $Id$
 *
 */

require_once 'CRM/Core/Form.php';

class CRM_Report_Form_Register extends CRM_Core_Form {
    public $_id;
    
    public function preProcess()  
    {  

        $this->_action = CRM_Utils_Request::retrieve( 'action',
                                               'String',
                                               $this, false, 'add' );
        $this->_opID = CRM_Core_DAO::getFieldValue( 'CRM_Core_DAO_OptionGroup',
                                             'report_list', 'id', 'name' );

    }
    function setDefaultValues( ) 
    {
        $defaults = array();
        $defaults['weight'] = CRM_Utils_Weight::getDefaultWeight( 'CRM_Core_DAO_OptionValue', 
                                                                  array( 'option_group_id' => $this->_opID) );
        return $defaults;
    }
    public function buildQuickForm( )  
    {
        
        $this->add( 'text', 'label',  ts('Title'), array( 'size'=> 40 ), true );
        $this->add( 'text', 'value',  ts('URL'),   array( 'size'=> 40 ), true );
        $this->add( 'text', 'name',   ts('Class'), array( 'size'=> 40 ), true );
        $element = $this->add( 'text', 'weight', ts('Weight'), array( 'size'=> 4 ), true );
        $element->freeze( );
        $this->add( 'text', 'description',  ts('Description'), array( 'size'=> 40 )  , true );

        $this->add('checkbox', 'is_active', ts('Enabled?'));
        require_once 'CRM/Core/Component.php';
        $this->_components = CRM_Core_Component::getComponents();
        //unset the report component
        unset($this->_components['CiviReport']);

        $components = array();
        foreach( $this->_components as $name => $object ) {
            $components[$object->componentID] = $object->info['translatedName'];
        }

        $this->add( 'select', 'component_id', ts('Component'),   array(''=>ts( 'Contact' )) + $components );     
        
        $this->addButtons(array( 
                                array ( 'type'      => 'upload',
                                        'name'      => ts('Save'), 
                                        'spacing'   => '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;', 
                                        'isDefault' => true   ), 
                                array ( 'type'      => 'cancel', 
                                        'name'      => ts('Cancel') ), 
                                ) 
                          );
        
        $this->addFormRule( array( 'CRM_Report_Form_Register', 'formRule' ), $this );
    }
    static function formRule( &$fields, &$files, $self ) 
    {  
        $errors = array( ); 
        return $errors;
    } 
      
    /** 
     * Function to process the form 
     * 
     * @access public 
     * @return None 
     */ 
    public function postProcess( )  
    {   
        // get the submitted form values. 

        $params = $this->controller->exportValues( $this->_name );
        
        $ids    = array( );
        $groupParams = array( 'name' => ('report_list') );
        require_once 'CRM/Core/OptionValue.php';
        $optionValue = CRM_Core_OptionValue::addOptionValue($params, $groupParams, $this->_action, $this->_id);
        
        CRM_Core_Session::setStatus( ts('The %1 \'%2\' has been saved.', array(1 => 'Report List', 2 => $optionValue->label)) );
    }     
}
?>