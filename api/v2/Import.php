<?php

/*
 +--------------------------------------------------------------------+
 | CiviCRM version 3.1                                                |
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
 * File for the CiviCRM APIv2 user framework join functions
 *
 * @package CiviCRM_APIv2
 * @subpackage API_Import
 * 
 * @copyright CiviCRM LLC (c) 2004-2010
 * @version $Id: UFJoin.php 26310 2010-02-18 15:10:24Z shot $
 *
 */

/**
 * Files required for this package
 */

/**
 * takes a fileName (on local file system) and creates
 * an import table from it
 *
 * @param array $params assoc array of name/value pairs
 *                      key: fileName value: path to file on local file system
 *
 * @return array $result assoc array of name/value pairs
 *                      key: tableName value: name of table created in CiviCRM DB
 *
 * @access public
 * 
 */
function civicrm_import_table_create( $params )
{
    if ( ! is_array( $params ) ) {
        return civicrm_create_error("params is not an array");
    }
    
    if ( empty( $params ) ) {
        return civicrm_create_error("params is an empty array");
    }
    
    if ( ! isset( $params['fileName'] ) ) {
        return civicrm_create_error("fileName is not set in input params");
    }

    // call the real function here
    // and get the tableName

    $result = array( 'tableName' => 'civicrm_import_job_Fake',
                     'is_error' => 0 );
    return $result;
}

/**
 * takes a fileName (on local file system) and creates
 * an import table from it
 *
 * @param array $params assoc array of name/value pairs
 *                      key: tableName value: name of table to be dropped from in CiviCRM DB
 *
 * @return array $result assoc array of name/value pairs
 *
 *
 * @access public
 * 
 */
function civicrm_import_table_drop( $params )
{
    if ( ! is_array( $params ) ) {
        return civicrm_create_error("params is not an array");
    }
    
    if ( empty( $params ) ) {
        return civicrm_create_error("params is an empty array");
    }
    
    if ( ! isset( $params['tableName'] ) ) {
        return civicrm_create_error("tableName is not set in input params");
    }

    // call the real function here
    // and drop the tabl

    $result = array( 'is_error' => 0 );
    return $result;
}

/**
 * takes a mapping array and creates an import mapping record in DB
 *
 * @param array $params assoc array of name/value pairs
 *                      key: mapping value: import mapping array
 *
 * @return array $result assoc array of name/value pairs
 *                      key: mapping_id value: 
 *
 * @access public
 * 
 */
function civicrm_import_mapping_create( $params )
{
    if ( ! is_array( $params ) ) {
        return civicrm_create_error("params is not an array");
    }
    
    if ( empty( $params ) ) {
        return civicrm_create_error("params is an empty array");
    }
    
    if ( ! isset( $params['mapping'] ) ||
         ! is_array( $params['mapping'] ) ) {
        return civicrm_create_error("mapping is not set in input params or mapping is not an array");
    }

    // call the real function here
    // and get the mapping id

    $result = array( 'mapping_id' => 1,
                     'is_error' => 0 );
    return $result;
}
/**
 * takes a mapping id and deletes it from the database
 *
 * @param array $params assoc array of name/value pairs
 *                      key: mapping_id 
 *
 * @return array $result assoc array of name/value pairs
 *
 * @access public
 * 
 */
function civicrm_import_mapping_delete( $params )
{
    if ( ! is_array( $params ) ) {
        return civicrm_create_error("params is not an array");
    }
    
    if ( empty( $params ) ) {
        return civicrm_create_error("params is an empty array");
    }
    
    if ( ! isset( $params['mapping_id'] ) ) {
        return civicrm_create_error("mapping_id is not set in input params");
    }

    // call the real function here
    // and drop the mapping

    $result = array( 'mapping_id' => 1,
                     'is_error' => 0 );
    return $result;
}


/**
 * takes a set of input params and imports the rows from the table with a given mapping
 *
 * @param array $params assoc array of name/value pairs
 *                      key: tableName (required)
 *                      key: mapping_id (required)
 *                      key: mode (create)
 *                      key: date_format
 *                      key: dupe_check
 *                      key: groups (array)
 *                      key: tags (array)
 *                      key: geocode (false)
 *                      key: 
 *                      key: 
 *
 * @return array $result assoc array of name/value pairs
 *                      key: errors      value: list of errors
 *                      key: warnings    value: list of warnings
 *                      key: total_count value: total number of contacts created
 *                      key: value:
 *                      key: value:
 *                      key: value:
 *
 * @access public
 * 
 */
function civicrm_import_rows( $params )
{
    if ( ! is_array( $params ) ) {
        return civicrm_create_error("params is not an array");
    }
    
    if ( empty( $params ) ) {
        return civicrm_create_error("params is an empty array");
    }
    
    if ( ! isset( $params['tableName'] ) ) {
        return civicrm_create_error("tableName is not set in input params");
    }

    if ( ! isset( $params['mapping_id'] ) ||
         ! is_numeric( $params['mapping_id'] ) ) {
        return civicrm_create_error("mapping_id is not set in input params or is not a valid number");
    }

    // check that the tableName exists and is populated

    // check that the mapping_id exists and is populated

    // call the real function here
    // and get the various stats

    $result = array( 'is_error' => 0 );
    return $result;
}