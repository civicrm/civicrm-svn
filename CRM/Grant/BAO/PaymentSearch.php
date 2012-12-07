<?php 

/*
 +--------------------------------------------------------------------+
 | CiviCRM version 4.1                                                |
 +--------------------------------------------------------------------+
 | Copyright CiviCRM LLC (c) 2004-2011                                |
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
 * @copyright CiviCRM LLC (c) 2004-2011
 * $Id$
 *
 */

class CRM_Grant_BAO_PaymentSearch { 
  const MODE_GRANT_PAYMENT   =    1;

  static function &getFields( ) {
    $fields = array( );
    $fields = CRM_Grant_BAO_GrantPayment::exportableFields( );
    return $fields;
  }
  
  function __construct( $params = null, $returnProperties = null, $fields = null,
    $includeContactIds = false, $strict = false, $mode = 1,
    $skipPermission = false, $searchDescendentGroups = true,
    $smartGroupCache = true, $displayRelationshipType = null,
    $operator = 'AND' ) {
    $this->_params =& $params;
    
    if ( $this->_params == null ) {
      $this->_params = array( );
    }
    if ( empty( $returnProperties ) ) {
      $this->_returnProperties = self::defaultReturnProperties( $mode );
    }
    else {
      $this->_returnProperties =& $returnProperties;
    }
    $this->_includeContactIds       = $includeContactIds;
    $this->_strict                  = $strict;
    $this->_mode                    = $mode;
    $this->_skipPermission          = $skipPermission;
    $this->_smartGroupCache         = $smartGroupCache;
    $this->_displayRelationshipType = $displayRelationshipType;
    $this->setOperator( $operator );
    
    if ( $fields ) {
      $this->_fields =& $fields;
      $this->_search = false;
      $this->_skipPermission = true;
    } 
    else {
      $this->_fields = CRM_Grant_BAO_GrantPayment::exportableFields( 'All', false, true, true );
    }
    // basically do all the work once, and then reuse it
    $this->initialize( );
  }
  
  function initialize( ) {
    $this->_select      = array( ); 
    $this->_element     = array( ); 
    $this->_tables      = array( );
    $this->_whereTables = array( );
    $this->_where       = array( ); 
    $this->_qill        = array( );
    $this->_options     = array( );
    $this->_cfIDs       = array( );
    $this->_paramLookup = array( );
    $this->_having 		= array( );
    
    $this->_customQuery = null;
    $this->select(  ); 
    $this->element(  );
    if ( ! empty( $this->_params ) ) {
      $this->buildParamsLookup( );
    }
    $this->_whereClause      = $this->whereClause( );
    
    $this->_tables = array( 'civicrm_payment' => 1);
    $this->_whereTables = array( 'civicrm_payment' => 1);
    
    $this->_whereTables = array( 'civicrm_payment' => 1);
    $this->_fromClause       = "FROM civicrm_payment";
    $this->_simpleFromClause = "FROM civicrm_payment";
  }
  
  function buildParamsLookup( ) {
    foreach ( $this->_params as $value ) {
      if ( !CRM_Utils_Array::value(0, $value) ) {
        continue;
      }
      $cfID = CRM_Core_BAO_CustomField::getKeyID( $value[0] );
      if ( $cfID ) {
        if ( ! array_key_exists( $cfID, $this->_cfIDs ) ) {
          $this->_cfIDs[$cfID] = array( );
        }
        $this->_cfIDs[$cfID][] = $value;
      }
      
      if ( ! array_key_exists( $value[0], $this->_paramLookup ) ) {
        $this->_paramLookup[$value[0]] = array( );
      }
      $this->_paramLookup[$value[0]][] = $value;
    }
  }
  
