<?php 

/**
 *  File for the TestContact class
 *
 *  (PHP 5)
 *  
 *   @copyright Copyright CiviCRM LLC (C) 2009
 *   @license   http://www.fsf.org/licensing/licenses/agpl-3.0.html
 *              GNU Affero General Public License version 3
 *   @version   $Id$
 *   @package   CiviCRM
 *
 *   This file is part of CiviCRM
 *
 *   CiviCRM is free software; you can redistribute it and/or
 *   modify it under the terms of the GNU Affero General Public License
 *   as published by the Free Software Foundation; either version 3 of
 *   the License, or (at your option) any later version.
 *
 *   CiviCRM is distributed in the hope that it will be useful,
 *   but WITHOUT ANY WARRANTY; without even the implied warranty of
 *   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *   GNU Affero General Public License for more details.
 *
 *   You should have received a copy of the GNU Affero General Public
 *   License along with this program.  If not, see
 *   <http://www.gnu.org/licenses/>.
 */

/**
 *  Include class definitions
 */
require_once 'CiviTest/CiviUnitTestCase.php';
require_once 'api/v2/Import.php';

/**
 *  Test APIv2 civicrm_activity_* functions
 *
 *  @package   CiviCRM
 */
class api_v2_ImportTest extends CiviUnitTestCase
{
    /**
     *  Constructor
     *
     *  Initialize configuration
     */
    function __construct( ) {
        parent::__construct( );
    }

    /**
     *  Test setup for every test
     *
     *  Connect to the database, truncate the tables that will be used
     *  and redirect stdin to a temporary file
     */
    public function setUp()
    {
        //  Connect to the database
        parent::setUp();
    }
	
    /**
     *  Test civicrm_import_table_create()
     *
     *  Verify that attempt to create temporary import table from a valid file path
     */
	function testImportTableCreate() {
		
		$params = array(
			'fileName' => 'valid/path/to/file',
			'skipColumnHeader' => TRUE,
		);
		
		$table =& civicrm_import_table_create($params);
		
        $this->assertEquals( 0, $table['is_error'], "In line " . __LINE__ );
        $this->assertEquals( 1, $table['tableName'], "In line " . __LINE__ );
		
		// delete the table
		$dao = new CRM_Core_DAO( );
        $db = $dao->getDatabaseConnection();
		$query = sprintf("DROP TABLE %s", $table['tableName']);
        $result = $db->query($query);
		
	}

    /**
     *  Verify that attempt to create temporary import table with empty params fails
     */
	function testEmptyImportTableParam() {
		$params = array();
		
		$table =& civicrm_import_table_create($params);
		
        $this->assertEquals( $table['is_error'], 1, "In line " . __LINE__ );
	}

    /**
     *  Verify that attempt to create temporary import table with bad file path  fails
     */
	function testBadFileImportTableParam() {
	
		$params = array(
			'fileName' => 'bad/path/to/file',
			'skipColumnHeader' => FALSE,
		);
		
		$table =& civicrm_import_table_create($params);
		
		$this->assertEquals( $table['is_error'], 1, "In line " . __LINE__ );
	}
	
    /**
     *  Verify that attempt to create temporary import table without required fields fails
     */
	function testRequiredParamImportTableParam() {
	
		$params = array(
			'skipColumnHeader' => FALSE,
		);
		
		$table =& civicrm_import_table_create($params);
		
		$this->assertEquals( $table['is_error'], 1, "In line " . __LINE__ );
	}
	
    /**
     *  Test civicrm_import_table_drop()
     *
     *  Verify attempt to drop temporary import table
     */
	function testImportTableDrop() {
	
		$params = array('tableName' => 'tablename');
		
		$table =& civicrm_import_table_drop($params);
		
        $this->assertEquals( 0, $table['is_error'], "In line " . __LINE__ );
        $this->assertEquals( 1, $table['tableName'], "In line " . __LINE__ );
	}

    /**
     *  Verify that attempt to drop temporary import table with empty param
     */	
	function testEmptyParamImportTableDrop() {
	
		$params = array();
		
		$table =& civicrm_import_table_drop($params);

		$this->assertEquals( $table['is_error'], 1, "In line " . __LINE__ );
	}
	
