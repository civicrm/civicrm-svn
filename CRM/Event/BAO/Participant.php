<?php

  /*
   +--------------------------------------------------------------------+
   | CiviCRM version 2.1                                                |
   +--------------------------------------------------------------------+
   | Copyright CiviCRM LLC (c) 2004-2008                                |
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
   *
   * @package CRM
   * @copyright CiviCRM LLC (c) 2004-2007
   * $Id$
   *
   */

require_once 'CRM/Event/DAO/Participant.php';

class CRM_Event_BAO_Participant extends CRM_Event_DAO_Participant
{
    /**
     * static field for all the membership information that we can potentially import
     *
     * @var array
     * @static
     */
    static $_importableFields = null;

    function __construct()
    {
        parent::__construct();
    }
        
    /**
     * takes an associative array and creates a participant object
     *
     * the function extract all the params it needs to initialize the create a
     * participant object. the params array could contain additional unused name/value
     * pairs
     *
     * @param array  $params (reference ) an assoc array of name/value pairs
     * @param array $ids    the array that holds all the db ids
     *
     * @return object CRM_Event_BAO_Participant object
     * @access public
     * @static
     */
    static function add(&$params, &$ids)
    {
        require_once 'CRM/Utils/Hook.php';
        
        if ( CRM_Utils_Array::value( 'participant', $ids ) ) {
            CRM_Utils_Hook::pre( 'edit', 'Participant', $ids['participant'], $params );
        } else {
            CRM_Utils_Hook::pre( 'create', 'Participant', null, $params ); 
        }
        
        // converting dates to mysql format
        $params['register_date']  = CRM_Utils_Date::isoToMysql($params['register_date']);
        
        $participantBAO =& new CRM_Event_BAO_Participant();
        $participantBAO->copyValues($params);
        $participantBAO->id = CRM_Utils_Array::value( 'participant', $ids );
        
        $result = $participantBAO->save();
        
        $session = & CRM_Core_Session::singleton();
        
        // reset the group contact cache for this group
        require_once 'CRM/Contact/BAO/GroupContactCache.php';
        CRM_Contact_BAO_GroupContactCache::remove( );

        if ( CRM_Utils_Array::value( 'participant', $ids ) ) {
            CRM_Utils_Hook::post( 'edit', 'Participant', $participantBAO->id, $participantBAO );
        } else {
            CRM_Utils_Hook::post( 'create', 'Participant', $participantBAO->id, $participantBAO );
        }
        
        return $result;
    }

    /**
     * Given the list of params in the params array, fetch the object
     * and store the values in the values array
     *
     * @param array $params input parameters to find object
     * @param array $values output values of the object
     *
     * @return CRM_Event_BAO_Participant|null the found object or null
     * @access public
     * @static
     */
    static function getValues( &$params, &$values, &$ids ) 
    {
        $participant =& new CRM_Event_BAO_Participant( );
        $participant->copyValues( $params );
        $participant->find();
        $participants = array();
        while ( $participant->fetch() ) {
            $ids['participant'] = $participant->id;
            CRM_Core_DAO::storeValues( $participant, $values[$participant->id] );
            $participants[$participant->id] = $participant;
        }       
        return $participants;
    }

    /**
     * Given a participant id, return contribution/fee line items
     *
     * @param $id int|array participant id
     *
     * @return array line items
     */
    static function getLineItems( $id ) {
        $lineItems = array();
        require_once 'CRM/Core/DAO.php';
        $query = "
SELECT li.label, li.qty, li.unit_price, li.line_total
  FROM civicrm_participant AS p,
       civicrm_participant_payment AS pp,
       civicrm_line_item AS li
 WHERE p.id = %1
   AND pp.participant_id = p.id
   AND li.entity_id = pp.contribution_id
   AND li.entity_table = 'civicrm_contribution'";

        $params = array( 1 => array( $id, 'Integer' ) );

        $dao = CRM_Core_DAO::executeQuery( $query, $params );

        while ( $dao->fetch() ) {
            $lineItems[] = array(
                'label' => $dao->label,
                'qty' => $dao->qty,
                'unit_price' => $dao->unit_price,
                'line_total' => $dao->line_total
            );
        }
        return $lineItems;
    }

    /**
     * takes an associative array and creates a participant object
     *
     * @param array $params (reference ) an assoc array of name/value pairs
     * @param array $ids    the array that holds all the db ids
     *
     * @return object CRM_Event_BAO_Participant object 
     * @access public
     * @static
     */

