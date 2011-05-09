<?php
/*
 +--------------------------------------------------------------------+
 | CiviCRM version 4.0                                                |
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
require_once 'CiviTest/CiviUnitTestCase.php';

class api_v3_PledgeTest extends CiviUnitTestCase 
{
    /**
     * Assume empty database with just civicrm_data
     */
    protected $_individualId;    
    protected $_pledge;
    protected $_apiversion;
    protected $params;
    protected $scheduled_date;
    public $DBResetRequired = false;

    
    function setUp() 
    {
        $this->_apiversion = 3;    
        parent::setUp();
        //need to set scheduled payment in advance we are running test @ midnight & it becomes unexpectedly overdue
        //due to timezone issues 
        $this->scheduled_date = date('Ymd',mktime(0, 0, 0, date("m"), date("d")+2, date("y")));
   
        $this->_individualId = $this->individualCreate(null);
        $this->params =  array(
                        'contact_id'             => $this->_individualId,
                        'pledge_create_date'    => date('Ymd'),
                        'start_date'   					 => date('Ymd'),
                        'scheduled_date'         => $this->scheduled_date,   
                        'pledge_amount'         => 100.00,
                        'pledge_status_id'         => '2',
                        'pledge_contribution_type_id'  => '1',
                        'pledge_original_installment_amount' => 20,
                        'frequency_interval'             => 5,
                        'frequency_unit'             => 'year',
                        'frequency_day'            => 15,
                        'installments'            =>5,
                        'sequential'						  =>1,
                        'version'									=>$this->_apiversion,
          
                  ); 
    }
    
    function tearDown() 
    {
      $this->contactDelete($this->_individualId);
    }

///////////////// civicrm_pledge_get methods

    function testGetEmptyParamsPledge()
    {
    // carry over from old contribute - should return empty array - not written for contact
    }
    
    
    function testGetParamsNotArrayPledge()
    {
    //carry over from old contribute - no separate handling for this now
    
    }
 

    function testGetPledge()
    {     
     
                       
        $this->_pledge =& civicrm_api3_pledge_create($this->params);
        $params = array('pledge_id'=>$this->_pledge['id'],
                         'version'    => $this->_apiversion);        
        $result = civicrm_api3_pledge_get($params);
        $pledge = $result['values'][$this->_pledge['id']];
        $this->documentMe($params,$result,__FUNCTION__,__FILE__); 
        $this->assertEquals($this->_individualId,$pledge['contact_id'], 'in line' . __LINE__); 
        $this->assertEquals($this->_pledge['id'],$pledge['pledge_id'], 'in line' . __LINE__); 
        $this->assertEquals(date('Y-m-d').' 00:00:00', $pledge['pledge_create_date'], 'in line' . __LINE__); 
        $this->assertEquals(100.00,$pledge['pledge_amount'], 'in line' . __LINE__);
        $this->assertEquals('Pending',$pledge['pledge_status'], 'in line' . __LINE__);
        $this->assertEquals(5,$pledge['pledge_frequency_interval'], 'in line' . __LINE__);
        $this->assertEquals('year',$pledge['pledge_frequency_unit'], 'in line' . __LINE__);
        $this->assertEquals(date('Y-m-d',strtotime($this->scheduled_date)) .' 00:00:00',$pledge['pledge_next_pay_date'], 'in line' . __LINE__);
        $this->assertEquals($pledge['pledge_next_pay_amount'],20.00, 'in line' . __LINE__);
        
        $params2 = array( 'pledge_id' => $this->_pledge['id'],
                           'version'  =>$this->_apiversion, );
        $pledge   =& civicrm_api3_pledge_delete($params2);
    }

