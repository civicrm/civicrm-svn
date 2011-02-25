<?php
/*
 +--------------------------------------------------------------------+
 | CiviCRM version 3.4                                                |
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
        $this->_individualId = $this->individualCreate(null,$this->_apiversion);
        $this->_pledgeID = $this->pledgeCreate($this->_individualId);
        $this->_contributionID = $this->contributionCreate($this->_individualId, $this->_contributionTypeId);
    }
    
    function tearDown() 
    {
      $this->contributionDelete($this->_contributionID);
    }


    function testGetPledgePayment()
    {
       $params = array('version'	=>$this->_apiversion,
                       );                        
        $result=& civicrm_pledge_payment_get($params);
        $this->documentMe($params,$result,__FUNCTION__,__FILE__); 
        $this->assertEquals(0, $result['is_error'], " in line " . __LINE__);
        $this->assertEquals(5, $result['count'], " in line " . __LINE__);

    }
    

    function testCreatePledgePayment()
    {
      $params = array(
                        'contact_id'             => $this->_individualId,
          							'pledge_id' 						 => $this->_pledgeID,
                        'contribution_id'        => $this->_contributionID,  
                        'version'									=>$this->_apiversion,
                        'status_id'							 => 1,
          
                  );                        
        $result= civicrm_pledge_payment_create($params);
        $this->documentMe($params,$result,__FUNCTION__,__FILE__);
        $this->assertEquals(0, $result['is_error'], " in line " . __LINE__);
        civicrm_pledge_payment_delete($pledgeID);

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
          
                  );                        
        $pledgePayment= civicrm_pledge_payment_create($params);
        $result = civicrm_pledge_payment_delete($pledgePayment['values'][0]);
        $this->documentMe($pledgePayment['values'],$result,__FUNCTION__,__FILE__);
        $this->assertEquals(0, $result['is_error'], " in line " . __LINE__);
        
    }
}
 