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
 *
 */

/**
 * This class contains all the function that are called using AJAX (jQuery)
 */
class CRM_Group_Page_AJAX
{
    static function getGroupList( ) 
    {
    
        $sortMapper  = array( 0 => 'label', 1 => 'id', 2 => '', 
                              3 => 'group_type', 4 => 'visibility' );

        $sEcho       = CRM_Utils_Type::escape($_REQUEST['sEcho'], 'Integer');
        $offset      = isset($_REQUEST['iDisplayStart'])? CRM_Utils_Type::escape($_REQUEST['iDisplayStart'], 'Integer'):0;
        $rowCount    = isset($_REQUEST['iDisplayLength'])? CRM_Utils_Type::escape($_REQUEST['iDisplayLength'], 'Integer'):25; 
        $sort        = isset($_REQUEST['iSortCol_0'] )? CRM_Utils_Array::value( CRM_Utils_Type::escape($_REQUEST['iSortCol_0'],'Integer'), $sortMapper ): null;
        $sortOrder   = isset($_REQUEST['sSortDir_0'] )? CRM_Utils_Type::escape($_REQUEST['sSortDir_0'], 'String'):'asc';

        $params    = $_POST;
        if ( $sort && $sortOrder ) {
            $params['sortBy']  = $sort . ' '. $sortOrder;
        }
        
        $params['page'] = ($offset/$rowCount) + 1;
        $params['rp']   = $rowCount;

        // get group list 
        require_once 'CRM/Contact/BAO/Group.php';
        $groups = CRM_Contact_BAO_Group::getGroupListSelector( $params );

        require_once "CRM/Utils/JSON.php";
        $iFilteredTotal = $iTotal =  $params['total'];
        $selectorElements = array( 'group_name', 'group_id', 'group_description',
                                   'group_type', 'visibility',
                                   'links' );

        echo CRM_Utils_JSON::encodeDataTableSelector( $groups, $sEcho, $iTotal, $iFilteredTotal, $selectorElements );
        CRM_Utils_System::civiExit( );
    }
}
