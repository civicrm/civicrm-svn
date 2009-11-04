<?php
/*
 +--------------------------------------------------------------------+
 | CiviCRM version 3.1                                                |
 +--------------------------------------------------------------------+
 | Copyright CiviCRM LLC (c) 2004-2009                                |
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


require_once 'api/v2/Contribute.php';
require_once 'CiviTest/CiviUnitTestCase.php';

class api_v2_ContributionTest extends CiviUnitTestCase 
{
    /**
     * Assume empty database with just civicrm_data
     */
    protected $_individualId;    
    protected $_contribution;
    protected $_contributionTypeId;
    
    function setUp() 
    {
        parent::setUp();

        $this->_contributionTypeId = 1;
        $this->_individualId = $this->individualCreate();
    }
    
    function tearDown() 
    {
    }

///////////////// civicrm_contribution_get methods

    function testGetEmptyParamsContribution()
    {

        $params = array();
        $contribution =& civicrm_contribution_get($params);

        $this->assertEquals( $contribution['is_error'], 1 );
        $this->assertEquals( $contribution['error_message'], 'No input parameters present' );
    }
    
    
    function testGetParamsNotArrayContribution()
    {
        $params = 'contact_id= 1';                            
        $contribution =& civicrm_contribution_get($params);
        $this->assertEquals( $contribution['is_error'], 1 );
        $this->assertEquals( $contribution['error_message'], 'Input parameters is not an array' );
    }
 

    function testGetContribution()
    {        
        $p = array(
                        'contact_id'             => $this->_individualId,
                        'receive_date'           => date('Ymd'),
                        'total_amount'           => 100.00,
                        'contribution_type_id'   => $this->_contributionTypeId,
                        'non_deductible_amount'  => 10.00,
                        'fee_amount'             => 51.00,
                        'net_amount'             => 91.00,
                        'trxn_id'                => 23456,
                        'invoice_id'             => 78910,
                        'source'                 => 'SSF',
                        'contribution_status_id' => 1
                        );
        
        $this->_contribution =& civicrm_contribution_add($p);
        $params = array('contribution_id'=>$this->_contribution['id']);        
        $contribution =& civicrm_contribution_get($params);

        $this->assertEquals($contribution['contact_id'],$this->_individualId); 
        $this->assertEquals($contribution['contribution_type_id'],$this->_contributionTypeId);        
        $this->assertEquals($contribution['total_amount'],100.00);
        $this->assertEquals($contribution['non_deductible_amount'],10.00);
        $this->assertEquals($contribution['fee_amount'],51.00);
        $this->assertEquals($contribution['net_amount'],91.00);
        $this->assertEquals($contribution['trxn_id'],23456);
        $this->assertEquals($contribution['invoice_id'],78910);
        $this->assertEquals($contribution['contribution_source'],'SSF');
        $this->assertEquals($contribution['contribution_status_id'], 'Completed' );
       
        $params2 = array( 'contribution_id' => $this->_contribution['id'] );
    }

