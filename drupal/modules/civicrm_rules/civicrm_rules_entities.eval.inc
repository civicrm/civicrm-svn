<?php

/**
 * @file
 * Contains rules integration for the user module needed during evaluation.
 *
 * @addtogroup rules
 * @{
 */

/**
 * Condition civicrm_event type
 */
function civicrm_rules_condition_event_type($entityobj, $eventTypes) {
    if ( !isset($eventTypes[$entityobj->event_type_id]) || 
         empty($eventTypes[$entityobj->event_type_id]) ) {
        return false;
    }
    return true;
}

/**
 * Condition civicrm_participant_role - return true if it matches chosen role
 */
function civicrm_rules_condition_participant_role($entityobj, $roleIDs) {
    if ( !isset($roleIDs[$entityobj->role_id]) || 
         empty($roleIDs[$entityobj->role_id]) ) {
        return false;
    }
    return true;
}

/**
 * Handler to get the user.
 * @todo this should either move into an event specific file or be more generic
 */
function civicrm_rules_events_argument_civicrm_event($eventobj) {
    if ( !isset($eventobj['event']) ) {
        return;
    }
    static $result;
    static $contact;

    if (empty($result) || empty($contact)) {
        $event = $eventobj['event'];
        //todo BAO POST hook leaves created_id blank, the api call is a work-around for now 
        if (empty($event->created_id)){
            require_once 'api/api.php';
            $result = civicrm_api('Event','get',array('version' => 3,'id' => $event->id, 'sequential' =>1));
            if ( !CRM_Utils_Array::value('is_error', $result) && CRM_Utils_Array::value('created_id', $result['values'][0]) ) {
                $contact = civicrm_api('UFMatch','get',array('version' => 3,'contact_id' => $result['values'][0]['created_id'], 'sequential' =>1)); 
            }
        } else {
            $contact = civicrm_api('UFMatch','get',array('version' => 3,'contact_id' => $event->created_id, 'sequential' =>1));  
        }
    }

    if(!empty($contact) && !CRM_Utils_Array::value('is_error', $result) && is_numeric($contact['values'][0]['uf_id'])) {
        return $contact['values'][0]['uf_id'];
    }
    return null;
}

/*
 *get node related via cck  - this relies on there being a field called civicrm_event.
 *If you use this field on more than one event type it will load the last one (not something you should do)
 *I wanted to do it using a civicrm_event cck type but got a bit stuck.
 */
function civicrm_rules_events_argument_civicrm_eventnode($participantobj){
    if ( !module_exists('content') || !isset($participantobj['participant']) ) {
        return null;
    }

    $participant = $participantobj['participant'];
    $eventID     = $participant->event_id;
    //todo there must be a clever way to get a node based on the cck field but I don't know it yet
    //I looked into the search_index_links but that seems to rely on touching the node 
    // really I am only needing type_name now as I am specifying the field name to be 
    // field_civicrm_event_value but leaving this in in case I go back to the cck way
    $sql = "SELECT nf.field_name, i.type_name
FROM {content_node_field} nf
LEFT JOIN {content_node_field_instance} i ON nf.field_name = i.field_name
WHERE nf.field_name LIKE '%civicrm_event'
LIMIT 1";
   
    $results = db_query($sql);
   
    while ($result = db_fetch_object($results)) {
        //todo rewrite this with %s & figure out why cck field ends in contact_id
        $sql = "SELECT nid  FROM {content_type_" . $result->type_name . "} WHERE " . $result->field_name  . "_value = %d";
        $nodeid = db_result(db_query($sql, $eventID));
        return $nodeid;
    }
    return null;
}

/**
 * Handler to get the user. This will work if $entityobj->contact_id is defined
 * e.g. for participant object, contribution object
 */
function civicrm_rules_events_argument_civicrm_contactID_load_user($entityobj) {
    require_once 'api/api.php';
    $contact = civicrm_api('UFMatch','get',array('version' => 3,'contact_id' => $entityobj->contact_id, 'sequential' =>1));
    if(!empty($contact) && is_numeric( $contact['values'][0]['uf_id'])) {
        return $contact['values'][0]['uf_id'];
    }
    return null;
}