<?php
/*
 +--------------------------------------------------------------------+
 | CiviCRM version 1.5                                                |
 +--------------------------------------------------------------------+
 | Copyright (c) 2005 Donald A. Lobo                                  |
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
 | Foundation at info[AT]socialsourcefoundation[DOT]org.  If you have |
 | questions about the Affero General Public License or the licensing |
 | of CiviCRM, see the Social Source Foundation CiviCRM license FAQ   |
 | at http://www.openngo.org/faqs/licensing.html                       |
 +--------------------------------------------------------------------+
*/

/**
 *
 * @package CRM
 * @author Donald A. Lobo <lobo@yahoo.com>
 * @copyright Donald A. Lobo (c) 2005
 * $Id$
 *
 */

/** 
 *  this file contains functions for Partners
 */


require_once 'CRM/Quest/DAO/Partner.php';

class CRM_Quest_BAO_Partner extends CRM_Quest_DAO_Partner {

    
    /**
     * class constructor
     */
    function __construct( ) {
        parent::__construct( );
    }

    /**
     * function to get all parnters
     *
     */
    function getPartners( $type = 'College')
    {
        $partners = array();
        $dao = &new CRM_Quest_DAO_Partner();
        if ( $type != 'All' ) {
            $dao->partner_type =  $type ;
        }
        $dao->orderBy('weight');
        $dao->find();
        while( $dao->fetch() ) {
            $partners[$dao->id] = $dao->name;
        }

        return $partners;
    }
    
     /**
     * function to add/update partner Information
     *
     * @param array $params reference array contains the values submitted by the form
     * @param array $ids    reference array contains the id
     * 
     * @access public
     * @static 
     * @return object
     */
    static function &createRelative(&$relativeParams, &$ids) {
        $dao = & new CRM_Quest_DAO_PartnerRelative();
        $dao->copyValues($relativeParams);
        if( $ids['id'] ) {
            $dao->id = $ids['id'];
        }
        $dao->save();
        
        return $dao;
    }

    static function &getPartnersForContact( $cid, $is_supplement = null ) {
        $query = "
SELECT p.name as name
FROM   quest_partner p,
       quest_partner_ranking r
WHERE  r.contact_id  = $cid
  AND  r.partner_id  = p.id
  AND  ( r.ranking     >= 1 OR
         r.is_forward  = 1 )
";

        if ( $is_supplement !== null ) {
            $query .= " AND p.is_supplement = $is_supplement";
        }

        $partners = array( );
        $dao =& CRM_Core_DAO::executeQuery( $query, CRM_Core_DAO::$_nullArray );
        while ( $dao->fetch( ) ) {
            $partners[$dao->name] = 1;
        }
        return $partners;
    }
}
    
?>