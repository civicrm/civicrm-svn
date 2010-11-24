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


 
class WebTest_Campaign_SurveyUsageScenario extends CiviSeleniumTestCase {

  protected $captureScreenshotOnFailure = TRUE;
  protected $screenshotPath = '/tmp/';
  protected $screenshotUrl = 'http://api.dev.civicrm.org/sc/';
    
  protected function setUp()
  {
      parent::setUp();
  }
  
  function testSurveyUsageScenario()
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
      $this->webtestLogin();
      
      // Create new group
      $title = substr(sha1(rand()), 0, 7);
      $groupName = "group_$title";
      $this->addGroup( $groupName );

      // Adding contact
      // We're using Quick Add block on the main page for this.
      $firstName1 = substr(sha1(rand()), 0, 7);
      $this->webtestAddContact( $firstName1, "Smith", "$firstName1.smith@example.org" );
     
      // add contact to group
      // visit group tab
      $this->click("css=li#tab_group a");
      $this->waitForElementPresent("group_id");

      // add to group
      $this->select("group_id", "label=$groupName");
      $this->click("_qf_GroupContact_next");
      $this->waitForPageToLoad("30000");

      $firstName2 = substr(sha1(rand()), 0, 7);
      $this->webtestAddContact( $firstName2, "John", "$firstName2.john@example.org" );
     
      // add contact to group
      // visit group tab
      $this->click("css=li#tab_group a");
      $this->waitForElementPresent("group_id");

      // add to group
      $this->select("group_id", "label=$groupName");
      $this->click("_qf_GroupContact_next");
      $this->waitForPageToLoad("30000");

      // Enable CiviCampaign module if necessary
      $this->open($this->sboxPath . "civicrm/admin/setting/component?reset=1");
      $this->waitForPageToLoad('30000');
      $this->waitForElementPresent("_qf_Component_next-bottom");
      $enabledComponents = $this->getSelectOptions("enableComponents-t");
      if (! array_search( "CiviCampaign", $enabledComponents ) ) {
          $this->addSelection("enableComponents-f", "label=CiviCampaign");
          $this->click("//option[@value='CiviCampaign']");
          $this->click("add");
          $this->click("_qf_Component_next-bottom");
          $this->waitForPageToLoad("30000");          
          $this->assertTrue($this->isTextPresent("Your changes have been saved."));    
      }

      // Go directly to the URL of the screen that you will be testing
      $this->open($this->sboxPath . "civicrm/campaign/add&reset=1");

      // As mentioned before, waitForPageToLoad is not always reliable. Below, we're waiting for the submit
      // button at the end of this page to show up, to make sure it's fully loaded.
      $this->waitForElementPresent("_qf_Campaign_next-bottom");

      // Let's start filling the form with values.
      $this->type("title", "Campaign $title");

      // select the campaign type
      $this->select("campaign_type_id", "value=2");

      // fill in the description
      $this->type("description", "This is a test campaign");

      // include groups for the campaign
      $this->addSelection("includeGroups-f", "label=$groupName");
      $this->click("//option[@value=4]");
      $this->click("add");

      // fill the end date for campaign
      $this->webtestFillDate("end_date", "+1 year");
      
      // select campaign status
      $this->select("status_id", "value=2");

      // click save
      $this->click("_qf_Campaign_next-bottom");
      $this->waitForPageToLoad("30000");
      
      $this->assertTrue($this->isTextPresent("Campaign Campaign $title has been saved."), 
                        "Status message didn't show up after saving!");

      // create a custom data set for activities -> survey
      $this->open($this->sboxPath . "civicrm/admin/custom/group?action=add&reset=1");

      $this->waitForElementPresent("_qf_Group_next-bottom");
      // fill in a unique title for the custom group
      $this->type("title", "Group $title");
      
      // select the group this custom data set extends
      $this->select("extends[0]", "value=Activity");
      $this->waitForElementPresent("extends[1]");
      $this->select("extends[1]", "value=27");
      
      // save the custom group
      $this->click("_qf_Group_next-bottom");

      $this->waitForElementPresent("_qf_Field_next_new-bottom");
      $this->assertTrue($this->isTextPresent("Your custom field set 'Group $title' has been added. You can add it custom fields now."), "Status message didn't show up after saving!");