  function whereClause( ) {
    $this->_where[0] = array( );
    $this->_qill[0]  = array( );
    $config = CRM_Core_Config::singleton( );
    
    if ( ! empty( $this->_params ) ) {
      foreach ( array_keys( $this->_params ) as $id ) {
        if ( !CRM_Utils_Array::value(0, $this->_params[$id]) ) {
          continue;
        }
        $this->whereClauseSingle( $this->_params[$id] );
      }
    }
    
    if ( $this->_customQuery ) {
      // Added following if condition to avoid the wrong value diplay for 'myaccount' / any UF info.
      // Hope it wont affect the other part of civicrm.. if it does please remove it.
      if ( !empty($this->_customQuery->_where) ) {
        $this->_where = CRM_Utils_Array::crmArrayMerge( $this->_where, $this->_customQuery->_where );
      }
      
      $this->_qill  = CRM_Utils_Array::crmArrayMerge( $this->_qill , $this->_customQuery->_qill  );
    }
    
    $clauses    = array( );
    $andClauses = array( );
    
    $validClauses = 0;
    if ( ! empty( $this->_where ) ) {
      foreach ( $this->_where as $grouping => $values ) {
        if ( $grouping > 0 && ! empty( $values ) ) {
          $clauses[$grouping] = ' ( ' . implode( " {$this->_operator} ", $values ) . ' ) ';
          $validClauses++;
        }
      }
      
      if ( ! empty( $this->_where[0] ) ) {
        $andClauses[] = ' ( ' . implode( " AND", $this->_where[0] ) . ' ) ';
      }
      if ( ! empty( $clauses ) ) {
        $andClauses[] = ' ( ' . implode( ' OR ', $clauses ) . ' ) ';
      }
      
      if ( $validClauses > 1 ) {
        $this->_useDistinct = true;
      }
    }
    
    return implode( ' AND ', $andClauses );
  }
  
  function whereClauseSingle( &$values ) {
    switch ( $values[0] ) {
      case 'payment_status_id':
          $this->payment_status( $values );
          return;
      case 'payable_to_name':
          $this->payableName($values);
          return;
      case 'payment_batch_number':
          $this->payment_batch_number($values);
          return;
      case 'payment_number':
          $this->payment_number( $values );
          return;
      case 'amount':
          $this->amount( $values ); 
          return;
      case 'payment_created_date_low':
      case 'payment_created_date_high':
          $this->paymentDates( $values );
          return;
          
    }
  }
  
  function payment_status( &$values ) {
    list( $name, $op, $value, $grouping, $wildcard ) = $values;
    
    if ( ! $op ) {
      $op = '=';
    }
    $n = trim( $value ); 
    if ( strtolower( $n ) == 'odd' ) {
      $this->_where[$grouping][] = " ( civicrm_payment.payment_status_id % 2 = 1 )";
      $this->_qill[$grouping][]  = ts( 'Payment Status Id is odd' );
    }
    else if ( strtolower( $n ) == 'even' ) {
      $this->_where[$grouping][] = " ( civicrm_payment.payment_status_id % 2 = 0 )";
      $this->_qill[$grouping][]  = ts( 'Payment Status Id is even' );
    }
    else {
      $value = strtolower(CRM_Core_DAO::escapeString($n));
      $value = "'$value'";
      
      $this->_where[$grouping][] = " ( civicrm_payment.payment_status_id $op $value )";
      require_once 'CRM/Core/OptionGroup.php';
      $paymentStatus = CRM_Core_OptionGroup::values( 'payment' );
      $n =$paymentStatus[$n];
      $this->_qill[$grouping][]  = ts( 'Payment Status' ) . " $op '$n'";
    }
    
    $this->_tables['civicrm_payment'] = $this->_whereTables['civicrm_payment'] = 1; 
  }
  
