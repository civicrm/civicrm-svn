<?php

/*
 +--------------------------------------------------------------------+
 | CiviCRM version 3.4                                                |
 +--------------------------------------------------------------------+
 | Copyright CiviCRM LLC (c) 2004-2011                                |
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
 
class WebTest_Member_AddPricesetTest extends CiviSeleniumTestCase
{

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

      $setTitle = 'Membership Fees - '.substr(sha1(rand()), 0, 7);
      $usedFor = 'Membership';
      $setHelp = 'Select your membership options.';
      $this->_testAddSet( $setTitle, $usedFor, $setHelp );

      // Get the price set id ($sid) by retrieving and parsing the URL of the New Price Field form
      // which is where we are after adding Price Set.
      $elements = $this->parseURL( );
      $sid = $elements['queryString']['sid'];
      $this->assertType( 'numeric', $sid );

      $validStrings = array( );

      $fields = array( 
                      'National Membership'    => 'CheckBox',
                     );
      $this->_testAddPriceFields( $fields, $validateStrings );
      //var_dump($validateStrings);
      
      // load the Price Set Preview and check for expected values
      $this->_testVerifyPriceSet( $validateStrings, $sid );      
  }

  function _testAddSet( $setTitle, $usedFor, $setHelp )
  {
      $this->open($this->sboxPath . 'civicrm/admin/price&reset=1&action=add');
      $this->waitForPageToLoad('30000');
      $this->waitForElementPresent('_qf_Set_next-bottom');

      // Enter Priceset fields (Title, Used For ...)
      $this->type('title', $setTitle);
      if ( $usedFor == 'Event' ){
          $this->check('extends[1]');
      } elseif ( $usedFor == 'Contribution') {
          $this->check('extends[2]');
      } elseif ( $usedFor == 'Membership') {
          $this->check('extends[3]');
      }

      $this->type('help_pre', $setHelp);

      $this->assertChecked('is_active', 'Verify that Is Active checkbox is set.');
      $this->click('_qf_Set_next-bottom');      

      $this->waitForPageToLoad('30000');
      $this->waitForElementPresent('_qf_Field_next-bottom');

  }
  
  function _testAddPriceFields( &$fields, &$validateString, $dateSpecificFields = false  )
  {
      foreach ( $fields as $label => $type ) {
          $validateStrings[] = $label;
          
          $this->type('label', $label);
          $this->select('html_type', "value={$type}");

          switch ($type) {
             case 'CheckBox':
                $options = array( 1 => array( 'label'  => 'General',
                                              'membership_type_id' => 1 ),
                                  2 => array( 'label'  => 'Student', 
                                              'membership_type_id' => 2 ),
                                  3 => array( 'label'  => 'Lifetime', 
                                              'membership_type_id' => 3 ),
                                  );
                $this->addMultipleChoiceOptions( $options, $validateStrings );
                break;

             default:
                break;
          }
          $this->click('_qf_Field_next_new-bottom');
          $this->waitForPageToLoad('30000');
          $this->waitForElementPresent('_qf_Field_next-bottom');
      }
  }
  
  function _testVerifyPriceSet( $validateStrings, $sid )
  {
      // verify Price Set at Preview page
      // start at Manage Price Sets listing
      $this->open($this->sboxPath . 'civicrm/admin/price?reset=1');
      $this->waitForPageToLoad('30000');
      
      // Use the price set id ($sid) to pick the correct row
      $this->click("css=tr#row_{$sid} a[title='Preview Price Set']");
      
      $this->waitForPageToLoad('30000');
      // Look for Register button
      $this->waitForElementPresent('_qf_Preview_cancel-bottom');
      
      // Check for expected price set field strings
      $this->assertStringsPresent( $validateStrings );
  }
  
  function _testVerifyRegisterPage( $contributionPageTitle )
  {
      $this->open( $this->sboxPath . 'civicrm/admin/contribute?reset=1' );
      $this->waitForElementPresent( '_qf_SearchContribution_refresh' );
      $this->type( 'title', $contributionPageTitle );
      $this->click( '_qf_SearchContribution_refresh' );
      $this->waitForPageToLoad( '50000' );
      $id = $this->getAttribute("//div[@id='configure_contribution_page']//div[@class='dataTables_wrapper']/table/tbody/tr@id");
      $id = explode( '_', $id );
      $registerUrl = "civicrm/contribute/transact?reset=1&id=$id[2]";
      return $registerUrl;
  }
  
}
