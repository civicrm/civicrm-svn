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


require_once 'api/v3/Contribution.php';
require_once 'CiviTest/CiviUnitTestCase.php';

class api_v3_ContributionTest extends CiviUnitTestCase 
{
    /**
     * Assume empty database with just civicrm_data
     */
    protected $_individualId;    
    protected $_contribution;
    protected $_contributionTypeId;
    protected $_apiversion;
    
    function setUp() 
    {
        parent::setUp();
        $this->_apiversion = 3;
        $this->_contributionTypeId = 1;
        $this->_individualId = $this->individualCreate(null, $this->_apiversion);
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
        $this->assertEquals( $contribution['error_message'], 'Mandatory key(s) missing from params array: version' );
    }
    
    
    function testGetParamsNotArrayContribution()
    {
        $params = 'contact_id= 1';                            
        $contribution =& civicrm_contribution_get($params);
        $this->assertEquals( $contribution['is_error'], 1 );
        $this->assertEquals( $contribution['error_message'], 'Input variable `params` is not an array' );
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
                        'contribution_status_id' => 1,
                        'version'								=> $this->_apiversion,
                        );
        $this->_contribution =& civicrm_contribution_create($p);
        $this->assertEquals( $this->_contribution['is_error'], 0 ,'In line ' . __LINE__ );        
        
        $params = array('contribution_id'=>$this->_contribution['id'],
                         'version'								=> $this->_apiversion,                         );        
        $contribution =& civicrm_contribution_get($params);
        $this->documentMe($params,$contribution,__FUNCTION__,__FILE__); 
        $this->assertEquals($contribution['values'][$contribution['id']]['contact_id'],$this->_individualId,'In line ' . __LINE__ ); 
        $this->assertEquals($contribution['values'][$contribution['id']]['contribution_type_id'],$this->_contributionTypeId);        
        $this->assertEquals($contribution['values'][$contribution['id']]['total_amount'],100.00,'In line ' . __LINE__ );
        $this->assertEquals($contribution['values'][$contribution['id']]['non_deductible_amount'],10.00,'In line ' . __LINE__ );
        $this->assertEquals($contribution['values'][$contribution['id']]['fee_amount'],51.00,'In line ' . __LINE__ );
        $this->assertEquals($contribution['values'][$contribution['id']]['net_amount'],91.00,'In line ' . __LINE__ );
        $this->assertEquals($contribution['values'][$contribution['id']]['trxn_id'],23456,'In line ' . __LINE__ );
        $this->assertEquals($contribution['values'][$contribution['id']]['invoice_id'],78910,'In line ' . __LINE__ );
        $this->assertEquals($contribution['values'][$contribution['id']]['contribution_source'],'SSF','In line ' . __LINE__ );
        $this->assertEquals($contribution['values'][$contribution['id']]['contribution_status'], 'Completed','In line ' . __LINE__  );
       
        $params2 = array( 'contribution_id' => $this->_contribution['id'] ,
                          'version'         => $this->_apiversion);
        civicrm_contribution_delete($params2);
    }

