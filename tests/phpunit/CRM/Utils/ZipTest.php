<?php
require_once 'CiviTest/CiviUnitTestCase.php';
class CRM_Utils_ZipTest extends CiviUnitTestCase {
  function get_info() {
    return array(
      'name' => 'Zip Test',
      'description' => 'Test Zip Functions',
      'group' => 'CiviCRM BAO Tests',
    );
  }

  function setUp() {
    parent::setUp();
    $this->file = FALSE;
  }
  
  function tearDown() {
    parent::tearDown();
    if ($this->file) {
      unlink($this->file);
    }
  }

  function testFindBaseDirName_normal() {
    $this->_doTest('author-com.example.foo-random/',
      array('author-com.example.foo-random'),
      array('author-com.example.foo-random/README.txt' => 'hello')
    );
  }

  function testFindBaseDirName_0() {
    $this->_doTest('0/',
      array('0'),
      array()
    );
  }
  
  function testFindBaseDirName_plainfile() {
    $this->_doTest(FALSE,
      array(),
      array('README.txt' => 'hello')
    );
  }

  function testFindBaseDirName_twodir() {
    $this->_doTest(FALSE,
      array('dir-1', 'dir-2'),
      array('dir-1/README.txt' => 'hello')
    );
  }

  function testFindBaseDirName_dirfile() {
    $this->_doTest(FALSE,
      array('dir-1'),
      array('dir-1/README.txt' => 'hello', 'MANIFEST.MF' => 'extra')
    );
  }

  function testFindBaseDirName_dot() {
    $this->_doTest(FALSE,
      array('.'),
      array('./README.txt' => 'hello')
    );
  }

  function testFindBaseDirName_dots() {
    $this->_doTest(FALSE,
      array('..'),
      array('../README.txt' => 'hello')
    );
  }

  function testFindBaseDirName_weird() {
    $this->_doTest(FALSE,
      array('foo/../'),
      array('foo/../README.txt' => 'hello')
    );
  }
  
  function _doTest($expectedBaseDirName, $dirs, $files) {
    $this->file = tempnam(sys_get_temp_dir(), 'testzip-');
    $this->assertTrue(CRM_Utils_Zip::createTestZip($this->file, $dirs, $files));
    
    $zip = new ZipArchive();
    $this->assertTrue($zip->open($this->file));
    $this->assertEquals($expectedBaseDirName, CRM_Utils_Zip::findBaseDirName($zip));
  }
}