///////////////// civicrm_pledge_add
     
    function testCreateEmptyParamsPledge()
    {
        $params = array();
        $pledge =& civicrm_api3_pledge_create($params);
        $this->assertEquals( $pledge['is_error'], 1 );
    }
    

    function testCreateParamsNotArrayPledge()
    {
        $params = 'contact_id= 1';                            
        $pledge =& civicrm_api3_pledge_create($params);
        $this->assertEquals( $pledge['is_error'], 1 );
    }
    
    function testCreateParamsWithoutRequiredKeys()
    {
        $params = array( 'no_required' => 1 );
        $pledge =& civicrm_api3_pledge_create($params);
        $this->assertEquals( $pledge['is_error'], 1 );
    }
    
    function testCreatePledge()
    {
                     
        $result=& civicrm_api3_pledge_create($this->params);
        $this->documentMe($this->params,$result,__FUNCTION__,__FILE__); 
        $this->assertEquals(0, $result['is_error'], "in line " . __LINE__);
        $this->assertEquals($result['values'][0]['amount']     ,100.00, 'In line ' . __LINE__); 
        $this->assertEquals($result['values'][0]['installments'] ,5, 'In line ' . __LINE__); 
        $this->assertEquals($result['values'][0]['frequency_unit'],'year', 'In line ' . __LINE__);
        $this->assertEquals($result['values'][0]['frequency_interval'],5, 'In line ' . __LINE__);
        $this->assertEquals($result['values'][0]['frequency_day'],15, 'In line ' . __LINE__);
        $this->assertEquals($result['values'][0]['original_installment_amount'],20, 'In line ' . __LINE__);
    //    $this->assertEquals($result['values'][0]['contribution_type_id'],1, 'In line ' . __LINE__);
        $this->assertEquals($result['values'][0]['status_id'],2, 'In line ' . __LINE__);
        $this->assertEquals($result['values'][0]['create_date'],date('Ymd'), 'In line ' . __LINE__);
        $this->assertEquals($result['values'][0]['start_date'],date('Ymd'), 'In line ' . __LINE__);        
        $this->assertEquals($result['is_error'], 0 , 'In line ' . __LINE__);

        $pledgeID = array( 'pledge_id' => $result['id'], 'version' => 3 );
        $pledge   =& civicrm_api3_pledge_delete($pledgeID);

    }
/*
 * test that using original_installment_amount rather than pledge_original_installment_amount works
 * Pledge field behaviour is a bit random & so pledge has come to try to handle both unique & non -unique fields
 */
    function testCreatePledgeWithNonUnique()
    {
        $params = $this->params; 
        $params['original_installment_amount']   =    $params['pledge_original_installment_amount'];
        
        unset ($params['pledge_original_installment_amount']);
        $result=& civicrm_api3_pledge_create($params);
        $result = civicrm_api('Pledge', 'Get', array('version' => 3, 'id' => $result['id'], 'sequential' => 1));
        $pledge = $result['values'][0];
 
        $this->assertEquals(0, $result['is_error'], "in line " . __LINE__);
        $this->assertEquals(100.00, $pledge['pledge_amount']     , 'In line ' . __LINE__); 
        $this->assertEquals('year',$pledge['pledge_frequency_unit'], 'In line ' . __LINE__);
        $this->assertEquals(5,$pledge['pledge_frequency_interval'], 'In line ' . __LINE__);
        $this->assertEquals(20, $pledge['pledge_next_pay_amount'],'In line ' . __LINE__);

        $pledgeID = array( 'pledge_id' => $result['id'], 'version' => 3 );
        $pledge   =& civicrm_api3_pledge_delete($pledgeID);

    }
        function testCreateCancelPledge()
    {

                     
        $result=& civicrm_api3_pledge_create($this->params);
        $this->assertEquals(0, $result['is_error'], "in line " . __LINE__);
        $this->assertEquals(2, $result['values'][0]['status_id'], "in line " . __LINE__);
        $cancelparams = array('sequential' => 1, 'version' => $this->_apiversion,'id' => $result['id'], 'pledge_status_id' => 3);
        $result=& civicrm_api3_pledge_create( $cancelparams);
        $this->assertEquals(3, $result['values'][0]['status_id'], "in line " . __LINE__);
        $pledgeID = array( 'pledge_id' => $result['id'], 'version' => 3 );
        $pledge   =& civicrm_api3_pledge_delete($pledgeID);    
    }

    /*
     * test that status is set to pending
     */
    function testCreatePledgeNoStatus()
    {
   
        $params = $this->params;
        unset ($params['status_id']);             
        $result=& civicrm_api3_pledge_create($params);
        $this->assertEquals(0, $result['is_error'], "in line " . __LINE__);
        $this->assertEquals(2, $result['values'][0]['status_id'], "in line " . __LINE__);
        $pledgeID = array( 'pledge_id' => $result['id'], 'version' => 3 );
        $pledge   =& civicrm_api3_pledge_delete($pledgeID);    
    }   
    
    //To Update Pledge
    function testCreateUpdatePledge()
    {

  // we test 'sequential' param here too     
        $pledgeID = $this->pledgeCreate($this->_individualId);
        $old_params = array(
                            'id' => $pledgeID,  
                            'sequential' =>1,  
                            'version'			 =>$this->_apiversion,
                            );
        $original =& civicrm_api3_pledge_get($old_params);
        //Make sure it came back
        $this->assertEquals($original['values'][0]['pledge_id'], $pledgeID, 'In line ' . __LINE__);
        //set up list of old params, verify
        $old_contact_id = $original['values'][0]['contact_id'];
        $old_frequency_unit = $original['values'][0]['pledge_frequency_unit'];
        $old_frequency_interval = $original['values'][0]['pledge_frequency_interval'];
        $old_status_id = $original['values'][0]['pledge_status'];

        
        //check against values in CiviUnitTestCase::createPledge()
        $this->assertEquals($old_contact_id, $this->_individualId, 'In line ' . __LINE__);
        $this->assertEquals($old_frequency_unit, 'year', 'In line ' . __LINE__);
        $this->assertEquals($old_frequency_interval, 5, 'In line ' . __LINE__);
        $this->assertEquals($old_status_id, 'Pending', 'In line ' . __LINE__);
        $params = array(
                        'id'                     => $pledgeID,
                        'contact_id'             => $this->_individualId,    
                        'pledge_status_id'   => 3,
                        'amount'             => 100,
                        'contribution_type_id' => 1,
                        'start_date' => date('Ymd'),
                        'installments' => 10,
                        'version'			 =>$this->_apiversion,
                        );
        
        $pledge =& civicrm_api3_pledge_create($params); 
       $this->assertEquals( $pledge['is_error'], 0 );    
        $new_params = array(
                            'id' => $pledge['id'],   
                            'version'			 =>$this->_apiversion, 
                            );
        $pledge =& civicrm_api3_pledge_get($new_params);
        $this->assertEquals($pledge['values'][$pledgeID]['contact_id'], $this->_individualId, 'In line ' . __LINE__);
        $this->assertEquals($pledge['values'][$pledgeID]['pledge_status'], 'Cancelled', 'In line ' . __LINE__);
        $pledge   =& civicrm_api3_pledge_delete($new_params);
        $this->assertEquals( $pledge['is_error'], 0, 'In line ' . __LINE__ );

    }