///////////////// civicrm_contribution_
     
    function testCreateEmptyParamsContribution()
    {

    
      $params = array( );
        $contribution = civicrm_contribution_create($params);
        $this->assertEquals( $contribution['is_error'], 1 ,'In line ' . __LINE__ );
        $this->assertEquals( $contribution['error_message'], 'Mandatory key(s) missing from params array: contact_id, total_amount, one of (contribution_type_id, contribution_type), version','In line ' . __LINE__  );
    }
    

    function testCreateParamsNotArrayContribution()
    {
 
        $params = 'contact_id= 1';                            
        $contribution =& civicrm_contribution_create($params);
        $this->assertEquals( $contribution['is_error'], 1 );
        $this->assertEquals( $contribution['error_message'], 'Input variable `params` is not an array' );
    }
    
    function testCreateParamsWithoutRequiredKeys()
    {
        $params = array( 'no_required' => 1 );
        $contribution =& civicrm_contribution_create($params);
        $this->assertEquals( $contribution['is_error'], 1 );
        $this->assertEquals( $contribution['error_message'], 'Mandatory key(s) missing from params array: contact_id, total_amount, one of (contribution_type_id, contribution_type), version' );
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
                        'version' =>$this->_apiversion,
                        );
        
        $contribution=& civicrm_contribution_create($params);
        $this->documentMe($params, $contribution,__FUNCTION__,__FILE__);        
        $this->assertEquals($contribution['values'][$contribution['id']]['contact_id'], $this->_individualId, 'In line ' . __LINE__ );                              
        $this->assertEquals($contribution['values'][$contribution['id']]['receive_date'],date('Ymd'), 'In line ' . __LINE__ );
        $this->assertEquals($contribution['values'][$contribution['id']]['total_amount'],100.00, 'In line ' . __LINE__ );
        $this->assertEquals($contribution['values'][$contribution['id']]['contribution_type_id'],$this->_contributionTypeId, 'In line ' . __LINE__ );
        $this->assertEquals($contribution['values'][$contribution['id']]['payment_instrument_id'],1, 'In line ' . __LINE__ );
        $this->assertEquals($contribution['values'][$contribution['id']]['non_deductible_amount'],10.00, 'In line ' . __LINE__ );
        $this->assertEquals($contribution['values'][$contribution['id']]['fee_amount'],50.00, 'In line ' . __LINE__ );
        $this->assertEquals($contribution['values'][$contribution['id']]['net_amount'],90.00, 'In line ' . __LINE__ );
        $this->assertEquals($contribution['values'][$contribution['id']]['trxn_id'],12345, 'In line ' . __LINE__ );
        $this->assertEquals($contribution['values'][$contribution['id']]['invoice_id'],67890, 'In line ' . __LINE__ );
        $this->assertEquals($contribution['values'][$contribution['id']]['source'],'SSF', 'In line ' . __LINE__ );
        $this->assertEquals($contribution['values'][$contribution['id']]['contribution_status_id'], 1, 'In line ' . __LINE__ );
        $this->_contribution = $contribution;

        $contributionID = array( 'contribution_id' => $contribution['id'] ,
                                  'version'        =>$this->_apiversion);
        $contribution   =& civicrm_contribution_delete($contributionID);
        
        $this->assertEquals( $contribution['is_error'], 0 ,'In line ' . __LINE__ );

    }
    
            /**
     *  Test  using example code
     */
    function testContributionCreateExample( )
    {
    
      require_once 'api/v3/examples/ContributionCreate.php';
      $result = contribution_create_example();
      $expectedResult = contribution_create_expectedresult();
      $this->assertEquals($result,$expectedResult);
    }
    
    //To Update Contribution
    //CHANGE: we require the API to do an incremental update
    function testCreateUpdateContribution()
    {
  
        $contributionID = $this->contributionCreate($this->_individualId,$this->_contributionTypeId,$this->_apiversion);
        $old_params = array(
                            'contribution_id' => $contributionID,   
                            'version'					=> $this->_apiversion, 
                            );
        $original =& civicrm_contribution_get($old_params);
        //Make sure it came back
        $this->assertTrue(empty($original['is_error']), 'In line ' . __LINE__);
        $this->assertEquals($original['id'], $contributionID, 'In line ' . __LINE__);
        //set up list of old params, verify

        //This should not be required on update:
        $old_contact_id = $original['values'][$contributionID]['contact_id'];
        $old_payment_instrument = $original['values'][$contributionID]['instrument_id'];
        $old_fee_amount = $original['values'][$contributionID]['fee_amount'];
        $old_source = $original['values'][$contributionID]['contribution_source'];

        //note: current behavior is to return ISO.  Is this
        //documented behavior?  Is this correct
        $old_receive_date = date('Ymd', strtotime($original['values'][$contributionID]['receive_date']));

        $old_trxn_id = $original['values'][$contributionID]['trxn_id'];
        $old_invoice_id = $original['values'][$contributionID]['invoice_id'];
        
        //check against values in CiviUnitTestCase::createContribution()
        $this->assertEquals($old_contact_id, $this->_individualId, 'In line ' . __LINE__);
        $this->assertEquals($old_fee_amount, 50.00, 'In line ' . __LINE__);
        $this->assertEquals($old_source, 'SSF', 'In line ' . __LINE__);
        $this->assertEquals($old_trxn_id, 12345, 'In line ' . __LINE__);
        $this->assertEquals($old_invoice_id, 67890, 'In line ' . __LINE__);
        $params = array(
                        'id'                     => $contributionID,
                        'contact_id'             => $this->_individualId,    
                        'total_amount'           => 110.00,
                        'contribution_type_id'   => $this->_contributionTypeId,
                        'non_deductible_amount'  => 10.00,
                        'net_amount'             => 100.00,
                        'contribution_status_id' => 1,
                        'note'                   => 'Donating for Nobel Cause',
                        'version'								=>$this->_apiversion,
                        );
        
        $contribution =& civicrm_contribution_create($params);
       
        $new_params = array(
                            'contribution_id' => $contribution['id'],  
                            'version'					=>$this->_apiversion,  
                            );
        $contribution =& civicrm_contribution_get($new_params);
        
        $this->assertEquals($contribution['values'][$contributionID]['contact_id'], $this->_individualId, 'In line ' . __LINE__ );   
        $this->assertEquals($contribution['values'][$contributionID]['total_amount'],110.00, 'In line ' . __LINE__ );
        $this->assertEquals($contribution['values'][$contributionID]['contribution_type_id'],$this->_contributionTypeId, 'In line ' . __LINE__ );
        $this->assertEquals($contribution['values'][$contributionID]['instrument_id'],$old_payment_instrument, 'In line ' . __LINE__ );
        $this->assertEquals($contribution['values'][$contributionID]['non_deductible_amount'],10.00, 'In line ' . __LINE__ );
        $this->assertEquals($contribution['values'][$contributionID]['fee_amount'],$old_fee_amount, 'In line ' . __LINE__ );
        $this->assertEquals($contribution['values'][$contributionID]['net_amount'],100.00, 'In line ' . __LINE__ );
        $this->assertEquals($contribution['values'][$contributionID]['trxn_id'],$old_trxn_id, 'In line ' . __LINE__ );
        $this->assertEquals($contribution['values'][$contributionID]['invoice_id'],$old_invoice_id, 'In line ' . __LINE__ );
        $this->assertEquals($contribution['values'][$contributionID]['contribution_source'],$old_source, 'In line ' . __LINE__ );
        $this->assertEquals($contribution['values'][$contributionID]['contribution_status'], 'Completed' , 'In line ' . __LINE__ );
        $params = array( 'contribution_id' => $contributionID,
                                  'version'        =>$this->_apiversion);
        $result   =& civicrm_contribution_delete($params);
        $this->assertEquals( $result['is_error'], 0 ,'in line' . __LINE__);

    }

