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
class testEditLocationType(PyHttpTestCase):
    def setUp(self):
        global db
        db = commonAPI.dbStart()
    
    def tearDown(self):
        commonAPI.dbStop(db)
    
    def runTest(self):
        self.msg('Test started')

        drupal_path = commonConst.DRUPAL_PATH

        commonAPI.login(self)

        #self.msg("Testing URL: %s" % self.replaceURL('''%s/civicrm/admin/locationType''') % drupal_path)
        url = "%s/civicrm/admin/locationType" % drupal_path
        self.msg("Testing URL: %s" % url)
        params = None
        Validator.validateRequest(self, self.getMethod(), "get", url, params)
        self.get(url, params)
        self.msg("Response code: %s" % self.getResponseCode())
        self.assertEquals("Assert number 5 failed", 200, self.getResponseCode())
        Validator.validateResponse(self, self.getMethod(), url, params)

        name        = '\'Test Location Type\''
        query       = 'select id from crm_location_type where name=%s' % name  
        locationTID = db.loadVal(query)

        LTID = '''%s''' % locationTID 
        params = [
            ('''action''', '''update'''),
            ('''id''', LTID),]
        #self.msg("Testing URL: %s" % self.replaceURL('''%s/civicrm/admin/locationType?action=update&id=5''') % drupal_path)
        url = "%s/civicrm/admin/locationType" % drupal_path
        self.msg("Testing URL: %s" % url)
        Validator.validateRequest(self, self.getMethod(), "get", url, params)
        self.get(url, params)
        self.msg("Response code: %s" % self.getResponseCode())
        self.assertEquals("Assert number 6 failed", 200, self.getResponseCode())
        Validator.validateResponse(self, self.getMethod(), url, params)
        
        params = [
            ('''_qf_default''', '''LocationType:next'''),
            ('''name''', '''Test Location Type'''),
            ('''description''', '''This is test Location Type.... test for editing the location ytpe'''),
            ('''_qf_LocationType_next''', '''Save'''),]
        url = "%s/civicrm/admin/locationType" % drupal_path
        self.msg("Testing URL: %s" % url)
        Validator.validateRequest(self, self.getMethod(), "post", url, params)
        self.post(url, params)
        self.msg("Response code: %s" % self.getResponseCode())
        self.assertEquals("Assert number 7 failed", 302, self.getResponseCode())
        Validator.validateResponse(self, self.getMethod(), url, params)
        
        params = [
            ('''reset''', '''1'''),
            ('''action''', '''browse'''),]
        #self.msg("Testing URL: %s" % self.replaceURL('''%s/civicrm/admin/locationType?reset=1&action=browse''') % drupal_path)
        url = "%s/civicrm/admin/locationType" % drupal_path
        self.msg("Testing URL: %s" % url)
        Validator.validateRequest(self, self.getMethod(), "get", url, params)
        self.get(url, params)
        self.msg("Response code: %s" % self.getResponseCode())
        self.assertEquals("Assert number 8 failed", 200, self.getResponseCode())
        Validator.validateResponse(self, self.getMethod(), url, params)

        print ("**************************************************************************************")
        print "Location Type \'%s\' is Edited Successfully" % name
        print ("**************************************************************************************")
        
    # ^^^ Insert new recordings here.  (Do not remove this line.)


# Code to load and run the test
if __name__ == 'main':
    test = testEditLocationType("testEditLocationType")
    test.Run()
