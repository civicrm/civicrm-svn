<?php

/*
 +--------------------------------------------------------------------+
 | CiviCRM version 3.3                                                |
 +--------------------------------------------------------------------+
 | Copyright CiviCRM LLC (c) 2004-2009                                |
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

require_once 'CiviTest/CiviSeleniumTestCase.php';

class WebTest_Contact_RelationshipAddTest extends CiviSeleniumTestCase
{
  protected $captureScreenshotOnFailure = TRUE;
  protected $screenshotPath = '/var/www/api.dev.civicrm.org/public/sc';
  protected $screenshotUrl = 'http://api.dev.civicrm.org/sc/';
    
  protected function setUp()
  {
      parent::setUp();
  }

  function testRelationshipAddTest( )
  {
      // This is the path where our testing install resides. 
      // The rest of URL is defined in CiviSeleniumTestCase base class, in
      // class attributes.
      $this->open( $this->sboxPath );
      
      // Logging in. Remember to wait for page to load. In most cases,
      // you can rely on 30000 as the value that allows your test to pass, however,
      // sometimes your test might fail because of this. In such cases, it's better to pick one element
      // somewhere at the end of page and use waitForElementPresent on it - this assures you, that whole
      // page contents loaded and you can continue your test execution.
      $this->webtestLogin( );
      
      //create a relationship type between different contact types
      $params = array( 'label_a_b'       => 'Owner of '.rand( ),
                       'label_b_a'       => 'Belongs to '.rand( ),
                       'contact_type_a'  => 'Individual',
                       'contact_type_b'  => 'Household',
                       'description'     => 'The company belongs to this individual' );
      
      $this->webtestAddRelationshipType( $params );

      //create a New Individual
      $firstName = substr(sha1(rand()), 0, 7);
      $this->webtestAddContact( $firstName, "Anderson", "$firstName@anderson.name" );
      $sortName    = "Anderson, $firstName";
      $displayName = "$firstName Anderson";
      
      // Go directly to the URL of the screen that you will be testing (New Household).
      $this->open($this->sboxPath . "civicrm/contact/add&reset=1&ct=Household");
      
      //fill in Household name
      $this->click("household_name");
      $name = "Fraddie Grant's home " . substr(sha1(rand()), 0, 7);
      $this->type("household_name", $name );
      
      // Clicking save.
      $this->click("_qf_Contact_upload_view");
      $this->waitForElementPresent("css=.crm-contact-tabs-list");

      // visit relationship tab of the household
      $this->click("css=li#tab_rel a");
      
      // wait for add Relationship link
      $this->waitForElementPresent('link=Add Relationship');
      $this->click('link=Add Relationship');
      
      //choose the created relationship type 
      $this->waitForElementPresent("relationship_type_id");
      $this->select('relationship_type_id', "label={$params['label_b_a']}");

      //fill in the individual
      $this->typeKeys("css=input#rel_contact", $sortName);
      $this->click("css=input#rel_contact");

      $this->waitForElementPresent("search-button");
      $this->click("search-button");
      
      //check the checkbox
      $this->waitForElementPresent("xpath=//table/tbody//tr[1]/td[1]/input");
      $this->click("xpath=//table/tbody//tr[1]/td[1]/input");

      //fill in the relationship start date
      $this->webtestFillDate('start_date' , '-2 year' );

      $description = "Well here is some description !!!!";
      $this->type("description", $description );
      
      //save the relationship
      $this->click("_qf_Relationship_upload");
      $this->waitForElementPresent("current-relationships");

      //check the status message
      $this->assertTrue($this->isTextPresent("1 new relationship record created."));
      
      $this->waitForElementPresent("xpath=//div[@id='current-relationships']//div//table/tbody//tr/td[9]/span/a[text()='View']");
      $this->click("xpath=//div[@id='current-relationships']//div//table/tbody//tr/td[9]/span/a[text()='View']");
     
      $this->waitForPageToLoad("300000"); 
      $this->webtestVerifyTabularData(
                                      array(
                                            'Description'         => $description,
                                            'Status'	          => 'Enabled',
                                            $params['label_b_a']  => $sortName
                                            )
                                      );

  }  

}
?>