      // add a custom field to the custom group
      $this->type("label", "Field $title");

      $this->select("data_type[1]", "value=Radio");

      $this->waitForElementPresent("option_label_1");

      // create a set of options
      $this->type("option_label_1", "Option $title 1");
      $this->type("option_value_1", "1");

      $this->type("option_label_2", "Option $title 2");
      $this->type("option_value_2", "2");
      
      // save the custom field
      $this->click("_qf_Field_next-bottom");

      $this->waitForElementPresent("newCustomField");
      $this->assertTrue($this->isTextPresent("Your custom field 'Field $title' has been saved."), 
                        "Status message didn't show up after saving!");

      // create a profile for campaign
      $this->open($this->sboxPath . "civicrm/admin/uf/group/add?action=add&reset=1");

      $this->waitForElementPresent("_qf_Group_next-bottom");

      // fill in a unique title for the profile
      $this->type("title", "Profile $title");

      // save the profile
      $this->click("_qf_Group_next-bottom");

      $this->waitForElementPresent("_qf_Field_next-bottom");
      $this->assertTrue($this->isTextPresent("Your CiviCRM Profile 'Profile $title' has been added. You can add fields to this profile now."), "Status message didn't show up after saving!");

      // add a profile field for activity
      $this->select("field_name[0]", "value=Activity");
      $this->waitForElementPresent("field_name[1]");
      $this->select("field_name[1]", "label=Field $title :: Group $title");
      
      $this->click("_qf_Field_next-bottom");
      $this->waitForPageToLoad("30000");
      $this->assertTrue($this->isTextPresent("Your CiviCRM Profile Field 'Field $title' has been saved to 'Profile $title'."), "Status message didn't show up after saving!");

      // create a survey
      $this->open($this->sboxPath . "civicrm/survey/add&reset=1");

      $this->waitForElementPresent("_qf_Survey_next-bottom");

      // fill in a unique title for the survey
      $this->type("title", "Survey $title");
      
      // select the created campaign
      $this->select("campaign_id", "label=Campaign $title");
      
      // select the activity type
      $this->select("activity_type_id", "value=27");

      // select the profile created for the survey
      $this->select("profile_id", "label=Profile $title");

      // create a set of options for Survey Responses
      $this->type("option_label_1", "Label $title 1");
      $this->type("option_value_1", "1");

      $this->type("option_label_2", "Label $title 2");
      $this->type("option_value_2", "2");

      // fill in reserve survey respondents
      $this->type("default_number_of_contacts", 50);
      
      // fill in interview survey respondents
      $this->type("max_number_of_contacts", 100);
      
      // release frequency
      $this->type("release_frequency", 2);
      
      $this->click("_qf_Survey_next-bottom");
      $this->waitForPageToLoad("30000");
      $this->assertTrue($this->isTextPresent("Survey Survey $title has been saved."), 
                        "Status message didn't show up after saving!");

      // Reserve Respondents
      $this->open($this->sboxPath . "civicrm/survey/search&reset=1&op=reserve");

      $this->waitForElementPresent("_qf_Search_refresh");

      // search for the respondents
      $this->select("campaign_survey_id", "label=Survey $title");

      $this->click("_qf_Search_refresh");

      $this->waitForElementPresent("Go");
      $this->click("CIVICRM_QFID_ts_all_4");
      $this->click("Go");

      $this->waitForElementPresent("_qf_Reserve_done_reserve-bottom");
      $this->click("_qf_Reserve_done_reserve-bottom");
      $this->waitForPageToLoad("30000");
      $this->assertTrue($this->isTextPresent("Reservation has been added for 2 Contact(s)."),
                        "Status message didn't show up after saving!");

      // Interview Respondents
      $this->open($this->sboxPath . "civicrm/survey/search&reset=1&op=interview");

      $this->waitForElementPresent("_qf_Search_refresh");

      // search for the respondents
      $this->select("campaign_survey_id", "label=Survey $title");

      $this->click("_qf_Search_refresh");

      $this->waitForElementPresent("Go");
      $this->click("CIVICRM_QFID_ts_all_4");
      $this->click("Go");

      $this->waitForElementPresent("_qf_Interview_cancel_interview");

