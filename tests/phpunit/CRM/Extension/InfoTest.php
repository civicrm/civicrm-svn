<?php

require_once 'CiviTest/CiviUnitTestCase.php';

class CRM_Extension_InfoTest extends CiviUnitTestCase {
  function setUp() {
    parent::setUp();
    $this->file = tempnam(sys_get_temp_dir(), 'infoxml-');
  }

  function tearDown() {
    unlink($this->file);
    parent::tearDown();
  }

  function testGood() {
    file_put_contents($this->file, "<extension key='test.foo' type='module'><file>foo</file><typeInfo><extra>zamboni</extra></typeInfo></extension>");

    $info = CRM_Extension_Info::loadFromFile($this->file);
    $this->assertEquals('test.foo', $info->key);
    $this->assertEquals('foo', $info->file);
    $this->assertEquals('zamboni', $info->typeInfo['extra']);
  }

  function testBad() {
    // <file> vs file>
    file_put_contents($this->file, "<extension key='test.foo' type='module'>file>foo</file></extension>");

    $exc = NULL;
    try {
      $info = CRM_Extension_Info::loadFromFile($this->file);
    } catch (CRM_Extension_Exception $e) {
      $exc = $e;
    }
    $this->assertTrue(is_object($exc));
  }
}
