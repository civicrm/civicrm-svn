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
 *
 * @package CRM
 * @copyright CiviCRM LLC (c) 2004-2011
 * $Id$
 *
 */


class CRM_Upgrade_Page_Upgrade extends CRM_Core_Page {
    const QUEUE_NAME = 'CRM_Upgrade';

    function preProcess( ) {
        parent::preProcess( );
    }

    function run( ) {
        // lets get around the time limit issue if possible for upgrades
        if ( ! ini_get( 'safe_mode' ) ) {
            set_time_limit( 0 );
        }
        
        $latestVer  = CRM_Utils_System::version();
        $currentVer = CRM_Core_BAO_Domain::version();
        if ( ! $currentVer ) {
            CRM_Core_Error::fatal( ts('Version information missing in civicrm database.') );
        } else if ( stripos($currentVer, 'upgrade') ) {
            CRM_Core_Error::fatal( ts('Database check failed - the database looks to have been partially upgraded. You may want to reload the database with the backup and try the upgrade process again.') );
        }
        if ( ! $latestVer ) {
            CRM_Core_Error::fatal( ts('Version information missing in civicrm codebase.') );
        }

        // hack to make past ver compatible /w new incremental upgrade process
        $convertVer = array( '2.1'      => '2.1.0',
                             '2.2'      => '2.2.alpha1',
                             '2.2.alph' => '2.2.alpha3',
                             '3.1.0'    => '3.1.1', // since 3.1.1 had domain.version set as 3.1.0
                             );
        if ( isset($convertVer[$currentVer]) ) {
            $currentVer = $convertVer[$currentVer];
        }

        // since version is suppose to be in valid format at this point, especially after conversion ($convertVer),
        // lets do a pattern check -
        if ( !CRM_Utils_System::isVersionFormatValid( $currentVer ) ) {
            CRM_Core_Error::fatal( ts( 'Database is marked with invalid version format. You may want to investigate this before you proceed further.' ) );
        }

        // This could be removed in later rev
        if ( $currentVer == '2.1.6' ) {
            $config = CRM_Core_Config::singleton( );
            // also cleanup the templates_c directory
            $config->cleanupCaches( );
        }
        // end of hack
        
        CRM_Utils_System::setTitle(ts('Upgrade CiviCRM to Version %1', 
                                      array( 1 => $latestVer )));
        
        $preUpgradeMessage = null;

        $template = CRM_Core_Smarty::singleton( );
        $template->assign( 'pageTitle', ts('Upgrade CiviCRM to Version %1', 
                                           array( 1 => $latestVer )));
        $template->assign( 'menuRebuildURL', 
                           CRM_Utils_System::url( 'civicrm/menu/rebuild', 'reset=1' ) );
        $template->assign( 'cancelURL', 
                          CRM_Utils_System::url( 'civicrm/dashboard', 'reset=1' ) );

        if ( version_compare($currentVer, $latestVer) > 0 ) {
            // DB version number is higher than codebase being upgraded to. This is unexpected condition-fatal error.
            $dbToolsLink = CRM_Utils_System::docURL2( "Database Troubleshooting Tools", true );
            $error = ts( 'Your database is marked with an unexpected version number: %1. The automated upgrade to version %2 can not be run - and the %2 codebase may not be compatible with your database state. You will need to determine the correct version corresponding to your current database state. The database tools utility at %3 may be helpful. You may want to revert to the codebase you were using prior to beginning this upgrade until you resolve this problem.',
                           array( 1 => $currentVer, 2 => $latestVer, 3 => $dbToolsLink ) );
            CRM_Core_Error::fatal( $error );
        } else if ( version_compare($currentVer, $latestVer) == 0 ) {
            $postUpgradeMessage = ts( 'Your database has already been upgraded to CiviCRM %1',
                                      array( 1 => $latestVer ) );
            $template->assign( 'upgraded', true );
        } else {
            $upgrade  = new CRM_Upgrade_Form( );
            $postUpgradeMessage   = ts('CiviCRM upgrade was successful.');
            CRM_Upgrade_Incremental_Legacy::setPostUpgradeMessage( $postUpgradeMessage, $currentVer, $latestVer );
            CRM_Upgrade_Incremental_Legacy::setPreUpgradeMessage( $preUpgradeMessage, $currentVer, $latestVer );
            self::setPreUpgradeMessage( $preUpgradeMessage, $currentVer, $latestVer );

            $template->assign( 'currentVersion',  $currentVer);
            $template->assign( 'newVersion',      $latestVer );
            $template->assign( 'upgradeTitle',   ts('Upgrade CiviCRM from v %1 To v %2', 
                                                    array( 1=> $currentVer, 2=> $latestVer ) ) );
            $template->assign( 'upgraded', false );

            // hack to make 4.0.x (D7,J1.6) codebase go through 3.4.x (d6, J1.5) upgrade files, 
            // since schema wise they are same
            if ( CRM_Upgrade_Form::getRevisionPart( $currentVer ) == '4.0' ) {
                $currentVer = str_replace( '4.0.', '3.4.', $currentVer );
            }

            if ( CRM_Utils_Array::value('upgrade', $_POST) ) {
                // Persistent message storage across upgrade steps. TODO: Use structured message store
                // Note: In clustered deployments, this file must be accessible by all web-workers.
                $postUpgradeMessageFile = CRM_Utils_File::tempnam('civicrm-post-upgrade');
                file_put_contents($postUpgradeMessageFile, $postUpgradeMessage);
                
                $queueRunner = new CRM_Queue_Runner(array(
                    'title' => ts('CiviCRM Upgrade Tasks'),
                    'queue' => self::buildQueue($currentVer, $latestVer, $postUpgradeMessageFile),
                    'isMinimal' => TRUE,
                    'pathPrefix' => 'civicrm/upgrade/queue',
                ));
                $queueResult = $queueRunner->runAll(); // FIXME allow using web-runner
                if ($queueResult !== TRUE ) {
                  throw new Exception('Error running queued tasks: ' . print_r($queueResult, TRUE));
                }
                
                $postUpgradeMessage = file_get_contents($postUpgradeMessageFile); // TODO: Use structured message store
                $upgrade->setVersion( $latestVer );
                $template->assign( 'upgraded', true );
                
                // cleanup caches CRM-8739
                $config = CRM_Core_Config::singleton( );
                $config->cleanupCaches( 1 , false );
            }
        }

        $template->assign( 'preUpgradeMessage', $preUpgradeMessage );
        $template->assign( 'message', $postUpgradeMessage );

        $content = $template->fetch( 'CRM/common/success.tpl' );
        echo CRM_Utils_System::theme( 'page', $content, true, $this->_print, false, true );
    }
    
