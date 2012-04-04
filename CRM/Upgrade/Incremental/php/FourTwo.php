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
 | Version 3, 19 November 2007.                                       |
 |                                                                    |
 | CiviCRM is distributed in the hope that it will be useful, but     |
 | WITHOUT ANY WARRANTY; without even the implied warranty of         |
 | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.               |
 | See the GNU Affero General Public License for more details.        |
 |                                                                    |
 | You should have received a copy of the GNU Affero General Public   |
 | License along with this program; if not, contact CiviCRM LLC       |
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

class CRM_Upgrade_Incremental_php_FourTwo {
    
    function verifyPreDBstate ( &$errors ) {
        return true;
    }
    
    function upgrade_4_2_alpha1( $rev ) {
    	$config = CRM_Core_Config::singleton( );

        $upgrade = new CRM_Upgrade_Form( );
        $upgrade->processSQL( $rev );

        // now rebuild all the triggers
        // CRM-9716
        CRM_Core_DAO::triggerRebuild( );

        // Create an event registration profile with a single email field
        $sql = "INSERT INTO `civicrm_uf_group` (`is_active`, `group_type`, `title`, `help_pre`, `help_post`, `limit_listings_group_id`, `post_URL`, `add_to_group_id`, `add_captcha`, `is_map`, `is_edit_link`, `is_uf_link`, `is_update_dupe`, `cancel_URL`, `is_cms_user`, `notify`, `is_reserved`, `name`, `created_id`, `created_date`, `is_proximity_search`)
              VALUES (1, 'Individual, Contact', 'Event Registration', NULL, NULL, NULL, NULL, NULL, 0, 0, 0, 0, 0, NULL, 0, NULL, 0, 'event_registration', NULL, NULL, 0);";
        CRM_Core_DAO::executeQuery($sql);
        $eventRegistrationId = CRM_Core_DAO::singleValueQuery('SELECT LAST_INSERT_ID()');
        $sql = "INSERT INTO `civicrm_uf_field` (`uf_group_id`, `field_name`, `is_active`, `is_view`, `is_required`, `weight`, `help_post`, `help_pre`, `visibility`, `in_selector`, `is_searchable`, `location_type_id`, `phone_type_id`, `label`, `field_type`, `is_reserved`)
              VALUES ({$eventRegistrationId}, 'email', 1, 0, 1, 1, NULL, NULL, 'User and User Admin Only', 0, 0, NULL, NULL, 'Email Address', 'Contact', 0);";
        CRM_Core_DAO::executeQuery($sql);

        $sql = "SELECT * FROM `civicrm_event` WHERE is_online_registration = 1;";
        $events = CRM_Core_DAO::executeQuery($sql);
        while ( $events->fetch() ) {
            // Get next weights for the event registration profile
            $nextMainWeight = $nextAdditionalWeight = 1;
            $sql = "SELECT weight FROM `civicrm_uf_join` WHERE entity_id = {$events->id} AND module = 'CiviEvent' ORDER BY weight DESC LIMIT 1";
            $weights = CRM_Core_DAO::executeQuery($sql);
            $weights->fetch();
            if ( isset($weights->weight) ) {
                $nextMainWeight += $weights->weight;
            }
            $sql = "SELECT weight FROM `civicrm_uf_join` WHERE entity_id = {$events->id} AND module = 'CiviEvent_Additional' ORDER BY weight DESC LIMIT 1";
            $weights = CRM_Core_DAO::executeQuery($sql);
            $weights->fetch();
            if ( isset($weights->weight) ) {
                $nextAdditionalWeight += $weights->weight;
            }
            // Add an event registration profile to the event
            $sql = "INSERT INTO `civicrm_uf_join` (`is_active`, `module`, `entity_table`, `entity_id`, `weight`, `uf_group_id`)
                    VALUES (1, 'CiviEvent', 'civicrm_event', {$events->id}, {$nextMainWeight}, {$eventRegistrationId});";
            CRM_Core_DAO::executeQuery($sql);
            $sql = "INSERT INTO `civicrm_uf_join` (`is_active`, `module`, `entity_table`, `entity_id`, `weight`, `uf_group_id`)
                    VALUES (1, 'CiviEvent_Additional', 'civicrm_event', {$events->id}, {$nextAdditionalWeight}, {$eventRegistrationId});";
            CRM_Core_DAO::executeQuery($sql);
        }

    }

  }