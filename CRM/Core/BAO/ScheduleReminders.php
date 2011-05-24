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
            $sel1[$key] = $key;

            switch ($entityValue) {
            case 'activity_type':
                require_once 'CRM/Core/PseudoConstant.php';
                $sel2[$key] = CRM_Core_PseudoConstant::activityType(false);
                break;

            case 'event_type':
                require_once 'CRM/Event/PseudoConstant.php';
                $sel2[$key] = CRM_Event_PseudoConstant::eventType();
                break;

            case 'civicrm_event':
                require_once 'CRM/Event/PseudoConstant.php';
                $sel2[$key] = CRM_Event_PseudoConstant::event( null, false, "( is_template IS NULL OR is_template != 1 )" );
                break;
            }

            switch ($entityStatus) {
            case 'activity_status':
                $sel3[$key] = CRM_Core_PseudoConstant::activityStatus();
                break;

            case 'civicrm_participant_status_type':
                $sel3[$key] = CRM_Event_PseudoConstant::participantStatus( null, null, 'label' );
                break;
            }
        }

        return array(  $sel1 , $sel2, $sel3 );
    }
   
}