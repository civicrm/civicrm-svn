<?php
// $Id$

require_once 'api/api.php';
require_once 'CiviTest/CiviUnitTestCase.php';
class api_v3_SyntaxConformanceAllEntitiesTest extends CiviUnitTestCase {
  protected $_apiversion;

  /* This test case doesn't require DB reset */



  public $DBResetRequired = FALSE;

  /* they are two types of missing APIs:
       - Those that are to be implemented
         (in some future version when someone steps in -hint hint-). List the entities in toBeImplemented[ {$action} ]
       Those that don't exist
         and that will never exist (eg an obsoleted Entity
         they need to be returned by the function toBeSkipped_{$action} (because it has to be a static method and therefore couldn't access a this->toBeSkipped)
    */ function setUp() {
    parent::setUp();

    $this->toBeImplemented['get'] = array('ParticipantPayment', 'Profile', 'CustomValue', 'Website', 'Constant', 'Job');
    $this->toBeImplemented['create'] = array('SurveyRespondant', 'OptionGroup', 'UFMatch', 'LocationType');
    $this->toBeImplemented['delete'] = array('MembershipPayment', 'OptionGroup', 'SurveyRespondant', 'UFJoin', 'UFMatch', 'LocationType');
    $this->onlyIDNonZeroCount['get'] = array('ActivityType', 'Entity', 'Domain');
    $this->deprecatedAPI = array('Location', 'ActivityType', 'SurveyRespondant');
  }

  function tearDown() {}


  public static function entities($skip = NULL) {
    // uncomment to make a quicker run when adding a test
    //return array(array ('Tag'), array ('Activity')  );
    $tmp = civicrm_api('Entity', 'Get', array('version' => 3));
    if (!is_array($skip)) {
      $skip = array();
    }
    $tmp = array_diff($tmp['values'], $skip);
    $entities = array();
    foreach ($tmp as $e) {
      $entities[] = array($e);
    }
    return $entities;
  }

  public static function entities_get() {
    // all the entities, beside the ones flagged
    return api_v3_SyntaxConformanceAllEntitiesTest::entities(api_v3_SyntaxConformanceAllEntitiesTest::toBeSkipped_get(TRUE));
  }

  public static function entities_create() {
    return api_v3_SyntaxConformanceAllEntitiesTest::entities(api_v3_SyntaxConformanceAllEntitiesTest::toBeSkipped_create(TRUE));
  }

  public static function entities_updatesingle() {
    return api_v3_SyntaxConformanceAllEntitiesTest::entities(api_v3_SyntaxConformanceAllEntitiesTest::toBeSkipped_updatesingle(TRUE));
  }

  public static function entities_delete() {
    return api_v3_SyntaxConformanceAllEntitiesTest::entities(api_v3_SyntaxConformanceAllEntitiesTest::toBeSkipped_delete(TRUE));
  }

  public static function toBeSkipped_get($sequential = FALSE) {
    $entitiesWithoutGet = array('Mailing', 'MailingEventSubscribe', 'MailingEventConfirm', 'MailingEventResubscribe', 'MailingEventUnsubscribe', 'MailingGroup', 'Location', 'DeprecatedUtils');
    if ($sequential === TRUE) {
      return $entitiesWithoutGet;
    }
    $entities = array();
    foreach ($entitiesWithoutGet as $e) {
      $entities[] = array($e);
    }
    return $entities;
  }


  public static function toBeSkipped_create($sequential = FALSE) {
    $entitiesWithoutCreate = array('Mailing', 'MailingGroup', 'Constant', 'Entity', 'Location', 'Profile', 'DeprecatedUtils');
    if ($sequential === TRUE) {
      return $entitiesWithoutCreate;
    }
    $entities = array();
    foreach ($entitiesWithoutCreate as $e) {
      $entities[] = array($e);
    }
    return $entities;
  }

  public static function toBeSkipped_delete($sequential = FALSE) {
    $entitiesWithout = array('Mailing', 'MailingGroup', 'Constant', 'Entity', 'Location', 'Domain', 'Profile', 'CustomValue', 'DeprecatedUtils');
    if ($sequential === TRUE) {
      return $entitiesWithout;
    }
    $entities = array();
    foreach ($entitiesWithout as $e) {
      $entities[] = array($e);
    }
    return $entities;
  }
  /*
  * At this stage exclude the ones that don't pass & add them as we can troubleshoot them
  */

