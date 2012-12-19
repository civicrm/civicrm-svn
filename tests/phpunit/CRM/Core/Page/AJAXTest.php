<?php

require_once 'CiviTest/CiviUnitTestCase.php';

class CRM_Core_Page_AJAXTest extends CiviUnitTestCase {
  public function testCheckAuthz() {
    $cases = array();

    $cases[] = array('method', 'CRM_Foo::method', FALSE);
    $cases[] = array('method', 'CRM_Foo_Page_AJAX_Bar::method', FALSE);
    $cases[] = array('method', 'CRM_Foo_Page_AJAX::method', TRUE);
    $cases[] = array('method', 'CRM_Foo_Page_AJAX::method(', FALSE);
    $cases[] = array('method', 'CRM_Foo_Page_AJAX::method()', FALSE);
    $cases[] = array('method', 'othermethod;CRM_Foo_Page_AJAX::method', FALSE);
    $cases[] = array('method', 'CRM_Foo_Page_AJAX::method;othermethod', FALSE);
    $cases[] = array('method', 'CRM_Foo_Page_Inline_Bar', FALSE);
    $cases[] = array('method', 'CRM_Foo_Page_Inline_Bar::method', FALSE);
    $cases[] = array('method', 'CRM_Foo->method', FALSE);

    $cases[] = array('page', 'CRM_Foo', FALSE);
    $cases[] = array('page', 'CRM_Foo_Bar', FALSE);
    $cases[] = array('page', 'CRM_Foo_Page', FALSE);
    $cases[] = array('page', 'CRM_Foo_Page_Bar', FALSE);
    $cases[] = array('page', 'CRM_Foo_Page_Inline', FALSE);
    $cases[] = array('page', 'CRM_Foo_Page_Inline_Bar', TRUE);
    $cases[] = array('page', 'CRM_Foo_Page_Inline_Bar_Bang', FALSE);
    $cases[] = array('page', 'othermethod;CRM_Foo_Page_Inline_Bar', FALSE);
    $cases[] = array('page', 'CRM_Foo_Page_Inline_Bar;othermethod', FALSE);
    $cases[] = array('page', 'CRM_Foo_Form', FALSE);
    $cases[] = array('page', 'CRM_Foo_Form_Bar', FALSE);
    $cases[] = array('page', 'CRM_Foo_Form_Inline', FALSE);
    $cases[] = array('page', 'CRM_Foo_Form_Inline_Bar', TRUE);
    $cases[] = array('page', 'CRM_Foo_Form_Inline_Bar_Bang', FALSE);
    $cases[] = array('page', 'othermethod;CRM_Foo_Form_Inline_Bar', FALSE);
    $cases[] = array('page', 'CRM_Foo_Form_Inline_Bar;othermethod', FALSE);

    // aliases for 'page'
    $cases[] = array('class', 'CRM_Foo_Bar', FALSE);
    $cases[] = array('class', 'CRM_Foo_Page_Inline_Bar', TRUE);
    $cases[] = array('', 'CRM_Foo_Bar', FALSE);
    $cases[] = array('', 'CRM_Foo_Page_Inline_Bar', TRUE);
    
    // invalid type
    $cases[] = array('invalidtype', 'CRM_Foo_Page_Inline_Bar', FALSE);
    $cases[] = array('invalidtype', 'CRM_Foo_Page_AJAX::method', FALSE);

    foreach ($cases as $case) {
      list ($type, $class_name, $expectedResult) = $case;
      $actualResult = CRM_Core_Page_AJAX::checkAuthz($type, $class_name);
      $this->assertEquals($expectedResult, $actualResult, sprintf('Check type=[%s] value=[%s]', $type, $class_name));
    }
  }
}
