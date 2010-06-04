<?php

/*
 +--------------------------------------------------------------------+
 | CiviCRM version 3.1                                                |
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


 
class WebTest_Event_AddPricesetTest extends CiviSeleniumTestCase {

  protected function setUp()
  {
      parent::setUp();
  }

  function testAddPriceSet()
  {

      // This is the path where our testing install resides. 
      // The rest of URL is defined in CiviSeleniumTestCase base class, in
      // class attributes.
      $this->open( $this->sboxPath );

      // Log in using webtestLogin() method
      $this->webtestLogin();

      // Go directly to the URL of the screen that you will be testing (New Event).
      $this->open($this->sboxPath . "civicrm/admin/price&reset=1&action=add");

      $setTitle = 'Conference Fees - '.substr(sha1(rand()), 0, 7);
      $usedFor = 'Event';
      $setHelp = "Select your conference options.";
      $this->_testAddSet( $setTitle, $usedFor, $setHelp );

      $elements = $this->parseURL( );
      $this->assertType( "numeric", $elements['queryString']['sid'] );

      $fields = array( "Full Conference" => "Text",
                       "Meal Choice" => "Select",
                       "Pre-conference Meetup?" => "Radio",
                       "Evening Sessions" => "CheckBox",
                     );
      $this->_testAddPriceFields( $fields );
      
      $this->_testVerifyPriceSet( $fields, $elements['queryString']['sid'] );      
  }

 
  function _testAddSet( $setTitle, $usedFor, $setHelp ) {
      $this->waitForPageToLoad('30000');
      $this->waitForElementPresent("_qf_Set_next-bottom");

      // Enter Priceset fields (Title, Used For ...)
      $this->type("title", $setTitle);
      if ( $usedFor == 'Event' ){
          $this->check("extends[1]");
      } elseif ( $usedFor == 'Contribution') {
          $this->check("extends[2]");
      }

      $this->type("help_pre", $setHelp);

      $this->assertChecked("is_active", "Verify that Is Active checkbox is set.");
      $this->click("_qf_Set_next-bottom");      

      $this->waitForPageToLoad('30000');
      $this->waitForElementPresent("_qf_Field_next-bottom");
  }
  
  function _testAddPriceFields( &$fields ) {
      foreach ($fields as $label => $type ){
         $this->type("label", $label);
         $this->select("html_type", "value={$type}");

         switch ($type) {
             case 'Text':
                $this->type("price", "525.00");
                $this->check("is_required");
                break;
             case 'Select':
                $this->type("option_label_1", "Chicken");
                $this->type("option_name_1", "30.00");
                $this->click("link=another choice");
                $this->type("option_label_2", "Vegetarian");
                $this->type("option_name_2", "25.00");
                break;
             case 'Radio':
                $this->type("option_label_1", "Yes");
                $this->type("option_name_1", "50.00");
                $this->click("link=another choice");
                $this->type("option_label_2", "No");
                $this->type("option_name_2", "0");
                $this->check("is_required");
                break;
             case 'CheckBox':
                $this->type("option_label_1", "First Night");
                $this->type("option_name_1", "15.00");
                $this->click("link=another choice");
                $this->type("option_label_2", "Second Night");
                $this->type("option_name_2", "15.00");
                break;
             default:
                break;
          }
          $this->click("_qf_Field_next_new-bottom");
          $this->waitForPageToLoad('30000');
          $this->waitForElementPresent("_qf_Field_next-bottom");
      }
  }

  
  function _testVerifyPriceSet( &$fields, $sid ){
      // verify Price Set at Preview page
      // start at Manage Price Sets listing
      $this->open($this->sboxPath . "civicrm/admin/price?reset=1");
      $this->waitForPageToLoad('30000');

      // Fixme: need to figure out a way to address the correct row
      $this->click("css=tr#row_{$sid} a[title='Preview Price Set']");
      
      $this->waitForPageToLoad('30000');
      // Look for Register button
      $this->waitForElementPresent("_qf_Preview_cancel-bottom");
      
      // Check for correct event info strings
      $this->_checkStrings( array_keys( $fields ) );
  }

}
