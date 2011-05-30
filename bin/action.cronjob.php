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

        $this->_tempTable = "civicrm_cron_action_temp";
    }

    public function run( )
    {
        require_once 'CRM/Core/BAO/ActionLog.php';
        require_once 'CRM/Core/BAO/ScheduleReminders.php';
        $mappings = CRM_Core_BAO_ScheduleReminders::getMapping( );

        require_once 'CRM/Contact/BAO/Contact.php';
        require_once 'CRM/Core/BAO/MessageTemplates.php';
        foreach ( $mappings as $mappingID => $mapping ) {
            $this->buildRecipientContacts( $mappingID );

            $this->sendMailings( $mappingID );
        }
    }

    public function sendMailings( $mappingID ) {
        require_once 'CRM/Core/BAO/Domain.php';
        $domainValues     = CRM_Core_BAO_Domain::getNameAndEmail( );
        $fromEmailAddress = "$domainValues[0] <$domainValues[1]>";
        
        require_once 'CRM/Core/DAO/ActionMapping.php';
        $mapping = new CRM_Core_DAO_ActionMapping( );
        $mapping->id = $mappingID;
        $mapping->find( true );

        $actionSchedule = new CRM_Core_DAO_ActionSchedule( );
        $actionSchedule->id = $mappingID;
        $actionSchedule->find( true );

        $query = "select * from $this->_tempTable LIMIT 1";
        $dao   = CRM_Core_DAO::executeQuery( $query );
        
        // QUESTION: in cases when same contact has multiple activites for example, should we 
        // send multiple mails ? if not should the log be maintained for every activity ?
        while ( $dao->fetch() ) {
            $toEmail  = CRM_Contact_BAO_Contact::getPrimaryEmail( $dao->contact_id );
            if ( $toEmail ) {
                $result = CRM_Core_BAO_ScheduleReminders::sendReminder( $dao->contact_id,
                                                                        $toEmail,
                                                                        $mappingID,
                                                                        $fromEmailAddress );
                if ( ! $result || is_a( $result, 'PEAR_Error' ) ) {
                    // we could not send an email, for now we ignore, CRM-3406
                }
                
                // do action logging
                $actionLogParams  = array( 'entity_id'          => $dao->entity_id,
                                           'entity_table'       => $mapping->entity,
                                           'action_schedule_id' => $actionSchedule->id, // action_schedule Id
                                           );
                // FIXME: repetition_number should be updated by create function itself.
                CRM_Core_BAO_ActionLog::create( $actionLogParams );
            }
            
            // delete processed record
            $query = "DELETE from {$this->_tempTable} WHERE contact_id = %1 AND entity_id = %2 AND entity_table = %3";
            CRM_Core_DAO::executeQuery( $query, array( 1 => array( $dao->contact_id, 'Integer' ),
                                                       2 => array( $dao->entity_id, 'Integer'  ), 
                                                       3 => array( $mapping->entity, 'String'  ) ) );
            $dao->free();
            
            // get the next record for processing
            $query = "select * from {$this->_tempTable} LIMIT 1";
            $dao   = CRM_Core_DAO::executeQuery( $query );
        }
    }
    
    public function buildRecipientContacts( $mappingID ) {
        $actionSchedule = new CRM_Core_DAO_ActionSchedule( );
        $actionSchedule->id = $mappingID;

        if ( $actionSchedule->find( true ) ) {
            CRM_Core_DAO::executeQuery( "DROP TABLE IF EXISTS {$this->_tempTable}" );
            $sql = "
CREATE TEMPORARY TABLE {$this->_tempTable} ( contact_id   int, 
                                    entity_id    int, 
                                    entity_table varchar(128), 
                                    UNIQUE UI_contact_entity_id_table (contact_id, entity_id, entity_table) ) 
ENGINE=HEAP";
            CRM_Core_DAO::executeQuery( $sql );

            require_once 'CRM/Core/DAO/ActionMapping.php';
            $mapping = new CRM_Core_DAO_ActionMapping( );
            $mapping->id = $mappingID;
            $mapping->find( true );

            $select = $join = $where = array( );

            $value  = explode( CRM_Core_DAO::VALUE_SEPARATOR, $actionSchedule->entity_value  );
            $value  = implode( ',', $value );

            $status = explode( CRM_Core_DAO::VALUE_SEPARATOR, $actionSchedule->entity_status );
            $status = implode( ',', $status );
        
            $recipientOptions = CRM_Core_OptionGroup::values( $mapping->entity_recipient );

            $from = "{$mapping->entity} e";

            if ( $mapping->entity == 'civicrm_activity' ) {
                switch ( $recipientOptions[$actionSchedule->recipient] ) {
                case 'Activity Assignees':
                    $select[] = "r.assignee_contact_id as contact_id";
                    $join[]   = "INNER JOIN civicrm_activity_assignment r ON  r.activity_id = e.id";
                    break;
                case 'Activity Source':
                    $select[] = "e.source_contact_id as contact_id";
                    break;
                case 'Activity Targets':
                    $select[] = "r.target_contact_id as contact_id";
                    $join[]   = "INNER JOIN civicrm_activity_target r ON  r.activity_id = e.id";
                    break;
                default:
                    break;
                }

                $select[] = "e.id as entity_id";
                $select[] = "'{$mapping->entity}' as entity_table";
                $join[]   = "LEFT JOIN civicrm_action_log reminder ON reminder.id = (select al.id FROM civicrm_action_log al WHERE al.entity_id = e.id and al.entity_table = '{$mapping->entity}' ORDER BY al.action_date_time DESC LIMIT 1)";

                // build where clause
                if ( !empty($value) ) {
                    $where[]  = "e.activity_type_id IN ({$value})";
                }
                if ( !empty($status) ) {
                    $where[]  = "e.status_id IN ({$status})";
                }

                // datetime where clause
                $startEvent = ( $actionSchedule->first_action_condition == 'before' ? "DATE_SUB" : "DATE_ADD" ) . 
                        "(e.activity_date_time, INTERVAL {$actionSchedule->first_action_offset} {$actionSchedule->first_action_unit})";
                $startEventClause = "reminder.id IS NULL AND NOW() >= {$startEvent}";

                if ( $actionSchedule->is_repeat ) {
                    // if repeat is turned ON:
                    $repeatEvent = ( $actionSchedule->repetition_end_action == 'before' ? "DATE_SUB" : "DATE_ADD" ) . 
                        "(e.activity_date_time, INTERVAL {$actionSchedule->repetition_end_frequency_interval} {$actionSchedule->repetition_end_frequency_unit})";

                    if ( $actionSchedule->repetition_start_frequency_unit == 'day' ) {
                        $hrs = 24 * $actionSchedule->repetition_start_frequency_interval;
                    } else if ( $actionSchedule->repetition_start_frequency_unit == 'week' ) {
                        $hrs = 24 * $actionSchedule->repetition_start_frequency_interval * 7;
                    } else {
                        $hrs = $actionSchedule->repetition_start_frequency_interval;
                    }
                    
                    $repeatEventClause = "reminder.id IS NOT NULL AND NOW() <= {$repeatEvent} AND TIMEDIFF(NOW(), reminder.action_date_time) >= TIME('{$hrs}:00:00')";
                }

                // IF NO logs:
                // ( now >= date_built_from_start_time )
                // Otherwise IF repeat is turned ON:
                // ( (now <= repeat_end_time ) && ( diff(now && logged_date_time) >= repeat_interval ) )
                $where[] = $actionSchedule->is_repeat ? "( ({$startEventClause}) OR ($repeatEventClause) )" : $startEventClause;
            }

            // build final query
            $selectClause = "SELECT " . implode( ', ', $select );
            $fromClause   = "FROM $from";
            $joinClause   = !empty( $join ) ? implode( ' ', $join ) : '';
            $whereClause  = "WHERE " . (!empty( $where ) ? implode( ' AND ', $where ) : '(1)');
            
            $query = "
INSERT INTO {$this->_tempTable} (contact_id, entity_id, entity_table)
{$selectClause} 
{$fromClause} 
{$joinClause} 
{$whereClause}";

            CRM_Core_DAO::executeQuery( $query );
        }
    }
}

$cron = new CRM_Cron_Action( );
$cron->run( );

