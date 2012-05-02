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
        
        $upgrade  = new CRM_Upgrade_Form( );
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
            $postUpgradeMessage   = ts('CiviCRM upgrade was successful.');
            if ( $latestVer == '3.2.alpha1' ) {
                $postUpgradeMessage .= '<br />' . ts("We have reset the COUNTED flag to false for the event participant status 'Pending from incomplete transaction'. This change ensures that people who have a problem during registration can try again.");
            } else if ( $latestVer == '3.2.beta3' && ( version_compare($currentVer, '3.1.alpha1') >= 0 ) ) {
                $subTypes = CRM_Contact_BAO_ContactType::subTypes( );
                                
                if ( is_array( $subTypes ) && !empty( $subTypes ) ) {
                    $config = CRM_Core_Config::singleton( );
                    $subTypeTemplates = array( );
                    
                    if ( isset( $config->customTemplateDir ) ) {
                        foreach( $subTypes as $key => $subTypeName ) {
                            $customContactSubTypeEdit = $config->customTemplateDir . "CRM/Contact/Form/Edit/" . $subTypeName . ".tpl";
                            $customContactSubTypeView = $config->customTemplateDir . "CRM/Contact/Page/View/" . $subTypeName . ".tpl";
                            if ( file_exists( $customContactSubTypeEdit ) || file_exists( $customContactSubTypeView ) ) {
                                $subTypeTemplates[$subTypeName] = $subTypeName;
                            }
                        }
                    } 
                    
                    foreach( $subTypes as $key => $subTypeName ) {
                        $customContactSubTypeEdit = $config->templateDir . "CRM/Contact/Form/Edit/" . $subTypeName . ".tpl";
                        $customContactSubTypeView = $config->templateDir . "CRM/Contact/Page/View/" . $subTypeName . ".tpl";
                            if ( file_exists( $customContactSubTypeEdit ) || file_exists( $customContactSubTypeView ) ) {
                                $subTypeTemplates[$subTypeName] = $subTypeName;
                            }
                    }
                                        
                    if ( !empty( $subTypeTemplates ) ) {
                        $subTypeTemplates = implode( ',', $subTypeTemplates );
                        $postUpgradeMessage .= '<br />' . ts('You are using custom template for contact subtypes: %1.', array(1 => $subTypeTemplates)) . '<br />' . ts('You need to move these subtype templates to the SubType directory in %1 and %2 respectively.', array(1 => 'CRM/Contact/Form/Edit', 2 => 'CRM/Contact/Page/View'));
                    }
                }
            } else if ( $latestVer == '3.2.beta4' ) {
                $statuses = array( 'New', 'Current', 'Grace', 'Expired', 'Pending', 'Cancelled', 'Deceased' );
                $sql = "
SELECT  count( id ) as statusCount 
  FROM  civicrm_membership_status 
 WHERE  name IN ( '" . implode( "' , '", $statuses )  .  "' ) ";
                $count = CRM_Core_DAO::singleValueQuery( $sql );
                if ( $count < count( $statuses ) ) {
                    $postUpgradeMessage .= '<br />' . ts( "One or more Membership Status Rules was disabled during the upgrade because it did not match a recognized status name. if custom membership status rules were added to this site - review the disabled statuses and re-enable any that are still needed (Administer > CiviMember > Membership Status Rules)." );
                }
            } else if ( $latestVer == '3.4.alpha1' ) {
                $renamedBinScripts = array( 'ParticipantProcessor.php',
                                            'RespondentProcessor.php',
                                            'UpdateGreeting.php',
                                            'UpdateMembershipRecord.php',
                                            'UpdatePledgeRecord.php ' );
                $postUpgradeMessage .= '<br />' . ts( 'The following files have been renamed to have a ".php" extension instead of a ".php.txt" extension' ) . ': ' . implode( ', ', $renamedBinScripts );
            }

            // set pre-upgrade warnings if any -
            self::setPreUpgradeMessage( $preUpgradeMessage, $currentVer, $latestVer );
            
            //turning some tables to monolingual during 3.4.beta3, CRM-7869
            $upgradeTo   = str_replace( '4.0.', '3.4.', $latestVer  );
            $upgradeFrom = str_replace( '4.0.', '3.4.', $currentVer );
            
            // check for changed message templates
            self::checkMessageTemplate( $template, $preUpgradeMessage, $upgradeTo, $upgradeFrom );

            if ( $upgrade->multilingual && 
                 version_compare( $upgradeFrom, '3.4.beta3'  ) == -1 &&
                 version_compare( $upgradeTo,   '3.4.beta3'  ) >=  0  ) {
                $config = CRM_Core_Config::singleton( );
                $preUpgradeMessage .= '<br />' . ts( "As per <a href='%1'>the related blog post</a>, we are making contact names, addresses and mailings monolingual; the values entered for the default locale (%2) will be preserved and values for other locales removed.", array( 1 => 'http://civicrm.org/blogs/shot/multilingual-civicrm-3440-making-some-fields-monolingual', 2 => $config->lcMessages ) );
            }

            if ( version_compare( $currentVer, '3.4.6' ) == -1 &&
                 version_compare( $latestVer,  '3.4.6' ) >= 0 ) {
                $googleProcessorExists = CRM_Core_DAO::singleValueQuery( "SELECT id FROM civicrm_payment_processor WHERE payment_processor_type = 'Google_Checkout' AND is_active = 1 LIMIT 1;" );

                if ( $googleProcessorExists ) {
                    $preUpgradeMessage .= '<br />' . ts( 'To continue using Google Checkout Payment Processor with latest version of CiviCRM, requires updating merchant account settings. Please refer "Set API callback URL and other settings" section of <a href="%1" target="_blank"><strong>Google Checkout Configuration</strong></a> doc.', array( 1 => 'http://wiki.civicrm.org/confluence/x/zAJTAg' ) );
                }
            }

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
                $postUpgradeMessageFile = CRM_Utils_File::tempnam('civicrm-post-upgrade');
                file_put_contents($postUpgradeMessageFile, $postUpgradeMessage);
                
                $revisions = $upgrade->getRevisionSequence();
                foreach ( $revisions as $rev ) {
                    // proceed only if $currentVer < $rev
                    if ( version_compare($currentVer, $rev) < 0 ) {
                        $this->doIncrementalUpgradeStep($rev, $currentVer, $latestVer, $postUpgradeMessageFile);
                    }
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
     * Perform an incremental version update
     *
     * @param $rev string, the target (intermediate) revision e.g '3.2.alpha1'
     * @param $currentVer string, the original revision
     * @param $latestVer string, the target (final) revision
     * @param $postUpgradeMessageFile string, path a file which lists the post-upgrade messages
     */
    static function doIncrementalUpgradeStep($rev, $currentVer, $latestVer, $postUpgradeMessageFile) {
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
    }

    function setPreUpgradeMessage ( &$preUpgradeMessage, $currentVer, $latestVer ) 
    {
        if ( ( version_compare($currentVer, '3.3.alpha1') <  0  &&
               version_compare($latestVer,  '3.3.alpha1') >= 0 ) ||
             ( version_compare($currentVer, '3.4.alpha1') <  0  &&
               version_compare($latestVer,  '3.4.alpha1') >= 0 ) ) {
            $query = "
SELECT  id 
  FROM  civicrm_mailing_job 
 WHERE  status NOT IN ( 'Complete', 'Canceled' ) AND is_test = 0 LIMIT 1";
            $mjId  = CRM_Core_DAO::singleValueQuery( $query );
            if ( $mjId ) {
                $preUpgradeMessage = ts("There are one or more Scheduled or In Progress mailings in your install. Scheduled mailings will not be sent and In Progress mailings will not finish if you continue with the upgrade. We strongly recommend that all Scheduled and In Progress mailings be completed or cancelled and then upgrade your CiviCRM install.");
            }
        }
    }
    function checkMessageTemplate( &$template, &$message, $latestVer, $currentVer ) 
    {
        if ( version_compare($currentVer, '3.1.alpha1') < 0 ) {
            return;
        }
        
        $sql =
            "SELECT orig.workflow_id as workflow_id,
             orig.msg_title as title
            FROM civicrm_msg_template diverted JOIN civicrm_msg_template orig ON (
                diverted.workflow_id = orig.workflow_id AND
                orig.is_reserved = 1                    AND (
                    diverted.msg_subject != orig.msg_subject OR
                    diverted.msg_text    != orig.msg_text    OR
                    diverted.msg_html    != orig.msg_html
                )
            )";
        
        $dao =& CRM_Core_DAO::executeQuery($sql);
        while ($dao->fetch()) {
            $workflows[$dao->workflow_id] = $dao->title;
        }

        if( empty( $workflows ) ) {
            return;
        }

        $html = null;
        $pathName = dirname( dirname( __FILE__ ) );
        $flag = false;
        foreach( $workflows as $workflow => $title) {
            $name = CRM_Core_DAO::getFieldValue( 'CRM_Core_DAO_OptionValue',
                                                 $workflow,
                                                 'name',
                                                 'id' ) ;  
            
            // check if file exists locally
            $textFileName = implode( DIRECTORY_SEPARATOR,
                                 array($pathName,
                                       "{$latestVer}.msg_template",
                                       'message_templates',
                                       "{$name}_text.tpl" ) );

            $htmlFileName = implode( DIRECTORY_SEPARATOR,
                                     array($pathName,
                                           "{$latestVer}.msg_template",
                                           'message_templates',
                                           "{$name}_html.tpl" ) );
            
            if ( file_exists( $textFileName ) || 
                 file_exists( $htmlFileName ) ) {
                $flag = true;
                $html .= "<li>{$title}</li>";
            }
        }

        if ( $flag == true ) {
            $html = "<ul>". $html."<ul>";
           
            $message .= '<br />' . ts("The default copies of the message templates listed below will be updated to handle new features. Your installation has customized versions of these message templates, and you will need to apply the updates manually after running this upgrade. <a href='%1' style='color:white; text-decoration:underline; font-weight:bold;' target='_blank'>Click here</a> for detailed instructions. %2", array( 1 => 'http://wiki.civicrm.org/confluence/display/CRMDOC40/Message+Templates#MessageTemplates-UpgradesandCustomizedSystemWorkflowTemplates', 2 => $html));
           
        }
    }

}
