<?php
require_once 'CiviTest/CiviSeleniumTestCase.php';

class WebTest_Admin_LocalizationTest extends CiviSeleniumTestCase
{

  protected function setUp()
  {
      parent::setUp();
  }

  function testDefaultCountryIsEnabled()
  {
    $this->open("/");
    $this->type("edit-name", "joe");
    $this->type("edit-pass", "my");
    $this->type("edit-pass", "myd620c34alpha2");
    $this->click("edit-submit");
    $this->waitForPageToLoad("30000");
    $this->click("link=CiviCRM");
    $this->waitForPageToLoad("30000");
    $this->click("//ul[@id='civicrm-menu']/li[10]");
    $this->click("//div[@id='root-menu-div']/div[11]/ul/li[8]/div/a");
    $this->waitForPageToLoad("30000");
    $this->addSelection("countryLimit-t", "label=United States");
    $this->click("//select[@id='countryLimit-t']/option");
    $this->click("//input[@name='remove' and @value='<< Remove' and @type='button' and @onclick=\"QFAMS.moveSelection('countryLimit', this.form.elements['countryLimit-f[]'], this.form.elements['countryLimit-t[]'], this.form.elements['countryLimit[]'], 'remove', 'none'); return false;\"]");
    $this->addSelection("countryLimit-f", "label=Afghanistan");
    $this->removeSelection("countryLimit-f", "label=Afghanistan");
    $this->addSelection("countryLimit-f", "label=Cambodia");
    $this->removeSelection("countryLimit-f", "label=Cambodia");
    $this->addSelection("countryLimit-f", "label=Cameroon");
    $this->removeSelection("countryLimit-f", "label=Cameroon");
    $this->addSelection("countryLimit-f", "label=Canada");
    $this->click("//input[@name='add' and @value='Add >>' and @type='button' and @onclick=\"QFAMS.moveSelection('countryLimit', this.form.elements['countryLimit-f[]'], this.form.elements['countryLimit-t[]'], this.form.elements['countryLimit[]'], 'add', 'none'); return false;\"]");
    $this->click("_qf_Localization_next-bottom");
    $this->waitForPageToLoad("30000");
    try {
        $this->assertFalse($this->isTextPresent("Your changes have been saved."));
    } catch (PHPUnit_Framework_AssertionFailedError $e) {
        array_push($this->verificationErrors, $e->toString());
    }
  }
}
?>