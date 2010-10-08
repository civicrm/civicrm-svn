<?php

require_once 'PHPUnit/Extensions/SeleniumTestCase.php';

class WebTest_Mailing_MailingNewTest extends PHPUnit_Extensions_SeleniumTestCase
{
  protected function setUp()
  {
    $this->setBrowser("*chrome");
    $this->setBrowserUrl("http://www.yahoo.com");
  }

  public function testNewMailing()
  {
    $this->open($this->sandboxURL);
    $this->type("edit-name", $this->username);
    $this->type("edit-pass", $this->password);
    $this->click("edit-submit");
    $this->waitForPageToLoad("30000");
    $this->click("link=CiviCRM");
    $this->waitForPageToLoad("30000");
    $this->click("//ul[@id='civicrm-menu']/li[7]");
    $this->click("//div[@id='root-menu-div']/div[8]/ul/li[1]/div/a");
    $this->waitForPageToLoad("30000");
    $this->type("name", "test mailing");
    $this->addSelection("includeGroups-f", "label=test mailing");
    $this->click("add");
    $this->click("_qf_Group_next");
    $this->waitForPageToLoad("30000");
    $this->click("_qf_Settings_next");
    $this->waitForPageToLoad("30000");
    $this->type("subject", "test mail");
    $this->click("_qf_Upload_upload");
    $this->waitForPageToLoad("30000");
    $this->click("_qf_Test_next");
    $this->waitForPageToLoad("30000");
    $this->click("_qf_Schedule_next");
    $this->waitForPageToLoad("30000");
  }
}
?>
