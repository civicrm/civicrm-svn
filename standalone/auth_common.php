<?php

/*
 +--------------------------------------------------------------------+
 | CiviCRM version 3.3                                                |
 +--------------------------------------------------------------------+
 | Copyright U.S. PIRG Education Fund (c) 2007                        |
 | Licensed to CiviCRM under the Academic Free License version 3.0.   |
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
 | License and the CiviCRM Licensing Exception along                  |
 | with this program; if not, contact CiviCRM LLC                     |
 | at info[AT]civicrm[DOT]org. If you have questions about the        |
 | GNU Affero General Public License or the licensing of CiviCRM,     |
 | see the CiviCRM license FAQ at http://civicrm.org/licensing        |
 +--------------------------------------------------------------------+
*/

/**
 *
 * @package CRM
 * @copyright U.S. PIRG Education Fund 2007
 * $Id$
 *
 */

// often we're here before we get a full include path,
// so we should make sure we can find this file
set_include_path(get_include_path() . PATH_SEPARATOR . "..");
require_once 'CRM/Utils/System.php';

function displayError($message) {
    echo "<link rel=\"stylesheet\" type=\"text/css\" href=\"../packages/jquery/css/openid-selector.css\" />"; 
    print "<div class=\"error\">$message\n<br/>";
    print "<a href=\"index.php\">Home Page</a></div>\n";
    exit(0);
}

function doIncludes() {
    
    require_once 'bootstrap_common.php';

    /**
     * Require the OpenID consumer code.
     */
    require_once "Auth/OpenID/Consumer.php";

    /**
     * Require the "MySQL store" module, which we'll need to store OpenID
     * information.
     */
    require_once "Auth/OpenID/MySQLStore.php";

    /**
     * Require the Simple Registration extension API.
     */
    require_once "Auth/OpenID/SReg.php";

    /**
     * Require the PAPE extension module.
     */
    require_once "Auth/OpenID/PAPE.php";
}

doIncludes();

global $pape_policy_uris;
$pape_policy_uris = array(
                          PAPE_AUTH_MULTI_FACTOR_PHYSICAL,
                          PAPE_AUTH_MULTI_FACTOR,
                          PAPE_AUTH_PHISHING_RESISTANT
                          );

function &getStore() {
    /**
     * Setup the database store for the OpenID sessions.
     */
    $dao = new CRM_Core_DAO();
    if (defined('CIVICRM_DSN')) {
        $dsn = CIVICRM_DSN;
    }
    $dao->init($dsn);

    $connection         =& $dao->getDatabaseConnection();
    $settings_table     = "civicrm_openid_settings";
    $associations_table = "civicrm_openid_associations";
    $nonces_table       = "civicrm_openid_nonces";
    
    $store = new Auth_OpenID_MySQLStore($connection,
                                         $associations_table,$nonces_table);
    return $store;
}

function &getConsumer() {
    /**
     * Create a consumer object using the store object created earlier.
     */
    $store    = getStore();
    $consumer = new Auth_OpenID_Consumer($store);
    return $consumer;
}

function getScheme() {
    $req_headers = CRM_Utils_System::getRequestHeaders();
    $scheme = 'http';
    if ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') ||
        (isset($req_headers['X_FORWARDED_PROTO']) &&
            strtolower($req_headers['X_FORWARDED_PROTO']) == 'https')) {
        $scheme .= 's';
    }
    return $scheme;
}

function getReturnTo() {
    $urlPort = getUrlPort();
    
    return sprintf("%s://%s%s%s/finish_auth.php",
                   getScheme(), $_SERVER['SERVER_NAME'],
                   $urlPort,
                   dirname($_SERVER['PHP_SELF']));
}

function getTrustRoot() {
    $urlPort = getUrlPort();
    
    return sprintf("%s://%s%s%s/",
                   getScheme(), $_SERVER['SERVER_NAME'],
                   $urlPort,
                   dirname($_SERVER['PHP_SELF']));
}

function getUrlPort() {
    $req_headers = CRM_Utils_System::getRequestHeaders();
    $scheme = getScheme();
    if ( array_key_exists('X_FORWARDED_PROTO', $req_headers ) &&
         $req_headers['X_FORWARDED_PROTO'] == 'https' ) {
        $server_port = 443;
    } else {
	    $matches = array();
	    preg_match('/:(\d{1,5})$/',$_SERVER['HTTP_HOST'],$matches);
	    if (array_key_exists(1, $matches)) {
	        $server_port = $matches[1];
	    } else {
	        $server_port = $_SERVER['SERVER_PORT'];
	    }
    }
    
    if ($scheme == 'http' && $server_port == 80) {
        $urlPort = '';
    } elseif ($scheme == 'https' && $server_port == 443) {
        $urlPort = '';
    } else {
        $urlPort = ":$server_port";
    }
    
    return $urlPort;
}
