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

require_once 'CRM/Report/Form.php';
require_once 'CRM/Grant/PseudoConstant.php';
require_once 'CRM/Contact/BAO/ContactType.php';
class CRM_Report_Form_Grant_Statistics extends CRM_Report_Form {
    
    protected $_addressField = false;

    protected $_customGroupExtends = array( 'Grant' );

    function __construct( ) 
    {
        $this->_columns = 
            array( 
                  'civicrm_grant'  =>
                  array( 'dao'     => 'CRM_Grant_DAO_Grant',
                         'fields'  =>
                         array( 'summary_statistics' => 
                                array( 'name'          => 'id', 
                                       'title'         => ts( 'Summary Statistics' ),
                                       'required'      => true,
                                       ),
                                'grant_type_id' =>
                                array( 'name'          => 'grant_type_id' ,
                                       'title'         => ts( 'By Grant Type' ),
                                       ),
                                'status_id' =>
                                array( 'no_display'    => true,
                                       'required'      => true, 
                                       ),
                                'amount_total' => 
                                array( 'no_display'    => true,
                                       'required'      => true, 
                                       ),
                                'grant_report_received' => 
                                array( 'no_display'    => true,
                                       'required'      => true, 
                                       ),
                                'currency' =>
                                array( 'no_display'    => true,
                                       'required'      => true, 
                                       ),
                                ),  
                         'filters' =>             
                         array( 'application_received_date' =>
                                array( 'name'          => 'application_received_date' ,
                                       'title'         => ts( 'Application Received' ),
                                       'operatorType'  => CRM_Report_Form::OP_DATE,
                                       'type'          => CRM_Utils_Type::T_DATE,
                                       ),
                                'decision_date'=>
                                array( 'name'          => 'decision_date' ,
                                       'title'         => ts( 'Grant Decision' ),
                                       'operatorType'  => CRM_Report_Form::OP_DATE,
                                       'type'          => CRM_Utils_Type::T_DATE,
                                       ),
                                'money_transfer_date' =>
                                array( 'name'          => 'money_transfer_date' ,
                                       'title'         => ts( 'Money Transfer Date' ),
                                       'operatorType'  => CRM_Report_Form::OP_DATE,
                                       'type'          => CRM_Utils_Type::T_DATE,
                                       ),
                                'grant_due_date' =>
                                array( 'name'          => 'grant_due_date' ,
                                       'title'         => ts( 'Grant Due Date' ),
                                       'operatorType'  => CRM_Report_Form::OP_DATE,
                                       'type'          => CRM_Utils_Type::T_DATE,
                                       ),
                                'grant_type'     => 
                                array( 'name'          => 'grant_type_id' ,
                                       'title'         => ts( 'Grant Type' ),
                                       'operatorType'  => CRM_Report_Form::OP_MULTISELECT,
                                       'options'       => CRM_Grant_PseudoConstant::grantType( ),
                                       ),
                                'status_id' => 
                                array( 'name'          => 'status_id',
                                       'title'         => ts('Grant Status' ),
                                       'operatorType'  => CRM_Report_Form::OP_MULTISELECT,
                                       'options'       => CRM_Grant_PseudoConstant::grantStatus( ),
                                       ),
                                'amount_requested' =>
                                array( 'name'          => 'amount_requested' ,
                                       'title'         => ts( 'Amount Requested' ),
                                       'type'          => CRM_Utils_Type::T_MONEY,
                                       ),
                                'amount_granted' =>
                                array( 'name'          => 'amount_granted',
                                       'title'         => ts( 'Amount Granted' ),
                                       ),
                                'grant_report_received'=>
                                array( 'name'          => 'grant_report_received',
                                       'title'         => ts( 'Grant Report Received' ), 
                                       'operatorType'  => CRM_Report_Form::OP_SELECT,
                                       'options'       => array( '' => ts('- select -'), 
                                                                 0  => ts('No'), 
                                                                 1  => ts('Yes'),
                                                                 ),
                                       ),
                                ),
                         ),
                  'civicrm_contact'=>
                  array( 'dao'     => 'CRM_Contact_DAO_Contact',
                         'fields'  =>
                         array( 'id' =>
                                array( 'required'      => true,  
                                       'no_display'    => true,
                                       ), 
                                'gender_id' =>  
                                array( 'name'          => 'gender_id',
                                       'title'         => ts( 'By Gender' ),
                                       ),
                                'contact_type' =>
                                array( 'name'          => 'contact_type',
                                       'title'         => ts( 'By Contact Type' ),
                                       ),
                                ),
                         'grouping'=> 'contact-fields',
                         ),
                  'civicrm_address' =>
                  array( 'dao' => 'CRM_Core_DAO_Address',
                         'fields' =>
                         array( 'country_id' => 
                                array( 'name'          => 'country_id',
                                       'title'         => ts( 'By Country' ), 
                                       ),
                                ),
                         'filters' =>             
                         array( 'country_id' => 
                                array( 'title'         => ts( 'Country' ), 
                                       'operatorType'  => CRM_Report_Form::OP_MULTISELECT,
                                       'options'       => CRM_Core_PseudoConstant::country( ),
                                       ), 
                                ),
                         ),
                  'civicrm_world_region' => 
                  array( 'dao'    => 'CRM_Core_DAO_Worldregion',
                         'fields' => 
                         array( 'id'       =>
                                array( 'no_display'    => true,
                                       ),
                                'name'      => 
                                array( 'name'          => 'name',
                                       'title'         => ts( 'By World Region' ),
                                       ),
                                ),
                         'filters' =>
                         array( 'region_id' =>
                                array( 'name'          => 'id', 
                                       'title'         => ts( 'World Region' ),
                                       'operatorType'  => CRM_Report_Form::OP_MULTISELECT,
                                       'options'       => CRM_Core_PseudoConstant::worldRegion( ),
                                       ),
                                ),
                         ),
                   ); 
        parent::__construct( );
    }

