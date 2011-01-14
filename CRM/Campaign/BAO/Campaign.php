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
        
        if ( !(CRM_Utils_Array::value('id', $params)) )  {
            
            if ( !(CRM_Utils_Array::value('created_id', $params)) ) {
                $session = CRM_Core_Session::singleton( );
                $params['created_id'] = $session->get( 'userID' );
            }
            
            if ( !(CRM_Utils_Array::value('created_date', $params)) ) {
                $params['created_date'] = date('YmdHis');
            }
            
            if ( !(CRM_Utils_Array::value('name', $params)) ) {
                $params['name'] =  CRM_Utils_String::titleToVar($params['title'], 64 );
            }
        }
        
        $campaign = new CRM_Campaign_DAO_Campaign();
        $campaign->copyValues( $params );
        $campaign->save();
       
        /* Create the campaign group record */
        $groupTableName   = CRM_Contact_BAO_Group::getTableName( );
        require_once 'CRM/Campaign/DAO/CampaignGroup.php';
        $dao = new CRM_Campaign_DAO_CampaignGroup();
        
        if ( CRM_Utils_Array::value( 'include', $params['groups'] ) && 
             is_array( $params['groups']['include'] ) ) {                    
            foreach( $params['groups']['include'] as $entityId ) {
                $dao->reset( );
                $dao->campaign_id  = $campaign->id;
                $dao->entity_table = $groupTableName;
                $dao->entity_id    = $entityId;
                $dao->group_type   = 'include';
                $dao->save( );
            }
        }
        
        //store custom data
        if ( CRM_Utils_Array::value( 'custom', $params ) &&
             is_array( $params['custom'] ) ) {
            require_once 'CRM/Core/BAO/CustomValueTable.php';
            CRM_Core_BAO_CustomValueTable::store( $params['custom'], 'civicrm_campaign', $campaign->id );
        }
        
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
    
    /**
     * Return the all eligible campaigns w/ cache.
     *
     * @param int      $includeId  lets inlcude this campaign by force.
     * @param int      $excludeId  do not include this campaign.
     * @param boolean  $onlyActive consider only active campaigns.
     *
     * @return $campaigns a set of campaigns.
     * @access public
     */
    public static function getCampaigns( $includeId = null, 
                                         $excludeId = null, 
                                         $onlyActive = true, 
                                         $forceAll = false ) 
    {
        static $campaigns;
        $cacheKey = 0;
        foreach ( array( 'includeId', 'excludeId', 'onlyActive', 'forceAll' ) as $param ) {
            $cacheParam = $$param;
            if ( !$cacheParam ) $cacheParam = 0;
            $cacheKey .= '_' . $cacheParam;
        }
        
        if ( !isset( $campaigns[$cacheKey] ) ) {
            $where = array( '( camp.title IS NOT NULL )',
                            '( camp.end_date IS NULL OR camp.end_date >= CURDATE() )');
            if ( $excludeId  ) $where[] = "( camp.id != $excludeId )";
            if ( $onlyActive ) $where[] = '( camp.is_active = 1 )';
            $whereClause = implode( ' AND ', $where );
            if ( $includeId ) $whereClause .= " OR ( camp.id = $includeId )"; 
            
            //lets force all.
            if ( $forceAll ) $whereClause = '( 1 )'; 
            
            $query = "
SELECT  camp.id, camp.title
  FROM  civicrm_campaign camp
 WHERE  {$whereClause}";
            
            $campaign = CRM_Core_DAO::executeQuery( $query );
            $campaigns[$cacheKey] = array( );
            while ( $campaign->fetch( ) ) {
                $campaigns[$cacheKey][$campaign->id] = $campaign->title;
            }
        }
        
        return $campaigns[$cacheKey];
    }
    
    /**
     * Wrapper to self::getCampaigns( )
     * w/ permissions and component check.
     *
     */
    public static function getPermissionedCampaigns( $includeId  = null, 
                                                     $excludeId  = null, 
                                                     $onlyActive = true,
                                                     $doCheckForComponent   = true,
                                                     $doCheckForPermissions = true  ) {
        $cacheKey = 0;
        $cachekeyParams = array( 'includeId', 'excludeId', 'onlyActive', 
                                 'doCheckForComponent', 'doCheckForPermissions' );
        foreach ( $cachekeyParams as $param ) {
            $cacheKeyParam = $$param;
            if ( !$cacheKeyParam ) $cacheKeyParam = 0;
            $cacheKey .= '_' . $cacheKeyParam;
        }
        
        static $validCampaigns;
        if ( !isset( $validCampaigns[$cacheKey] ) ) {
            $isValid   = true;
            $campaigns = array( 'campaigns'         => array( ),
                                'hasAccessCampaign' => false,
                                'isCampaignEnabled' => false );
            
            //do check for component.
            if ( $doCheckForComponent ) {
                $campaigns['isCampaignEnabled'] = $isValid = self::isCampaignEnable( );
            }
            
            //do check for permissions.
            if ( $doCheckForPermissions ) {
                $campaigns['hasAccessCampaign'] = $isValid = self::accessCampaign( );
            }
            
            //finally retrieve campaigns from db.
            if ( $isValid ) $campaigns['campaigns'] = self::getCampaigns( $includeId, $excludeId, $onlyActive );  
            
            //store in cache.
            $validCampaigns[$cacheKey] = $campaigns;
        }
        
        return $validCampaigns[$cacheKey];
    }
    
    /*
     * Is CiviCampaign enabled.
     *
     */
    public static function isCampaignEnable( ) 
    {
        static $isEnable = null;
        
        if ( !isset( $isEnable ) ) { 
            $isEnable = false;
            $config = CRM_Core_Config::singleton( );
            if ( in_array( 'CiviCampaign', $config->enableComponents ) ) {
                $isEnable = true;
            }
        }
        
        return $isEnable;
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
     * Function to get Campaigns groups
     *
     * @param int $campaignId campaign id 
     *
     * @static
     */
    static function getCampaignGroups( $campaignId ) 
    {
        $campaignGroups = array( ); 
        if ( !$campaignId ) return $campaignGroups; 
        
        require_once 'CRM/Campaign/DAO/CampaignGroup.php';
        $campGrp = new CRM_Campaign_DAO_CampaignGroup( );
        $campGrp->campaign_id = $campaignId;
        $campGrp->group_type  = 'Include'; 
        $campGrp->find( );
        while ( $campGrp->fetch() ) {
            CRM_Core_DAO::storeValues( $campGrp, $campaignGroups[$campGrp->id] );
        }
        
        return $campaignGroups;
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
    
    static function accessCampaign( ) {
        $allow = false;
        if ( CRM_Core_Permission::check( 'manage campaign' ) ||
             CRM_Core_Permission::check( 'administer CiviCampaign' ) ) {
            $allow = true;
        }
        
        return $allow;
    }
    
    /*
     * Add select element for campaign 
     * and assign needful info to templates.
     *
     */
    public static function addCampaign( &$form, $connectedCampaignId = null ) 
    {
        $campaignDetails = self::getPermissionedCampaigns( $connectedCampaignId );
        $fields = array( 'campaigns', 'hasAccessCampaign', 'isCampaignEnabled' );
        foreach ( $fields as $fld ) $$fld = CRM_Utils_Array::value( $fld, $campaignDetails ); 
        $hasCampaigns = false;
        if ( !empty( $campaigns ) ) $hasCampaigns = true;
        
        $showAddCampaign = false;
        if ( $connectedCampaignId || ( $isCampaignEnabled && $hasAccessCampaign ) ) {
            $showAddCampaign = true;
            $campaign =& $form->add( 'select', 
                                     'campaign_id', 
                                     ts( 'Campaign' ), 
                                     array( '' => ts( '- select -' ) ) + $campaigns );
            //lets freeze when user does not has access or campaign is disabled. 
            if ( !$isCampaignEnabled || !$hasAccessCampaign ) $campaign->freeze( ); 
        }
        
        $addCampaignURL = null;
        if ( empty( $campaigns ) && $hasAccessCampaign && $isCampaignEnabled ) {
            $addCampaignURL = CRM_Utils_System::url( 'civicrm/campaign/add', 'reset=1' );
        }
        $infoFields = array( 'hasCampaigns', 
                             'addCampaignURL', 
                             'showAddCampaign', 
                             'hasAccessCampaign', 
                             'isCampaignEnabled' );
        foreach ( $infoFields as $fld ) $campaignInfo[$fld] = $$fld; 
        $form->assign( 'campaignInfo', $campaignInfo );
    }
    
}