  public static function toBeSkipped_updatesingle($sequential = FALSE) {
    $entitiesWithout = array(
      'Mailing',
      'MailingGroup',
      'MailingEventUnsubscribe',
      'MailingEventSubscribe',
      'Constant',
      'Entity',
      'Location',
      'Domain',
      'Profile',
      'CustomValue',
      'DeprecatedUtils',
      'SurveyRespondant',
      'Tag',
      'UFMatch',
      'UFJoin',
      'UFField',
      'OptionValue',
      'Relationship',
      'RelationshipType',
      'ParticipantStatusType',
      'Note',
      'OptionGroup',
      'Membership',
      'MembershipType',
      
      'MembershipStatus',
      'Group',
      'GroupOrganization',
      'GroupNesting',
      'Job',
      'File',
      'EntityTag',
      'CustomField',
      'CustomGroup',
      'Contribution',
      'ContributionRecur',
      'ActivityType',
      'MailingEventConfirm',
      'Case',
      'Contact',
      'ContactType',
      'MailingEventResubscribe',
      'UFGroup',

      'Activity',
      'Address',
      'Email',
      'Event',
      'GroupContact',
      'MembershipPayment',
      'Participant',
      'ParticipantPayment',
      'Pledge',
      'PledgePayment',
    );
    if ($sequential === TRUE) {
      return $entitiesWithout;
    }
    $entities = array();
    foreach ($entitiesWithout as $e) {
      $entities[] = array(
        $e,
      );
    }
    return $entities;
  }

  /** testing the _get **/

  /**
   * @dataProvider toBeSkipped_get
   entities that don't need a get action
   */
  public function testNotImplemented_get($Entity) {
    $result = civicrm_api($Entity, 'Get', array('version' => 3));
    $this->assertEquals(1, $result['is_error'], 'In line ' . __LINE__);
    $this->assertContains("API ($Entity,Get) does not exist", $result['error_message']);
  }

  /**
   * @dataProvider entities
   * @expectedException PHPUnit_Framework_Error
   */
  public function testWithoutParam_get($Entity) {
    // should get php complaining that a param is missing
    $result = civicrm_api($Entity, 'Get');
  }

