<?php
require_once '/var/www/api.dev.civicrm.org/trunk/tools/packages/PHPUnit/Extensions/SeleniumTestCase.php';
 
class WebTest_Generic_CheckDashboardTest extends PHPUnit_Extensions_SeleniumTestCase
{
    protected $captureScreenshotOnFailure = TRUE;
    protected $screenshotPath = '/var/www/api.dev.civicrm.org/public/sc';
    protected $screenshotUrl = 'http://api.dev.civicrm.org/sc/';


  function setUp()
  {
    $this->setBrowser('*firefox');
    $this->setBrowserUrl("http://sandbox.civicrm.org/");

  }

  function testCheckDashboardElements()
  {
    $this->open("/");
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
