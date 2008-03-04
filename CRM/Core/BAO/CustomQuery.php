<?php 

/* 
 +--------------------------------------------------------------------+ 
 | CiviCRM version 2.1                                                | 
 +--------------------------------------------------------------------+ 
 | Copyright CiviCRM LLC (c) 2004-2007                                | 
 +--------------------------------------------------------------------+ 
 | This file is a part of CiviCRM.                                    | 
 |                                                                    | 
 | CiviCRM is free software; you can copy, modify, and distribute it  | 
 | under the terms of the GNU Affero General Public License           | 
 | Version 3, 19 November 2007.                                       | 
 |                                                                    | 
 | CiviCRM is distributed in the hope that it will be useful, but     | 
 | WITHOUT ANY WARRANTY; without even the implied warranty of         | 
 | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.               | 
 | See the GNU Affero General Public License for more details.        | 
 |                                                                    | 
 | You should have received a copy of the GNU Affero General Public   |
 | License along with this program; if not, contact CiviCRM LLC       |
 | at info[AT]civicrm[DOT]org. If you have questions about the        |
 | GNU Affero General Public License or the licensing of CiviCRM,     |
 | see the CiviCRM license FAQ at http://civicrm.org/licensing        |
 +--------------------------------------------------------------------+ 
*/ 
 
/** 
 * 
 * 
 * @package CRM 
 * @copyright CiviCRM LLC (c) 2004-2007 
 * $Id$ 
 * 
 */ 

class CRM_Core_BAO_CustomQuery {

    const PREFIX = 'custom_value_';

    /**
     * the set of custom field ids
     *
     * @var array
     */
    protected $_ids;

    /**
     * the select clause
     *
     * @var array
     */
    public $_select;

    /**
     * the name of the elements that are in the select clause
     * used to extract the values
     *
     * @var array
     */
    public $_element;

    /** 
     * the tables involved in the query
     * 
     * @var array 
     */ 
    public $_tables;
    public $_whereTables;

    /** 
     * the where clause 
     * 
     * @var array 
     */ 
    public $_where;

    /**
     * The english language version of the query
     *  
     * @var array  
     */  
    public $_qill;

    /**
     * The cache to translate the option values into labels
     *   
     * @var array   
     */ 
    public $_options;

    /**
     * The custom fields information
     *    
     * @var array    
     */ 
    protected $_fields;

    /**
     * This stores custom data group types and tables that it extends
     *
     * @var array    
     * @static
     */
    static $extendsMap = array(
                               'Contact'      => 'civicrm_contact',
                               'Individual'   => 'civicrm_contact',
                               'Household'    => 'civicrm_contact',
                               'Organization' => 'civicrm_contact',
                               'Contribution' => 'civicrm_contribution',
                               'Membership'   => 'civicrm_membership',
                               'Participant'  => 'civicrm_participant',
                               'Group'        => 'civicrm_group',
                               'Relationship' => 'civicrm_relationship',
                               'Event'        => 'civicrm_event',
                               'Activity'     => 'civicrm_activity',
                               );

