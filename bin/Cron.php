<?php

/*
 +--------------------------------------------------------------------+
 | CiviCRM version 3.4                                               |
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

/*
 */

class CRM_Cron {
    
    function __construct() 
    {
        // you can run this program either from an apache command, or from the cli
        if ( php_sapi_name( ) == "cli" ) {
            require_once ("cli.php");
            $cli = new civicrm_cli ( );
            //if it doesn't die, it's authenticated
        } else { 
            //from the webserver
            $this->initialize( );
          
            $config = CRM_Core_Config::singleton();
           
            // this does not return on failure
            CRM_Utils_System::authenticateScript( true );
            
            //log the execution time of script
            CRM_Core_Error::debug_log_message( 'Cron.php' );
            
            // load bootstrap to call hooks
            require_once 'CRM/Utils/System.php';
            CRM_Utils_System::loadBootStrap(  );
        }
    }

    function initialize( ) {
        require_once '../civicrm.config.php';
        require_once 'CRM/Core/Config.php';

        $config = CRM_Core_Config::singleton();
    }

    public function run( )
    {
        // FIXME: Need to generalize all hard coded options
        $renewalMsgId = 1;
        $mappingID    = 1;
        $actionScheduleID  = 1;

        require_once 'CRM/Core/BAO/Domain.php';
        $domainValues     = CRM_Core_BAO_Domain::getNameAndEmail( );
        $domainFromEmail  = "$domainValues[0] <$domainValues[1]>";
        
        //use domain email address as a default From email.
        $fromEmailAddress = $domainFromEmail;
        
        require_once 'CRM/Core/BAO/ScheduleReminders.php';
        $mappings = CRM_Core_BAO_ScheduleReminders::getMapping( );

        while ( $mappings as $mappingID => $mapping ) {
            $contacts  = CRM_Core_BAO_ActionMapping::getRecipientContacts( $mappingID );

            // $scheduled = CRM_Core_BAO_ActionSchedule::isScheduled( $mappingID );
            $scheduled = true;

            if ( $scheduled ) {
                foreach ( $contacts as $contactID ) {
                    $toEmail  = CRM_Contact_BAO_Contact::getPrimaryEmail( $contactID );
                    if ( $toEmail ) {
                        $result = CRM_Core_BAO_MessageTemplates::sendReminder( $contactID,
                                                                               $toEmail,
                                                                               $renewalMsgId,
                                                                               $fromEmailAddress );
                        if ( ! $result ||
                             is_a( $result, 'PEAR_Error' ) ) {
                            // we could not send an email, for now we ignore, CRM-3406
                        }
                    }
                }
                
                if ( !empty($contacts) ) {
                    $actionLogParams = array( 'entity_id'          => $mappingID, // set it to mappingID
                                              'entity_table'       => 'civicrm_action_mapping',
                                              'action_schedule_id' => $actionScheduleID, // action_schedule Id
                                              'action_date_time'   => date('YmdHis'),
                                              );
                    // FIXME: repetition_number should be updated by create function itself.
                    $activity = CRM_Core_BAO_ActionLog::create( $actionLogParams );
                }
            }
        }
    }
}

$cron = new CRM_Cron( );
$cron->run( );

