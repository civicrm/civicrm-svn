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

require_once 'CRM/Core/Page/Basic.php';

/**
 * Page for displaying list of financial types
 */
class CRM_Financial_Page_FinancialTypeAccount extends CRM_Core_Page 
{
    /**
     * The action links that we need to display for the browse screen
     *
     * @var array
     * @static
     */
    static $_links = null;

    /**
     * The account id that we need to display for the browse screen
     *
     * @var array
     * @static
     */
    static $_aid = null;
    

    /**
     * Get BAO Name
     *
     * @return string Classname of BAO.
     */
    function getBAOName() 
    {
        return 'CRM_Financial_BAO_FinancialTypeAccount';
    }

    /**
     * Get action Links
     *
     * @return array (reference) of action links
     */
    function &links()
        {
            if (!(self::$_links)) {
                self::$_links = array(
                                      CRM_Core_Action::UPDATE  => array(
                                                                        'name'  => ts('Edit'),
                                                                        'url'   => 'civicrm/admin/financial/financialType/accounts',
                                                                        'qs'    => 'action=update&id=%%id%%&aid=%%aid%%&reset=1',
                                                                        'title' => ts('Edit Financial Type Account') 
                                                                        ),
                                      CRM_Core_Action::DELETE  => array(
                                                                        'name'  => ts('Delete'),
                                                                        'url'   => 'civicrm/admin/financial/financialType/accounts',
                                                                        'qs'    => 'action=delete&id=%%id%%&aid=%%aid%%',
                                                                        'title' => ts('Delete Financial Type Account') 
                                                                        )
                                      );
            }
            return self::$_links;
        }

    /**
     * Run the page.
     *
     * This method is called after the page is created. It checks for the  
     * type of action and executes that action.
     * Finally it calls the parent's run method.
     *
     * @return void
     * @access public
     *
     */
    function run()
    {
        // get the requested action
        $action = CRM_Utils_Request::retrieve('action', 'String',
                                              $this, false, 'browse'); // default to 'browse'

        // assign vars to templates
        $this->assign('action', $action);
        $id = CRM_Utils_Request::retrieve('id', 'Positive',
                                          $this, false, 0);
        $this->_aid = CRM_Utils_Request::retrieve('aid', 'Positive',
                                          $this, false, 0);

        // what action to take ?
        if ($action & (CRM_Core_Action::UPDATE | CRM_Core_Action::ADD | CRM_Core_Action::DELETE)) {
            $this->edit($action, $id) ;
        } else
              $this->browse($action, $id) ;
        // parent run 
        return parent::run();
    }

    /**
     * Browse all custom data groups.
     *  
     * 
     * @return void
     * @access public
     * @static
     */
    function browse()
    {
        // get all custom groups sorted by weight
        $financialType = array();
        require_once 'CRM/Financial/DAO/EntityFinancialAccount.php';
        require_once  "CRM/Core/OptionGroup.php";
        require_once 'CRM/Financial/BAO/FinancialAccount.php';
        require_once 'CRM/Contact/BAO/Contact.php';
        $params = array();
        $dao = new CRM_Financial_DAO_EntityFinancialAccount();
        $params['entity_id'] = $this->_aid;
        if ( $this->_aid ) {
            $this->_title = CRM_Core_DAO::getFieldValue( 'CRM_Financial_DAO_FinancialType', $this->_aid, 'name' );
            CRM_Utils_System::setTitle( $this->_title .' - '.ts( 'Financial Accounts' ) );
          
        $dao->copyValues( $params );
        //$dao->orderBy('id');
        $dao->find();

        while ($dao->fetch()) {
            $financialType[$dao->id] = array();
            CRM_Core_DAO::storeValues( $dao, $financialType[$dao->id] );

            $params = array( 'id' => $dao->financial_account_id );
            $defaults = array();
            $financialAccount = CRM_Financial_BAO_FinancialAccount::retrieve( &$params, &$defaults );
            if( !empty($financialAccount) ){
                $financialType[$dao->id]['financial_account'] =  $financialAccount->name;
                $financialType[$dao->id]['accounting_code'] = $financialAccount->accounting_code;
                $financialType[$dao->id]['is_active'] = $financialAccount->is_active;
                if( !empty( $financialAccount->contact_id ) ){
                    $financialType[$dao->id]['owned_by'] = CRM_Contact_BAO_Contact::displayName( $financialAccount->contact_id );
                }
                if ( !empty( $financialAccount->financial_account_type_id ) ){
                    $optionGroupName = 'financial_account_type';
                
                    $financialType[$dao->id]['financial_account_type'] = current (CRM_Core_OptionGroup::values( $optionGroupName, false, false, false, "and v.value = {$financialAccount->financial_account_type_id} " ) ) ;
                }
                if ( !empty( $dao->account_relationship ) ){
                    $optionGroupName = 'account_relationship';
                
                    $financialType[$dao->id]['account_relationship'] = current (CRM_Core_OptionGroup::values( $optionGroupName, false, false, false, "and v.value = {$dao->account_relationship} " ) ) ;
                }
            }
            
            // form all action links
            $action = array_sum(array_keys($this->links()));
            $financialType[$dao->id]['action'] = CRM_Core_Action::formLink(self::links(), $action, 
                                                                           array( 'id' => $dao->id,
                                                                                  'aid'=> $dao->entity_id ) );
        }
        $this->assign('rows', $financialType);
        $this->assign( 'aid', $this->_aid );
    }else{
            CRM_Core_Error::fatal( );
            return null;
    }

    }
    
    /**
     * edit CiviCRM Financial Type Account data.
     *
     * editing would involved modifying existing financial Account Type + adding data
     * to new financial Account Type.
     *
     * @param string $action the action to be invoked
     *
     * @return void
     * @access public
     */

      function edit( $action )
    {
          // create a simple controller for editing CiviCRM Profile data
          $controller = new CRM_Core_Controller_Simple( 'CRM_Financial_Form_FinancialTypeAccount', ts('Financial Account Types'), $action );
    
          // set the userContext stack
          $session = CRM_Core_Session::singleton();
          $session->pushUserContext( CRM_Utils_System::url( 'civicrm/admin/financial/financialType/accounts',
                                                            'reset=1&action=browse&aid=' . $this->_aid ) );
          $controller->set( 'aid', $this->_aid );
        
          $controller->setEmbedded( true );
          $controller->process();
          $controller->run();
    }
}

