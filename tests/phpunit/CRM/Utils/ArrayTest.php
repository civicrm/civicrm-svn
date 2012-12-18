<?php
require_once 'CiviTest/CiviUnitTestCase.php';
class CRM_Utils_ArrayTest extends CiviUnitTestCase {
  function testBreakReference() {
    // Get a reference and make a change
    $fooRef1 = self::returnByReference();
    $this->assertEquals('original', $fooRef1['foo']);
    $fooRef1['foo'] = 'modified';

    // Make sure that the referenced item was actually changed
    $fooRef2 = self::returnByReference();
    $this->assertEquals('modified', $fooRef1['foo']);
    $this->assertEquals('modified', $fooRef2['foo']);

    // Get a non-reference, make a change, and make sure the references were unaffected.
    $fooNonReference = CRM_Utils_Array::breakReference(self::returnByReference());
    $fooNonReference['foo'] = 'privately-modified';
    $this->assertEquals('modified', $fooRef1['foo']);
    $this->assertEquals('modified', $fooRef2['foo']);
    $this->assertEquals('privately-modified', $fooNonReference['foo']);
  }

  private function &returnByReference() {
    static $foo;
    if ($foo === NULL) {
      $foo['foo'] = 'original';
    }
    return $foo;
  }

}
