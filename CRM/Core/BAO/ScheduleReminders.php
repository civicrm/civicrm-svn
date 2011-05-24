<?php

/*
 +--------------------------------------------------------------------+
 | CiviCRM version 3.4                                                |
 +--------------------------------------------------------------------+
 | Copyright (C) 2011 Marty Wright                                    |
 | Licensed to CiviCRM under the Academic Free License version 3.0.   |
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

require_once 'CRM/Core/DAO/ActionSchedule.php';
require_once 'CRM/Core/DAO/ActionMapping.php';

/**
 * This class contains functions for managing Scheduled Reminders
 */
class CRM_Core_BAO_ScheduleReminders extends CRM_Core_DAO_ActionSchedule
{

    static function getMapping(  ) 
    {
        $dao  = new CRM_Core_DAO_ActionMapping( );
        $dao->find(  );

        $mapping = $defaults = array();
        while ( $dao->fetch( ) ) { 
            CRM_Core_DAO::storeValues( $dao, $defaults );
            $mapping[$dao->id] = $defaults;
        }
        return $mapping;
    }


    static function getSelection(  ) 
    {
        $mapping  = self::getMapping(  );

        require_once 'CRM/Core/PseudoConstant.php';
        require_once 'CRM/Event/PseudoConstant.php';
        $participantStatus = CRM_Event_PseudoConstant::participantStatus( null, null, 'label' );
        $activityStatus = CRM_Core_PseudoConstant::activityStatus();
        $event = CRM_Event_PseudoConstant::event( null, false, "( is_template IS NULL OR is_template != 1 )" );
        $activityType = CRM_Core_PseudoConstant::activityType(false);
        $eventType = CRM_Event_PseudoConstant::eventType();

        foreach ( $mapping as $value ) {
            $entityValue  = $value['entity_value'];
            $entityStatus = $value['entity_status'];
            $entityDate = $value['entity_date'];
          
            if( $entityValue == 'activity_type' &&
                $value['entity'] == 'civicrm_activity' ) {
                $key = 'Activity';
            } elseif( $entityValue == 'event_type' &&
                $value['entity'] == 'civicrm_participant') {
                $key = 'EventType';
            } elseif( $entityValue == 'civicrm_event' &&
                $value['entity'] == 'civicrm_participant' ) {
                $key = 'EventName';
            }
            $sel1[$key] = $key;

            switch ($entityValue) {
            case 'activity_type':
                $sel2[$key] = $activityType;
                break;

            case 'event_type':
                $sel2[$key] = $eventType;
                break;

            case 'civicrm_event':
                require_once 'CRM/Event/PseudoConstant.php';
                $sel2[$key] = $event;
                break;
            }

            switch ($entityDate) {
            case 'activity_date_time':
                $sel4[$entityDate] = ts('Activity Date Time');
                break;

            case 'event_start_date':
                $sel4[$entityDate] = ts('Event Start Date');
                break;
            }
            
        }
        $sel3 = $sel2;

        foreach ( $mapping as $value ) {
            $entityValue  = $value['entity_value'];
            $entityStatus = $value['entity_status'];
          
            if( $entityValue == 'activity_type' &&
                $value['entity'] == 'civicrm_activity' ) {
                $key = 'Activity';
            } elseif( $entityValue == 'event_type' &&
                $value['entity'] == 'civicrm_participant') {
                $key = 'EventType';
            } elseif( $entityValue == 'civicrm_event' &&
                $value['entity'] == 'civicrm_participant' ) {
                $key = 'EventName';
            }
                      
            switch ($entityStatus) {
            case 'activity_status':
                foreach($sel3[$key] as $kkey => &$vval) {
                    $vval = $activityStatus;
                }
                break;

            case 'civicrm_participant_status_type':
                foreach($sel3[$key] as $kkey => &$vval) {
                    $vval = $participantStatus;
                }
                break;
            }
        }
        
        return array(  $sel1 , $sel2, $sel3, $sel4 );


    }
   
    // function to retrieve a list of contact-ids that belongs to current actionMapping/site.
    static function getRecipientContacts( $mappingID ) {

        require_once 'CRM/Core/BAO/ActionMapping.php';
        $actionMapping = new CRM_Core_DAO_ActionMapping( );
        $actionMapping->id = $mappingID;
        
        if ( $actionMapping->find( true ) ) {
            switch ( $actionMapping->entity ) {
            case 'civicrm_activity ' :
                $query = "
SELECT id 
FROM civicrm_contact ";
                break;
            }
        }
    }
}