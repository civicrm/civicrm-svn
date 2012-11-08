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

require_once 'CRM/Contribute/Form.php';
require_once 'CRM/Core/PseudoConstant.php';
require_once 'CRM/Contribute/PseudoConstant.php';
/**
 * This class generates form components for Contribution Type
 * 
 */
class CRM_Financial_Form_FinancialTypeAccount extends CRM_Contribute_Form
{
    
    
    /**
     * the financial type id saved to the session for an update
     *
     * @var int
     * @access protected
     */
    protected $_aid;

    /**
     * The financial type accounts id, used when editing the field
     *
     * @var int
     * @access protected
     */
    protected $_id;
   
    /**
     * The name of the BAO object for this form
     *
     * @var string
     */
    protected $_BAOName;

    /**
     * Function to set variables up before form is built
     *
     * @return void
     * @access public
     */

    public function preProcess(){
        require_once 'CRM/Core/DAO.php';
        $this->_aid = CRM_Utils_Request::retrieve( 'aid', 'Positive', $this );
        $this->_id  = CRM_Utils_Request::retrieve( 'id' , 'Positive', $this ); 
        $this->_BAOName = $this->get( 'BAOName' );
        if ( $this->_aid && ( $this->_action & CRM_Core_Action::ADD  ) ) {
            $this->_title = CRM_Core_DAO::getFieldValue( 'CRM_Financial_DAO_FinancialType', $this->_aid, 'name' );
            CRM_Utils_System::setTitle( $this->_title .' - '.ts( 'Financial Accounts' ) );
            
            $url = CRM_Utils_System::url( 'civicrm/admin/financial/financialType/accounts', 
                                          "reset=1&action=browse&aid={$this->_aid}" ); 
            
            $session = CRM_Core_Session::singleton( ); 
            $session->pushUserContext( $url );
        } 
        if ( $this->_id ){
            $financialAccount = CRM_Core_DAO::getFieldValue( 'CRM_Financial_DAO_EntityFinancialAccount', $this->_id, 'financial_account_id' );
            $fieldTitle = CRM_Core_DAO::getFieldValue( 'CRM_Financial_DAO_FinancialAccount', $financialAccount, 'name' );
            CRM_Utils_System::setTitle( $fieldTitle .' - '.ts( 'Financial Type Accounts' ) );
        }
    }




    /**
     * Function to build the form
     *
     * @return None
     * @access public
     */
    public function buildQuickForm( ) 
    {
       
        parent::buildQuickForm( );

        if ( isset( $this->_id ) ) {
            require_once 'CRM/Financial/BAO/FinancialTypeAccount.php';
            $params = array( 'id' => $this->_id );
            CRM_Financial_BAO_FinancialTypeAccount::retrieve( $params, $defaults );
            $this->setDefaults( $defaults );
            $financialAccountTitle = CRM_Core_DAO::getFieldValue( 'CRM_Financial_DAO_FinancialAccount', $defaults['financial_account_id'], 'name' );
        }
        if ($this->_action & CRM_Core_Action::DELETE ) { 
            return;
        }
        $this->applyFilter('__ALL__', 'trim');
        
        if ( $this->_action == CRM_Core_Action::UPDATE ){
            $this->assign( 'aid', $this->_id );
            //hidden field to catch the group id in profile
            $this->add('hidden', 'financial_type_id', $this->_aid);
        
            //hidden field to catch the field id in profile
            $this->add('hidden', 'account_type_id', $this->_id);
        }
        $AccountTypeRelationship = CRM_Core_PseudoConstant::accountOptionValues( 'account_relationship' );
        if ( !empty( $AccountTypeRelationship ) ) {
            $this->add('select', 
                       'account_relationship', 
                       ts('Financial Type Relationship'), 
                       array('select' => '-Select Financial Account Relationship-') + $AccountTypeRelationship, 
                       true );
        }
        
        $this->add('select', 
                   'financial_account_id', 
                   ts( 'Financial Account' ), 
                   array(''=>ts( '- Select Financial Account-' )) + CRM_Contribute_PseudoConstant::contributionType( ),
                   true );

        $this->addButtons( array(
                                 array ( 'type'      => 'next',
                                         'name'      => ts('Save'),
                                         'isDefault' => true ),
                                 array ( 'type'      => 'next',
                                         'name'      => ts('Save and New'),
                                         'subName'   => 'new'),
                                 array ( 'type'      => 'cancel',
                                         'name'      => ts('Cancel') ),
                                 )
                           );
        
        //$this->addFormRule( array( 'CRM_Financial_Form_FinancialType', 'formRule'), $this );
    }


    
    /**
     * Function to process the form
     *
     * @access public
     * @return None
     */
    public function postProcess() 
    {
        require_once 'CRM/Financial/BAO/FinancialTypeAccount.php';
        if($this->_action & CRM_Core_Action::DELETE) {
            CRM_Financial_BAO_FinancialTypeAccount::del($this->_id);
            CRM_Core_Session::setStatus( ts('Selected financial type account has been deleted.') );
        } else { 

            $params = $ids = array( );
            // store the submitted values in an array
            $params = $this->exportValues();
            
            if ($this->_action & CRM_Core_Action::UPDATE) {
                $ids['entityFinancialAccount'] = $this->_id;
            }
            
            if ($this->_action & CRM_Core_Action::ADD) {
                $params['entity_table'] = 'civicrm_financial_type';
                $params['entity_id'] = $this->_aid;
            }
            $financialTypeAccount = CRM_Financial_BAO_FinancialTypeAccount::add($params, $ids);
            CRM_Core_Session::setStatus( ts('The financial type Account has been saved.' ));

            
            $buttonName = $this->controller->getButtonName( );
           
        
            $session = CRM_Core_Session::singleton( );
            if ( $buttonName == $this->getButtonName( 'next', 'new' ) ) {
                CRM_Core_Session::setStatus( ts(' You can add another Financial Account Type.') );
                $session->replaceUserContext( CRM_Utils_System::url( 'civicrm/admin/financial/financialType/accounts/add', 
                                                                     "reset=1&action=add&aid={$this->_aid}" ) );
            } else {
                $session->replaceUserContext( CRM_Utils_System::url( 'civicrm/admin/financial/financialType/accounts', 
                                                                     "reset=1&action=browse&aid={$this->_aid}" ) );
            } 
            
        }
    }
}

 