      $this->click("CIVICRM_QFID_1_2");
      $this->select("xpath=//div[@id='voterRecords_wrapper']//table/tbody/tr[1]/td[6]/select", "value=Label $title 1");
      $this->click("xpath=//div[@id='voterRecords_wrapper']//table/tbody/tr[1]/td[7]/a");

      $this->click("CIVICRM_QFID_2_8");
      $this->select("xpath=//div[@id='voterRecords_wrapper']//table/tbody/tr[2]/td[6]/select", "value=Label $title 2");
      $this->click("xpath=//div[@id='voterRecords_wrapper']//table/tbody/tr[2]/td[7]/a");

      $this->click("_qf_Interview_cancel_interview");
      $this->waitForPageToLoad("30000");

      // add a contact to the group to test release respondents
      $firstName3 = substr(sha1(rand()), 0, 7);
      $this->webtestAddContact( $firstName3, "James", "$firstName3.james@example.org" );
      $url = $this->getLocation();
      $id  = explode( 'cid=', $url );
      $sortName3 = "James, $firstName3";
     
      // add contact to group
      // visit group tab
      $this->click("css=li#tab_group a");
      $this->waitForElementPresent("group_id");

      // add to group
      $this->select("group_id", "label=group_$title");
      $this->click("_qf_GroupContact_next");
      $this->waitForPageToLoad("30000");

      // Reserve Respondents
      $this->open($this->sboxPath . "civicrm/survey/search&reset=1&op=reserve");

      $this->waitForElementPresent("_qf_Search_refresh");

      // search for the respondents
      $this->select("campaign_survey_id", "label=Survey $title");

      $this->click("_qf_Search_refresh");

      $this->waitForElementPresent("Go");
      $this->click("CIVICRM_QFID_ts_all_4");
      $this->click("Go");

      $this->waitForElementPresent("_qf_Reserve_done_reserve-bottom");
      $this->click("_qf_Reserve_done_reserve-bottom");
      $this->waitForPageToLoad("30000");
      $this->assertTrue($this->isTextPresent("Reservation has been added for 3 Contact(s)."),
                        "Status message didn't show up after saving!");
      
      // Release Respondents
      $this->open($this->sboxPath . "civicrm/survey/search&reset=1&op=release");
      
      $this->waitForElementPresent("_qf_Search_refresh");

      // search for the respondents
      $this->select("campaign_survey_id", "label=Survey $title");

      $this->click("_qf_Search_refresh");

      $this->waitForElementPresent("Go");
      $this->click("xpath=id('mark_x_$id[1]')");
      
      $this->waitForElementPresent("Go");
      $this->click("Go");
      $this->waitForPageToLoad("30000");

      $this->waitForElementPresent("_qf_Release_done-bottom");
      $this->click("_qf_Release_done-bottom");
      $this->waitForPageToLoad("30000");
      $this->assertTrue($this->isTextPresent("1 respondent(s) have been released."),
                        "Status message didn't show up after saving!");  

      // check whether contact is available for reserving again
      $this->open($this->sboxPath . "civicrm/survey/search&reset=1&op=reserve");

      $this->waitForElementPresent("_qf_Search_refresh");

      // search for the respondents
      $this->select("campaign_survey_id", "label=Survey $title");

      $this->click("_qf_Search_refresh");
      $this->waitForPageToLoad("30000");
      $this->assertTrue($this->isTextPresent(" 1 Result"), "Status message didn't show up after saving!");
  }

  function addGroup( $groupName = 'New Group' ) {
      $this->open($this->sboxPath . "civicrm/group/add&reset=1");
      
      // As mentioned before, waitForPageToLoad is not always reliable. Below, we're waiting for the submit
      // button at the end of this page to show up, to make sure it's fully loaded.
      $this->waitForElementPresent("_qf_Edit_upload");

      // fill group name
      $this->type("title", $groupName);
      
      // fill description
      $this->type("description", "Adding new group.");

      // check Access Control
      $this->click("group_type[1]");

      // check Mailing List
      $this->click("group_type[2]");

      // select Visibility as Public Pages
      $this->select("visibility", "value=Public Pages");
      
      // Clicking save.
      $this->click("_qf_Edit_upload");
      $this->waitForPageToLoad("30000");

      // Is status message correct?
      $this->assertTrue($this->isTextPresent("The Group '$groupName' has been saved."));
  }
}