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

class CRM_Campaign_BAO_Query 
{
    //since normal activity clause clause get collides.
    const
        civicrm_activity         =  'civicrm_survey_activity',
        civicrm_activity_target  =  'civicrm_survey_activity_target';
    
    /**
     * static field for all the campaign fields
     *
     * @var array
     * @static
     */
    static $_campaignFields = null;
    
    /**
     * Function get the fields for campaign.
     *
     * @return array self::$_campaignFields  an associative array of campaign fields
     * @static
     */
    static function &getFields( ) 
    {
        if ( !isset( self::$_campaignFields ) ) {
            self::$_campaignFields = array( );
        }
        
        return self::$_campaignFields;
    }
    
    /** 
     * if survey, campaign are involved, add the specific fields.
     * 
     * @return void  
     * @access public  
     */
    static function select( &$query ) 
    {
        // get survey activity target table in.
        if ( CRM_Utils_Array::value( 'survey_activity_target_id', $query->_returnProperties ) ) {
            $query->_select['survey_activity_target_id'] = 'civicrm_activity_target.target_contact_id as survey_activity_target_id';
            $query->_element['survey_activity_target_id']       = 1;
            $query->_tables[self::civicrm_activity_target]      = 1;
            $query->_whereTables[self::civicrm_activity_target] = 1;
        }
        
        // get survey activity table in.
        if ( CRM_Utils_Array::value( 'survey_activity_id', $query->_returnProperties ) ) {
            $query->_select['survey_activity_id']        = 'civicrm_activity.id as survey_activity_id';
            $query->_element['survey_activity_id']       = 1;
            $query->_tables[self::civicrm_activity]      = 1;
            $query->_whereTables[self::civicrm_activity] = 1;
        }
        
        // get survey table.
        if ( CRM_Utils_Array::value( 'campaign_survey_id', $query->_returnProperties ) ) {
            $query->_select['campaign_survey_id']  = 'civicrm_survey.id as survey_id';
            $query->_element['campaign_survey_id'] = 1;
            $query->_tables['civicrm_survey']      = 1;
            $query->_whereTables['civicrm_survey'] = 1;
        }
        
        // get campaign table.
        if ( CRM_Utils_Array::value( 'campaign_id', $query->_returnProperties ) ) {
            $query->_select['campaign_id']           = 'civicrm_campaign.id as campaign_id';
            $query->_element['campaign_id']          = 1;
            $query->_tables['civicrm_campaign']      = 1;
            $query->_whereTables['civicrm_campaign'] = 1;
        }
        
    }
    
    static function where( &$query ) 
    {        
        $grouping = null;
        foreach ( array_keys( $query->_params ) as $id ) {
            if ( $query->_mode == CRM_Contact_BAO_QUERY::MODE_CONTACTS ) {
                $query->_useDistinct = true;
            }
            if ( in_array( $query->_params[$id][0], array( 'campaign_survey_id', 'survey_status_id' ) ) ) {
                $query->_tables['civicrm_survey']              = $query->_whereTables['civicrm_survey'  ] = 1;
                $query->_tables[self::civicrm_activity]        = $query->_whereTables['survey_civicrm_activity'] = 1;
                $query->_tables[self::civicrm_activity_target] = $query->_whereTables[self::civicrm_activity_target] = 1;
            }
            
            self::whereClauseSingle( $query->_params[$id], $query );
        }
    }
    
    static function whereClauseSingle( &$values, &$query ) 
    {
        list( $name, $op, $value, $grouping, $wildcard ) = $values;
        
        $fields = array( );
        $fields = self::getFields();
        if ( !empty ( $value ) ) {
            $quoteValue = "\"$value\"";
        }
        
        switch ( $name ) {
            
        case 'campaign_survey_id' :
            $aType = $value;
            $query->_qill[$grouping ][] = ts( 'Survey - %1', array( 1 => CRM_Core_DAO::getFieldValue( 'CRM_Campaign_DAO_Survey', $value, 'title' ) ) );
            
            $query->_where[$grouping][] = CRM_Contact_BAO_Query::buildClause( 'civicrm_activity.source_record_id', 
                                                                              $op, $value, "Integer" );
            $query->_where[$grouping][] = CRM_Contact_BAO_Query::buildClause( 'civicrm_survey.id', 
                                                                              $op, $value, "Integer" );
            return;
        case 'survey_status_id' :
            require_once 'CRM/Core/PseudoConstant.php';
            $activityStatus = CRM_Core_PseudoConstant::activityStatus( );

            $query->_qill[$grouping ][] = ts( 'Survey Status - %1', array( 1 => $activityStatus[$value] ) );
            $query->_where[$grouping][] = CRM_Contact_BAO_Query::buildClause( 'civicrm_activity.status_id', 
                                                                              $op, $value, "Integer" );
            return;
        case 'campaign_search_voter_for' :
            if ( in_array( $value, array('release', 'interview' ) ) ) {
                $query->_where[$grouping][] = '(civicrm_activity.is_deleted = 0 OR civicrm_activity.is_deleted IS NULL)';
            }
            return;
        }
    }
    
    static function from( $name, $mode, $side ) 
    {
        $from = null;
        switch ( $name ) {
            
        case self::civicrm_activity_target :
            $from = " $side JOIN civicrm_activity_target ON civicrm_activity_target.target_contact_id = contact_a.id ";
            break;
            
        case self::civicrm_activity :
            require_once 'CRM/Campaign/PseudoConstant.php';
            $surveyActivityTypes = CRM_Campaign_PseudoConstant::activityType( );
            $from = " $side JOIN civicrm_activity ON ( civicrm_activity.id = civicrm_activity_target.activity_id AND civicrm_activity.activity_type_id IN (". implode( ',', array_keys( $surveyActivityTypes ) ) .") ) ";
            break;
            
        case 'civicrm_survey':
            $from = " $side JOIN civicrm_survey ON civicrm_survey.id = civicrm_activity.source_record_id ";
            break;
            
        case 'civicrm_campaign':
            $from = " $side JOIN civicrm_campaign ON civicrm_campaign.id = civicrm_survey.campaign_id ";
            break;
        }
        
        return $from;
    }
    
    static function defaultReturnProperties( $mode ) 
    {
        $properties = null;
        if ( $mode & CRM_Contact_BAO_Query::MODE_CAMPAIGN ) {
            $properties = array(
                                'contact_id'                => 1,
                                'contact_type'              => 1, 
                                'sort_name'                 => 1, 
                                'display_name'              => 1,
                                'street_number'             => 1,
                                'street_address'            => 1,
                                'city'                      => 1, 
                                'postal_code'               => 1,
                                'state_province'            => 1,
                                'country'                   => 1,
                                'email'                     => 1,
                                'phone'                     => 1,
                                'survey_activity_target_id' => 1,
                                'survey_activity_id'        => 1,
                                'survey_status_id'          => 1,
                                'campaign_survey_id'        => 1,
                                'campaign_id'               => 1
                                );
        }
        
        return $properties;
    }
    
    static function tableNames( &$tables ) 
    {
    }
    static function searchAction( &$row, $id ) 
    {
    }
    
    static function info( &$tables ) {
        $weight = end( $tables );
        $tables[self::civicrm_activity_target] = ++$weight;
        $tables[self::civicrm_activity]        = ++$weight;
        $tables['civicrm_survey']              = ++$weight;
        $tables['civicrm_campaign']            = ++$weight;
    }
    
}

