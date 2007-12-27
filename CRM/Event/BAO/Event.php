<?php

/*
 +--------------------------------------------------------------------+
 | CiviCRM version 2.0                                                |
 +--------------------------------------------------------------------+
 | Copyright CiviCRM LLC (c) 2004-2007                                |
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
 * @copyright CiviCRM LLC (c) 2004-2007
 * $Id$
 *
 */

require_once 'CRM/Event/DAO/Event.php';

class CRM_Event_BAO_Event extends CRM_Event_DAO_Event 
{

    /**
     * class constructor
     */
    function __construct( ) 
    {
        parent::__construct( );
    }
    
    /**
     * Takes a bunch of params that are needed to match certain criteria and
     * retrieves the relevant objects. Typically the valid params are only
     * contact_id. We'll tweak this function to be more full featured over a period
     * of time. This is the inverse function of create. It also stores all the retrieved
     * values in the default array
     *
     * @param array $params   (reference ) an assoc array of name/value pairs
     * @param array $defaults (reference ) an assoc array to hold the flattened values
     *
     * @return object CRM_Event_BAO_ManageEvent object
     * @access public
     * @static
     */
    static function retrieve( &$params, &$defaults ) 
    {
        $event  = new CRM_Event_DAO_Event( );
        $event->copyValues( $params );
        if ( $event->find( true ) ) {
            CRM_Core_DAO::storeValues( $event, $defaults );
            return $event;
        }
        return null;
    }

    /**
     * update the is_active flag in the db
     *
     * @param int      $id        id of the database record
     * @param boolean  $is_active value we want to set the is_active field
     *
     * @return Object             DAO object on sucess, null otherwise
     * @static
     */
    static function setIsActive( $id, $is_active ) 
    {
        return CRM_Core_DAO::setFieldValue( 'CRM_Event_DAO_Event', $id, 'is_active', $is_active );
    }
    
    /**
     * function to add the eventship types
     *
     * @param array $params reference array contains the values submitted by the form
     * @param array $ids    reference array contains the id
     * 
     * @access public
     * @static 
     * @return object
     */
    static function add(&$params, &$ids)
    {
        require_once 'CRM/Utils/Hook.php';
        
        if ( CRM_Utils_Array::value( 'event', $ids ) ) {
            CRM_Utils_Hook::pre( 'edit', 'Event', $ids['event_id'], $params );
        } else {
            CRM_Utils_Hook::pre( 'create', 'Event', null, $params ); 
        }
        
        $event =& new CRM_Event_DAO_Event( );
        $event->domain_id = CRM_Core_Config::domainID( );
        $event->id = CRM_Utils_Array::value( 'event_id', $ids );
        
        $event->copyValues( $params );
        $result = $event->save( );
        
        if ( CRM_Utils_Array::value( 'event', $ids ) ) {
            CRM_Utils_Hook::post( 'edit', 'Event', $event->id, $event );
        } else {
            CRM_Utils_Hook::post( 'create', 'Event', $event->id, $event );
        }
        
        return $result;
    }
    
    /**
     * function to create the event
     *
     * @param array $params reference array contains the values submitted by the form
     * @param array $ids    reference array contains the id
     * 
     * @access public
     * @static 
     * 
     */
    public static function create( &$params, &$ids) 
    {
        require_once 'CRM/Core/Transaction.php';
        $transaction = new CRM_Core_Transaction( );
        
        $event = self::add($params, $ids);
        
        if ( is_a( $event, 'CRM_Core_Error') ) {
            CRM_Core_DAO::transaction( 'ROLLBACK' );
            return $event;
        }
        
        $session = & CRM_Core_Session::singleton();
        $id = $session->get('userID');
        if ( !$id ) {
            $id = $params['contact_id'];
        } 
                
        // Log the information on successful add/edit of Event
        require_once 'CRM/Core/BAO/Log.php';
        $logParams = array(
                        'entity_table'  => 'civicrm_event',
                        'entity_id'     => $event->id,
                        'modified_id'   => $id,
                        'modified_date' => date('Ymd')
                        );
        
        CRM_Core_BAO_Log::add( $logParams );
        
        if ( CRM_Utils_Array::value( 'custom', $params ) &&
             is_array( $params['custom'] ) ) {
            require_once 'CRM/Core/BAO/CustomValueTable.php';
            CRM_Core_BAO_CustomValueTable::store( $params['custom'], 'civicrm_event', $event->id );
        }
        
        $transaction->commit( );
        
        return $event;
    }
     
