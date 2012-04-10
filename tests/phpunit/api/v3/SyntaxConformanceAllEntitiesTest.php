<?php
require_once 'api/api.php';
require_once 'CiviTest/CiviUnitTestCase.php';

class api_v3_SyntaxConformanceAllEntitiesTest extends CiviUnitTestCase
{
    protected $_apiversion;

    /* This test case doesn't require DB reset */
    public $DBResetRequired = false;

    /* they are two types of missing APIs:
       - Those that are to be implemented 
         (in some future version when someone steps in -hint hint-). List the entities in toBeImplemented[ {$action} ]
       Those that don't exist
         and that will never exist (eg an obsoleted Entity
         they need to be returned by the function toBeSkipped_{$action} (because it has to be a static method and therefore couldn't access a this->toBeSkipped)
    */

    function setUp()    {
       parent::setUp();
       
       $this->toBeImplemented['get'] = array ('ParticipantPayment', 'Profile','CustomValue','Website','Constant','Job');
       $this->toBeImplemented['create'] = array ('SurveyRespondant','OptionGroup','UFMatch','LocationType');
       $this->toBeImplemented['delete'] = array ('MembershipPayment','OptionGroup','SurveyRespondant','UFJoin','UFMatch','LocationType');
       $this->onlyIDNonZeroCount['get'] = array( 'ActivityType', 'Entity', 'Domain' );
       $this->deprecatedAPI = array('Location', 'ActivityType', 'SurveyRespondant');
    }

    function tearDown()    {
    }


    public static function entities($skip = NULL ) {
        //return array(array ('Tag'), array ('Activity')  ); // uncomment to make a quicker run when adding a test
        $tmp = civicrm_api ('Entity','Get', array ('version' => 3 ));
        if (!is_array ($skip)) {
          $skip = array();
        }
        $tmp = array_diff ($tmp['values'],$skip);
        $entities = array ();
        foreach ($tmp as $e) {
          $entities [] = array ($e);
          
        }
        return $entities;
    }

    public static function entities_get () {
      // all the entities, beside the ones flagged
      return api_v3_SyntaxConformanceAllEntitiesTest::entities (api_v3_SyntaxConformanceAllEntitiesTest::toBeSkipped_get (true));
    }

    public static function entities_create () {
      return api_v3_SyntaxConformanceAllEntitiesTest::entities (api_v3_SyntaxConformanceAllEntitiesTest::toBeSkipped_create (true));
    }

    public static function entities_delete () {
      return api_v3_SyntaxConformanceAllEntitiesTest::entities (api_v3_SyntaxConformanceAllEntitiesTest::toBeSkipped_delete (true));
    }

    public static function toBeSkipped_get ($sequential = false) {
      $entitiesWithoutGet = array ('Mailing','MailingGroup','Location','DeprecatedUtils');
      if ($sequential === true) {
        return $entitiesWithoutGet;
      }
      $entities = array ();
      foreach ($entitiesWithoutGet as $e) {
        $entities [] = array ($e);
      }
      return $entities;
    }


    public static function toBeSkipped_create ($sequential = false) {
        $entitiesWithoutCreate = array ('Mailing','MailingGroup','Constant','Entity','Location', 'Profile','DeprecatedUtils');
      if ($sequential === true) {
        return $entitiesWithoutCreate;
      }
      $entities = array ();
      foreach ($entitiesWithoutCreate as $e) {
        $entities [] = array ($e);
      }
      return $entities;
    }

    public static function toBeSkipped_delete ($sequential = false) {
        $entitiesWithout = array ('Mailing','MailingGroup','Constant','Entity','Location','Domain', 'Profile', 'CustomValue','DeprecatedUtils');
      if ($sequential === true) {
        return $entitiesWithout;
      }
      $entities = array ();
      foreach ($entitiesWithout as $e) {
        $entities [] = array ($e);
      }
      return $entities;
    }



/** testing the _get **/ 
    /**
     * @dataProvider toBeSkipped_get
       entities that don't need a get action
     */
    public function testNotImplemented_get ($Entity) {
        $result = civicrm_api ($Entity,'Get',array('version' => 3));
        $this->assertEquals( 1, $result['is_error'], 'In line ' . __LINE__ );
        $this->assertContains ("API ($Entity,Get) does not exist",$result['error_message']);
    }

    /**
     * @dataProvider entities
     * @expectedException PHPUnit_Framework_Error
     */
    public function testWithoutParam_get ($Entity) {
        // should get php complaining that a param is missing
        $result = civicrm_api ($Entity,'Get');
    }
    
    /**
     * @dataProvider entities
     */
    public function testGetFields($Entity){
      if(in_array($Entity , $this->deprecatedAPI) || $Entity == 'Entity' || $Entity == 'CustomValue' || $Entity == 'MailingGroup'){
        return;
      }
      
      $result = civicrm_api($Entity, 'getfields', array('version' => 3));
      $this->assertTrue(is_array($result['values']), "$Entity ::get fields doesn't return values array in line " . __LINE__);
      foreach ($result['values'] as $key => $value) {
             $this->assertTrue(is_array($value), $Entity . "::" . $key . " is not an array in line " . __LINE__); 
      }
    }
    /**
     * @dataProvider entities_get
     */
    public function testEmptyParam_get ( $Entity ) {

        if (in_array ($Entity,$this->toBeImplemented['get'])) {
            // $this->markTestIncomplete("civicrm_api3_{$Entity}_get to be implemented");
          return;
        }
        $result = civicrm_api ($Entity,'Get',array());
        $this->assertEquals( 1, $result['is_error'], 'In line ' . __LINE__ );
        $this->assertContains ("Mandatory key(s) missing from params array", $result['error_message']);
    }


