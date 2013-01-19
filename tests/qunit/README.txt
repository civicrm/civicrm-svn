==== QUnit Test Suite for CiviCRM ====

QUnit is a JavaScript-based unit-testing framework. It is ideally suited to
testing pure-JavaScript modules -- for example, many jQuery and Backbone
plugins use QUnit. For more details about, see:

  http://qunitjs.com/
  http://qunitjs.com/cookbook/

CiviCRM is a large application and may include some pure-Javascript
components -- one should use QUnit to test these components.  Note: CiviCRM
also includes many non-Javascript components (MySQL, PHP, etc).  For
integration-testing that encompasses all of CiviCRM's different
technologies, see the CiviCRM WebTest suite. QUnit is *only* appropriate
unit-testing of pure JS.

==== QUICKSTART ====

To create a new test-suite:

1. Determine a name for a new test-suite, such as "my-stuff".

2. Copy "civicrm/tests/qunit/examples" to "civicrm/tests/qunit/my-stuff"

3. Edit the "civicrm/tests/qunit/my-stuff/index.php" to load your JS file
   (my-stuff.js) as well as any dependencies (jQuery, Backbone, etc). 
   Search for the text "FIXME".

4. Edit the "civcrm/tests/qunit/my-stuff/tests.js"

==== CONVENTIONS ====

The following is a quick draft of coding conventions. If there's a problem
with it, we can change it -- but please communicate any problems/issues
(e.g.  via IRC, mailing-list, or forum).

 * CiviCRM includes multiple test-suites. One test-suite should be created for
   each logically distinct JavaScript component.

   Rationale: CiviCRM is a large application with a diverse mix of
   components written by diverse authors.  Each component may present
   different requirements for testing -- e.g. HTML fixtures, CSS fixtures,
   third-party JS dependencies, etc.

   Note: As a rule-of-thumb, if you add a new js file to CiviCRM
   ("civicrm/js/foo.js"), and if that file is useful on its own, then you
   might create a new test-suite for it ("civicrm/tests/qunit/foo").

 * Each QUnit test-suite for CiviCRM lives in a subdirectory of
   "tests/qunit/"; specifically, each test-suite lives within a file called
   "index.php".
   
   Rationale: Following a predictable naming convention will help us automate
   testing across all suites, and it will make the code more recognizable
   to other developers.

 * Each "index.php" file requires "../civicrm-qunit.php". References
   to any files outside the test-suite (qunit.js, jquery.js, etc) should be
   based on variables provided by civicrm-qunit.php.

   Rationale: One must specify the paths of various JS dependencies because
   CiviCRM doesn't currently include an Javascript autoloader.  Also,
   hard-coding the paths would make it harder to reorganize the code-base in
   the future.

==== TODO ====

Each test-suite must be run individually. This is OK for now. In the future,
we might further automate -- e.g. we could make a WebTest which iterates
through tests/qunit/*/index.php and checks the outcome for each.
