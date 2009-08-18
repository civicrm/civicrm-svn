<?php  // vim: set si ai expandtab tabstop=4 shiftwidth=4 softtabstop=4:

/**
 *  File for the CRM_Contact_Form_Search_Custom_GroupTest class
 *
 *  (PHP 5)
 *  
 *   @author Walt Haas <walt@dharmatech.org> (801) 534-1262
 *   @copyright Copyright CiviCRM LLC (C) 2009
 *   @license   http://www.fsf.org/licensing/licenses/agpl-3.0.html
 *              GNU Affero General Public License version 3
 *   @version   $Id$
 *   @package CiviCRM
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
 *  Include parent class definition
 */
require_once 'CiviTest/CiviUnitTestCase.php';

/**
 *  Include class under test
 */
require_once 'CRM/Contact/Form/Search/Custom/Group.php';

/**
 *  Include form definitions
 */
require_once 'CRM/Core/Form.php';

/**
 *  Include DAO to do queries
 */
require_once 'CRM/Core/DAO.php';

/**
 *  Include dataProvider for tests
 */
require_once 'tests/phpunit/CRM/Contact/Form/Search/Custom/GroupTestDataProvider.php';

/**
 *  Test contact custom search functions
 *
 *  @package CiviCRM
 */
class CRM_Contact_Form_Search_Custom_GroupTest extends CiviUnitTestCase
{

    public function dataProvider()
    {
        return new CRM_Contact_Form_Search_Custom_GroupTestDataProvider;
    }

    /**
     *  Test CRM_Contact_Form_Search_Custom_Group::count()
     *  @dataProvider dataProvider
     */
    public function testCount( $fv, $count, $ids, $full )
    {
        //echo "testCount\n";
        $op = new PHPUnit_Extensions_Database_Operation_Insert( );
        $op->execute( $this->_dbconn,
                      new PHPUnit_Extensions_Database_DataSet_FlatXMLDataSet(
                             dirname(__FILE__)
                             . '/dataset.xml') );

        $obj = new CRM_Contact_Form_Search_Custom_Group( $fv );
        $this->assertEquals( $count, $obj->count( ),
                             'In line ' . __LINE__  );

    }

    /**
     *  Test CRM_Contact_Form_Search_Custom_Group::all()
     *  @dataProvider dataProvider
     */
    public function testAll( $fv, $count, $ids, $full )
    {
        // echo "testAll\n";
        $op = new PHPUnit_Extensions_Database_Operation_Insert( );
        $op->execute( $this->_dbconn,
                      new PHPUnit_Extensions_Database_DataSet_FlatXMLDataSet(
                             dirname(__FILE__)
                             . '/dataset.xml') );
        $obj = new CRM_Contact_Form_Search_Custom_Group( $fv );
        $sql = $obj->all( );
        $this->assertTrue( is_string( $sql ), 'In line ' . __LINE__ );
        $dao =& CRM_Core_DAO::executeQuery( $sql );
        while ( $dao->fetch( ) ) {
            $all[] = array( 'contact_id'   => $dao->contact_id,
                            'contact_type' => $dao->contact_type,
                            'sort_name'    => $dao->sort_name );

        }
        asort( $all );
        $this->assertEquals( $full, $all, 'In line ' . __LINE__ );
    }

    /**
     *  Test CRM_Contact_Form_Search_Custom_Group::contactIDs()
     *  @dataProvider dataProvider
     */
    public function testContactIDs( $fv, $count, $ids, $full )
    {
        // echo "testContactIDs\n";
        $op = new PHPUnit_Extensions_Database_Operation_Insert( );
        $op->execute( $this->_dbconn,
                      new PHPUnit_Extensions_Database_DataSet_FlatXMLDataSet(
                             dirname(__FILE__)
                             . '/dataset.xml') );
        $obj = new CRM_Contact_Form_Search_Custom_Group( $fv );
        $sql = $obj->contactIDs( );
        $this->assertTrue( is_string( $sql ), 'In line ' . __LINE__ );
        $dao =& CRM_Core_DAO::executeQuery( $sql );
        $contacts = array( );
        while ( $dao->fetch( ) ) {
            $contacts[] = $dao->contact_id;
        }
        asort( $contacts );
        $this->assertEquals( $ids, $contacts, 'In line ' . __LINE__ );
    }


