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

      $setTitle = 'Conference Fees - '.substr(sha1(rand()), 0, 7);
      $usedFor = 'Event';
      $setHelp = "Select your conference options.";
      $this->_testAddSet( $setTitle, $usedFor, $setHelp );

      // Get the price set id ($sid) by retrieving and parsing the URL of the New Price Field form
      // which is where we are after adding Price Set.
      $elements = $this->parseURL( );
      $sid = $elements['queryString']['sid'];
      $this->assertType( "numeric", $sid );

      $validStrings = array( );

      $fields = array( "Full Conference" => "Text",
                       "Meal Choice" => "Select",
                       "Pre-conference Meetup?" => "Radio",
                       "Evening Sessions" => "CheckBox",
                     );
      $this->_testAddPriceFields( $fields, $validateStrings );
      // var_dump($validateStrings);
      
      // load the Price Set Preview and check for expected values
      $this->_testVerifyPriceSet( $validateStrings, $sid );      
  }

 
  function _testAddSet( $setTitle, $usedFor, $setHelp ) {
      $this->open($this->sboxPath . "civicrm/admin/price&reset=1&action=add");
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
  
  function _testAddPriceFields( &$fields, &$validateStrings ) {
      foreach ($fields as $label => $type ){
          $validateStrings[] = $label;
          
          $this->type("label", $label);
          $this->select("html_type", "value={$type}");

          switch ($type) {
             case 'Text':
                $validateStrings[] = "525.00";
                $this->type("price", "525.00");
                $this->check("is_required");
                break;
             case 'Select':
                $options = array( 1 => array( 'label' => "Chicken",
                                              'amount'  => "30.00" ),
                                  2 => array( 'label' => "Vegetarian", 
                                              'amount'  => "25.00" ) );
                $this->addMultipleChoiceOptions( $options, $validateStrings );
                break;
             case 'Radio':
                $options = array( 1 => array( 'label' => "Yes",
                                              'amount'  => "50.00" ),
                                  2 => array( 'label' => "No", 
                                              'amount'  => "0" ) );
                $this->addMultipleChoiceOptions( $options, $validateStrings );
                $this->check("is_required");
                break;
             case 'CheckBox':
                $options = array( 1 => array( 'label' => "First Night",
                                              'amount'  => "15.00" ),
                                  2 => array( 'label' => "Second Night", 
                                              'amount'  => "15.00" ) );
                $this->addMultipleChoiceOptions( $options, $validateStrings );
                break;
             default:
                break;
          }
          $this->click("_qf_Field_next_new-bottom");
          $this->waitForPageToLoad('30000');
          $this->waitForElementPresent("_qf_Field_next-bottom");
      }
  }

  
  function _testVerifyPriceSet( $validateStrings, $sid ){
      // verify Price Set at Preview page
      // start at Manage Price Sets listing
      $this->open($this->sboxPath . "civicrm/admin/price?reset=1");
      $this->waitForPageToLoad('30000');

      // Use the price set id ($sid) to pick the correct row
      $this->click("css=tr#row_{$sid} a[title='Preview Price Set']");
      
      $this->waitForPageToLoad('30000');
      // Look for Register button
      $this->waitForElementPresent("_qf_Preview_cancel-bottom");
      
      // Check for expected price set field strings
      $this->assertStringsPresent( $validateStrings );
  }

}