    static function &create(&$params, &$ids) 
    { 
        require_once 'CRM/Utils/Date.php';

        require_once 'CRM/Core/Transaction.php';
        $transaction = new CRM_Core_Transaction( );
        
        if ( CRM_Utils_Array::value( 'participant', $ids ) ) {
            $status = CRM_Core_DAO::getFieldValue( 'CRM_Event_DAO_Participant', $ids['participant'], 'status_id' );
        }
        
        $participant = self::add($params, $ids);
        
        if ( is_a( $participant, 'CRM_Core_Error') ) {
            $transaction->rollback( );
            return $participant;
        }
        
        if ( ( ! CRM_Utils_Array::value( 'participant', $ids ) ) ||
             ( $params['status_id'] != $status ) ) {
            self::addActivity( $participant );
        }
        
        $session = & CRM_Core_Session::singleton();
        $id = $session->get('userID');
        if ( !$id ) {
            $id = $params['contact_id'];
        }
        
        // add custom field values       
         if ( CRM_Utils_Array::value( 'custom', $params ) &&
             is_array( $params['custom'] ) ) {
            require_once 'CRM/Core/BAO/CustomValueTable.php';
            CRM_Core_BAO_CustomValueTable::store( $params['custom'], 'civicrm_participant', $participant->id );
        }
        
        if ( CRM_Utils_Array::value('note', $params) || CRM_Utils_Array::value('participant_note', $params)) {
            if ( CRM_Utils_Array::value('note', $params) ) {
                $note = CRM_Utils_Array::value('note', $params);
            } else {
                $note = CRM_Utils_Array::value('participant_note', $params);
            }
        
            if ( ! isset($ids['note']) ) {
                $noteDetails = CRM_Core_BAO_Note::getNote( $participant->id, 'civicrm_participant' );
                $ids['note']['id'] = array_pop( array_flip( $noteDetails ) );
            }

            require_once 'CRM/Core/BAO/Note.php';
            $noteParams = array(
                                'entity_table'  => 'civicrm_participant',
                                'note'          => $note,
                                'entity_id'     => $participant->id,
                                'contact_id'    => $id,
                                'modified_date' => date('Ymd')
                                );
            
            CRM_Core_BAO_Note::add( $noteParams, $ids['note'] );
        }

        // Log the information on successful add/edit of Participant data.
        require_once 'CRM/Core/BAO/Log.php';
        require_once 'CRM/Event/PseudoConstant.php' ;
        $logParams = array(
                        'entity_table'  => 'civicrm_participant',
                        'entity_id'     => $participant->id,
                        'data'          => CRM_Event_PseudoConstant::participantStatus($participant->status_id),
                        'modified_id'   => $id,
                        'modified_date' => date('Ymd')
                        );
        
        CRM_Core_BAO_Log::add( $logParams );
        
        $params['participant_id'] = $participant->id;
        
        $transaction->commit( );
        
        return $participant;
    }

    /**
     * Function to add activity record for Event participation
     *
     * @param object  $participant   (reference) $participant object
     *
     * @access public
     * @static
     */
    static function addActivity( &$participant ) 
    {
        require_once "CRM/Event/BAO/Event.php";
        $event = CRM_Event_BAO_Event::getEvents( true, $participant->event_id );
        $date = date( 'YmdHis' );
        require_once "CRM/Event/PseudoConstant.php";
        $roles  = CRM_Event_PseudoConstant::participantRole( );
        $status = CRM_Event_PseudoConstant::participantStatus( );

        $subject = $event[$participant->event_id];
        if ( CRM_Utils_Array::value( $participant->role_id, $roles ) ) {
            $subject .= ' - ' . $roles[$participant->role_id]; 
        }
        if ( CRM_Utils_Array::value( $participant->status_id, $status ) ) {
            $subject .= ' - ' . $status[$participant->status_id]; 
        }

        require_once "CRM/Core/OptionGroup.php";
        $activityParams = array( 'source_contact_id' => $participant->contact_id,
                                 'source_record_id'  => $participant->id,
                                 'activity_type_id'  => CRM_Core_OptionGroup::getValue( 'activity_type',
                                                                                        'Event Registration',
                                                                                        'name' ),
                                 'subject'            => $subject,
                                 'activity_date_time' => $date,
                                 'is_test'            => $participant->is_test,
                                 'status_id'          => 2
                                 );

        require_once 'api/v2/Activity.php';
        if ( is_a( civicrm_activity_create( $activityParams ), 'CRM_Core_Error' ) ) {
            return false;
        }
    }
    
