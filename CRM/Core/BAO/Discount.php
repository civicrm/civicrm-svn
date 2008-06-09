<?php

/*
 +--------------------------------------------------------------------+
 | CiviCRM version 2.1                                                |
 +--------------------------------------------------------------------+
 | Copyright CiviCRM LLC (c) 2004-2008                                |
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
 * @copyright CiviCRM LLC (c) 2004-2007
 * $Id$
 *
 */

require_once 'CRM/Core/DAO/Discount.php';

class CRM_Core_BAO_Discount extends CRM_Core_DAO_Discount 
{

    /**
     * class constructor
     */
    function __construct( ) 
    {
        parent::__construct( );
    }


    /**
     * Function to delete the discount
     *
     * @param int $id   discount id
     *
     * @return boolean
     * @access public
     * @static
     *
     */
    static function del ( $id ) 
    {
        // delete all discount records with the selected discounted id
        $discount =& new CRM_Core_DAO_Discount( );
        $discount->id = $id;
        if ( $discount->delete( ) ) {
            return true;
        }
        return false;
    }

    /**
     *
     * The function extracts all the params it needs to create a
     * discount object. the params array contains additional unused name/value
     * pairs
     * 
     * @param array  $params         (reference) an assoc array of name/value pairs
     * 
     * @return object    CRM_Core_DAO_Discount object on success, otherwise null
     * @access public
     * @static
     */
    static function add( &$params ) 
    {
        $discount =& new CRM_Core_DAO_Discount( );
        $discount->copyValues( $params );
        $discount->save( );
        return $discount;
    }
    
    /**
     * Determine whether the given table/id 
     * has discount associated with it
     *
     * @param  integer  $entityId      entity id to be searched 
     * @param  string   $entityTable   entity table to be searched 
     * @return array    $optionGroupIDs option group Ids associated with discount
     *
     */
    static function getOptionGroup( $entityId, $entityTable ) 
    {
        require_once 'CRM/Core/DAO/Discount.php';
        $dao =& new CRM_Core_DAO_Discount( );
        $dao->entity_id    = $entityId;
        $dao->entity_table = $entityTable;
        $dao->find( );
        while ( $dao->fetch( ) ) {
            $optionGroupIDs[$dao->id] = $dao->option_group_id;
        }
        return $optionGroupIDs;
    }

    /**
     * Determine in which discount set the registration date falls
     *
     * @param  integer  $entityId      entity id to be searched 
     * @param  string   $entityTable   entity table to be searched 
     *
     * @return integer  $i             discount set no. which matches
     *                                 the date criteria
     */
    static function findSet( $entityId, $entityTable ) 
    {
        require_once 'CRM/Core/DAO/Discount.php';
        $dao =& new CRM_Core_DAO_Discount( );
        $dao->entity_id    = $entityId;
        $dao->entity_table = $entityTable;
        $dao->find( );
        $i = 0;
        require_once "CRM/Utils/Date.php";
        while ( $dao->fetch( ) ) {
            $i++;
            $falls = CRM_Utils_Date::getRange( $dao->start_date, $dao->end_date);
            if ( $falls == true) {
                return $i;
            }
        }
        return false;
    }
}


