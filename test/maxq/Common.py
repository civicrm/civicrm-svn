# This file have all the constant required for carrying out maxq generated test scripts.


# Constants For Localhost
#
# The following constants are for carrying out tests on localhost  
# If the tests need to be carried on the locahost,  
# following constants should be uncommented and
# Contastants For sandbox.openngo.org should be commented.
USERNAME    = '''manishzope'''
PASSWORD    = '''manish'''

HOST        = "localhost"
DRUPAL_PATH = "http://" + HOST  + "/drupal"



# Constants For sandbox.openngo.org
#
# The following constants are for carrying out tests on sandbox.openngo.org  
# If the tests need to be carried on the sandbox.openngo.org,
# following constants should be uncommented and
# Contastants For Localhost should be commented.
#USERNAME    = '''demo'''
#PASSWORD    = '''demo'''

#HOST        = "sandbox.openngo.org"
#DRUPAL_PATH = "http://" + HOST  + "/crm"



# Following constant declares the database driver
# needed for database connection while carrying test.
MSQLDRIVER  = "org.gjt.mm.mysql.Driver"
#MSQLDRIVER  = "com.mysql.jdbc.Driver"



# Following constants are database specific constants.
DBNAME      = "civicrm"
DBUSERNAME  = "civicrm"
DBPASSWORD  = "Mt!Everest"
