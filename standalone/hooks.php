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

/**
 * This is where hook functions go for standalone installations
 *
 * If you're familiar with the hook design pattern, or the Drupal concept,
 * this should all be pretty familiar to you.
 * 
 * See http://wiki.civicrm.org/confluence/display/CRMDOC/CiviCRM+hook+specification
 * for more information on hooks and what they do.
 *
 * Hook function names should all start with "standalone_". So in the
 * documentation where you see "hook_", replace that with "standalone_".
 * See the example below for more details.
 */
 
/* Example hook function definition
function standalone_civicrm_post( $op, $objectName, $objectId, &$objectRef ) {
    if ( $op == 'create' && $objectName = 'Individual' ) {
        // we're creating a new individual contact, do something clever here
    }
}
*/
 
// Define your hook functions below here

