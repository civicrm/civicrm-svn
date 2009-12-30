<?php
/*
 * Heavily based on anonymous code contribution. No claim.
 *
 * civicrm_handler_field_event_type.inc
 * Display's Event Type after grabbing the label from the database
 *
 */
class civicrm_handler_field_encounter_medium extends views_handler_field {
    static $_encounter_medium;

    function construct( ) {
        if ( ! self::$_encounter_medium ) {
            if ( ! civicrm_initialize( ) ) {
                return;
            }
            require_once 'CRM/Core/OptionGroup.php';
            self::$_encounter_medium = CRM_Core_OptionGroup::values( 'encounter_medium', false );
        }
    }

    function render( $values ) {
        $sid = $values->{$this->field_alias};
        if ( empty( $sid ) ||
             (int ) $sid <= 0 ) {
            return null;
        }
        return self::$_encounter_medium[$values->{$this->field_alias}];
    }
}

