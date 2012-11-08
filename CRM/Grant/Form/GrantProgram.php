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
require_once 'CRM/Grant/BAO/GrantProgram.php';
/**
 * This class generates form components for Par Service Fees
 * 
 */
class CRM_Grant_Form_GrantProgram extends CRM_Core_Form
{
    protected $_id     = null;

    protected $_fields = null;


    function preProcess( ) {
        $this->_action     = CRM_Utils_Request::retrieve('action', 'String', $this );
        $this->_id = CRM_Utils_Request::retrieve('id', 'Positive', $this );
        
        $title = null;
        if ( $this->_action & CRM_Core_Action::UPDATE ) $title = ts('Edit Grant Program');
        if ( $this->_action & CRM_Core_Action::DELETE ) $title = ts('Delete Grant Program');
        if ( $title ) CRM_Utils_System::setTitle( $title );
        
        $session = CRM_Core_Session::singleton();
        $session->pushUserContext( CRM_Utils_System::url('civicrm/grant_program', 'reset=1') );
        $this->assign( 'action', $this->_action );

        $this->_values = $this->get( 'values' );
        if ( !is_array( $this->_values ) ) {
            $this->_values = array( );
            // if we are editing
            if ( isset( $this->_id ) && $this->_id ) {
                $params = array( 'id' => $this->_id );
                CRM_Grant_BAO_GrantProgram::retrieve( $params, $this->_values );
            }
            //lets use current object session.
            $this->set( 'values', $this->_values );
        }
    }
    