    /**
     * Function to delete the event
     *
     * @param int $id  event id
     *
     * @access public
     * @static
     *
     */
    static function del( $id )
    { 
        require_once 'CRM/Core/BAO/CustomGroup.php';
        $extends   = array('event');
        $groupTree = CRM_Core_BAO_CustomGroup::getGroupDetail( null, null, $extends );
        
        foreach( $groupTree as $values ) {
            $query = "DELETE FROM " . $values['table_name'] . " WHERE entity_id = " . $id ; 
                        
            $params = array( 1 => array( $values['table_name'], 'string'),
                             2 => array( $id, 'integer') );
            
            CRM_Core_DAO::executeQuery( $query, CRM_Core_DAO::$_nullArray );
        }
        
        $dependencies = array(
                  'CRM_Event_DAO_EventPage'    => 
                             array( 
                                   'event_id'       => $id ),
                  'CRM_Core_DAO_UFJoin'        => 
                             array(
                                   'entity_id'      => $id,
                                   'entity_table'   => 'civicrm_event' ),
                  );
        
        foreach ( $dependencies as $daoName => $values ) {
            require_once (str_replace( '_', DIRECTORY_SEPARATOR, $daoName ) . ".php");
            eval('$dao =& new ' . $daoName . '( );');

            if ( $daoName == 'CRM_Core_DAO_CustomOption' ) {
                require_once 'CRM/Event/DAO/EventPage.php';
                $eventPage = new CRM_Event_DAO_EventPage( );
                $eventPage->event_id = $values['event_id'];
                $eventPage->find( );
                while ( $eventPage->fetch( ) ) {
                    eval('$dao =& new ' . $daoName . '( );');
                    $dao->entity_id    = $eventPage->id;
                    $dao->entity_table = $values['entity_table'];
                    $dao->find( );
                    while ( $dao->fetch( ) ) {
                        $dao->delete( );
                    }
                }
            } else {
                foreach ( $values as $fieldName => $fieldValue ) {
                    $dao->$fieldName = $fieldValue;
                }
                
                $dao->find();
                
                while ( $dao->fetch() ) {
                    $dao->delete();
                }
            }
        }
        
        require_once 'CRM/Event/DAO/Event.php';
        $event     = & new CRM_Event_DAO_Event( );
        $event->id = $id; 
        
        $event->find();
        while ($event->fetch() ) {
            return $event->delete();
        }
        return false;
    }
    
    /**
     * Function to get current/future Events 
     *
     * @param $all boolean true if events all are required else returns current and future events
     *
     * @static
     */
    static function getEvents( $all = false, $id = false) 
    {
        $query = "SELECT `id`, `title`, `start_date` FROM `civicrm_event`";
        
        if ( !$all ) {
            $endDate = date( 'YmdHis' );
            $query .= " WHERE `end_date` >= {$endDate};";
        }
        if ( $id ) {
            $query .= " WHERE `id` = {$id};";
        }

        $events = array( );
        
        $dao =& CRM_Core_DAO::executeQuery( $query, CRM_Core_DAO::$_nullArray );
        while ( $dao->fetch( ) ) {
            $events[$dao->id] = $dao->title . ' - '.CRM_Utils_Date::customFormat($dao->start_date);
        }
        
        return $events;
    }
    
