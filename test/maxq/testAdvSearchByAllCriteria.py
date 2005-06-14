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
class testAdvSearchByAllCriteria(PyHttpTestCase):
    def setUp(self):
        global db
        db = commonAPI.dbStart()
    
    def tearDown(self):
        commonAPI.dbStop(db)
    
    def runTest(self):
        self.msg('Test started')

        drupal_path = commonConst.DRUPAL_PATH

        commonAPI.login(self)

        #self.msg("Testing URL: %s" % self.replaceURL('''%s/civicrm/contact/search/advanced''') % drupal_path)
        url = "%s/civicrm/contact/search/advanced" % drupal_path
        self.msg("Testing URL: %s" % url)
        params = None
        Validator.validateRequest(self, self.getMethod(), "get", url, params)
        self.get(url, params)
        self.msg("Response code: %s" % self.getResponseCode())
        self.assertEquals("Assert number 4 failed", 200, self.getResponseCode())
        Validator.validateResponse(self, self.getMethod(), url, params)
        
        # self.msg("Testing URL: %s" % self.replaceURL('''http://localhost/favicon.ico'''))
        # url = "http://localhost/favicon.ico"
        # params = None
        # Validator.validateRequest(self, self.getMethod(), "get", url, params)
        # self.get(url, params)
        # self.msg("Response code: %s" % self.getResponseCode())
        # self.assertEquals("Assert number 5 failed", 404, self.getResponseCode())
        # Validator.validateResponse(self, self.getMethod(), url, params)
        
        params = [
            ('''_qf_default''', '''Advanced:refresh'''),
            ('''sort_name''', '''zope'''),
            ('''_qf_Advanced_refresh''', '''Search'''),
            ('''cb_group[1]''', '''1'''),
            ('''street_name''', '''Bay'''),
            ('''city''', '''Albany'''),
            ('''state_province''', '''1031'''),
            ('''country''', '''1228'''),
            ('''postal_code''', ''''''),
            ('''postal_code_low''', '''400000'''),
            ('''postal_code_high''', '''405000'''),]
        url = "%s/civicrm/contact/search/advanced" % drupal_path
        Validator.validateRequest(self, self.getMethod(), "post", url, params)
        self.post(url, params)
        self.msg("Response code: %s" % self.getResponseCode())
        self.assertEquals("Assert number 6 failed", 302, self.getResponseCode())
        Validator.validateResponse(self, self.getMethod(), url, params)
        
        name       = '%s' % params[1][1]
        street     = '%s' % params[4][1]
        city       = '%s' % params[5][1]
        stateQuery = 'select name from crm_state_province where id=%s' % params[6][1]
        state   = db.loadVal(stateQuery)
        countryQuery = 'select name from crm_country where id=%s' % params[7][1]
        country = db.loadVal(countryQuery)
        postalL = '%s' % params[9][1] 
        postalH = '%s' % params[10][1]
        group   = 'Newsletter Subscribers'
        
        query = 'SELECT count(DISTINCT crm_contact.id)  FROM crm_contact \
        LEFT JOIN crm_location ON (crm_contact.id = crm_location.contact_id AND crm_location.is_primary = 1) \
        LEFT JOIN crm_address ON crm_location.id = crm_address.location_id \
        LEFT JOIN crm_phone ON (crm_location.id = crm_phone.location_id AND crm_phone.is_primary = 1) \
        LEFT JOIN crm_email ON (crm_location.id = crm_email.location_id AND crm_email.is_primary = 1) \
        LEFT JOIN crm_state_province ON crm_address.state_province_id = crm_state_province.id \
        LEFT JOIN crm_country ON crm_address.country_id = crm_country.id \
        LEFT JOIN crm_group_contact ON crm_contact.id = crm_group_contact.contact_id \
        LEFT JOIN crm_entity_tag ON crm_contact.id = crm_entity_tag.entity_id \
        WHERE group_id=1 AND crm_group_contact.status=\"In\" AND LOWER(crm_contact.sort_name) LIKE \'%%%s%%\' AND LOWER(crm_address.street_name) LIKE \'%%%s%%\' AND LOWER(crm_address.city) LIKE \'%%%s%%\' AND crm_address.state_province_id=%s AND crm_address.country_id=%s AND crm_address.postal_code>=%s AND crm_address.postal_code<=%s AND 1' % (name, street, city, params[6][1], params[7][1], postalL, postalH)
        
        noOfContact = db.loadVal(query)
        if noOfContact == '1' :
            string = "Found %s contact" % noOfContact
        else :
            string = "Found %s contacts" % noOfContact

        params = [
            ('''_qf_Advanced_display''', '''true'''),]
        #self.msg("Testing URL: %s" % self.replaceURL('''%s/civicrm/contact/search/advanced?_qf_Advanced_display=true''') % drupal_path)
        url = "%s/civicrm/contact/search/advanced" % drupal_path
        self.msg("Testing URL: %s" % url)
        Validator.validateRequest(self, self.getMethod(), "get", url, params)
        self.get(url, params)
        self.msg("Response code: %s" % self.getResponseCode())
        self.assertEquals("Assert number 7 failed", 200, self.getResponseCode())
        Validator.validateResponse(self, self.getMethod(), url, params)

        print ("*********************************************************************************")
        print ("The Citeria for search is ")
        self.msg ("%s : %s" % ("Sort Name      ", name))
        self.msg ("%s : %s" % ("Group          ", group))
        self.msg ("%s : %s" % ("Street Name    ", street))
        self.msg ("%s : %s" % ("City           ", city))
        self.msg ("%s : %s" % ("State Province ", state))
        self.msg ("%s : %s" % ("Country        ", country))
        self.msg ("%s : %s to %s" % ("Postal Code    ", postalL, postalH))
        print ("*********************************************************************************")
        
        if self.responseContains(string) :
            print ("*********************************************************************************")
            self.msg ("Search \"%s\"" % string)
            print ("*********************************************************************************")
        
        elif noOfContact == '0' :
            print ("*********************************************************************************")
            self.msg("The Response is \"%s\"" % string )
            print ("*********************************************************************************")            
        
        else :
            print ("*********************************************************************************")
            self.msg("The Response does not match with the result from the database ")
            print ("*********************************************************************************")            

    # ^^^ Insert new recordings here.  (Do not remove this line.)


# Code to load and run the test
if __name__ == 'main':
    test = testAdvSearchByAllCriteria("testAdvSearchByAllCriteria")
    test.Run()
