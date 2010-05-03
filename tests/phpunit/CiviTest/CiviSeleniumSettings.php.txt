<?php

class CiviSeleniumSettings {

	var $sandboxURL = 'http://tests.dev.civicrm.org';

	var $sandboxPATH = '/drupal/';
	
	var $username = 'demo';

	var $password = 'demo';


	function __construct() {
		$this->fullSandboxPath = $this->sandboxURL . $this->sandboxPATH;
	}

}
?>