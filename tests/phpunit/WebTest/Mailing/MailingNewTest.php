<?php
/*
 +--------------------------------------------------------------------+
 | CiviCRM version 3.3                                                |
 +--------------------------------------------------------------------+
 | Copyright CiviCRM LLC (c) 2004-2010                                |
 +--------------------------------------------------------------------+
 | This file is a part of CiviCRM.                                    |
 |                                                                    |
 | CiviCRM is free software; you can copy, modify, and distribute it  |
 | under the terms of the GNU Affero General Public License           |
 | Version 3, 19 November 2007 and the CiviCRM Licensing Exception.   |
 |                                                                    |
 | CiviCRM is distributed in the hope that it will be useful, but     |
 | WITHOUT ANY WARRANTY; without even the implied warranty of         |
 | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.               |
 | See the GNU Affero General Public License for more details.        |
 |                                                                    |
 | You should have received a copy of the GNU Affero General Public   |
 | License along with this program; if not, contact CiviCRM LLC       |
 | at info[AT]civicrm[DOT]org. If you have questions about the        |
 | GNU Affero General Public License or the licensing of CiviCRM,     |
 | see the CiviCRM license FAQ at http://civicrm.org/licensing        |
 +--------------------------------------------------------------------+
*/
require_once 'PHPUnit/Extensions/SeleniumTestCase.php';

class WebTest_Mailing_MailingNewTest extends PHPUnit_Extensions_SeleniumTestCase
{
  protected function setUp()
  {
    $this->setBrowser("*chrome");
    $this->setBrowserUrl("http://localhost");
  }

  public function testNewMailing()
  {
    $this->open("/civisvn/");
    $this->type("edit-name", "admin");
    $this->type("edit-pass", "Emotive1");
    $this->click("edit-submit");
    $this->waitForPageToLoad("90000");
    $this->open("http://localhost/civisvn/civicrm");
    $this->click("//div[@id='root-menu-div']/div[8]/ul/li[1]/div/a");
    $this->waitForPageToLoad("90000");
    $this->type("name", "test1");
    $this->click("add");
    $this->addSelection("includeGroups-f", "label=test mailing");
    $this->click("add");
    $this->click("_qf_Group_next");
    $this->waitForPageToLoad("90000");
    $this->click("_qf_Settings_next");
    $this->waitForPageToLoad("90000");
    $this->type("subject", "test mail");
    $this->click("_qf_Upload_upload");
    $this->waitForPageToLoad("90000");
    $this->click("_qf_Test_next");
    $this->waitForPageToLoad("90000");
    $this->click("_qf_Schedule_next");
    $this->waitForPageToLoad("90000");

	// run the mail cron
	$batch_size = 40;
	for($i = 1; $i <= 5; $i++) {
		
		$batch_size *= $i;

		$this->open("http://localhost/civisvn/sites/all/modules/civicrm/bin/civimail.cronjob.php?name=admin&pass=Emotive1&key=12345678910");
		$this->waitForPageToLoad("90000");

		$this->open("http://localhost/civisvn/civicrm");		
		$this->click("//ul[@id='civicrm-menu']/li[7]");
		$this->click("//div[@id='root-menu-div']/div[8]/ul/li[3]/div/a");
		$this->waitForPageToLoad("90000");
		$this->click("link=Report");
		$this->waitForPageToLoad("90000");
		$this->assertTrue($this->isTextPresent($batch_size));
				
	}

	// check the report one more time to make sure it is complete
	$this->open("http://localhost/civisvn/civicrm");		
		$this->click("//ul[@id='civicrm-menu']/li[7]");
		$this->click("//div[@id='root-menu-div']/div[8]/ul/li[3]/div/a");
		$this->waitForPageToLoad("90000");
		$this->click("link=Report");
		$this->waitForPageToLoad("90000");
	$this->assertTrue($this->isTextPresent("Complete"));
	
  }
}
?>
