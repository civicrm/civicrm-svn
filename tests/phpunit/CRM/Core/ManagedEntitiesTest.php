<?php

require_once 'CiviTest/CiviUnitTestCase.php';

class CRM_Core_ManagedEntitiesTest extends CiviUnitTestCase {
  function get_info() {
    return array(
      'name'    => 'ManagedEntities',
      'description' => 'Test automatic creation/deletion of entities',
      'group'     => 'Core',
    );
  }

  function setUp() {
    parent::setUp();
    $this->modules = array(
      'one' => new CRM_Core_Module('com.example.one', TRUE),
      'two' => new CRM_Core_Module('com.example.two', TRUE),
    );
  }

  function tearDown() {
    parent::tearDown();
  }

  /**
   * Set up an active module and, over time, the hook implementation changes
   * to (1) create 'foo' entity, (2) create 'bar' entity', (3) remove 'foo'
   * entity
   */
  function testAddRemoveEntitiesModule() {
    $decls = array();

    // create first managed entity ('foo')
    $decls[] = array(
      'module' => 'com.example.one',
      'name' => 'foo',
      'entity' => 'CustomSearch',
      'params' => array(
        'version' => 3,
        'class_name' => 'CRM_Example_One_Foo',
        'is_reserved' => 1,
      ),
    );
    $me = new CRM_Core_ManagedEntities($this->modules, $decls);
    $me->reconcile();
    $foo = $me->get('com.example.one', 'foo');
    $this->assertEquals('CRM_Example_One_Foo', $foo['name']);

    // later on, hook returns an extra managed entity ('bar')
    $decls[] = array(
      'module' => 'com.example.one',
      'name' => 'bar',
      'entity' => 'CustomSearch',
      'params' => array(
        'version' => 3,
        'class_name' => 'CRM_Example_One_Bar',
        'is_reserved' => 1,
      ),
    );
    $me = new CRM_Core_ManagedEntities($this->modules, $decls);
    $me->reconcile();
    $foo = $me->get('com.example.one', 'foo');
    $this->assertEquals('CRM_Example_One_Foo', $foo['name']);
    $bar = $me->get('com.example.one', 'bar');
    $this->assertEquals('CRM_Example_One_Bar', $bar['name']);

    // and then hook changes its mind, removing 'foo'
    unset($decls[0]);
    $me = new CRM_Core_ManagedEntities($this->modules, $decls);
    $me->reconcile();
    $foo = $me->get('com.example.one', 'foo');
    $this->assertTrue($foo === NULL);
    $bar = $me->get('com.example.one', 'bar');
    $this->assertEquals('CRM_Example_One_Bar', $bar['name']);
  }

  /**
   * Set up an active module with one managed-entity and, over
   * time, the content of the entity changes
   */
  function testModifyDeclaration() {
    $decls = array();

    // create first managed entity ('foo')
    $decls[] = array(
      'module' => 'com.example.one',
      'name' => 'foo',
      'entity' => 'CustomSearch',
      'params' => array(
        'version' => 3,
        'class_name' => 'CRM_Example_One_Foo',
        'is_reserved' => 1,
      ),
    );
    $me = new CRM_Core_ManagedEntities($this->modules, $decls);
    $me->reconcile();
    $foo = $me->get('com.example.one', 'foo');
    $this->assertEquals('CRM_Example_One_Foo', $foo['name']);

    // later on, hook specification changes
    $decls[0]['params']['class_name'] = 'CRM_Example_One_Foobar';
    $me = new CRM_Core_ManagedEntities($this->modules, $decls);
    $me->reconcile();
    $foo = $me->get('com.example.one', 'foo');
    $this->assertEquals('CRM_Example_One_Foobar', $foo['name']);
  }

  /**
   * Setup an active module with a malformed entity declaration
   */
  function testInvalidDeclarationModule() {
    // create first managed entity ('foo')
    $decls = array();
    $decls[] = array(
      'module' => 'com.example.unknown', // erroneous
      'name' => 'foo',
      'entity' => 'CustomSearch',
      'params' => array(
        'version' => 3,
        'class_name' => 'CRM_Example_One_Foo',
        'is_reserved' => 1,
      ),
    );
    $me = new CRM_Core_ManagedEntities($this->modules, $decls);
    try {
      $me->reconcile();
      $this->fail('Expected exception when using invalid declaration');
    } catch (Exception $e) {
     // good
    }
  }

