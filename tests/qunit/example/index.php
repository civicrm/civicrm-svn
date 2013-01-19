<?php
$urls = require_once '../civicrm-qunit.php';
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<title><!-- FIXME -->QUnit Example</title>
<link rel="stylesheet" href="<?php echo $urls['qunit_css'] ?>">
</head>
<body>
  <div id="qunit"></div>
  <div id="qunit-fixture"></div>
  <!-- Load the qUnit runtime -->
  <script src="<?php echo $urls['qunit_js'] ?>"></script>
  <!-- Load the JS logic -->
  <!-- FIXME --><script src="<?php echo $urls['js_dir'] ?>/Contact.js"></script>
  <!-- Load the JS test-case -->
  <script src="tests.js"></script>
</body>
</html>
