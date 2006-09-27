<?php
/*
 +--------------------------------------------------------------------+
 | CiviCRM version 1.6                                                |
 +--------------------------------------------------------------------+
 | Copyright CiviCRM LLC (c) 2004-2006                                  |
 +--------------------------------------------------------------------+
 | This file is a part of CiviCRM.                                    |
 |                                                                    |
 | CiviCRM is free software; you can copy, modify, and distribute it  |
 | under the terms of the Affero General Public License Version 1,    |
 | March 2002.                                                        |
 |                                                                    |
 | CiviCRM is distributed in the hope that it will be useful, but     |
 | WITHOUT ANY WARRANTY; without even the implied warranty of         |
 | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.               |
 | See the Affero General Public License for more details.            |
 |                                                                    |
 | You should have received a copy of the Affero General Public       |
 | License along with this program; if not, contact the Social Source |
 | Foundation at info[AT]civicrm[DOT]org.  If you have questions       |
 | about the Affero General Public License or the licensing  of       |
 | of CiviCRM, see the Social Source Foundation CiviCRM license FAQ   |
 | http://www.civicrm.org/licensing/                                  |
 +--------------------------------------------------------------------+
*/

/**
 *
 * @package CRM
 * @author Donald A. Lobo <lobo@civicrm.org>
 * @copyright CiviCRM LLC (c) 2004-2006
 * $Id$
 *
 */

/** 
 *  this file contains functions for Household
 */


require_once 'CRM/Quest/DAO/Household.php';

class CRM_Quest_BAO_Household extends CRM_Quest_DAO_Household {

    
    /**
     * class constructor
     */
    function __construct( ) {
        parent::__construct( );
    }

    

    /**
     * function to add/update Household Information
     *
     * @param array $params reference array contains the values submitted by the form
     * @param array $ids    reference array contains the id
     * 
     * @access public
     * @static 
     * @return object
     */
    static function create(&$params, &$ids) {
        
        $dao = & new CRM_Quest_DAO_Household();
        $dao->copyValues($params);
        if( $ids['id'] ) {
            $dao->id = $ids['id'];
        }
        $student = $dao->save();
        return $student;
        
    }

    /**
     * function to get person ids (households)
     *
     * @param int  $params reference array contains the values submitted by the form
     *
     * @access public
     * @static 
     * @return array of ids
     */
    static function getHouseholdsIds( $conatcID ) {
        $personIds = array();
        require_once 'CRM/Quest/DAO/Household.php';
        $dao = & new CRM_Quest_DAO_Household();
        $dao->contact_id = $conatcID;
        $dao->find( );
        while ( $dao->fetch() )  {
            if ( $dao->person_1_id ) {
                $personIds[$dao->person_1_id] = $dao->person_1_id;
            }
            if ( $dao->person_2_id ) {
                $personIds[$dao->person_2_id] = $dao->person_2_id;
            }
        }

        return $personIds;
    }


}
    
?>