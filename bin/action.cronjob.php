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

class CRM_Cron_Action {
    
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
            CRM_Core_Error::debug_log_message( 'action.cronjob.php' );
            
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
        require_once 'CRM/Core/BAO/ActionLog.php';
        require_once 'CRM/Core/BAO/ScheduleReminders.php';
        $mappings = CRM_Core_BAO_ScheduleReminders::getMapping( );

        foreach ( $mappings as $mappingID => $mapping ) {
            $this->buildRecipientContacts( $mappingID );

            $this->sendMailings( $mappingID );
        }
    }

    public function sendMailings( $mappingID ) {
        require_once 'CRM/Contact/BAO/Contact.php';
        require_once 'CRM/Core/BAO/Domain.php';
        $domainValues     = CRM_Core_BAO_Domain::getNameAndEmail( );
        $fromEmailAddress = "$domainValues[0] <$domainValues[1]>";
        
        require_once 'CRM/Core/DAO/ActionMapping.php';
        $mapping = new CRM_Core_DAO_ActionMapping( );
        $mapping->id = $mappingID;
        $mapping->find( true );

        $actionSchedule = new CRM_Core_DAO_ActionSchedule( );
        $actionSchedule->id = $mappingID;

        if ( $actionSchedule->find( true ) ) {
            $query = "
SELECT * 
FROM  civicrm_action_log 
WHERE action_schedule_id = %1 AND action_date_time IS NULL";
            $dao   = CRM_Core_DAO::executeQuery( $query, array( 1 => array( $actionSchedule->id, 'Integer' ) ) );

            // QUESTION: in cases when same contact has multiple activites for example, should we 
            // send multiple mails ? if not should the log be maintained for every activity ?
            while ( $dao->fetch() ) {
                $toEmail = CRM_Contact_BAO_Contact::getPrimaryEmail( $dao->contact_id );
                if ( $toEmail ) {
                    $result = CRM_Core_BAO_ScheduleReminders::sendReminder( $dao->contact_id,
                                                                            $toEmail,
                                                                            $mappingID,
                                                                            $fromEmailAddress );
                    if ( ! $result || is_a( $result, 'PEAR_Error' ) ) {
                        // we could not send an email, for now we ignore, CRM-3406
                    }
                    
                }
                //FIXME: set is_error and message for errors or no email.
                $query = "UPDATE civicrm_action_log SET action_date_time = NOW() WHERE id = %1";
                CRM_Core_DAO::executeQuery( $query, array( 1 => array( $dao->id, 'Integer' ) ) );
            }
            $dao->free();
        }
    }
    
    public function buildRecipientContacts( $mappingID ) {
        $actionSchedule = new CRM_Core_DAO_ActionSchedule( );
        $actionSchedule->id = $mappingID;

        if ( $actionSchedule->find( true ) ) {
            require_once 'CRM/Core/DAO/ActionMapping.php';
            $mapping = new CRM_Core_DAO_ActionMapping( );
            $mapping->id = $mappingID;
            $mapping->find( true );

            $select = $join = $where = array( );

            $value  = explode( CRM_Core_DAO::VALUE_SEPARATOR, $actionSchedule->entity_value  );
            $value  = implode( ',', $value );

            $status = explode( CRM_Core_DAO::VALUE_SEPARATOR, $actionSchedule->entity_status );
            $status = implode( ',', $status );
        
            require_once 'CRM/Core/OptionGroup.php';
            $recipientOptions = CRM_Core_OptionGroup::values( $mapping->entity_recipient );

            $from = "{$mapping->entity} e";

            if ( $mapping->entity == 'civicrm_activity' ) {
                switch ( $recipientOptions[$actionSchedule->recipient] ) {
                case 'Activity Assignees':
                    $contactField = "r.assignee_contact_id";
                    $join[] = "INNER JOIN civicrm_activity_assignment r ON  r.activity_id = e.id";
                    break;
                case 'Activity Source':
                    $contactField = "e.source_contact_id";
                    break;
                case 'Activity Targets':
                    $contactField = "r.target_contact_id";
                    $join[] = "INNER JOIN civicrm_activity_target r ON  r.activity_id = e.id";
                    break;
                default:
                    break;
                }
                $select[] = "{$contactField} as contact_id";
                $select[] = "e.id as entity_id";
                $select[] = "'{$mapping->entity}' as entity_table";
                $select[] = "{$actionSchedule->id} as action_schedule_id";
                $reminderJoinClause   = "civicrm_action_log reminder ON reminder.contact_id = {$contactField} AND 
reminder.entity_id    = e.id AND 
reminder.entity_table = 'civicrm_activity' AND
reminder.action_schedule_id = %1";

                // build where clause
                if ( !empty($value) ) {
                    $where[]  = "e.activity_type_id IN ({$value})";
                }
                if ( !empty($status) ) {
                    $where[]  = "e.status_id IN ({$status})";
                }

                $startEvent = ( $actionSchedule->start_action_condition == 'before' ? "DATE_SUB" : "DATE_ADD" ) . 
                    "(e.activity_date_time, INTERVAL {$actionSchedule->start_action_offset} {$actionSchedule->start_action_unit})";
            }

            // ( now >= date_built_from_start_time )
            $startEventClause = "reminder.id IS NULL AND NOW() >= {$startEvent}";

            // build final query
            $selectClause = "SELECT " . implode( ', ', $select );
            $fromClause   = "FROM $from";
            $joinClause   = !empty( $join ) ? implode( ' ', $join ) : '';
            $whereClause  = "WHERE " . implode( ' AND ', $where );
            
            $query = "
INSERT INTO civicrm_action_log (contact_id, entity_id, entity_table, action_schedule_id)
{$selectClause} 
{$fromClause} 
{$joinClause}
LEFT JOIN {$reminderJoinClause}
{$whereClause} AND {$startEventClause}";
            CRM_Core_DAO::executeQuery( $query, array( 1 => array( $actionSchedule->id, 'Integer' ) ) );

            // if repeat is turned ON:
            if ( $actionSchedule->is_repeat ) {
                if ( $mapping->entity == 'civicrm_activity' ) {
                    $repeatEvent = ( $actionSchedule->end_action == 'before' ? "DATE_SUB" : "DATE_ADD" ) . 
                        "(e.activity_date_time, INTERVAL {$actionSchedule->end_frequency_interval} {$actionSchedule->end_frequency_unit})";
                }

                if ( $actionSchedule->repetition_frequency_unit == 'day' ) {
                    $hrs = 24 * $actionSchedule->repetition_frequency_interval;
                } else if ( $actionSchedule->repetition_frequency_unit == 'week' ) {
                    $hrs = 24 * $actionSchedule->repetition_frequency_interval * 7;
                } else {
                    $hrs = $actionSchedule->repetition_frequency_interval;
                }
                
                // (now <= repeat_end_time )
                $repeatEventClause = "NOW() <= {$repeatEvent}"; 
                // diff(now && logged_date_time) >= repeat_interval
                $havingClause      = "HAVING TIMEDIFF(NOW(), latest_log_time) >= TIME('{$hrs}:00:00')";
                $groupByClause     = "GROUP BY reminder.contact_id, reminder.entity_id, reminder.entity_table"; 
                $selectClause     .= ", MAX(reminder.action_date_time) as latest_log_time";

                // Note this query tries to insert MAX(reminder.action_date_time) in place of is_error
                $query = "
INSERT INTO civicrm_action_log (contact_id, entity_id, entity_table, action_schedule_id, is_error)
{$selectClause} 
{$fromClause} 
{$joinClause}
INNER JOIN {$reminderJoinClause}
{$whereClause} AND {$repeatEventClause}
{$groupByClause}
{$havingClause}";
                CRM_Core_DAO::executeQuery( $query, array( 1 => array( $actionSchedule->id, 'Integer' ) ) );

                // just to clean is_error values
                $query = "
UPDATE civicrm_action_log 
SET    is_error = 0 
WHERE  action_date_time IS NULL AND action_schedule_id = %1";
                CRM_Core_DAO::executeQuery( $query, array( 1 => array( $actionSchedule->id, 'Integer' ) ) );
            }
        }
    }
}

$cron = new CRM_Cron_Action( );
$cron->run( );

