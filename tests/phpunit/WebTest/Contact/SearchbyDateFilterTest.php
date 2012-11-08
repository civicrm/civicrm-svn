<?php
/*
 +--------------------------------------------------------------------+
 | CiviCRM version 4.2                                                |
 +--------------------------------------------------------------------+
 | Copyright CiviCRM LLC (c) 2004-2012                                |
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
class WebTest_Contact_SearchbyDateFilterTest extends CiviSeleniumTestCase {

  protected function setUp() {
    parent::setUp();
  }

  /*
   * Function to test individual pane seperatly.
   */
  function testAdvancedSearch() {
    // This is the path where our testing install resides.
    // The rest of URL is defined in CiviSeleniumTestCase base class, in
    // class attributes.
    $this->open($this->sboxPath);

    // Logging in. Remember to wait for page to load. In most cases,
    // you can rely on 30000 as the value that allows your test to pass, however,
    // sometimes your test might fail because of this. In such cases, it's better to pick one element
    // somewhere at the end of page and use waitForElementPresent on it - this assures you, that whole
    // page contents loaded and you can continue your test execution.
    $this->webtestLogin();

    // Get all default advance search panes.
    $allpanes = $this->_advanceSearchPanesDateFilter();

    // Test Individual panes.
    foreach (array_keys($allpanes) as $pane) {
      // Go to the Advance Search
      $this->open($this->sboxPath . 'civicrm/contact/search/advanced?reset=1');
      $this->waitForPageToLoad('30000');

      // Select some fields from pane.
      $this->_selectPaneFields($pane);

      $this->click('_qf_Advanced_refresh');

      $this->waitForPageToLoad('60000');
      // check the opened panes.
      $this->_checkOpenedPanes(array($pane));
    }
    
  }

  function testIndividualSearchPage(){
     // This is the path where our testing install resides.
    // The rest of URL is defined in CiviSeleniumTestCase base class, in
    // class attributes.
    $this->open($this->sboxPath);

    // Logging in. Remember to wait for page to load. In most cases,
    // you can rely on 30000 as the value that allows your test to pass, however,
    // sometimes your test might fail because of this. In such cases, it's better to pick one element
    // somewhere at the end of page and use waitForElementPresent on it - this assures you, that whole
    // page contents loaded and you can continue your test execution.
    $this->webtestLogin();

    $this->open($this->sboxPath . 'civicrm/contribute/search?reset=1');
    $this->select("contribution_date_relative","value=previous_before.quarter");
    $this->click("_qf_Search_refresh");
    $this->waitForPageToLoad('60000');
    $this->select("contribution_date_relative","value=previous_2.quarter");
    $this->click("_qf_Search_refresh");
    $this->waitForPageToLoad('60000');
    $this->select("contribution_date_relative","value=earlier.quarter");
    $this->click("_qf_Search_refresh");
    $this->waitForPageToLoad('60000');
    $this->select("contribution_date_relative","value=ending.year"); 
    $this->click("_qf_Search_refresh");
    $this->waitForPageToLoad('60000');
    $this->open($this->sboxPath . 'civicrm/member/search?reset=1');
    $this->select("member_end_date_relative","value=previous_before.month");
    $this->click("_qf_Search_refresh");
    $this->waitForPageToLoad('60000');
    $this->select("member_end_date_relative","value=previous_2.month");
    $this->click("_qf_Search_refresh");
    $this->waitForPageToLoad('60000');
    $this->select("member_end_date_relative","value=earlier.month");
    $this->click("_qf_Search_refresh");
    $this->waitForPageToLoad('60000');
    $this->select("member_end_date_relative","value=ending.month");
    $this->click("_qf_Search_refresh");
    $this->waitForPageToLoad('60000');
    $this->open($this->sboxPath . 'civicrm/event/search?reset=1');
    $this->select("event_relative","value=previous_before.week");
    $this->click("_qf_Search_refresh");
    $this->waitForPageToLoad('60000');
    $this->select("event_relative","value=previous_2.week");
    $this->click("_qf_Search_refresh");
    $this->waitForPageToLoad('60000');
    $this->select("event_relative","value=earlier.week");
    $this->click("_qf_Search_refresh");
    $this->waitForPageToLoad('60000');
    $this->select("event_relative","value=ending.week");
    $this->click("_qf_Search_refresh");
    $this->waitForPageToLoad('60000');
    $this->open($this->sboxPath . 'civicrm/activity/search?reset=1');
    $this->select("activity_date_relative","value=previous_before.day");
    $this->click("_qf_Search_refresh");
    $this->waitForPageToLoad('60000');
    $this->select("activity_date_relative","value=previous_2.day");
    $this->click("_qf_Search_refresh");
    $this->waitForPageToLoad('60000');
    $this->select("activity_date_relative","value=earlier.day");
    $this->click("_qf_Search_refresh");
    $this->waitForPageToLoad('60000');
    $this->select("activity_date_relative","value=ending.quarter");
    $this->click("_qf_Search_refresh");
    $this->waitForPageToLoad('60000');
    $this->open($this->sboxPath . 'civicrm/pledge/search?reset=1');
    $this->select("pledge_payment_date_relative","value=greater.week");
    $this->click("_qf_Search_refresh");
    $this->waitForPageToLoad('60000');
    $this->click("xpath=//form[@id='Search']/div[2]/div/div");
    $this->select("pledge_payment_date_relative","value=greater.day");
    $this->click("_qf_Search_refresh");
    $this->waitForPageToLoad('60000');
    $this->click("xpath=//form[@id='Search']/div[2]/div/div");
    $this->select("pledge_payment_date_relative","value=greater.quarter");
    $this->click("_qf_Search_refresh");
    $this->waitForPageToLoad('60000');
    $this->click("xpath=//form[@id='Search']/div[2]/div/div");
    $this->select("pledge_payment_date_relative","value=greater.month");
    $this->click("_qf_Search_refresh");
    $this->waitForPageToLoad('60000');
    $this->open($this->sboxPath . 'civicrm/mailing?reset=1');
    $this->select("mailing_relative","value=previous_before.year");
    $this->click("_qf_Search_refresh");
    $this->waitForPageToLoad('60000');
    $this->select("mailing_relative","value=previous_2.year");
    $this->click("_qf_Search_refresh");
    $this->waitForPageToLoad('60000');
    $this->select("mailing_relative","value=earlier.year");
    $this->click("_qf_Search_refresh");
    $this->waitForPageToLoad('60000');
    $this->select("mailing_relative","value=greater.year");
    $this->click("_qf_Search_refresh");
    $this->waitForPageToLoad('60000');
  }

  function _checkOpenedPanes($openedPanes = array(
    )) {
    if (!$this->isTextPresent('No matches found')) {
      $this->click('css=div.crm-advanced_search_form-accordion div.crm-accordion-header');
    }

    $allPanes = $this->_advanceSearchPanesDateFilter();

    foreach ($allPanes as $paneRef => $pane) {
      if (in_array($paneRef, $openedPanes)) {
        // assert for element present.
        $this->waitForElementPresent("css=div.crm-accordion-wrapper div.crm-accordion-body {$pane['bodyLocator']}");
      }
      else {
        $this->assertTrue(!$this->isElementPresent("css=div.crm-accordion-wrapper div.crm-accordion-body {$pane['bodyLocator']}"));
      }
    }
  }

  function _selectPaneFields($paneRef, $selectFields = array(
    )) {
    $pane = $this->_advanceSearchPanesDateFilter($paneRef);

    $this->click("css=div.crm-accordion-wrapper {$pane['headerLocator']}");
    $this->waitForElementPresent("css=div.crm-accordion-wrapper div.crm-accordion-body {$pane['bodyLocator']}");

    foreach ($pane['fields'] as $fld => $field) {
      if (!empty($selectFields) && !in_array($fld, $selectFields)) {
        continue;
      }

      $fldLocator = isset($field['locator']) ? $field['locator'] : '';

      switch ($field['type']) {
        case 'text':
          $this->type($fldLocator, current($field['values']));
          break;

        case 'select':
          foreach ($field['values'] as $op) {
            $this->select($fldLocator, 'label=' . $op);
          }
          break;

        case 'checkbox':
          foreach ($field['values'] as $op) {
            if (!$this->isChecked($op)) {
              $this->click($op);
            }
          }
          break;

        case 'radio':
          foreach ($field['values'] as $op) {
            $this->click($op);
          }
          break;

        case 'date':
          $this->webtestFillDate($fldLocator, current($field['values']));
          break;
      }
    }
  }

  function _advanceSearchPanesDateFilter($paneRef = NULL) {
    static $_advance_search_panes;

    if (!isset($_advance_search_panes) || empty($_advance_search_panes)) {
      $_advance_search_panes = array(
         'activity' =>
        array(
          'headerLocator' => 'div#activity',
          'bodyLocator' => 'input#activity_contact_name',
          'title' => 'Activities',
          'fields' =>
          array(
           'Activity Dates' =>
                array(
                      'type' => 'select',
                      'locator' => 'activity_date_relative',
                      'values' => array('This Year'),
            ),
              ),
              ),
        'demographics' =>
        array(
          'headerLocator' => 'div#demographics',
          'bodyLocator' => 'input#birth_date_low_display',
          'title' => 'Demographics',
          'fields' =>
          array(
                'Deceased Dates' =>
                array(
                      'type' => 'select',
                      'locator' => 'deceased_date_relative',
                      'values' => array('This Quarter'),
                      ),
                ),
              ),
        'change_log' =>
        array(
          'headerLocator' => 'div#changeLog',
          'bodyLocator' => 'input#changed_by',
          'title' => 'Change Log',
          'fields' =>
          array(
                 'Modified Between' =>
                array(
                      'type' => 'select',
                      'locator' => 'log_date_relative',
                      'values' => array('This Week'),
            ),
          ),
              ),
        'mailing' =>
        array(
          'headerLocator' => 'div#CiviMail',
          'bodyLocator' => 'select#mailing_date_relative',
          'title' => 'Mailings',
          'fields' =>
          array(
            'Mailing Date' =>
                array(
                      'type' => 'select',
                      'locator' => 'mailing_date_relative',
                      'values' => array('Prior to Previous Quarter'),
            ),
          ),
        ),
         
        'contribution' =>
        array(
          'headerLocator' => 'div#CiviContribute',
          'bodyLocator' => 'select#financial_type_id',
          'title' => 'Contributions',
          'fields' =>
          array(
            'Contribution Dates' =>
                array(
                      'type' => 'select',
                      'locator' => 'contribution_date_relative',
                      'values' => array('This Day'),
            ),
          ),
        ),
         'pledge' =>
         array(
          'headerLocator' => 'div#CiviPledge',
          'bodyLocator' => 'select#pledge_payment_date_relative',
          'title' => 'Pledges',
          'fields' =>
          array(
            'Contribution Dates' =>
                array(
                      'type' => 'select',
                      'locator' => 'pledge_payment_date_relative',
                      'values' => array('Prior to Previous Month'),
            ),
          ),
        ),
        'membership' =>
        array(
          'headerLocator' => 'div#CiviMember',
          'bodyLocator' => 'input#member_source',
          'title' => 'Memberships',
          'fields' =>
          array(
                 'Member Since' =>
                array(
                      'type' => 'select',
                      'locator' => 'member_join_date_relative',
                      'values' => array('Previous Year'),
            ),
         
          ),
        ),
        'event' =>
        array(
          'headerLocator' => 'div#CiviEvent',
          'bodyLocator' => 'input#event_name',
          'title' => 'Events',
          'fields' =>
          array(
                'Event Dates' =>
                array(
                      'type' => 'select',
                      'locator' => 'event_relative',
                      'values' => array('Previous Week'),
            ),
                ),
              ),
      
    
                                     );
    }

    if ($paneRef) {
      return $_advance_search_panes[$paneRef];
    }

    return $_advance_search_panes;
  }
}