///////////////// civicrm_contribution_delete methods

    function testDeleteEmptyParamsContribution()
    {
        $params = array( );
        $contribution = civicrm_contribution_delete($params);
        $this->assertEquals( $contribution['is_error'], 1 );
        $this->assertEquals( $contribution['error_message'], 'Mandatory key(s) missing from params array: contribution_id, version' );
    }
    
    
    function testDeleteParamsNotArrayContribution()
    {
        $params = 'contribution_id= 1';                            
        $contribution = civicrm_contribution_delete($params);
        $this->assertEquals( $contribution['is_error'], 1 );
        $this->assertEquals( $contribution['error_message'], 'Input variable `params` is not an array' );
    }

     
    function testDeleteWrongParamContribution()
    {
        $params = array( 'contribution_source' => 'SSF' );
        $contribution =& civicrm_contribution_delete( $params );
        $this->assertEquals($contribution['is_error'], 1);
        $this->assertEquals( $contribution['error_message'], 'Mandatory key(s) missing from params array: contribution_id, version' );
    }
    
    
    function testDeleteContribution()
    {
     
        $contributionID = $this->contributionCreate( $this->_individualId , $this->_contributionTypeId,$this->_apiversion );
        $params         = array( 'contribution_id' => $contributionID ,
                                  'version'        => $this->_apiversion,);
        $contribution   = civicrm_contribution_delete( $params );
        $this->documentMe($params,$result,__FUNCTION__,__FILE__); 
        $this->assertEquals( $contribution['is_error'], 0, 'In line ' . __LINE__ );
    }

