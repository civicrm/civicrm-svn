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

class WebTest_Member_OfflineAutoRenewMembershipTest extends CiviSeleniumTestCase {

  protected $captureScreenshotOnFailure = TRUE;
  protected $screenshotPath = '/var/www/api.dev.civicrm.org/public/sc';
  protected $screenshotUrl = 'http://api.dev.civicrm.org/sc/';
    
  protected function setUp()
  {
      parent::setUp();
  }

  function testOfflineAutoRenewMembership()
  {
      $this->open( $this->sboxPath );
      $this->webtestLogin();

      // We need a payment processor
      $processorName = "Webtest AuthNet" . substr(sha1(rand()), 0, 7);
      $this->webtestAddPaymentProcessor($processorName, 'AuthNet');

      // -- star creating a membership contribution page
      $this->open($this->sboxPath . "civicrm/admin/contribute/add&reset=1&action=add");

      // a random 7-char string and an even number to make this pass unique
      $hash = substr(sha1(rand()), 0, 7);

      // fill in Title and Settings
      $contributionTitle = "Title $hash";
      $this->type('title',$contributionTitle );
      $this->select('contribution_type_id', 'value=1');
      $this->fillRichTextField('intro_text','This is Introductory Message','CKEditor');
      $this->fillRichTextField('footer_text','This is Footer Message','CKEditor');
        
      // continue
      $this->click('_qf_Settings_next');
      $this->waitForPageToLoad();

      // get page id for future use
      $matches = array();
      preg_match('/id=([0-9]+)/', $this->getLocation(), $matches);
      $page_id = $matches[1];
      
      //this contribution page for membership signup
      $this->waitForElementPresent('payment_processor_id');
      $this->select("payment_processor_id", "label=" . $processorName);
      $this->click("amount_block_is_active");
        
      // save
      $this->click('_qf_Amount_next');
      $this->waitForPageToLoad();
        
      // go to Memberships
      $this->click('css=#tab_membership a');

      // fill in Memberships
      $this->waitForElementPresent('is_active');
      $this->click('is_active');
      $this->type('new_title',     "Title - New Membership $hash");
      $this->type('renewal_title', "Title - Renewals $hash");
      $this->click('membership_type[2]');
      $this->click('is_required');
        
      // save
      $this->click('_qf_MembershipBlock_next');
      $this->waitForPageToLoad();
        
      // go to Receipt
      $this->click('css=#tab_thankYou a');

      // fill in Receipt
      $this->waitForElementPresent('thankyou_title');
      $this->type('thankyou_title',     "Thank-you Page Title $hash");
      $this->type('receipt_from_name',  "Receipt From Name $hash");
      $this->type('receipt_from_email', "$hash@example.org");
      $this->type('receipt_text',       "Receipt Message $hash");
      $this->type('cc_receipt',         "$hash@example.net");
      $this->type('bcc_receipt',        "$hash@example.com");
        
      // save
      $this->click('_qf_ThankYou_next');
      $this->waitForPageToLoad();
      
      
      // go to Tell a Friend
      $this->click('css=#tab_friend a');
      
      // fill Tell a Friend
      $this->waitForElementPresent('tf_is_active');
      $this->click('tf_is_active');
      $this->type('tf_title',          "TaF Title $hash");
      $this->type('intro',             "TaF Introduction $hash");
      $this->type('suggested_message', "TaF Suggested Message $hash");
      $this->type('general_link',      "TaF Info Page Link $hash");
      $this->type('thankyou_title',    "TaF Thank-you Title $hash");
      $this->type('thankyou_text',     "TaF Thank-you Message $hash");
      
      // save
      $this->click('_qf_Contribute_next');
      $this->waitForPageToLoad();
      
      // go to Profiles
      $this->click('css=#tab_custom a');
      
      // fill in Profiles
      $this->waitForElementPresent('custom_pre_id');
      $this->select('custom_pre_id',  'value=1');
      
      
      // save
      $this->click('_qf_Custom_next');
      $this->waitForPageToLoad();
      
      // go to Premiums
      $this->click('css=#tab_premium a');
      
      // fill in Premiums
      $this->waitForElementPresent('premiums_active');
      $this->click('premiums_active');
      $this->type('premiums_intro_title',   "Prem Title $hash");
      $this->type('premiums_intro_text',    "Prem Introductory Message $hash");
      $this->type('premiums_contact_email', "$hash@example.info");
      $this->type('premiums_contact_phone', rand(100000000, 999999999));
      $this->click('premiums_display_min_contribution');
        
      // save
      $this->click('_qf_Premium_next');
      $this->waitForPageToLoad();
        
      // go to Widgets
      $this->click('css=#tab_widget a');
      
      // fill in Widgets
      $this->waitForElementPresent('is_active');
      $this->click('is_active');
      $this->type('url_logo',     "URL to Logo Image $hash");
      $this->type('button_title', "Button Title $hash");
      $this->type('about',        "About $hash");
        
      // save
      $this->click('_qf_Widget_next');
      $this->waitForPageToLoad();
      
      // go to Personal Campaigns
      $this->click('css=#tab_pcp a');
      
      // fill in Personal Campaigns
      $this->waitForElementPresent('pcp_active');
      $this->click('pcp_active');
      $this->click('is_approval_needed');
      $this->type('notify_email', "$hash@example.name");
      $this->select('supporter_profile_id', 'value=2');
      $this->type('tellfriend_limit', 7);
      $this->type('link_text', "'Create Personal Campaign Page' link text $hash");
      
      // save and done
      $this->click('_qf_PCP_upload_done');
      $this->waitForPageToLoad();
      
      //get Url for Live Contribution Page
      $registerUrl = "{$this->sboxPath}civicrm/contribute/transact?reset=1&id=$page_id";
      
      //logout
      $this->open($this->sboxPath . "civicrm/logout&reset=1");
      $this->waitForPageToLoad('30000');
  }
}