    /**
     * Fill the queue with upgrade tasks
     *
     * @param $currentVer string, the original revision
     * @param $latestVer string, the target (final) revision
     * @param $postUpgradeMessageFile string, path of a modifiable file which lists the post-upgrade messages
     * @return CRM_Queue
     */
    static function buildQueue($currentVer, $latestVer, $postUpgradeMessageFile) {
        $upgrade = new CRM_Upgrade_Form( );
    
        // Ensure that queue can be created
        if (!CRM_Queue_BAO_QueueItem::findCreateTable()) {
            CRM_Core_Error::fatal( ts('Failed to find or create queueing table') );
        }
        $queue = CRM_Queue_Service::singleton()->create(array(
            'name' => self::QUEUE_NAME,
            'type' => 'Sql',
            'reset' => TRUE,
        ));
        
        $revisions = $upgrade->getRevisionSequence();
        foreach ( $revisions as $rev ) {
            // proceed only if $currentVer < $rev
            if ( version_compare($currentVer, $rev) < 0 ) {
                $queue->createItem(new CRM_Queue_Task(
                  array('CRM_Upgrade_Page_Upgrade', 'doIncrementalUpgradeStep'), // callback
                  array($rev, $currentVer, $latestVer, $postUpgradeMessageFile), // arguments
                  "Upgrade DB to $rev"
                ));
            }
        }
        
        return $queue;
    }
    