  function payableName( &$values ) {
    list( $name, $op, $value, $grouping, $wildcard ) = $values;
    $op = 'LIKE';
    $newName = $name;
    $name    = trim( $value ); 
    
    if ( empty( $name ) ) {
      return;
    }
    
    $config = CRM_Core_Config::singleton( );
    $sub  = array( ); 
    
    //By default, $sub elements should be joined together with OR statements (don't change this variable).
    $subGlue = ' OR ';
    
    $strtolower = function_exists('mb_strtolower') ? 'mb_strtolower' : 'strtolower';
    
    if ( substr( $name, 0 , 1 ) == '"' &&
      substr( $name, -1, 1 ) == '"' ) {
      //If name is encased in double quotes, the value should be taken to be the string in entirety and the 
      $value = substr( $name, 1, -1 );
      $value = $strtolower(CRM_Core_DAO::escapeString($value));
      $wc = ( $newName == 'payable_to_name') ? 'LOWER(civicrm_payment.payable_to_name)' : 'LOWER(civicrm_payment.payable_to_name)';

      $sub[] = " ( $wc = '%$value%' ) ";
    }
    else if ( strpos( $name, ',' ) !== false ) {
      // if we have a comma in the string, search for the entire string 
      $value = $strtolower(CRM_Core_DAO::escapeString($name));
      if ( $wildcard ) {
        if ( $config->includeWildCardInName ) {
          $value = "'%$value%'";
        } 
        else {
          $value = "'$value%'";
        }
        $op    = 'LIKE';
      }
      else {
        $value = "'%$value%'";
      }
      if ( $newName == 'payable_to_name') {
        $wc = ( $op != 'LIKE' ) ? "LOWER(civicrm_payment.payable_to_name)" : "civicrm_payment.payable_to_name";
      }
      $sub[] = " ( $wc $op $value )";
    }
    else {
      //Else, the string should be treated as a series of keywords to be matched with match ANY/ match ALL depending on Civi config settings (see CiviAdmin)
      // The Civi configuration setting can be overridden if the string *starts* with the case insenstive strings 'AND:' or 'OR:'  
      // TO THINK ABOUT: what happens when someone searches for the following "AND: 'a string in quotes'"? - probably nothing - it would make the AND OR variable reduntant because there is only one search string?

      // Check to see if the $subGlue is overridden in the search text
      if (strtolower(substr( $name,  0,  4 ))=='and:'){
        $name = substr( $name,  4 );
        $subGlue = ' AND ';
      }
      if ( strtolower(substr( $name,  0,  3 ))=='or:'){
        $name = substr( $name,  3 );
        $subGlue = ' OR ';
      }
      
      $firstChar = substr( $name,  0,  1 );
      $lastChar  = substr( $name, -1, 1 );
      $quotes    = array( "'", '"' );
      if ( in_array( $firstChar, $quotes ) &&
        in_array( $lastChar , $quotes ) ) {
        $name   = substr( $name,  1 );
        $name   = substr( $name, 0, -1 );
        $pieces = array( $name );
      }
      else {
        $pieces =  explode( ' ', $name );
      }
      foreach ( $pieces as $piece ) { 
        $value = $strtolower( CRM_Core_DAO::escapeString( trim( $piece ) ) );
        if ( strlen( $value ) ) {
          // Added If as a sanitization - without it, when you do an OR search, any string with
          // double spaces (i.e. "  ") or that has a space after the keyword (e.g. "OR: ") will
          // return all contacts because it will include a condition similar to "OR contact
          // name LIKE '%'".  It might be better to replace this with array_filter. 
          $fieldsub = array();
          if ( $wildcard ) {
            if ( $config->includeWildCardInName ) {
              $value = "'%$value%'";
            }
            else {
              $value = "'$value%'";
            }
            $op    = 'LIKE';
          }
          else {
            $value = "'%$value%'";
          }
          if ( $newName == 'payable_to_name') {
            $wc = ( $op != 'LIKE' ) ? "LOWER(civicrm_payment.payable_to_name)" : "civicrm_payment.payable_to_name";
          }
          $fieldsub[] = " ( $wc $op $value )";
          $sub[] = ' ( ' . implode( ' OR ', $fieldsub ) . ' ) ';
          // I seperated the glueing in two.  The first stage should always be OR because we are searching for matches in *ANY* of these fields
        }
      }
    } 
    
    $sub = ' ( ' . implode( $subGlue, $sub ) . ' ) ';
    $this->_where[$grouping][] = $sub;
    $this->_qill[$grouping][]  = ts( 'Payee Name like' ) . " - '$name'";
    $this->_tables['civicrm_payment'] = $this->_whereTables['civicrm_payment'] = 1; 
  }
  
  function paymentDates( $values ) {
    list( $name, $op, $value, $grouping, $wildcard ) = $values;
    
    if ( ($name == 'payment_created_date_low') || ($name == 'payment_created_date_high') ) {
      $this->dateQueryBuilder( $values,
        'civicrm_payment', 'payment_created_date', 'payment_created_date', ts('Date') );
    } 
  }
    
  function &getWhereValues( $name, $grouping )  {
    $result = null;
    foreach ( $this->_params as $id => $values ) {
      if ( $values[0] == $name && $values[3] == $grouping ) {
        return $values;
      }
    }
    
    return $result;
  }
  