    /**
     *  Test something
     *  @todo write this test
     */
    public function testBuildForms()
    {
        throw new PHPUnit_Framework_IncompleteTestError("test not implemented");
    }

    /**
     *  Test CRM_Contact_Form_Search_Custom_Group::columns()
     *  It returns an array of translated name => keys
     */
    public function testColumns()
    {
        $formValues = array();
        $obj = new CRM_Contact_Form_Search_Custom_Group( $formValues );
        $columns = $obj->columns( );
        $this->assertTrue( is_array( $columns ), 'In line ' . __LINE__ );
        foreach( $columns as $key => $value ) {
            $this->assertTrue( is_string( $key ), 'In line ' . __LINE__ );
            $this->assertTrue( is_string( $value ), 'In line ' . __LINE__ );
        }
    }

    /**
     *  Test CRM_Contact_Form_Search_Custom_Group::from()
     *  @todo write this test
     */
    public function testFrom()
    {
        throw new PHPUnit_Framework_IncompleteTestError("test not implemented");
    }

    /**
     *  Test CRM_Contact_Form_Search_Custom_Group::summary()
     *  It returns NULL
     */
    public function testSummary()
    {
        $formValues = array();
        $obj = new CRM_Contact_Form_Search_Custom_Group( $formValues );
        $this->assertNull( $obj->summary( ), 'In line ' . __LINE__ );
    }

    /**
     *  Test CRM_Contact_Form_Search_Custom_Group::templateFile()
     *  Returns the path to the file as a string
     */
    public function testTemplateFile()
    {
        $formValues = array();
        $obj = new CRM_Contact_Form_Search_Custom_Group( $formValues );
        $fileName = $obj->templateFile( );
        $this->assertTrue( is_string( $fileName ), 'In line ' . __LINE__ );
        //FIXME: we would need to search the include path to do the following
        //$this->assertTrue( file_exists( $fileName ), 'In line ' . __LINE__ );
    }

    /**
     *  Test CRM_Contact_Form_Search_Custom_Group::where( )
     *  With no arguments it returns '(1)'
     */
    public function testWhereNoArgs()
    {
        $formValues = array( CRM_Core_Form::CB_PREFIX . '17' => true,
                             CRM_Core_Form::CB_PREFIX . '23' => true);
        $obj = new CRM_Contact_Form_Search_Custom_Group( $formValues );
        $this->assertEquals( ' (1) ', $obj->where( ), 'In line ' . __LINE__ );
    }

    /**
     *  Test CRM_Contact_Form_Search_Custom_Group::where( )
     *  With false argument it returns '(1)'
     */
    public function testWhereFalse()
    {
        $formValues = array( CRM_Core_Form::CB_PREFIX . '17' => true,
                             CRM_Core_Form::CB_PREFIX . '23' => true);
        $obj = new CRM_Contact_Form_Search_Custom_Group( $formValues );
        $this->assertEquals( ' (1) ', $obj->where( false ),
                             'In line ' . __LINE__ );
    }

    /**
     *  Test CRM_Contact_Form_Search_Custom_Group::where( )
     *  With true argument it returns list of contact IDs
     */
    public function testWhereTrue()
    {
        $formValues = array( CRM_Core_Form::CB_PREFIX . '17' => true,
                             CRM_Core_Form::CB_PREFIX . '23' => true);
        $obj = new CRM_Contact_Form_Search_Custom_Group( $formValues );
        $this->assertEquals( 'contact_a.id IN ( 17, 23 )', $obj->where( true ),
                             'In line ' . __LINE__ );
    }

} // class CRM_Contact_Form_Search_Custom_GroupTest

// -- set Emacs parameters --
// Local variables:
// mode: php;
// tab-width: 4
// c-basic-offset: 4
// c-hanging-comment-ender-p: nil
// indent-tabs-mode: nil
// End: