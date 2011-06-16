<?php 
//	
require_once 'civicrm_rules_entities_utils.inc';
/*
 * Declare post functions for valid post entities. This declares 5 actions for each enabled object
 * Enabled objects are defined through the 'civicrm_rules_post_entities' drupal variable 
 */
function civicrm_rules_entities_event_info(){
    $validObjects = variable_get('civicrm_rules_post_entities', array( ) );
    $events = array();
    
    if ( isset($validObjects['contact']) ) {
        unset ($validObjects['contact']); //Contact is dealt with elsewhere
    }
    if (is_array($validObjects)){
  
    foreach ($validObjects as $entity => $enabled){
        if(!$enabled == 0){
            //todo consider building the entity name into the argument rather than calling the same argument for each
            $events['civicrm_' . $entity . "_create"] = array( 
                                                              'label'     => t( "$entity has been created" ),
                                                              'module'   =>  'CiviCRM ' . $entity,
                                                              'arguments' => civicrm_rules_entities_arguments( t( 'Created ' . $entity ),
                                                                                                               "civicrm_$entity" ),);
            $events['civicrm_' . $entity . "_edit"] = array( 
                                                            'module'   =>  'CiviCRM ' . $entity,
                                                            'label'     => t( "$entity has been updated" ),
                                                            'arguments' => civicrm_rules_entities_arguments( t( 'Updated ' . $entity ),
                                                                                                             "civicrm_$entity" ),);
            $events['civicrm_' . $entity . "_view"] = array( 
                                                            'module'   =>  'CiviCRM ' . $entity,
                                                            'label'     => t( "$entity has been viewed" ),
                                                            'arguments' => civicrm_rules_entities_arguments( t( 'Viewed ' . $entity ),
                                                                                                             "civicrm_$entity" ),);
            $events['civicrm_' . $entity . "_delete"] = array( 
                                                              'module'   =>  'CiviCRM ' . $entity,
                                                              'label'     => t( "$entity has been deleted" ),
                                                              'arguments' => civicrm_rules_entities_arguments( t( 'Deleted ' . $entity ),
                                                                                                               "civicrm_$entity" ),);
        }
    }
        }
    return $events;

}


/**
 * implementation of hook_rules_condition_info()
 */
function civicrm_rules_entities_condition_info() {

    return array(
                 'civicrm_rules_condition_participant_role' => array(
                                                                     'label' => t('Participant Role'),
                                                                     'arguments' =>  civicrm_rules_entities_conditions(  t( 'Participant Role' ),'civicrm_participant' ), 
                                                                     'module' => 'CiviCRM participant',
                                                                     ),
                 'civicrm_rules_condition_event_type' => array(
                                                               'label' => t('Event Type'),
                                                               'arguments' =>  civicrm_rules_entities_conditions(  t( 'Created Event' ),'civicrm_event' ), 
                                                               'module' => 'CiviCRM event',
                                                               ),
                 );
}

/*
 * Hook Arguments - define the objects that will be loaded by each event & made available for actions
 * @todo restructure this so that either all arguments are in one file by themselves or each entity has it's own file (prefered)
 * @return array arguments - including object Name + label, any specific items & acting user
 */
function civicrm_rules_entities_arguments( $label, $type = 'contact' ){
    $default =  array( $type   => array( 'type'  => $type,
                                         'label' => $label ) )+ rules_events_global_user_argument();
    if ($type == 'civicrm_event'){
        return $default + array( 
                                'cms_user' => array(
                                                    'type' => 'user',
                                                    'label' => t('User that created the event'),
                                                    'handler' => 'civicrm_rules_events_argument_civicrm_event',
                                                    ) ,) ;
        //TODO add something here to call 'civicrm_rules_events_argument_civicrm_event', 
        //with 'contact' as arg & load contact - not required right now but really - this is the point of rules!
      
    } 
    
    if ($type == 'civicrm_participant'){
        return $default + array(
    
                                $type   => array( 
                                                 'type'  => $type,
                                                 'label' => $label ),
                                'event node' => array(
                                                      'type' => 'node',
                                                      'label' => t('Node related to the event'),
                                                      'handler' => 'civicrm_rules_events_argument_civicrm_eventnode',),
                                'cms_user' => array(
                                                    'type' => 'user',
                                                    'label' => t('User that registered for the event'),
                                                    'handler' => 'civicrm_rules_events_argument_civicrm_contactID_load_user',
                                                    ),
                                );
    }
}

/**
 * Handler to get the user.
 * @todo this should either move into an event specific file or be more generic
 */
function civicrm_rules_events_argument_civicrm_event(&$eventobj) {
    static $result;
    static $contact;
    if (empty($result) || empty($contact)){
        //todo BAO POST hook leaves created_id blank, the api call is a work-around for now 
        if (empty($eventobj->created_by)){
            require_once 'api/api.php';
            $result = civicrm_api('Event','get',array('version' => 3,'id' => $eventobj->id, 'sequential' =>1));
            $contact = civicrm_api('UFMatch','get',array('version' => 3,'contact_id' => $result['values'][0]['created_id'], 'sequential' =>1));
        }
    }

    if(is_numeric( $contact['values'][0]['uf_id'])){
        return user_load(array('uid' => $contact['values'][0]['uf_id']));
    }
}

/*
 *get node related via cck  - this relies on there being a field called civicrm_event.
 *If you use this field on more than one event type it will load the last one (not something you should do)
 *I wanted to do it using a civicrm_event cck type but got a bit stuck.
 */

function civicrm_rules_events_argument_civicrm_eventnode(&$participant){

    $eventID = $participant->event_id;
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
        return $node = node_load(array('nid' => $nodeid));
    }
}


/**
 * Handler to get the user. This will work if $entityobj->contact_id is defined
 * e.g. for participant object, contribution object
 */
function civicrm_rules_events_argument_civicrm_contactID_load_user(&$entityobj) {
    require_once 'api/api.php';
    $contact = civicrm_api('UFMatch','get',array('version' => 3,'contact_id' => $entityobj->contact_id, 'sequential' =>1));
    if(is_numeric( $contact['values'][0]['uf_id'])){
        return user_load(array('uid' => $contact['values'][0]['uf_id']));
    }
}

/**
 * Returns conditions available for CiviCRM Rules
 */
function civicrm_rules_entities_conditions( $label, $type = 'contact' ) {

    $default = array($type   => array('type'  => $type,'label' => $label ));
    if ($type == 'civicrm_participant'){
        //ie pass in participant object & value from field
        return array(
                     $type   => array('type'  => $type,
                                      'label' => $label ),
                     'civicrm_participant_role' => array(
                                                         'type' => 'string',
                                                         'label' => t('Participant role'),
                                                         )     
                     );
    }
    /* if ($type == 'civicrm_event'){
       return $default + array(  
       'civicrm_event_type' => array(
       'type' => 'value',
       'label' => t('CiviCRM event type'),
       ));

       }*/

 
    
	return $default;
}



/**
 * Condition civicrm_participant_role - return true if it matches chosen role
 */
function civicrm_rules_condition_participant_role(&$entityobj,$roleID,$field) {
    return $entityobj->role_id == $roleID ? true : false;
}

function civicrm_rules_condition_event_node(&$entityobj,$roleID,$field) {
    //just a hack for our site
    //SELECT * FROM `content_type_og_node` WHERE `field_civicrm_event_value` =993
    $sql = "SELECT * FROM `content_type_og_node` WHERE `field_civicrm_event_value` = $entityobj->id"; 
    $nid = db_result(db_query($sql));
    return $entityobj->id == $nid  ? true : false;
}

/**
 * Condition: Check for content types - Configuration form
 */
function civicrm_rules_condition_event_type_form($settings, &$form) {
    $form['settings']['civicrm_event_type'] = array(
                                                    '#type' => 'checkboxes',
                                                    '#title' => t('CiviCRM Event Types'),
                                                    '#options' => civicrm_rules_get_options( 'event_type'), 
                                                    '#multiple' => TRUE,
                                                    '#default_value' => isset($settings['civicrm_event_type']) ? $settings['civicrm_event_type'] : array(),
                                                    '#required' => TRUE,
 
                                                    );
}
/**
 * Condition civicrm_event type
 */
function civicrm_rules_condition_event_type(&$entityobj,&$eventTypes) {
    return empty($eventTypes[$entityobj->event_type_id])  ? false : true;
}
