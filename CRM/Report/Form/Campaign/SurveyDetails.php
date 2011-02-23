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

class CRM_Report_Form_Campaign_SurveyDetails extends CRM_Report_Form {
    
    protected $_emailField   = false;
    
    protected $_phoneField   = false;
    
    protected $_summary      = null;
    
    protected $_customGroupExtends = array( 'Contact', 'Individual', 'Household', 'Organization' );

    function __construct( ) {
        
        //filter options for survey activity status.
        $responseStatus = array( );
        require_once 'CRM/Core/PseudoConstant.php';
        $activityStatus = CRM_Core_PseudoConstant::activityStatus( 'name' );
        if ( $statusId = array_search( 'Scheduled', $activityStatus ) ) {
            $responseStatus[$statusId] = ts( 'Reserved' );
        }
        if ( $statusId = array_search( 'Completed', $activityStatus ) ) {
            $responseStatus[$statusId] = ts( 'Interviewed' );
        }
        
        //get all interviewers.
        $allSurveyInterviewers = CRM_Campaign_BAO_Survey::getInterviewers( );
        
        $this->_columns = 
            array( 'civicrm_activity_assignment' => 
                   array( 'dao'       => 'CRM_Activity_DAO_ActivityAssignment',
                          'fields'    =>  array( 'assignee_contact_id' => array( 'title' => ts( 'Interviewer Name' ) ) ),
                          'filters'   =>  array( 'assignee_contact_id' => array( 'name'   => 'assignee_contact_id',
                                                                                 'title'  => ts( 'Interviewer Name' ),
                                                                                 'type'          => CRM_Utils_Type::T_INT,
                                                                                 'operatorType'  => 
                                                                                 CRM_Report_Form::OP_SELECT,
                                                                                 'options' => array( '' => ts( '- any interviewer -' ) ) + $allSurveyInterviewers ) ),
                          'grouping'  => 'survey-interviewer-fields',
                          ),
                   
                   'civicrm_contact'  =>
                   array( 'dao'       => 'CRM_Contact_DAO_Contact',
                          'fields'    =>  array( 'id'           => array( 'title'       => ts( 'Contact ID' ),
                                                                          'no_display'  => true, 
                                                                          'required'    => true),  
                                                 'display_name' => array( 'title'       => ts( 'Respondent Name' ),
                                                                          'required'    => true,
                                                                          'no_repeat'   => true ),
                                                 ),
                          'filters'   =>  array('sort_name'     => array( 'title'       => ts( 'Respondent Name' ),
                                                                          'operator'    => 'like' ) ),
                          'grouping'  => 'contact-fields',
                          'order_bys' => array( 'display_name'  => array( 'title'       => ts( 'Contact Name' ),
                                                                          'required'    => true ) ),
                          ),
                   
                   'civicrm_phone'    => 
                   array( 'dao'       => 'CRM_Core_DAO_Phone',
                          'fields'    => array( 'phone'         =>  array( 'name'       => 'phone',
                                                                           'title'      => ts( 'Phone' ) ) ),
                          'grouping'  => 'location-fields',
                          ),
                   
                   'civicrm_address'  =>
                   array( 'dao'       => 'CRM_Core_DAO_Address',
                          'fields'    => array( 'street_number'     => array( 'name'  => 'street_number',
                                                                              'title' => ts( 'Street Number' ),
                                                                              'type'  => 1 ),
                                                'street_name'       => array( 'name'  => 'street_name',
                                                                              'title' => ts( 'Street Name' ),
                                                                              'type'  => 1 ),
                                                'street_unit'       => array( 'name'  => 'street_unit',
                                                                              'title' => ts( 'Street Unit' ),
                                                                              'type'  => 1 ),
                                                'postal_code'       => array( 'name'  => 'postal_code',
                                                                              'title' => ts( 'Postal Code' ),
                                                                              'type'  => 1 ),
                                                'city'              => array( 'name'  => 'city',
                                                                              'title' => ts( 'City' ),
                                                                              'type'  => 1 ),
                                                'state_province_id' => array( 'name'    => 'state_province_id',
                                                                              'title'   => ts( 'State/Province' ) ),
                                                'country_id'        => array( 'name'    => 'country_id',
                                                                              'title'   => ts( 'Country' ) ) ),
                          'filters'   =>   array( 'street_number'   => array( 'title'   => ts( 'Street Number' ),
                                                                              'type'    => 1,
                                                                              'name'    => 'street_number' ),
                                                  'street_name'     => array( 'title'    => ts( 'Street Name' ),
                                                                              'name'     => 'street_name',
                                                                              'operator' => 'like' ),
                                                  'postal_code'     => array( 'title'   => ts( 'Postal Code' ),
                                                                              'type'    => 1,
                                                                              'name'    => 'postal_code' ),
                                                  'city'            => array( 'title'   => ts( 'City' ),
                                                                              'operator' => 'like',
                                                                              'name'    => 'city' ),
                                                  'state_province_id' =>  array( 'name'  => 'state_province_id',
                                                                                 'title' => ts( 'State/Province' ), 
                                                                                 'operatorType' => 
                                                                                 CRM_Report_Form::OP_MULTISELECT,
                                                                                 'options'       => 
                                                                                 CRM_Core_PseudoConstant::stateProvince()), 
                                                  'country_id'        =>  array( 'name'         => 'country_id',
                                                                                 'title'        => ts( 'Country' ), 
                                                                                 'operatorType' => 
                                                                                 CRM_Report_Form::OP_MULTISELECT,
                                                                                 'options'       => 
                                                                                 CRM_Core_PseudoConstant::country( ) ) ),
                          'group_bys' =>   array( 'street_name'       =>  array( 'title' => ts('Street Name') ),
                                                  'street_number'     =>  array( 'title' => 'Odd / Even Street Number' ) ),
                          
                          'order_bys' =>   array( 'street_name'       => array( 'title'   => ts( 'Street Name' ) ),
                                                  'street_number'     => array( 'title'   => 'Odd / Even Street Number' ) ),
                          
                          'grouping'  => 'location-fields',
                          ),
                   
                   'civicrm_email'    => 
                   array( 'dao'       => 'CRM_Core_DAO_Email',
                          'fields'    =>  array( 'email' => array( 'name' => 'email',
                                                                   'title' => ts( 'Email' ) ) ),
                          'grouping'  => 'location-fields',
                          ),
                   
                   'civicrm_activity' =>
                   array( 'dao'       => 'CRM_Activity_DAO_Activity',
                          'alias'     => 'survey_activity',
                          'fields'    => array( 'survey_id'        => array( 'name'         => 'source_record_id',
                                                                             'title'        => ts( 'Survey' ),
                                                                             'required'    => true,
                                                                             'type'         => CRM_Utils_Type::T_INT,
                                                                             'operatorType' => 
                                                                             CRM_Report_Form::OP_MULTISELECT,
                                                                             'options'      => 
                                                                             CRM_Campaign_BAO_Survey::getSurveys( ) ),
                                                'survey_response'  =>  array( 'name'     => 'survey_response',
                                                                              'title'    => ts( 'Survey Responses' ) ), 
                                                'result'           =>  array( 'name'     => 'result',
                                                                              'required' => true,
                                                                              'title'    => ts('Survey Result') ) ),
                          'filters'   => array( 'survey_id' => array( 'name'         => 'source_record_id',
                                                                      'title'        => ts( 'Survey' ),
                                                                      'type'         => CRM_Utils_Type::T_INT,
                                                                      'operatorType' => CRM_Report_Form::OP_MULTISELECT,
                                                                      'options'      => 
                                                                      CRM_Campaign_BAO_Survey::getSurveys( ) ) ,
                                                'status_id' => array( 'name'          => 'status_id',
                                                                      'title'         => ts( 'Respondent Status' ), 
                                                                      'type'          => CRM_Utils_Type::T_INT,
                                                                      'operatorType'  => CRM_Report_Form::OP_SELECT,
                                                                      'options'       => $responseStatus ) ),
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
            if ( !isset( $table['fields'] ) ) continue; 
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
        $this->_from = " FROM civicrm_contact {$this->_aliases['civicrm_contact']} {$this->_aclFrom} ";
        
        //get the activity table joins.
        $this->_from .= " INNER JOIN civicrm_activity_target {$this->_aliases['civicrm_activity_target']} ON ( {$this->_aliases['civicrm_contact']}.id = civicrm_activity_target.target_contact_id )\n";
        $this->_from .= " INNER JOIN civicrm_activity {$this->_aliases['civicrm_activity']} ON ( {$this->_aliases['civicrm_activity']}.id = civicrm_activity_target.activity_id )\n";
        $this->_from .= " INNER JOIN civicrm_activity_assignment {$this->_aliases['civicrm_activity_assignment']} ON ( {$this->_aliases['civicrm_activity']}.id = {$this->_aliases['civicrm_activity_assignment']}.activity_id )\n";
        
        //get the address table.
        $this->_from .= " LEFT JOIN civicrm_address {$this->_aliases['civicrm_address']} ON {$this->_aliases['civicrm_contact']}.id = {$this->_aliases['civicrm_address']}.contact_id AND {$this->_aliases['civicrm_address']}.is_primary = 1\n";
        
        if ( $this->_emailField ) {
            $this->_from .= "LEFT JOIN civicrm_email {$this->_aliases['civicrm_email']} ON {$this->_aliases['civicrm_contact']}.id = {$this->_aliases['civicrm_email']}.contact_id AND {$this->_aliases['civicrm_email']}.is_primary = 1\n";
        }
        
        if ( $this->_phoneField ) {
            $this->_from .= "LEFT JOIN civicrm_phone {$this->_aliases['civicrm_phone']} ON {$this->_aliases['civicrm_contact']}.id = {$this->_aliases['civicrm_phone']}.contact_id AND {$this->_aliases['civicrm_phone']}.is_primary = 1\n";
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
    
    function groupBy( ) {
        $this->_groupBy = null;
        if ( !CRM_Utils_System::isNull( $this->_params['group_bys'] ) &&
             is_array( $this->_params['group_bys'] ) ) {
            foreach ( $this->_columns as $tableName => $table ) {
                if ( array_key_exists('group_bys', $table) ) {
                    foreach ( $table['group_bys'] as $fieldName => $field ) {
                        if ( !in_array( $fieldName, array( 'street_name', 'street_number' ) ) && 
                             CRM_Utils_Array::value( $fieldName, $this->_params['group_bys'] ) ) {
                            $this->_groupBy[] = $field['dbAlias'];
                        }
                    }
                }
            }
        }
        if ( is_array( $this->_groupBy ) && !empty( $this->_groupBy ) ) {
            $this->_groupBy = ' GROUP BY ' . implode( ', ', $this->_groupBy );
        }
    }
    
    function orderBy( ) {
        $this->_orderBy = null;
        
        //group by as per street name and odd/even street number.
        $groupBys = CRM_Utils_Array::value( 'group_bys', $this->_params, array( ) );
        
        $specialOrderFields = array( 'street_name', 'street_number' );
        foreach ( $specialOrderFields as $fldName ) {
            if ( CRM_Utils_Array::value( $fldName, $groupBys ) ) {
                $field = CRM_Utils_Array::value( $fldName, $this->_columns['civicrm_address']['group_bys'], array( ) );
                if ( $fldName == 'street_number' ) {
                    $this->_orderBy[] = "{$field['dbAlias']}%2 desc";
                } else {
                    $this->_orderBy[] = "{$field['dbAlias']} desc";
                }
            }
        }
        
        foreach ( $this->_columns as $tableName => $table ) {
            if ( array_key_exists('order_bys', $table) ) {
                foreach ( $table['order_bys'] as $fieldName => $field ) {
                    if ( !in_array( $fieldName, $specialOrderFields ) ) {
                        $this->_orderBy[] = $field['dbAlias'];
                    }
                }
            }
        }
        
        //if user does not select any survey, make order by survey.
        if ( CRM_Utils_System::isNull( $this->_params['survey_id_value'] ) ) {
            $this->_orderBy[] = " {$this->_aliases['civicrm_activity']}.source_record_id ";
        }
        
        if ( is_array( $this->_orderBy ) && !empty( $this->_orderBy ) ) {
            $this->_orderBy[] = " {$this->_aliases['civicrm_activity']}.id desc ";
            $this->_orderBy = "ORDER BY " . implode( ', ', $this->_orderBy ) . " ";
        }
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
            if ( array_key_exists( 'civicrm_activity_assignment_assignee_contact_id', $row ) ) {
                $rows[$rowNum]['civicrm_activity_assignment_assignee_contact_id' ] =
                    CRM_Utils_Array::value( $row['civicrm_activity_assignment_assignee_contact_id'], 
                                            CRM_Campaign_BAO_Survey::getInterviewers( ) );
            }
            if ( array_key_exists( 'civicrm_activity_survey_id', $row ) ) {
                $rows[$rowNum]['civicrm_activity_survey_id']  = 
                    CRM_Utils_Array::value( $row['civicrm_activity_survey_id'],
                                            CRM_Campaign_BAO_Survey::getSurveys( ) ); 
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
                //hiding labels for now
                $fieldValueMap[$responseField->option_group_id][$responseField->value] = $responseField->label;
                
                //lets use value, since interviewer uses the "cover sheet" to translate vlaue to label 
                //$fieldValueMap[$responseField->option_group_id][$responseField->value] = $responseField->value;
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
