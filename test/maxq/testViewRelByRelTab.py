# Generated by MaxQ [com.bitmechanic.maxq.generator.JythonCodeGenerator]
from PyHttpTestCase import PyHttpTestCase
from com.bitmechanic.maxq import Config
from com.bitmechanic.maxq import DBUtil
import Common
global validatorPkg
if __name__ == 'main':
    validatorPkg = Config.getValidatorPkgName()
# Determine the validator for this testcase.
exec 'from '+validatorPkg+' import Validator'


# definition of test class
class testViewRelByRelTab(PyHttpTestCase):
    def runTest(self):
        self.msg('Test started')

        drupal_path = Common.DRUPAL_PATH
        
        #self.msg("Testing URL: %s" % self.replaceURL('''%s/''') % drupal_path)
        url = "%s/" % drupal_path
        self.msg("Testing URL: %s" % url)
        params = None
        Validator.validateRequest(self, self.getMethod(), "get", url, params)
        self.get(url, params)
        self.msg("Response code: %s" % self.getResponseCode())
        self.assertEquals("Assert number 1 failed", 200, self.getResponseCode())
        Validator.validateResponse(self, self.getMethod(), url, params)

        params = [
            ('''edit[destination]''', '''node'''),
            ('''edit[name]''', Common.USERNAME),
            ('''edit[pass]''', Common.PASSWORD),
            ('''op''', '''Log in'''),]
        #self.msg("Testing URL: %s" % self.replaceURL('''%s/user/login?edit[destination]=node&edit[name]=manishzope&edit[pass]=manish&op=Log in''') % drupal_path)
        url = "%s/user/login" % drupal_path
        self.msg("Testing URL: %s" % url)
        Validator.validateRequest(self, self.getMethod(), "post", url, params)
        self.post(url, params)
        self.msg("Response code: %s" % self.getResponseCode())
        self.assertEquals("Assert number 2 failed", 302, self.getResponseCode())
        Validator.validateResponse(self, self.getMethod(), url, params)
        
        #self.msg("Testing URL: %s" % self.replaceURL('''%s/node''') % drupal_path)
        url = "%s/node" % drupal_path
        self.msg("Testing URL: %s" % url)
        params = None
        Validator.validateRequest(self, self.getMethod(), "get", url, params)
        self.get(url, params)
        self.msg("Response code: %s" % self.getResponseCode())
        self.assertEquals("Assert number 3 failed", 200, self.getResponseCode())
        Validator.validateResponse(self, self.getMethod(), url, params)

        #self.msg("Testing URL: %s" % self.replaceURL('''%s/civicrm/contact/search''') % drupal_path)
        url = "%s/civicrm/contact/search" % drupal_path
        self.msg("Testing URL: %s" % url)
        params = None
        Validator.validateRequest(self, self.getMethod(), "get", url, params)
        self.get(url, params)
        self.msg("Response code: %s" % self.getResponseCode())
        self.assertEquals("Assert number 4 failed", 200, self.getResponseCode())
        Validator.validateResponse(self, self.getMethod(), url, params)

        params = [
            ('''_qf_default''', '''Search:refresh'''),
            ('''contact_type''', '''Individual'''),
            ('''group''', ''''''),
            ('''tag''', ''''''),
            ('''sort_name''', '''Zope'''),
            ('''_qf_Search_refresh''', '''Search'''),]
        #self.msg("Testing URL: %s" % self.replaceURL('''%s/civicrm/contact/search?_qf_default=Search:refresh&contact_type=&group=&tag=&sort_name=&_qf_Search_refresh=Search''') % drupal_path)
        url = "%s/civicrm/contact/search" % drupal_path
        self.msg("Testing URL: %s" % url)
        Validator.validateRequest(self, self.getMethod(), "post", url, params)
        self.post(url, params)
        self.msg("Response code: %s" % self.getResponseCode())
        self.assertEquals("Assert number 7 failed", 302, self.getResponseCode())
        Validator.validateResponse(self, self.getMethod(), url, params)
        
        params = [
            ('''_qf_Search_display''', '''true'''),]
        #self.msg("Testing URL: %s" % self.replaceURL('''%s/civicrm/contact/search?_qf_Search_display=true''') % drupal_path)
        url = "%s/civicrm/contact/search" % drupal_path
        self.msg("Testing URL: %s" % url)
        Validator.validateRequest(self, self.getMethod(), "get", url, params)
        self.get(url, params)
        self.msg("Response code: %s" % self.getResponseCode())
        self.assertEquals("Assert number 8 failed", 200, self.getResponseCode())
        Validator.validateResponse(self, self.getMethod(), url, params)
        
        params = [
            ('''reset''', '''1'''),
            ('''cid''', '''43'''),]
        #self.msg("Testing URL: %s" % self.replaceURL('''%s/civicrm/contact/view?reset=1&cid=43''') % drupal_path)
        url = "%s/civicrm/contact/view" % drupal_path
        self.msg("Testing URL: %s" % url)
        Validator.validateRequest(self, self.getMethod(), "get", url, params)
        self.get(url, params)
        self.msg("Response code: %s" % self.getResponseCode())
        self.assertEquals("Assert number 10 failed", 200, self.getResponseCode())
        Validator.validateResponse(self, self.getMethod(), url, params)
        
        #self.msg("Testing URL: %s" % self.replaceURL('''%s/civicrm/contact/view/rel''') % drupal_path)
        url = "%s/civicrm/contact/view/rel" % drupal_path
        self.msg("Testing URL: %s" % url)
        params = None
        Validator.validateRequest(self, self.getMethod(), "get", url, params)
        self.get(url, params)
        self.msg("Response code: %s" % self.getResponseCode())
        self.assertEquals("Assert number 5 failed", 200, self.getResponseCode())
        Validator.validateResponse(self, self.getMethod(), url, params)
        
        #self.msg("Testing URL: %s" % self.replaceURL('''http://localhost/favicon.ico'''))
        #url = "http://localhost/favicon.ico"
        #params = None
        #Validator.validateRequest(self, self.getMethod(), "get", url, params)
        #self.get(url, params)
        #self.msg("Response code: %s" % self.getResponseCode())
        #self.assertEquals("Assert number 6 failed", 404, self.getResponseCode())
        #Validator.validateResponse(self, self.getMethod(), url, params)
        
        params = [
            ('''action''', '''view'''),
            ('''rid''', '''50'''),
            ('''rtype''', '''b_a'''),]
        #self.msg("Testing URL: %s" % self.replaceURL('''%s/civicrm/contact/view/rel?action=view&rid=50&rtype=b_a''') % drupal_path)
        url = "%s/civicrm/contact/view/rel" % drupal_path
        self.msg("Testing URL: %s" % url)
        Validator.validateRequest(self, self.getMethod(), "get", url, params)
        self.get(url, params)
        self.msg("Response code: %s" % self.getResponseCode())
        self.assertEquals("Assert number 7 failed", 200, self.getResponseCode())
        Validator.validateResponse(self, self.getMethod(), url, params)
        
        db = DBUtil("%s" % Common.MSQLDRIVER, "jdbc:mysql://%s/%s" % (Common.DBHOST, Common.DBNAME), "%s" % Common.DBUSERNAME, "%s" % Common.DBPASSWORD)

        queryA       = 'select contact_id_a from crm_relationship where id=%s' % params[1][1]
        queryB       = 'select contact_id_b from crm_relationship where id=%s' % params[1][1]
        queryRType   = 'select relationship_type_id from crm_relationship where id=%s' % params[1][1]
        
        contactIdA   = db.loadVal(queryA)
        contactIdB   = db.loadVal(queryB)
        relationId   = db.loadVal(queryRType)

        queryCA      = 'select sort_name from crm_contact where id=%s' % int(contactIdA)
        queryCB      = 'select sort_name from crm_contact where id=%s' % int(contactIdB)

        contactNameA = db.loadVal(queryCA)
        contactNameB = db.loadVal(queryCB)
        
        queryRA      = 'select name_a_b from crm_relationship_type where id=%s' % relationId
        queryRB      = 'select name_b_a from crm_relationship_type where id=%s' % relationId

        relationA    = db.loadVal(queryRA)
        relationB    = db.loadVal(queryRB)
                               
        db.close()

        if self.responseContains(relationA) and self.responseContains(contactNameB) :
            print ("************************************************************************************")
            print ("Page Response Shows the Relationship as : ")
            print ("------------------------------------------------------------------------------------")
            print ("\'" + contactNameA + "\' -- " + relationA + " -- \'" + contactNameB + "\'")
            print ("------------------------------------------------------------------------------------")
            print ("************************************************************************************")
            print ("\n")
            print ("************************************************************************************")
            print ("Check for the same Relationship through second contact's view : ")
            print ("------------------------------------------------------------------------------------")
            print ("\'" + contactNameB + "\' -- " + relationB + " -- \'" + contactNameA + "\'")
            print ("------------------------------------------------------------------------------------")
            print ("************************************************************************************")
        else :
            print ("************************************************************************************")
            print ("Database Values and Response by the Test Script \"Do Not Match\"")
            print ("************************************************************************************")
        
        params = [
            ('''action''', '''browse'''),]
        #self.msg("Testing URL: %s" % self.replaceURL('''%s/civicrm/contact/view/rel?action=browse''') % drupal_path)
        url = "%s/civicrm/contact/view/rel" % drupal_path
        self.msg("Testing URL: %s" % url)
        Validator.validateRequest(self, self.getMethod(), "get", url, params)
        self.get(url, params)
        self.msg("Response code: %s" % self.getResponseCode())
        self.assertEquals("Assert number 9 failed", 200, self.getResponseCode())
        Validator.validateResponse(self, self.getMethod(), url, params)

        #self.msg("Testing URL: %s" % self.replaceURL('''http://localhost/favicon.ico'''))
        #url = "http://localhost/favicon.ico"
        #params = None
        #Validator.validateRequest(self, self.getMethod(), "get", url, params)
        #self.get(url, params)
        #self.msg("Response code: %s" % self.getResponseCode())
        #self.assertEquals("Assert number 10 failed", 404, self.getResponseCode())
        #Validator.validateResponse(self, self.getMethod(), url, params)
        
        self.msg('Test successfully complete.')
    # ^^^ Insert new recordings here.  (Do not remove this line.)


# Code to load and run the test
if __name__ == 'main':
    test = testViewRelByRelTab("testViewRelByRelTab")
    test.Run()
