<?php

/*
 +--------------------------------------------------------------------+
 | CiviCRM version 3.2                                                |
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

require_once 'CRM/Core/Form.php';
require_once 'CRM/Campaign/DAO/Campaign.php';

Class CRM_Campaign_BAO_Campaign extends CRM_Campaign_DAO_Campaign
{
    /**
     * takes an associative array and creates a campaign object
     *
     * the function extract all the params it needs to initialize the create a
     * contact object. the params array could contain additional unused name/value
     * pairs
     *
     * @param array  $params (reference ) an assoc array of name/value pairs
     *
     * @return object CRM_Campaign_DAO_Campaign object
     * @access public
     * @static
     */
    static function create( &$params ) 
    {
        if ( empty( $params ) ) {
            return;
        }
       
        $campaign = new CRM_Campaign_DAO_Campaign();
        $campaign->copyValues( $params );
        $campaign->save();

        return $campaign;
    }
   
    /**
     * function to delete the campaign
     *
     * @param  int $id id of the campaign
     */
    public static function del( $id )
    {
        if ( !$id ) {
            return false;
        }
        $dao     = new CRM_Campaign_DAO_Campaign( );
        $dao->id = $id;
        return $dao->delete( );
    }

    /**
     * Takes a bunch of params that are needed to match certain criteria and
     * retrieves the relevant objects. Typically the valid params are only
     * campaign_id. 
     *
     * @param array  $params   (reference ) an assoc array of name/value pairs
     * @param array  $defaults (reference ) an assoc array to hold the flattened values
     *
     * @access public
     */
    public function retrieve ( &$params, &$defaults ) 
    {
        $campaign = new CRM_Campaign_DAO_Campaign( );
        
        $campaign->copyValues($params);
        
        if( $campaign->find( true ) ) {
            CRM_Core_DAO::storeValues( $campaign, $defaults );
            return $campaign;
        }
        return null;  
    }

    public function getAllCampaign( $id=null ) 
    {
        $campaigns = array( );
        $whereClause = null;
        if ( $id ) {
            $whereClause = " AND c.id != ".$id;
        }
        $campaignParent = array();
        $sql = "
SELECT c.id as id, c.title as title
FROM  civicrm_campaign c
WHERE c.title IS NOT NULL" . $whereClause;
        
        $dao =& CRM_Core_DAO::executeQuery( $sql );
        while ( $dao->fetch() ) {
           $campaigns[$dao->id] = $dao->title;
           
        }
        
        return  $campaigns ;

    }

     /**
     * Function to get Campaigns 
     *
     * @param $all boolean true if campaign is active else returns camapign 
     *
     * @static
     */
    static function getCampaign( $all = false, $id = false) 
    {
       $campaign = array( );
       $dao = new CRM_Campaign_DAO_Campaign( );
       if ( !$all ) {
           $dao->is_active = 1;
       }
       
       if ( $id ) {
           $dao->id = $id;  
       }
       $dao->find( );
       while ( $dao->fetch() ) {
           CRM_Core_DAO::storeValues($dao, $campaign[$dao->id]);
       }
       
       return $campaign;
    }

    /**
     * update the is_active flag in the db
     *
     * @param int      $id        id of the database record
     * @param boolean  $is_active value we want to set the is_active field
     *
     * @return Object             DAO object on sucess, null otherwise
     * @static
     */ 
    static function setIsActive( $id, $is_active ) 
    {  
        return CRM_Core_DAO::setFieldValue( 'CRM_Campaign_DAO_Campaign', $id, 'is_active', $is_active );
    }
}

?>