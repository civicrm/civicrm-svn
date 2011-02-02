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
                  'civicrm_contact'=>
                  array( 'dao'     => 'CRM_Contact_DAO_Contact',
                         'fields'  =>
                         array( 'display_name' => 
                                array( 'title'         => ts( 'Contact Name' ),
                                       'required'      => true,
                                       'no_repeat'     => true,
                                       ),
                                'gender_id' =>  
                                array( 'name'          => 'gender_id',
                                       'title'         => ts( 'Gender' ),
                                       ),
                                'id' => 
                                array( 'no_display' => true,
                                       'required'  => true, 
                                       ), 
                                ),
                         'grouping'=> 'contact-fields',
                         'filters' =>             
                         array( 'display_name' => 
                                array( 'title'         => ts( 'Contact Name' ),
                                       'operator'      => 'like' ), 
                                'gender_id' => 
                                array( 'title'         => ts( 'Gender' ),
                                       'operatorType'  => CRM_Report_Form::OP_MULTISELECT,
                                       'options'       => CRM_Core_PseudoConstant::gender( ),
                                       ),
                                ),
                         'group_bys' => 
                         array( 'gender_id' => 
                                array( 'title'         => ts( 'Gender' ),
                                       ),
                                ),
                         ),
                  'civicrm_address' =>
                  array( 'dao' => 'CRM_Core_DAO_Address',
                         'fields' =>
                         array( 'country_id' => 
                                array( 'name'          => 'country_id',
                                       'title'         => ts( 'Country' ), 
                                       ),
                                'state_province_id' => 
                                array( 'name'          => 'state_province_id',
                                       'title'         => ts( 'State/Province' ), 
                                       ),
                                ),
                         'filters' =>             
                         array( 'country_id' => 
                                array( 'title'         => ts( 'Country' ), 
                                       'operatorType'  => CRM_Report_Form::OP_MULTISELECT,
                                       'options'       => CRM_Core_PseudoConstant::country( ),
                                       ), 
                                'state_province_id' => 
                                array( 'title'         => ts( 'State/Province' ), 
                                       'operatorType'  => CRM_Report_Form::OP_MULTISELECT,
                                       'options'       => CRM_Core_PseudoConstant::stateProvince( ),
                                       ), 
                                ),
                         'group_bys' => 
                         array( 'country_id' => 
                                array( 'title'         => ts( 'Country' ),
                                       ),
                                'state_province_id' =>
                                array( 'title'         => ts( 'State/Province' ),
                                       ),
                                ),
                         ),
                  'civicrm_grant'  =>
                  array( 'dao'     => 'CRM_Grant_DAO_Grant',
                         'fields'  =>
                         array( 'grant_type_id' =>
                                array( 'name'          => 'grant_type_id' ,
                                       'title'         => ts( 'Grant Type' ),
                                       ),
                                'status_id' =>
                                array( 'name'          => 'status_id' ,
                                       'title'         => ts( 'Grant Status' ),
                                       ),
                                'amount_requested' =>
                                array( 'name'          => 'amount_requested' ,
                                       'title'         => ts( 'Amount Requested' ),
                                       'type'          => CRM_Utils_Type::T_MONEY
                                       ),
                                'amount_granted' =>
                                array( 'name'          => 'amount_granted' ,
                                       'title'         => ts( 'Amount Granted' ),
                                       ),
                                'application_received_date' =>
                                array( 'name'          => 'application_received_date' ,
                                       'title'         => ts( 'Application Received Date' ),
                                       'default'=>true
                                       ),
                                'money_transfer_date' =>
                                array( 'name'          => 'money_transfer_date' ,
                                       'title'         => ts( 'Money Transfer Date' ),
                                       'type'          => CRM_Utils_Type::T_DATE
                                       ),
                                'grant_due_date' =>
                                array( 'name'          => 'grant_due_date' ,
                                       'title'         => ts( 'Grant Due Date' ),
                                       'type'          => CRM_Utils_Type::T_DATE
                                       ),
                                'grant_report_received'=>
                                array( 'name'          => 'grant_report_received' ,
                                       'title'         => ts( 'Grant Report Received' ),  ),
                                ),
                         
                         'filters' =>             
                         array( 'grant_type'     => 
                                array( 'name'          =>'grant_type_id' ,
                                       'title'         => ts( 'Grant Type' ),
                                       'operatorType'  => CRM_Report_Form::OP_MULTISELECT,
                                       'options'       => CRM_Grant_PseudoConstant::grantType( ),
                                      ),
                                'status_id' => 
                                array( 'name'          => 'status_id',
                                       'title'         => ts('Grant Status' ),
                                       'operatorType'  => CRM_Report_Form::OP_MULTISELECT,
                                       'options'       =>  CRM_Grant_PseudoConstant::grantStatus( ),
                                       ),
                                ),
                         'group_bys' =>
                         array( 'grant_type_id' => 
                                array( 'title'         => ts( 'Grant Type' )
                                       ),
                                'status_id' => 
                                array( 'title'         => ts( 'Grant Status' )
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
            if ( $tableName == 'civicrm_address' ) {
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
                            {$this->_aliases['civicrm_address']}.is_primary = 1\n";
        }
    }

    function where( ) 
    {
        $clauses = array( );
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
        $this->_groupBy = "";
        if ( CRM_Utils_Array::value( 'group_bys', $this->_params ) &&
             is_array($this->_params['group_bys']) &&
             !empty($this->_params['group_bys']) ) {
            foreach ( $this->_columns as $tableName => $table ) {
                if ( array_key_exists('group_bys', $table) ) {
                    foreach ( $table['group_bys'] as $fieldName => $field ) {
                        if ( CRM_Utils_Array::value( $fieldName, $this->_params['group_bys'] ) ) {
                            $this->_groupBy[] = $field['dbAlias'];
                        }
                    }
                }
            }
        } 
        if ( !empty( $this->_groupBy ) ) {
            $this->_groupBy = "ORDER BY " . implode( ', ', $this->_groupBy )  . ", {$this->_aliases['civicrm_contact']}.sort_name";
        } 
    }

    function grantStatistics( &$rows ) 
    {
        if ( !$this->_having ) {
            $grantTypes      = CRM_Grant_PseudoConstant::grantType( );
            $grantStatus     = CRM_Grant_PseudoConstant::grantStatus( );
            $granted         = array_search( 'Granted', $grantStatus );
            $countries       = CRM_Core_PseudoConstant::country( );
            $stateProvince   = CRM_Core_PseudoConstant::stateProvince( );
            $gender          = CRM_Core_PseudoConstant::gender( );
            $contactTypes    = CRM_Contact_BAO_ContactType::basicTypePairs( false, 'id' );
            $worldRegion     = CRM_Core_PseudoConstant::worldRegion( );

            $this->_select .= ",
SUM( {$this->_aliases['civicrm_grant']}.amount_total )    as amount_total,
COUNT( {$this->_aliases['civicrm_grant']}.status_id )     as grants_awarded,
{$this->_aliases['civicrm_grant']}.currency               as currency,
{$this->_aliases['civicrm_contact']}.gender_id            as gender_id,
{$this->_aliases['civicrm_contact']}.contact_type         as contact_type,
COUNT({$this->_aliases['civicrm_grant']}.id)              as count,
civicrm_country.region_id                                 as world_region
";
            $this->_from .= "
LEFT JOIN civicrm_country ON ( {$this->_aliases['civicrm_address']}.country_id = civicrm_country.id )";
                  
            $where = "
WHERE {$this->_aliases['civicrm_grant']}.status_id = %1";

            $group = " 
GROUP BY {$this->_aliases['civicrm_grant']}.grant_type_id, 
         {$this->_aliases['civicrm_grant']}.status_id,
         {$this->_aliases['civicrm_contact']}.gender_id,
         {$this->_aliases['civicrm_address']}.country_id";
            
            $sql    = "{$this->_select} {$this->_from} {$where} {$group}";
            $params = array( 1 => array( $granted, 'Integer' ) );
            $dao    = CRM_Core_DAO::executeQuery( $sql, $params );

            $query = "
SELECT COUNT(id) 
  FROM civicrm_grant";
            $grantsReceived = CRM_Core_DAO::singleValueQuery( $query );
            
            $grantStatistics = array( 'grant_type'     => array( 'title' => 'Number of grants awarded in Grant Types' ),
                                      'country'        => array( 'title' => 'Country breakdown <br/> Total number of countries' ),
                                      'gender'         => array( 'title' => 'Gender breakdown' ),
                                      'contacts'       => array( 'title' => 'Contacts breakdown' ),
                                      'world_region'   => array( 'title' => 'Regional breakdown' ),
                                      );
            $awardsGranted = $totalAmount = $grantReportsReceived = $totalCountries = 0;
            
            while ( $dao->fetch( ) ) {
                if ( $dao->civicrm_grant_grant_report_received ) {
                    $grantReportsReceived ++;
                }

                $awardsGranted = $dao->count;
                $totalAmount   = $dao->amount_total;

                $currency      = CRM_Core_DAO::getFieldValue( 'CRM_Core_DAO_Currency', $dao->currency, 'symbol', 'name' );
                                
                $grantType     = CRM_Utils_Array::value( $dao->civicrm_grant_grant_type_id, $grantTypes );
                $grantStatistics['grant_type']['value'][$grantType]['currency'][$currency] += $dao->amount_total;
                $grantStatistics['grant_type']['value'][$grantType]['count'] ++;
                
                if ( $dao->civicrm_address_country_id ) {
                    $country = CRM_Utils_Array::value( $dao->civicrm_address_country_id, $countries );
                    $grantStatistics['country']['value'][$country]['currency'][$currency] += $dao->amount_total;
                    $grantStatistics['country']['value'][$country]['count'] ++;
                    $totalCountries ++;
                } else {
                    $grantStatistics['country']['value']['Unassigned']['currency'][$currency] += $dao->amount_total;
                    $grantStatistics['country']['value']['Unassigned']['count'] ++;
                }

                if ( $dao->world_region ) {
                    $region = CRM_Utils_Array::value( $dao->world_region, $worldRegion );
                    $grantStatistics['world_region']['value'][$region]['currency'][$currency] += $dao->amount_total;
                    $grantStatistics['world_region']['value'][$region]['count'] ++;
                } else {
                    $grantStatistics['world_region']['value']['Unassigned']['currency'][$currency] += $dao->amount_total;
                    $grantStatistics['world_region']['value']['Unassigned']['count'] ++;
                }

                $title = "Total Number of {$dao->contact_type}(s)";
                $grantStatistics['contacts']['value'][$title]['currency'][$currency] += $dao->amount_total;
                $grantStatistics['contacts']['value'][$title]['count'] ++;
                
                $genderLabel = CRM_Utils_Array::value( $dao->gender_id, $gender );
                $grantStatistics['gender']['value'][$genderLabel]['currency'][$currency] += $dao->amount_total;
                $grantStatistics['gender']['value'][$genderLabel]['count'] ++;
            }
            
            $grantStatistics['country']['count']  = '<br/>' . $totalCountries;

            $statistics['grants_received']        = array( 'title' => 'Total Number of grants received',
                                                           'count' => $grantsReceived );
            $statistics['grants_awarded']         = array( 'title' => 'Total Number of grants awarded',
                                                           'count' => $awardsGranted );
            $statistics['grants_awarded_amount']  = array( 'title' => 'Total of awarded',
                                                           'count' => $totalAmount );
            $statistics['grants_report_received'] = array( 'title' => 'Total Number of grant reports received',
                                                           'count' => $grantReportsReceived );
                
            $statistics = array_merge( $statistics, $grantStatistics );
        }
        
        $this->assign( 'grantStatistics', $statistics );
    }

    function alterDisplay( &$rows ) 
    {
        $this->grantStatistics ( $rows );
        // custom code to alter rows
        $entryFound = false;
        foreach ( $rows as $rowNum => $row ) {
            // convert display name to links
            if ( array_key_exists( 'civicrm_contact_display_name', $row ) && 
                 array_key_exists( 'civicrm_contact_id', $row ) ) {
                $url = CRM_Report_Utils_Report::getNextUrl( 'grant/detail', 
                                                            'reset=1&force=1&id_op=eq&id_value=' . 
                                                            $row['civicrm_contact_id'],
                                                            $this->_absoluteUrl, $this->_id );
                $rows[$rowNum]['civicrm_contact_display_name_link'] = $url;
                $rows[$rowNum]['civicrm_contact_display_name_hover'] = 
                    ts("Lists grant(s) for this record.");
                $entryFound = true;
            }

            // handle Grant Type
            if ( array_key_exists( 'civicrm_grant_grant_type_id', $row ) ) {
                if ( $value = $row['civicrm_grant_grant_type_id'] ) {
                    $rows[$rowNum]['civicrm_grant_grant_type_id'] = CRM_Grant_PseudoConstant::grantType( $value );
                }
                $entryFound = true;
            }

            // handle Grant Status
            if ( array_key_exists( 'civicrm_grant_status_id', $row ) ) {
                if ( $value = $row['civicrm_grant_status_id'] ) {
                    $rows[$rowNum]['civicrm_grant_status_id'] = CRM_Grant_PseudoConstant::grantStatus( $value ); 
                }
                $entryFound = true;
            }

            // handle Grant Report Received status
            if ( array_key_exists( 'civicrm_grant_grant_report_received', $row ) ) {
                if ( $value = $row['civicrm_grant_grant_report_received'] ) {
                    if( $value == 1 ) {
                        $value ='Yes';
                    }else {
                        $value ='No';
                    } 
                    $rows[$rowNum]['civicrm_grant_grant_report_received'] = $value; 
                }
                $entryFound = true;
            }

            // handle State Province
            if ( array_key_exists( 'civicrm_address_state_province_id', $row ) ) {
                if ( $value = $row['civicrm_address_state_province_id'] ) {
                    $rows[$rowNum]['civicrm_address_state_province_id'] = 
                        CRM_Core_PseudoConstant::stateProvince( $value, false );

                    $url = 
                        CRM_Report_Utils_Report::getNextUrl( 'grant/detail',
                                                             "reset=1&force=1&state_province_id_op=in&state_province_id_value={$value}", 
                                                             $this->_absoluteUrl, $this->_id );
                    $rows[$rowNum]['civicrm_address_state_province_id_link']  = $url;
                    $rows[$rowNum]['civicrm_address_state_province_id_hover'] = 
                        ts('List all grant(s) for this state.');
                }
                $entryFound = true;
            }

            // handle Country
            if ( array_key_exists( 'civicrm_address_country_id', $row) ) {
                if ( $value = $row['civicrm_address_country_id'] ) {
                    $rows[$rowNum]['civicrm_address_country_id'] = 
                        CRM_Core_PseudoConstant::country( $value, false );
                    $url = CRM_Report_Utils_Report::getNextUrl( 'grant/detail',
                                                                "reset=1&force=1&" . 
                                                                "country_id_op=in&country_id_value={$value}",
                                                                $this->_absoluteUrl, $this->_id );
                    $rows[$rowNum]['civicrm_address_country_id_link'] = $url;
                    $rows[$rowNum]['civicrm_address_country_id_hover'] = 
                        ts('List all grant(s) for this country.');
                }
                
                $entryFound = true;
            }

            // handle Gender
            if ( array_key_exists( 'civicrm_contact_gender_id', $row ) ) {
                if ( $value = $row['civicrm_contact_gender_id'] ) {
                    $gender = CRM_Core_PseudoConstant::gender( );
                    $rows[$rowNum]['civicrm_contact_gender_id'] = CRM_Utils_Array::value( $value, $gender );
                }
                $entryFound = true;
            }

            if ( !$entryFound ) {
                break;
            }
        }
    }
}