    /**
     * check whether the event is 
     * full for participation
     *
     * @param int $eventId
     *
     * @static
     * @access public
     */
    static function eventFull( $eventId )
    {
        require_once 'CRM/Event/PseudoConstant.php';
        $statusTypes  = CRM_Event_PseudoConstant::participantStatus( null, false );
        $status = implode( ',', array_keys( $statusTypes ) );
        if ( !$status ) {
            $status = 0;
        }
        // fix for CRM-2877, participant has to have is_filter true
        // for event to be full
        $query = "SELECT   count(civicrm_participant.id) as total_participants,
                           civicrm_event.max_participants as max_participants,
                           civicrm_event.event_full_text as event_full_text  
                  FROM     civicrm_participant, civicrm_event 
                  WHERE    civicrm_participant.event_id = civicrm_event.id
                     AND   civicrm_participant.status_id IN ( {$status} )
                     AND   civicrm_participant.is_test = 0 
                     AND   civicrm_participant.event_id = {$eventId} 
                  GROUP BY civicrm_participant.event_id";
        
        $dao =& CRM_Core_DAO::executeQuery( $query, CRM_Core_DAO::$_nullArray );
        
        if ( $dao->fetch( ) ) {
            if( $dao->max_participants == NULL ) {
                return false;
            }
            
            if( $dao->total_participants >= $dao->max_participants ) {
                if( $dao->event_full_text ) {
                    return $dao->event_full_text;
                } else {
                    return ts( "This event is full !!!" );
                }
            }
        }
        return false;

    }

    /**
     * combine all the importable fields from the lower levels object
     *
     * @return array array of importable Fields
     * @access public
     */
    function &importableFields( $contactType = 'Individual', $status = true, $onlyParticipant = false ) 
    {
        if ( ! self::$_importableFields ) {
            if ( ! self::$_importableFields ) {
                self::$_importableFields = array();
            }

            if ( !$onlyParticipant ) {
                if ( !$status ) {
                    $fields = array( '' => array( 'title' => ts('- do not import -') ) );
                } else {
                    $fields = array( '' => array( 'title' => ts('- Participant Fields -') ) );
                }
            } else {
                $fields = array( );
            }
            
            require_once 'CRM/Core/DAO/Note.php';
            $tmpFields     = CRM_Event_DAO_Participant::import( );
            unset($tmpFields['participant_is_test']);
            $note          = array( 'participant_note' => array( 'title' => 'Participant Note',
                                                                 'name'  => 'participant_note'));

            $tmpConatctField = array( );
            if ( !$onlyParticipant ) {
                require_once 'CRM/Contact/BAO/Contact.php';
                $contactFields = CRM_Contact_BAO_Contact::importableFields( $contactType, null );
                if ($contactType == 'Individual') {
                    require_once 'CRM/Core/DAO/DupeMatch.php';
                    $dao = & new CRM_Core_DAO_DupeMatch();
                    $dao->find(true);
                    $fieldsArray = explode('AND',$dao->rule);
                } elseif ($contactType == 'Household') {
                    $fieldsArray = array('household_name', 'email');
                } elseif ($contactType == 'Organization') {
                    $fieldsArray = array('organization_name', 'email');
                }

                if( is_array($fieldsArray) ) {
                    foreach ( $fieldsArray as $value) {
                        $tmpConatctField[trim($value)] = CRM_Utils_Array::value(trim($value),$contactFields);
                        if (!$status) {
                            $title = $tmpConatctField[trim($value)]['title']." (match to contact)" ;
                        } else {
                            $title = $tmpConatctField[trim($value)]['title'];
                        }
                        
                        $tmpConatctField[trim($value)]['title'] = $title;
                    }
                }
            }
            $tmpConatctField['external_identifier'] = CRM_Utils_Array::value('external_identifier',$contactFields);
            $tmpConatctField['external_identifier']['title'] = $contactFields['external_identifier']['title'] . " (match to contact)";
            
            $fields = array_merge($fields, $tmpConatctField);
            $fields = array_merge($fields, $tmpFields);
            $fields = array_merge($fields, $note);
            //$fields = array_merge($fields, $optionFields);
            
            $fields = array_merge($fields, CRM_Core_BAO_CustomField::getFieldsForImport('Participant'));
            self::$_importableFields = $fields;
        }
        return self::$_importableFields;
    }

    /**
     * function to get the event name/sort name for a particular participation / participant
     *
     * @param  int    $participantId  id of the participant

     * @return array $name associated array with sort_name and event title
     * @static
     * @access public
     */
    static function participantDetails( $participantId ) 
    {
        $query = "
SELECT civicrm_contact.sort_name as name, civicrm_event.title as title
FROM   civicrm_participant 
   LEFT JOIN civicrm_event   ON (civicrm_participant.event_id = civicrm_event.id)
   LEFT JOIN civicrm_contact ON (civicrm_participant.contact_id = civicrm_contact.id)
WHERE  civicrm_participant.id = {$participantId}
";
        $dao =& CRM_Core_DAO::executeQuery( $query, CRM_Core_DAO::$_nullArray );
        
        $details = array( );
        while ( $dao->fetch() ) {
            $details['name' ] = $dao->name;
            $details['title'] = $dao->title;
        }
        
        return $details;
    }
  
