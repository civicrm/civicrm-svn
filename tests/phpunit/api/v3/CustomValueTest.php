<?php

require_once 'CiviTest/CiviUnitTestCase.php';

class api_v3_CustomValueTest extends CiviUnitTestCase 
{
    protected $_apiversion;
    protected $_individual;
    protected $params;
    protected $id;
    public $DBResetRequired = true;  
    function setUp() 
    {
        $this->_apiversion = 3;
        $this->individual = $this->individualCreate();
        $this->params = array('version' => $this->_apiversion, 
        											'entity_id' => $this->individual);
        $this->ids['single'] = $this->entityCustomGroupWithSingleFieldCreate( 'mySingleField','Contacts');
        $this->ids['multi'] = $this->CustomGroupMultipleCreateWithFields();
        parent::setUp();
    }

    function tearDown() 
    {  
        $this->customFieldDelete($ids['single']['custom_field_id']);
        $this->customGroupDelete($ids['single']['custom_group_id']); 
        $this->customFieldDelete($ids['multi']['custom_field_id'][0]);
        $this->customFieldDelete($ids['multi']['custom_field_id'][1]);
        $this->customGroupDelete($ids['multi']['custom_group_id'][2]); 
    }

   public function testCreateCustomValue () {

     		$params = array('custom_' . $this->ids['single']['custom_field_id'] => 'customString') + $this->params;
        $result = civicrm_api('custom_value','create',$params);
        $this->documentMe($params,$result,__FUNCTION__,__FILE__); 
        $this->assertAPISuccess($result, 'In line ' . __LINE__ );
        $this->assertEquals( 1, $result['count'], 'In line ' . __LINE__ );
        $this->assertNotNull( $result['values'][$result['id']]['id'], 'In line ' . __LINE__ );           

    }
/*
   public function testGetCustomValue () {
     
        $result = civicrm_api('custom_value','get',$this->params);
        $this->documentMe($this->params,$result,__FUNCTION__,__FILE__); 
        $this->assertAPISuccess($result, 'In line ' . __LINE__ );
        $this->assertEquals( 1, $result['count'], 'In line ' . __LINE__ );
        $this->assertNotNull( $result['values'][$result['id']]['id'], 'In line ' . __LINE__ );           
        $this->id = $result['id']; 
    }

   public function testDeleteCustomValue () {
        $entity = civicrm_api('custom_value','get',$this->params);   
        $result = civicrm_api('custom_value','delete',array('version' =>3,'id' => $entity['id']));
        $this->documentMe($this->params,$result,__FUNCTION__,__FILE__); 
        $this->assertAPISuccess($result, 'In line ' . __LINE__ );
        $checkDeleted = civicrm_api('survey','get',array('version' =>3,));
        $this->assertEquals( 0, $checkDeleted['count'], 'In line ' . __LINE__ );
        
    }
    
   public function testGetCustomValueChainDelete () {
        $description = "demonstrates get + delete in the same call";
        $subfile = 'ChainedGetDelete';
        $params = array('version' =>3,
                        'title'   => "survey title",
                        'api.survey.delete' => 1);
        $result = civicrm_api('survey','create',$this->params);   
        $result = civicrm_api('survey','get',$params );    
        $this->documentMe($params,$result,__FUNCTION__,__FILE__,$description,$subfile); 
        $this->assertAPISuccess($result, 'In line ' . __LINE__ );
        $this->assertEquals( 0,civicrm_api('survey','getcount',array('version' => 3)), 'In line ' . __LINE__ );

    } 
    */ 
}

