<?php
;
require_once 'CiviTest/CiviUnitTestCase.php';

class api_v3_GrantTest extends CiviUnitTestCase 
{
    protected $_apiversion = 3;
    protected $params;
    protected $ids = array();
    protected $_entity = 'Grant';
    public $DBResetRequired = false;  
    function setUp() 
    {   
        parent::setUp();
        $this->ids['contact'][0] = $this->individualCreate();
        $this->params = array('version' =>3,
                              'contact_id'   =>  $this->ids['contact'][0],
                              'application_received_date' => 'now',
                              'decision_date' => 'next Monday',
                              'amount_total'  => '500',
                              'status_id'     => 1,
                              'rationale'     => 'Just Because',
                              'currency'      => 'USD',
                              'grant_type_id' => 1,

                           );

    }

    function tearDown() 
    {  
      foreach ($this->ids as $entity => $entities) {
        foreach ($entities as $id){
          civicrm_api($entity, 'delete', array('version' => $this->_apiversion, 'id' => $id));
        }
      }
    }

   public function testCreateGrant () {
        $result = civicrm_api($this->_entity,'create',$this->params);
        $this->documentMe($this->params,$result,__FUNCTION__,__FILE__); 
        $this->assertAPISuccess($result, 'In line ' . __LINE__ );
        $this->assertEquals( 1, $result['count'], 'In line ' . __LINE__ );
        $this->assertNotNull( $result['values'][$result['id']]['id'], 'In line ' . __LINE__ );
        $this->getAndCheck($this->params, $result['id'], $this->_entity);
    }

   public function testGetGrant () {
        $result = civicrm_api($this->_entity,'create',$this->params);
        $this->ids['grant'][0] = $result['id'];
        $result = civicrm_api($this->_entity,'get', array('version' => $this->_apiversion, 'rationale' => 'Just Because'));
        $this->documentMe($this->params,$result,__FUNCTION__,__FILE__); 
        $this->assertAPISuccess($result, 'In line ' . __LINE__ );
        $this->assertEquals( 1, $result['count'], 'In line ' . __LINE__ );

    }

   public function testDeleteGrant () {
        $result = civicrm_api($this->_entity,'create',$this->params);
        $result = civicrm_api($this->_entity,'delete',array('version' =>3,'id' => $result['id']));
        $this->documentMe($this->params,$result,__FUNCTION__,__FILE__); 
        $this->assertAPISuccess($result, 'In line ' . __LINE__ );
        $checkDeleted = civicrm_api($this->_entity,'get',array('version' =>3,));
        $this->assertEquals( 0, $checkDeleted['count'], 'In line ' . __LINE__ );
        
    }
    
}