    /**
     * @dataProvider entities_get
     * @Xdepends testEmptyParam_get // no need to test the simple if the empty doesn't work/is skipped. doesn't seem to work
     */
    public function testSimple_get ($Entity) {
        // $this->markTestSkipped("test gives core error on test server (but not on our locals). Skip until we can get server to pass");
        return;
        if (in_array ($Entity,$this->toBeImplemented['get'])) {
          return;
        }
        $result = civicrm_api ($Entity,'Get',array('version' => 3));
        if ($result['is_error']) { // @TODO: list the get that have mandatory params
          $this->assertContains ("Mandatory key(s) missing from params array", $result['error_message']);
          $this->assertContains ("id", $result['error_message']); // either id or contact_id or entity_id is one of the field missing
        } else {
           $this->assertEquals(3, $result['version']);
           $this->assertArrayHasKey('count', $result);
           $this->assertArrayHasKey('values', $result);
        }
    }

    /**
     * @dataProvider entities_get
     */
    public function testAcceptsOnlyID_get ($Entity) {
        $nonExistantID = 30867307034; // big random number. fun fact: if you multiply it by pi^e, the result is another random number, but bigger ;)
        if (in_array ($Entity,$this->toBeImplemented['get'])) {
          return;
        }

        // FIXME
        // the below function returns different values and hence an early return
        // we'll fix this once beta1 is released
//        return;

        $result = civicrm_api ($Entity,'Get', array('version' => 3, 'id' => $nonExistantID ));

        if ($result['is_error']) {
          $this->assertEquals("only id should be enough", $result['error_message']);//just to get a clearer message in the log
        }
        if ( ! in_array( $Entity, $this->onlyIDNonZeroCount['get'] ) ) {
            $this->assertEquals(0, $result['count']);
        }
    }


    /**
     * @dataProvider entities_get
     */
    public function testNonExistantID_get ($Entity) {
        $nonExistantID = 30867307034; // cf testAcceptsOnlyID_get
        if (in_array ($Entity,$this->toBeImplemented['get'])) {
          return;
        }

        $result = civicrm_api ($Entity,'Get',array('version' => 3, 'id' => $nonExistantID ));

        if ($result['is_error']) { // redundant with testAcceptsOnlyID_get
          return;
        }


        $this->assertArrayHasKey('version', $result);
        $this->assertEquals(3, $result['version']);
        if ( ! in_array( $Entity, $this->onlyIDNonZeroCount['get'] ) ) {
            $this->assertEquals(0, $result['count']);
        }
    }


/** testing the _create **/ 
    /**
     * @dataProvider toBeSkipped_create
       entities that don't need a create action
     */
    public function testNotImplemented_create ($Entity) {
        $result = civicrm_api ($Entity,'Create',array('version' => 3));
        $this->assertEquals( 1, $result['is_error'], 'In line ' . __LINE__ );
        $this->assertContains ("API ($Entity,Create) does not exist",$result['error_message']);
    }

    /**
     * @dataProvider entities
     * @expectedException PHPUnit_Framework_Error
     */
    public function testWithoutParam_create ($Entity) {
        // should create php complaining that a param is missing
        $result = civicrm_api ($Entity,'Create');
    }


    /**
     * @dataProvider entities_create
     */
    public function testEmptyParam_create ($Entity) {
        if (in_array ($Entity,$this->toBeImplemented['create'])) {
            // $this->markTestIncomplete("civicrm_api3_{$Entity}_create to be implemented");
            return;
        }
        $result = civicrm_api ($Entity,'Create',array());
        $this->assertEquals( 1, $result['is_error'], 'In line ' . __LINE__ );
        $this->assertContains ("Mandatory key(s) missing from params array", $result['error_message']);
    }

    /**
     * @dataProvider entities
     */
    public function testCreateWrongTypeParamTag_create () {
        $result = civicrm_api ("Tag",'Create','this is not a string');
        $this->assertEquals( 1, $result['is_error'], 'In line ' . __LINE__ );
        $this->assertEquals ("Input variable `params` is not an array",$result['error_message']);
    }

/** testing the _getFields **/ 
/** testing the _delete **/ 
    /**
     * @dataProvider toBeSkipped_delete
       entities that don't need a delete action
     */
    public function testNotImplemented_delete ($Entity) {
        $nonExistantID = 151416349;
        $result = civicrm_api($Entity, 'Delete', array('version' => 3, 'id' => $nonExistantID));
        $this->assertEquals(1, $result['is_error'], 'In line ' . __LINE__ );
        $this->assertContains("API ($Entity,Delete) does not exist", $result['error_message']);
    }

    /**
     * @dataProvider entities
     * @expectedException PHPUnit_Framework_Error
     */
    public function testWithoutParam_delete ($Entity) {
        // should delete php complaining that a param is missing
        $result = civicrm_api ($Entity,'Delete');
    }


    /**
     * @dataProvider entities_delete
     */
    public function testEmptyParam_delete ($Entity) {
        if (in_array ($Entity,$this->toBeImplemented['delete'])) {
            // $this->markTestIncomplete("civicrm_api3_{$Entity}_delete to be implemented");
            return;
        }
        $result = civicrm_api ($Entity,'Delete',array());
        $this->assertEquals( 1, $result['is_error'], 'In line ' . __LINE__ );
        $this->assertContains ("Mandatory key(s) missing from params array", $result['error_message']);
    }

    /**
     * @dataProvider entities
     */
    public function testDeleteWrongTypeParamTag_delete () {
        $result = civicrm_api ("Tag",'Delete','this is not a string');
        $this->assertEquals( 1, $result['is_error'], 'In line ' . __LINE__ );
        $this->assertEquals ("Input variable `params` is not an array",$result['error_message']);
    }


}
