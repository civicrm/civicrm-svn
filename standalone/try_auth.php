<?php

/*
 +--------------------------------------------------------------------+
 | CiviCRM version 3.2                                                |
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

require_once "auth_common.php";

function getOpenIDURL() {
    // Render a default page if we got a submission without an openid
    // value.
    if (empty($_REQUEST['openid_identifier'])) {
        displayError("Expected an OpenID URL.");
    }
    
    return $_REQUEST['openid_identifier'];
}

function run() {
    $openid   = getOpenIDURL();
    $consumer = getConsumer();

    // Begin the OpenID authentication process.
    $auth_request = $consumer->begin($openid);

    // No auth request means we can't begin OpenID.
    if (!$auth_request) {
        // check for new install, if no, go to index, else goto new-install page
        require_once 'CRM/Core/BAO/UFMatch.php';
        $contactIds = CRM_Core_BAO_UFMatch::getContactIDs();

        if ( count($contactIds) > 0 ) {
            displayError("Authentication error; not a valid OpenID.");
        } else {
            $session = CRM_Core_Session::singleton( );
            $session->set( 'new_install', true );
            include('new_install.html');
            exit(1);
        }
    }

    $sreg_request = Auth_OpenID_SRegRequest::build(
                                                   // Required
                                                   array('nickname'),
                                                   // Optional
                                                   array('fullname', 'email'));

    if ($sreg_request) {
        $auth_request->addExtension($sreg_request);
    }

    $policy_uris = null;
    if ( isset($_REQUEST['policies']) ) {
        $policy_uris = $_REQUEST['policies'];
    }

    $pape_request = new Auth_OpenID_PAPE_Request($policy_uris);
    if ($pape_request) {
        $auth_request->addExtension($pape_request);
    }

    $redirect_url = $auth_request->redirectURL(getTrustRoot(),
                                               getReturnTo());
    
    // If the redirect URL can't be built, display an error
    // message.
    if (Auth_OpenID::isFailure($redirect_url)) {
        displayError("Could not redirect to server: " . $redirect_url->message);
    } else {
        // Send redirect.
        header("Location: ". $redirect_url);
        exit(2);
    }
}

run();
