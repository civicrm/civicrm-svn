<?php
require_once 'api/v3/Email.php';
require_once 'CiviTest/CiviUnitTestCase.php';

class api_v3_EmailTest extends CiviUnitTestCase 
{
    protected $_apiversion;
    protected $_contactID;
    protected $_locationType;
    function setUp() 
    {
        $this->_apiversion = 3;
        parent::setUp();
        $this->_contactID    = $this->organizationCreate(null);
        $this->_locationType = $this->locationTypeCreate(null); 
    }

    function tearDown() 
    {  
//TODO delete org & location type
    }
   public function testCreateEmail () {
        $params = array('contact_id' => $this->_contactID,
                        'location_type_id' => $this->_locationType->id,
                        'email'            => 'api@a-team.com',
                        'is_primary'       =>1,
                        'version'          =>$this->_apiversion,
        //TODO email_type_id
         );
         
         //check there are no emails to start with
        $get = civicrm_api3_email_get(array('version' => 3,
                                      'location_type_id' => $this->_locationType->id,));
        $this->assertEquals( 0, $get['is_error'], 'In line ' . __LINE__ );       
        $this->assertEquals( 0, $get['count'], 'Contact not successfully deleted In line ' . __LINE__ );        
        $result = civicrm_api3_email_create($params);

        $this->documentMe($params,$result,__FUNCTION__,__FILE__); 
        $this->assertEquals( 0, $result['is_error'], 'In line ' . __LINE__ );
        $this->assertEquals( 1, $result['count'], 'In line ' . __LINE__ );
        $this->assertNotNull( $result['id'], 'In line ' . __LINE__ );
        $this->assertNotNull( $result['values'][$result['id']]['id'], 'In line ' . __LINE__ );
        $delresult = civicrm_api3_email_delete(array('id'=> $result['id'], 'version' => 3));
        $this->assertEquals( 0, $delresult['is_error'], 'In line ' . __LINE__ );

    }
   public function testCreateEmailWithoutEmail(){
      $result = civicrm_api('Email','Create',array('contact_id' => 4, 'version' => 3));
      $this->assertEquals( 1, $result['is_error'], 'In line ' . __LINE__ );
      $this->assertContains( 'missing', $result['error_message'], 'In line ' . __LINE__ );
      $this->assertContains( 'email', $result['error_message'], 'In line ' . __LINE__ );
  
   }    
    
    public function testDeleteEmail () {
        $params = array('contact_id' => $this->_contactID,
                        'location_type_id' => $this->_locationType->id,
                        'email'            => 'api@a-team.com',
                        'is_primary'       =>1,
                        'version'          =>$this->_apiversion,
        //TODO email_type_id
         );
         //check there are no emails to start with
        $get = civicrm_api3_email_get(array('version' => 3,
                                      'location_type_id' => $this->_locationType->id,));
        $this->assertEquals( 0, $get['is_error'], 'In line ' . __LINE__ );       
        $this->assertEquals( 0, $get['count'], 'emailt already exists ' . __LINE__ );        
         
        //create one
        $create = civicrm_api3_email_create($params);
       
        $this->assertEquals( 0, $create['is_error'], 'In line ' . __LINE__ );
        
        $result = civicrm_api3_email_delete(array('id'=> $create['id'], 'version' => 3));
        $this->documentMe($params,$result,__FUNCTION__,__FILE__); 
        $this->assertEquals( 0, $result['is_error'], 'In line ' . __LINE__ );
        $this->assertEquals( 1, $result['count'], 'In line ' . __LINE__ );
        $get = civicrm_api3_email_get(array('version' => 3,
                                      'location_type_id' => $this->_locationType->id,));
        $this->assertEquals( 0, $get['is_error'], 'In line ' . __LINE__ );       
        $this->assertEquals( 0, $get['count'], 'Contact not successfully deleted In line ' . __LINE__ );   
    }



    
}