    function select( ) 
    {
        $select = array( );
        
        $this->_columnHeaders = array( );
        foreach ( $this->_columns as $tableName => $table ) {
            if ( in_array( $tableName, array( 'civicrm_address', 'civicrm_world_region' ) ) ) {
                $this->_addressField = true;
            }

            if ( array_key_exists('fields', $table) ) { 
                foreach ( $table['fields'] as $fieldName => $field ) {
                    if ( CRM_Utils_Array::value( 'required', $field ) ||
                         CRM_Utils_Array::value( $fieldName, $this->_params['fields'] ) ) {
                        
                        $select[] = "{$field['dbAlias']} as {$tableName}_{$fieldName}";
                        
                        $this->_columnHeaders["{$tableName}_{$fieldName}"]['title'] = $field['title'];
                        $this->_columnHeaders["{$tableName}_{$fieldName}"]['type']  = CRM_Utils_Array::value( 'type', $field );
                    }
                }
            }
        }
        
        $this->_select = "SELECT " . implode( ', ', $select ) . " ";
    }

    function from( ) 
    {
        $this->_from = "
        FROM civicrm_grant {$this->_aliases['civicrm_grant']}
                        LEFT JOIN civicrm_contact {$this->_aliases['civicrm_contact']} 
                    ON ({$this->_aliases['civicrm_grant']}.contact_id  = {$this->_aliases['civicrm_contact']}.id  ) ";
        if ( $this->_addressField ) {
            $this->_from .= "
                  LEFT JOIN civicrm_address {$this->_aliases['civicrm_address']} 
                         ON {$this->_aliases['civicrm_contact']}.id = 
                            {$this->_aliases['civicrm_address']}.contact_id AND 
                            {$this->_aliases['civicrm_address']}.is_primary = 1\n
                  LEFT JOIN civicrm_country country
                         ON {$this->_aliases['civicrm_address']}.country_id = 
                            country.id
                  LEFT JOIN civicrm_worldregion {$this->_aliases['civicrm_world_region']}
                         ON country.region_id = 
                            {$this->_aliases['civicrm_world_region']}.id";
        } 
    }

    function where( ) 
    {
        $granted = array_search( 'Granted', CRM_Grant_PseudoConstant::grantStatus( ) );
        $clauses[] = "status_id = {$granted}";
        foreach ( $this->_columns as $tableName => $table ) {
            if ( array_key_exists('filters', $table) ) { 
                foreach ( $table['filters'] as $fieldName => $field ) {
                    
                    $clause = null;
                    if ( CRM_Utils_Array::value( 'type', $field ) & CRM_Utils_Type::T_DATE ) {
                        $relative = CRM_Utils_Array::value( "{$fieldName}_relative", $this->_params );
                        $from     = CRM_Utils_Array::value( "{$fieldName}_from"    , $this->_params );
                        $to       = CRM_Utils_Array::value( "{$fieldName}_to"      , $this->_params );
                        
                        if ( $relative || $from || $to ) {
                            $clause = $this->dateClause( $field['name'], $relative, $from, $to, $field['type'] );
                        }
                    } else { 
                        $op = CRM_Utils_Array::value( "{$fieldName}_op", $this->_params );
                        if ( $op ) {
                            $clause = 
                                $this->whereClause( $field,
                                                    $op,
                                                    CRM_Utils_Array::value( "{$fieldName}_value", $this->_params ),
                                                    CRM_Utils_Array::value( "{$fieldName}_min", $this->_params ),
                                                    CRM_Utils_Array::value( "{$fieldName}_max", $this->_params ) );
                        }
                    }
                    if ( ! empty( $clause ) ) {
                        $clauses[] = $clause;
                        $this->_where = "WHERE " . implode( ' AND ', $clauses );
                    }
                    
                }
            }
        }
    }

