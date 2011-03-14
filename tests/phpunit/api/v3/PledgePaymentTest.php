<?php
/*
 +--------------------------------------------------------------------+
 | CiviCRM version 3.4                                                |
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


require_once 'api/v3/Pledge.php';
require_once 'api/v3/PledgePayment.php';
require_once 'CiviTest/CiviUnitTestCase.php';

class api_v3_PledgePaymentTest extends CiviUnitTestCase 
{
    /**
     * Assume empty database with just civicrm_data
     */
    protected $_individualId;    
    protected $_pledgeID;
    protected $_apiversion;
    protected $_contributionID;
    protected $_contributionTypeId;   

    function setUp() 
    {
        $this->_apiversion = 3;    
        parent::setUp();

        $this->_contributionTypeId = 1;   
        $this->_individualId = $this->individualCreate(null);
        $this->_pledgeID = $this->pledgeCreate($this->_individualId);
        $this->_contributionID = $this->contributionCreate($this->_individualId, $this->_contributionTypeId);
    }
    
    function tearDown() 
    {
      $this->contributionDelete($this->_contributionID);
      civicrm_api3_pledge_delete(array('id'			 => $this->_pledgeID,
                                       'version' => 3,));
    }


    function testGetPledgePayment()
    {
       $params = array('version'	=>$this->_apiversion,
                       );                        
        $result=& civicrm_api3_pledge_payment_get($params);
        $this->documentMe($params,$result,__FUNCTION__,__FILE__); 
        $this->assertEquals(0, $result['is_error'], " in line " . __LINE__);
        $this->assertEquals(5, $result['count'], " in line " . __LINE__);

    }
    
    /*
     * Test that passing in a single variable works
     */
      function testGetSinglePledgePayment(){
// this isn't working at the moment but leaving it 'broken' for now as this is using the
 //boiler plate code (e.g. same as tag_get so it seems we should work the 'best' way for this
 //since it is a new api should we push on to get the unique fields working?                           
 
             $createparams = array(
                        'contact_id'             => $this->_individualId,
          							'pledge_id' 						 => $this->_pledgeID,
                        'contribution_id'        => $this->_contributionID,  
                        'version'									=>$this->_apiversion,
                        'status_id'							 => 1,
          
                  );                        
           $createResult = civicrm_api3_pledge_payment_create($createparams);
           $this->assertEquals(0, $createResult['is_error'], " in line " . __LINE__);
           $params = array('version'	=>$this->_apiversion,
                           'pledge_payment_status_id' =>1,
                           'status_id'								=>1, 	
                             );
          $result= civicrm_api3_pledge_payment_get($params);                     
           $this->assertEquals(0, $result['is_error'], " in line " . __LINE__); 
           $this->assertEquals(1, $result['count'], " in line " . __LINE__); 
                     
      }  

    function testCreatePledgePayment()
    {
      //check that 5 pledge payments exist at the start
      $getParams = array('version'	=>$this->_apiversion,
                       );                        
      $beforeAdd=& civicrm_api3_pledge_payment_get($getParams);
      $this->assertEquals(0, $beforeAdd['is_error'], " in line " . __LINE__);
      $this->assertEquals(5, $beforeAdd['count'], " in line " . __LINE__);
      
      //test the pledge_payment_create function
      $params = array(
                        'contact_id'             => $this->_individualId,
          							'pledge_id' 						 => $this->_pledgeID,
                        'contribution_id'        => $this->_contributionID,  
                        'version'									=>$this->_apiversion,
                        'status_id'							 => 1,
                        'actual_amount'					 => 20,
          
                  );                        
      $result= civicrm_api3_pledge_payment_create($params);
      $this->documentMe($params,$result,__FUNCTION__,__FILE__);
      $this->assertEquals(0, $result['is_error'], " in line " . __LINE__);
      
      //check existing updated not new one created - 'create' means add contribution_id in this context
      $afterAdd=& civicrm_api3_pledge_payment_get($getParams);
      $this->assertEquals(0, $beforeAdd['is_error'], " in line " . __LINE__);
      $this->assertEquals(5, $afterAdd['count'], " in line " . __LINE__);   

      //get the created payment & check it out
      $getParams['id'] = $result['id'];
      $getIndPayment= civicrm_api3_pledge_payment_get($getParams);  
      $this->assertEquals(1, $getIndPayment['count'], " in line " . __LINE__); 
      $this->assertEquals(20, $getIndPayment['values'][$result['id']]['actual_amount'], " in line " . __LINE__); 

      //create a second pledge payment - need a contribution first &can't use the CiviUnitTest case function as invoice is hard-coded
      $contributionParams = array('version' => 3,
                                  'total_amount'  => 20,
                                  'contact_id' =>$this->_individualId,
                                  'contribution_type_id' => $this->_contributionTypeId);
      $contribution = civicrm_api('contribution','create',$contributionParams);

      $this->assertEquals(0,  $contribution['is_error'], " in line " . __LINE__);  

      $params['contribution_id'] =  $contribution['id'];
      
 
      $resultCont2= civicrm_api3_pledge_payment_create($params);
      $this->assertEquals(0, $resultCont2['is_error'], " in line " . __LINE__);
      //make sure original is untouched & has not been updated
      $this->assertGreaterThan( $result['id'], $resultCont2['id']," in line " . __LINE__);
      $getIndPaymentAgain= civicrm_api3_pledge_payment_get($getParams);      
      $this->assertEquals(1, $getIndPaymentAgain['count'], " in line " . __LINE__);  
      $this->assertEquals($this->_contributionID, $getIndPaymentAgain['values'][$result['id']]['contribution_id'], " in line " . __LINE__); 
      
    }
    
    function testUpdatePledgePayment(){
      $params = array(
                        'contact_id'             => $this->_individualId,
          							'pledge_id' 						 => $this->_pledgeID,
                        'contribution_id'        => $this->_contributionID,  
                        'version'									=>$this->_apiversion,
                        'status_id'							 => 2,
                        'actual_amount'					 => 20,
          
                  );                        
      $result= civicrm_api3_pledge_payment_create($params);
      $updateparams = array('id' => $result['id'],
                        		'status_id' =>1,
                            'version'		=>$this->_apiversion,
      );
      
      $result= civicrm_api('pledge_payment','update',$updateparams); 
      $this->assertEquals($result['is_error'], 0) ;
      $this->assertEquals('20.00',$result['values'][$result['id']]['actual_amount']) ;
      $this->assertEquals($result['values'][$result['id']]['status_id'], 2) ;

      
    }
    function testDeletePledgePayment()
    {
      $params = array(
                        'contact_id'             => $this->_individualId,
          							'pledge_id' 						 => $this->_pledgeID,
                        'contribution_id'        => $this->_contributionID,  
                        'version'									=>$this->_apiversion,
                        'status_id'							 => 1,
                        'sequential'						 => 1,
                        'actual_amount'					 => 20,
          
                  );                        
        $pledgePayment= civicrm_api3_pledge_payment_create($params);

        $deleteParams = array('id' => $pledgePayment['id'],
                               'version'									=>$this->_apiversion, );
        $result = civicrm_api3_pledge_payment_delete($deleteParams);
        $this->documentMe($deleteParams,$result,__FUNCTION__,__FILE__);
        $this->assertEquals(0, $result['is_error'], " in line " . __LINE__);
        
    }
}
 