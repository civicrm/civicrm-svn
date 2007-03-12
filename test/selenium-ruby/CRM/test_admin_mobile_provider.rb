# This is a test case of using Selenium and its Ruby bindings
# Information' Mobile Phone Provider definition
# This test case allows you to add/edit/disable/enable/delete Mobile Phone Provider information

require 'crm_page_controller'
require '../selenium'

class TC_TestAdminMobileProvider < Test::Unit::TestCase
  def setup
    @page = CRMPageController.new
    @selenium = @page.start_civicrm
    @page.login
  end
  
  def teardown
    @page.logout
  end
  
  def test_mobile_provider
    move_to_mobile_provider()
    
    add_mobile_provider()
    edit_mobile_provider()
    disable_mobile_provider()
    enable_mobile_provider()
    delete_mobile_provider()
  end
  
  #get Mobile Provider link
  def move_to_mobile_provider
    #Click Administer CiviCRM
    assert_equal "Administer CiviCRM", @selenium.get_text("link=Administer CiviCRM")
    @page.click_and_wait "link=Administer CiviCRM"
    
    #Click Mobile Phone Provider 
    assert_equal "Mobile\nPhone\nProviders", @selenium.get_text("//a[@id='id_MobilePhoneProviders']")
    @page.click_and_wait "//a[@id='id_MobilePhoneProviders']"
  end

  # Add new Mobile Phone Service Provider 
  def add_mobile_provider
    if @selenium.is_text_present("There are no option values entered. You can add one")
      @page.click_and_wait "link=add one"
    else
      assert_equal "» New Mobile Provider", @selenium.get_text("link=» New Mobile Provider")
      @page.click_and_wait "link=» New Mobile Provider"    
    end
        
    # Read new Mobile Phone Service Name
    @selenium.type  "label", "New Provider"
    
    if @selenium.get_value("//input[@type='checkbox' and @name='is_active']") == 'off'
      @selenium.check 'is_active'
    end
    
    # Submit the form 
    @page.click_and_wait "//input[@type='submit' and @name='_qf_Options_next']"
    assert @selenium.is_text_present("The Mobile Provider \"New Provider\" has been saved.")
  end
  
  # Edit Mobile Phone Service Provider
  def edit_mobile_provider
    assert_equal "Edit", @selenium.get_text("//div[@id='mobile_provider']/descendant::tr[td[contains(.,'New Provider')]]/descendant::a[contains(.,'Edit')]")
    @page.click_and_wait "//div[@id='mobile_provider']/descendant::tr[td[contains(.,'New Provider')]]/descendant::a[contains(.,'Edit')]"
    
    # Read new Mobile Phone Provider Service
    @selenium.type "label", "New Mobile Provider"
    assert_equal 'on', @selenium.get_value("//input[@type='checkbox' and @name='is_active']")
    
    # Submit the form 
    @page.click_and_wait "//input[@type='submit' and @name='_qf_Options_next']"
    assert @selenium.is_text_present("The Mobile Provider \"New Mobile Provider\" has been saved.")
  end
  
  # Disable Mobile Phone Service
  def disable_mobile_provider
    assert_equal "Disable", @selenium.get_text("//div[@id='mobile_provider']/descendant::tr[td[contains(.,'New Mobile Provider')]]/descendant::a[contains(.,'Disable')]")
    @page.click_and_wait "//div[@id='mobile_provider']/descendant::tr[td[contains(.,'New Mobile Provider')]]/descendant::a[contains(.,'Disable')]"
    assert_equal "Are you sure you want to disable this Mobile Provider?\n\nUsers will no longer be able to select this value when adding or editing Mobile Provider.", @selenium.get_confirmation( )
  end
  
  # Enable Mobile Phone Service
  def enable_mobile_provider
    assert_equal "Enable", @selenium.get_text("//div[@id='mobile_provider']/descendant::tr[td[contains(.,'New Mobile Provider')]]/descendant::a[contains(.,'Enable')]")
    @page.click_and_wait "//div[@id='mobile_provider']/descendant::tr[td[contains(.,'New Mobile Provider')]]/descendant::a[contains(.,'Enable')]"
  end
  
  # Delete Mobile Phone Provider 
  def delete_mobile_provider
    assert_equal "Delete", @selenium.get_text("//div[@id='mobile_provider']/descendant::tr[td[contains(.,'New Mobile Provider')]]/descendant::a[contains(.,'Delete')]")
    @page.click_and_wait "//div[@id='mobile_provider']/descendant::tr[td[contains(.,'New Mobile Provider')]]/descendant::a[contains(.,'Delete')]"
    assert @selenium.is_text_present("WARNING: Deleting this option will result in the loss of all Mobile Provider related records which use the option. This may mean the loss of a substantial amount of data, and the action cannot be undone. Do you want to continue?")
    @page.click_and_wait "//input[@type='submit' and @name='_qf_Options_next']"
  end
end
