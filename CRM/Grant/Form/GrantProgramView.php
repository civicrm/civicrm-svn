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

require_once 'CRM/Core/Form.php';  
require_once 'CRM/Grant/BAO/Grant.php';
require_once 'CRM/Grant/BAO/GrantProgram.php';

/**
 * This class generates form components for processing a Grant
 * 
 */
class CRM_Grant_Form_GrantProgramView extends CRM_Core_Form
{

    /**  
     * Function to set variables up before form is built  
     *                                                            
     * @return void  
     * @access public  
     */
    public function preProcess( ) 
    {
        $this->_id        = CRM_Utils_Request::retrieve( 'id', 'Positive', $this );
        $values = array( ); 
        $params['id'] = $this->_id;
        CRM_Grant_BAO_GrantProgram::retrieve( $params, $values );
        $contributionTypes = CRM_Contribute_PseudoConstant::financialType();
        $this->assign('grantType', CRM_Grant_BAO_GrantProgram::getOptionName( $values['grant_type_id'] ) );
        $this->assign('grantProgramStatus', CRM_Grant_BAO_GrantProgram::getOptionName($values['status_id'] ) );
        $this->assign('contributionType', $contributionTypes[$values['financial_type_id']] );
        $this->assign('grantProgramAlgorithm', CRM_Grant_BAO_GrantProgram::getOptionName( $values['allocation_algorithm'] ) );
        $grantTokens = array( 'label','name','total_amount',
                              'remainder_amount','allocation_date', 'is_active', 'is_auto_email' );

        foreach ( $grantTokens as $token ) {
            $this->assign( $token, CRM_Utils_Array::value( $token, $values ) );
        }
        $this->assign( 'id', $this->_id );
    }

    /**
     * Function to build the form
     *
     * @return None
     * @access public
     */
    public function buildQuickForm( ) 
    {
        $this->addButtons(array(  
                                array ( 'type'      => 'cancel',  
                                        'name'      => ts('Done'),  
                                        'spacing'   => '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;',  
                                        'isDefault' => true   )
                                )
                          );
    }

    public function allocate( ) 
    {   
        require_once 'CRM/Grant/BAO/Grant.php';
        $params = array(
                        'status_id' => 1,
                        'grant_program_id' => $_POST['pid'],
                        );

        $grantAlgorithm = CRM_Core_OptionGroup::values( 'allocation_algorithm' );
        $grantAlgorithm = array_flip($grantAlgorithm);
        $grantAlgorithmId = $grantAlgorithm[$_POST['algorithm']];
        $result = CRM_Grant_BAO_Grant::getGrants( $params );

        if ( !empty($result ) ) {
            if ( $grantAlgorithmId == 2 ) {
                foreach ( $result as $key => $row ) {
                    $order[$key] = $row['assessment'];
                    $GrantId[] = $key;
                }
            
                $sort_order = SORT_DESC;
                array_multisort( $order, $sort_order, $result );
                $totalAmount = $_POST['amount'];
            } 
            foreach ( $result as $key => $value ) {
                if ( $totalAmount >= $value['amount_total'] ) {
                    if ( $grantAlgorithmId == 2 ) {
                        $value['amount_granted'] = $value['amount_total'];
                        $totalAmount = $totalAmount - $value['amount_total'];
                    } 
                    $ids['grant']            = $GrantId[$key];
                    $result = CRM_Grant_BAO_Grant::add( &$value, &$ids );
                }
            }
        }
        
        $grantProgramParams['remainder_amount'] = $totalAmount;
        $grantProgramParams['id'] =  $_POST['pid'];
        $ids['grant_program']     =  $_POST['pid'];
        CRM_Grant_BAO_GrantProgram::create( $grantProgramParams, $ids );
        CRM_Core_Session::setStatus( 'Trial allocation done successfully.' );
    }
    
    public function finalize( ) 
    {   $grantedAmount = 0;
        $params = array(
                        'status_id' => 1,
                        'grant_program_id' => $_POST['pid'],
                        );
        $result = CRM_Grant_BAO_Grant::getGrants( $params );
        if ( !empty($result ) ) {
            foreach ($result as $key => $row) {
                $grantedAmount += $row['amount_granted'];
            }
            $totalAmount = $_POST['amount'];
            if( $grantedAmount < $totalAmount ) {
                $data['confirm'] = 'confirm';
                $data['amount_granted'] =  $grantedAmount;
                echo json_encode($data);
                exit(); 
            } else {
                $data['total_amount'] =  $totalAmount;
                $data['amount_granted'] =  $grantedAmount;
                echo json_encode($data);
                exit(); 
            }
        }
    }
    
    public function processFinalization( ) {
        
        $params = array(
                        'status_id' => 1,
                        'grant_program_id' => $_POST['pid'],
                        );
        $result = CRM_Grant_BAO_Grant::getGrants( $params );
        if ( !empty($result ) ) {
            foreach ($result as $key => $row) {
                if ( $row['amount_granted'] > 0 ) {
                    $ids['grant'] = $key;
                    $row['status_id'] = 2;
                    
                    $result = CRM_Grant_BAO_Grant::add( &$row, &$ids );
                } 
            }
            CRM_Core_Session::setStatus( 'Pending grants finalized successfully.' );
        }
    }
    
    public function reject( ) 
    {
        $id = $_POST['pid'];
        $params = array(
                        'status_id' => 1,
                        'grant_program_id' => $_POST['pid'],
                        );

       $result = CRM_Grant_BAO_Grant::getGrants( $params );
  
        if ( !empty($result ) ) {
            foreach ( $result as $key => $value ) {
                $value['status_id'] = 3;
                $value['amount_granted'] = 0.00;
                $ids['grant'] = $key;
                $result = CRM_Grant_BAO_Grant::add( &$value, &$ids );
            } 
            CRM_Core_Session::setStatus( 'Pending grants rejected successfully.' );
        }
    }
}