    /**
     *  Test civicrm_import_mapping_create()
     *
     *  Verify attempt to create and save an import mapping
	 *  mapping param has not been determined
     */
	function testImportMappingCreate() {
	
	}
	
    /**
     *  Verify that attempt to create mapping with empty array param
     */	
	function testEmptyParamImportMappingCreate() {
		$params = array();
		
		$mapping =& civicrm_import_mapping_create($params);
        $this->assertEquals( 0, $mapping['is_error'], "In line " . __LINE__ );
        $this->assertEquals( 1, $mapping['mapping_id'], "In line " . __LINE__ );
	}

    /**
     *  Verify that attempt to create an import mapping with just the required fields
	 * (first name, last name, email?)
     */	
	function testRequiredParamImportMappingCreate() {
	
	}

    /**
     *  Verify that attempt to create an import mapping without required field fails?
	 *  is there required field
     */	
	function testRequiredParamImportMappingCreate() {
	
	}

    /**
     *  Test civicrm_import_mapping_delete()
     *
     *  Verify attempt to delete an import mapping
     */
	function testImportMappingDelete() {
		$param = array('mapping_id' => 1);
		
		$mapping =& civicrm_import_mapping_delete($params);
	
        $this->assertEquals( 0, $mapping['is_error'], "In line " . __LINE__ );
		
		
	}	
	
   /**
     *  Verify that attempt to delete a mapping with an empty array parameter
     */
	function testImportMappingDelete() {
		$param = array();
		
		$mapping =& civicrm_import_mapping_delete($params);
	
		$this->assertEquals( $mapping['is_error'], 1, "In line " . __LINE__ );
		
	}	

    /**
     *  Test civicrm_import_mapping_delete()
     *
     *  Verify attempt to import rows with all given parameters
	 *  Should there be limit and offset for importing batch?
     */
/*
 *                      key: tableName (required)
 *                      key: mapping_id (required)
 *                      key: onDuplicate
 *                      key: contactType
 *                      key: contactSubType
 *                      key: dateFormats
 *                      key: groups (array)
 *                      key: tags (array)
 *                      key: geocode (false)
 */
	function testImportRows() {
		$param = array(
			'tableName' => 'tablename',
			'mapping_id' => 1,
			'onDuplicate' => 'skip',
			'contactType' => 'Individual',
			'dateFormats' => 'yyyy-mm-dd',
			'groups' => array(1, 3, 5),
			'tags' => array(1),
			'geocode' => FALSE,
		);
		
		$rows =& civicrm_import_rows($params);
	
        $this->assertEquals( $rows['is_error'], 1, "In line " . __LINE__ );
       
		// rows['errors']
		// rows['warnings']
		// rows['total_count']
		
	}
	
   /**
     *  Verify that attempt to import rows with minimum requirements
     */	
	function testRequiredFieldImportRows() {
		$param = array(
			'tableName' => 'tablename',
			'mapping_id' => 1,
		);
		
		$rows =& civicrm_import_rows($params);
	
        $this->assertEquals( $rows['is_error'], 1, "In line " . __LINE__ );
     
	}
	
   /**
     *  Verify that attempt to import rows with no required fields
     */	
	function testNoRequiredFieldImportRows() {
	
		$param = array(
			'onDuplicate' => 'skip',
			'contactType' => 'Individual',
			'dateFormats' => 'yyyy-mm-dd',
			'groups' => array(1, 3, 5),
			'tags' => array(1),
			'geocode' => FALSE,
		);
		
		$rows =& civicrm_import_rows($params);
	
        $this->assertEquals( $rows['is_error'], 1, "In line " . __LINE__ );
     
	}
	
   /**
     *  Verify that attempt to import rows with an empty param
     */	
	function testEmptyParamImportRows() {
	
		$param = array();
		
		$rows =& civicrm_import_rows($params);
	
        $this->assertEquals( $rows['is_error'], 1, "In line " . __LINE__ );
	}
	
} // class api_v2_ImportTest

?>