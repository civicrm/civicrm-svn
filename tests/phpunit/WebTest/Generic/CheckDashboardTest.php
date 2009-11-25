<?php
require_once 'PHPUnit/Extensions/SeleniumTestCase.php';
 
class WebTest_Generic_CheckDashboardTest extends PHPUnit_Extensions_SeleniumTestCase
{

  protected $coverageScriptUrl = 'http://tests.dev.civicrm.org/drupal/phpunit_coverage.php';
    
  function setUp()
  {
    $this->setBrowser('*firefox');
    $this->setBrowserUrl("http://tests.dev.civicrm.org/");
  }

  function testCheckDashboardElements()
  {
    $this->open("/drupal/");
    $this->type("edit-name", "demo");
    $this->type("edit-pass", "demo");
    $this->click("edit-submit");
    $this->waitForPageToLoad("30000");
    $this->click("link=CiviCRM");
    $this->waitForPageToLoad("30000");
    $this->assertTrue($this->isTextPresent("Activities"));
    $this->assertTrue($this->isElementPresent("link=My Contact Dashboard"));
  }

}
?>