    function groupBy( ) 
    {
        $this->_groupBy = '';
        
        if ( CRM_Utils_Array::value( 'fields', $this->_params ) &&
             is_array( $this->_params['fields'] ) &&
             !empty( $this->_params['fields'] ) ) {
            foreach ( $this->_columns as $tableName => $table ) {
                if ( array_key_exists( 'fields', $table ) ) {
                    foreach ( $table['fields'] as $fieldName => $field ) {
                        if ( CRM_Utils_Array::value( $fieldName, $this->_params['fields'] ) ) {
                            $this->_groupBy[] = $field['dbAlias'];
                        }
                    }
                }
            }
        } 
        if ( !empty( $this->_groupBy ) ) {
            $this->_groupBy = " GROUP BY " . implode( ', ', $this->_groupBy );
        } 
    }

    function alterDisplay( &$rows ) 
    {
        $awardedGrantsAmount = $grantsReceived = $totalAmount = $awardedGrants = $grantReportsReceived = 0;
        CRM_Core_Error::debug( '$rows', $rows );
        exit;
        
        $grantTypes      = CRM_Grant_PseudoConstant::grantType( );
        $countries       = CRM_Core_PseudoConstant::country( );
        $gender          = CRM_Core_PseudoConstant::gender( );

        $query = "
SELECT COUNT(id) as count , SUM(amount_total) as totalAmount 
  FROM civicrm_grant";
        
        $result = CRM_Core_DAO::executeQuery( $query );
        while ( $result->fetch( ) ) {
            $grantsReceived = $result->count;
            $totalAmount    = $result->totalAmount;
        }
        
        $query  .= " WHERE status_id = %1 GROUP BY status_id";
        $granted = array_search( 'Granted', CRM_Grant_PseudoConstant::grantStatus( ) );
        $params  = array( 1 => array( $granted, 'Integer' ) );
                
        $values = CRM_Core_DAO::executeQuery( $query, $params );
        while ( $values->fetch( ) ) {
            $awardedGrants       = $values->count;
            $awardedGrantsAmount = $values->totalAmount;
        }
        
        foreach ( $rows as $key => $values ) {
            if ( CRM_Utils_Array::value( 'civicrm_grant_grant_report_received', $values ) ) {
                $grantReportsReceived ++;
            }
            
            $currency = CRM_Core_DAO::getFieldValue( 'CRM_Core_DAO_Currency', $values['civicrm_grant_currency'], 
                                                     'symbol', 'name' );
                        
            if ( CRM_Utils_Array::value( 'civicrm_grant_grant_type_id', $values ) ) {
                $grantType = CRM_Utils_Array::value( $values['civicrm_grant_grant_type_id'], $grantTypes );
                $grantStatistics['grant_type']['title'] = ts( 'Grant Types' );
                $grantStatistics['grant_type']['value'][$grantType]['currency'][$currency]['value'] += 
                    $values['civicrm_grant_amount_total'];
                $grantStatistics['grant_type']['value'][$grantType]['currency'][$currency]['percentage'] = 
                    round( ( $grantStatistics['grant_type']['value'][$grantType]['currency'][$currency]['value'] / $awardedGrantsAmount ) * 100 );
                $grantStatistics['grant_type']['value'][$grantType]['count'] ++;
                $grantStatistics['grant_type']['value'][$grantType]['percentage'] = 
                    round( ( $grantStatistics['grant_type']['value'][$grantType]['count'] / $awardedGrants ) * 100 );
            }
            
            if ( CRM_Utils_Array::value( 'civicrm_address_country_id', $values ) ) {
                $grantStatistics['country']['title'] = ts( 'Country' );
                if ( $values['civicrm_address_country_id'] ) {
                    $country = CRM_Utils_Array::value( $values['civicrm_address_country_id'], $countries );
                    $grantStatistics['country']['value'][$country]['currency'][$currency]['value'] += 
                        $values['civicrm_grant_amount_total'];
                    $grantStatistics['country']['value'][$country]['currency'][$currency]['percentage'] = 
                        round( ( $grantStatistics['country']['value'][$country]['currency'][$currency]['value'] / $awardedGrantsAmount ) * 100 );
                    $grantStatistics['country']['value'][$country]['count'] ++;
                    $grantStatistics['country']['value'][$country]['percentage'] = 
                        round( ( $grantStatistics['country']['value'][$country]['count'] / $awardedGrants ) * 100 );
                } else {
                    $grantStatistics['country']['value']['Unassigned']['currency'][$currency]['value'] += 
                        $values['civicrm_grant_amount_total'];
                    $grantStatistics['country']['value']['Unassigned']['currency'][$currency]['percentage'] = 
                        round( ( $grantStatistics['country']['value']['Unassigned']['currency'][$currency]['value'] / $awardedGrantsAmount ) * 100 );
                    $grantStatistics['country']['value']['Unassigned']['count'] ++;
                    $grantStatistics['country']['value']['Unassigned']['percentage'] = 
                        round( ( $grantStatistics['country']['value']['Unassigned']['count'] / $awardedGrants ) * 100 );
                }
            }
            
            if ( CRM_Utils_Array::value( 'civicrm_world_region_name', $values ) ) {
                $grantStatistics['world_region']['title'] = ts( 'Regions' );
                if ( $region = $values['civicrm_world_region_name'] ) {
                    $grantStatistics['world_region']['value'][$region]['currency'][$currency]['value'] += 
                        $values['civicrm_grant_amount_total'];
                    $grantStatistics['world_region']['value'][$region]['currency'][$currency]['percentage'] = 
                        round( ( $grantStatistics['world_region']['value'][$region]['currency'][$currency]['value'] / $awardedGrantsAmount ) * 100 );
                    $grantStatistics['world_region']['value'][$region]['count'] ++;
                    $grantStatistics['world_region']['value'][$region]['percentage'] = 
                        round( ( $grantStatistics['world_region']['value'][$region]['count'] / $awardedGrants ) * 100 );
                } else {
                    $grantStatistics['world_region']['value']['Unassigned']['currency'][$currency]['value'] += 
                        $values['civicrm_grant_amount_total'];
                    $grantStatistics['world_region']['value']['Unassigned']['currency'][$currency]['percentage'] = 
                        round( ( $grantStatistics['world_region']['value']['Unassigned']['currency'][$currency]['value'] / $awardedGrantsAmount ) * 100 );
                    $grantStatistics['world_region']['value']['Unassigned']['count'] ++;
                    $grantStatistics['world_region']['value']['Unassigned']['percentage'] = 
                        round( ( $grantStatistics['world_region']['value']['Unassigned']['count'] / $awardedGrants ) * 100 );
                }
            }
            
            if ( CRM_Utils_Array::value( 'civicrm_contact_contact_type', $values ) ) {
                $title = "Total Number of {$values['civicrm_contact_contact_type']}(s)";
                $grantStatistics['contacts']['title'] = ts( 'Contact Type' );
                $grantStatistics['contacts']['value'][$title]['currency'][$currency]['value'] += 
                    $values['civicrm_grant_amount_total'];
                $grantStatistics['contacts']['value'][$title]['currency'][$currency]['percentage'] = 
                    round( ( $grantStatistics['contacts']['value'][$title]['currency'][$currency]['value'] / $awardedGrantsAmount ) * 100 );
                $grantStatistics['contacts']['value'][$title]['count'] ++;
                $grantStatistics['contacts']['value'][$title]['percentage'] = 
                    round( ( $grantStatistics['contacts']['value'][$title]['count'] / $awardedGrants ) * 100 );
            }
            
            if ( $genderId = CRM_Utils_Array::value( 'civicrm_contact_gender_id', $values ) ) {
                $genderLabel = CRM_Utils_Array::value( $genderId, $gender );
                $grantStatistics['gender']['title'] = ts( 'Gender' );
                $grantStatistics['gender']['value'][$genderLabel]['currency'][$currency]['value'] += 
                    $values['civicrm_grant_amount_total'];
                $grantStatistics['gender']['value'][$genderLabel]['currency'][$currency]['percentage'] = 
                    round( ( $grantStatistics['gender']['value'][$genderLabel]['currency'][$currency]['value'] / $awardedGrantsAmount ) * 100 );
                $grantStatistics['gender']['value'][$genderLabel]['count'] ++;
                $grantStatistics['gender']['value'][$genderLabel]['percentage'] = 
                    round( ( $grantStatistics['gender']['value'][$genderLabel]['count'] / $awardedGrants ) * 100 );
            }
        }
        
        $statistics['grants_received']        = array( 'title'  => 'Total Number of grants received',
                                                       'count'  => $grantsReceived,
                                                       'amount' => $totalAmount );
        $statistics['grants_awarded']         = array( 'title'  => 'Total Number of grants awarded',
                                                       'count'  => $awardedGrants,
                                                       'amount' => $awardedGrantsAmount );
        $statistics['grants_report_received'] = array( 'title'  => 'Total Number of grant reports received',
                                                       'count'  => $grantReportsReceived );
        $this->assign( 'totalStatistics', $statistics );
        $this->assign( 'grantStatistics', $grantStatistics );
    }
}