///////////////// civicrm_contribution_search methods

    /**
     *  Test civicrm_contribution_search with wrong params type
     */
    function testSearchWrongParamsType()
    {
        $params = 'a string';
        $result =& civicrm_contribution_get($params);

        $this->assertEquals( $result['is_error'], 1, 'In line ' . __LINE__ );
        $this->assertEquals( $result['error_message'], 'Input variable `params` is not an array', 'In line ' . __LINE__ );
    }

    /**
     *  Test civicrm_contribution_search with empty params.
     *  All available contributions expected.
     */
     function testSearchEmptyParams()
     {
        $params = array('version' => $this->_apiversion);

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
                  'contribution_status_id' => 1,
                  'version'								 =>$this->_apiversion,
                  );         
        $contribution =& civicrm_contribution_create($p);

        $result =& civicrm_contribution_get($params);
        // We're taking the first element.
        $res = $result['values'][1];

        $this->assertEquals( $p['contact_id'],            $res['contact_id'], 'In line ' . __LINE__ );
        $this->assertEquals( $p['total_amount'],          $res['total_amount'], 'In line ' . __LINE__ );
        $this->assertEquals( $p['contribution_type_id'],  $res['contribution_type_id'], 'In line ' . __LINE__ );
        $this->assertEquals( $p['net_amount'],            $res['net_amount'], 'In line ' . __LINE__ );
        $this->assertEquals( $p['non_deductible_amount'], $res['non_deductible_amount'], 'In line ' . __LINE__ );        
        $this->assertEquals( $p['fee_amount'],            $res['fee_amount'], 'In line ' . __LINE__ );        
        $this->assertEquals( $p['trxn_id'],               $res['trxn_id'], 'In line ' . __LINE__ );                
        $this->assertEquals( $p['invoice_id'],            $res['invoice_id'], 'In line ' . __LINE__ );                        
        $this->assertEquals( $p['source'],                $res['contribution_source'], 'In line ' . __LINE__ );                        
        // contribution_status_id = 1 => Completed
        $this->assertEquals( 'Completed',                 $res['contribution_status'], 'In line ' . __LINE__ );                        
     }

    /**
     *  Test civicrm_contribution_search. Success expected.
     */
     function testSearch()
     {

        
         $p1 = array(
                     'contact_id'             => $this->_individualId,
                     'receive_date'           => date('Ymd'),
                     'total_amount'           => 100.00,
                     'contribution_type_id'   => $this->_contributionTypeId,
                     'non_deductible_amount'  => 10.00,
                     'contribution_status_id' => 1,
                     'version'								=>$this->apiversion,
                     );       
         $contribution1 =& civicrm_contribution_create($p1);
         
         $p2 = array(
                     'contact_id'             => $this->_individualId,
                     'receive_date'           => date('Ymd'),
                     'total_amount'           => 200.00,
                     'contribution_type_id'   => $this->_contributionTypeId,
                     'non_deductible_amount'  => 20.00,
                     'trxn_id'                => 5454565,
                     'invoice_id'             => 1212124,
                     'fee_amount'             => 50.00,
                     'net_amount'             => 60.00,
                     'contribution_status_id' => 2,
                     'version'								=>$this->apiversion,
                     );    
         $contribution2 =& civicrm_contribution_create($p2);
         
         $params = array( 'contribution_id'=> $contribution2['id'] ,
         									'version' => $this->_apiversion);
         $result =& civicrm_contribution_get($params);
         $res    = $result['values'][$contribution2['id']];
         
         $this->assertEquals( $p2['contact_id'],            $res['contact_id'], 'In line ' . __LINE__ );
         $this->assertEquals( $p2['total_amount'],          $res['total_amount'], 'In line ' . __LINE__ );
         $this->assertEquals( $p2['contribution_type_id'],  $res['contribution_type_id'], 'In line ' . __LINE__ );
         $this->assertEquals( $p2['net_amount'],            $res['net_amount'], 'In line ' . __LINE__ );
         $this->assertEquals( $p2['non_deductible_amount'], $res['non_deductible_amount'], 'In line ' . __LINE__ );        
         $this->assertEquals( $p2['fee_amount'],            $res['fee_amount'], 'In line ' . __LINE__ );        
         $this->assertEquals( $p2['trxn_id'],               $res['trxn_id'], 'In line ' . __LINE__ );                
         $this->assertEquals( $p2['invoice_id'],            $res['invoice_id'], 'In line ' . __LINE__ );    
         // contribution_status_id = 2 => Pending
         $this->assertEquals( 'Pending',                    $res['contribution_status'], 'In line ' . __LINE__ ); 
         
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