    /**
     * class constructor
     *
     * Takes in a set of custom field ids andsets up the data structures to 
     * generate a query
     *
     * @param  array  $ids     the set of custom field ids
     *
     * @access public
     */
    function __construct( $ids ) {
        $this->_ids    =& $ids;

        $this->_select       = array( ); 
        $this->_element      = array( ); 
        $this->_tables       = array( ); 
        $this->_whereTables  = array( ); 
        $this->_where        = array( );
        $this->_qill         = array( );
        $this->_options      = array( );

        $this->_fields       = array( );

        if ( empty( $this->_ids ) ) {
            return;
        }

        // initialize the field array
        $tmpArray = array_keys( $this->_ids );
        $idString = implode( ',', $tmpArray );
        $query = "
SELECT f.id, f.label, f.data_type,
       f.html_type, f.is_search_range,
       f.option_group_id, f.custom_group_id,
       f.column_name, g.table_name 
  FROM civicrm_custom_field f,
       civicrm_custom_group g
 WHERE f.custom_group_id = g.id
   AND g.is_active = 1
   AND f.is_active = 1 
   AND f.id IN ( $idString )";

        $dao =& CRM_Core_DAO::executeQuery( $query, CRM_Core_DAO::$_nullArray );
        $optionIds = array( );
        while ( $dao->fetch( ) ) {
            // get the group dao to figure which class this custom field extends
            $extends =& CRM_Core_DAO::getFieldValue( 'CRM_Core_DAO_CustomGroup', $dao->custom_group_id, 'extends' );
            $extendsTable = self::$extendsMap[$extends];
            $this->_fields[$dao->id] = array( 'id'              => $dao->id,
                                              'label'           => $dao->label,
                                              'extends'         => $extendsTable,
                                              'data_type'       => $dao->data_type,
                                              'html_type'       => $dao->html_type,
                                              'is_search_range' => $dao->is_search_range,
                                              'column_name'     => $dao->column_name,
                                              'table_name'      => $dao->table_name ) ;

            // store it in the options cache to make things easier
            // during option lookup
            $this->_options[$dao->id] = array( );
            $this->_options[$dao->id]['attributes'] = array( 'label'     => $dao->label,
                                                             'data_type' => $dao->data_type, 
                                                             'html_type' => $dao->html_type );
            $optionIds = array( );
            if ( ( $dao->html_type == 'CheckBox' ||
                   $dao->html_type == 'Radio'    ||
                   $dao->html_type == 'Select'   ||
                   $dao->html_type == 'Multi-Select' ) ) {
                if ( $dao->option_group_id ) {
                    $optionIds[] = $dao->option_group_id;
                } else if ( $dao->data_type != 'Boolean' ) {
                    CRM_Core_Error::fatal( );
                }
            }
            
            // build the cache for custom values with options (label => value)
            if ( ! empty( $optionIds ) ) {
                $optionIdString = implode( ',', $optionIds );
                $query = "
SELECT label, value
  FROM civicrm_option_value
 WHERE option_group_id IN ( $optionIdString )
";

                $option =& CRM_Core_DAO::executeQuery( $query, CRM_Core_DAO::$_nullArray );
                while ( $option->fetch( ) ) {
                    $dataType = $this->_fields[$dao->id]['data_type'];
                    if ( $dataType == 'Int' || $dataType == 'Float' ) {
                        $num = round($option->value, 2);
                        $this->_options[$dao->id]["$num"] = $option->label;
                    } else {
                        $this->_options[$dao->id][$option->value] = $option->label;
                    }
                }
            }
        }
    }
    
    /**
     * generate the select clause and the associated tables
     * for the from clause
     *
     * @param  NULL 
     * @return void
     * @access public
     */   
    function select( ) {
        if ( empty( $this->_fields ) ) {
            return;
        }

        foreach ( $this->_fields as $id => $field ) {
            $name = $field['table_name'];
            $fieldName = 'custom_' . $field['id'];
            $this->_select["{$name}_id"]  = "{$name}.id as {$name}_id";
            $this->_element["{$name}_id"] = 1;
            $this->_select[$fieldName]    = "{$field['table_name']}.{$field['column_name']} as $fieldName";
            $this->_element[$fieldName]   = 1;
            $joinTable = null;
            if ( $field['extends'] == 'civicrm_contact' ) {
                $joinTable = 'contact_a';
            } else if ( $field['extends'] == 'civicrm_contribution' ) {
                $joinTable = 'civicrm_contribution';
            } else if ( $field['extends'] == 'civicrm_participant' ) {
                $joinTable = 'civicrm_participant';
            } else if ( $field['extends'] == 'civicrm_membership' ) {
                $joinTable = 'civicrm_membership';
            }
            if ( $joinTable ) {
                $this->_tables[$name] = "\nLEFT JOIN $name ON $name.entity_id = $joinTable.id";
                if ( $this->_ids[$id] ) {
                    $this->_whereTables[$name] = $this->_tables[$name];
                }
                if ( $joinTable != 'contact_a' ) {
                    $this->_whereTables[$joinTable] = $this->_tables[$joinTable] = 1;
                }
            }
        }

    }

