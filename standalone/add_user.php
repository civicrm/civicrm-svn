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

/********************************************
 * This is currently not used; ignore.
 *
 * WSM - 12/27/07
 ********************************************/

require_once 'auth_common.php';
require_once 'CRM/Core/BAO/UFMatch.php';
$ar = CRM_Core_BAO_UFMatch::getContactIDs();
if ( ! empty( $ar[0] ) ) {
  header("Location:login.php");
  exit(0);
}
$openid = $_POST['openid_url'];
$firstname = $_POST['first_name'];
$lastname = $_POST['last_name'];
$email = $_POST['email'];
//require_once 'CRM/Utils/System/Standalone.php';
$user = array( 'openid'    => $openid,
               'firstname' => $firstname,
               'lastname'  => $lastname,
               'email'     => $email );
$session = CRM_Core_Session::singleton();
$session->set('user', $user);
$session->set('new_install', true);

header("Location:try_auth.php?openid_url=$openid");
exit(0);

