<?php

/*
 +--------------------------------------------------------------------+
 | CiviCRM version 2.2                                                |
 +--------------------------------------------------------------------+
 | Copyright CiviCRM LLC (c) 2004-2009                                |
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
 * @copyright CiviCRM LLC (c) 2004-2009
 * $Id$
 *
 */

/**
 * This class holds all the Pseudo constants that are specific to Event. This avoids
 * polluting the core class and isolates the Event
 */
class CRM_Event_PseudoConstant extends CRM_Core_PseudoConstant 
{
    /**
     * Event
     *
     * @var array
     * @static
     */
    private static $event; 
    
    /**
     * Participant Status 
     *
     * @var array
     * @static
     */
    private static $participantStatus; 
    
    /**
     * Participant Role
     *
     * @var array
     * @static
     */
    private static $participantRole; 
    
    /**
     * Participant Listing
     *
     * @var array
     * @static
     */
    private static $participantListing; 
    
    /**
     * Event Type.
     *
     * @var array
     * @static
     */
    private static $eventType; 
    
    /**
     * Get all the n events
     *
     * @access public
     * @return array - array reference of all events if any
     * @static
     */
    public static function &event( $id = null )
    {
        if ( ! self::$event ) {
            CRM_Core_PseudoConstant::populate( self::$event,
                                               'CRM_Event_DAO_Event',
                                               false, 'title', 'is_active', null, null);
        }
        if ($id) {
            if (array_key_exists($id, self::$event)) {
                return self::$event[$id];
            } else {
                return null;
            }
        }
        return self::$event;
    }
    
    /**
     * Get all the n participant statuses
     *
     * @access public
     * @return array - array reference of all participant statuses if any
     * @static
     */
    public static function &participantStatus( $id = null, $cond = null ) 
    { 
        if ( self::$participantStatus === null ) {
            self::$participantStatus = array( );
        }
        
        $index = $cond ? $cond : 'No Condition';
        if ( ! CRM_Utils_Array::value( $index, self::$participantStatus ) ) {
            self::$participantStatus[$index] = array( );
            require_once "CRM/Core/PseudoConstant.php";
            CRM_Core_PseudoConstant::populate( self::$participantStatus[$index],
                                               'CRM_Event_DAO_ParticipantStatusType',
                                               false, 'name', 'is_active', $cond, 'id' );
        }
        
        if ( $id ) {
            return self::$participantStatus[$index][$id];
        }
        
        return self::$participantStatus[$index];
    }

    /**
     * Return a status-type-keyed array of status classes
     *
     * @return array  of status classes, keyed by status type
     */
    static function &participantStatusClass()
    {
        static $statusClasses = null;

        if ($statusClasses === null) {
            self::populate($statusClasses, 'CRM_Event_DAO_ParticipantStatusType', true, 'class');
        }

        return $statusClasses;
    }
    
    /**
     * Get all the n participant roles
     *
     * @access public
     * @return array - array reference of all participant roles if any
     * @static
     */
    public static function &participantRole( $id = null )
    {
        if ( ! self::$participantRole ) {
            self::$participantRole = array( );
            require_once "CRM/Core/OptionGroup.php";
            self::$participantRole = CRM_Core_OptionGroup::values("participant_role");
        }
        
        If( $id ) {
            return self::$participantRole[$id];
        }
        
        return self::$participantRole;
    }

    /**
     * Get all the participant listings
     *
     * @access public
     * @return array - array reference of all participant listings if any
     * @static
     */
    public static function &participantListing( $id = null )
    {
        if ( ! self::$participantListing ) {
            self::$participantListing = array( );
            require_once "CRM/Core/OptionGroup.php";
            self::$participantListing = CRM_Core_OptionGroup::values("participant_listing");
        }
        
        if( $id ) {
            return self::$participantListing[$id];
        }
        
        return self::$participantListing;
    }
    
    /**
     * Get all  event types.
     *
     * @access public
     * @return array - array reference of all event types.
     * @static
     */
    public static function &eventType( $id = null )
    {
        if ( ! self::$eventType ) {
            self::$eventType = array( );
            require_once "CRM/Core/OptionGroup.php";
            self::$eventType = CRM_Core_OptionGroup::values("event_type");
        }
        
        if( $id ) {
            return self::$eventType[$id];
        }
        
        return self::$eventType;
    }
    
}

