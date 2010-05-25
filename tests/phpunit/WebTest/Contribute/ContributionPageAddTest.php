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

class WebTest_Contribute_ContributionPageAddTest extends CiviSeleniumTestCase {

    function testContributionPageAdd()
    {
        // a random 7-char string and an even number to make this pass unique
        $hash = substr(sha1(rand()), 0, 7);
        $rand = 2 * rand(2, 50);

        // open browser, login, go to the New Contribution Page page
        $this->open($this->sboxPath);
        $this->webtestLogin();
        $this->open($this->sboxPath . 'civicrm/admin/contribute?action=add&reset=1');
        $this->waitForPageToLoad();

        // fill in step 1 (Title and Settings)
        $this->type('title', "Title $hash");
        $this->select('contribution_type_id', 'value=1');

        $this->click('is_organization');
        $this->type('for_organization', "On behalf $hash");
        $this->click('CIVICRM_QFID_2_Require');

        // FIXME: handle Introductory/Footer Message

        $this->type('goal_amount', 10 * $rand);

        // FIXME: handle Start/End Date/Time

        $this->click('honor_block_is_active');
        $this->type('honor_block_title', "Honoree Section Title $hash");
        $this->type('honor_block_text',  "Honoree Introductory Message $hash");

        // go to step 2
        $this->click('_qf_Settings_next');
        $this->waitForPageToLoad();

        // fill in step 2 (Amounts)
        $this->click('is_pay_later');
        $this->type('pay_later_text',    "Pay later label $hash");
        $this->type('pay_later_receipt', "Pay later instructions $hash");

        $this->click('is_pledge_active');
        $this->click('pledge_frequency_unit[week]');
        $this->click('is_pledge_interval');
        $this->type('initial_reminder_day',    3);
        $this->type('max_reminders',           2);
        $this->type('additional_reminder_day', 1);

        $this->click('is_allow_other_amount');
        $this->type('min_amount', $rand / 2);
        $this->type('max_amount', $rand * 2);

        $this->type('label_1', "Label $hash");
        $this->type('value_1', "$rand");

        // go to step 3
        $this->click('_qf_Amount_next');
        $this->waitForPageToLoad();

        // fill in step 3 (Memberships)

        // go to step 4
        $this->click('_qf_MembershipBlock_next');
        $this->waitForPageToLoad();

        // fill in step 4 (Thanks and Receipt)
        $this->type('thankyou_title',     "Thank-you Page Title $hash");
        $this->type('receipt_from_email', "$hash@example.org");

        // go to step 5
        $this->click('_qf_ThankYou_next');
        $this->waitForPageToLoad();

        // fill in step 5 (Tell a Friend)

        // go to step 6
        $this->click('_qf_Contribute_next');
        $this->waitForPageToLoad();

        // fill in step 6 (Include Profiles)

        // go to step 7
        $this->click('_qf_Custom_next');
        $this->waitForPageToLoad();

        // fill in step 7 (Premiums)

        // go to step 8
        $this->click('_qf_Premium_next');
        $this->waitForPageToLoad();

        // fill in step 8 (Widget Settings)

        // go to step 9
        $this->click('_qf_Widget_next');
        $this->waitForPageToLoad();

        // fill in step 9 (Enable Personal Campaign Pages)

        // submit new contribution page
        $this->click('_qf_PCP_next');
        $this->waitForPageToLoad();

        $this->assertTrue($this->isTextPresent("Title $hash"));
    }
}
