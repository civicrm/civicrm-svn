<?php

/*
 +--------------------------------------------------------------------+
 | CiviCRM version 2.2                                                |
 +--------------------------------------------------------------------+
 | Copyright CiviCRM LLC (c) 2004-2009                                |
 +--------------------------------------------------------------------+
 | This file is a part of CiviCRM.                                    |
 |                                                                    |
 | CiviCRM is free software; you can copy, modify, and distribute it  |
 | under the terms of the GNU Affero General Public License           |
 | Version 3, 19 November 2007.                                       |
 |                                                                    |
 | CiviCRM is distributed in the hope that it will be useful, but     |
 | WITHOUT ANY WARRANTY; without even the implied warranty of         |
 | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.               |
 | See the GNU Affero General Public License for more details.        |
 |                                                                    |
 | You should have received a copy of the GNU Affero General Public   |
 | License along with this program; if not, contact CiviCRM LLC       |
 | at info[AT]civicrm[DOT]org. If you have questions about the        |
 | GNU Affero General Public License or the licensing of CiviCRM,     |
 | see the CiviCRM license FAQ at http://civicrm.org/licensing        |
 +--------------------------------------------------------------------+
*/

/**
 *
 * @package CRM
 * @copyright CiviCRM LLC (c) 2004-2009
 * $Id$
 *
 */

/**
 * Given one of: ( page, title, text ) parameters, generates
 * an HTML link to documentation.
 *
 * @param array  $params the function params
 * @param object $smarty reference to the smarty object 
 *
 * @return string HTML code of a link to documentation
 * @access public
 */
function smarty_function_docURL( $params, &$smarty ) {

    if ( ! isset( $params['page'] ) || 
         ! isset( $smarty ) ) {
        return;
    }

    $docBaseURL = 'http://wiki.civicrm.org/confluence/display/CRMUPCOMING/';

    if ( ! isset( $params['title'] ) ) {
        $params['title'] = ts( 'Opens documentation in a new window.' );
    }

    if ( ! isset( $params['text'] ) ) {
        $params['text'] = ts( '(learn more...)' );
    }
    
    if ( ! isset( $params['style'] ) ) {
        $params['style'] = '';
    } else {
        $style = "style=\"{$params['style']}\"";
    }

    $link = $docBaseURL . str_replace( ' ', '+', $params['page'] );

    if ( isset( $params['URLonly'] ) && $params['URLonly'] == true ) {
        return $link;
    } else {
        return "<a href=\"{$link}\" $style target=\"_blank\" title=\"{$params['title']}\">{$params['text']}</a>";
    }
    
}