<?php
require_once 'api/v3/Campaign.php';
require_once 'CiviTest/CiviUnitTestCase.php';

class api_v3_CampaignTest extends CiviUnitTestCase 
{
    protected $_apiversion;
    protected $params;
    protected $id;
    public $DBResetRequired = false;  
    function setUp() 
    {
        $this->_apiversion = 3;
        $phoneBankActivity =  civicrm_api('Option_value','Get', array('label' => 'PhoneBank','version' =>$this->_apiversion,'sequential' =>1));
        $phoneBankActivityTypeID = $phoneBankActivity['values'][0]['value'];
        $this->params = array('version' =>3,
                              'title'   => "campaign title",
                              'activity_type_id' => $phoneBankActivityTypeID,
                              'max_number_of_contacts' => 12,
                              'instructions'		=> "Call people, ask for money",

                           );
        parent::setUp();
    }

    function tearDown() 
    {  
    }

   public function testCreateCampaign () {
        $result = civicrm_api('campaign', 'create', $this->params);
        $this->documentMe($this->params,$result,__FUNCTION__,__FILE__); 
        $this->assertEquals( 0, $result['is_error'], 'In line ' . __LINE__ );
        $this->assertEquals( 1, $result['count'], 'In line ' . __LINE__ );
        $this->assertNotNull( $result['values'][$result['id']]['id'], 'In line ' . __LINE__ );           

    }

   public function testGetCampaign () {
        $result = civicrm_api('campaign', 'get', ($this->params));
        $this->documentMe($this->params,$result,__FUNCTION__,__FILE__); 
        $this->assertEquals( 0, $result['is_error'], 'In line ' . __LINE__ );
        $this->assertEquals( 1, $result['count'], 'In line ' . __LINE__ );
        $this->assertNotNull( $result['values'][$result['id']]['id'], 'In line ' . __LINE__ );           
        $this->id = $result['id']; 
    }

   public function testDeleteCampaign () {
        $entity = civicrm_api('campaign', 'get', ($this->params));   
        $result = civicrm_api('campaign', 'delete', array('version' =>3,'id' => $entity['id']));
        $this->documentMe($this->params,$result,__FUNCTION__,__FILE__); 
        $this->assertEquals( 0, $result['is_error'], 'In line ' . __LINE__ );

        $checkDeleted = civicrm_api('campaign', 'get', array('version' =>3,));
        $this->assertEquals( 0, $checkDeleted['count'], 'In line ' . __LINE__ );
        
    }
    
    
}