    /**
     * Perform an incremental version update
     *
     * @param $rev string, the target (intermediate) revision e.g '3.2.alpha1'
     * @param $currentVer string, the original revision
     * @param $latestVer string, the target (final) revision
     * @param $postUpgradeMessageFile string, path of a modifiable file which lists the post-upgrade messages
     */
    static function doIncrementalUpgradeStep(CRM_Queue_TaskContext $ctx, $rev, $currentVer, $latestVer, $postUpgradeMessageFile) {
        $upgrade = new CRM_Upgrade_Form( );
        
        // as soon as we start doing anything we append ".upgrade" to version.
        // this also helps detect any partial upgrade issues
        $upgrade->setVersion( $rev . '.upgrade' );

        $phpFunctionName = 'upgrade_' . str_replace( '.', '_', $rev );

        // follow old upgrade process for all version
        // below 3.2.alpha1 
        if ( version_compare( $rev , '3.2.alpha1' ) < 0 ) {
            if ( is_callable(array('CRM_Upgrade_Incremental_Legacy', $phpFunctionName)) ) {
                call_user_func(array('CRM_Upgrade_Incremental_Legacy', $phpFunctionName), $rev);
            } else {
                $upgrade->processSQL( $rev );
            }
        } else {
            // new upgrade process from version
            // 3.2.alpha1 
            $versionObject = $upgrade->incrementalPhpObject( $rev );
            
            // pre-db check for major release.
            if ( $upgrade->checkVersionRelease( $rev, 'alpha1' ) ) {
                if ( !(is_callable(array($versionObject, 'verifyPreDBstate'))) ) {
                    CRM_Core_Error::fatal("verifyPreDBstate method was not found for $rev");
                }
                
                $error = null;
                if ( !($versionObject->verifyPreDBstate($error)) ) {
                    if ( ! isset( $error ) ) {
                        $error = "post-condition failed for current upgrade for $rev";
                    }
                    CRM_Core_Error::fatal( $error );
                }

                // set post-upgrade-message if any
                if ( is_callable(array($versionObject, 'setPostUpgradeMessage')) ) {
                    $postUpgradeMessage = file_get_contents($postUpgradeMessageFile);
                    $versionObject->setPostUpgradeMessage( $postUpgradeMessage, $currentVer, $latestVer );
                    file_put_contents($postUpgradeMessageFile, $postUpgradeMessage);
                }
            }

            $upgrade->setSchemaStructureTables( $rev );

            if ( is_callable(array($versionObject, $phpFunctionName)) ) {
                $versionObject->$phpFunctionName( $rev );
            } else {
                $upgrade->processSQL( $rev );
            }
        }

        // after an successful intermediate upgrade, set the complete version
        $upgrade->setVersion( $rev );
        
        return TRUE;
    }

    /**
     * Compute any messages which should be displayed before upgrade
     * by calling the 'setPreUpgradeMessage' on each incremental upgrade
     * object.
     *
     * @param $preUpgradeMessage string, alterable
     */
    static function setPreUpgradeMessage ( &$preUpgradeMessage, $currentVer, $latestVer ) {
        $upgrade = new CRM_Upgrade_Form( );
        
        // Scan through all php files and see if any file is interested in setting pre-upgrade-message
        // based on $currentVer, $latestVer. 
        // Please note, at this point upgrade hasn't started executing queries.
        $revisions = $upgrade->getRevisionSequence();
        foreach ( $revisions as $rev ) {
            if ( version_compare($currentVer, $rev) < 0     && 
                 version_compare( $rev , '3.2.alpha1' ) > 0 ) {
                $versionObject = $upgrade->incrementalPhpObject( $rev );
                if ( is_callable(array($versionObject, 'setPreUpgradeMessage')) ) {
                    $versionObject->setPreUpgradeMessage( $preUpgradeMessage, $currentVer, $latestVer );
                }
            }
        }
    }

}
