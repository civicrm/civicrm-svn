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
require_once 'CRM/Core/ShowHideBlocks.php';
require_once 'CRM/Custom/Form/CustomData.php';

/**
 * This class generates form components for custom data
 * 
 * It delegates the work to lower level subclasses and integrates the changes
 * back in. It also uses a lot of functionality with the CRM API's, so any change
 * made here could potentially affect the API etc. Be careful, be aware, use unit tests.
 *
 */
class CRM_Case_Form_CustomData extends CRM_Core_Form
{
    /**
     * The entity id, used when editing/creating custom data
     *
     * @var int
     */
    protected $_entityID;
    
    /**
     * The custom data type
     *
     * @var int
     */
    protected $_cdType;

    /**
     * entity sub type of the table id
     *
     * @var string
     * @access protected
     */
    protected $_subTypeID;

    /**
     * pre processing work done here.
     *
     * gets session variables for table name, id of entity in table, type of entity and stores them.
     *
     * @param
     * @return void
     *
     * @access public
     *
     */
    function preProcess()
    {
        $this->_cdType = CRM_Utils_Array::value( 'type', $_GET );
        
        $this->assign('cdType', false);
        if ( $this->_cdType ) {
            $this->assign('cdType', true);
            return CRM_Custom_Form_CustomData::preProcess( $this );
        }

		$this->_groupID   = CRM_Utils_Request::retrieve( 'groupID',  'Positive', $this, true );
		$this->_entityID  = CRM_Utils_Request::retrieve( 'entityID', 'Positive', $this, true );
		$this->_subTypeID = CRM_Utils_Request::retrieve( 'subType',  'Positive', $this, true );
		$this->_contactID = CRM_Utils_Request::retrieve( 'cid',      'Positive', $this, true );
	
        // when custom data is included in this page
        if ( CRM_Utils_Array::value( 'hidden_custom', $_POST ) ) {
            $session = CRM_Core_Session::singleton( );
            $session->pushUserContext( CRM_Utils_System::url( 'civicrm/contact/view/case', "reset=1&id={$this->_entityID}&cid={$this->_contactID}&action=view" ) );
        }
    }
    
    /**
     * Function to actually build the form
     *
     * @return void
     * @access public
     */
    public function buildQuickForm()
    {
        if ( $this->_cdType ) {
            return CRM_Custom_Form_CustomData::buildQuickForm( $this );
        }

        //need to assign custom data type and subtype to the template
        $this->assign('entityID',   $this->_entityID );
		$this->assign('groupID',    $this->_groupID );
		$this->assign('subType',    $this->_subTypeID );
		$this->assign('contactID',  $this->_contactID );

        // make this form an upload since we dont know if the custom data injected dynamically
        // is of type file etc
        $this->addButtons(array(
                                array ( 'type'      => 'upload',
                                        'name'      => ts('Save'),
                                        'isDefault' => true   ),
                                array ( 'type'       => 'cancel',
                                        'name'      => ts('Cancel') ),
                                )
                          );        
    }
    
    /**
     * Set the default form values
     *
     * @access protected
     * @return array the default array reference
     */
    function &setDefaultValues()
    { 
        if ( $this->_cdType ) {
            $customDefaultValue = CRM_Custom_Form_CustomData::setDefaultValues( $this );
            return $customDefaultValue;
        }

        if ( !CRM_Utils_Array::value( 'hidden_custom_group_count', $_POST ) ) { 
            $customValueCount = 1;
        } else {
            $customValueCount = $_POST['hidden_custom_group_count'][$this->_groupID];
        }
        $this->assign('customValueCount', $customValueCount );
	    
        $defaults = array();
        return $defaults;
    }
    
    /**
     * Process the user submitted custom data values.
     *
     * @access public
     * @return void
     */
    public function postProcess() 
    {
        $fields = array();
        require_once 'CRM/Core/BAO/CustomValueTable.php';
        CRM_Core_BAO_CustomValueTable::postProcess( $_POST,
                                                    $fields,
                                                    'civicrm_case',
                                                    $this->_entityID,
                                                    'Case' );
    }
}
