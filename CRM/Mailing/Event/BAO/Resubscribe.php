<?php

/*
 +--------------------------------------------------------------------+
 | CiviCRM version 1.8                                                |
 +--------------------------------------------------------------------+
 | Copyright CiviCRM LLC (c) 2004-2007                                |
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
 | License along with this program; if not, contact CiviCRM LLC       |
 | at info[AT]civicrm[DOT]org.  If you have questions about the       |
 | Affero General Public License or the licensing  of CiviCRM,        |
 | see the CiviCRM license FAQ at http://civicrm.org/licensing        |
 +--------------------------------------------------------------------+
*/

/**
 *
 * @package CRM
 * @copyright CiviCRM LLC (c) 2004-2007
 * $Id$
 *
 */


require_once 'Mail/mime.php';
require_once 'CRM/Utils/Verp.php';

require_once 'CRM/Mailing/Event/DAO/Resubscribe.php';

class CRM_Mailing_Event_BAO_Resubscribe {

    /**
     * class constructor
     */
    function __construct( ) {
        parent::__construct( );
    }

    /**
     * Resubscribe a contact to the domain
     *
     * @param int $job_id       The job ID
     * @param int $queue_id     The Queue Event ID of the recipient
     * @param string $hash      The hash
     * @return boolean          Was the contact succesfully resubscribed?
     * @access public
     * @static
     */
    public static function resub_to_domain($job_id, $queue_id, $hash) {
        $q =& CRM_Mailing_Event_BAO_Queue::verify($job_id, $queue_id, $hash);
        if (! $q) {
            return false;
        }
        CRM_Core_DAO::transaction('BEGIN');
        $contact =& new CRM_Contact_BAO_Contact();
        $contact->id = $q->contact_id;
        $contact->is_opt_out = false;
        $contact->save();
        
        $ue =& new CRM_Mailing_Event_BAO_Unsubscribe();
        $ue->event_queue_id = $queue_id;
        $ue->org_unsubscribe = 1;
        //$ue->time_stamp = date('YmdHis');
        if ( $ue->find(true) ) {
            $ue->delete();
        }

        $shParams = array(
                          'contact_id'    => $q->contact_id,
                          'group_id'      => null,
                          'status'        => 'Added',
                          'method'        => 'Email',
                          'tracking'      => $ue->id
                          );
        CRM_Contact_BAO_SubscriptionHistory::create($shParams);
        
        CRM_Core_DAO::transaction('COMMIT');
        
        return true;
    }

    /**
     * Resubscribe a contact from all groups that received this mailing
     *
     * @param int $job_id       The job ID
     * @param int $queue_id     The Queue Event ID of the recipient
     * @param string $hash      The hash
     * @return array|null $groups    Array of all groups from which the contact was removed, or null if the queue event could not be found.
     * @access public
     * @static
     */
    public static function &resub_to_mailing($job_id, $queue_id, $hash) {
        /* First make sure there's a matching queue event */
        $q =& CRM_Mailing_Event_BAO_Queue::verify($job_id, $queue_id, $hash);
        if (! $q) {
            return null;
        }
        
        $contact_id = $q->contact_id;
        
        CRM_Core_DAO::transaction('BEGIN');
        
        $do =& new CRM_Core_DAO();
        $mg         = CRM_Mailing_DAO_Group::getTableName();
        $job        = CRM_Mailing_BAO_Job::getTableName();
        $mailing    = CRM_Mailing_BAO_Mailing::getTableName();
        $group      = CRM_Contact_BAO_Group::getTableName();
        $gc         = CRM_Contact_BAO_GroupContact::getTableName();
        
        $do->query("
            SELECT      $mg.entity_table as entity_table,
                        $mg.entity_id as entity_id
            FROM        $mg
            INNER JOIN  $job
                ON      $job.mailing_id = $mg.mailing_id
            WHERE       $job.id = " 
                . CRM_Utils_Type::escape($job_id, 'Integer') . "
                AND     $mg.group_type = 'Include'");
        
        /* Make a list of groups and a list of prior mailings that received 
         * this mailing */
         
        $groups = array();
        $mailings = array();
        
        while ($do->fetch()) {
            if ($do->entity_table == $group) {
                //$groups[$do->entity_id] = true;
                $groups[$do->entity_id] = $do->entity_table;
            } else if ($do->entity_table == $mailing) {
                $mailings[] = $do->entity_id;
            }
        }
        
        /* As long as we have prior mailings, find their groups and add to the
         * list */
        while (! empty($mailings)) {
            $do->query("
                SELECT      $mg.entity_table as entity_table,
                            $mg.entity_id as entity_id
                FROM        $mg
                WHERE       $mg.mailing_id IN (".implode(', ', $mailings).")
                    AND     $mg.group_type = 'Include'");
            
            $mailings = array();
            
            while ($do->fetch()) {
                if ($do->entity_table == $group) {
                    $groups[$do->entity_id] = true;
                } else if ($do->entity_table == $mailing) {
                    $mailings[] = $do->entity_id;
                }
            }
        }

        /* Now we have a complete list of recipient groups.  Filter out all
         * those except smart groups and those that the contact belongs to */
        $do->query("
            SELECT      $group.id as group_id,
                        $group.title as title
            FROM        $group
            LEFT JOIN   $gc
                ON      $gc.group_id = $group.id
            WHERE       $group.id IN (".implode(', ', array_keys($groups)).")
                AND     ($group.saved_search_id is not null
                            OR  ($gc.contact_id = $contact_id
                                AND $gc.status = 'Removed')
                        )");
                        
        while ($do->fetch()) {
            $groups[$do->group_id] = $do->title;
        }
        
        $contacts = array($contact_id);
        
        foreach ($groups as $group_id => $group_name) {
            
            if ( $group_name == 'civicrm_group' ) {
                list($total, $removed, $notremoved) = CRM_Contact_BAO_GroupContact::addContactsToGroup( $contacts, $group_id, 'Email', 'Added');
            } else {
                list($total, $removed, $notremoved) = CRM_Contact_BAO_GroupContact::removeContactsFromGroup( $contacts, $group_id, 'Email');
                //CRM_Contact_BAO_GroupContact::removeContactsFromGroup( $contacts, $group_id, 'Email', $queue_id);
            }
            
            if ($notremoved) {
                
                unset($groups[$group_id]);
            }
        }
        
        $ue =& new CRM_Mailing_Event_BAO_Unsubscribe();
        $ue->event_queue_id = $queue_id;
        $ue->org_resubscribe = 0;
        //$ue->time_stamp = date('YmdHis');
        if ($ue->find(true)) {
            $ue->delete();
        }
        
        CRM_Core_DAO::transaction('COMMIT');
        return $groups;
    }

}

?>