  function dateQueryBuilder( &$values,
    $tableName, $fieldName, $dbFieldName, $fieldTitle,
    $appendTimeStamp = true ) {
    list( $name, $op, $value, $grouping, $wildcard ) = $values;
    if ( ! $value ) {
      return;
    }
    
    if ( $name == "{$fieldName}_low" ||
      $name == "{$fieldName}_high" ) {
      if ( isset( $this->_rangeCache[$fieldName] ) ) {
        return;
      }
      $this->_rangeCache[$fieldName] = 1;
      
      $secondOP = $secondPhrase = $secondValue = $secondDate = $secondDateFormat = null;
      
      if ( $name == $fieldName . '_low' ) {
        $firstOP      = '>=';
        $firstPhrase  = 'greater than or equal to';
        $firstDate    = CRM_Utils_Date::processDate( $value );

        $secondValues = $this->getWhereValues( "{$fieldName}_high", $grouping );
        if ( ! empty( $secondValues ) &&
          $secondValues[2] ) {
            $secondOP          = '<=';
            $secondPhrase      = 'less than or equal to';
            $secondValue       = $secondValues[2];
            
            if ( $appendTimeStamp &&
              strlen( $secondValue ) == 10 ) {
              $secondValue .= ' 23:59:59';
            }
            $secondDate = CRM_Utils_Date::processDate( $secondValue );
        }
        
      }
      else if ( $name == $fieldName . '_high' ) {
        $firstOP           = '<=';
        $firstPhrase       = 'less than or equal to';
        
        if ( $appendTimeStamp && strlen( $value ) == 10 ) {
          $value .= ' 23:59:59';
        }
        $firstDate    = CRM_Utils_Date::processDate( $value );
        $secondValues = $this->getWhereValues( "{$fieldName}_low", $grouping );
        if ( ! empty( $secondValues ) &&
             $secondValues[2] ) {
          $secondOP          = '>=';
          $secondPhrase      = 'greater than or equal to';
          $secondValue       = $secondValues[2];
          $secondDate = CRM_Utils_Date::processDate( $secondValue );
        }
      }
      
      if ( ! $appendTimeStamp ) {
        $firstDate = substr(  $date, 0, 8 );
      }
      $firstDateFormat = CRM_Utils_Date::customFormat( $firstDate );
      
      if ( $secondDate ) {
        if ( ! $appendTimeStamp ) {
          $secondDate = substr(  $secondDate, 0, 8 );
        }
        $secondDateFormat = CRM_Utils_Date::customFormat( $secondDate );
      }
      
      $this->_tables[$tableName] = $this->_whereTables[$tableName] = 1;
      if ( $secondDate ) {
        $this->_where[$grouping][] = "
( {$tableName}.{$dbFieldName} $firstOP '$firstDate' ) AND
( {$tableName}.{$dbFieldName} $secondOP '$secondDate' )
";
        $this->_qill[$grouping][]  = 
            "$fieldTitle - $firstPhrase \"$firstDateFormat\" " .
            ts('AND') .
            " $secondPhrase \"$secondDateFormat\"";
      }
      else {
        $this->_where[$grouping][] = "{$tableName}.{$dbFieldName} $firstOP '$firstDate'";
        $this->_qill[$grouping][]  = "$fieldTitle - $firstPhrase \"$firstDateFormat\"";
      }
    }
    
    if ( $name == $fieldName ) {
      $op     = '=';
      $phrase = '=';
      
      $date = CRM_Utils_Date::processDate( $value );
      
      if ( !$appendTimeStamp ) {
        $date = substr(  $date, 0, 8 );
      }

      $format  = CRM_Utils_Date::customFormat( $date );
      
      if ( $date ) {
        $this->_where[$grouping][] = "{$tableName}.{$dbFieldName} $op '$date'";
        if ( $tableName == 'civicrm_log' && $fieldName == 'added_log_date' ) {
          //CRM-6903 --hack to check modified date of first record.
          //as added date means first modified date of object.
          $addedDateQuery = 'select id from civicrm_log group by entity_id order by id';
          $this->_where[$grouping][] = "civicrm_log.id IN ( {$addedDateQuery} )";
        }
      }
      else {
        $this->_where[$grouping][] = "{$tableName}.{$dbFieldName} $op";
      }
      $this->_tables[$tableName] = $this->_whereTables[$tableName] = 1;
      $this->_qill[$grouping][]  = "$fieldTitle - $phrase \"$format\"";
    }
  }
  