    /**
     * Get the values for pseudoconstants for name->value and reverse.
     *
     * @param array   $defaults (reference) the default values, some of which need to be resolved.
     * @param boolean $reverse  true if we want to resolve the values in the reverse direction (value -> name)
     *
     * @return void
     * @access public
     * @static
     */
    static function resolveDefaults(&$defaults, $reverse = false)
    {
        require_once 'CRM/Event/PseudoConstant.php';

        self::lookupValue($defaults, 'event', CRM_Event_PseudoConstant::event(), $reverse);
        self::lookupValue($defaults, 'status', CRM_Event_PseudoConstant::participantStatus(), $reverse);
        self::lookupValue($defaults, 'role', CRM_Event_PseudoConstant::participantRole(), $reverse);
    }

    /**
     * This function is used to convert associative array names to values
     * and vice-versa.
     *
     * This function is used by both the web form layer and the api. Note that
     * the api needs the name => value conversion, also the view layer typically
     * requires value => name conversion
     */
    static function lookupValue(&$defaults, $property, &$lookup, $reverse)
    {
        $id = $property . '_id';

        $src = $reverse ? $property : $id;
        $dst = $reverse ? $id       : $property;

        if (!array_key_exists($src, $defaults)) {
            return false;
        }

        $look = $reverse ? array_flip($lookup) : $lookup;
        
        if(is_array($look)) {
            if (!array_key_exists($defaults[$src], $look)) {
                return false;
            }
        }
        $defaults[$dst] = $look[$defaults[$src]];
        return true;
    }
    
    /**                          
     * Delete the record that are associated with this participation
     * 
     * @param  int  $id id of the participation to delete                                                                           
     * 
     * @return void
     * @access public 
     * @static 
     */ 
    static function deleteParticipant( $id ) 
    {
        require_once 'CRM/Core/Transaction.php';
        $transaction = new CRM_Core_Transaction( );

        //delete activity record
        require_once "CRM/Activity/BAO/Activity.php";
        $params = array( 'source_record_id' => $id,
                         'activity_type_id' => 5 );// activity type id for event registration

        CRM_Activity_BAO_Activity::deleteActivity( $params );

        // delete the participant payment record
        // we need to do this since the cascaded constraints
        // dont work with join tables
        require_once 'CRM/Event/BAO/ParticipantPayment.php';
        $p = array( 'participant_id' => $id );
        CRM_Event_BAO_ParticipantPayment::deleteParticipantPayment( $p );

        $participant = new CRM_Event_DAO_Participant( );
        $participant->id = $id;
        $participant->delete( );

        $transaction->commit( );
        return $participant;
    }
    
    /**
     *Checks duplicate participants
     *
     * @param array  $duplicates (reference ) an assoc array of name/value pairs
     * @param array $input an assosiative array of name /value pairs
     * from other function
     * @return object CRM_Contribute_BAO_Contribution object    
     * @access public
     * @static
     */
    static function checkDuplicate( $input, &$duplicates ) 
    {    
        $eventId         = CRM_Utils_Array::value( 'event_id'  , $input );
        $contactId      = CRM_Utils_Array::value( 'contact_id', $input );
        
        $clause = array( );
        $input = array( );
        
        if ( $eventId ) {
            $clause[]  = "event_id = %1";
            $input[1]  = array( $eventId, 'Integer' );
        }
        
        if ( $contactId ) {
            $clause[]  = "contact_id = %2";
            $input[2]  = array( $contactId, 'Integer' );
        }
        
        if ( empty( $clause ) ) {
            return false;
        }
        
        $clause = implode( ' AND ', $clause );
        
        $query = "SELECT id FROM civicrm_participant WHERE $clause";
        $dao =& CRM_Core_DAO::executeQuery( $query, $input );
        $result = false;
        while ( $dao->fetch( ) ) {
            $duplicates[] = $dao->id;
            $result = true;
        }
        return $result;
    }
    
    /**
     * fix the event level
     *
     * When price sets are used as event fee, fee_level is set as ^A
     * seperated string. We need to change that string to comma
     * separated string before using fee_level in view mode.
     *
     * @param string  $eventLevel  event_leval string from db
     * 
     * @static
     * @return void
     */
    static function fixEventLevel( &$eventLevel )
    {
        if ( ( substr( $eventLevel, 0, 1) == CRM_Core_BAO_CustomOption::VALUE_SEPERATOR ) &&
             ( substr( $eventLevel, -1, 1) == CRM_Core_BAO_CustomOption::VALUE_SEPERATOR ) ) {
            $eventLevel = implode( ', ', explode( CRM_Core_BAO_CustomOption::VALUE_SEPERATOR, 
                                                  substr( $eventLevel, 1, -1) ) );
        }
    }
}

