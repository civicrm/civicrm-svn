<?php

/*
 +--------------------------------------------------------------------+
 | CiviCRM version 4.1                                                |
 +--------------------------------------------------------------------+
 | Copyright CiviCRM LLC (c) 2004-2011                                |
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
 * This interface defines methods that need to be implemented
 * by every scheduled job (cron task) in CiviCRM.
 *
 * @package CRM
 * @copyright CiviCRM LLC (c) 2004-2011
 * $Id$
 *
 */

class CRM_Core_ScheduledJob
{


    var $name = null;

    var $remarks = array();

    /*
     * Class constructor
     * 
     * @param string $namespace namespace prefix for component's files
     * @access public
     * 
     */
    public function __construct( $params ) {
        foreach( $params as $name => $param ) {
            $this->$name = $param;
        }
        $cmd = split( '_', $this->command );

        if( is_array( $cmd) && $cmd[0] == 'civicrm' ) {
            $this->apiAction = $cmd[3];
            $this->apiEntity = $cmd[2];
        } else {
            // fixme: maybe report error here?
        }
        
        if( !empty( $this->parameters ) ) {
            $lines = split( "\n", $this->parameters );
            $this->apiParams = array();
            foreach( $lines as $line ) {
                $pair = split( "=", $line );
                if( empty($pair[0]) || empty($pair[1]) ) {
                    $this->remarks[] .= 'Malformed parameters!';
                    break;
                }
                $this->apiParams[ trim($pair[0]) ] = trim($pair[1]);

            }
        }
    }                                                          

    public function __destruct( ) {
    }

}