  function amount( &$values ) {
    list( $name, $op, $value, $grouping, $wildcard ) = $values;
    
    if ( ! $op ) {
      $op = '=';
    }
    $n = trim( $value ); 
    if ( strtolower( $n ) == 'odd' ) {
      $this->_where[$grouping][] = " ( civicrm_payment.amount % 2 = 1 )";
      $this->_qill[$grouping][]  = ts( 'Payment Amount is odd' );
    }
    else if ( strtolower( $n ) == 'even' ) {
      $this->_where[$grouping][] = " ( civicrm_payment.amount % 2 = 0 )";
      $this->_qill[$grouping][]  = ts( 'Payment Amount is even' );
    }
    else {
      $value = strtolower(CRM_Core_DAO::escapeString($n));
      $value = "'$value'";
      
      $this->_where[$grouping][] = " ( civicrm_payment.amount $op $value )";
      $this->_qill[$grouping][]  = ts( 'Payment Amount' ) . " $op '$n'";
    }
    
    $this->_tables['civicrm_payment'] = $this->_whereTables['civicrm_payment'] = 1; 
    
  }

  function payment_number( &$values ) {
    list( $name, $op, $value, $grouping, $wildcard ) = $values;
         
    if ( ! $op ) {
        $op = '=';
    }
    $n = trim( $value ); 
    if ( strtolower( $n ) == 'odd' ) {
      $this->_where[$grouping][] = " ( civicrm_payment.payment_number % 2 = 1 )";
      $this->_qill[$grouping][]  = ts( 'Payment Batch Number is odd' );
    }
    else if ( strtolower( $n ) == 'even' ) {
      $this->_where[$grouping][] = " ( civicrm_payment.payment_number % 2 = 0 )";
      $this->_qill[$grouping][]  = ts( 'Payment Batch Number is even' );
    }
    else {
      $value = strtolower(CRM_Core_DAO::escapeString($n));
      $value = "'$value'";
      
      $this->_where[$grouping][] = " ( civicrm_payment.payment_number $op $value )";
      $this->_qill[$grouping][]  = ts( 'Payment Number' ) . " $op '$n'";
    }
    $this->_tables['civicrm_payment'] = $this->_whereTables['civicrm_payment'] = 1; 
  }
     
  function payment_batch_number( &$values ) {
    list( $name, $op, $value, $grouping, $wildcard ) = $values;
        
    if ( ! $op ) {
      $op = '=';
    }
    $n = trim( $value ); 
    if ( strtolower( $n ) == 'odd' ) {
      $this->_where[$grouping][] = " ( civicrm_payment.payment_batch_number % 2 = 1 )";
      $this->_qill[$grouping][]  = ts( 'Payment Batch Number is odd' );
    }
    else if ( strtolower( $n ) == 'even' ) {
      $this->_where[$grouping][] = " ( civicrm_payment.payment_batch_number % 2 = 0 )";
      $this->_qill[$grouping][]  = ts( 'Payment Batch Number is even' );
    }
    else {
      $value = strtolower(CRM_Core_DAO::escapeString($n));
      $value = "'$value'";
      
      $this->_where[$grouping][] = " ( civicrm_payment.payment_batch_number $op $value )";
      $this->_qill[$grouping][]  = ts( 'Payment Batch Number' ) . " $op '$n'";
    }
    
    $this->_tables['civicrm_payment'] = $this->_whereTables['civicrm_payment'] = 1; 
  }
  
  /** 
   * build select for CiviGrant 
   * 
   * @return void  
   * @access public  
   */
  function select(  ) {   
    $this->_select['id']                    = 'civicrm_payment.id as id';
    $this->_select['payable_to_name']       = 'civicrm_payment.payable_to_name as payable_to_name';
    $this->_select['payment_batch_number']  = 'civicrm_payment.payment_batch_number as payment_batch_number';
    $this->_select['payment_number']        = 'civicrm_payment.payment_number as payment_number';
    $this->_select['payment_status_id']     = 'civicrm_payment.payment_status_id as payment_status_id';
    $this->_select['payment_created_date']  = 'civicrm_payment.payment_created_date as payment_created_date';
    $this->_select['amount']                = 'civicrm_payment.amount as amount';
    return $this->_select;
  }
  
