<?php
require_once 'api/api.php';
require_once 'CiviTest/CiviUnitTestCase.php';

class api_v3_SyntaxConformanceAllEntities extends CiviUnitTestCase
{
    protected $_apiversion;

    /* they are two types of missing APIs:
       - Those that are to be implemented 
         (in some future version when someone steps in -hint hint-). List the entities in toBeImplemented[ {$action} ]
       Those that don't exist
         and that will never exist (eg an obsoleted Entity
         they need to be returned by the function toBeSkipped_{$action} (because it has to be a static method and therefore couldn't access a this->toBeSkipped)
    */

    function setUp()    {
       parent::setUp();
       
       $this->toBeImplemented['get'] = array ('UFGroup','UFField','CustomGroup','ParticipantPayment');
       $this->toBeImplemented['create'] = array ();
       $this->toBeImplemented['delete'] = array ();
    }

    function tearDown()    {
    }


    public static function entities_get () {
      // all the entities, beside the ones flagged
      return api_v3_SyntaxConformanceAllEntities::entities (api_v3_SyntaxConformanceAllEntities::toBeSkipped_get (true));
    }

    public static function entities($skip = NULL ) {
//        return array(array ('Tag'), array ('Group')  ); // uncomment to make a quicker run when adding a test
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

    public static function toBeSkipped_get ($sequential = false) {
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
     * @dataProvider entities_get
     */
    public function testEmptyParam_get ($Entity) {

        if (in_array ($Entity,$this->toBeImplemented['get'])) {
          //$this->markTestSkipped("civicrm_api3_{$Entity}_get to be implemented");
          $this->markTestIncomplete("civicrm_api3_{$Entity}_get to be implemented");
          return;
        }
        $result = civicrm_api ($Entity,'Get',array());
        $this->assertEquals( 1, $result['is_error'], 'In line ' . __LINE__ );
        $this->assertContains ("Mandatory key(s) missing from params array", $result['error_message']);
    }

    /**
     * @dataProvider entities
     */
    public function testGetWrongTypeParamTag_get () {
        $result = civicrm_api ("Tag",'Get','this is not a string');
        $this->assertEquals( 1, $result['is_error'], 'In line ' . __LINE__ );
        $this->assertEquals ("Input variable `params` is not an array",$result['error_message']);
    }

/*

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
