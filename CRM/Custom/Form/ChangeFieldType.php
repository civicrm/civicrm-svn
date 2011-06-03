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

require_once 'CRM/Core/Form.php';
require_once 'CRM/Core/BAO/CustomField.php';

/**
 * This class is to build the form for Deleting Group
 */
class CRM_Custom_Form_ChangeFieldType extends CRM_Core_Form {

    /**
     * the field id
     *
     * @var int
     * @access protected
     */
    protected $_id;
    
    /**
     * array of custom field values
     */
    protected $_values;

    /**
     * mapper array of valid field type 
     */
    protected $_htmlTypeTransitions;

    /**
     * set up variables to build the form
     *
     * @return void
     * @acess protected
     */
    function preProcess( ) {
        $this->_id  = CRM_Utils_Request::retrieve( 'id', 'Positive',
                                                   $this, true );
        
        $this->_values = array( );
        $params = array( 'id' => $this->_id );
        CRM_Core_BAO_CustomField::retrieve( $params, $this->_values );
        
        $this->_htmlTypeTransitions = self::fieldTypeTransitions( CRM_Utils_Array::value('data_type', $this->_values ),
                                                                  CRM_Utils_Array::value('html_type', $this->_values ) );

        if ( empty($this->_values) || empty($this->_htmlTypeTransitions) ) {
            CRM_Core_Error::fatal( ts( "Invalid custom field or can't change input type of this custom field." ) );
        }

        $url = CRM_Utils_System::url( 'civicrm/admin/custom/group/field/update',
                                      "action=update&reset=1&gid={$this->_values['custom_group_id']}&id={$this->_id}" ); 
        $session = CRM_Core_Session::singleton( ); 
        $session->pushUserContext( $url );
        
        CRM_Utils_System::setTitle( ts( 'Change Field Type: %1',
                                        array( 1 => $this->_values['label'] ) ) );
    }

    /**
     * Function to actually build the form
     *
     * @return None
     * @access public
     */
    public function buildQuickForm( ) {
        
        $srcHtmlType = $this->add( 'select',
                                   'src_html_type',
                                   ts( 'Source HTML Type' ),
                                   array( $this->_values['html_type'] => $this->_values['html_type'] ),
                                   true );

        $srcHtmlType->setValue($this->_values['html_type']);
        $srcHtmlType->freeze();
        
        $dstHtmlType = $this->add( 'select',
                                   'dst_html_type',
                                   ts( 'Destination HTML Type' ),
                                   array ('' => ts('- select -') ) + $this->_htmlTypeTransitions,
                                   true );
                    
        $this->addButtons( array(
                                 array ( 'type'      => 'next',
                                         'name'      => ts('Change Field Type'),
                                         'isDefault' => true   ),
                                 array ( 'type'       => 'cancel',
                                         'name'      => ts('Cancel') ),
                                 )
                           );
    }    

    /**
     * Process the form when submitted
     *
     * @return void
     * @access public
     */
    public function postProcess( ) {
        $params = $this->controller->exportValues( $this->_name );

        $tableName = CRM_Core_DAO::getFieldValue( 'CRM_Core_DAO_CustomGroup',
                                                  $this->_values['custom_group_id'],
                                                  'table_name' );

        $srcHtmlType = $this->_values['html_type'];
        $dstHtmlType = $params['dst_html_type'];
        
        $customField = new CRM_Core_DAO_CustomField( );
        $customField->id = $this->_id;
        $customField->find(true);

        if ( $dstHtmlType == 'Text' && in_array($srcHtmlType, array('Select', 'Radio', 'Autocomplete-Select') ) ) {
            $customField->option_group_id = "NULL";
            CRM_Core_BAO_CustomField::checkOptionGroup( $this->_values['option_group_id'] );
        }

        $customField->html_type = $dstHtmlType;
        $customField->save();
        
        CRM_Core_Session::setStatus( ts('Input type of custom field \'%1\' has been successfully changed to \'%2\'.',
                                        array( 1 => $this->_values['label'], 2 => $dstHtmlType ) ) );
    }

    static function fieldTypeTransitions($dataType, $htmlType) {
        // Text field is single value field, 
        // can not be change to other single value option which contains option group
        if ( $htmlType == 'Text' ) {
            return null;
        }

        $singleValueOps = array( 'Text'   => 'Text',
                                 'Select' => 'Select',
                                 'Radio'  => 'Radio',
                                 'Autocomplete-Select' => 'Autocomplete-Select' );

        $mutliValueOps  = array( 'CheckBox'        => 'CheckBox',
                                 'Multi-Select'    => 'Multi-Select',
                                 'AdvMulti-Select' => 'AdvMulti-Select' );
        
        switch( $dataType ) {
        case 'String':
            if ( in_array($htmlType, array_keys($singleValueOps)) ) {
                unset($singleValueOps[$htmlType]);
                return $singleValueOps;
            } else if ( in_array($htmlType, array_keys($mutliValueOps)) ) {
                unset($mutliValueOps[$htmlType]);
                return $mutliValueOps;
            }
            break;
            
        case 'Int':
        case 'Float':
        case 'Int':
        case 'Money':
            if ( in_array($htmlType, array_keys($singleValueOps)) ) {
                unset($singleValueOps[$htmlType]);
                return $singleValueOps;
            }
            break;
            
        case 'Memo':
            $ops = array( 'TextArea'       => 'TextArea',
                          'RichTextEditor' => 'RichTextEditor' );
            if ( in_array($htmlType, array_keys($ops)) ) {
                unset($ops[$htmlType]);
                return $ops;
            }
            break;
        }

        return null;
    }
    
}
