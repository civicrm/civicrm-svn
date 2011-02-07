<?php

/*
 +--------------------------------------------------------------------+
 | CiviCRM version 3.3                                                |
 +--------------------------------------------------------------------+
 | Copyright CiviCRM LLC (c) 2004-2010                                |
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
 * @copyright TTTP
 * $Id$
 *
 */

/**
 */
function smarty_function_crmAPI( $params, &$smarty ) {
    
    civicrm_api_include($params['entity']);
    $fnName = civicrm_api_get_function_name($params['entity'], $params['action']);
    if ( ! function_exists( $fnName ) ) {
        $smarty->trigger_error("Unknown function called: $fnName");
        return;
    }
    // trap all fatal errors
    require_once 'CRM/Utils/REST.php';
    CRM_Core_Error::setCallback( array( 'CRM_Utils_REST', 'fatal' ) );
    unset ($params ['entity']);
    unset ($params ['method']);
    unset ($params ['assign']);
    if (!empty($params['return'])) {
        $return= explode(",", $params['return']);
        foreach ($return as $r) {
            $params ["return.".trim($r)] = 1;
        }
        unset ($params ['return']);
    }
    $result = $fnName( $params );
    CRM_Core_Error::setCallback( );
    if ( $result === false ) {
        $smarty->trigger_error("Unkown error");
        return;
    }
    if (empty($params['var'])) {
        $smarty->trigger_error("assign: missing 'var' parameter");
        return;
    }

    if (!empty($params['json'])) {
      $smarty->assign($params["var"],json_encode($result));
    } else {
      $smarty->assign($params["var"],$result);
    }
}


?>
