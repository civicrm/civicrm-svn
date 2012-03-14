<?php

require_once 'CiviTest/CiviUnitTestCase.php';

class api_v3_WebsiteTest extends CiviUnitTestCase 
{
    protected $_apiversion;
    protected $params;
    protected $id;
    protected $_entity;
    public $DBResetRequired = false;  
    function setUp() 
    {
        parent::setUp();

        $this->_entity = 'website';
        $this->_apiversion = 3;
        $this->_contactID    = $this->organizationCreate( );
        $this->params = array('version' =>3,
                              'contact_id'   => $this->_contactID,
                              'url'   => 'website.com',
                              'website_type_id' => 1,

                           );
    }

    function tearDown() 
    {  
    }

   public function testCreateWebsite () {
        $result = civicrm_api($this->_entity,'create',$this->params);
        $this->documentMe($this->params,$result,__FUNCTION__,__FILE__); 
        $this->assertAPISuccess($result, 'In line ' . __LINE__ );
        $this->assertEquals( 1, $result['count'], 'In line ' . __LINE__ );
        $this->getAndCheck($this->params, $result['id'],$this->_entity);
        $this->assertNotNull( $result['values'][$result['id']]['id'], 'In line ' . __LINE__ );           

    }

   public function testGetWebsite () {
        $result = civicrm_api($this->_entity,'create',$this->params);
        $result = civicrm_api($this->_entity,'get',$this->params);
        $this->documentMe($this->params,$result,__FUNCTION__,__FILE__); 
        $this->assertAPISuccess($result, 'In line ' . __LINE__ );
        $this->assertEquals( 1, $result['count'], 'In line ' . __LINE__ );
        $this->assertNotNull( $result['values'][$result['id']]['id'], 'In line ' . __LINE__ );           
        civicrm_api('website','delete',array('version' =>3,'id' => $result['id']));
    }

   public function testDeleteWebsite () {
        $result = civicrm_api($this->_entity,'create',$this->params);
        $entity = civicrm_api($this->_entity,'get',$this->params);   
        $result = civicrm_api($this->_entity,'delete',array('version' =>3,'id' => $entity['id']));
        $this->documentMe($this->params,$result,__FUNCTION__,__FILE__); 
        $this->assertAPISuccess($result, 'In line ' . __LINE__ );
        $checkDeleted = civicrm_api($this->_entity,'get',array('version' =>3,));
        $this->assertEquals( 0, $checkDeleted['count'], 'In line ' . __LINE__ );
        
    }

}