///////////////// civicrm_contribution_add
     
    function testCreateEmptyParamsContribution()
    {
        $params = array();
        $contribution =& civicrm_contribution_add($params);
        $this->assertEquals( $contribution['is_error'], 1 );
        $this->assertEquals( $contribution['error_message'], 'No input parameters present' );
    }
    

    function testCreateParamsNotArrayContribution()
    {
        $params = 'contact_id= 1';                            
        $contribution =& civicrm_contribution_add($params);
        $this->assertEquals( $contribution['is_error'], 1 );
        $this->assertEquals( $contribution['error_message'], 'Input parameters is not an array' );
    }
    
    function testCreateParamsWithoutRequiredKeys()
    {
        $params = array( 'no_required' => 1 );
        $contribution =& civicrm_contribution_add($params);
        $this->assertEquals( $contribution['is_error'], 1 );
        $this->assertEquals( $contribution['error_message'], 'Required fields not found for contribution contact_id' );
    }
    function testCreateContribution()
    {
        $params = array(
                        'contact_id'             => $this->_individualId,                              
                        'receive_date'           => date('Ymd'),
                        'total_amount'           => 100.00,
                        'contribution_type_id'   => $this->_contributionTypeId,
                        'payment_instrument_id'  => 1,
                        'non_deductible_amount'  => 10.00,
                        'fee_amount'             => 50.00,
                        'net_amount'             => 90.00,
                        'trxn_id'                => 12345,
                        'invoice_id'             => 67890,
                        'source'                 => 'SSF',
                        'contribution_status_id' => 1,
                        $customField             => 'Custom Data for Contribution'
                        );
        
        $contribution =& civicrm_contribution_add($params);
        
        $this->assertEquals($contribution['contact_id'], $this->_individualId, 'In line ' . __LINE__ );                              
        $this->assertEquals($contribution['receive_date'],date('Ymd'), 'In line ' . __LINE__ );
        $this->assertEquals($contribution['total_amount'],100.00, 'In line ' . __LINE__ );
        $this->assertEquals($contribution['contribution_type_id'],$this->_contributionTypeId, 'In line ' . __LINE__ );
        $this->assertEquals($contribution['payment_instrument_id'],1, 'In line ' . __LINE__ );
        $this->assertEquals($contribution['non_deductible_amount'],10.00, 'In line ' . __LINE__ );
        $this->assertEquals($contribution['fee_amount'],50.00, 'In line ' . __LINE__ );
        $this->assertEquals($contribution['net_amount'],90.00, 'In line ' . __LINE__ );
        $this->assertEquals($contribution['trxn_id'],12345, 'In line ' . __LINE__ );
        $this->assertEquals($contribution['invoice_id'],67890, 'In line ' . __LINE__ );
        $this->assertEquals($contribution['source'],'SSF', 'In line ' . __LINE__ );
        $this->assertEquals($contribution['contribution_status_id'], 1, 'In line ' . __LINE__ );
        $this->_contribution = $contribution;

        $contributionID = array( 'contribution_id' => $contribution['id'] );
        $contribution   =& civicrm_contribution_delete($contributionID);
        
        $this->assertEquals( $contribution['is_error'], 0 );
        $this->assertEquals( $contribution['result'], 1 );
    }
    
    
    //To Update Contribution
    function testCreateUpdateContribution()
    {
        $contributionID = $this->contributionCreate($this->_individualId,$this->_contributionTypeId);

        $params = array(
                        'id'                     => $contributionID,
                        'contact_id'             => $this->_individualId,                              
                        'receive_date'           => date('Ymd'),
                        'total_amount'           => 110.00,
                        'contribution_type_id'   => $this->_contributionTypeId,
                        'payment_instrument_id'  => 1,
                        'non_deductible_amount'  => 20.00,
                        'fee_amount'             => 60.00,
                        'net_amount'             => 100.00,
                        'trxn_id'                => 23456,
                        'invoice_id'             => 78901,
                        'source'                 => 'WORLD',
                        'contribution_status_id' => 1,
                        'note'                   => 'Donating for Nobel Cause',
                        );
        
        $contribution =& civicrm_contribution_add($params);

        $this->assertEquals($contribution['contact_id'], $this->_individualId, 'In line ' . __LINE__ );                              
        $this->assertEquals($contribution['receive_date'],date('Ymd'), 'In line ' . __LINE__ );
        $this->assertEquals($contribution['total_amount'],110.00, 'In line ' . __LINE__ );
        $this->assertEquals($contribution['contribution_type_id'],$this->_contributionTypeId, 'In line ' . __LINE__ );
        $this->assertEquals($contribution['payment_instrument_id'],1, 'In line ' . __LINE__ );
        $this->assertEquals($contribution['non_deductible_amount'],20.00, 'In line ' . __LINE__ );
        $this->assertEquals($contribution['fee_amount'],60.00, 'In line ' . __LINE__ );
        $this->assertEquals($contribution['net_amount'],100.00, 'In line ' . __LINE__ );
        $this->assertEquals($contribution['trxn_id'],23456, 'In line ' . __LINE__ );
        $this->assertEquals($contribution['invoice_id'],78901, 'In line ' . __LINE__ );
        $this->assertEquals($contribution['source'],'WORLD', 'In line ' . __LINE__ );
        $this->assertEquals($contribution['contribution_status_id'], 1 , 'In line ' . __LINE__ );
        
        $contributionID = array( 'contribution_id' => $contribution['id'] );
        $contribution   =& civicrm_contribution_delete($contributionID);
        
        $this->assertEquals( $contribution['is_error'], 0 );
        $this->assertEquals( $contribution['result'], 1 );
    }