    /**
     * Function to get events Summary
     *
     * @static
     * @return array Array of event summary values
     */
    static function getEventSummary( $admin = false )
    {
        $eventSummary = array( );
        
        $query = "SELECT count(id) as total_events
                  FROM   civicrm_event 
                  WHERE  civicrm_event.is_active=1";
        
        $dao =& CRM_Core_DAO::executeQuery( $query, CRM_Core_DAO::$_nullArray );
        
        if ( $dao->fetch( ) ) {
            $eventSummary['total_events'] = $dao->total_events;
        }
        
        if ( empty( $eventSummary ) ||
             $dao->total_events = 0 ) {
            return $eventSummary;
        }
        
        // Get the Id of Option Group for Event
        require_once 'CRM/Core/DAO/OptionGroup.php';
        $optionGroupDAO = new CRM_Core_DAO_OptionGroup();
        $optionGroupDAO->name = 'event_type';
        $optionGroupId = null;
        if ($optionGroupDAO->find(true) ) {
            $optionGroupId = $optionGroupDAO->id;
        }
        
        $params = array( 1 => array( $optionGroupId, 'Integer' ),
                         2 => array( CRM_Core_Config::domainID( ),
                                     'Integer' ) );
        $query = "
SELECT     civicrm_event.id as id, civicrm_event.title as event_title, civicrm_event.is_public as is_public,
           civicrm_event.max_participants as max_participants, civicrm_event.start_date as start_date,
           civicrm_event.end_date as end_date, civicrm_event.is_map as is_map,
           civicrm_option_value.label as event_type, count(civicrm_participant.id) as participants
FROM       civicrm_event
LEFT JOIN  civicrm_participant  ON ( civicrm_event.id = civicrm_participant.event_id ) 
LEFT JOIN  civicrm_option_value ON (
              civicrm_event.event_type_id = civicrm_option_value.value )
WHERE      civicrm_event.is_active = 1     AND
           civicrm_event.domain_id = %2    AND
           civicrm_participant.is_test = 0 AND
           civicrm_participant.status_id IN ( 1, 2 ) AND
           civicrm_option_value.option_group_id = %1
GROUP BY   civicrm_event.id
ORDER BY   civicrm_event.end_date DESC
LIMIT      0, 10
";
        
        $dao =& CRM_Core_DAO::executeQuery( $query, $params );
        
        $properties = array( 'eventTitle'      => 'event_title',      'isPublic'     => 'is_public', 
                             'maxParticipants' => 'max_participants', 'startDate'    => 'start_date', 
                             'endDate'         => 'end_date',         'eventType'    => 'event_type', 
                             'isMap'           => 'is_map',           'participants' => 'participants' );
        
        while ( $dao->fetch( ) ) {
            require_once 'CRM/Core/Config.php';
            $config = CRM_Core_Config::singleton();
            
            foreach ( $properties as $property => $name ) {
                $set = null;
                if (( $name == 'start_date' ) || 
                    ( $name == 'end_date' ) ) {
                    $eventSummary['events'][$dao->id][$property] = CRM_Utils_Date::customFormat($dao->$name, '%B %d%f %Y');
                } else if ( $name == 'participants' ) {
                    $eventSummary['events'][$dao->id][$property] = $dao->$name;
                    if ( $dao->$name ) {
                        $set = CRM_Utils_System::url( 'civicrm/event/search',"reset=1&force=1&event=$dao->id" );
                    }
                    
                    $eventSummary['events'][$dao->id]['participant_url'] = $set;
                } else if ( $name == 'is_public' ) {
                    if ( $dao->$name ) {
                        $set = 'Yes';
                    } else {
                        $set = 'No';
                    }
                    
                    $eventSummary['events'][$dao->id][$property] = $set;
                } else if ( $name == 'is_map' ) {
                    if ( $dao->$name && $config->mapAPIKey ) {
                        $params = array();
                        $values = array();
                        $ids    = array();
                        
                        $params['event_id'] = $dao->id;
                        
                        require_once 'CRM/Core/BAO/Location.php';
                        CRM_Core_BAO_Location::getValues($params, $values, $ids, 1 );
                        
                        if ( is_numeric( CRM_Utils_Array::value('geo_code_1',$values['location'][1]['address']) ) ||
                             ( $config->mapGeoCoding &&
                               $values['location'][1]['address']['city'] && 
                               $values['location'][1]['address']['state_province_id']
                             ) ) {
                            $set = CRM_Utils_System::url( 'civicrm/contact/map',"reset=1&eid={$dao->id}" );
                        }
                    }
                    
                    $eventSummary['events'][$dao->id][$property] = $set;
                    if ( $admin ) {
                        $eventSummary['events'][$dao->id]['configure'] =
                            CRM_Utils_System::url( "civicrm/admin/event", "action=update&id=$dao->id&reset=1" );
                    }
                } else {
                    $eventSummary['events'][$dao->id][$property] = $dao->$name;
                }
            }
        }
        
        return $eventSummary;
    }
   
    /**
     * function to get the information to map a event
     *
     * @param  array  $ids    the list of ids for which we want map info
     *
     * @return null|string     title of the event
     * @static
     * @access public
     */
        