  function element(  ) {    
    $this->_element['id']                    = 1;
    $this->_element['payable_to_name']       = 1;
    $this->_element['payment_batch_number']  = 1;
    $this->_element['payment_number']        = 1;
    $this->_element['payment_status_id']     = 1;
    $this->_element['payment_created_date']  = 1;
    $this->_element['amount']                = 1;
    return $this->_element;
  }
  
  /** 
   * Given a list of conditions in params generate the required
   * where clause
   * 
   * @return void 
   * @access public 
   */ 
  static function where( &$query ) {
    foreach ( array_keys( $query->_params ) as $id ) {
      if ( substr( $query->_params[$id][0], 0, 6) == 'grant_' ) {
        self::whereClauseSingle( $query->_params[$id], $query );
      }
    }
  }
  
  /**
   * getter for the qill object
   *
   * @return string
   * @access public
   */
  function qill( ) {
    return (isset($this->_qill)) ? $this->_qill : "";
  }
  
  static function defaultReturnProperties( $mode, $includeCustomFields = true ) {
    $properties = null;
    if ( $mode & CRM_Grant_BAO_PaymentSearch::MODE_GRANT_PAYMENT ) {
      $properties = array('id'                  => 1,
        'payable_to_name'     => 1,
        'payment_batch_number'=> 1,
        'payment_number'      => 1,
        'payment_status_id'   => 1,
        'payment_created_date'=> 1,
        'amount'              => 1,
      );
    }
    
    return $properties;
  }
  
  /**
   * add all the elements shared between grant search and advanaced search
   *
   * @access public 
   * @return void
   * @static
   */   
  static function addShowHide( &$showHide ) {
    $showHide->addHide( 'grantForm' );
    $showHide->addShow( 'grantForm_show' );
  }
  
  static function searchAction( &$row, $id ) {
  }
  
  static function tableNames( &$tables ) {
  }
  
  function query( $count = false, $sortByChar = false, $groupContacts = false ) {
    $select = 'SELECT ';
    if ( !$count ) {
      if (  ! empty( $this->_select ) ) {
        $select .= implode( ', ', $this->_select );
      }
    }
    else {
      $select .= "count( DISTINCT ".$this->_distinctComponentClause." ) ";
    }
    $from = '';
    if (  ! empty( $this->_fromClause ) ) {
      $from = $this->_fromClause;
    }
    $where = '';
    if ( ! empty( $this->_whereClause ) ) {
      $where = "WHERE {$this->_whereClause}";
    }
    
    $having = '';
    if ( ! empty( $this->_having ) ) {
      foreach ( $this->_having as $havingsets ) {
        foreach ( $havingsets as $havingset ) {
          $havingvalue[] = $havingset;
        }
      }
      $having = ' HAVING ' . implode( ' AND ', $havingvalue );
    }
    return array( $select, $from, $where, $having );
  }
  
