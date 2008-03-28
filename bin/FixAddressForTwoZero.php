<?php

/*
 +--------------------------------------------------------------------+
 | CiviCRM version 2.0                                                |
 +--------------------------------------------------------------------+
 | Copyright CiviCRM LLC (c) 2004-2007                                |
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
 * A PHP cron script to format all the addresses in the database. Currently
 * it only does geocoding if the geocode values are not set. At a later
 * stage we will also handle USPS address cleanup and other formatting
 * issues
 *
 */

function run( ) {
    session_start( );                               

    require_once '../civicrm.config.php'; 
    require_once 'CRM/Core/Config.php'; 
    
    $config =& CRM_Core_Config::singleton(); 

    require_once 'Console/Getopt.php';
    $shortOptions = "n:p:";
    $longOptions  = array( 'name=', 'pass=' );

    $getopt  = new Console_Getopt( );
    $args = $getopt->readPHPArgv( );
    array_shift( $args );
    list( $valid, $dontCare ) = $getopt->getopt2( $args, $shortOptions, $longOptions );

    $vars = array(
                  'name'  => 'n',
                  'pass'  => 'p' );

    foreach ( $vars as $var => $short ) {
        $$var = null;
        foreach ( $valid as $v ) {
            if ( $v[0] == $short || $v[0] == "--$var" ) {
                $$var = $v[1];
                break;
            }
        }
        if ( ! $$var ) {
            $$var = CRM_Utils_Array::value( $var, $_REQUEST );
        }
    }

    // this does not return on failure
    // require_once 'CRM/Utils/System.php';
    CRM_Utils_System::authenticateScript( true, $name, $pass );
    
    require_once 'CRM/Upgrade/TwoZero/Form/Step3.php';

    $message = CRM_Upgrade_TwoZero_Form_Step3::cleanupIsPrimary( );
    echo $message;
}


echo "Starting process<p>";

run( );

echo "Done<p>";

?>