    static function &getMapInfo(&$id ) 
    {
        
        $sql = "
SELECT
  civicrm_event.id as event_id,
  civicrm_event.title as display_name,
  civicrm_address.street_address as street_address,
  civicrm_address.city as city,
  civicrm_address.postal_code as postal_code,
  civicrm_address.postal_code_suffix as postal_code_suffix,
  civicrm_address.geo_code_1 as latitude,
  civicrm_address.geo_code_2 as longitude,
  civicrm_state_province.abbreviation as state,
  civicrm_country.name as country,
  civicrm_location_type.name as location_type
FROM civicrm_event
LEFT JOIN civicrm_location ON (civicrm_location.entity_table = 'civicrm_event' AND
                               civicrm_event.id = civicrm_location.entity_id )
LEFT JOIN civicrm_address ON civicrm_location.id = civicrm_address.location_id
LEFT JOIN civicrm_state_province ON civicrm_address.state_province_id = civicrm_state_province.id
LEFT JOIN civicrm_country ON civicrm_address.country_id = civicrm_country.id
LEFT JOIN civicrm_location_type ON civicrm_location_type.id = civicrm_location.location_type_id
WHERE civicrm_event.id =" . CRM_Utils_Type::escape( $id, 'Integer' );
       
        $dao =& new CRM_Core_DAO( );
        $dao->query( $sql );

        $locations = array( );

        $config =& CRM_Core_Config::singleton( );

        while ( $dao->fetch( ) ) {
       
            $location = array( );
            $location['displayName'] = $dao->display_name ;
            $location['lat'        ] = $dao->latitude;
            $location['lng'        ] = $dao->longitude;
            $address = '';

            CRM_Utils_String::append( $address, '<br />',
                                      array( $dao->street_address, $dao->city) );
            CRM_Utils_String::append( $address, ', ',
                                      array(   $dao->state, $dao->postal_code ) );
            CRM_Utils_String::append( $address, '<br /> ',
                                      array( $dao->country ) );
            $location['address'      ] = $address;
            $location['url'          ] = CRM_Utils_System::url( 'civicrm/event/register', 'reset=1&action=preview&id=' . $dao->event_id );
            $location['location_type'] = $dao->location_type;
            $eventImage = '<img src="' . $config->resourceBase . 'i/contact_org.gif" alt="Organization " height="20" width="15" />';
            $location['image'] = $eventImage;
            $locations[] = $location;
        }
        return $locations;
    }

    /**
     * function to get the complete information of an event
     *
     * @param  date    $start    the start date for the event
     * @param  integer $type     the type id for the event 
     *
     * @return  array  $all      array of all the events that are searched
     * @static
     * @access public
     */      
    static function &getCompleteInfo( $start = null, $type =null, $eventId = null ) 
    {
       
        if ( $start ) {
            // get events with start_date >= requested start
            $condition =  CRM_Utils_Type::escape( $start, 'Date' );
        } else {
            // get events with start date >= today
            $condition =  date("Ymd");
        }
        if ( $type ) {
            $condition = $condition . " AND civicrm_event.event_type_id = " . CRM_Utils_Type::escape( $type, 'Integer' ); 

        }

        // Get the Id of Option Group for Event Types
        require_once 'CRM/Core/DAO/OptionGroup.php';
        $optionGroupDAO = new CRM_Core_DAO_OptionGroup();
        $optionGroupDAO->name = 'event_type';
        $optionGroupId = null;
        if ($optionGroupDAO->find(true) ) {
            $optionGroupId = $optionGroupDAO->id;
        }
        
        $query = "
SELECT
  civicrm_event.id as event_id, 
  civicrm_email.email as email, 
  civicrm_event.title as summary, 
  civicrm_event.start_date as start, 
  civicrm_event.end_date as end, 
  civicrm_event.description as description, 
  civicrm_event.is_show_location as is_show_location, 
  civicrm_option_value.label as event_type, 
  civicrm_loc_block.name as location_name, 
  civicrm_address.street_address as street_address, 
  civicrm_address.supplemental_address_1 as supplemental_address_1, 
  civicrm_address.supplemental_address_2 as supplemental_address_2, 
  civicrm_address.city as city, 
  civicrm_address.postal_code as postal_code, 
  civicrm_address.postal_code_suffix as postal_code_suffix, 
  civicrm_state_province.abbreviation as state, 
  civicrm_country.name AS country
FROM civicrm_event
LEFT JOIN civicrm_loc_block ON civicrm_event.loc_block_id = civicrm_loc_block.id
LEFT JOIN civicrm_address ON civicrm_loc_block.address_id = civicrm_address.id
LEFT JOIN civicrm_state_province ON civicrm_address.state_province_id = civicrm_state_province.id
LEFT JOIN civicrm_country ON civicrm_address.country_id = civicrm_country.id
LEFT JOIN civicrm_email ON civicrm_loc_block.email_id = civicrm_email.id
LEFT JOIN civicrm_option_value ON (
                                    civicrm_event.event_type_id = civicrm_option_value.value AND
                                    civicrm_option_value.option_group_id = %1 )
WHERE civicrm_event.is_active = 1 
      AND civicrm_event.domain_id = %2
      AND civicrm_event.is_public = 1 
      AND civicrm_event.start_date >= {$condition}"; 
    
        if(isset( $eventId )) {
            $query .= " AND civicrm_event.id =$eventId ";
        }
        $query .=" ORDER BY   civicrm_event.start_date ASC";


        $params = array( 1 => array( $optionGroupId, 'Integer' ),
                         2 => array( CRM_Core_Config::domainID( ),'Integer' )
                         );
        
        $dao =& CRM_Core_DAO::executeQuery( $query, $params );
        $all = array( );
        $config =& CRM_Core_Config::singleton( );
        
        while ( $dao->fetch( ) ) {
        
            $info                     = array( );
            $info['event_id'     ]    = $dao->event_id;
            $info['uid'          ]    = "CiviCRM_EventID_" . $dao->event_id . "@" . $config->userFrameworkBaseURL;
            $info['summary'      ]    = $dao->summary;
            $info['description'  ]    = $dao->description;
            $info['start_date'   ]    = $dao->start;
            $info['end_date'     ]    = $dao->end;
            $info['contact_email']    = $dao->email;
            $info['event_type'   ]    = $dao->event_type;
            $info['is_show_location'] = $dao->is_show_location;
  
  
            $address = '';
            require_once 'CRM/Utils/String.php';
            CRM_Utils_String::append( $address, ', ',
                                      array( $dao->location_name) );
            $addrFields = array(
                            'street_address'         => $dao->street_address,
                            'supplemental_address_1' => $dao->supplemental_address_1,
                            'supplemental_address_2' => $dao->supplemental_address_2,
                            'city'                   => $dao->city,
                            'state_province'         => $dao->state,
                            'postal_code'            => $dao->postal_code,
                            'postal_code_suffix'     => $dao->postal_code_suffix,
                            'country'                => $dao->country,
                            'county'                 => null
                            );           
            
            require_once 'CRM/Utils/Address.php';
            CRM_Utils_String::append( $address, ', ',
                                      CRM_Utils_Address::format($addrFields) );
            $info['location'     ] = $address;
            $info['url'          ] = CRM_Utils_System::url( 'civicrm/event/info', 'reset=1&id=' . $dao->event_id, true, null, false );
           
            $all[] = $info;
        }
        
        return $all;
    }

