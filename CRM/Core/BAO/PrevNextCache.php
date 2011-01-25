<?php

/*
 +--------------------------------------------------------------------+
 | CiviCRM version 3.3                                                |
 +--------------------------------------------------------------------+
 | Copyright CiviCRM LLC (c) 2004-2010                                |
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
 * @copyright CiviCRM LLC (c) 2004-2010
 * $Id$
 *
 */

require_once 'CRM/Core/DAO/PrevNextCache.php';

/**
 * BAO object for crm_log table
 */

class CRM_Core_BAO_PrevNextCache extends CRM_Core_DAO_PrevNextCache
{

    function loadCache( $rgid, $gid, $cid, $oid, &$mergeId = null ) 
    {
        $contactType = CRM_Core_DAO::getFieldValue( 'CRM_Contact_DAO_Contact', $cid, 'contact_type' );
        $cacheKey  = "merge $contactType";
        $cacheKey .= $rgid ? "_{$rgid}" : '_0';
        $cacheKey .= $gid ? "_{$gid}" : '_0';
        
        if ( $mergeId == null ) {
            $query = "
SELECT id 
FROM   civicrm_prevnext_cache
WHERE  entity_id1 = $cid AND
       entity_id2 = $oid AND
       entity_table = 'civicrm_contact' AND
       cacheKey     = '$cacheKey'
";
            $mergeId = CRM_Core_DAO::singleValueQuery( $query );
        }
        
        $pos = array( );
        if ( $mergeId ) {
            $sqlPrev = "SELECT * FROM civicrm_prevnext_cache WHERE id < $mergeId ORDER BY ID DESC LIMIT 1";
            $dao = CRM_Core_DAO::executeQuery( $sqlPrev, CRM_Core_DAO::$_nullArray );
            if ( $dao->fetch() ) {
                $pos['prev']['id1']     = $dao->entity_id1;
                $pos['prev']['id2']     = $dao->entity_id2;
                $pos['prev']['mergeId'] = $dao->id;
            }
            
            $sqlNext = "SELECT * FROM civicrm_prevnext_cache WHERE id > $mergeId ORDER BY ID ASC LIMIT 1";
            $dao = CRM_Core_DAO::executeQuery( $sqlNext, CRM_Core_DAO::$_nullArray );
            if ( $dao->fetch() ) {
                $pos['next']['id1']     = $dao->entity_id1;
                $pos['next']['id2']     = $dao->entity_id2;
                $pos['next']['mergeId'] = $dao->id;
            }
        }   
        return $pos;
    }

    function clearCache( $id = null, $cacheKey = null )
    {
        //clear cache
        $sql = "DELETE FROM civicrm_prevnext_cache
                           WHERE  entity_table = 'civicrm_contact'";
        
        if ( is_numeric( $id ) ) {
            $sql .= " AND ( entity_id1 = {$id} OR
                            entity_id2 = {$id} )";
        }
        
        if ( isset( $cacheKey ) ) {
            $sql .= " AND cacheKey LIKE '%$cacheKey%'";
        }

        CRM_Core_DAO::executeQuery( $sql );
    }
    
    function reloadCache( $cacheKey ) 
    {
        $main = array();
        $query = "
SELECT data 
FROM   civicrm_prevnext_cache
WHERE  cacheKey = '$cacheKey'
";
        
        $dao = CRM_Core_DAO::executeQuery( $query );
        while ( $dao->fetch() ) {
            $main[] = unserialize( $dao->data );
        }
        
        return $main;
    }

}