///////////////// civicrm_pledge_delete methods

    function testDeleteEmptyParamsPledge()
    {

        $params = array('version' =>$this->_apiversion );
        $pledge = civicrm_api3_pledge_delete($params);
        $this->assertEquals( $pledge['is_error'], 1 );
        $this->assertEquals( $pledge['error_message'], 'Mandatory key(s) missing from params array: one of (id, pledge_id)' );
    }
    
    
    function testDeleteParamsNotArrayPledge()
    {
        $params = 'pledge_id= 1';                            
        $pledge = civicrm_api3_pledge_delete($params);
        $this->assertEquals( $pledge['is_error'], 1 );
        $this->assertEquals( $pledge['error_message'], 'Input variable `params` is not an array' );
    }

     
    function testDeleteWrongParamPledge()
    {
        $params = array( 'pledge_source' => 'SSF',
                         'version'			 =>$this->_apiversion );
        $pledge =& civicrm_api3_pledge_delete( $params );
        $this->assertEquals($pledge['is_error'], 1);
        $this->assertEquals( $pledge['error_message'], 'Mandatory key(s) missing from params array: one of (id, pledge_id)' );
    }
    
    
    function testDeletePledge()
    {

        $pledgeID = $this->pledgeCreate( $this->_individualId  );
        $params         = array( 'pledge_id' => $pledgeID,
                                  'version'  => $this->_apiversion );
        $result   = civicrm_api3_pledge_delete( $params );
        $this->documentMe($params,$result,__FUNCTION__,__FILE__); 
        $this->assertEquals( $result['is_error'], 0 );

    }
    
    /*
     * test to make sure suite has deleted all pledges
     */
    function testCheckTidyUpofPledge(){
      $result = civicrm_api3_pledge_get(array('version' => 3));
      $this->assertEquals(0, $result['is_error'], 'in line ' . __LINE__);
      $this->assertEquals(0, $result['count'], 'in line ' . __LINE__);
    }

}