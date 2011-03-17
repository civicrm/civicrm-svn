<?php
require_once 'api/api.php';
require_once 'CiviTest/CiviUnitTestCase.php';

class api_v3_SyntaxConformanceAllEntities extends CiviUnitTestCase
{
    protected $_apiversion;

    function setUp()    {
       parent::setUp();
    }

    function tearDown()    {
    }


    public static function entities_get () {
      // all the entities, beside the ones flagged
      return api_v3_SyntaxConformanceAllEntities::entities (api_v3_SyntaxConformanceAllEntities::not_implemented_get_apis (true));
    }

    public static function entities($skip = array () ) {
//        return array(array ('Tag'), array ('Group')  );
        $tmp = civicrm_api ('Entity','Get', array ('version' => 3 ));
        $tmp = array_diff ($tmp['values'],$skip);
        $entities = array ();
        foreach ($tmp as $e) {
//          if ($e == "Entity") continue;//Entity is a fake Entity, so we skip it
          $entities [] = array ($e);
          
        }
        return $entities;
    }

    public static function not_implemented_get_apis ($sequential = false) {
      $entitiesWithoutGet = array ('Mailer','MailerGroup','Location');
      if ($sequential === true) {
        return $entitiesWithoutGet;
      }
      $entities = array ();
      foreach ($entitiesWithoutGet as $e) {
        $entities [] = array ($e);
      }
      return $entities;
    }




/** testing the _get **/ 
    /**
     * @dataProvider not_implemented_get_apis
     */
    public function testNotImplemented_get ($Entity) {
        $result = civicrm_api ($Entity,'Get',array('version' => 3));
        $this->assertEquals( 1, $result['is_error'], 'In line ' . __LINE__ );
         $this->assertContains ("API ($Entity,Get) does not exist",$result['error_message']);
    }

    /**
     * @dataProvider entities_get
     */
    public function testEmptyParam_get ($Entity) {
        $result = civicrm_api ($Entity,'Get',array());
        $this->assertEquals( 1, $result['is_error'], 'In line ' . __LINE__ );
        $this->assertContains ("Mandatory key(s) missing from params array", $result['error_message']);
    }


/*
    public function testGetWrongTypeParamTag_get () {
        $result = civicrm_api ($this->EntityName,'Get','aaaaaaaaaaaaaaa');
print_r ($result);
        $this->assertEquals( 1, $result['is_error'], 'In line ' . __LINE__ );
    }


    public function testWithoutVersionTag_get () {
        $result = civicrm_api ($this->EntityName,'Get',array());
print_r ($result);
        $this->assertEquals( 1, $result['is_error'], 'In line ' . __LINE__ );
    }

    public function testSimpleTag_get () {
        $result = civicrm_api ($this->EntityName,'Get',array('version' => 3));
print_r ($result);
        if ($result['is_error']) {
          // that's an Entity that needs at least one filter
          // and civicrm_verify_mandatory shout an error message
        } else { // it returns the list of all the entities
// test if count is set and an >=0
assertGreaterThanOrEqual
// test if value is set and an array
        }
    }

    public function testFetchByIDTag_get () {
        $result = civicrm_api ($this->EntityName,'Get',array('version' => 3, 'debug' => true, 'id' => 0 ));
print_r ($result);
        if ($result['is_error']) {
          // that's an Entity that needs at least one filter
          // and civicrm_verify_mandatory shout an error message
        } else { // it returns the list of all the entities
// test if count is set and an >=0
// test if value is set and an array
        }
    }
*/


/** testing the _create **/ 
/** testing the _getFields **/ 
/** testing the _delete **/ 

}
