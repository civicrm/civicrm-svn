<?php

/*
 +--------------------------------------------------------------------+
 | CiviCRM version 4.1                                                |
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

require_once 'CRM/Grant/Form/Task.php';

/**
 * This class provides the functionality to delete a group of
 * participations. This class provides functionality for the actual
 * deletion.
 */
class CRM_Grant_Form_Task_Pay extends CRM_Grant_Form_Task 
{
    /**
     * Are we operating in "single mode", i.e. deleting one
     * specific participation?
     *
     * @var boolean
     */
    protected $_single = false;

    /**
     * build all the data structures needed to build the form
     *
     * @return void
     * @access public
     */
    function preProcess( ) 
    {
        parent::preProcess( );

        //check permission for delete.
        if ( !CRM_Core_Permission::checkActionPermission( 'CiviGrant', CRM_Core_Action::PAY ) ) {
            CRM_Core_Error::fatal( ts( 'You do not have permission to access this page' ) );  
        }
        require_once "CRM/Core/PseudoConstant.php";
        require_once 'CRM/Core/OptionGroup.php';
        $grantStatus = CRM_Core_OptionGroup::values( 'grant_status' );
        $grantStatus =  array_flip($grantStatus);
        
        $paidGrants = $approvedGrants = array();
        CRM_Core_PseudoConstant::populate( &$paidGrants, 'CRM_Grant_DAO_Grant', true, 'status_id', false, " id in (".implode ( ', ' , $this->_grantIds ).") AND status_id = {$grantStatus['Paid']}" );
        CRM_Core_PseudoConstant::populate( &$approvedGrants, 'CRM_Grant_DAO_Grant', true, 'status_id', false, " id in (".implode ( ', ' , $this->_grantIds ).") AND status_id = {$grantStatus['Approved']}" );
        
        $this->_paidGrants = $paidGrants;
        $this->_notApproved = count($this->_grantIds) - count( $this->_paidGrants ) - count( $approvedGrants );

        foreach ( $approvedGrants as $key => $value ) {
            $grantProgram = new CRM_Grant_DAO_Grant( );
            $grantArray =  array( 'id' => $key );
            $grantProgram->copyValues( $grantArray );
            $grantProgram->find( true );
            $currencyDetails[$grantProgram->contact_id][$grantProgram->currency] = $key;
        }
        //$this->_currency = $currencyDetails;
        $curency = 0;
        if ( !empty( $currencyDetails ) ) {
            foreach ( $currencyDetails as $key => $value ) {
                if ( count($value) > 1 ) {
                    foreach ( $value as $unsetKey => $unsetVal ) {
                        unset( $approvedGrants[$unsetVal] );
                        $curency++;
                    }
                }
            }
            $this->_curency = $curency;
        }
        $this->_approvedGrants = $approvedGrants;
    }

    /**
     * Build the form
     *
     * @access public
     * @return void
     */
    function buildQuickForm( ) 
    {
        if ( count($this->_approvedGrants) ) {
            $this->assign( 'paid', count( $this->_paidGrants ) );
            $this->assign( 'approved', count( $this->_approvedGrants ) );
            $this->assign( 'total', count( $this->_grantIds ) );
            $this->assign( 'notApproved', $this->_notApproved );
            $this->assign( 'multipleCurrency', $this->_curency );
            
            $this->addButtons( array(
                                 array ( 'type'      => 'next',
                                         'name'      => ts('Continue >>'),
                                         'spacing'   => '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;',
                                         'isDefault' => true   ),
                                 array ( 'type'      => 'cancel',
                                         'name'      => ts('Cancel') ),
                                 )
                           );
        } else {
             $this->addButtons(array( 
                                    array ( 'type'      => 'cancel', 
                                            'name'      => ts('Cancel') ), 
                                    ) 
                              );
        }
    }

    /**
     * process the form after the input has been submitted and validated
     *
     * @access public
     * @return None
     */
    public function postProcess( ) 
    {
        $this->set( 'approvedGrants', $this->_approvedGrants );
        $this->controller->resetPage( 'GrantPayment' );
    }
}