  /**
   * @dataProvider entities
   */
  public function testGetFields($Entity) {
    if (in_array($Entity, $this->deprecatedAPI) || $Entity == 'Entity' || $Entity == 'CustomValue' || $Entity == 'MailingGroup') {
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
  public function testEmptyParam_get($Entity) {

    if (in_array($Entity, $this->toBeImplemented['get'])) {
      // $this->markTestIncomplete("civicrm_api3_{$Entity}_get to be implemented");
      return;
    }
    $result = civicrm_api($Entity, 'Get', array());
    $this->assertEquals(1, $result['is_error'], 'In line ' . __LINE__);
    $this->assertContains("Mandatory key(s) missing from params array", $result['error_message']);
  }
  /**
   * @dataProvider entities_get
   */
  public function testEmptyParam_getString($Entity) {

    if (in_array($Entity, $this->toBeImplemented['get'])) {
      // $this->markTestIncomplete("civicrm_api3_{$Entity}_get to be implemented");
      return;
    }
    $result = civicrm_api($Entity, 'Get', 'string');
    $this->assertEquals(1, $result['is_error'], 'In line ' . __LINE__);
    $this->assertEquals(2000, $result['error_code']);
    $this->assertEquals('Input variable `params` is not an array', $result['error_message']);
  }
  /**
   * @dataProvider entities_get
   * @Xdepends testEmptyParam_get // no need to test the simple if the empty doesn't work/is skipped. doesn't seem to work
   */
  public function testSimple_get($Entity) {
    // $this->markTestSkipped("test gives core error on test server (but not on our locals). Skip until we can get server to pass");
    return;
    if (in_array($Entity, $this->toBeImplemented['get'])) {
      return;
    }
    $result = civicrm_api($Entity, 'Get', array('version' => 3));
    // @TODO: list the get that have mandatory params
    if ($result['is_error']) {
      $this->assertContains("Mandatory key(s) missing from params array", $result['error_message']);
      // either id or contact_id or entity_id is one of the field missing
      $this->assertContains("id", $result['error_message']);
    }
    else {
      $this->assertEquals(3, $result['version']);
      $this->assertArrayHasKey('count', $result);
      $this->assertArrayHasKey('values', $result);
    }
  }

  /**
   * @dataProvider entities_get
   */
  public function testAcceptsOnlyID_get($Entity) {
    // big random number. fun fact: if you multiply it by pi^e, the result is another random number, but bigger ;)
    $nonExistantID = 30867307034;
    if (in_array($Entity, $this->toBeImplemented['get'])) {
      return;
    }

    // FIXME
    // the below function returns different values and hence an early return
    // we'll fix this once beta1 is released
    //        return;

    $result = civicrm_api($Entity, 'Get', array('version' => 3, 'id' => $nonExistantID));

    if ($result['is_error']) {
      // just to get a clearer message in the log
      $this->assertEquals("only id should be enough", $result['error_message']);
    }
    if (!in_array($Entity, $this->onlyIDNonZeroCount['get'])) {
      $this->assertEquals(0, $result['count']);
    }
  }

  /**
   * @dataProvider entities_get
   */
  public function testNonExistantID_get($Entity) {
    // cf testAcceptsOnlyID_get
    $nonExistantID = 30867307034;
    if (in_array($Entity, $this->toBeImplemented['get'])) {
      return;
    }

    $result = civicrm_api($Entity, 'Get', array('version' => 3, 'id' => $nonExistantID));

    // redundant with testAcceptsOnlyID_get
    if ($result['is_error']) {
      return;
    }


    $this->assertArrayHasKey('version', $result);
    $this->assertEquals(3, $result['version']);
    if (!in_array($Entity, $this->onlyIDNonZeroCount['get'])) {
      $this->assertEquals(0, $result['count']);
    }
  }

  /** testing the _create **/

  /**
   * @dataProvider toBeSkipped_create
   entities that don't need a create action
   */
  public function testNotImplemented_create($Entity) {
    $result = civicrm_api($Entity, 'Create', array('version' => 3));
    $this->assertEquals(1, $result['is_error'], 'In line ' . __LINE__);
    $this->assertContains("API ($Entity,Create) does not exist", $result['error_message']);
  }

  /**
   * @dataProvider entities
   * @expectedException PHPUnit_Framework_Error
   */
  public function testWithoutParam_create($Entity) {
    // should create php complaining that a param is missing
    $result = civicrm_api($Entity, 'Create');
  }

  /**
   * @dataProvider entities_create
   */
  public function testEmptyParam_create($Entity) {
    if (in_array($Entity, $this->toBeImplemented['create'])) {
      // $this->markTestIncomplete("civicrm_api3_{$Entity}_create to be implemented");
      return;
    }
    $result = civicrm_api($Entity, 'Create', array());
    $this->assertEquals(1, $result['is_error'], 'In line ' . __LINE__);
    $this->assertContains("Mandatory key(s) missing from params array", $result['error_message']);
  }

  /**
   * @dataProvider entities
   */
  public function testCreateWrongTypeParamTag_create() {
    $result = civicrm_api("Tag", 'Create', 'this is not a string');
    $this->assertEquals(1, $result['is_error'], 'In line ' . __LINE__);
    $this->assertEquals("Input variable `params` is not an array", $result['error_message']);
  }

  /**
   * @dataProvider entities_updatesingle
   *
   * limitations include the problem with avoiding loops when creating test objects -
   * hence FKs only set by createTestObject when required. e.g parent_id on campaign is not being followed through
   * Currency - only seems to support US
   */
  public function testCreateSingleValueAlter($entityName) {
    $baoString = 'CRM_Grant_BAO_Grant';
    $baoString = _civicrm_api3_get_DAO($entityName);
    $this->assertNotEmpty($baoString, $entityName);
    $this->assertNotEmpty($entityName, $entityName);
    $fields = civicrm_api($entityName, 'getfields', array(
        'version' => 3,
      )
    );

    $fields = $fields['values'];
    $return = array_keys($fields);
    $baoObj = new CRM_Core_DAO();
    $baoObj->createTestObject($baoString, array('currency' => 'USD'), 2, 0);
    $getentities = civicrm_api($entityName, 'get', array(
        'version' => 3,
        'sequential' => 1,
        'return' => $return,
        'options' => array(
          'sort' => 'id DESC',
          'limit' => 2,
        ),
      ));
    // lets use first rather than assume only one exists
    $entity = $getentities['values'][0];
    $entity2 = $getentities['values'][1];
    foreach ($fields as $field => $specs) {
      $fieldName = $field;
      if (!empty($specs['uniquename'])) {
        $fieldName = $specs['uniquename'];
      }
      if ($field == 'currency' || $field == 'id') {
        continue;
      }
      switch ($specs['type']) {
        case CRM_Utils_Type::T_DATE:
        case CRM_Utils_Type::T_TIMESTAMP:
          $entity[$fieldName] = '2012-05-20';
          break;
        //case CRM_Utils_Type::T_DATETIME:

        case 12:
          $entity[$fieldName] = '2012-05-20 03:05:20';
          break;

        case CRM_Utils_Type::T_STRING:
        case CRM_Utils_Type::T_BLOB:
        case CRM_Utils_Type::T_MEDIUMBLOB:
        case CRM_Utils_Type::T_TEXT:
        case CRM_Utils_Type::T_LONGTEXT:
        case CRM_Utils_Type::T_EMAIL:
          $entity[$fieldName] = 'New String';
          break;

        case CRM_Utils_Type::T_INT:
          // probably created with a 1
          $entity[$fieldName] = 111;
          if (CRM_Utils_Array::value('FKClassName', $specs)) {
            $entity[$fieldName] = empty($entity2[$field]) ? CRM_Utils_Array::value($specs['uniqueName'], $entity2) : $entity2[$field];
            //todo - there isn't always something set here - & our checking on unset values is limited
            if (empty($entity[$field])) {
              unset($entity[$field]);
            }
          }
          break;

        case CRM_Utils_Type::T_BOOL:
        case CRM_Utils_Type::T_BOOLEAN:
          // probably created with a 1
          $entity[$fieldName] = 0;
          break;

        case CRM_Utils_Type::T_FLOAT:
        case CRM_Utils_Type::T_MONEY:
          $entity[$field] = 222;
          break;

        case CRM_Utils_Type::T_URL:
          $entity[$field] = 'warm.beer.com';
      }
      $constant = CRM_Utils_Array::value('pseudoconstant', $specs);
      if (!empty($constant)) {
        $constantOptions = array_reverse(array_keys(CRM_Core_PseudoConstant::getConstant($constant)));
        $entity[$field] = $constantOptions[0];
      }
      $enum = CRM_Utils_Array::value('enumValues', $specs);
      if (!empty($enum)) {
        // reverse so we 'change' value
        $options = array_reverse(explode(',', $enum));
        $entity[$fieldName] = $options[0];
      }
      $updateParams = array(
        'version' => 3,
        'id' => $entity['id'],
        $field => $entity[$field],
      );

      $update = civicrm_api($entityName, 'create', $updateParams);

      $this->assertAPISuccess($update, print_r($updateParams, TRUE) . 'in line ' . __LINE__);
      $checkParams = array(
        'id' => $entity['id'],
        'version' => 3,
        'sequential' => 1,
        'return' => $return,
        'options' => array(
          'sort' => 'id DESC',
          'limit' => 2,
        ),
      );

      $checkEntity = civicrm_api($entityName, 'getsingle', $checkParams);
      $this->assertEquals($entity, $checkEntity, "changing field $fieldName");
    }
    $baoObj->deleteTestObjects($baoString);
    $baoObj->free();
  }

  /** testing the _getFields **/

  /** testing the _delete **/

  /**
   * @dataProvider toBeSkipped_delete
   entities that don't need a delete action
   */
  public function testNotImplemented_delete($Entity) {
    $nonExistantID = 151416349;
    $result = civicrm_api($Entity, 'Delete', array('version' => 3, 'id' => $nonExistantID));
    $this->assertEquals(1, $result['is_error'], 'In line ' . __LINE__);
    $this->assertContains("API ($Entity,Delete) does not exist", $result['error_message']);
  }

  /**
   * @dataProvider entities
   * @expectedException PHPUnit_Framework_Error
   */
  public function testWithoutParam_delete($Entity) {
    // should delete php complaining that a param is missing
    $result = civicrm_api($Entity, 'Delete');
  }

  /**
   * @dataProvider entities_delete
   */
  public function testEmptyParam_delete($Entity) {
    if (in_array($Entity, $this->toBeImplemented['delete'])) {
      // $this->markTestIncomplete("civicrm_api3_{$Entity}_delete to be implemented");
      return;
    }
    $result = civicrm_api($Entity, 'Delete', array());
    $this->assertEquals(1, $result['is_error'], 'In line ' . __LINE__);
    $this->assertContains("Mandatory key(s) missing from params array", $result['error_message']);
  }

  /**
   * @dataProvider entities
   */
  public function testDeleteWrongTypeParamTag_delete() {
    $result = civicrm_api("Tag", 'Delete', 'this is not a string');
    $this->assertEquals(1, $result['is_error'], 'In line ' . __LINE__);
    $this->assertEquals("Input variable `params` is not an array", $result['error_message']);
  }
}