    function setDefaultValues( ) 
    {
        $defaults = $this->_values;
        if ( !empty( $defaults ) ) {
            $defaults['grant_type_id'] = CRM_Grant_BAO_GrantProgram::getOptionValue( $defaults['grant_type_id'] );
            $defaults['status_id']     = CRM_Grant_BAO_GrantProgram::getOptionValue( $defaults['status_id'] );
            if ( !empty( $defaults['allocation_date'] ) ) {
                $defaults['allocation_date']  = strftime("%m/%d/%Y", strtotime( $defaults['allocation_date'] ));
            }
            if ( !empty( $defaults['applications_start_date'] ) ) {
                $defaults['applications_start_date']  = strftime("%m/%d/%Y", strtotime( $defaults['applications_start_date'] ));
            }
            if ( !empty( $defaults['applications_end_date'] ) ) {
                $defaults['applications_end_date']  = strftime("%m/%d/%Y", strtotime( $defaults['applications_end_date'] ));
            }
            $defaults['allocation_algorithm'] = CRM_Grant_BAO_GrantProgram::getOptionValue( $defaults['allocation_algorithm'] );
        }
        if ( !$this->_id ) {
            $defaults['is_active'] = 1;
            $defaults['is_auto_email'] = 1;
            return $defaults;
        }
        return $defaults;
    }
    /**
     * Function to build the form
     *
     * @return None
     * @access public
     */
    public function buildQuickForm( $check = false ) 
    {
        parent::buildQuickForm( );

        
        if ( $this->_action & CRM_Core_Action::DELETE ) {
            
            $this->addButtons( array(
                                     array ( 'type'      => 'next',
                                             'name'      => ts('Delete'),
                                             'isDefault' => true   ),

                                     array ( 'type'      => 'cancel',
                                             'name'      => ts('Cancel') ),
                                     )
                               );
            return;
        }

        $this->applyFilter('__ALL__','trim');
        $attributes = CRM_Core_DAO::getAttribute( 'CRM_Grant_DAO_GrantProgram' );
        
        $this->add( 'text', 'label', ts( 'Label' ),
                    $attributes['label'], true );
        
        $grantType = CRM_Core_OptionGroup::values( 'grant_type' );
        $this->add('select', 'grant_type_id',  ts( 'Grant Type' ),
                   array( '' => ts( '- select -' ) ) + $grantType , true);

        $this->add( 'text', 'total_amount', ts( 'Total Amount' ),
                    $attributes['total_amount'], false );         
        $this->addRule('total_amount', ts('Please enter a valid amount.'), 'money'); 
        
        $this->add( 'text', 'remainder_amount', ts( 'Remainder Amount' ),
                    $attributes['remainder_amount'], false );
        $this->addRule('remainder_amount', ts('Please enter a valid amount.'), 'money'); 

        $contributionTypes = CRM_Grant_BAO_GrantProgram::contributionTypes();
        $this->add('select', 'financial_type_id',  ts( 'Financial Types' ),
                   array( '' => ts( '- select -' ) ) + $contributionTypes , true);
        
        $grantStatus = CRM_Core_OptionGroup::values( 'grant_program_status' );
        $this->add('select', 'status_id',  ts( 'Grant Status' ),
                   array( '' => ts( '- select -' ) ) + $grantStatus , true);

        $grantAlgorithm = CRM_Core_OptionGroup::values( 'allocation_algorithm' );
        $this->add('select', 'allocation_algorithm',  ts( 'Allocation Algorithm' ),
                   array( '' => ts( '- select -' ) ) + $grantAlgorithm , true); 

        $this->addDate( 'applications_start_date', ts('Application Start Date'), false, array( 'formatType' => 'custom') );
        $this->addDate( 'applications_end_date', ts('Application End Date'), false, array( 'formatType' => 'custom') );
        $this->addDate( 'allocation_date', ts('Allocation Date'), false, array( 'formatType' => 'custom') );

        $this->add( 'checkbox', 'is_active', ts('Enabled?') );

        $this->add( 'checkbox', 'is_auto_email', ts('Auto email?') );

        $this->addButtons(array( 
                                array ( 'type'      => 'upload',
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
        CRM_Utils_System::flushCache( 'CRM_Grant_DAO_GrantProgram' );

        if ( $this->_action & CRM_Core_Action::DELETE ) {
            CRM_Grant_BAO_GrantProgram::del( $this->_id );
            CRM_Core_Session::setStatus( ts('Selected Grant Program has been deleted successfully.') );
            return;
        }

        $values   = $this->controller->exportValues( $this->_name );
        $domainID = CRM_Core_Config::domainID( );

        $result = $this->updateGrantProgram( $values, $domainID );
        if ( $result ) {
            CRM_Core_Session::setStatus( ts( 'Grant Program  %1 has been saved.', array( 1 => $result->label ) ) );
            $session = CRM_Core_Session::singleton();
            $session->pushUserContext(CRM_Utils_System::url('civicrm/grant_program', 'reset=1&action=browse&id=' . $result->id ));
        }
        
    }//end of function

    function updateGrantProgram( &$values, $domainID ) {
        require_once 'CRM/Core/DAO.php';
        $dao = new CRM_Grant_DAO_GrantProgram( );
        if ( empty( $values['is_active']) )
            $values['is_active'] = 0;
        if ( empty( $values['is_auto_email']) )
            $values['is_auto_email']  = 0;
        $dao->id                      = $this->_id;
        $dao->domain_id               = $domainID;
        $dao->label                   = $values['label'];
        $dao->name                    = $values['label'];
        $dao->grant_type_id           = CRM_Grant_BAO_GrantProgram::getOptionValueID( CRM_Core_DAO::getFieldValue('CRM_Core_DAO_OptionGroup','grant_type','id','name'), $values['grant_type_id']);
        $dao->total_amount            = $values['total_amount'];
        $dao->remainder_amount        = $values['remainder_amount'];
        $dao->financial_type_id       = $values['financial_type_id'];
        $dao->status_id               = CRM_Grant_BAO_GrantProgram::getOptionValueID( CRM_Core_DAO::getFieldValue('CRM_Core_DAO_OptionGroup','grant_program_status','id','name'), $values['status_id']);
        $dao->applications_start_date = CRM_Utils_Date::processDate( $values['applications_start_date'] );
        $dao->applications_end_date   = CRM_Utils_Date::processDate( $values['applications_end_date'] );
        $dao->allocation_date         = CRM_Utils_Date::processDate( $values['allocation_date'] );
        $dao->is_active               = $values['is_active'];
        $dao->is_auto_email           = $values['is_auto_email'];
        $dao->allocation_algorithm    = CRM_Grant_BAO_GrantProgram::getOptionValueID( CRM_Core_DAO::getFieldValue('CRM_Core_DAO_OptionGroup','allocation_algorithm','id','name'), $values['allocation_algorithm']);
        return $dao->save( );
    }
}


