<?php
/*
 +--------------------------------------------------------------------+
 | CiviCRM version 3.2                                                |
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

    
    function setUp() 
    {
        $this->_apiversion = 3;    
        parent::setUp();

     //   $this->_pledgeTypeId = 1;
        $this->_individualId = $this->individualCreate(null,$this->_apiversion);
    }
    
    function tearDown() 
    {
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
      //need to set scheduled payment in advance we are running test @ midnight & it becomes unexpectedly overdue
      //due to timezone issues 
       $dayaftertomorrow = mktime(0, 0, 0, date("m"), date("d")+2, date("y"));
      
        $p = array(
                        'contact_id'             => $this->_individualId,
                        'pledge_create_date'    => date('Ymd'),
                        'start_date'    => date('Ymd'),
                        'scheduled_date'    => date('Ymd',$dayaftertomorrow),  
                        'pledge_amount'         => 100.00,
                        'pledge_status_id'         => '2',
                        'contribution_type_id'  => '1',
                        'pledge_original_installment_amount' => 20,
                        'frequency_interval'             => 1,
                        'frequency_unit'             => 'month',
                        'frequency_day'            => 1,
                        'installments'            =>5,
                        'version'						=>$this->_apiversion,
          
                  );                        
        $this->_pledge =& civicrm_pledge_create($p);

        $params = array('pledge_id'=>$this->_pledge['id']);        
        $result =& civicrm_pledge_get($params);
        $this->documentMe($params,$result,__FUNCTION__,__FILE__); 
        $this->assertEquals($result['values'][$this->_pledge['id']]['contact_id'],$this->_individualId); 
        $this->assertEquals($result['values'][$this->_pledge['id']]['pledge_id'],$this->_pledge['id']); 
        $this->assertEquals($result['values'][$this->_pledge['id']]['pledge_create_date'],date('Y-m-d').' 00:00:00'); 
        $this->assertEquals($result['values'][$this->_pledge['id']]['pledge_amount'],100.00);
        $this->assertEquals($result['values'][$this->_pledge['id']]['pledge_status'],'Pending');
        $this->assertEquals($result['values'][$this->_pledge['id']]['pledge_frequency_interval'],1);
        $this->assertEquals($result['values'][$this->_pledge['id']]['pledge_frequency_unit'],'month');
        $this->assertEquals($result['values'][$this->_pledge['id']]['pledge_next_pay_date'],date('Y-m-d',$dayaftertomorrow).' 00:00:00');
        $this->assertEquals($result['values'][$this->_pledge['id']]['pledge_next_pay_amount'],20.00);
        
        $params2 = array( 'pledge_id' => $this->_pledge['id'],
                           'version'  =>$this->_apiversion, );
        $pledge   =& civicrm_pledge_delete($params2);
    }

///////////////// civicrm_pledge_add
     
    function testCreateEmptyParamsPledge()
    {
        $params = array();
        $pledge =& civicrm_pledge_create($params);
        $this->assertEquals( $pledge['is_error'], 1 );
        $this->assertEquals( $pledge['error_message'], 'No input parameters present' );
    }
    

    function testCreateParamsNotArrayPledge()
    {
        $params = 'contact_id= 1';                            
        $pledge =& civicrm_pledge_create($params);
        $this->assertEquals( $pledge['is_error'], 1 );
        $this->assertEquals( $pledge['error_message'], 'Input variable `params` is not an array' );
    }
    
    function testCreateParamsWithoutRequiredKeys()
    {
        $params = array( 'no_required' => 1 );
        $pledge =& civicrm_pledge_create($params);
        $this->assertEquals( $pledge['is_error'], 1 );
        $this->assertEquals( $pledge['error_message'], 'Mandatory key(s) missing from params array: pledge_contact_id, pledge_amount, pledge_original_installment_amount, frequency_interval, frequency_day, start_date, version' );
    }
    function testCreatePledge()
    {
       $dayaftertomorrow = mktime(0, 0, 0, date("m"), date("d")+2, date("y"));
        $params = array(
                        'contact_id'             => $this->_individualId,
                        'pledge_create_date'    => date('Ymd'),
                        'start_date'    => date('Ymd'),
                        'scheduled_date'    => date('Ymd',$dayaftertomorrow),   
                        'pledge_amount'         => 100.00,
                        'pledge_status_id'         => '2',
                        'contribution_type_id'  => '1',
                        'pledge_original_installment_amount' => 20,
                        'frequency_interval'             => 5,
                        'frequency_unit'             => 'year',
                        'frequency_day'            => 15,
                        'installments'            =>5,
                        'sequential'						  =>1,
                        'version'									=>$this->_apiversion,
          
                  );                        
        $result=& civicrm_pledge_create($params);
        $this->documentMe($params,$result,__FUNCTION__,__FILE__); 
        $this->assertEquals($result['values'][0]['amount']     ,100.00, 'In line ' . __LINE__); 
        $this->assertEquals($result['values'][0]['installments'] ,5, 'In line ' . __LINE__); 
        $this->assertEquals($result['values'][0]['frequency_unit'],'year', 'In line ' . __LINE__);
        $this->assertEquals($result['values'][0]['frequency_interval'],5, 'In line ' . __LINE__);
        $this->assertEquals($result['values'][0]['frequency_day'],15, 'In line ' . __LINE__);
        $this->assertEquals($result['values'][0]['original_installment_amount'],20, 'In line ' . __LINE__);
        $this->assertEquals($result['values'][0]['contribution_type_id'],1, 'In line ' . __LINE__);
        $this->assertEquals($result['values'][0]['status_id'],2, 'In line ' . __LINE__);
        $this->assertEquals($result['values'][0]['create_date'],date('Ymd'), 'In line ' . __LINE__);
        $this->assertEquals($result['values'][0]['start_date'],date('Ymd'), 'In line ' . __LINE__);        
        $this->assertEquals($result['values'][0]['is_error'], 0 , 'In line ' . __LINE__);

        $pledgeID = array( 'pledge_id' => $result['values'][0]['pledge_id'] );
        $pledge   =& civicrm_pledge_delete($pledgeID);

    }
    
    
    //To Update Pledge
    function testCreateUpdatePledge()
    {

  // we test 'sequential' param here too     
        $pledgeID = $this->pledgeCreate($this->_individualId,$this->_apiversion);
        $old_params = array(
                            'id' => $pledgeID,  
                            'sequential' =>1,  
                            );
        $original =& civicrm_pledge_get($old_params);
        //Make sure it came back
        $this->assertEquals($original[0]['pledge_id'], $pledgeID, 'In line ' . __LINE__);
        //set up list of old params, verify
        $old_contact_id = $original[0]['contact_id'];
        $old_frequency_unit = $original[0]['pledge_frequency_unit'];
        $old_frequency_interval = $original[0]['pledge_frequency_interval'];
        $old_status_id = $original[0]['pledge_status'];

        
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
                        );
        
        $pledge =& civicrm_pledge_create($params); 
       $this->assertEquals( $pledge['is_error'], 0 );    
        $new_params = array(
                            'id' => $pledge['id'],    
                            );
        $pledge =& civicrm_pledge_get($new_params);
        $this->assertEquals($pledge[$pledgeID]['contact_id'], $this->_individualId, 'In line ' . __LINE__);
        $this->assertEquals($pledge[$pledgeID]['pledge_status'], 'Cancelled', 'In line ' . __LINE__);
        $pledge   =& civicrm_pledge_delete($new_params);
        $this->assertEquals( $pledge['is_error'], 0, 'In line ' . __LINE__ );

    }

///////////////// civicrm_pledge_delete methods

    function testDeleteEmptyParamsPledge()
    {

        $params = array( );
        $pledge = civicrm_pledge_delete($params);
        $this->assertEquals( $pledge['is_error'], 1 );
        $this->assertEquals( $pledge['error_message'], 'Could not find pledge_id in input parameters' );
    }
    
    
    function testDeleteParamsNotArrayPledge()
    {
        $params = 'pledge_id= 1';                            
        $pledge = civicrm_pledge_delete($params);
        $this->assertEquals( $pledge['is_error'], 1 );
        $this->assertEquals( $pledge['error_message'], 'Could not find pledge_id in input parameters' );
    }

     
    function testDeleteWrongParamPledge()
    {
        $params = array( 'pledge_source' => 'SSF' );
        $pledge =& civicrm_pledge_delete( $params );
        $this->assertEquals($pledge['is_error'], 1);
        $this->assertEquals( $pledge['error_message'], 'Could not find pledge_id in input parameters' );
    }
    
    
    function testDeletePledge()
    {

        $pledgeID = $this->pledgeCreate( $this->_individualId , $this->_apiversion );
        $params         = array( 'pledge_id' => $pledgeID,
                                  'version'  => $this->_apiversion );
        $result   = civicrm_pledge_delete( $params );
        $this->documentMe($params,$result,__FUNCTION__,__FILE__); 
        $this->assertEquals( $result['is_error'], 0 );

    }


}