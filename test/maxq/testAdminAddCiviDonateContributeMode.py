# Generated by MaxQ [com.bitmechanic.maxq.generator.JythonCodeGenerator]
from PyHttpTestCase import PyHttpTestCase
from com.bitmechanic.maxq import Config
from com.bitmechanic.maxq import DBUtil
import commonConst, commonAPI
global validatorPkg
if __name__ == 'main':
    validatorPkg = Config.getValidatorPkgName()
# Determine the validator for this testcase.
exec 'from '+validatorPkg+' import Validator'


# definition of test class
class testAdminAddCiviDonateContributeMode(PyHttpTestCase):
    def setUp(self):
        global db
        db = commonAPI.dbStart()
    
    def tearDown(self):
        commonAPI.dbStop(db)
    
    def runTest(self):
        self.msg('Test started')
        
        drupal_path = commonConst.DRUPAL_PATH
        
        commonAPI.login(self)
        
        params = [
            ('''reset''', '''1'''),]
        url = "%s/civicrm/contribute/admin" % drupal_path
        self.msg("Testing URL: %s" % url)
        Validator.validateRequest(self, self.getMethod(), "get", url, params)
        self.get(url, params)
        self.msg("Response code: %s" % self.getResponseCode())
        self.assertEquals("Assert number 7 failed", 200, self.getResponseCode())
        Validator.validateResponse(self, self.getMethod(), url, params)
        
        url = "%s/civicrm/contribute/admin/contributionMode" % drupal_path
        self.msg("Testing URL: %s" % url)
        params = None
        Validator.validateRequest(self, self.getMethod(), "get", url, params)
        self.get(url, params)
        self.msg("Response code: %s" % self.getResponseCode())
        self.assertEquals("Assert number 8 failed", 200, self.getResponseCode())
        Validator.validateResponse(self, self.getMethod(), url, params)
        
        params = [
            ('''action''', '''add'''),
            ('''reset''', '''1'''),]
        url = "%s/civicrm/contribute/admin/contributionMode" % drupal_path
        self.msg("Testing URL: %s" % url)
        Validator.validateRequest(self, self.getMethod(), "get", url, params)
        self.get(url, params)
        self.msg("Response code: %s" % self.getResponseCode())
        self.assertEquals("Assert number 9 failed", 200, self.getResponseCode())
        Validator.validateResponse(self, self.getMethod(), url, params)
        
        params = [
            ('''_qf_default''', '''ContributionMode:next'''),
            ('''name''', '''New Contribution'''),
            ('''description''', '''new mode'''),
            ('''is_active''', '''0'''),
            ('''_qf_ContributionMode_next''', '''Save'''),]
        
        url = "%s/civicrm/contribute/admin/contributionMode" % drupal_path
        self.msg("Testing URL: %s" % url)
        
        name = '%s' % params[1][1]
        queryID = "select id from civicrm_contribution_mode where name=\'%s\'" % name
        cid     = db.loadVal(queryID)
        
        Validator.validateRequest(self, self.getMethod(), "post", url, params)
        self.post(url, params)
        
        if cid:
            self.msg("Response code: %s" % self.getResponseCode())
            self.assertEquals("Assert number 10 failed", 200, self.getResponseCode())
            Validator.validateResponse(self, self.getMethod(), url, params)
            print "******************************************************************"
            print "Contribution Mode \'%s\' already exists." % name
            print "******************************************************************"
        else :
            self.msg("Response code: %s" % self.getResponseCode())
            self.assertEquals("Assert number 11 failed", 302, self.getResponseCode())
            Validator.validateResponse(self, self.getMethod(), url, params)
            print "******************************************************************"
            print "Contribution Mode \'%s\' added successfully." % name
            print "******************************************************************"
        
        params = [
            ('''reset''', '''1'''),
            ('''action''', '''browse'''),]
        url = "%s/civicrm/contribute/admin/contributionMode" % drupal_path
        self.msg("Testing URL: %s" % url)
        Validator.validateRequest(self, self.getMethod(), "get", url, params)
        self.get(url, params)
        self.msg("Response code: %s" % self.getResponseCode())
        self.assertEquals("Assert number 12 failed", 200, self.getResponseCode())
        Validator.validateResponse(self, self.getMethod(), url, params)
        
        commonAPI.logout(self)
    # ^^^ Insert new recordings here.  (Do not remove this line.)


# Code to load and run the test
if __name__ == 'main':
    test = testAdminAddCiviDonateContributeMode("testAdminAddCiviDonateContributeMode")
    test.Run()
