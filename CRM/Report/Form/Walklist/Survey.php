<?php

/*
 +--------------------------------------------------------------------+
 | CiviCRM version 3.4                                                |
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
require_once 'CRM/Campaign/BAO/Survey.php';

class CRM_Report_Form_Walklist_Survey extends CRM_Report_Form {
    protected $_addressField = false;
    
    protected $_emailField   = false;
    
    protected $_phoneField   = false;
    
    protected $_activityField = false;
    
    protected $_summary      = null;
    
    protected $_customGroupExtends = array( 'Contact', 'Individual', 'Household', 'Organization' );

    function __construct( ) {
        $this->_columns = 
            array( 'civicrm_contact'  =>
                   array( 'dao'       => 'CRM_Contact_DAO_Contact',
                          'fields'    =>  array( 'id'           => array( 'title'       => ts( 'Contact ID' ),
                                                                          'no_display'  => true, 
                                                                          'required'    => true),  
                                                 'display_name' => array( 'title'       => ts( 'Contact Name' ),
                                                                          'required'    => true,
                                                                          'no_repeat'   => true ),
                                                 ),
                          'filters'   =>  array('sort_name'     => array( 'title'       => ts( 'Contact Name' ),
                                                                          'operator'    => 'like' ) ),
                          'grouping'  => 'contact-fields',
                          'order_bys' => array( 'display_name'  => array( 'title'       => ts( 'Contact Name' ),
                                                                          'required'    => true ) ),
                          ),
                   
                   'civicrm_address'  =>
                   array( 'dao'       => 'CRM_Core_DAO_Address',
                          'fields'    => array( 'street_number'     => array( 'title' => ts( 'Street Number' ),
                                                                              'type'  => 1 ),
                                                'street_address'    => null,
                                                'city'              => null,
                                                'postal_code'       => null,
                                                'state_province_id' => array( 'title'   => ts( 'State/Province' ), 
                                                                              'default' => true ),
                                                'country_id'        => array( 'title'   => ts( 'Country' ), ), ),
                          'filters'   =>   array( 'street_number'   => array( 'title'   => ts( 'Street Number' ),
                                                                              'type'    => 1,
                                                                              'name'    => 'street_number' ),
                                                  'street_address'  => null,
                                                  'city'            => null,
                                                  ),
                          'grouping'  => 'location-fields',
                          ),
                   
                   'civicrm_email'    => 
                   array( 'dao'       => 'CRM_Core_DAO_Email',
                          'fields'    =>  array( 'email' => array( 'default' => true ) ),
                          'grouping'  => 'location-fields',
                          ),
                   
                   'civicrm_phone'    => 
                   array( 'dao'       => 'CRM_Core_DAO_Phone',
                          'fields'    => array( 'phone' => null ),
                          'grouping'  => 'location-fields',
                          ),
                   
                   'civicrm_activity' =>
                   array( 'dao'       => 'CRM_Activity_DAO_Activity',
                          'alias'     => 'survey_activity',
                          'fields'    => array( 'result'           =>  array( 'name'    => 'result',
                                                                              'title'   => ts('Status'),
                                                                              'default' => true ),
                                                'survey_response'  =>  array( 'title'   => ts( 'Response Codes' ),
                                                                              'default' => true ) ),
                          
                          'filters'   => array( 'survey_id' => array( 'name'         => 'source_record_id',
                                                                      'title'        => ts( 'Survey' ),
                                                                      'type'         => CRM_Utils_Type::T_INT,
                                                                      'operatorType' => CRM_Report_Form::OP_MULTISELECT,
                                                                      'options'      => 
                                                                      CRM_Campaign_BAO_Survey::getSurveys( ) ) ),
                          'grouping' => 'survey-activity-fields',
                          ),
                   
                   );
        
        parent::__construct( );
    }
    
    function preProcess( ) {
        parent::preProcess( );
    }
    
    function select( ) {
        $select = array( );
        
        //add the survey response fields.
        $this->_addSurveyResponseColumns( );
        
        $this->_columnHeaders = array( );
        foreach ( $this->_columns as $tableName => $table ) {
            foreach ( $table['fields'] as $fieldName => $field ) {
                if ( CRM_Utils_Array::value( 'required', $field ) ||
                     CRM_Utils_Array::value( $fieldName, $this->_params['fields'] ) ) {
                    
                    $fieldsName = CRM_Utils_Array::value( 1, explode( '_', $tableName ) );
                    if ( $fieldsName ) $this->{"_$fieldsName".'Field'} = true;
                    
                    //need to pickup custom data/survey response fields.
                    if ( $fieldName == 'survey_response' ) continue;
                    
                    $select[] = "{$field['dbAlias']} as {$tableName}_{$fieldName}";
                    $this->_columnHeaders["{$tableName}_{$fieldName}"]['title'] = $field['title'];
                    $this->_columnHeaders["{$tableName}_{$fieldName}"]['type']  = $field['type'];
                }
            }
        }
        
        $this->_select = "SELECT " . implode( ",\n", $select ) . " ";
    }
    
    function from( ) {
        $this->_from = null;
        
        $this->_from = "
FROM       civicrm_contact {$this->_aliases['civicrm_contact']} {$this->_aclFrom}
";
        if ( $this->_addressField ) {
            $this->_from .= "LEFT JOIN civicrm_address {$this->_aliases['civicrm_address']} ON {$this->_aliases['civicrm_contact']}.id = {$this->_aliases['civicrm_address']}.contact_id AND {$this->_aliases['civicrm_address']}.is_primary = 1\n";
        }
        
        if ( $this->_emailField ) {
            $this->_from .= "LEFT JOIN civicrm_email {$this->_aliases['civicrm_email']} ON {$this->_aliases['civicrm_contact']}.id = {$this->_aliases['civicrm_email']}.contact_id AND {$this->_aliases['civicrm_email']}.is_primary = 1\n";
        }
        
        if ( $this->_phoneField ) {
            $this->_from .= "LEFT JOIN civicrm_phone {$this->_aliases['civicrm_phone']} ON {$this->_aliases['civicrm_contact']}.id = {$this->_aliases['civicrm_phone']}.contact_id AND {$this->_aliases['civicrm_phone']}.is_primary = 1\n";
        }
        
        //get the survey clause in.
        if ( $this->_activityField ) {
            $this->_from .= "INNER JOIN civicrm_activity_target civicrm_activity_target ON ( {$this->_aliases['civicrm_contact']}.id = civicrm_activity_target.target_contact_id )\n";
            $this->_from .= "INNER JOIN civicrm_activity {$this->_aliases['civicrm_activity']} ON ( {$this->_aliases['civicrm_activity']}.id = civicrm_activity_target.activity_id )\n";
        }
    }
    
    function where( ) {
        $clauses = array( );
        foreach ( $this->_columns as $tableName => $table ) {
            if ( array_key_exists('filters', $table) ) {
                foreach ( $table['filters'] as $fieldName => $field ) {
                    $clause = null;
                    
                    if ( $field['type'] & CRM_Utils_Type::T_DATE ) {
                        $relative = CRM_Utils_Array::value( "{$fieldName}_relative", $this->_params );
                        $from     = CRM_Utils_Array::value( "{$fieldName}_from"    , $this->_params );
                        $to       = CRM_Utils_Array::value( "{$fieldName}_to"      , $this->_params );
                        
                        $clause = $this->dateClause( $field['name'], $relative, $from, $to, $field['type'] );
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
                    }
                }
            }
        }
        
        //apply survey activity types filter.
        $surveyActivityTypes = CRM_Campaign_BAO_Survey::getSurveyActivityType( );
        if ( !empty( $surveyActivityTypes ) ) {
            $clauses[] = "( {$this->_aliases['civicrm_activity']}.activity_type_id IN ( ". 
                implode( ' , ', array_keys(  $surveyActivityTypes ) ) . ' ) )';
        }
        
        if ( empty( $clauses ) ) {
            $this->_where = "WHERE ( 1 ) ";
        } else {
            $this->_where = "WHERE " . implode( ' AND ', $clauses );
        }
        
        if ( $this->_aclWhere ) {
            $this->_where .= " AND {$this->_aclWhere} ";
        }
    }
    

    function orderBy( ) {
        $this->_orderBy = "";
        foreach ( $this->_columns as $tableName => $table ) {
            if ( array_key_exists('order_bys', $table) ) {
                foreach ( $table['order_bys'] as $fieldName => $field ) {
                    $this->_orderBy[] = $field['dbAlias'];
                }
            }
        }
        $this->_orderBy = "ORDER BY " . implode( ', ', $this->_orderBy ) . " ";
    }
    
    function postProcess( ) {
        // get the acl clauses built before we assemble the query
        $this->buildACLClause( $this->_aliases['civicrm_contact'] );
        parent::postProcess();
    }
    
    function alterDisplay( &$rows ) {
        // custom code to alter rows
        $entryFound = false;
        foreach ( $rows as $rowNum => $row ) { 
            // handle state province
            if ( array_key_exists('civicrm_address_state_province_id', $row) ) {
                if ( $value = $row['civicrm_address_state_province_id'] ) {
                    $rows[$rowNum]['civicrm_address_state_province_id'] = 
                        CRM_Core_PseudoConstant::stateProvince( $value );
                }
                $entryFound = true;
            }
            
            // handle country
            if ( array_key_exists('civicrm_address_country_id', $row) ) {
                if ( $value = $row['civicrm_address_country_id'] ) {
                    $rows[$rowNum]['civicrm_address_country_id'] = 
                        CRM_Core_PseudoConstant::country( $value );
                }
                $entryFound = true;
            }
            
            // convert display name to links
            if ( array_key_exists('civicrm_contact_display_name', $row) && 
                 array_key_exists('civicrm_contact_id', $row) ) {
                $url = CRM_Report_Utils_Report::getNextUrl( 'contact/detail', 
                                                            'reset=1&force=1&id_op=eq&id_value=' . 
                                                            $row['civicrm_contact_id'],
                                                            $this->_absoluteUrl, $this->_id );
                $rows[$rowNum]['civicrm_contact_display_name_link' ] = $url;
                $entryFound = true;
            }
            
            // skip looking further in rows, if first row itself doesn't 
            // have the column we need
            if ( !$entryFound ) {
                break;
            }
        }
        
        //format the survey response data.
        $this->_formatSurveyResponseData( $rows );
    }
    
    private function _formatSurveyResponseData( &$rows ) 
    {
        $surveyIds = CRM_Utils_Array::value( 'survey_id_value', $this->_params );
        if ( CRM_Utils_System::isNull( $surveyIds ) ||
             !CRM_Utils_Array::value( 'survey_response',  $this->_params['fields'] ) ) {
            return;
        }
        
        $surveyResponseFields   = array( );
        $surveyResponseFieldIds = array( );
        foreach ( $this->_columns as $tableName => $values ) {
            if ( !is_array( $values['fields'] ) ) continue;
            foreach ( $values['fields'] as $name => $field ) {
                if ( CRM_Utils_Array::value( 'isSurveyResponseField', $field ) ) {
                    $fldId = substr( $name, 7 );
                    $surveyResponseFields[$name]    = "{$tableName}_{$name}";
                    $surveyResponseFieldIds[$fldId] = $fldId;
                }
            }
        }
        
        $hasResponseData = false;
        foreach ( $surveyResponseFields as $fldName ) {
            foreach ( $rows as $row ) {
                if ( CRM_Utils_Array::value( $fldName, $row ) ) {
                    $hasResponseData = true;
                    break;
                }
            }
        }
        if ( !$hasResponseData ) return; 
        
        //start response data fomatting.
        $query = ' 
    SELECT  cf.id,
            cf.data_type,
            cf.html_type,
            cg.table_name, 
            cf.column_name,
            ov.value, ov.label,
            cf.option_group_id
      FROM  civicrm_custom_field cf      
INNER JOIN  civicrm_custom_group cg ON ( cg.id = cf.custom_group_id )        
 LEFT JOIN  civicrm_option_value ov ON ( cf.option_group_id = ov.option_group_id )
     WHERE  cf.id IN ( '. implode( ' , ', $surveyResponseFieldIds ) . ' )';
        
        $responseFields = array( );
        $fieldValueMap  = array( ); 
        $properties = array(  'id', 
                              'data_type', 
                              'html_type', 
                              'column_name', 
                              'option_group_id', );
        
        $responseField = CRM_Core_DAO::executeQuery( $query );
        while ( $responseField->fetch( ) ) {
            $reponseFldName = $responseField->table_name . '_custom_'. $responseField->id;
            foreach( $properties as $prop ) {
                $responseFields[$reponseFldName][$prop] = $responseField->$prop;
            }
            if ( $responseField->option_group_id ) {
                $fieldValueMap[$responseField->option_group_id][$responseField->value] = $responseField->label;
            }
        }
        $responseField->free( );
        
        //actual data formatting.
        $hasData = false;
        foreach ( $rows as &$row ) {
            if ( !is_array( $row ) ) {
                continue; 
            }
            
            foreach ( $row as $name => &$value ) {
                if ( !array_key_exists( $name, $responseFields ) ) {
                    continue;
                }
                
                $hasData = true;
                $value = $this->formatCustomValues( $value, 
                                                    $responseFields[$name],
                                                    $fieldValueMap );
            }
            
            if ( !$hasData ) break;  
        }
    }
    
    private function _addSurveyResponseColumns( ) 
    {
        $surveyIds = CRM_Utils_Array::value( 'survey_id_value', $this->_params );
        if ( CRM_Utils_System::isNull( $surveyIds ) ||
             !CRM_Utils_Array::value( 'survey_response',  $this->_params['fields'] ) ) {
            return;
        }
        
        require_once 'CRM/Campaign/BAO/Survey.php';
        require_once 'CRM/Core/BAO/CustomField.php';
        $responseFields = array( );
        foreach ( $surveyIds as $surveyId ) {
            $responseFields += CRM_Campaign_BAO_survey::getSurveyResponseFields( $surveyId );
        }
        
        $responseFieldIds = array( );
        foreach ( array_keys( $responseFields ) as $key ) {
            $cfId = CRM_Core_BAO_CustomField::getKeyID( $key );
            if ( $cfId ) $responseFieldIds[$cfId] = $cfId;
        }
        
        if ( empty( $responseFieldIds ) ) return;
        
        $query ='
     SELECT  cg.extends, 
             cf.data_type, 
             cf.html_type,  
             cg.table_name,       
             cf.column_name,
             cf.time_format,
             cf.id as cfId,
             cf.option_group_id
       FROM  civicrm_custom_group cg 
INNER  JOIN  civicrm_custom_field cf ON ( cg.id = cf.custom_group_id )
      WHERE  cf.id IN ( '. implode( ' , ',  $responseFieldIds ).' )';   
        $response = CRM_Core_DAO::executeQuery( $query );
        while ( $response->fetch( ) ) {
            $resTable  = $response->table_name;
            $fieldName = "custom_{$response->cfId}";
            
            //need to check does these custom data already included.

            if ( !array_key_exists( $resTable, $this->_columns ) ) {
                $this->_columns[$resTable]['dao']     = 'CRM_Contact_DAO_Contact'; 
                $this->_columns[$resTable]['extends'] = $response->extends;
                $this->_columns[$resTable]['alias']   = $tableAlias;
            }
            if ( !CRM_Utils_Array::value( 'alias', $this->_columns[$resTable] ) ) {
                $this->_columns[$resTable]['alias'] = "{$resTable}_survey_response"; 
            }
            if ( !is_array( $this->_columns[$resTable]['fields'] ) ) {
                $this->_columns[$resTable]['fields'] = array( );
            }
            if ( array_key_exists( $fieldName, $this->_columns[$resTable]['fields'] ) ) {
                $this->_columns[$resTable]['fields'][$fieldName]['required'] = true;
                continue;
            }
            
            $fldType = 'CRM_Utils_Type::T_STRING';
            if ( $response->time_format ) $fldType = CRM_Utils_Type::T_TIMESTAMP;
            $field = array( 'name'     => $response->column_name,
                            'type'     => $fldType,
                            'title'    => $responseFields[$fieldName]['title'],
                            'dataType' => $response->data_type,
                            'htmlType' => $response->html_type,
                            'required' => true,
                            'alias'    => $this->_columns[$resTable]['alias'],
                            'dbAlias'  => $this->_columns[$resTable]['alias'].'.'.$response->column_name,
                            'isSurveyResponseField' => true );
            
            $this->_columns[$resTable]['fields'][$fieldName] = $field;
            $this->_aliases[$resTable] = $this->_columns[$resTable]['alias'];
        }
    }
    
}