    /**
     * generate the where clause and also the english language
     * equivalent
     * 
     * @param NULL
     * 
     * @return void
     * 
     * @access public
     */   
    function where( ) {
        //CRM_Core_Error::debug( 'fld', $this->_fields );
        //CRM_Core_Error::debug( 'ids', $this->_ids );

        foreach ( $this->_ids as $id => $values ) {

            // Fixed for Isuue CRM 607
            if ( CRM_Utils_Array::value( $id, $this->_fields ) === null ||
                 ! $values ) {
                continue;
            }
           
            foreach ( $values as $tuple ) {
                list( $name, $op, $value, $grouping, $wildcard ) = $tuple;
                
                // fix $value here to escape sql injection attacks
                $field = $this->_fields[$id];
                $qillValue = CRM_Core_BAO_CustomField::getDisplayValue( $value, $id, $this->_options );

                if ( ! is_array( $value ) ) {
                    $value = addslashes(trim($value));
                }

                $fieldName = "{$field['table_name']}.{$field['column_name']}";
                switch ( $field['data_type'] ) {

                case 'String':
                    $sql = "LOWER($fieldName)";
                    // if we are coming in from listings,
                    // for checkboxes the value is already in the right format and is NOT an array 
                    if ( is_array( $value ) ) {
                        require_once 'CRM/Core/BAO/CustomOption.php';

                        //ignoring $op value for checkbox and multi select
                        $sqlValue = array( );
                        if ($field['html_type'] == 'CheckBox') {
                            foreach ( $value as $k => $v ) { 
                                $sqlValue[] = "( $sql like '%" . CRM_Core_BAO_CustomOption::VALUE_SEPERATOR . $k . CRM_Core_BAO_CustomOption::VALUE_SEPERATOR . "%' ) ";
                            }
                            $this->_where[$grouping][] = implode( ' AND ', $sqlValue );
                            $this->_qill[$grouping][]  = "$field[label] $op $qillValue";
                        } else { // for multi select
                            foreach ( $value as $k => $v ) { 
                                $sqlValue[] = "( $sql like '%" . CRM_Core_BAO_CustomOption::VALUE_SEPERATOR . $v . CRM_Core_BAO_CustomOption::VALUE_SEPERATOR . "%' ) ";
                            }
                            $this->_where[$grouping][] = implode( ' AND ', $sqlValue ); 
                            $this->_qill[$grouping][]  = "$field[label] $op $qillValue";
                        }                    
                    } else {
                        if ( $field['is_search_range'] && is_array( $value ) ) {
                            $this->searchRange( $field['id'],
                                                $field['label'],
                                                $field['data_type'],
                                                $fieldName,
                                                $value,
                                                $grouping );
                        } else {
                            $val = CRM_Utils_Type::escape( strtolower(trim($value)), 'String' );
                            if ( $wildcard ) {
                                $val = strtolower( addslashes( $val ) );
                                $val = "%$val%";
                                $op  = 'LIKE';
                            }
                            $this->_where[$grouping][] = "{$sql} {$op} '{$val}'";
                            $this->_qill[$grouping][]  = "$field[label] $op $qillValue";
                        }
                    } 
                    continue;
                
                case 'Int':
                    if ( $field['is_search_range'] && is_array( $value ) ) {
                        $this->searchRange( $field['id'], $field['label'], $field['data_type'], $fieldName, $value, $grouping );
                    } else {
                        $this->_where[$grouping][] = "$fieldName {$op} " . CRM_Utils_Type::escape( $value, 'Integer' );
                        $this->_qill[$grouping][]  = $field['label'] . " $op $value";
                    }
                    continue;
                
                case 'Boolean':
                    $value = (int ) $value;
                    $value = ( $value == 1 ) ? 1 : 0;
                    $this->_where[$grouping][] = "$fieldName {$op} " . CRM_Utils_Type::escape( $value, 'Integer' );
                    $value = $value ? ts('Yes') : ts('No');
                    $this->_qill[$grouping][]  = $field['label'] . " {$op} {$value}";
                    continue;

                case 'Float':
                    if ( $field['is_search_range'] && is_array( $value ) ) {
                        $this->searchRange( $field['id'], $field['label'], $field['data_type'], $fieldName, $value, $grouping );
                    } else {                
                        $this->_where[$grouping][] = "$fieldName {$op} " . CRM_Utils_Type::escape( $value, 'Float' );
                        $this->_qill[$grouping][]  = $field['label'] . " {$op} {$value}";
                    }
                    continue;                    
                
                case 'Money':
                    if ( $field['is_search_range'] && is_array( $value ) ) {
                        foreach( $value as $key => $val ) {
                            require_once "CRM/Utils/Rule.php";
                            $moneyFormat = CRM_Utils_Rule::cleanMoney($value[$key]);
                            $value[$key] = $moneyFormat;
                        }
                        $this->searchRange( $field['id'], $field['label'], $field['data_type'], $fieldName, $value, $grouping );
                    } else { 
                        $moneyFormat = CRM_Utils_Rule::cleanMoney($value);
                        $value       = $moneyFormat;
                        $this->_where[$grouping][] = "$fieldName {$op} " . CRM_Utils_Type::escape( $value, 'Float' );
                        $this->_qill[$grouping][]  = $field['label'] . " {$op} {$value}";
                    }
                    continue;
                
                case 'Memo':
                    $val = CRM_Utils_Type::escape( strtolower(trim($value)), 'String' );
                    $this->_where[$grouping][] = "$fieldName {$op} '{$val}'";
                    $this->_qill[$grouping][] = "$field[label] $op $value";
                    continue;
                
                case 'Date':
                    $fromValue = CRM_Utils_Array::value( 'from', $value );
                    $toValue   = CRM_Utils_Array::value( 'to'  , $value );
                    if ( ! $fromValue && ! $toValue ) {
                        $date = CRM_Utils_Date::format( $value );
                        if ( ! $date ) { 
                            continue; 
                        } 
                    
                        $this->_where[$grouping][] = "$fieldName {$op} {$date}";
                        $date = CRM_Utils_Date::format( $value, '-' ); 
                        $this->_qill[$grouping][]  = $field['label'] . " {$op} " . 
                            CRM_Utils_Date::customFormat( $date ); 
                    } else {
                        $fromDate = CRM_Utils_Date::format( $fromValue );
                        $toDate   = CRM_Utils_Date::format( $toValue   );
                        if ( ! $fromDate && ! $toDate ) {
                            continue;
                        }
                        if ( $fromDate ) {
                            $this->_where[$grouping][] = "$fieldName >= $fromDate";
                            $fromDate = CRM_Utils_Date::format( $fromValue, '-' );
                            $this->_qill[$grouping][]  = $field['label'] . ' >= ' .
                                CRM_Utils_Date::customFormat( $fromDate );
                        }
                        if ( $toDate ) {
                            $this->_where[$grouping][] = "$fieldName <= $toDate";
                            $toDate = CRM_Utils_Date::format( $toValue, '-' );
                            $this->_qill[$grouping][]  = $field['label'] . ' <= ' .
                                CRM_Utils_Date::customFormat( $toDate );
                        }
                    }
                    continue;
                
                case 'StateProvince':
                    $states =& CRM_Core_PseudoConstant::stateProvince();
                    if ( ! is_numeric( $value ) ) {
                        $value  = array_search( $value, $states );
                    }
                    if ( $value ) {
                        $this->_where[$grouping][] = "$fieldName {$op} " . CRM_Utils_Type::escape( $value, 'Int' );
                        $this->_qill[$grouping][]  = $field['label'] . " {$op} {$states[$value]}";
                    }
                    continue;
                
                case 'Country':
                    $countries =& CRM_Core_PseudoConstant::country();
                    if ( ! is_numeric( $value ) ) {
                        $value  = array_search( $value, $countries );
                    }
                    if ( $value ) {
                        $this->_where[$grouping][] = "$fieldName {$op} " . CRM_Utils_Type::escape( $value, 'Int' );
                        $this->_qill[$grouping][]  = $field['label'] . " {$op} {$countries[$value]}";
                    }
                    continue;
                }
            
            }
            //CRM_Core_Error::debug( 'w', $this->_where );
        }
    }