///////////////// civicrm_contribution_delete methods

    function testDeleteEmptyParamsContribution()
    {
        $params = array( );
        $contribution = civicrm_contribution_delete($params);
        $this->assertEquals( $contribution['is_error'], 1 );
        $this->assertEquals( $contribution['error_message'], 'Could not find contribution_id in input parameters' );
    }
    
    
    function testDeleteParamsNotArrayContribution()
    {
        $params = 'contribution_id= 1';                            
        $contribution = civicrm_contribution_delete($params);
        $this->assertEquals( $contribution['is_error'], 1 );
        $this->assertEquals( $contribution['error_message'], 'Could not find contribution_id in input parameters' );
    }

     
    function testDeleteWrongParamContribution()
    {
        $params = array( 'contribution_source' => 'SSF' );
        $contribution =& civicrm_contribution_delete( $params );
        $this->assertEquals($contribution['is_error'], 1);
        $this->assertEquals( $contribution['error_message'], 'Could not find contribution_id in input parameters' );
    }
    
    
    function testDeleteContribution()
    {
        $contributionID = $this->contributionCreate( $this->_individualId , $this->_contributionTypeId );
        $params         = array( 'contribution_id' => $contributionID );
        $contribution   = civicrm_contribution_delete( $params );
        $this->assertEquals( $contribution['is_error'], 0 );
        $this->assertEquals( $contribution['result'], 1 );
    }

///////////////// civicrm_contribution_search methods

    /**
     *  Test civicrm_contribution_search with wrong params type
     */
    function testSearchWrongParamsType()
    {
        $params = 'a string';
        $result =& civicrm_contribution_search($params);

        $this->assertEquals( $result['is_error'], 1, 'In line ' . __LINE__ );
        $this->assertEquals( $result['error_message'], 'Params need to be an array', 'In line ' . __LINE__ );
    }

    /**
     *  Test civicrm_contribution_search with empty params
     */
     function testSearchEmptyParams()
     {
        $params = array();
        $result =& civicrm_contribution_search($params);
        $this->assertEquals( $result['is_error'], 1, 'In line ' . __LINE__ );
        $this->assertEquals( $result['error_message'], 'No input parameters present', 'In line ' . __LINE__ );
     }

    /**
     *  Test civicrm_contribution_search. Success expected.
     */
     function testSearch()
     {
         $p = array(
                    'contact_id'             => $this->_individualId,
                    'receive_date'           => date('Ymd'),
                    'total_amount'           => 100.00,
                    'contribution_type_id'   => $this->_contributionTypeId,
                    'non_deductible_amount'  => 10.00,
                    'fee_amount'             => 51.00,
                    'net_amount'             => 91.00,
                    'trxn_id'                => 23456,
                    'invoice_id'             => 78910,
                    'source'                 => 'SSF',
                    'contribution_status_id' => 1
                    );
         
         $contribution =& civicrm_contribution_add($p);
         $params = array('contribution_id'   => $contribution['id'],
                         'return.fee_amount' => 1,
                         'sort'              => 'contribution_id'
                        );       
        $result =& civicrm_contribution_get($params);
        $this->assertEquals(  $result['fee_amount'], 51.00 );

        $params         = array( 'contribution_id' => $contribution['id'] );
        $contribution   = civicrm_contribution_delete( $params );
        
     }

