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

class WebTest_Contact_RelationshipAddTest extends CiviSeleniumTestCase {

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
      
      $params = array( 'label_a_b'       => 'Owner of'.rand( ),
                       'label_b_a'       => 'Belongs to'.rand( ),
                       'contact_type_a'  => 'Individual',
                       'contact_type_b'  => 'Household',
                       'description'     => 'The company belongs to this individual' );
      
      $this->webtestAddRelationshipType( $params );

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
      // visit relationship tab
      $this->click("css=li#tab_rel a");
      
      // wait for add Relationship link
      $this->waitForElementPresent('link=Add Relationship');
      //$this->waitForPageToLoad("300000");    
      $this->click('link=Add Relationship');
      $this->waitForPageToLoad("30000");    
      $this->select('relationship_type_id', "label={$params['label_b_a']}");
      $this->typeKeys("css=input#rel_contact", $sortName);
      $this->click("css=input#rel_contact");
      

      //$this->waitForElementPresent("_qf_Relationship_refresh");
      //      $this->click("_qf_Relationship_refresh");
      
      $this->waitForElementPresent("search-button");
      //      
      $this->click("search-button");
      $this->waitForPageToLoad("30000");    
      $this->waitForElementPresent("_qf_Relationship_refresh_savedetails");
      $this->waitForElementPresent("contact_select");
      $this->click("details-save");
      
      $this->waitForPageToLoad("60000");    
      
      // Is status message correct?
      //      $this->assertTrue($this->isTextPresent("Saved"));

  }  

}
?>
