<?php

/*
 +--------------------------------------------------------------------+
 | CiviCRM version 3.1                                                |
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
 * A PHP cron script to run the outstanding and scheduled CiviMail jobs
 * initiated by Owen Barton from a mailing sent by Lobo to crm-mail
 *
 * The structure of the file is set to mimiic soap.php which is a stand-alone
 * script and hence does not have any UF issues. You should be able to run
 * this script using a web url or from the command line
 */

function run( ) {
    session_start( );                               
                                            
    require_once '../civicrm.config.php'; 
    require_once 'CRM/Core/Config.php'; 
    
    $config =& CRM_Core_Config::singleton(); 

    // this does not return on failure
    CRM_Utils_System::authenticateScript( true );

    // get the current and guessed values
    require_once 'CRM/Core/BAO/Setting.php';
    list( $oldURL, $oldDir, $oldSiteName ) = CRM_Core_BAO_Setting::getConfigSettings( );
    list( $newURL, $newDir, $newSiteName ) = CRM_Core_BAO_Setting::getBestGuessSettings( );
    
    $oldVal_1 = $newVal_1 = null;
    $oldVal_2 = $newVal_2 = null;
    $oldVal_3 = $newVal_3 = null;

    require_once 'CRM/Utils/Request.php';

    // retrieve these values from the argument list 
    $variables = array( 'oldURL'  , 'newURL'  ,
                        'oldDir'  , 'newDir'  ,
                        'oldVal_1', 'newVal_1',
                        'oldVal_2', 'newVal_2',
                        'oldVal_3', 'newVal_3' );
    foreach ( $variables as $var ) {
        $$var = CRM_Utils_Request::retrieve( $var,
                                             'String',
                                             CRM_Core_DAO::$_nullArray,
                                             false,
                                             $$var,
                                             'REQUEST' );
    }

    $from = $to = array( );

    if ( $oldURL && $newURL ) {
        $from[] = $oldURL;
        $to[]   = $newURL;
    }
    if ( $oldDir && $newDir ) {
        $from[] = $oldDir;
        $to[]   = $newDir;
    }
    if ( $oldSiteName && $newSiteName ) {
        $from[] = $oldSiteName;
        $to[]   = $newSiteName;
    }

    for ( $i = 1 ; $i<=3; $i++ ) {
        $oldVar = "oldVal_$i";
        $newVar = "newVal_$i";
        if ( $$oldVar && $$newVar ) {
            $from[]  = $$oldVar;
            $to[]    = $$newVar;
        }
    }

    $sql = "
SELECT config_backend
FROM   civicrm_domain
WHERE  id = %1
";
    $params = array( 1 => array( CRM_Core_Config::domainID( ), 'Integer' ) );
    $configBackend = CRM_Core_DAO::singleValueQuery( $sql, $params );
    if ( ! $configBackend ) {
        CRM_Core_Error::fatal( );
    }
    $configBackend = unserialize( $configBackend );

    $configBackend = str_replace( $from,
                                  $to  ,
                                  $configBackend );

    $configBackend = serialize( $configBackend );
    $sql = "
UPDATE civicrm_domain
SET    config_backend = %2
WHERE  id = %1
";
    $params[2] = array( $configBackend, 'String' );
    CRM_Core_DAO::executeQuery( $sql, $params );
    
    // clear the template_c and upload directory also
    $config->cleanup( 3, true );
    
    // clear all caches
    CRM_Core_Config::clearDBCache( );

    $resetSessionTable = CRM_Utils_Request::retrieve( 'resetSessionTable',
                                                      'Boolean',
                                                      CRM_Core_DAO::$_nullArray,
                                                      false,
                                                      false,
                                                      'REQUEST' );
    if ( $config->userFramework == 'Drupal' &&
         $resetSessionTable ) {
        db_query("DELETE FROM {sessions} WHERE 1");
    } else {
        $session =& CRM_Core_Session::singleton( );
        $session->reset( 2 );
    }

    echo "Site Move Completed. Please visit <a href=\"$newURL\">your moved site</a> and test the move<p>";

    CRM_Core_Error::debug( $from );
    CRM_Core_Error::debug( $to );
}

run( );