  /**
   * Setup an active module with a malformed entity declaration
   */
  function testMissingName() {
    // create first managed entity ('foo')
    $decls = array();
    $decls[] = array(
      'module' => 'com.example.unknown',
      'name' => NULL, // erroneous
      'entity' => 'CustomSearch',
      'params' => array(
        'version' => 3,
        'class_name' => 'CRM_Example_One_Foo',
        'is_reserved' => 1,
      ),
    );
    $me = new CRM_Core_ManagedEntities($this->modules, $decls);
    try {
      $me->reconcile();
      $this->fail('Expected exception when using invalid declaration');
    } catch (Exception $e) {
     // good
    }
  }

  /**
   * Setup an active module with a malformed entity declaration
   */
  function testMissingEntity() {
    // create first managed entity ('foo')
    $decls = array();
    $decls[] = array(
      'module' => 'com.example.unknown',
      'name' => 'foo',
      'entity' => NULL, // erroneous
      'params' => array(
        'version' => 3,
        'class_name' => 'CRM_Example_One_Foo',
        'is_reserved' => 1,
      ),
    );
    $me = new CRM_Core_ManagedEntities($this->modules, $decls);
    try {
      $me->reconcile();
      $this->fail('Expected exception when using invalid declaration');
    } catch (Exception $e) {
     // good
    }
  }

  /**
   * Setup an active module with an entity -- then disable and re-enable the
   * module
   */
  function testDeactivateReactivateModule() {
    // create first managed entity ('foo')
    $decls = array();
    $decls[] = array(
      'module' => 'com.example.one',
      'name' => 'foo',
      'entity' => 'CustomSearch',
      'params' => array(
        'version' => 3,
        'class_name' => 'CRM_Example_One_Foo',
        'is_reserved' => 1,
      ),
    );
    $me = new CRM_Core_ManagedEntities($this->modules, $decls);
    $me->reconcile();
    $foo = $me->get('com.example.one', 'foo');
    $this->assertEquals(1, $foo['is_active']);
    $this->assertEquals('CRM_Example_One_Foo', $foo['name']);

    // now deactivate module, which has empty decls and which cascades to managed object
    $this->modules['one']->is_active = FALSE;
    $me = new CRM_Core_ManagedEntities($this->modules, array());
    $me->reconcile();
    $foo = $me->get('com.example.one', 'foo');
    $this->assertEquals(0, $foo['is_active']);
    $this->assertEquals('CRM_Example_One_Foo', $foo['name']);

    // and reactivate module, which again provides decls and which cascades to managed object
    $this->modules['one']->is_active = TRUE;
    $me = new CRM_Core_ManagedEntities($this->modules, $decls);
    $me->reconcile();
    $foo = $me->get('com.example.one', 'foo');
    $this->assertEquals(1, $foo['is_active']);
    $this->assertEquals('CRM_Example_One_Foo', $foo['name']);
  }

  /**
   * Setup an active module with an entity -- then entirely uninstall the
   * module
   */
  function testUninstallModule() {
    // create first managed entity ('foo')
    $decls = array();
    $decls[] = array(
      'module' => 'com.example.one',
      'name' => 'foo',
      'entity' => 'CustomSearch',
      'params' => array(
        'version' => 3,
        'class_name' => 'CRM_Example_One_Foo',
        'is_reserved' => 1,
      ),
    );
    $me = new CRM_Core_ManagedEntities($this->modules, $decls);
    $me->reconcile();
    $foo = $me->get('com.example.one', 'foo');
    $this->assertEquals('CRM_Example_One_Foo', $foo['name']);

    // then destory module; note that decls go away
    unset($this->modules['one']);
    $me = new CRM_Core_ManagedEntities($this->modules, array());
    $me->reconcile();
    $foo = $me->get('com.example.one', 'foo');
    $this->assertTrue(NULL === $foo);
  }
}
