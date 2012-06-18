<?php
require_once 'CiviTest/CiviUnitTestCase.php';
class CRM_Utils_SignerTest extends CiviUnitTestCase {
  function get_info() {
    return array(
      'name' => 'Signer Test',
      'description' => 'Test array-signing functions',
      'group' => 'CiviCRM BAO Tests',
    );
  }

  function setUp() {
    parent::setUp();
  }

  function testSignValidate() {
    $cases = array();
    $cases[] = array(
      'signParams' => array(
        'a' => 'eh',
        'b' => 'bee',
        'c' => NULL,
      ),
      'validateParams' => array(
        'a' => 'eh',
        'b' => 'bee',
        'c' => NULL,
      ),
      'isValid' => TRUE,
    );
    $cases[] = array(
      'signParams' => array(
        'a' => 'eh',
        'b' => 'bee',
        'c' => NULL,
      ),
      'validateParams' => array(
        'a' => 'eh',
        'b' => 'bee',
        'c' => NULL,
        'irrelevant' => 'totally-irrelevant',
      ),
      'isValid' => TRUE,
    );
    $cases[] = array(
      'signParams' => array(
        'a' => 'eh',
        'b' => 'bee',
        'c' => NULL,
      ),
      'validateParams' => array(
        'a' => 'eh',
        'b' => 'bee',
        'c' => '',
      ),
      'isValid' => TRUE,
    );
    $cases[] = array(
      'signParams' => array(
        'a' => 'eh',
        'b' => 'bee',
        'c' => NULL,
      ),
      'validateParams' => array(
        'a' => 'eh',
        'b' => 'bee',
        'c' => 0,
      ),
      'isValid' => FALSE,
    );
    $cases[] = array(
      'signParams' => array(
        'a' => 'eh',
        'b' => 'bee',
        'c' => 0,
      ),
      'validateParams' => array(
        'a' => 'eh',
        'b' => 'bee',
        'c' => NULL,
      ),
      'isValid' => FALSE,
    );
    $cases[] = array(
      'signParams' => array(
        'a' => 'eh',
        'b' => 'bee',
        'c' => NULL,
      ),
      'validateParams' => array(
        'a' => 'eh',
        'b' => 'bay',
        'c' => NULL,
      ),
      'isValid' => FALSE,
    );
    $cases[] = array(
      'signParams' => array(
        'a' => 'eh',
        'b' => 'bee',
        'c' => NULL,
      ),
      'validateParams' => array(
        'a' => 'eh',
        'b' => 'bee',
        'c' => FALSE,
      ),
      'isValid' => FALSE,
    );
    $cases[] = array(
      'signParams' => array(
        // int
        'a' => 1,
        'b' => 'bee',
      ),
      'validateParams' => array(
        // string
        'a' => '1',
        'b' => 'bee',
      ),
      'isValid' => TRUE,
    );

    foreach ($cases as $caseId => $case) {
      require_once 'CRM/Utils/Signer.php';
      $signer = new CRM_Utils_Signer('secret', array('a', 'b', 'c'));
      $signature = $signer->sign($case['signParams']);
      // arbitrary
      $this->assertTrue(!empty($signature) && is_string($signature));

      // same as $signer but physically separate
      $validator = new CRM_Utils_Signer('secret', array('a', 'b', 'c'));
      $isValid = $validator->validate($signature, $case['validateParams']);

      if ($isValid !== $case['isValid']) {
        $this->fail("Case ${caseId}: Mismatch: " . var_export($case, TRUE));
      }
      $this->assertTrue(TRUE, 'Validation yielded expected result');
    }
  }
}

