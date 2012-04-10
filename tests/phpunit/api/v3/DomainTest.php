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

require_once 'CiviTest/CiviUnitTestCase.php';
require_once 'api/v3/Domain.php';

/**
 * Test class for Domain API - civicrm_domain_*
 *
 *  @package   CiviCRM
 */
class api_v3_DomainTest extends CiviUnitTestCase
{

    /* This test case doesn't require DB reset - apart from 
       where cleanDB() is called. */
    public $DBResetRequired = false;

    protected $_apiversion;
    protected $params;
    
    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     *
     * @access protected
     */
    protected function setUp()
    {
        parent::setUp();
        $this->_apiversion =3;
        $this->params = array( 'name' => 'A-team domain', 
                         'description' => 'domain of chaos',
                         'version'		=>3,
                         'domain_version' => '3.4.1',
                          );

    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     *
     * @access protected
     */
    protected function tearDown()
    {
    }

///////////////// civicrm_domain_get methods

    /**
     * Test civicrm_domain_get. Takes no params.
     * Testing mainly for format.
     */
    public function testGet()
    {
        $this->cleanDB();

        $params = array('version' => 3);
        $result = civicrm_api('domain','get',$params);
        $this->documentMe($params,$result,__FUNCTION__,__FILE__);     

        $this->assertType( 'array', $result, 'In line' . __LINE__ );

        foreach( $result['values'] as $key => $domain ) {
            if ( $key == 'version' ) {
                continue;
            }

            $this->assertEquals( "info@FIXME.ORG", $domain['from_email'], 'In line ' . __LINE__ );
            $this->assertEquals( "FIXME", $domain['from_name'], 'In line' . __LINE__);
            
            // checking other important parts of domain information
            // test will fail if backward incompatible changes happen
            $this->assertArrayHasKey( 'id', $domain, 'In line' . __LINE__ );
            $this->assertArrayHasKey( 'name', $domain, 'In line' . __LINE__ );
            $this->assertArrayHasKey( 'domain_email', $domain, 'In line' . __LINE__ );
            $this->assertArrayHasKey( 'domain_phone', $domain, 'In line' . __LINE__ );
            $this->assertArrayHasKey( 'domain_address', $domain, 'In line' . __LINE__ ); 
        }
    }
    
    public function testGetCurrentDomain()
    {
        $params = array('version' => 3, 'current_domain' => 1);
        $result = civicrm_api('domain','get',$params);
        $this->documentMe($params,$result,__FUNCTION__,__FILE__);     

        $this->assertType( 'array', $result, 'In line' . __LINE__ );

        foreach( $result['values'] as $key => $domain ) {
            if ( $key == 'version' ) {
                continue;
            }

            $this->assertEquals( "info@FIXME.ORG", $domain['from_email'], 'In line ' . __LINE__ );
            $this->assertEquals( "FIXME", $domain['from_name'], 'In line' . __LINE__);
            
            // checking other important parts of domain information
            // test will fail if backward incompatible changes happen
            $this->assertArrayHasKey( 'id', $domain, 'In line' . __LINE__ );
            $this->assertArrayHasKey( 'name', $domain, 'In line' . __LINE__ );
            $this->assertArrayHasKey( 'domain_email', $domain, 'In line' . __LINE__ );
            $this->assertArrayHasKey( 'domain_phone', $domain, 'In line' . __LINE__ );
            $this->assertArrayHasKey( 'domain_address', $domain, 'In line' . __LINE__ ); 
        }
    }
        
///////////////// civicrm_domain_create methods
   /*
    * This test checks for a memory leak observed when doing 2 gets on current domain
    */
    public function testGetCurrentDomainTwice(){ 
     $domain = civicrm_api('domain', 'getvalue', array(
                 'version' => 3,
                 'current_domain' => 1,
                 'return' => 'name'));
     $this->assertEquals('Default Domain Name', $domain,print_r($domain,true) .'in line ' . __LINE__);
     $domain = civicrm_api('domain', 'getvalue', array(
                 'version' => 3,
                 'current_domain' => 1,
                 'return' => 'name'));
     $this->assertEquals('Default Domain Name', $domain,print_r($domain,true). 'in line ' . __LINE__);
     
    }
    
    /**
     * Test civicrm_domain_create.
     */
    public function testCreate()
    {
        $result =& civicrm_api('domain', 'create', $this->params);
        $this->documentMe($this->params,$result,__FUNCTION__,__FILE__);       
        $this->assertType( 'array', $result );
        $this->assertEquals($result['is_error'], 0);
        $this->assertEquals($result['count'], 1); 
        
        $this->assertNotNull($result['id']);
        $this->assertEquals($result['values'][$result['id']]['name'], $this->params['name']) ;
    }    

    /**
     * Test civicrm_domain_create with empty params.
     * Error expected.
     */
    public function testCreateWithEmptyParams()
    {
        $params = array( );
        $result =& civicrm_api('domain', 'create', $params);
        $this->assertEquals( $result['is_error'], 1,
                             "In line " . __LINE__ );
    }
    
    /**
     * Test civicrm_domain_create with wrong parameter type.
     */
    public function testCreateWithWrongParams()
    {
        $params = 1;
        $result =& civicrm_api('domain', 'create', $params);
        $this->assertEquals( $result['is_error'], 1,
                             "In line " . __LINE__ );
    }    
    
}
?>
