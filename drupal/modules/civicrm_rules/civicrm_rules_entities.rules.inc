<?php 
//	
require_once 'civicrm_rules_entities_utils.inc';
/*
 * Declare post functions for valid post entities. This declares 5 actions for each enabled object
 * Enabled objects are defined through the 'civicrm_rules_post_entities' drupal variable 
 */
function civicrm_rules_entities_event_info() {
    $validObjects = variable_get('civicrm_rules_post_entities', array( ) );
    $events = array();
    $defaults = array( 'access callback' => 'civicrm_rules_rules_integration_access',
                       'module'          => 'civicrm', 
                       );

    if ( isset($validObjects['contact']) ) {
        unset ($validObjects['contact']); //Contact is dealt with elsewhere
    }
    if (is_array($validObjects)) {
        foreach ($validObjects as $entity => $enabled) {
            if(!$enabled == 0) {
                //todo consider building the entity name into the argument rather than calling the same argument for each
                $events['civicrm_' . $entity . '_create'] = $defaults + 
                    array( 
                          'label'     => t( "$entity has been created" ),
                          'group'     =>  'CiviCRM ' . $entity,
                          'variables' => civicrm_rules_entities_arguments( t( 'Created ' . $entity), $entity )
                           );
                $events['civicrm_' . $entity . '_edit'] = $defaults + 
                    array( 
                          'group'     => 'CiviCRM ' . $entity,
                          'label'     => t( "$entity has been updated" ),
                          'variables' => civicrm_rules_entities_arguments( t( 'Updated ' . $entity ), $entity )
                           );
                $events['civicrm_' . $entity . '_view'] = $defaults + 
                    array( 
                          'group'     => 'CiviCRM ' . $entity,
                          'label'     => t( "$entity has been viewed" ),
                          'variables' => civicrm_rules_entities_arguments( t( 'Viewed ' . $entity ), $entity )
                           );
                $events['civicrm_' . $entity . '_delete'] = $defaults + 
                    array( 
                          'group'     => 'CiviCRM ' . $entity,
                          'label'     => t( "$entity has been deleted" ),
                          'variables' => civicrm_rules_entities_arguments( t( 'Deleted ' . $entity ), $entity ),);
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
                 'civicrm_rules_condition_participant_role' => 
                 array(
                       'label' => t('Participant Role'),
                       'base' => 'civicrm_rules_condition_participant_role',
                       'parameter' => civicrm_rules_entities_conditions( t( 'Participant Role' ), 'participant' ), 
                       'group' => 'CiviCRM participant' 
                       ),
                 'civicrm_rules_condition_event_type' => 
                 array(
                       'label' => t('Event Type'),
                       'base' => 'civicrm_rules_condition_event_type',
                       'parameter' => civicrm_rules_entities_conditions( t( 'Created Event' ), 'event' ), 
                       'group' => 'CiviCRM event',
                       ),
                 );
}

/*
 * Hook Arguments - define the objects that will be loaded by each event & made available for actions
 * @todo restructure this so that either all arguments are in one file by themselves or each entity has it's own file (prefered)
 * @return array arguments - including object Name + label, any specific items & acting user
 */
function civicrm_rules_entities_arguments($label, $type = 'contact') {
    $default =  array( $type   => array( 'type'  => $type,
                                         'label' => $label ) );
    if ($type == 'event') {
        return $default + array( 
                                'cms_user' => array(
                                                    'type' => 'user',
                                                    'label' => t('User that created the event'),
                                                    'handler' => 'civicrm_rules_events_argument_civicrm_event',
                                                    ), );
        //TODO add something here to call 'civicrm_rules_events_argument_civicrm_event', 
        //with 'contact' as arg & load contact - not required right now but really - this is the point of rules!
      
    } 
    
    if ($type == 'participant') {
        return $default + array(
    
                                $type   => array( 
                                                 'type'  => $type,
                                                 'label' => $label ),
                                'event_node' => array(
                                                      'type' => 'node',
                                                      'label' => t('Node related to the event'),
                                                      'handler' => 'civicrm_rules_events_argument_civicrm_eventnode'),
                                'cms_user' => array(
                                                    'type' => 'user',
                                                    'label' => t('User that registered for the event'),
                                                    'handler' => 'civicrm_rules_events_argument_civicrm_contactID_load_user',
                                                    ),
                                );
    }
    return $default;
}

/**
 * Returns conditions available for CiviCRM Rules
 */
function civicrm_rules_entities_conditions( $label, $type = 'contact' ) {

    $default = array($type   => array('type'  => $type,'label' => $label ));
    if ($type == 'participant'){
        //ie pass in participant object & value from field
        return $default + array('civicrm_participant_role' => array( 'type' => 'list<integer>',
                                                                     'label' => t('Participant role'),
                                                                     'options list' => 'civicrm_rules_get_participant_role_options',
                                                                     ) );
    }
    
    if ($type == 'event'){
        return $default + array( 'civicrm_event_type' => array( 'type' => 'list<integer>',
                                                                'label' => t('CiviCRM event type'),
                                                                'options list' => 'civicrm_rules_get_event_type_options',
                                                                ) );
       
    }
    
	return $default;
}

function civicrm_rules_condition_event_node(&$entityobj,$roleID,$field) {
    //just a hack for our site
    //SELECT * FROM `content_type_og_node` WHERE `field_civicrm_event_value` =993
    $sql = "SELECT * FROM `content_type_og_node` WHERE `field_civicrm_event_value` = $entityobj->id"; 
    $nid = db_result(db_query($sql));
    return $entityobj->id == $nid  ? true : false;
}

/**
 * Event type options
 */
function civicrm_rules_get_event_type_options( ) {
    return civicrm_rules_get_options( 'event_type' );
}


/**
 * Participant role options
 */
function civicrm_rules_get_participant_role_options( ) {
    return civicrm_rules_get_options( 'participant_role' );
}