    /**
     * function that does the actual query generation
     * basically ties all the above functions together
     *
     * @param NULL
     * @return  array   array of strings  
     * @access public
     */   
    function query( ) {
        $this->select( );

        $this->where( );
        
        $whereStr = null;
        if ( ! empty( $this->_where ) ) {
            $clauses = array( );
            foreach ( $this->_where as $grouping => $values ) {
                if ( ! empty( $values ) ) {
                    $clauses[] = implode( ' AND ', $values );
                }
            }
            if ( ! empty( $clauses ) ) {
                $whereStr = implode( ' OR ', $clauses );
            }
        }

        return array( implode( ' , '  , $this->_select ),
                      implode( ' '    , $this->_tables ),
                      $whereStr );
    }

    function searchRange( &$id, &$label, $type, $fieldName, &$value, &$grouping ) {
        $qill = array( );

        if ( isset( $value['from'] ) ) {
            $val = CRM_Utils_Type::escape( $value['from'], $type );

            if ( $type == 'String' ) {
                $this->_where[$grouping][] = "$fieldName >= '$val'";
            } else {
                $this->_where[$grouping][] = "$fieldName >= $val";
            }
            $qill[] = ts( 'greater than or equal to \'%1\'', array( 1 => $value['from'] ) );
        }

        if ( isset( $value['to'] ) ) {
            $val = CRM_Utils_Type::escape( $value['to'], $type );
            if ( $type == 'String' ) {
                $this->_where[$grouping][] = "$fieldName <= '$val'";
            } else {
                $this->_where[$grouping][] = "$fieldName <= $val";
            }
            $qill[] = ts( 'less than or equal to \'%1\'', array( 1 => $value['to'] ) );
        }

        if ( ! empty( $qill ) ) { 
            $this->_qill[$grouping][] = $label . ' - ' . implode( ' ' . ts('and') . ' ', $qill );
        }
        
    }

}


