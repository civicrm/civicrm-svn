<?php
/*
 +--------------------------------------------------------------------+
 | CiviCRM version 1.7                                                |
 +--------------------------------------------------------------------+
 | copyright CiviCRM LLC (c) 2004-2007                                |
 +--------------------------------------------------------------------+
 | This file is a part of CiviCRM.                                    |
 |                                                                    |
 | CiviCRM is free software; you can copy, modify, and distribute it  |
 | under the terms of the Affero General Public License Version 1,    |
 | March 2002.                                                        |
 |                                                                    |
 | CiviCRM is distributed in the hope that it will be useful, but     |
 | WITHOUT ANY WARRANTY; without even the implied warranty of         |
 | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.               |
 | See the Affero General Public License for more details.            |
 |                                                                    |
 | You should have received a copy of the Affero General Public       |
 | License along with this program; if not, contact the Social Source |
 | Foundation at info[AT]civicrm[DOT]org.  If you have questions      |
 | about the Affero General Public License or the licensing  of       |
 | of CiviCRM, see the Social Source Foundation CiviCRM license FAQ   |
 | http://civicrm.org/licensing/                                      |
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

function processQueue( ) {
    require_once 'CRM/Mailing/BAO/Job.php';
    CRM_Mailing_BAO_Job::runJobs();
}

function run( ) {
    session_start( );                               
                                            
    require_once '../civicrm.config.php'; 
    require_once 'CRM/Core/Config.php'; 
    
    $config =& CRM_Core_Config::singleton(); 
    
    $config->userFramework          = 'Soap'; 
    $config->userFrameworkClass     = 'CRM_Utils_System_Soap'; 
    $config->userHookClass          = 'CRM_Utils_Hook_Soap';
    

    // how to create a universal lock file name?
    // generally this should be in /var/lock but this is unwritable on Openwall
    // consider the semaphore mechanism described by christian.wessels at web.de
    // 07-Apr-2006 09:41 on http://us3.php.net/flock
    
    $lockName  = $config->uploadDir . '.civicrm_cronjob.lck';
    $staleTime = 30*60;           // lock goes stale after 30 minutes
    
    $fp = fopen($lockName, "w+");
    if (!flock($fp, LOCK_EX | LOCK_NB)) {  // if lock is already taken...
        if ((time() - filemtime($lockName)) > $staleTime) {
            echo "civimail.cronjob/php: $lockName is stale\n";
        }
        exit(0);                     // ...exit immediately
    }
    fwrite($fp, '0');              // sets modification time

    // we have an exclusive lock - run the mail queue
    processQueue( );

    // release the lock and clean up
    flock($fp, LOCK_UN);
    fclose($fp);
}

run( );

?>
