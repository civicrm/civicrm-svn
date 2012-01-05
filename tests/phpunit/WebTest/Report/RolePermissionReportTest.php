<?php

  /*
   +--------------------------------------------------------------------+
   | CiviCRM version 4.1                                                |
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

class WebTest_Report_RolePermissionReportTest extends CiviSeleniumTestCase {
    
    protected function setUp()
    {
        parent::setUp();
    }
    
    function testRolePermissionReport( )
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
        
        //create new roles
        $role1 = 'role1'.substr(sha1(rand()), 0, 7);
        $role2 = 'role2'.substr(sha1(rand()), 0, 7);
        $this->open($this->sboxPath . "admin/people/permissions/roles" );
        $this->waitForElementPresent("edit-add");
        $this->type("edit-name",$role1);          
        $this->click("edit-add");
        $this->open($this->sboxPath . "admin/people/permissions/roles" );
        $this->waitForElementPresent("edit-add");
        $this->type("edit-name",$role2);          
        $this->click("edit-add");
        $this->open($this->sboxPath . "admin/people/permissions/roles" );

        $this->waitForElementPresent("xpath=//table[@id='user-roles']/tbody//tr/td[1][text()='{$role1}']");
        $roleid = explode( '/', $this->getAttribute( "xpath=//table[@id='user-roles']/tbody//tr/td[1][text()='{$role1}']/../td[4]/a[text()='edit permissions']/@href" ) );
        $roleId1 = end($roleid);
        $this->waitForElementPresent("xpath=//table[@id='user-roles']/tbody//tr/td[1][text()='{$role2}']");
        $roleid = explode( '/', $this->getAttribute( "xpath=//table[@id='user-roles']/tbody//tr/td[1][text()='{$role2}']/../td[4]/a[text()='edit permissions']/@href" ) );
        $roleId2 = end($roleid);
        
        $user1 = $this->_testCreateUser( $roleId1 ); 
        $user2 = $this->_testCreateUser( $roleId2 ); 
        
        // let's give full CiviReport permissions.
        $permissions = array(
                             "edit-2-access-civireport",
                             "edit-2-view-all-contacts",
                             "edit-2-administer-civicrm",
                             "edit-2-access-civicrm"
                             );
        $this->changePermissions( $permissions );
        
        // change report setting to for a particular role
        $this->open($this->sboxPath . "civicrm/report/instance/1?reset=1");
        $this->waitForPageToLoad("30000");
        $this->click("css=div.crm-report_setting-accordion div.crm-accordion-header");
        $this->waitForElementPresent("_qf_Summary_submit_save");
        $this->select("permission", "value=access CiviCRM");
        $this->select("grouprole-f","value=$role1");
        $this->click("add");
        $this->click("_qf_Summary_submit_save");
        $this->waitForPageToLoad("30000");
        $this->open( $this->sboxPath . "civicrm/logout?reset=1" );
        $this->open( $this->sboxPath );
        $this->waitForElementPresent('edit-submit');
        $this->type('edit-name', $user2);
        $this->type('edit-pass', 'Test12345');
        $this->click('edit-submit');
        $this->waitForPageToLoad('30000'); 
        $this->open($this->sboxPath . "civicrm/report/instance/1?reset=1" );
        $this->waitForPageToLoad('30000');
        $this->assertTrue($this->isTextPresent("You do not have permission to access this report." ) );
        $this->open($this->sboxPath . "civicrm/report/list?reset=1" );
        $this->waitForPageToLoad('30000');
        $this->open( $this->sboxPath . "civicrm/logout?reset=1" );
        
    }  
    
    function _testCreateUser( $roleid ) {
        
        // Go directly to the URL of the screen that will Create User Authentically.
        $this->open( $this->sboxPath . "admin/people/create" );
        
        $this->waitForElementPresent( "edit-submit" );
        
        $name = "TestUser" . substr(sha1(rand()), 0, 4);
        $this->type( "edit-name", $name );
        
        $emailId   = substr(sha1(rand()), 0, 7).'@web.com';
        $this->type( "edit-mail", $emailId );
        $this->type( "edit-pass-pass1", "Test12345" );
        $this->type( "edit-pass-pass2", "Test12345" );
        $role = "edit-roles-".$roleid;
        $this->check("name=roles[$roleid] value={$roleid}");
        
        //Add profile Details 
        $firstName = 'Ma'.substr(sha1(rand()), 0, 4);
        $lastName  = 'An'.substr(sha1(rand()), 0, 7);
        
        $this->type( "first_name", $firstName );
        $this->type( "last_name", $lastName );
        
        //Address Details
        $this->type( "street_address-1", "902C El Camino Way SW" );
        $this->type( "city-1", "Dumfries" );
        $this->type( "postal_code-1", "1234" );
        $this->select( "state_province-1", "value=1019" );
        
        $this->click( "edit-submit" );
        $this->waitForPageToLoad( "30000" );
        return $name;
    }
}
?>
