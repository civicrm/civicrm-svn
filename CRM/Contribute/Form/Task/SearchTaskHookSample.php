<?php

/*
 +--------------------------------------------------------------------+
 | CiviCRM version 3.1                                                |
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

require_once 'CRM/Contribute/Form/Task.php';

/**
 * This class provides the functionality to save a search
 * Saved Searches are used for saving frequently used queries
 */
class CRM_Contribute_Form_Task_SearchTaskHookSample extends CRM_Contribute_Form_Task 
{
    /**
     * build all the data structures needed to build the form
     *
     * @return void
     * @access public
     */
    function preProcess( ) 
    {
        parent::preProcess( );
        $rows = array();
        // display name and email of all contact ids
        $contribIDs = implode( ',', $this->_contributionIds);      
        
        $query = "
SELECT c.display_name  as display_name,
       co.total_amount as amount,
       co.receive_date as receive_date,
       co.source       as source      
FROM   civicrm_contact c,
       civicrm_contribution co
WHERE  co.contact_id = c.id
AND    co.id IN ( $contribIDs )";
        
        $dao = CRM_Core_DAO::executeQuery( $query,
                                           CRM_Core_DAO::$_nullArray );
        
        while ( $dao->fetch( ) ) {
            $rows[]= array(
                           'display_name'    =>  $dao->display_name,
                           'amount'          =>  $dao->amount,
                           'source'          =>  $dao->source,
                           'receive_date'    =>  $dao->receive_date
                           );
        }
        $this->assign( 'rows', $rows );
    }
    
    /**
     * Function to actually build the form
     *
     * @return None
     * @access public
     */
    public function buildQuickForm( ) 
    {
        $this->addButtons( array(
                                 array ( 'type'      => 'done',
                                         'name'      => ts('Done'),
                                         'isDefault' => true   ),
                                 )
                           );
    }
}