  function searchQuery( $offset = 0, $rowCount = 0, $sort = null, 
                        $count = false, $includeContactIds = false,
                        $sortByChar = false, $groupContacts = false,
                        $returnQuery = false,
                        $additionalWhereClause = null, $sortOrder = null,
                        $additionalFromClause = null, $skipOrderAndLimit = false ) {
        
    list( $select, $from, $where, $having ) = $this->query( $count, $sortByChar, $groupContacts );
    $order = $orderBy = $limit = '';
    if ( !$count )  {
      $config = CRM_Core_Config::singleton( );
      if ( $config->includeOrderByClause ||
        isset( $this->_distinctComponentClause ) ) {
        if ($sort) {
          if ( is_string( $sort ) ) {
            $orderBy = $sort;
          }
          else {
            $orderBy = trim( $sort->orderBy() );
          }
          if ( ! empty( $orderBy ) ) {
            $order = " ORDER BY $orderBy";
            if ( $sortOrder ) {
              $order .= " $sortOrder";
            }
          }
        }
        else if ($sortByChar) { 
          $order = " ORDER BY UPPER(LEFT(civicrm_payment.payable_to_name, 1)) asc";
        }
        else {
          $order = " ORDER BY civicrm_payment.payable_to_name asc, civicrm_payment.id";
        }
      }
      
      if ( $rowCount > 0 && $offset >= 0 ) {
        $limit = " LIMIT $offset, $rowCount ";
        
        if ( isset( $this->_distinctComponentClause ) ) {
          $limitSelect = "SELECT {$this->_distinctComponentClause}";
        }
        else {
          $limitSelect = ( $this->_useDistinct ) ?
              'SELECT DISTINCT(civicrm_payment.id) as id' :
              'SELECT civicrm_payment.id as id';
        }
      }
      $groupBy = 'GROUP BY civicrm_payment.id';
      $query = "$select $from $where $having $groupBy $order $limit";
    }
    
    if ( $count ) {
      $query = "$select $from $where";
      return CRM_Core_DAO::singleValueQuery( $query );
    }
    
    $dao = CRM_Core_DAO::executeQuery( $query );
    if ( $groupContacts ) {
      $ids = array( );
      while ( $dao->fetch( ) ) {
        $ids[] = $dao->id;
      }
      return implode( ',', $ids );
    }
    return $dao;
  }

  static function convertFormValues( &$formValues, $wildcard = 0, $useEquals = false ) {
    $params = array( );
    if ( empty( $formValues ) ) {
      return $params;
    }
    foreach ( $formValues as $id => $values ) {
      $values = CRM_Grant_BAO_PaymentSearch::fixWhereValues( $id, $values, $wildcard, $useEquals );
      if ( ! $values ) {
        continue;
      }
      $params[] = $values;
    }
    return $params;
  }
  
  static function &fixWhereValues( $id, &$values, $wildcard = 0, $useEquals = false ) {
    // skip a few search variables
    static $skipWhere   = null;
    static $arrayValues = null;
    static $likeNames   = null;
    $result = null;
    
    if ( CRM_Utils_System::isNull( $values ) ) {
      return $result;
    }
    
    if ( ! $skipWhere ) {
      $skipWhere   = array( 'task', 'radio_ts', 'uf_group_id',
                            'component_mode', 'qfKey', 'operator',
                            'display_relationship_type' );
    }
    
    if ( in_array( $id, $skipWhere ) ||
         substr( $id, 0, 4 ) == '_qf_' ||
         substr( $id, 0, 7 ) == 'hidden_' ) {
      return $result;
    }
    
    if ( ! $likeNames ) {
      $likeNames = array( 'sort_name', 'email', 'note', 'display_name' );
    }
    
    if ( ! $useEquals && in_array( $id, $likeNames ) ) {
      $result = array( $id, 'LIKE', $values, 0, 1 );
    }
    else if ( is_string( $values ) && strpos( $values, '%' ) !== false ) {
      $result = array( $id, 'LIKE', $values, 0, 0 );
    }
    else if ( $id == 'group' ) {
      if ( is_array( $values ) ) {
        foreach ( $values as $groupIds => $val ) {
          $matches = array( );
          if ( preg_match( '/-(\d+)$/', $groupIds, $matches ) ) {
            if ( strlen( $matches[1] ) > 0 ) {
                $values[$matches[1]] = 1;
                unset( $values[$groupIds] );
            }
          }
        }
      }
      else {
        $groupIds = explode( ',', $values );
        unset( $values );
        foreach( $groupIds as $groupId ) {
          $values[$groupId] = 1;
        }
      }
      
      $result = array( $id, 'IN', $values, 0, 0 );
    }
    else if ( $id == 'contact_tags' || $id == 'tag' ) {
      if (! is_array( $values ) ) {
        $tagIds = explode( ',', $values );
        unset( $values );
        foreach( $tagIds as $tagId ) {
          $values[$tagId] = 1;
        }
      }
      $result = array( $id, 'IN', $values, 0, 0 );
    }
    else {
      $result = array( $id, '=', $values, 0, $wildcard );
    }
    
    return $result;
  }
  
  function setOperator( $operator ) {
    $validOperators = array( 'AND', 'OR' );
    if ( ! in_array( $operator, $validOperators ) ) {
      $operator = 'AND';
    }
    $this->_operator = $operator;
  }

}