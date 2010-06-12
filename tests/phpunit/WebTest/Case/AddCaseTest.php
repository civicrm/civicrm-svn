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


 
class WebTest_Case_AddCaseTest extends CiviSeleniumTestCase {

  protected function setUp()
  {
      parent::setUp();
  }

  function testStandaloneCaseAdd()
  {

      $this->open( $this->sboxPath );

      // Log in using webtestLogin() method
      $this->webtestLogin();
      
      // Enable CiviCase module if necessary
      $this->open($this->sboxPath . "civicrm/admin/setting/component?reset=1");
      $this->waitForPageToLoad('30000');
      $this->waitForElementPresent("_qf_Component_next-bottom");
      $enabledComponents = $this->getSelectOptions("enableComponents-t");
      if (! array_search( "CiviCase", $enabledComponents ) ) {
          $this->addSelection("enableComponents-f", "label=CiviCase");
          $this->click("//option[@value='CiviCase']");
          $this->click("add");
          $this->click("_qf_Component_next-bottom");
          $this->waitForPageToLoad("30000");          
      }

      // Go directly to the URL of the screen that you will be testing (New Case-standalone).
      $this->open($this->sboxPath . "civicrm/case/add&reset=1&action=add&atype=13&context=standalone");

      // As mentioned before, waitForPageToLoad is not always reliable. Below, we're waiting for the submit
      // button at the end of this page to show up, to make sure it's fully loaded.
      $this->waitForPageToLoad('30000');
      $this->waitForElementPresent("_qf_Case_upload-bottom");

      // Adding contact with randomized first name (so we can then select that contact when creating case)
      // We're using pop-up New Contact dialog
      $firstName = substr(sha1(rand()), 0, 7);
      $lastName = "Fraser";
      $contactName = "{$lastName}, {$firstName}";
      $displayName = "{$firstName} {$lastName}";
      $email = "{$lastName}.{$firstName}@example.org";
      $this->webtestNewDialogContact( $firstName, $lastName, $email, $type = 4 );

      // Fill in other form values. We'll use a case type which is included in CiviCase sample data / xml files.
      $caseTypeLabel = "Adult Day Care Referral";
      // activity types we expect for this case type
      $activityTypes = array( "ADC referral", "Follow up", "Medical evaluation", "Mental health evaluation" );
      $caseRoles = array( "Senior Services Coordinator", "Health Services Coordinator", "Benefits Specialist", "Client");
      $caseStatusLabel = "Ongoing";
      $subject = "Safe daytime setting - senior female";
      $this->select("medium_id", "value=1");
      $location = "Main offices";
      $this->type("activity_location", $location);
      $details = "65 year old female needs safe location during the day for herself and her dog. She's in good health but somewhat disoriented.";
      $this->type("activity_details", $details);
      $this->type("activity_subject", $subject);
      
      $this->select("case_type_id", "label={$caseTypeLabel}");
      $this->select("status_id", "label={$caseStatusLabel}");
      // Choose Case Start Date.
      // Using helper webtestFillDate function.
      $this->webtestFillDate('start_date', 'now');
      $today = date('F jS, Y', strtotime('now'));
      // echo 'Today is ' . $today;
      $this->type("duration", "20");
      $this->click("_qf_Case_upload-bottom");

      // We should be at manage case screen
      $this->waitForPageToLoad("30000");
      $this->waitForElementPresent("_qf_CaseView_cancel-bottom");
      
      // Is status message correct?
      $this->assertTextPresent("Case opened successfully.", "Save successful status message didn't show up after saving!");

      $summaryStrings = array(  "Case Summary",
                                $displayName,
                                "Case Type: {$caseTypeLabel}",
                                "Start Date: {$today}",
                                "Status: {$caseStatusLabel}",
                                );
                                
      $this->_testVerifyCaseSummary( $summaryStrings, $activityTypes );
      $this->_testVerifyCaseRoles( $caseRoles );
      $this->_testVerifyCaseActivities( $activityTypes );
      
      $openCaseData = array( "Client"           => $displayName,
                             "Activity Type"    => "Open Case",
                             "Subject"          => $subject,
                             "Created By"       => $this->settings->username,
                             "Reported By"      => $this->settings->username,
                             "Medium"           => "In Person",
                             "Location"         => $location,
                             "Date and Time"    => $today,
                             "Details"          => $details,
                             "Status"           => "Completed",
                             "Priority"         => "Normal",
                            );

      $this->_testVerifyOpenCaseActivity( $subject, $openCaseData );

  }

  function _testVerifyCaseSummary( $validateStrings, $activityTypes ){
      $this->assertStringsPresent( $validateStrings );
      foreach( $activityTypes as $aType ){
          $this->assertText("activity_type_id", $aType);
      }
      $this->assertElementPresent("link=Assign to Another Client", "Assign to Another Client link is missing.");
      $this->assertElementPresent("name=case_report_all", "Print Case Summary button is missing.");
  }
  
  function _testVerifyCaseRoles( $caseRoles ){
      // check that expected roles are listed in the Case Roles pane
      foreach( $caseRoles as $role ){
          $this->assertText("css=div.crm-case-roles-block", $role);
      }
      // check that case creator role has been assigned to logged in user
      $this->assertText("css=td.crm-case-caseview-role-name", $this->settings->username);
  }

  function _testVerifyCaseActivities( $activityTypes ){
      // check that expected auto-created activities are listed in the Case Activities table
      foreach( $activityTypes as $aType ){
          $this->assertText("activities-selector", $aType);
      }
  }
  
  function _testVerifyOpenCaseActivity( $subject, $openCaseData ){
      // check that open case subject is present
      $this->assertText("activities-selector", $subject);
      // click open case activity pop-up dialog
      $this->click("link=$subject");
      $this->waitForElementPresent("view-activity");
      $this->waitForElementPresent("css=tr.crm-case-activity-view-Activity");
      // set page location of table containing activity view data
      $activityViewPrefix = "//div[@id='activity-content']";
      $activityViewTableId = "crm-activity-view-table";
      // Probably don't need both tableId and prefix - but good examples for other situations where only one can be used
      $this->webtestVerifyTabularData( $openCaseData, $activityViewPrefix, $activityViewTableId );
  }

}
