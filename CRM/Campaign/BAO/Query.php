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
        // get survey table.
        if ( CRM_Utils_Array::value( 'survey_id', $query->_returnProperties ) ) {
            $query->_select['survey_id']           = 'civicrm_survey.id as survey_id';
            $query->_element['survey_id']          = 1;
            $query->_tables['civicrm_survey']      = 1;
            $query->_whereTables['civicrm_survey'] = 1;
        }
        
        // get camaign table.
        if ( CRM_Utils_Array::value( 'camaign_id', $query->_returnProperties ) ) {
            $query->_select['campaign_id']           = 'civicrm_campaign.id as campaign_id';
            $query->_element['camaign_id']           = 1;
            $query->_tables['civicrm_campaign']      = 1;
            $query->_whereTables['civicrm_campaign'] = 1;
        }
        
    }
    
    static function where( &$query ) 
    {
        $isTest   = false;
        $grouping = null;
        foreach ( array_keys( $query->_params ) as $id ) {
            if ( substr( $query->_params[$id][0], 0, 9 ) == 'campaign_' ) {
                if ( $query->_mode == CRM_Contact_BAO_QUERY::MODE_CONTACTS ) {
                    $query->_useDistinct = true;
                }
                
                $grouping = $query->_params[$id][3];
                self::whereClauseSingle( $query->_params[$id], $query );
            }
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
        
        require_once 'CRM/Campaign/PseudoConstant.php';
        $surveyActivityTypes = CRM_Campaign_PseudoConstant::activityType( );
        
        switch ( $name ) {
            
        case 'survey_id' :
            $aType = $value;
            $query->_qill[$grouping ][] = ts( 'Survey Type - %1', array( 1 => $surveyActivityTypes[$cType] ) );
            $query->_tables['civicrm_survey'  ] = $query->_whereTables['civicrm_survey'  ] = 1;
            $query->_tables['civicrm_activity'] = $query->_whereTables['civicrm_activity'] = 1;
            $query->_where[$grouping][] = CRM_Contact_BAO_Query::buildClause( 'civicrm_activity.source_record_id', 
                                                                              $op, $value, "Integer" );
            $query->_where[$grouping][] = CRM_Contact_BAO_Query::buildClause( 'civicrm_survey.id', 
                                                                              $op, $value, "Integer" );
            
            $typeIds = implode( ',', array_keys( $surveyActivityTypes ) ); 
            $query->_where[$grouping][] = CRM_Contact_BAO_Query::buildClause( 'civicrm_activity.activity_type_id', 
                                                                              $op, $typeIds, 'String' );
            return;
        }
    }
    
    static function from( $name, $mode, $side ) 
    {
        $from = null;
        switch ( $name ) {
            
        case 'civicrm_survey':
            $from = ' INNER JOIN civicrm_survey ON civicrm_survey.id = civicrm_activity.source_record_id ';
            break;
            
        case 'civicrm_campaign':
            $from = " $side JOIN civicrm_campaign ON civicrm_campign.survey_id = civicrm_survey.id ";
            break;
            
        }
        
        return $from;
    }
    
    static function defaultReturnProperties( $mode ) 
    {
        $properties = null;
        if ( $mode & CRM_Contact_BAO_Query::MODE_CAMPAIGN ) {
            $properties = array(
                                'contact_id'              => 1,
                                'contact_type'            => 1, 
                                'sort_name'               => 1, 
                                'display_name'            => 1,
                                'street_number'           => 1,
                                'street_address'          => 1,
                                'city'                    => 1, 
                                'postal_code'             => 1,
                                'state_province'          => 1,
                                'country'                 => 1,
                                'email'                   => 1,
                                'phone'                   => 1,
                                'campign_id'              => 1,
                                );
        }
        
        return $properties;
    }

    static function tableNames( &$tables ) 
    {
    }
    
}