    /**
     * This function is to make a copy of a Event, including
     * all the fields in the event Wizard
     *
     * @param int $id the event id to copy
     *
     * @return void
     * @access public
     */
    static function copy( $id )
    {
        $fieldsToPrefix = array( 'title' => ts( 'Copy of ' ) );
        
        $copyEvent      =& CRM_Core_DAO::copyGeneric( 'CRM_Event_DAO_Event', array( 'id' => $id ), null, $fieldsToPrefix );
        $copyEventPage  =& CRM_Core_DAO::copyGeneric( 'CRM_Event_DAO_EventPage', 
                                                      array( 'event_id'    => $id),
                                                      array( 'event_id'    => $copyEvent->id ));
        $copyCustom     =& CRM_Core_DAO::copyGeneric( 'CRM_Core_DAO_CustomOption',  
                                                      array( 'entity_id'    => $id,
                                                             'entity_table' => 'civicrm_event_page'),
                                                      array( 'entity_id'    => $copyEvent->id ));
        $copyUF         =& CRM_Core_DAO::copyGeneric( 'CRM_Core_DAO_UFJoin',
                                                      array( 'entity_id'    => $id,
                                                             'entity_table' => 'civicrm_event'),
                                                      array( 'entity_id'    => $copyEvent->id ));
        $copyCustomData =& CRM_Core_DAO::copyGeneric( 'CRM_Core_DAO_CustomValue',
                                                      array( 'entity_id'    => $id,
                                                             'entity_table' => 'civicrm_event'),
                                                      array( 'entity_id'    => $copyEvent->id ));

             
        require_once 'CRM/Core/BAO/Location.php';
        require_once 'CRM/Event/Form/ManageEvent/Location.php';
        $params   = array( 'entity_id' => $id ,'entity_table' => 'civicrm_event');
        
        $location = CRM_Core_BAO_Location::getValues($params, $values, $ids, 1);
        
        $values['entity_id']    = $copyEvent->id ;
        $values['entity_table'] = 'civicrm_event';
        
        $values['location'][1]['id']                      = null;
        $values['location'][1]['contact_id']              = null;
        
        unset($values['location'][1]['address']['id']);
        unset($values['location'][1]['address']['location_id']);
        
        $values['location'][1]['phone'][1]['id']          = null;
        $values['location'][1]['phone'][1]['location_id'] = null;
        $values['location'][1]['email'][1]['id']          = null;
        $values['location'][1]['email'][1]['location_id'] = null;
        $values['location'][1]['im'][1]['id']             = null;
        $values['location'][1]['im'][1]['location_id']    = null;
        
        $loc = CRM_Core_BAO_Location::create( $values, null, 'event' );
        $values['loc_block_id'] = $loc['id'];
    }

}
?>