///////////////// civicrm_contribution_format_create methods

     /**
     *  Test civicrm_contribution_format_creat with Empty params 
     */
    function testFormatCreateEmptyParams()
    {
        $params = array( );
        $result =& civicrm_contribution_format_create($params);

        $this->assertEquals( $result['is_error'], 1, 'In line ' . __LINE__ );
        $this->assertEquals( $result['error_message'], 'Input Parameters empty', 'In line ' . __LINE__ );
    }
    
    /**
     *  Test civicrm_contribution_format_creat with wrong params type
     */
    function testFormatCreateParamsType()
    {
        $params = 'a string';
        $result =& civicrm_contribution_format_create($params);

        $this->assertEquals( $result['is_error'], 1, 'In line ' . __LINE__ );
    }

    /**
     *  Test civicrm_contribution_format_creat with invalid data
     */
    function testFormatCreateInvalidData()
    {
         require_once 'CRM/Contribute/DAO/Contribution.php';
        $validParams = array( 'contact_id'   => $this->_individualId,
                              'receive_date' => date('Ymd'),
                              'total_amount'           => 100.00,
                              'contribution_type_id'   => $this->_contributionTypeId,
                              'contribution_status_id' => 1
                              );
        $params = $validParams;
        $params['receive_date'] = 'invalid';
        $result =& civicrm_contribution_format_create($params);
        $this->assertEquals( $result['is_error'], 1, 'In line ' . __LINE__ );

        $params = $validParams;
        $params['total_amount'] = 'invalid';
        $result =& civicrm_contribution_format_create($params);
        $this->assertEquals( $result['is_error'], 1, 'In line ' . __LINE__ );

        $params = $validParams;
        $params['currency'] = 'invalid';
        $result =& civicrm_contribution_format_create($params);
        $this->assertEquals( $result['is_error'], 1, 'In line ' . __LINE__ );

        $params = $validParams;
        $params['contribution_contact_id'] = 'invalid';
        $result =& civicrm_contribution_format_create($params);
        $this->assertEquals( $result['is_error'], 1, 'In line ' . __LINE__ );

        $params = $validParams;
        $params['contribution_contact_id'] = 999;
        $result =& civicrm_contribution_format_create($params);
        $this->assertEquals( $result['is_error'], 1, 'In line ' . __LINE__ );
    }

    /**
     *  Test civicrm_contribution_format_creat success expected
     */
    function testFormatCreate()
    {
        require_once 'CRM/Contribute/DAO/Contribution.php';
        require_once 'CRM/Contribute/PseudoConstant.php';

        $params = array( 'contact_id'             => $this->_individualId,
                         'receive_date'           => date('Ymd'),
                         'total_amount'           => 100.00,
                         'contribution_type_id'   => $this->_contributionTypeId,
                         'contribution_status_id' => 1,
                         'contribution_type'      => 'Donation',
                         'note'                   => 'note'
                         );
       
        $result =& civicrm_contribution_format_create($params);

        $this->assertEquals( $result['total_amount'],100.00, 'In line ' . __LINE__ );
        $this->assertEquals( $result['contribution_status_id'],1, 'In line ' . __LINE__ );

        $params         = array( 'contribution_id' => $result['id'] );
        $contribution   = civicrm_contribution_delete( $params );
    }

/////////////////  _civicrm_contribute_format_params for $create
    
    function testFormatParams() {
        require_once 'CRM/Contribute/DAO/Contribution.php';
        $params = array( 'contact_id'             => $this->_individualId,
                         'receive_date'           => date('Ymd'),
                         'total_amount'           => 100.00,
                         'contribution_type_id'   => $this->_contributionTypeId,
                         'contribution_status_id' => 1,
                         'contribution_type'      => null,
                         'note'                   => 'note',
                         'contribution_source'    => 'test'
                         );

        $values = array( );
        $result = _civicrm_contribute_format_params( $params, $values, true );
        $this->assertEquals( $values['total_amount'],100.00, 'In line ' . __LINE__ );
        $this->assertEquals( $values['contribution_status_id'],1, 'In line ' . __LINE__ );
    }
}

